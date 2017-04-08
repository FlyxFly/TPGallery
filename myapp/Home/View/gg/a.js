var app = angular.module('myapp', ['ngRoute']);

app.directive('onFinishRenderFilters', function ($timeout) {
    return {
        restrict: 'A'
        , link: function (scope, element, attr) {
            if (scope.$last === true) {
                $timeout(function () {
                    scope.$emit('ngRepeatFinished');
                })
            }
        }
    }
})

app.controller('mycontroller', function ($scope, $http) {
    $http.get('index.php').success(function (data) {
        if (data.status == 200) {
            console.info("请求index数据返回正常");
            delete(data.status);
        }
        $scope.data = data;
    });
    $scope.$on('ngRepeatFinished', function (ngRepeatFinishedEvent) {
                $('.main').masonry({
                    itemSelector:'.entry',
                    columnWidth:50
    })

})

app.config(['$routeProvider', function ($routeProvider) {
    $routeProvider.when('/post/:postid', {
            templateUrl: 'a-entry.html'
            , controller: 'entrycontroller'
        })
        .otherwise({
            templateUrl: 'a-index.html'
            , controller: 'mycontroller'
        })
}]);

app.controller('entrycontroller', ['$scope', '$routeParams', '$http', function ($scope, $routeParams, $http) {
    $http.get('index.php?post=' + $routeParams.postid).success(function (data) {
        if (data.status == 200) {
            console.info("请求post数据返回正常");
            delete(data.status);
        }
        $scope.data = data;
    });
     $scope.$on('ngRepeatFinished', function (ngRepeatFinishedEvent) {
                $('.entrybody').masonry({
                    itemSelector:'.entryimgs',
                    columnWidth:50
    })
}])