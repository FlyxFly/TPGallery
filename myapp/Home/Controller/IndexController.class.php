<?php
namespace Home\Controller;
use Think\Controller;
import("ORG\Util\Page");
class IndexController extends Controller {
    private function testIfInt($param){
	    $param=(int)$param;
	    if(($param+2==2)){die("非法操作！已记录IP：".get_client_ip()."。");}
    }

    private function totalpage(){
        $entryDB=M("entry");
        $entryQty=$entryDB->count();
        $entryPerPage=C("entryPerPage");
        if($entryQty<$entryPerPage){
            $totalPage=1;
        }
        if($entryQty % $entryPerPage){
            $totalPage=(int)($entryQty / $entryPerPage) + 1;
        }else{
            $totalPage=$entryQty / $entryPerPage;
        }
        return $totalPage;
    }

    private function getEntry($pid=null,$p=1){
        // echo("getEntry收到的页码：".$p);
        $entryDB=M("entry");
        $imgsDB=M("imgs");
        $metaDB=M("meta");
        $relationshipDB=M("relationship");
        $today=date("Y-m-d h:i:s");
        if($pid==null){
            if(session('user')){
                $whereCondition=array(
                    'createdate'=>array('lt',$today));
            }else{
                $whereCondition=array(
                    'createdate'=>array('lt',$today),'private'=>0);
            }
            $entrys = $entryDB
            ->where($whereCondition)
            ->field(array("postid","title","createdate"))
            ->order("postid desc")
            ->page($p,C("entryPerPage"))
            ->select();
        }else{
            if(session('user')){
                $whereCondition=array(
                    'postid'=>$pid,
                    'createdate'=>array('lt',$today));
            }else{
                $whereCondition=array(
                    'postid'=>$pid,
                    'createdate'=>array('lt',$today),
                    'private'=>0);
            }
            $entrys = $entryDB
            ->field(array("postid","title","createdate"))
            ->where($whereCondition)
            ->order("postid desc")
            ->page($p,C("entryPerPage"))
            ->select();            
        }
        foreach ($entrys as $key => $value) {
            $where=array("postid"=>$value["postid"],"cover"=>1);
            $coverURL=$imgsDB->field("url")->where($where)->select();
            $pid=$value["postid"];
            $entrys[$key]["cover"]=$coverURL[0]["url"];
            $entrys[$key]["entryurl"]=U("Home/Index/post?pid=".$pid);
            $entrys[$key]["imgcount"]=$imgsDB->where("postid=%d",$pid)->count();
        }
        return $entrys;
    }

    public function index($tagid=null){
        $p=$_GET["p"]?$_GET["p"]:1;
    	self::testIfInt($p);
    	$entryDB=M("entry");
    	$imgsDB=M("imgs");
        $metaDB=M("meta");
        $relationshipDB=M("relationship");

        if($tagid==null){
            $entryQty=$entryDB->count();
            $entrys=self::getEntry(null,$p);
            $this->assign("pagetitle","首页");
        }else{
            $thisTagEntryId=$relationshipDB->where("mid = '$tagid'")->select();
            $tagChinese=$metaDB->where("mid = '$tagid'")->select();

            $entryQty=count($thisTagEntryId);
            $entrys=array();
            foreach ($thisTagEntryId as $key => $value) {
                $thisPid=$value['pid'];
                $_entrys=self::getEntry($value['pid']);
                array_push($entrys,$_entrys[0]);
            }
            $this->assign("pagetitle",$tagChinese[0]['name']);
            $this->assign("thisMid",$tagid);
        }
        $this->assign("title",C("title"));
    	$this->assign("data",$entrys);
        // dump($entrys);
        //分页
        $paging= new  \Think\Page($entryQty, C("entryPerPage"));
        $show=$paging->show();
        $this->assign("page",$show);

        //tag
        $tags = $metaDB->where("type= 'category'")->select();
        $this->assign("AllTags",$tags);
        // dump($tags);
    	$this->display("indexHydrogen");      
    }   

    public function post($pid){
        $p=$_GET['p']?$_GET['p']:1;
        self::testIfInt($p);
    	self::testIfInt($pid);
		$entryDB=M("entry");
		$imgsDB=M("imgs");
		$where1=array("postid"=>$pid);
        if(session('user')){
                $$where1=array(
                    'postid'=>$pid);
            }else{
                $where1=array(
                    'postid'=>$pid,),
                    'private'=>0);
            }
        $entryInfo=$entryDB->field(array("title","createdate"))->where($where1)->where("createdate < '%s'",date("Y-m-d h:i:s"))->select();
        //判断日期和pid是否合法
        if($entryInfo){
            $imgUrls=$imgsDB->where($where1)->order("id desc")->page($p,C("imgPerPage"))->select();
            $result=array("info"=>$entryInfo[0],"content"=>$imgUrls);
            //图片分页
            $imgCount=$imgsDB->where($where1)->count();
            $paging= new  \Think\Page($imgCount, C("imgPerPage"));
            $pageShow=$paging->show();
            //分类 
        }else{
            E("页面不存在",404);
        }
		
        $metaDB=M("meta");
        $tags = $metaDB->where("type= 'category'")->select();
        $this->assign("title",C("title"));
        $this->assign("AllTags",$tags);
        $this->assign("page",$pageShow);
		$this->assign("data",$result);
		$this->display("postHydrogen");
		
    	
    }

    // public function tag($tagid){
    //     $tagDB=M("relationship");
    //     $entryDB=M("entry");
    //     $tagPost=$tagDB->where("mid = $tagid")->select();
    //     // dump($tagPost);
    //     $resultArray=array();
    //     foreach ($tagPost as $key => $value) {
    //         $pid=$value["pid"];
    //         $entry=$entryDB->where("postid = $pid")->select();
    //         dump($entry);
    //         // array_push($resultArray, $entry);
            
    //     }
    // }

    public function logout(){
        session("user",null);
        $this->redirect("Home/Index/index");
    }

    public function whoami(){
        dump(session("user"));
    }
}