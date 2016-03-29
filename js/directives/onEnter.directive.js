(function() {
	
	'use strict';
	
	angular
		.module('devHelper')
		.directive('onKeyEnter', onKeyEnter);
	
	onKeyEnter.$inject = [];
	
	function onKeyEnter() {
		
		var directive = {
			restrict : 'A',
      scope : {
	      onKeyEnterCallback : '&onKeyEnterCallback'
      },
			link : link
		};
		
		return directive;
		
		function link(scope, element, attrs) {
			
			console.log(scope);
			
      element.bind("keydown keypress", function(event) {
	      
	      if(event.which === 13) {
          event.preventDefault();
		      console.log('enter');
		      scope.onKeyEnterCallback();
		      
	      }
	      
      });
			
		}
		
	}
	
})();