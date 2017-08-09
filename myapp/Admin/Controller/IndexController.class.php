<?php

namespace Admin\Controller;
use Think\Controller;
import("ORG\Util\Page");

class IndexController extends Controller{
	public function __construct(){
		parent::__construct();
		if(!session("?user")){
			$this->redirect("Admin/Login/index");
		}else{
			if(!session("user")["type"]==1){
				$this->redirect("Home/Index/index");
			}
		}
	}

	public function index($p=1,$q=null,$cid=null){
		//p=页码，q=搜索关键字,$cid=分类id
		$entryDB=M("entry");
		$metaDB=M("meta");
		$relationshipDB=M("relationship");
		// dump(!$q==null);
		if(!$q==null){
			$allEntry=$entryDB->where("title like '%s'","%".$q."%")->order("postid desc")->page($p,C("entryPerPage"))->select();
			$totalEntryQty=$entryDB->where("title like '%s'","%".$q."%")->count();
		}else if(!$cid==null && (int)$cid){
			$dataSQL="select * from entry where postid in (select pid from relationship where mid={$cid})";
			$countSQL="select count(postid) from entry where postid in (select pid from relationship where mid={$cid})";
			$allEntry=M()->query($dataSQL);
			$totalEntryQtyRet=M()->query($countSQL);
			$totalEntryQty=$totalEntryQtyRet[0]["count(postid)"];
		}else{
			$allEntry=$entryDB->order("postid desc")->page($p,C("entryPerPage"))->select();
			$totalEntryQty=$entryDB->count();
			
		}
		// dump($allEntry);
		$entryInfo=array();
		foreach ($allEntry as $key => $value) {
			$postid=$value["postid"];
			$whereRule=array("pid"=>$postid,"type"=>"category");
			$thisEntryTagId=$relationshipDB->where($whereRule)->select();
			$value["category"]=array();
			if(count($thisEntryTagId)){
				foreach ($thisEntryTagId as $key => $value2) {
					$thisEntryMid=$value2['mid'];
					$tagChinese=$metaDB->where("mid= $thisEntryMid")->select();
					// array_push($value["category"],$tagChinese[0]['name']);
					$value["category"][$value2['mid']]=$tagChinese[0]['name'];
				}
			}else{
				$value["category"]=null;
			}
			
			array_push($entryInfo, $value);
		}
		// dump($entryInfo);
		$this->assign("entryinfo",$entryInfo);
		// dump($totalEntryQty);
		$totalpage=ceil($totalEntryQty/C("entryPerPage"));
		$this->assign("q",$q);
		$pageLink=$q==null?"Admin/Index/index?p=":"Admin/Index/index?q={$q}&p=";
		// dump($totalEntryQty);
        $this->assign("pagecode",semanticPage($totalpage,$p,$pageLink));
        $this->assign("pagename","后台首页");
        $this->assign("currModule","index");
        $this->assign("userinfo",session("user"));
		$this->display();

	}


	public function edit(){
		$entryDB=M("entry");
		$imgsDB=M("imgs");
		$metaDB=M("meta");
		$relationshipDB=M("relationship");
		if(isset($_GET['pid']) and $_GET['pid']){
			$pid=$_GET['pid'];
			$thisEntryInfo=$entryDB->where("postid = $pid")->select();
			$thisEntryInfo["imgs"]=$imgsDB->where("postid =$pid")->select();
			$this->assign("entryinfo",$thisEntryInfo);	
			// dump($thisEntryInfo);
			//日志

			//本日志分类和标签$category,$tagsArray
			$metas=$relationshipDB->where("pid = $pid")->field("mid")->select();
			$tagsArray=array();
			$thiscategory=array();

			if($metas){
				$tagsArray=array();
				foreach ($metas as $key => $value) {
					$mid=$value["mid"];
					$tagsAndCat=$metaDB->where("mid = $mid")->select();
					if($tagsAndCat){
						foreach ($tagsAndCat as $index => $item) {
							if($item["type"]=="category"){
								$category=array(
									"name"=>$item["name"],
									"mid"=>$item["mid"]
									);
							}else{
								array_push($tagsArray, $item["name"]);
							}
						}
					}
				}
			}else{
				$category=null;
			}
			
			$this->assign("tags",$tagsArray);
			$sortedCategory=array();
			$this->assign("pagename","文章编辑");
			$this->assign("currModule","edit");
			$thiscategory=array();
			if($metas){
			foreach ($metas as $key => $value) {
				array_push($thiscategory, $value["mid"]);
					}
				}else{
					$thiscategory=null;
				}
			$this->assign("thiscategory",$thiscategory);
		}else{
			$metas=$relationshipDB->distinct(true)->field("mid")->select();
			$this->assign("pagename","新建文章");
			$this->assign("currModule","create");
		}

		$allCategory=$metaDB->where("type = 'category'")->order("parent")->select();
		$this->assign("allcategory",$allCategory);
		

		
		$this->display();

	}

