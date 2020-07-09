<?php
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    $r->addRoute('GET', '/test', ['IndexController', 'test']);
    $r->addRoute('GET', '/test/{testNo}', ['IndexController', 'testDetail']);


    // 프로필 관련
    $r->addRoute('GET', '/profiles', ['ProfileController', 'getProfile']); // API No. 1
    $r->addRoute('POST', '/profiles', ['ProfileController', 'insertProfile']); // API No. 2
    $r->addRoute('PATCH', '/profiles/{profileId}', ['ProfileController', 'updateProfile']); // API No. 3
    $r->addRoute('DELETE', '/profiles/{profileId}', ['ProfileController', 'deleteProfile']); // API No. 4
    $r->addRoute('GET', '/profiles/images', ['ProfileController', 'getProfilesImg']); // API No. 10

    // 찜 관련
    $r->addRoute('POST', '/profiles/{profileId}/contents/{contentsId}/heart', ['HeartController', 'heart']); // API No. 5
    $r->addRoute('GET', '/profiles/{profileId}/contents/hearts', ['HeartController', 'getHeart']); // API No. 6

    // 회원 관련
    $r->addRoute('POST', '/user', ['UserController', 'insertUser']); // API No. 7
    $r->addRoute('PATCH', '/user/info', ['UserController', 'insertUserInfo']); // API No. 8
    $r->addRoute('POST', '/login', ['MainController', 'createJwt']); // API No. 9

    // 평가 관련
    $r->addRoute('POST', '/profiles/{profileId}/contents/{contentsId}/eval', ['EvaluateController', 'evalInsert']); // API No. 11

    // 홈화면
    $r->addRoute('GET', '/profiles/{profileId}/contents/netflix/original', ['HomeController', 'getNfOriginal']); // API No. 13
    $r->addRoute('GET', '/profiles/{profileId}/contents/top10', ['HomeController', 'getTop10']); // API No. 14
    $r->addRoute('GET', '/profiles/{profileId}/contents/recommend', ['HomeController', 'getRecommend']); // API No. 15

    // 검색
    $r->addRoute('GET', '/profiles/{profileId}/contents/search', ['SearchController', 'searchContents']); // API No. 16
    $r->addRoute('GET', '/profiles/{profileId}/contents/popular/search', ['SearchController', 'getPopularSearchContents']); // API No. 17


    // 상세 보기
    $r->addRoute('GET', '/profiles/{profileId}/contents/{contentsId}', ['DetailController', 'getDetails']); // API No. 12




    $r->addRoute('GET', '/jwt', ['MainController', 'validateJwt']);

    $r->addRoute('GET', '/d', ['IndexController', 'dbInsert']); // movie 데이터 삽입

//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            case 'ProfileController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/ProfileController.php';
                break;
            case 'HeartController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/HeartController.php';
                break;
            case 'UserController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/UserController.php';
                break;
            case 'EvaluateController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/EvaluateController.php';
                break;
            case 'HomeController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/HomeController.php';
                break;
            case 'SearchController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/SearchController.php';
                break;
             case 'DetailController':
                 $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                 require './controllers/DetailController.php';
                 break;
            /* case 'SearchController':
                 $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                 require './controllers/SearchController.php';
                 break;
             case 'ReviewController':
                 $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                 require './controllers/ReviewController.php';
                 break;
             case 'ElementController':
                 $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                 require './controllers/ElementController.php';
                 break;
             case 'AskFAQController':
                 $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                 require './controllers/AskFAQController.php';
                 break;*/
        }

        break;
}
