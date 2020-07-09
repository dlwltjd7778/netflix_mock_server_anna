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
         * API No. 5
         * API Name : 내가 찜한 컨텐츠 추가 및 삭제 API
         * 마지막 수정 날짜 : 20.07.04
        */
        case "heart":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileId = $vars["profileId"];
            $contentsId = $vars["contentsId"];

            if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";

            } else if(!(isExistContentsId($contentsId))){
                $res->isSuccess = FALSE;
                $res->code = 230;
                $res->message = "존재하지 않는 컨텐츠Id";

            } else if(!(isExistHeart($profileId,$contentsId))){
                insertHeart($profileId, $contentsId);
                $res->result->profileId = $profileId;
                $res->result->contentsId = $contentsId;
                $res->result->heartStatus = 'activated';

                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "찜 추가 성공";

            } else if(isExistHeart($profileId,$contentsId)){
                deleteHeart($profileId, $contentsId);
                $res->result->profileId = $profileId;
                $res->result->contentsId = $contentsId;
                $res->result->heartStatus = 'deleted';

                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "찜 삭제 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 6
         * API Name : 내가 찜한 컨텐츠 조회 API
         * 마지막 수정 날짜 : 20.07.04
        */
        case "getHeart":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileId = $vars["profileId"];

            if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";

            } else {

                if(getHearts($profileId)==null){
                    $res->result->profileId = $profileId;
                    $res->result = -1;

                } else {
                    $res->result->profileId = $profileId;
                    $res->result = getHearts($profileId);

                }
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
