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
          * API No. 13
          * API Name : 넷플릭스 오리지널 컨텐츠 조회 API
          * 마지막 수정 날짜 : 20.07.06
        */
        case "getNfOriginal":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileId = $vars["profileId"];

            if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";

            } else {
                $res->result = getNfOriginal();
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "넷플릭스 오리지널 컨텐츠 조회 성공";

            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
          * API No. 14
          * API Name : 인기순위 순으로 조회 API
          * 마지막 수정 날짜 : 20.07.06
        */
        case "getTop10":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileId = $vars["profileId"];

            if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";

            } else {
                $res->result = getTop10();
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "넷플릭스 오리지널 컨텐츠 조회 성공";

            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 15
         * API Name : 유저별 추천 컨텐츠 조회 API
         * 마지막 수정 날짜 : 20.07.06
        */
        case "getRecommend":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileId = $vars["profileId"];

            if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";

            } else {

                // 내가 찜한 컨텐츠의 상위 3개 장르 가져오기
                $genres = explode(',' , getHeartContentsGenreByProfileId($profileId));

                // 겹치는 장르 높은 순으로 비슷한 콘텐츠 추천
                $res-> recommendContents = getRecommendContents($genres);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "조회 성공";

            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
