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
         * API No. 12
         * API Name : 컨텐츠 상세 정보 조회 API
         * 마지막 수정 날짜 : 20.07.05
        */
        case "getDetails":
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

            } else {

                // 검색어로 유입 시 조회수 증가
                if($req->searchStatus=='Y'){
                    addSearchCnt($contentsId);
                }

                // 조회 수 증가
                if(isExistClick($profileId,$contentsId)) {
                    addClickCnt($profileId,$contentsId);
                }
                else {
                    insertClick($profileId,$contentsId);
                }

                // 컨텐츠 정보 가져오기
                $res->result->contentsInfo = getContentsDetail($contentsId);

                // 장르 가져오기
                $genres = explode(',' , getContentsDetail($contentsId)["genres"]);

                // 찜한 컨텐츠 상태 불러오기
                if(isExistHeart($profileId,$contentsId)) $res->result->heartStatus = 'Y';
                else $res->result->heartStatus = 'N';

                // 평가 컨텐츠 상태 불러오기
                if(getEvaluationStatus($profileId,$contentsId)!=null){
                    $res->result->evaluationStatus = getEvaluationStatus($profileId,$contentsId);
                } else {
                    $res->result->evaluationStatus = 'N';
                }

                // 겹치는 장르 높은 순으로 비슷한 콘텐츠 추천
                $res-> similarContents = getSimilarContents($genres,$contentsId);
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
