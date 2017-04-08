<?php

namespace Admin\Controller;
use Think\Controller;
import("ORG\Util\Page");

class TagController extends Controller{
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


	public function index($mid=null){
	$this->assign("pagename","标签管理");
	$this->assign("currModule","tag");
	$entryDB=M("entry");
	$metaDB=M("meta");
	$relationshipDB=M("relationship");
	$idToName=array();
	$allMeta=$metaDB->order('type')->select();
	foreach ($allMeta as $key => $item) {
		$idToName[$item['mid']]=$item['name'];
	}
	if($mid==null){
		$this->assign("allMeta",$allMeta);
		$this->assign("idToName",$idToName);
		$this->assign("saveAPI",U("Admin/Tag/save"));
		$this->assign("delAPI",U("Admin/Tag/del"));
		// dump($allMeta);
		$this->display();
	}else{
		$_oneMeta=$metaDB->where("mid=$mid")->select();
		$oneMeta=$_oneMeta[0];
		$this->assign("oneMeta",$oneMeta);
		$allCategory=$metaDB->where("type = 'category'")->select();
		$this->assign("allCategory",$allCategory);
		$this->assign("allCategoryJSON",json_encode($allCategory));
		// dump(U("Admin/Tag/save"));
		$this->assign("saveAPI",U("Admin/Tag/save"));
		$this->assign("delAPI",U("Admin/Tag/del"));
		$this->assign("metaManagePage",U("Admin/Tag/index"));
		$this->assign("userinfo",session("user"));

		$this->display();
	}
	}

	public function save(){
		$metaDB=M("meta");
		echo($metaDB->save($_POST));
	}

	public function del(){
		$metaDB=M("meta");
		echo($metaDB->delete($_POST['id']));
	}
}