	public function savepost(){
		$pid=$_POST['pid'];
		$entryDB=M("entry");
		$relationshipDB=M("relationship");
		$imgsDB=M("imgs");
		if(!isset($_POST['categorys'])){
			msg(0,"请至少指定一个分类");
		}
		if(!isset($_POST['cover'])){
			msg(0,"请选择封面");
		}
		if(!isset($_POST['private'])){
			$_POST['private']=0;
		}
		if(!$_POST['private']==1 & !$_POST['private']==0){
			msg(0,"private的值只能是0或者1");
		}
		if(!isset($_POST['pid']) or null==$_POST['pid']){
			//新建一个文章
			//添加entry
			$new_postid=$entryDB->max("postid");
			$entryinfo=array(
				"postid"=>$new_postid+1,
				"title"=>$_POST['title'],
				"authorid"=>session("user")["id"],
				"createdate"=>date("Y-m-d"),
				"private"=>$_POST['private']);

			$new_entry_id=$entryDB->data($entryinfo)->add();
			//添加标签meta
			$meta_info = array();
			if(sizeof($_POST['categorys'])){
				foreach ($_POST['categorys'] as $key => $value) {
					$meta_info[]=array("pid"=>$new_entry_id,"mid"=>$value);
				}
				$new_rela_result=$relationshipDB->addAll($meta_info);
			}
			//添加内容img url
			$imgs_info=array();
			//图片管理单独一个api
			// foreach ($_POST["imgs"] as $key => $value) {
			// 	if($_POST["imgs"]["cover"]==$key){
			// 		array_push($imgs_info,array(
			// 		"postid"=>$new_entry_id,
			// 		"url"=>$value["url"],
			// 		"height"=>$value["height"],
			// 		"width"=>$value["width"],
			// 		"cover"=>1));
			// 	}else{
			// 		array_push($imgs_info,array(
			// 		"postid"=>$new_entry_id,
			// 		"url"=>$value["url"],
			// 		"height"=>$value["height"],
			// 		"width"=>$value["width"],
			// 		"cover"=>0));
			// 	}
				
			// }
			$new_img_result=$imgsDB->addAll($imgs_info);
			if(!$new_entry_id){msg(0,"新增文章失败");}
			if(!$new_rela_result){msg(0,"新增分类失败");}
			// if(!$new_img_result){msg(0,"新增图片失败");}
			msg(200,"新增成功");
			// if($new_entry){
			// 	msg(1,$new_entry);
			// }else{
			// 	msg(1,"新增失败");
			// }
		}else{

			//更新文章
			
			//更新标题
			$titleupdate=$entryDB->where("postid=%d",$pid)->setField(array("title"=>$_POST['title'],"private"=>$_POST['private']));

			//更新分类
			$relationshipDB->where("pid=%d",$pid)->delete();
			$catearray=array();
			foreach ($_POST["categorys"] as $key => $value) {
				$cateupdate=$relationshipDB->add(array("pid"=>$pid,"mid"=>$value));
				
			}

			//更新封面
			$imgsDB=M("imgs");
			$imgsDB->where("postid=%d and cover=1",$pid)->setField("cover",0);
			$coverupdate=$imgsDB->where("id=%d",$_POST['cover'])->setField("cover",1);
			
			if($titleupdate || $cateupdate ||$coverupdate){msg(200,"更新成功！");}
		}
	}

	// public function saveTitle($pid=null,$mid=null,$title=null){
	// 	if($pid==null){
	// 		msg(0,"非法操作！");
	// 		exit;
	// 	}elseif ($title==null) {
	// 		msg(1,"标题不能为空！");
	// 		exit;
	// 	}
	// 	$relationship=M("relationship");
	// 	$entry=M("entry");

	// 	dump($mid);


	// }

