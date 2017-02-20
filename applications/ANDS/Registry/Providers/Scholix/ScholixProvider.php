<?php


namespace ANDS\Registry\Providers;


use ANDS\Registry\Providers\RIFCS\DatesProvider;
use ANDS\Registry\Providers\Scholix\ScholixDocument;
use ANDS\RegistryObject;
use ANDS\Util\XMLUtil;
use Carbon\Carbon;

class ScholixProvider implements RegistryContentProvider
{
    protected static $scholixableAttr = "scholixable";

    /**
     * if the record is a collection
     * and is related to a type of publication
     *
     * @param RegistryObject $record
     * @param null $relationships
     * @return bool
     */
    public static function isScholixable(RegistryObject $record, $relationships = null)
    {
        // early return if it's not a collection
        if ($record->class != "collection") {
            return false;
        }

        // record type needs to be a dataset or a collection
        if (!in_array($record->type, ['dataset', 'collection'])) {
            return false;
        }

        // search through combined relationships to see if there's a related publication
        if (!$relationships) {
            $relationships = RelationshipProvider::getMergedRelationships($record);
        }

        $types = collect($relationships)->map(function($item) {
            return $item->prop('to_related_info_type') ?: $item->prop('to_type');
        })->toArray();

        if (!in_array('publication', $types)) {
            return false;
        }

        return true;
    }

    /**
     * Process the object and (optionally) store processed data
     *
     * @param RegistryObject $record
     * @return mixed
     */
    public static function process(RegistryObject $record)
    {
        $record->deleteRegistryObjectAttribute(self::$scholixableAttr);

        $relationships = RelationshipProvider::getMergedRelationships($record);
        if (self::isScholixable($record, $relationships)) {
            $record->setRegistryObjectAttribute(self::$scholixableAttr, true);
        }

        // TODO implement get and store
    }

    /**
     * Return the processed content for given object
     *
     * @param RegistryObject $record
     * @return ScholixDocument
     */
    public static function get(RegistryObject $record)
    {
        $data = MetadataProvider::get($record);

        $doc = new ScholixDocument;

        $commonLinkMetadata = [
            'publicationDate' => DatesProvider::getPublicationDate($record, $data),
            'publisher' => [
                'name' => $record->group,
                'identifiers' => self::getIdentifiers($record, $data['recordData'])
            ],
            'linkProvider' => [
                'name' => 'Australian National Data Service',
                'identifiers' => [
                    'identifier' =>  'http://nla.gov.au/nla.party-1508909',
                    'schema' => 'AU-ANL:PEAU'
                ],
                'objectType' => $record->type,
                'title' => $record->title
            ]
        ];
        $relationships = self::getRelationships($record, $data);
        if (count($relationships) > 0) {
            $commonLinkMetadata['relationship'] = $relationships;
        }

        /**
         * Business Rule:
         * for each collection/identifier OR citationInfo/citationMetadata/identifier OR key
         * Produces a link to each of the related publication
         */

        $relatedPublications = self::getRelatedPublications($record, $data);

        // key
        $keyLink = $commonLinkMetadata;
        $keyLink['source'] = self::getKeySource($record, $data);
        foreach ($relatedPublications as $publication) {
            $keyTargetLink = $keyLink;
            if ($publication->isRelatesToIdentifier()) {
                $target = self::getTargetMetadataRelatedInfo($publication);
            } else {
                $target = self::getTargetMetadataObject($publication);
            }
            $keyTargetLink['target'] = $target;
            $doc->addLink($keyTargetLink);
        }

        // collection/identifier
        $collectionIdentifierLink = $commonLinkMetadata;
        $collectionIdentifierLink['source'] =
        $identifiers = IdentifierProvider::get($record, $data['recordData']);
        foreach ($identifiers as $identifier) {
            $identifierlink = $commonLinkMetadata;
            $identifierlink['source'] = self::getIdentifierSource($record, $identifier);
            $doc->addLink($identifierlink);
        }

        // collection/citationInfo/citationMetadata/identifier

        return $doc;
    }

