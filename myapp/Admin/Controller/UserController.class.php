<?php

namespace Admin\Controller;
use Think\Controller;


class UserController extends Controller{
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

	private function usertype(){
		return array(
		0=>"账户已停用",
		1=>"管理员",
		2=>"普通用户");
	}
	

	public function index(){
		$userDB=M("users");
		$allUser=$userDB->select();
		$this->assign("alluser",$allUser);
		$this->assign("usertype",self::usertype());
		$this->assign("pagename","查看用户");
		$this->assign("currModule","user");
		$this->display();
	}


	public function edit(){
		if(isset($_GET['id'])){
			$userDB=M("users");
			$user=$userDB->where("id =%d",$_GET['id'])->select();
		}else{

		}
		session("user.editid",$_GET['id']);
		session("user.editemail",$user[0]["email"]);
		$token=randomStr();
		session("user.token",$token);
		$this->assign("token",$token);
		$this->assign("userinfo",session("user"));
		$this->assign("usertype",self::usertype());
		$this->assign("user",$user[0]);
		$this->assign("pagename","编辑用户");
		$this->display();
	}

	public function saveAPI(){
		$userDB=M("users");
		if(!$_POST["token"]==session("user.token")){
			msg(0,"token不匹配");
		}
		if(!$_POST["id"]==session("user.editid")){
			msg(0,"id不匹配");
		}
		$condition=$_POST;
		if(!session("user.editmail")==$condition["email"]){
			$emailtest=$userDB->where("email='%s'",$condition["email"])->select();
			if($emailtest){
				msg(0,"邮箱重复");
			}
		}else{
			unset($condition["email"]);
		}
		if($_POST["password"]==""){
			unset($condition["password"]);
		}else{
			$condition["password"]=getSaltedMd5($condition["password"]);
		}
		$result=$userDB->where("id=%d",$_POST["id"])->save($condition);
		if($result){
			msg(200,"更新成功");
		}else{
			msg(0,"更新失败");
		}
	}

	// public function test(){
	// 	$a=array("a"=>111,"b"=>222);
	// 	unset($a["a"]);
	// 	dump($a);
	// }
}
