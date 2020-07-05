<?php

function getTitle($id)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT title FROM contents where id=?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0][title];
}
function getTitleIdx($id)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT titleIdx FROM contents where id=?;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["titleIdx"];
}

//READ
function test()
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Test;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//READ
function testDetail($testNo)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT * FROM Test WHERE no = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$testNo]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

// 회원가입
function insertUser($email, $pw)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO user (email, pw, isCanceled) VALUES (?,?,'Y');";

    $st = $pdo->prepare($query);
    $st->execute([$email, $pw]);

    $st = null;
    $pdo = null;
}

// 프로필 추가
function insertProfile($userId, $name, $profileImgId)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO profile (userId, name, profileImgId) VALUES (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$userId, $name, $profileImgId]);

    $st = null;
    $pdo = null;
}

// 찜 추가
function insertHeart($profileId, $contentsId)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO heart (profileId, contentsId) VALUES (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$profileId, $contentsId]);

    $st = null;
    $pdo = null;
}

// 찜 삭제
function deleteHeart($profileId, $contentsId)
{
    $pdo = pdoSqlConnect();
    $query = "DELETE FROM heart WHERE profileId=? and contentsId=?";

    $st = $pdo->prepare($query);
    $st->execute([$profileId, $contentsId]);

    $st = null;
    $pdo = null;
}

// 평가 추가
function insertEvaluation($profileId, $contentsId, $choice)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO evaluation (profileId, contentsId, choice) VALUES (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$profileId, $contentsId, $choice]);

    $st = null;
    $pdo = null;
}

// 평가 삭제
function deleteEvaluation($profileId, $contentsId)
{
    $pdo = pdoSqlConnect();
    $query = "DELETE FROM evaluation WHERE profileId=? and contentsId=?";

    $st = $pdo->prepare($query);
    $st->execute([$profileId, $contentsId]);

    $st = null;
    $pdo = null;
}

// 회원 정보 등록
function insertUserInfo($cardNum,$expYear,$expMonth, $name, $birthDay, $userId)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE user SET cardNum=?, expYear=?, expMonth=?, name=?, birth=? WHERE id=?;";

    $st = $pdo->prepare($query);
    $st->execute([$cardNum,$expYear,$expMonth, $name, $birthDay, $userId]);

    $st = null;
    $pdo = null;
}

// 이메일 정규식
function isValidEmail($email){
    $emailPattern = '/^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i';
    return preg_match($emailPattern ,$email);
}

// 비밀번호 정규식
function isValidPw($pw){
    $pwPattern = '/^[a-zA-Z0-9_]{4,60}$/';
    return preg_match($pwPattern ,$pw);
}

// 카드번호 정규식
function isValidCardNum($cardNum){
    $pwPattern = '/^[0-9]{12,19}$/';
    return preg_match($pwPattern ,$cardNum);
}

// 카드번호 정규식
function isValidexpDate($expDate){
    $pwPattern = '/^[0-9]{12,19}$/';
    return preg_match($pwPattern ,$expDate);
}

// 장르 가져오기
function getGenresByContentsId($contentsId){
    $pdo = pdoSqlConnect();
    $query = "select genre from genre where contentsId=?;";


    $st = $pdo->prepare($query);
    $st->execute([$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return $res;
}

// 상세보기에서 비슷한 컨텐츠 가져오기
function getSimilarContents($genres, $contentsId){
    $in_list = empty($genres)?'NULL':"'".join("','", $genres)."'";
    $pdo = pdoSqlConnect();
    $query = "select g.contentsId,c.thumbnailImgUrl from genre g
        inner join contents c
        on c.id = g.contentsId
        where g.genre in({$in_list}) and g.contentsId != ?
        group by g.contentsId
        order by count(g.contentsId) desc, rand()
        limit 6;";


    $st = $pdo->prepare($query);
    $st->execute([$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return $res;
}

// 장르로 컨텐츠 검색
function searchContentsByGenre($genre){
    $pdo = pdoSqlConnect();
    $query = "select id,thumbnailImgUrl,genres from contents where genres LIKE '%".$genre."%';";


    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return $res;
}

// userId 이메일로 찾기
function getUserIdbyEmail($email){
    $pdo = pdoSqlConnect();
    $query = "SELECT id FROM user WHERE email= ?;";


    $st = $pdo->prepare($query);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return $res[0][id];
}

// 존재하는 이메일인지 확인
function isExistEmail($email){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM user WHERE email= ? ) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// 존재하는 userId인지 확인
function isExistUserId($id){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM user WHERE id= ? ) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// 존재하는 ticketId인지 확인
function isExistTicketId($id){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ticket WHERE id= ? ) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// 결제 정보 등록
function insertPayment($userId, $ticketId)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO payment (userId, ticketId, payDate, expDate ) VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))";

    $st = $pdo->prepare($query);
    $st->execute([$userId, $ticketId]);

    $st = null;
    $pdo = null;

    $timestamp = strtotime("+1 months");
    return date("Y-m-d H:i:s", $timestamp);
}

// 존재하는 결제?
function isExistPayment($userId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM payment WHERE userId= ? AND payDate<=NOW() AND expDate>=NOW()) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}
// 존재하는 프로필이미지아이디인지 확인
function isExistProfileImgId($id){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM profileimgurl WHERE id= ? ) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// 존재하는 프로필아이디인지 확인
function isExistProfile($userId,$id){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM profile WHERE userId= ? and id=? and isDeleted='N' ) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$userId,$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// 존재하는 heart인지 확인
function isExistHeart($profileId,$contentsId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM heart WHERE profileId= ? and contentsId=? ) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$profileId,$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// 존재하는 evaluation인지 확인
function isExistEvaluation($profileId,$contentsId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM evaluation WHERE profileId= ? and contentsId=? ) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$profileId,$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// evaluation 상태 확인
function getEvaluationStatus($profileId,$contentsId){
    $pdo = pdoSqlConnect();
    $query = "select choice from evaluation where profileId=? and contentsId = ?;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$profileId,$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return $res[0]["choice"];

}


