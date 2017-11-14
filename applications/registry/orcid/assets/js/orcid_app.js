/**
 * ORCID APP angularJS module
 * @author Minh Duc Nguyen <minh.nguyen@ands.org.au>
 */
angular.module('orcid_app', ['portal-filters'])

	//Router
	.config(function($routeProvider){
		$routeProvider
			.when('/', {
				controller:IndexCtrl,
				template: $('#index').html()
			})
	})

	//Factory
	.factory('works', function($http){
		return {
			getWorks: function(orcid_id) {
				return $http.get(api_url + 'registry/orcids/' + orcid_id + '/works')
					.then(function(response) {return response.data})
			},
			importWorks: function(orcid_id, ids) {
				return $http.post(api_url + 'registry/orcids/' + orcid_id + '/works', {ids: ids})
					.then(function(response) {return response.data})
			},
			search: function(filters) {
				return $http.post(base_url+'/services/registry/post_solr_search', {filters:filters}).then(function(response) {return response.data});
			}
		}
	})
;

/**
 * Primary Controller
 * @param  $scope
 * @param factory works
 */
function IndexCtrl($scope, works) {

	//Default Values
	$scope.works = {};
	$scope.to_import = [];
	$scope.import_available = false;
	$scope.filters = {};
	$scope.search_results = {};
	$scope.imported_ids= [];
	$scope.import_stg = 'ready';

	$scope.orcid = {
		id:$('#orcid_id').text()
	};

	//Overwrite the import button to only open the modal if it's not disabled
	$('.import').click(function(e){
		e.preventDefault();
		if(!$(this).hasClass('disabled')){
			$('#myModal').modal();
		}
		return false;
	});

	//Refresh functions refreshes the works, populates the imported_ids 
	$scope.refresh = function (){
		$scope.imported_ids = [];
		works.getWorks($scope.orcid.id).then(function(data){
			$scope.works = data;
			if($scope.works){
				$.each($scope.works, function(){
					if(this.type=='imported' && this.in_orcid){
						$scope.imported_ids.push(this.id);
					}
				});
			}
		});
	};

	//run once
	$scope.refresh();

	/**
	 * Watch Expression on works and search result to updat the import tag
	 * @return {[type]} [description]
	 */
	$scope.$watch('works', function(){
		$scope.review();
	}, true);

	$scope.$watch('search_results', function() {
		$scope.review();
	}, true);

	$scope.review = function(){
		$scope.import_available = false;
		$scope.to_import = [];
		if($scope.works){
			$.each($scope.works, function(){
				if(this.to_import) {
					$scope.to_import.push(this);
					$scope.import_available = true;
				}
			});
		}
		if($scope.search_results && $scope.search_results.docs){
			$.each($scope.search_results.docs, function(){
				if(this.to_import) {
					$scope.to_import.push(this);
					$scope.import_available = true;
				}
			});
		}
		$scope.import_stg = 'ready';
	}

	$scope.alreadyImported = function(id) {
		var importedIDs = $scope.works.filter(function(item) {
			return item.in_orcid;
		}).map(function(item) {
			return item.registry_object_id
		});
		return importedIDs.indexOf(id) >= 0;
	};

	/**
	 * Generic SOLR search for collections
	 * @return search_result
	 */
	$scope.search = function() {
		if($scope.filters.q!=''){
			$scope.filters.rows = 100;
			$scope.filters.class = 'collection';
			works.search($scope.filters).then(function(data){
				$scope.search_results = data.result;
			});
		}
	}

	/**
	 * Import the set of to_import works to ORCID, calling the import_works factory method
	 * Increment import stages from idle->importing->complete, error is a stage
	 */
	$scope.import = function() {
		$scope.import_stg = 'importing';
		var ids = [];
		$.each($scope.to_import, function(){
			ids.push(this.id);
		});
		works.importWorks($scope.orcid.id, ids).then(function(data){
			console.log(data);
			$scope.import_stg = 'complete';
			$scope.refresh();
		});
	}
}