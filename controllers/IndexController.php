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

                //토큰 발급
                $jwt = getJWToken($req->email, $req->pw, JWT_SECRET_KEY);
                $res->jwt = $jwt;
                $res->userId = getDataByJWToken($jwt, JWT_SECRET_KEY)->userIdx;
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "회원가입 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 8
         * API Name : 회원 정보 등록 API
         * 마지막 수정 날짜 : 20.06.30
        */
        case "insertUserInfo":
            http_response_code(200);
            $userId = getUserIdxByToken();

            if($req->cardNum==null || $req->expYear==null || $req->expMonth==null || $req->name==null ||
                $req->bYear==null || $req->bMonth==null || $req->bDay==null){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "null값 존재";
            } else if(!($req->expMonth>=1 && $req->expMonth<=12)){
                $res->isSuccess = FALSE;
                $res->code = 230;
                $res->message = "잘못된 expMonth (1~12) 숫자만 가능";
            } else if(!($req->expYear>=date("Y") && $req->expYear<=date("Y")+25)){
                $res->isSuccess = FALSE;
                $res->code = 231;
                $res->message = "잘못된 expYear";
            } else if($req->expYear==date("Y") && $req->expMonth<date("n")){
                $res->isSuccess = FALSE;
                $res->code = 232;
                $res->message = "잘못된 expMonth(현재날짜보다 이전월 입력)";
            } else if(!isValidCardNum($req->cardNum)){
                $res->isSuccess = FALSE;
                $res->code = 240;
                $res->message = "카드번호는 12~19자리의 숫자";
            } else if(!($req->bYear>=1900 && (date("Y")-$req->bYear>=17))){
                $res->isSuccess = FALSE;
                $res->code = 250;
                $res->message = "잘못된 출생년도";
            } else{
                $birthDay = $req->bYear.".".$req->bMonth.".".$req->bDay;

                insertUserInfo($req->cardNum, $req->expYear, $req->expMonth, $req->name, $birthDay, $userId);

                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "등록 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 10
         * API Name : 결제 등록 API
         * 마지막 수정 날짜 : 20.07.01
        */
        case "insertPayment":
            http_response_code(200);
            $userId = getUserIdxByToken();

            if($req->ticketId==null){
                $res->isSuccess = FALSE;
                $res->code = 210;
                $res->message = "null값 존재";
            } else if(!(isExistTicketId($req->ticketId))){
                $res->isSuccess = FALSE;
                $res->code = 220;
                    $res->message = "존재하지않는 ticketId";
            } else if((isExistPayment($userId))){
                $res->isSuccess = FALSE;
                $res->code = 230;
                $res->message = "이미 이번 달 결제한 회원";
            } else{

                $res->expDate = insertPayment($req->ticketId, $userId);
                updateIsCanceled($userId);

                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "등록 성공";
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
