<?php

include_once "config.php";
include_once "lib.php";

if(isset($_GET["page"])){
    if(mth::notInt($_GET["page"])){
        die("非法页码，请检查");
    }
    $pageId=$_GET["page"];
}else{$pageId=1;}

try {  
     $connection=db::startConn($dbtype,$dbhost,$dbname,$dbuser,$dbpass);
        //初始化一个PDO对象，就是创建了数据库连接对象$pdo  
     $pdo = null;  
 } catch (PDOException $e) {  
      die ("Error!: " . $e->getMessage() . "<br/>");  
 }


//如果有postid就输出postid指定的日志
if(isset($_GET["post"])){
    if(mth::notInt($_GET["post"])){die("非法日志ID，请检查");}
    $postid=$_GET["post"];
    $result["info"]=db::singleQuery($connection,"select title,createdate from entry where postid=?",$postid);
    $result["content"]=db::singleQuery($connection,"select url from imgs where postid=?",$postid);
    echo(json_encode($result));
//以下计算总页数和当前页码
}else if(isset($_GET["totalpage"])){
    //获取日志总数量
    $_totalEntry=$connection->query('SELECT COUNT(*) FROM entry;');
    $_totalEntry=$_totalEntry->fetchAll();
    $totalEntry=$_totalEntry[0][0];

//计算总页数
    if($totalEntry){
        if($totalEntry<$entryOfEveryPage){
            $totalPage=1;
        }else if($totalEntry % $entryOfEveryPage){
            $totalPage=(int)($totalEntry / $entryOfEveryPage) + 1;
        }else{
            $totalPage=$totalEntry / $entryOfEveryPage;
        }};
    $page["totalpage"]=$totalPage;
    $page["currpage"]=$pageId;
    echo(json_encode($page));
}else{
//获取所有标签

    $tags=$connection->query("SELECT NAME FROM meta WHERE description IS NOT NULL ORDER BY ord;");
    $tags->setFetchMode(PDO::FETCH_ASSOC);//所有标签的数组
    $tags=$tags->fetchAll();



//输出本页日志信息和封面图片
    $sql="select postid,title,createdate from entry order by postid desc limit ?,?;";
    $_currEntrys=$connection->prepare($sql);
    $_currEntrys->bindValue(1,($pageId-1)*$entryOfEveryPage,PDO::PARAM_INT);
    $_currEntrys->bindValue(2,$entryOfEveryPage,PDO::PARAM_INT);
    $_currEntrys->execute();
    $currEntrys=$_currEntrys->fetchAll(PDO::FETCH_NAMED);
    $content_sql="select url from imgs where postid=? and cover=1";
    $content=$connection->prepare($content_sql);
    $output=array();
    $output["status"]=200;
    foreach($currEntrys as $row){
        $postid=$row["postid"];
        $content->bindValue(1,$postid,PDO::PARAM_INT);
        $content->execute();
        $result=$content->fetchAll(PDO::FETCH_NAMED);
        $output[$row["postid"]]=array();
       $output[$row["postid"]][]=$row;
        foreach($result as $c_row){
            $output[$row["postid"]][]=$c_row;
        }
    }
    echo(json_encode($output));
}