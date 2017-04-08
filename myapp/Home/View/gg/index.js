var app = angular.module('myapp', ['ngRoute','me-lazyimg']);
//检测ng-repeat是否完成
//app.directive('onFinishRenderFilters', function ($timeout) {
//    return {
//        restrict: 'A'
//        , link: function (scope, element, attr) {
//            if (scope.$last === true) {
//                $timeout(function () {
//                    scope.$emit('ngRepeatFinished');
//                })
//            }
//        }
//    }
//})





//ng-repeat执行完成判断
app.directive('repeatFinish', function () {
    return {
        link: function (scope, element, attr) {
            if (scope.$last == true) {
                scope.$eval(attr.repeatFinish);
                var cb = {
                    onscroll: function () {
                        masonry()
                        window.removeEventListener('scroll', cb.onscroll, false);
                        setTimeout(function () {
                            window.addEventListener('scroll', cb.onscroll, false);
                        }, 1500)
                    }
                };
                window.addEventListener('scroll', cb.onscroll, false);
            }
        }
    }
})

//瀑布流和picbox插件
function masonry() {
    if (document.getElementById('mainbox')) {
        $('#mainbox').masonry({
            itemSelector: '.entry'
            , columnWidth: 50
        });
    } else {
        $('.entrycontent').masonry({
            itemSelector: '.entryimgs'
            , columnWidth: 50
        });
        if (!/android|iphone|ipod|series60|symbian|windows ce|blackberry/i.test(navigator.userAgent)) {
            jQuery(function ($) {
                $("a[rel^='lightbox']").picbox({ /* Put custom options here */ }, null, function (el) {
                    return (this == el) || ((this.rel.length > 8) && (this.rel == el.rel));
                });
            });
        }
    }
}

//主页控制器
app.controller('mycontroller', function ($scope, $routeParams, $http) {
    if ($routeParams.pageid) {
        $scope.currpage=$routeParams.pageid;
        getData('index.php?page=' + $routeParams.pageid);
    } else {
        getData('index.php');
    };
    

    $http.get('index.php?totalpage=1').success(function (data) {
        $scope.pageinfo = data;
        $scope.pagesequence = [];
        for (var i = 0; i < $scope.pageinfo.totalpage; i++) {
            $scope.pagesequence.push(i);
        }
    });

    function getData(url) {
        $http.get(url).success(function (data) {
            if (data.status == 200) {
                delete(data.status);
            }
            $scope.data = data;
            console.info(data);
        });
    };
    
});


//日志页控制器
app.controller('entrycontroller', ['$scope', '$routeParams', '$http', function ($scope, $routeParams, $http) {
    $http.get('index.php?post=' + $routeParams.postid).success(function (data) {
        if (data.status == 200) {
            console.info("请求post数据返回正常");
            delete(data.status);
        }
        $scope.data = data;
        var cb = {
            onscroll: function () {
                $('.entrycontent').masonry({
                    itemSelector: '.entryimgs'
                    , columnWidth: 50
                });
                window.removeEventListener('scroll', cb.onscroll, false);
                setTimeout(function () {
                    window.addEventListener('scroll', cb.onscroll, false);
                }, 1000)
            }
        };
        window.addEventListener('scroll', cb.onscroll, false);

    });
}])

//路由控制
app.config(['$routeProvider', function ($routeProvider) {
    $routeProvider.when('/post/:postid', {
            templateUrl: 'index-entry.html'
            , controller: 'entrycontroller'
        })
        .when('/page/:pageid', {
            templateUrl: 'index-index.html'
            , controller: 'mycontroller'
        })
        .otherwise({
            templateUrl: 'index-index.html'
            , controller: 'mycontroller'
        })
}]);