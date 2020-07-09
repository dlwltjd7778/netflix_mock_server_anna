<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;

   

        /*
         * API No. 16
         * API Name : 제목으로 검색 API
         * 마지막 수정 날짜 : 20.07.07
        */
        case "searchContents":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileId = $vars["profileId"];
            $keyword = $_GET["searchKeyword"];

            if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";

            } else if($keyword==null){
                $res->isSuccess = FALSE;
                $res->code = 230;
                $res->message = "쿼리스트링 필요(searchKeyword)";

            } else {

                if(getContentsByKeyword($keyword)==null){
                    $res->isSuccess = FALSE;
                    $res->code = 240;
                    $res->message = "검색어와 일치하는 결과가 없습니다";
                } else{
                    $res-> result = getContentsByKeyword($keyword);
                    $res-> searchStatus = 'Y';
                    $res->isSuccess = TRUE;
                    $res->code = 100;
                    $res->message = "검색어로 조회 성공";
                }
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * API No. 17
         * API Name : 인기 검색 컨텐츠 조회 API
         * 마지막 수정 날짜 : 20.07.07
        */
        case "getPopularSearchContents":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileId = $vars["profileId"];

            if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";

            } else {
                $res-> result = getPopularSearchContents();
                $res-> searchStatus = 'Y';
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "인기 검색 컨텐츠 조회 성공";

            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
