"use struct";

(function() {
    
    var app = angular.module('zaysoApp', ['ngResource']);
    
    app.factory('Phone', ['$resource', function($resource){
            
        var url = '/arbiter/tourn' + '/projects/' + 'kicks' + '/persons';
        
        return $resource(url, {}, {
            query: {method:'GET', params:{phoneId:'phones'}, isArray:true}
        });
    }]);    
    app.controller('ProjectController',[ '$http','$attrs','Phone',function($http,$attrs,Phone) {
    
    var project = this;
    
    project.persons = [];
    project.phones = Phone.query();
    
    var url = $attrs.prefix + '/projects/' + $attrs.projectKey + '/persons';
    
    $http.get(url)
      .success(function(data) {
        project.persons = data;
      })
      .error(function(data,status) {
        alert('Failed ' + status);
      });
  }]);

})();




