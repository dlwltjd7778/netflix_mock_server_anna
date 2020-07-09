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
         * API No. 11
         * API Name : 평가 등록 및 삭제 API
         * 마지막 수정 날짜 : 20.07.04
        */
        case "evalInsert":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileId = $vars["profileId"];
            $contentsId = $vars["contentsId"];
            $choice = $req->choice;

            if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";

            } else if(!(isExistContentsId($contentsId))){
                $res->isSuccess = FALSE;
                $res->code = 230;
                $res->message = "존재하지 않는 컨텐츠Id";

            } else if(isExistEvaluation($profileId,$contentsId)){
                deleteEvaluation($profileId, $contentsId);
                $res->result->profileId = $profileId;
                $res->result->contentsId = $contentsId;
                $res->result->evalStatus = 'deleted';

                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "평가 삭제 성공";
            } else if(!($choice=='G'||$choice=='B')){
                $res->isSuccess = FALSE;
                $res->code = 240;
                $res->message = "choice는 G, B 중 한가지 가능";

            } else if(!(isExistEvaluation($profileId,$contentsId))){
                insertEvaluation($profileId, $contentsId, $choice);
                $res->result->profileId = $profileId;
                $res->result->contentsId = $contentsId;
                $res->result->evalStatus = 'activated';
                $res->result->choice = $choice;

                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "평가 추가 성공";

            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