	public function delPost(){
		if(!isset($_POST['pid'])){
			msg(0,"需要指定要删除的日志编号,请通过点击按钮来操作");
		}
		$pid=$_POST['pid'];
		$entryDB=M("entry");
		$relationshipDB=M("relationship");
		$imgsDB=M("imgs");
		$entryDB->startTrans();
		$entryDB->where("postid = %d",$pid)->delete();
		$relationshipDB->startTrans();
		$relationshipDB->where("pid = %d",$pid)->delete();
		$imgResult=$imgsDB->where("postid = %d",$pid)->delete();
		if($imgResult===false){
			$relationshipDB.rollback();
			$entryDB.rollback();
			msg(0,"删除日志对应的图片失败");
		}
		$relaResult=$relationshipDB->commit();
		if($relaResult===false){
			$entryDB.rollback();
			msg(0,"删除图片成功，但删除日志分类失败");
		}
		$entryResult=$entryDB->commit();
		if($entryResult===false){
			msg(0,"删除图片和分类成功，但是删除日志失败");
		}
		msg(200,"删除日志成功");

	}

	public function imgEdit(){
		if(isset($_POST['action'])){
			switch($_POST["action"]){
				case "delete":
				if(isset($_POST["imgid"])){
					$result=self::imgEditAPI("delete",$_POST);
					msg(1,json_encode($result));
				}else{
					msg(0,"未指定图片id");
				}
				break;

				case "add":
					$result=self::imgEditAPI("add",$_POST);
					msg(1,json_encode($result));
				break;

				default:
				msg(0,"操作不合法！");
			}

		}else{
			msg(0,"未指定操作");
		}

	}

	private function imgEditAPI($action,$data){
		//接受一个对象，对图片库进行操作
		//{"action":"","url":[],"imgid":[]}
		$imgsDB=M("imgs");
		switch($action){
			case "delete":
			$result=array('count'=>0,"data"=>array());
			foreach ($data["imgid"] as $key => $value) {
				$result["data"][$key]["url"]=$res[0]["url"];
				$res=$imgsDB->where("id=%d",$value)->select();
				$patharray=explode("/", $res[0]['url']);
				$filepath=urldecode(C("imgpath").$patharray[3]."/".$patharray[4]."/".$patharray[5]);
				// dump($filepath);
				if(!unlink($filepath)){
					$result["data"][$key]["delsuccess"]=0;
				}else{
					$sqlresult= $imgsDB->delete($value);
					$result["count"]+=1;
					$result["data"][$key]["delsuccess"]=1;
					
				}

			}
			return $result;
			break;

			case "add":
			$addresult=array("");
			$postid=$data['postid'];
			$resultcount=0;
			foreach ($data["url"] as $key => $value) {
				$sqlresult= $imgsDB->add(array("postid"=>(int)$postid,"url"=>$value));
				if($sqlresult){$resultcount++;}
			}
			$result=array("tobe"=>count($_POST["url"]),"success"=>$resultcount);
			return $sqlresult;

			
			break;
			case "edit":
			msg(0,"功能未完成");
			break;
			default:
			msg(0,"非法操作！");
		}

	}

	public function upload(){
		$entryDB=M("entry");
		$ret=$entryDB->field("postid,title")->order("postid desc")->select();
		$this->assign("allEntry",$ret);
		$this->assign("currModule","upload");
		$this->assign("pagename","文件上传");
		$this->assign("domain",C("fileHostDomain"));
		$this->display();
	}

