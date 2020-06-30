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
         * API No. 7
         * API Name : 회원가입 API
         * 마지막 수정 날짜 : 20.06.30
         */
        case "insertUser":
            http_response_code(200);

            if($req->email==null || $req->pw==null){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "null값 존재";
            } else if(isExistEmail($req->email)){
                $res->isSuccess = FALSE;
                $res->code = 220;
                $res->message = "존재하는 이메일";
            } else if(!isValidEmail($req->email)){
                $res->isSuccess = FALSE;
                $res->code = 230;
                $res->message = "잘못된 이메일 형식";
            } else if(!isValidPw($req->pw)){
                $res->isSuccess = FALSE;
                $res->code = 240;
                $res->message = "비밀번호는 4~60자리만 가능, 특수문자 불가능";

            } else{
                insertUser($req->email, $req->pw);

                $res->userId = getUserIdbyEmail($req->email);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "회원가입 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testDetail":
            http_response_code(200);
            $res->result = testDetail($vars["testNo"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testPost":
            http_response_code(200);
            $res->result = testPost($req->name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