    public static function getKeySource(RegistryObject $record, $data = null)
    {
        if (!$data) {
            $data = MetadataProvider::get($record);
        }

        $source = [
            'identifier' => [
                'value' => $record->key,
                'schema' => 'RIF-CS'
            ],
            'objectType' => $record->type,
            'title' => $record->title,
            'creators' => []
        ];

        /**
         * source[creator]
         * relatedObject/relation[@type=isPrincipleInvestigatorOf|hasPrincipalInvestigator]
         */
        $creators = collect($data['relationships'])->filter(function($item) {
            return in_array($item->prop('relation_type'), ['isPrincipleInvestigatorOf', 'hasPrincipalInvestigator']) && ($item->prop('to_class') == "party");
        })->map(function($item) {
            $to = $item->to();
            $creator = [
                'name' => $to->title
            ];
            $identifiers = collect(IdentifierProvider::get($to))->map(function($item) {
                return [
                    'identifier' => $item->value,
                    'schema' => $item->type
                ];
            })->toArray();
            if (count($identifiers) > 0) {
                $creator['identifier'] = $identifiers;
            }
            return $creator;
        })->values()->toArray();
        $source['creators'] = array_merge($source['creators'], $creators);

        /**
         * source[creator]
         * citationMetadata/contributor
         * TODO
         */

        return $source;
    }

    public static function getRelatedPublications(RegistryObject $record, $data = null)
    {
        if (!$data) {
            $data = MetadataProvider::get($record);
        }

        $relationships = collect($data['relationships'])->filter(function($item) {
            $type = $item->prop('to_related_info_type');
            if (!$type) {
                $type = $item->prop('to_type');
            }

            if ($type == 'publication') {
                return true;
            }
            return false;
        })->toArray();
        return $relationships;
    }

    public static function getRelationships(RegistryObject $record, $data = null)
    {
        if (!$data) {
            $data = MetadataProvider::get($record);
        }

        $relationships = collect($data['relationships'])->map(function($item) {
            $relationType = $item->prop('relation_type');
            $validRelationTypes = ['isCitedBy', 'isReferencedBy', 'isDocumentedBy', 'isSupplementedBy', 'isSupplementTo', 'isReviewedBy'];
            if (in_array($relationType, $validRelationTypes)) {
                return [
                    'name' => $item->prop('relation_type'),
                    'schema' => 'RIF-CS',
                    'inverse' => $item->prop('relation_type')
                ];
            }
            return null;
        })->filter(function($item) {
            return $item;
        })->values()->toArray();

        return $relationships;
    }

    public static function getIdentifiers(RegistryObject $record, $xml = null)
    {
        $identifiers = [];

        if (!$xml) {
            $data = MetadataProvider::getSelective($record, ['recordData']);
            $xml = $data['recordData'];
        }

        foreach (XMLUtil::getElementsByXPath($xml,
            'ro:registryObject/ro:' . $record->class . '/ro:identifier') AS $identifier) {
            $identifiers[] = [
                'identifier' => (string) $identifier,
                'schema' => (string) $identifier['type']
            ];
        }

        return $identifiers;
    }

    private static function getIdentifierSource($record, $identifier)
    {
        $source = [
            'identifier' => [
                'identifier' => $identifier['value'],
                'schema' => $identifier['type']
            ]
        ];


        return $source;
    }

    public static function getTargetMetadataRelatedInfo($publication)
    {
        return [
            'identifier' => [
                'identifier' => $publication->prop('to_identifier'),
                'schema' => $publication->prop('to_identifier_type')
            ],
            'objectType' => 'literature',
            'title' => $publication->prop('to_title')
        ];
    }

    public static function getTargetMetadataObject($publication)
    {
        return [];
    }
}