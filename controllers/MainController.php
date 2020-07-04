<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
         * API No. 0
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 19.04.25
         */
        case "validateJwt":
            // jwt 유효성 검사

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 333;
                $res->message = "유효하지 않은 토큰입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            http_response_code(200);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 9
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 20.07.01
         */
        case "createJwt":
            // jwt 유효성 검사
            http_response_code(200);

            if(!isExistEmail($req->email)){
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "존재하지 않는 이메일입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            if(!isValidUser($req->email, $req->pw)){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "잘못된 패스워드 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }

            //페이로드에 맞게 다시 설정 요함
            $jwt = getJWToken($req->email, $req->pw, JWT_SECRET_KEY);
            $res->jwt = $jwt;
            $res->userId = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "로그인 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