	public function uploadAPI(){
		$entryDB=M("entry");
		$imgsDB=m("imgs");
		if(!isset($_POST["action"])){
			msg(0,"未指定操作");
		}
		$upload = new \Think\Upload();
	    $upload->maxSize   =     10485760 ;// 设置附件上传大小
	    $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg', 'pdf');// 设置附件上传类型
	    $upload->rootPath  =     C("uploadDir"); // 设置附件上传根目录
	    $upload->autoSub=false;
		$newFileDir=M('options')->where('op = "newuploaddir" and user =0')->select();
	    //根据操作类型确定上传目录
		switch($_POST["action"]){
			case "add":
			if(!isset($_POST["postid"])){
				msg(0,"未指定日志ID");
			}
			
			if($_POST["postid"]<142){
				$upload->rootPath = "/home/wwwroot/ggimg/imgs/";
				$result=$entryDB->field("title")->where("id=%d",(int)$_POST["postid"])->select();
				if($result){
					$upload->savePath  = $result[0]["title"]."/";
				}else{
					$upload->savePath  = date("Y-m",time())."/";
				}
			}else{
				$upload->savePath  = date("Y-m",time())."/";
			}
			$info   =   $upload->upload();
		    if(!$info) {// 上传错误提示错误信息
		        $this->error($upload->getError());
		    }else{// 上传成功
		    	foreach ($info as $key => $value) {
		    		$data["postid"]=$_POST["postid"];
		    		$data["filepath"]=C("uploadDir").$value["savepath"].$value["savename"];
		    		$data["url"]=C("fileHostDomain")."/".$value["savepath"].$value["savename"];
		    		$imgsDB->data($data)->add();
		    	}
		    	msg(200,"操作成功",$info);
		    }
			break;

			case "new";
			// if($newFileDir[0]['value']=='upload'){
				$upload->savePath  = date("Y-m",time())."/";
			// }else{
				// $upload->savePath  = $_POST["newTitle"]."/";
			// }
			$info   =   $upload->upload();
		    if(!$info) {// 上传错误提示错误信息
		        $this->error($upload->getError());
		    }else{// 上传成功
		    	$checkIfExist=$entryDB->where("title= '%s'",$_POST["newTitle"])->select();//检查名称是否存在
				if($checkIfExist){//如果存在就跳过添加日志，直接用存在的id添加图片
					$newPostId=$checkIfExist[0]['id'];
				}else{//如果不存在就添加日志，添加图片
					$newPostId=$entryDB->max("postid")+1;
			    	$newEntryData["title"]=$_POST["newTitle"];
			    	$newEntryData["authorid"]=session("user")[id];
			    	$newEntryData["createdate"]=date("Y-m-d");
			    	$newEntryData["postid"]=$newPostId;
			    	$newEntryData["private"]=1;
			    	$newEntryId=$entryDB->add($newEntryData);
				}
		    	
		    	foreach ($info as $key => $value) {
		    		$data["postid"]=$newPostId;
		    		$data["filepath"]=C("uploadDir").$value["savepath"].$value["savename"];
		    		$data["url"]=C("fileHostDomain")."/".$value["savepath"].$value["savename"];
		    		$imgsDB->data($data)->add();
		    	}
		    	msg(200,"操作成功",$info);
		    }
			break;

			default:
			msg(0,"操作不合法");
		}

	}

	public function remoteDownloadAPI(){
		$entryDB=M("entry");
		$imgsDB=m("imgs");
		if(!isset($_POST["action"])){
			msg(0,"未指定操作");
		}
		$urlArr=explode("\r\n",$_POST["urls"]);
		$result=array();

		switch($_POST['action']){
			case 'new':
			$dirName=date("Y-m",time());
			$savePath  = C("uploadDir").date("Y-m",time())."/";
			if(!is_dir($savePath)){
				mkdir($savePath);
			}
			$checkIfExist=$entryDB->where("title= '%s'",$_POST["newTitle"])->select();//检查名称是否存在
			if($checkIfExist){//如果存在就跳过添加日志，直接用存在的id添加图片
				$newPostId=$checkIfExist[0]['id'];
			}else{//如果不存在就添加日志
				$newPostId=$entryDB->max("postid")+1;
		    	$newEntryData["title"]=$_POST["newTitle"];
		    	$newEntryData["authorid"]=session("user")[id];
		    	$newEntryData["createdate"]=date("Y-m-d");
		    	$newEntryData["postid"]=$newPostId;
		    	$newEntryId=$entryDB->add($newEntryData);
			}
			foreach ($urlArr as $key => $value) {
				$fileName=randomStr(13,'f').".".substr($value, -3);
				$info=self::getImage($value,$savePath,$fileName);
				array_push($result, array("source"=>$value,"saveto"=>$savePath.$fileName,"size"=>filesize($savePath.$fileName),"url"=>C("fileHostDomain")."/".$dirName."/".$fileName));
				$data["postid"]=$newPostId;
				$data["filepath"]=$savePath.$fileName;
	    		$data["url"]=C("fileHostDomain")."/".$dirName."/".$fileName;
	    		$imgsDB->data($data)->add();
			}
		    msg(200,"操作成功",$result);
			break;


			case 'add':
			if(!isset($_POST["postid"])){
				msg(0,"未指定日志ID");
			}
			$titleResult=$entryDB->field("title")->where("postid=%d",(int)$_POST["postid"])->select();
			if($titleResult){
				if($_POST["postid"]<142){
					$dirName=$titleResult[0]["title"];
				}else{
					$dirName=date("Y-m",time());
				}
				
				$savePath  = C("uploadDir").$dirName."/";
				if(!is_dir($savePath)){
				mkdir($savePath);
			}
			}else{
				msg(0,"非法id");
			}
			foreach ($urlArr as $key => $value) {
				$fileName=randomStr(13,'f').".".substr($value, -3);
				$info=self::getImage($value,$savePath,$fileName);
				array_push($result, array("source"=>$value,"saveto"=>$savePath.$fileName,"size"=>filesize($savePath.$fileName),"url"=>C("fileHostDomain")."/".$dirName."/".$fileName));
				$data["postid"]=$_POST["postid"];
				$data["filepath"]=C("uploadDir")."/".$dirName."/".$fileName;
	    		$data["url"]=C("fileHostDomain")."/".$dirName."/".$fileName;
	    		$imgsDB->data($data)->add();
			}
			msg(200,"操作成功",$result);
			break;
		}
	}

