(function() {
	
	'use strict';
	
	angular
		.module('devHelper.search')
		.controller('SearchController', SearchController)
		.controller('DialogController', DialogController)
		.filter('dateRange', DateRangeFilter);
		
	SearchController.$inject = ['$scope', '$rootScope', '$location', '$http', '$mdDialog', 'WS_URL', 'dateRangeFilter'];
	
	function SearchController($scope, $rootScope, $location, $http, $mdDialog, WS_URL, dateRangeFilter) {
		
		var sc = this;
		
		sc.inizialize        = inizialize;
		sc.getData           = getData;
		sc.showDialog        = showDialog;
		sc.querySearch       = querySearch;
		sc.showGlobal        = showGlobal;
		sc.makeItemForAction = makeItemForAction;
		sc.filterByDate      = filterByDate;
		sc.filterOnlyFree    = filterOnlyFree;
		sc.filterOnlyItalian = filterOnlyItalian;
		
		sc.inizialize();
		
		return sc;
		
		function inizialize() {
			
			if(!$rootScope.inputKeyword)
				$location.path('/home');
			else {
				
				$scope.search       = $rootScope.search;
				$scope.inputKeyword = $rootScope.inputKeyword;
				$scope.selectedType = $rootScope.selectedType;
				$scope.types        = $rootScope.types;
				
				$scope.types[$scope.selectedType].selected = true;
				
				angular.forEach($scope.types, function(value, key) {
					value.callbackUrl = WS_URL[key];
				});
				
				$scope.types.videotutorials.actions = {
					global : {
						
					},
					single : {
						getBooksBySameAuthor : {
							title : 'Get Books By',
							titleFields : [
								'author'
							],
							requiredField : 'author',
							callbackKw : {
								key : 'author',
								value : 'author'
							},
							callbackUrls : [
								WS_URL['books']
							],
							dialogTitle : 'Books By',
							dialogTitleFields : [
								'author'
							]
						}
					}
				};
				
				$scope.types.books.actions = {
					global : {
						
					},
					single : {
						getPrices : {
							title : 'Get Prices',
							titleFields : [
								
							],
							requiredField : 'isbn',
							callbackKw : {
								key : 'kw',
								value : 'isbn'
							},
							callbackUrls : [
								WS_URL['book_price']
							],
							dialogTitle : 'Prices of',
							dialogTitleFields : [
								'title'
							]
						}
					}
				};
				
				$scope.types.events.actions = {
					global : {
						getEventsByCity : {
							title : 'Search Events in Your City',
							titleFields : [
							],
							requiredField : '',
							callbackKw : {
								key : 'city',
								value : 'city'
							},
							callbackUrls : [
								WS_URL['events']
							],
							dialogTitle : 'Events in',
							dialogTitleFields : [
								'city'
							]
						},
						filterEventsByDate : {
							title : 'Filter Events by Date',
							callback : ''
						}
					},
					single : {
						getEventsByCity : {
							title : 'Get Events in',
							titleFields : [
								'venue_city'
							],
							requiredField : 'venue_city',
							callbackKw : {
								key : 'city',
								value : 'venue_city'
							},
							callbackUrls : [
								WS_URL['events']
							],
							dialogTitle : 'Events in',
							dialogTitleFields : [
								'venue_city'
							]
						},
						getGroupsByCity : {
							title : 'Get Groups in',
							titleFields : [
								'venue_city'
							],
							requiredField : 'venue_city',
							callbackKw : {
								key : 'city',
								value : 'venue_city'
							},
							callbackUrls : [
								WS_URL['groups']
							],
							dialogTitle : 'Groups in',
							dialogTitleFields : [
								'venue_city'
							]
						}
					}
				};
				
				$scope.types.groups.actions = {
					global : {
						getGroupsByCity : {
							title : 'Search Groups in Your City',
							titleFields : [
							],
							requiredField : '',
							callbackKw : {
								key : 'city',
								value : 'city'
							},
							callbackUrls : [
								WS_URL['groups']
							],
							dialogTitle : 'Groups in',
							dialogTitleFields : [
								'city'
							]
						}
					},
					single : {
						getGroupsByCity : {
							title : 'Get Group in',
							titleFields : [
								'city'
							],
							requiredField : 'city',
							callbackKw : {
								key : 'city',
								value : 'city'
							},
							callbackUrls : [
								WS_URL['groups']
							],
							dialogTitle : 'Groups in',
							dialogTitleFields : [
								'city'
							]
						},
						getEventsByCity : {
							title : 'Get Event in',
							titleFields : [
								'city'
							],
							requiredField : 'city',
							callbackKw : {
								key : 'city',
								value : 'city'
							},
							callbackUrls : [
								WS_URL['events']
							],
							dialogTitle : 'Events in',
							dialogTitleFields : [
								'city'
							]
						}
					}
				};
								
			}
											
		}
		
		function getData(key, type) {
			
			var keyword = $scope.inputKeyword.toLowerCase();
			$scope.selectedType = key;
						
			if( (!type.data && !type.loading) || ( type.keyword != keyword ) ) {
				
				type.data           = null;
				type.loading        = true;
				type.keyword        = $scope.inputKeyword.toLowerCase();
				
				$http.get(
					type.callbackUrl,
					{
						params : {
							kw : type.keyword
						}
					}
				).then(function(res) {
					
					type.data    = res.data.data;
					type.allData = res.data.data;
					type.loading = false;
					
					if(key == 'tutorials') {
						type.data.forEach(function(item) {
							if(item.lang == 'IT')
								item.img = 'media/logo_html.png';
							else	
								item.img = 'media/logo_tutorialspoint.png';
						});
					}
					
				});
				
			}
			
		}
		
		function showDialog(event, key, item, action) {
			
			var title = action.dialogTitle;
			action.dialogTitleFields.forEach(function(field) {
				title = title + ' <strong>"' + item[field] + '"</strong>';
			});
						
	    $mdDialog.show({
	      controller 					: DialogController,
	      controllerAs 				: 'DialogCtrl',
	      templateUrl 				: 'views/dialog.html',
	      parent 							: angular.element(document.body),
	      targetEvent 				: event,
	      clickOutsideToClose :true,
	      locals : {
		      key 	: key,
		      title : title,
		      item 	: item,
		      kw 		: action.callbackKw,
		      urls 	: action.callbackUrls
	      }
	    });
	    
	  };
	  
	  function querySearch (query) {
      
      var results = query ? $scope.search.filter( createFilterFor(query) ) : $scope.search, deferred;
      return results;
      
    }
		
		function createFilterFor(query) {
      var lowercaseQuery = angular.lowercase(query);
      return function filterFn(state) {
	      var lowercaseState = angular.lowercase(state);
        return (lowercaseState.indexOf(lowercaseQuery) === 0);
      };
    }
    
    function showGlobal(actionKey) {
	    $scope[actionKey] = !$scope[actionKey];
    }
    
    function makeItemForAction(action, text) {
	    action.item = {};
	    action.item[action.callbackKw.value] = text;
    }
    
    function filterByDate(filterStartDate, filterEndDate) {
	    
		  var startDate = Date.parse(filterStartDate);
		  var endDate   = Date.parse(filterEndDate);
		  
	    if( !isNaN(startDate) || !isNaN(endDate) ) {
		    
		    var data = $scope.types[$scope.selectedType].data;
		    		    
		    $scope.types[$scope.selectedType].data = dateRangeFilter(data, startDate, endDate);
		    
	    }
	    else {
		    
		    console.log('else');
		    
		    $scope.types[$scope.selectedType].data = $scope.types[$scope.selectedType].allData;
		    
	    }
	    	    
    }
    
    function filterOnlyFree(filterOnlyFree) {
	    console.log('ff');
	    console.log(filterOnlyFree);
    }
    
    function filterOnlyItalian(filterOnlyItalian) {
	    console.log('fit');
	    console.log(filterOnlyItalian);
	    
    }
		
	}
	
	DialogController.$inject = ['$scope', 'key', 'title', 'item', 'kw', 'urls', '$rootScope', '$http', '$q', '$mdDialog'];
	
	function DialogController($scope, key, title, item, kw, urls, $rootScope, $http, $q, $mdDialog) {
		
		var dc = this;
		
		dc.inizialize  = inizialize;
		dc.getData     = getData;
		dc.closeDialog = closeDialog;
		
		dc.inizialize();
		
		return dc;
		
		function inizialize() {
			
			$scope.key   = key;
			$scope.title = title;
			$scope.item  = item;
			$scope.kw    = kw;
			$scope.urls  = urls;
			
			dc.getData();
						
		}
		
		function getData() {
				
			$scope.loading = true;
			
			var promises = [];
			var data = [];
			
			var params = {};
			params[$scope.kw.key] = $scope.item[$scope.kw.value].toLowerCase();
			
			$scope.urls.forEach(function(url) {
				
				promises.push(
					$http.get(
						url,
						{
							params : params
						}
					).then(function(res) {
						
						var res = res.data.data;
						
						if($scope.key == 'books') {

							res.forEach(function(item) {
								
								if(item.seller == 'Libreria Universitaria')
									item.img = 'media/logo_libuni.jpg';
								else if(item.seller == 'Amazon')
									item.img = 'media/logo_amazon.png';
								
							});
							
						}
						
						data = data.concat(res);
						
					})
				);
				
			});
			
			$q.all(promises).then(function() {
				$scope.data = data;
				$scope.loading = false;
			});
						
		}
		
		function closeDialog() {
			$mdDialog.hide();
		}
		
	}
	
	function DateRangeFilter() {
		
		return function(data, filterStartDate, filterEndDate) {
			
			console.log(filterStartDate);
			console.log(filterEndDate);
			
			var results = [];
			
			data.forEach(function(item) {
				
				var itemStartDate = new Date(item.date_start*1000).getTime();
				var itemEndDate   = new Date(item.date_end*1000).getTime();
				
				if( isNaN(filterStartDate) ) {
					
					console.log('1');
					
					if( (itemEndDate <= filterEndDate) )
						results.push(item);
					
				}
				else if( isNaN(filterEndDate) ) {
					
					console.log('2');
					if( (itemStartDate >= filterStartDate) )
						results.push(item);
					
				}
				else {
					
					console.log('3');
					if( (itemStartDate >= filterStartDate) && ( (itemEndDate >= filterStartDate) && (itemEndDate <= filterEndDate) )  )
						results.push(item);
					
				}

				
			});
			
			return results;
			
		}
		
	}
	
})();