"use struct";

(function() {
    
  angular.module('zaysoApp', []);
    
  angular.module('zaysoApp').controller('ProjectController',[ '$http','$attrs',function($http,$attrs) {
    
    var project = this;
    
    project.persons = [];
    
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




