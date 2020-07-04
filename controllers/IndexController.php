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
            } else $res->result = getProfile($userId);
            $res->addProfileAvailable = addProfileAvailable($userId);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "프로필 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 2
         * API Name : 계정 ID에 따른 프로필 생성 API
         * 마지막 수정 날짜 : 20.06.30
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
                $res->message = "더이상 프로필 추가 불가";
            } else{
                insertProfile($userId, $profileName, $profileImgId);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "프로필등록 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
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
                $req->bYear==null || $req->bMonth==null || $req->bDay==null ||$req->ticketId==null){
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
            } else if(!(isExistTicketId($req->ticketId))){
                $res->isSuccess = FALSE;
                $res->code = 260;
                $res->message = "존재하지않는 ticketId";
            } else if((isExistPayment($userId))){
                $res->isSuccess = FALSE;
                $res->code = 270;
                $res->message = "이미 이번 달 결제한 회원";
            }else{
                $birthDay = $req->bYear.".".$req->bMonth.".".$req->bDay;

                insertUserInfo($req->cardNum, $req->expYear, $req->expMonth, $req->name, $birthDay, $userId);
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


        case "dbInsert":
            http_response_code(200);
//            for($i=1;$i<=298;$i++) {
//                $title = urlencode(getTitle($i));
//
//                $curl = curl_init();
//
//                curl_setopt_array($curl, array(
//                    CURLOPT_URL => "https://imdb8.p.rapidapi.com/title/find?q=" . $title . "",
//                    CURLOPT_RETURNTRANSFER => true,
//                    CURLOPT_FOLLOWLOCATION => true,
//                    CURLOPT_ENCODING => "",
//                    CURLOPT_MAXREDIRS => 10,
//                    CURLOPT_TIMEOUT => 30,
//                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                    CURLOPT_CUSTOMREQUEST => "GET",
//                    CURLOPT_HTTPHEADER => array(
//                        "x-rapidapi-host: imdb8.p.rapidapi.com",
//                        "x-rapidapi-key: 7990790a43msh87838b8de3b2d40p1f01b1jsn8d7c94b1134f"
//                    ),
//                ));
//
//                $response = curl_exec($curl);
//                $err = curl_error($curl);
//
//                curl_close($curl);
//
//                if ($err) {
//                    echo "cURL Error #:" . $err;
//                } else {
//                    $jiDung = json_decode($response);
//                    $strTok = explode('/', $jiDung->results[0]->id);
//                    $movieId = $strTok[2];
//
//                    $actor = "";
//                    for ($j = 0; $j < sizeof($jiDung->results[0]->principals); $j++) {
//                        if ($j != sizeof($jiDung->results[0]->principals) - 1) {
//                            $actor = $actor . $jiDung->results[0]->principals[$j]->name . ", ";
//                        } else {
//                            $actor = $actor . $jiDung->results[0]->principals[$j]->name;
//                        }
//
//                    }
//                    echo $actor;
//
//                    $thumbnail = $jiDung->results[0]->image->url;
//
//                    insertMovieData($actor, $thumbnail, $movieId,$i);
//
//                    //  echo json_encode($jiDung->results[0], JSON_NUMERIC_CHECK);
//
//                    //echo $response;
//                }
//
//            }
//            break;
//           // for($i=1;$i<=290;$i++) {
//              //  $titleIdx = urlencode(getTitleIdx($i));
//                $curl = curl_init();
//
//                curl_setopt_array($curl, array(
//                    CURLOPT_URL => "https://imdb8.p.rapidapi.com/title/get-plots?tconst=" . "tt1289403" . "",
//                    CURLOPT_RETURNTRANSFER => true,
//                    CURLOPT_FOLLOWLOCATION => true,
//                    CURLOPT_ENCODING => "",
//                    CURLOPT_MAXREDIRS => 10,
//                    CURLOPT_TIMEOUT => 30,
//                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                    CURLOPT_CUSTOMREQUEST => "GET",
//                    CURLOPT_HTTPHEADER => array(
//                        "x-rapidapi-host: imdb8.p.rapidapi.com",
//                        "x-rapidapi-key: 17b52a9242msh9cb76529088f20dp18a1b6jsn0f08a88867fb"
//                    ),
//                ));
//
//                $response = curl_exec($curl);
//                $err = curl_error($curl);
//
//                curl_close($curl);
//
//                if ($err) {
//                    echo "cURL Error #:" . $err;
//                } else {
//                    $result = json_decode($response);
//                    echo $result->plots[0]->text;
//                    //echo $response;
//                }
//
//                //insertMovieDetails($result->plots[0]->text, getTitleIdx($i));
//          //  }
//            break;



    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
