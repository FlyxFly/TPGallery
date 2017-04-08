<?php



class db{

    public static function startConn($type,$host,$name,$user,$pass){
        try{
            $dsn=$type.':host='.$host.';dbname='.$name;
            $conn=new PDO($dsn,$user,$pass);
            $conn->query("set names utf8");
        }catch(PDOException $e){
            echo $e;
        }
        return $conn;
    }

    public static function singleQuery($dbObj,$sql,$postId){
        $query=$dbObj->prepare($sql);
        $query->bindValue(1,$postId,PDO::PARAM_INT);
        $query->execute();
        $result=$query->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}


class mth{
    
    public static function notInt($param){
        $param=(int)$param;
        return ($param+2==2);
    }
}