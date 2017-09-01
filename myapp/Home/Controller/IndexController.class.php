<?php
namespace Home\Controller;
use Think\Controller;
import("ORG\Util\Page");
class IndexController extends Controller {
    private $options='';
    public function __construct(){
        parent::__construct();
        $optionsModel = new \Admin\Model\OptionsModel();
        $this->options=$optionsModel->getCachedSysConfig();
        $this->options['entrysperpage']=(int)$this->options['entrysperpage'];
        $this->options['imgsperpage']=(int)$this->options['imgsperpage'];
    }

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
                    'createdate'=>array('lt',$today),'private'=>$this->options['showhiddenentry']);
            }
            $entrys = $entryDB
            ->where($whereCondition)
            ->field(array("postid","title","createdate"))
            ->order("postid desc")
            ->page($p,$this->options['entrysperpage'])
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
                    'private'=>$this->options['showhiddenentry']);
            }
            $entrys = $entryDB
            ->field(array("postid","title","createdate"))
            ->where($whereCondition)
            ->order("postid desc")
            ->page($p,$this->options['entrysperpage'])
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

    public function oldindex($tagid=null,$p=1){
    	self::testIfInt($p);
    	$entryDB=M("entry");
    	$imgsDB=M("imgs");
        $metaDB=M("meta");
        $relationshipDB=M("relationship");
        if($tagid==null){
            if(session('user')){
                $entryQty=$entryDB->count();
            }else{
                $entryQty=$entryDB->where("private=1")->count();
            }
            
            $entrys=self::getEntry(null,$p);
            $this->assign("pagetitle","Home");
        }else{
            if(session('user')){
                $thisTagEntryId=$relationshipDB->where("mid = '$tagid'")->select();
            }else{
                $thisTagEntryId=$relationshipDB->where("mid = '$tagid' and private=0")->select();
            }
            
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
        // dump($this->options);
        //分页
        $paging= new  \Think\Page($entryQty, $this->options['entrysperpage']);
        $show=$paging->show();
        $this->assign("page",$show);

        //tag
        $tags = $metaDB->where("type= 'category'")->select();
        $this->assign("AllTags",$tags);
        $this->assign("options",$this->options);
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
                    'postid'=>$pid,
                    'private'=>$this->options['showhiddenentry']);
            }
        $entryInfo=$entryDB->field(array("title","createdate"))->where($where1)->where("createdate < '%s'",date("Y-m-d h:i:s"))->select();
        //判断日期和pid是否合法
        if($entryInfo){
            $imgUrls=$imgsDB->where($where1)->order("id desc")->page($p,$this->options['imgsperpage'])->select();
            $seperateUrls=['video'=>[],'img'=>[]];
            foreach ($imgUrls as $key => $value) {
                $value["url"]=str_replace("http://", "https://", $value["url"]);
                if($value['type']==1){
                    array_push($seperateUrls['video'], $value);
                }else{
                    array_push($seperateUrls['img'], $value);
                }
                
            }
            $result=array("info"=>$entryInfo[0],"content"=>$seperateUrls);
            //图片分页
            $next=$entryDB->where('postid>%d',array($pid))->limit(1)->select();
            $prev=$entryDB->where('postid<%d',array($pid))->order('postid desc')->limit(1)->select();
            $prevAndNext=array($prev[0],$next[0]);
            // dump($prevAndNext);
            $imgCount=$imgsDB->where($where1)->count();
            $paging= new  \Think\Page($imgCount, C("imgPerPage"));
            $pageShow=$paging->show();
            //分类 
        }else{
            E("Forbidden! You are NOT allowed to view this page.",403);
        }
		
        $metaDB=M("meta");
        $tags = $metaDB->where("type= 'category'")->select();
        $this->assign('PrevAndNext',$prevAndNext);
        $this->assign("AllTags",$tags);
        $this->assign("page",$pageShow);
        // dump($result);
		$this->assign("data",$result);
        $this->assign("options",$this->options);
		// $this->display("postHydrogen");
        $this->display("test");  
		
    	
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

    public function test($page=1,$tagid=0,$private=0){
//         header("Access-Control-Allow-Origin: *"); // 允许任意域名发起的跨域请求  
//         header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With');
//         header('Content-type: application/json; charset=UTF-8');
//         echo '[
//   [5397108000014,1001150002,"1千克贝因美绿爱+较大婴儿配方奶粉",335,1.347,0.003445],
//   [5397108000021,1001150003,"1千克贝因美绿爱+幼儿配方奶粉",325,1.347,0.003445],
//   [5397108000069,1001380001,"贝因美爱+red婴儿配方奶粉800克",328,1.083333333333,0.002840112],
//   [5397108000076,1001380002,"贝因美爱+red较大婴儿配方奶粉800克",318,1.083333333333,0.002840112],
//   [5397108000083,1001380003,"贝因美爱+red幼儿配方奶粉800克",308,1.083333333333,0.002840112],
//   [5397108000786,1001150004,"贝因美绿爱+婴儿配方奶粉800克",288,1.083333333333,0.002840112],
//   [5397108000793,1001150005,"贝因美绿爱+较大婴儿配方奶粉800克",288,1.083333333333,0.002840112],
//   [5397108000809,1001150006,"贝因美绿爱+幼儿配方奶粉800克",288,1.083333333333,0.002840112],
//   [5397108001165,1001470001,"可睿心Creation+婴儿配方奶粉800克",228,1.08333333333333,0.002840112],
//   [5397108001172,1001470002,"可睿心Creation+较大婴儿配方奶粉800克",228,1.08333333333333,0.002840112],
//   [5397108001189,1001470003,"可睿心Creation+幼儿配方奶粉800克",228,1.08333333333333,0.002840112],
//   [6904591000521,1001040001,"(08版)450g金装贝因美初生婴儿配方奶粉",56.6,0.53133333,0.001925625]
// ]';
    // $result=M('imgs')->query('select * from imgs where postid=50');
    // dump($result);
        // $this->display();
        // $ret=M('entry')->join('left join imgs on entry.postid=imgs.postid')->where('imgs.cover=1')->order('entry.postid desc')->page(1,10)->;
        // dump($ret);
        $ret=D('entry')->getPost($page,$tagid,$private);
        dump($ret);

    }


    public function Landing(){
        $this->display();
    }

    public function index($tagid=0,$p=1){
        $legalTagId=D('entry')->getCategoryIds();
        $this->assign("pagetitle",'Home');
        if(!$tagid==0 and !in_array($tagid,$legalTagId)){
            $msg='tid not exist: '.$tagid;
            E($msg,44);
        }else if(in_array($tagid,$legalTagId)){
            $tagInfo=D('entry')->getTagInfoById($tagid);
            $this->assign("tagInfo",$tagInfo);
            $this->assign("pagetitle",$tagInfo['name']);
        }
        $entryModel=D('entry');
        $entryDB=M('entry');
        $login=session('user')?true:false;
        $p=(int)$p?(int)$p:1;
        if($login){
            $result=$entryModel->getPost($p,$tagid,1);
        }else{
            $result=$entryModel->getPost($p,$tagid,0);
        }
        foreach ($result['data'] as $key => $value) {
            $result['data'][$key]['url']=str_replace('http://', 'https://', $value['url']);
            // dump($result['data'][$key]['url']);

        }
        // dump($result['data']);
        $this->assign("title",C("title"));
        $this->assign("data",$result['data']);
        $imgCount=D('entry')->getImgCount();
        // dump($imgCount);
        $paging= new  \Think\Page($result['count'], C("entryPerPage"));
        $show=$paging->show();
        $this->assign("imgCount",$imgCount);
        $this->assign("page",$show);

        //tag
        $tags = M('meta')->where("type= 'category'")->select();
        $this->assign("AllTags",$tags);
        $this->assign("options",$this->options);
        $this->display("indexSQLopt"); 
    }
}