// 존재하는 contents인지 확인
function isExistContentsId($contentsId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM contents WHERE id= ? and isDeleted='N' ) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// 정기결제여부 y로 등록
function updateIsCanceled($userId)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE user SET isCanceled='N' WHERE id=?;";

    $st = $pdo->prepare($query);
    $st->execute([$userId]);

    $st = null;
    $pdo = null;
}

// 로그인 시
function isValidUser($email, $pw){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM user WHERE email= ? AND pw = ?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$email, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// 프로필 가져오기
function getProfile($id){
    $pdo = pdoSqlConnect();
    $query = "SELECT p.id as profileId, piu.profileImgUrl  from profile p
                    inner join profileimgurl piu
                        on p.profileImgId = piu.id
                where p.userId=? and p.isDeleted='N';";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return $res;

}

// 찜한 컨텐츠 가져오기
function getHearts($profileId){
    $pdo = pdoSqlConnect();
    $query = "SELECT h.contentsId, c.thumbnailImgUrl,c.nfOriginal from heart h
                    inner join contents c
                        on c.id=h.contentsId
                where h.profileId=? and c.isDeleted='N';";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$profileId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return $res;

}

// 컨텐츠 상세정보 가져오기
function getContentsDetail($contentsId){
    $pdo = pdoSqlConnect();
    $query = "select genres, thumbnailImgUrl, year,age,concat(runtime div 60,'시간 ',mod(runtime,60),'분') as runtime,videoUrl,details,actors,directors
                from contents
                where id=? and isDeleted='N';";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return $res[0];

}

// 존재하는 click인지 확인
function isExistClick($profileId,$contentsId){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM click WHERE profileId=? and contentsId=?) AS exist;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$profileId,$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return intval($res[0]["exist"]);

}

// click insert
function insertClick($profileId,$contentsId){
    $pdo = pdoSqlConnect();
    $query = "insert into click (profileId,contentsId) values (?,?);";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$profileId,$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
   // $res = $st->fetchAll();

    $st=null;$pdo = null;

}

// click 수 추가
function addClickCnt($profileId,$contentsId){
    $pdo = pdoSqlConnect();
    $query = "update click set clickCnt = clickCnt+1 where profileId=? and contentsId = ?;";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$profileId,$contentsId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);


    $st=null;$pdo = null;

}

// 프로필 추가 가능 여부
function addProfileAvailable($id){
    $pdo = pdoSqlConnect();
    $query = "
                select
                (SELECT t.sameTimePeople FROM ticket t
                inner join payment p
                on p.ticketId = t.id
                where p.userId=?)
                >
                (SELECT count(p.id) from profile p
                    inner join profileimgurl piu
                        on p.profileImgId = piu.id
                where p.userId=? and p.isDeleted='N') as addProfileAvailable;
                ";


    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([$id,$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return $res[0][addProfileAvailable];

}

// 프로필 수정
function updateProfile($name, $profileImgId, $id)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE profile SET name=?, profileImgId=? where id=?;";
    $st = $pdo->prepare($query);
    $st->execute([$name, $profileImgId, $id]);

    $st = null;
    $pdo = null;
}

// 프로필 삭제
function deleteProfile($id, $userId)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE profile SET isDeleted='Y' where id=? and userId=?;";
    $st = $pdo->prepare($query);
    $st->execute([$id, $userId]);

    $st = null;
    $pdo = null;
}

// contents에 data등록1
function insertMovieData($actors, $thumbnailImgUrl, $titleIdx,$id)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE contents SET actors=?, thumbnailImgUrl=?, titleIdx=?
                where id=?;";
    $st = $pdo->prepare($query);
    $st->execute([$actors, $thumbnailImgUrl, $titleIdx,$id]);

    $st = null;
    $pdo = null;
}

// contents에 data등록2
function insertMovieDetails($details, $titleIdx)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE contents SET details=? where titleIdx=?;";
    $st = $pdo->prepare($query);
    $st->execute([$details, $titleIdx]);

    $st = null;
    $pdo = null;
}

function getProfilesImgName()
{
    $pdo = pdoSqlConnect();
    $query = "select name from profileimgurl;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["name"];
}
function getProfilesImgUrl()
{
    $pdo = pdoSqlConnect();
    $query = "select id as profileImgId, profileImgUrl from profileimgurl;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
