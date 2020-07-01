<?php

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

// 존재하는 ticketId인지 확인
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
