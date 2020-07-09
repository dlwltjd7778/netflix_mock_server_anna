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
         * API No. 1
         * API Name : 계정 ID에 따른 프로필 조회 API
         * 마지막 수정 날짜 : 20.07.02
        */
        case "getProfile":
            http_response_code(200);
            $userId = getUserIdxByToken();

            if(getProfile($userId)==null) {
                $res->result = -1;
            } else {$res->result = getProfile($userId);
                $res->addProfileAvailable = addProfileAvailable($userId);}
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "프로필 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 2
         * API Name : 계정 ID에 따른 프로필 생성 API
         * 마지막 수정 날짜 : 20.07.04
         */
        case "insertProfile":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileName = $req->profileName;
            $profileImgId = $req->profileImgId;

            if($profileName==null || $profileImgId==null){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "null값 존재";
            } else if(!isExistProfileImgId($profileImgId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필이미지Id";
            } else if(!addProfileAvailable($userId)){
                $res->isSuccess = FALSE;
                $res->code = 230;
                $res->message = "더이상 프로필 추가 불가 or 이용권 구매x";
            } else{
                insertProfile($userId, $profileName, $profileImgId);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "프로필등록 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 3
         * API Name : 계정 ID에 따른 프로필 수정 API
         * 마지막 수정 날짜 : 20.07.04
        */
        case "updateProfile":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileName = $req->profileName;
            $profileImgId = $req->profileImgId;
            $profileId = $vars["profileId"];

            if($profileName==null || $profileImgId==null){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "null값 존재";
            } else if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";
            } else if(!isExistProfileImgId($profileImgId)){
                $res->isSuccess = FALSE;
                $res->code = 230;
                $res->message = "존재하지 않는 프로필이미지Id";
            } else{
                updateProfile($profileName, $profileImgId, $profileId);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "프로필 수정 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 4
         * API Name : 계정 ID에 따른 프로필 삭제 API
         * 마지막 수정 날짜 : 20.07.04
        */
        case "deleteProfile":
            http_response_code(200);
            $userId = getUserIdxByToken();
            $profileId = $vars["profileId"];

            if(!isExistProfile($userId,$profileId)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하지 않는 프로필";
            } else{
                deleteProfile($profileId, $userId);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "프로필 삭제 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        /*
         * API No. 10
         * API Name : 프로필이미지 종류 가져오기 API
         * 마지막 수정 날짜 : 20.07.04
        */
        case "getProfilesImg":
            http_response_code(200);
            $userId = getUserIdxByToken();

            if(getProfilesImgUrl()==null) {
                $res->result = -1;
            } else {
                $res->results->profileName =getProfilesImgName();
                $res->results->details = getProfilesImgUrl();

                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "프로필 조회 성공";
            }

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

      
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
