<footer id="footer" role="contentinfo">
    <section class="section swatch-black">
        <div class="container">
            <div class="row element-normal-top element-normal-bottom">
                <div class="col-md-8">
                    <p>
                        Research Data Australia is the data discovery service of the Australian National Data Service (ANDS).
                        ANDS is supported by the Australian Government through the National Collaborative Research Infrastructure Strategy Program.
                        <a href="http://ands.org.au/__data/assets/pdf_file/0006/387843/better-data-for-australian-research.pdf" target="_blank" style="color:#84A07B">Read more about ANDS...</a>
                    </p>
                </div>
                <div class="col-md-2">
                   <a href="https://education.gov.au/national-collaborative-research-infrastructure-strategy-ncris" target="_blank" class="gov_logo"><img style="height:75px;" src="<?php echo asset_url('images/NCRIS_PROVIDER_rev.png','core');?>" alt="National Collaborative Research Infrastructure Strategy (NCRIS)" /></a>
                </div>
                <div class="col-md-2">
                    <a href="http://www.ands.org.au/" class="footer_logo"><img src="{{asset_url('images/footer_logo_rev.png', 'core')}}" alt="" style="height:75px;"/></a>
                </div>
            </div>
            <div class="row element-normal-top element-normal-bottom">
                <div class="col-md-3">
                    <div id="categories-3" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">Quick Links</h3>
                        <ul>
                            <li class="cat-item"> <a href="{{portal_url()}}" title="">Home</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/about')}}" title="">About</a> </li>
                            <li class="cat-item"> <a href="{{portal_url()}}profile" title="">My RDA</a> </li>
                            <li class="cat-item myCustomTrigger"> <a href="" title="">Contact us</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/disclaimer')}}" title="">Disclaimer</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('page/privacy')}}" title="">Privacy Policy</a> </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div id="categories-3" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">Explore</h3>
                        <ul>
                            <li class="cat-item"> <a href="{{portal_url('themes')}}" title="">Themed Collections</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('theme/open-data')}}#!/access_rights=open" title="">Open Data</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('theme/services')}}#!/class=service" title="">Services and Tools</a> </li>
                            <li class="cat-item"> <a href="{{portal_url('grants')}}" title="">Projects and Grants</a> </li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <?php

                        $params = [
                            'url' => ((isset($ro) && isset($ro->core['slug']) && isset($ro->core['id'])) ? base_url().$ro->core['slug'].'/'.$ro->core['id'] : current_url() ),
                            'title' => ((isset($ro) && isset($ro->core['slug']) && isset($ro->core['id'])) ? $ro->core['title']. ' - Research Data Australia' : 'Research Data Australia' )
                        ];

                        if (isset($ro) && isset($ro->core['id'])) {
                            $params['id'] = $ro->core['id'];
                        }

                        $params = http_build_query($params);
                    ?>
                    <div id="categories-5" class="sidebar-widget widget_categories">
                        <h3 class="sidebar-header">Share</h3>
                        <ul>
                            <li class="cat-item"><a class="noexicon social-sharing" href="{{ portal_url('page/share/facebook?'.$params) }}" target="_blank"><i class="fa fa-facebook"></i> Facebook</a></li>
                            <li class="cat-item"><a class="noexicon social-sharing" href="{{ portal_url('page/share/twitter/?'.$params) }}" target="_blank"><i class="fa fa-twitter"></i> Twitter</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div id="categories-4" class="sidebar-widget  widget_categories">
                        <h3 class="sidebar-header">External Resources</h3>
                        <ul>
                            <li class="cat-item"> <a href="http://www.ands.org.au/" title="" target="_blank">ANDS Website</a> </li>
                            <li class="cat-item"> <a href="http://developers.ands.org.au" title="" target="_blank">Developers</a> </li>
                            <li class="cat-item"> <a href="{{base_url('registry/')}}" title="">ANDS Online Services</a> </li>
                            @if(isset($ro) && $ro->core['id'])
                                <li class="cat-item"> <a href="{{base_url('registry/registry_object/view/')}}/<?=$this->ro->id?>" title="">Registry View</a> </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript" defer src="https://jira.ands.org.au/s/d41d8cd98f00b204e9800998ecf8427e/en_AUc8oc9c-1988229788/6265/77/1.4.7/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=d9610dcf"></script>

    <script>
        _.contains = _.includes;
    </script>

    @if(isset($lib))
        @foreach($lib as $l)
            @if($l=='jquery-ui')
                <script type="text/javascript" defer src="{{asset_url('vendor/jquery-ui/jquery-ui.js', 'core')}}"></script>
            @elseif($l=='dynatree')
                <script type="text/javascript" defer src="{{asset_url('vendor/dynatree/dist/jquery.dynatree.js', 'core')}}"></script>
            @elseif($l=='textAngular')
                <link rel='stylesheet' href="{{asset_url('vendor/textAngular/src/textAngular.css', 'core')}}">
                <script defer src="{{asset_url('vendor/textAngular/dist/textAngular-rangy.min.js', 'core')}}"></script>
                <script defer src="{{asset_url('vendor/textAngular/dist/textAngular-sanitize.min.js', 'core')}}"></script>
                <script defer src="{{asset_url('vendor/textAngular/dist/textAngular.min.js', 'core')}}"></script>
            @elseif($l=='colorbox')
                <script defer src="{{asset_url('vendor/colorbox/jquery.colorbox-min.js', 'core')}}"></script>
            @elseif($l=='mustache')
                <script defer src="{{asset_url('vendor/mustache/mustache.min.js', 'core')}}"></script>
            @elseif($l=='map')
                <script defer="text/javascript" async src="https://maps.googleapis.com/maps/api/js?{{ \ANDS\Util\Config::get('app.google_api_key') ? 'key='.\ANDS\Util\Config::get('app.google_api_key') : ''  }}&libraries=drawing&amp;sensor=false"></script>
            @elseif($l=='ngupload')
                <script defer="text/javascript" async src="{{asset_url('vendor/ng-file-upload/angular-file-upload-all.min.js','core')}}"></script>
            @endif
        @endforeach
    @endif

    @if(isset($scripts))
        @foreach($scripts as $script)
            <script src="{{asset_url('js/'.$script.'.js')}}"></script>
        @endforeach
    @endif

</footer>