	private function getImage($url,$save_dir='',$filename='',$type=0){
            if(trim($url)==''){
                return array('file_name'=>'','save_path'=>'','error'=>1);
            }
            if(trim($save_dir)==''){
                $save_dir='./';
            }
            if(trim($filename)==''){//保存文件名
                $ext=strrchr($url,'.');
            if($ext!='.gif'&&$ext!='.jpg'&&$ext!='.png'&&$ext!='.apk'){
                return array('file_name'=>'','save_path'=>'','error'=>3);
            }
                $filename=time().rand(0,10000).$ext;
            }
            if(0!==strrpos($save_dir,'/')){
                $save_dir.='/';
            }
            //创建保存目录
            if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
                return array('file_name'=>'','save_path'=>'','error'=>5);
            }
            //获取远程文件所采用的方法
            if($type){
                $ch=curl_init();
                $timeout=5;
                curl_setopt($ch,CURLOPT_URL,$url);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
                $img=curl_exec($ch);
                curl_close($ch);
            }else{
                ob_start();
                readfile($url);
                $img=ob_get_contents();
                ob_end_clean();
            }
            //$size=strlen($img);
            //文件大小
            $fp2=@fopen($save_dir.$filename,'a');
            fwrite($fp2,$img);
            fclose($fp2);
            unset($img,$url);
            return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
    }

	public function logout(){
		session("user",null);
		$this->redirect("Login/Index");
	}


	public function editconf(){
		$optionsDB=D("options");
		if($_POST){
			if($_POST['whose']==='system' and session('user')["type"]==1){
				$userid=0;
			}else{
				$userid=session("user")["id"];
			}
			if($_POST['sitename']==''){msg(0,'站点名称不能为空');};
			if($_POST['uploaddir']==''){msg(0,'上传目录不能为空');};
			if($_POST['entrysperpage']==''){msg(0,'每页日志数不能为空');};
			if($_POST['imgsperpage']==''){msg(0,'每页图片数不能为空');};
			echo json_encode(D('options')->updateConfig($userid,$_POST));
		}else{
			$sysOption=$optionsDB->getCachedSysConfig();
			// dump($sysOption);
			$userOption=$optionsDB->getUserConfig(session("user")["id"]);
			$this->assign('sysOption',$sysOption);
			$this->assign('userOption',$userOption);
			$this->display();
		}
		
	}
	public function test(){
		// // $data=array('name'=>'小明','desc'=>'小明是一个中国人');
		// // F('info',$data);
		// // if(!F('confCache')){
		// // 	echo 'yes';
		// // }else{
		// // 	echo 'no';
		// // }

		// $url='http://vip1.bdimg.com/icon/viplogo_618_2.png';
		// $fileName=randomStr(13,'f');
		// $fileExt=substr($url,-3);

		// echo $fileName;
		// dump(self::getImage($url,'D:\wamp64\www\thinkphp\myapp\Runtime\Temp'),$fileName.'.'.$fileExt);
		// $path='D:\\wamp64\\www\\TestProjects\\uploadtest\\201604\\';
		// $filename='cd5fepjoec7rk.txt';
		// if(!is_dir($path)){
		// 	mkdir($path);
		// }

		// $fp2=@fopen('D:\\wamp64\\www\\TestProjects\\uploadtest\\201604\\cd5fepjoec7rk.txt','a');
  //       fwrite($fp2,'abc');
  //       fclose($fp2);
		
		// echo date("Y-m",time());
		// $newFileDir=M('options')->where('op = "newuploaddir" and user = 0')->select();
		// dump($newFileDir[0]['value']);
		$a=[];
		$a[0]=44;
		$a[]=33;
		dump($a);
	}

}