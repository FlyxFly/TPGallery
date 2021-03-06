<?php

namespace Admin\Controller;
use Think\Controller;

class LoginController extends Controller{
	public function __construct(){
		parent::__construct();
		if(session("?user")){
			if(session("user.type")==1){
				$this->redirect("Admin/Index/index",null,3,"You are logged in. Redirecting to Admin Panel in 3s.");
			}else{
				$this->redirect("Home/Index/index",null,3,"You are logged in. Redirecting to Home in 3s.");
			}
		}
	}

	public function index(){
		$this->assign('pagename','Login');
		$this->display("newlogin");
	}

	public function reg(){
		$this->assign('pagename','Sign Up');
		$this->display("register");
	}

	public function check(){
		if(!isset($_POST['type']) || !isset($_POST['value'])){
			msg(0,"请求不完整");
		}
		$type=$_POST['type'];
		$value=$_POST['value'];
		$usersDB=M('users');
		if($type=='email'){
			if(!self::getByEmail($value)){
				msg(200,"邮件地址可用");
			}else{
				msg(199,"邮件地址不可用");
			}
		}

		if($type=='username'){
			if(!self::getByUsername($value)){
				msg(200,"用户名可用");
			}else{
				msg(199,"用户名不可用");
			}
		}
		msg(0,"非法请求");
	}

	public function login(){
		
		$log=D('Home/Entry');
		if(!isset($_POST['email']) || !isset($_POST['password'])){
			msg(0,"Please input both email and password.");
		}
		$passcheck=self::checkUserPass($_POST['email'],$_POST['password']);
		if($passcheck){

			session(["start"]);
			session("user",$passcheck[0]);
			M("users")->where("id = %d",$passcheck[0]['id'])->setField(array("lastlogindate"=>time(),"lastloginip"=>getIp()));
			$log->addLog('login','admin',1,'登录成功,用户:'.$passcheck[0]['username']);
			msg(200,"Login success!");
			
		}else{
			$log->addLog('login','admin',0,'登录失败,用户:'.$_POST['email'].',密码:'.$_POST['password']);
			msg(0,"Username or password wrong!");
		}
		
		
	}

	public function register(){
		
		$log=D('Home/Entry');
		if(!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['email'])){
			msg(0,"Please input username password and email.");
		}
		$data['email']=$_POST['email'];
		$data['password']=getSaltedMD5($_POST['password']);
		$data['username']=$_POST['username'];

		if(self::getByUsername($data['username'])){
			msg(0,"Username was taken.");
		}
		if(self::getByEmail($data['email'])){
			msg(0,"Email was taken.");
		}
		$data['createip']=getIp();
		$data['createdate']=time();
		$data['type']=2;
		$usersDB=M('users');
		$result=$usersDB->data($data)->add();
		if($result){
			$log->addLog('reg',null,1,'注册成功');
			msg(200,"Success! Please login.");
		}else{
			$log->addLog('reg',null,0,'注册失败');
			msg(0,"FAIL! Please retry!");
		}
	}


	private function getByUsername($username=null){
		if($username==null){
			msg(0,"Username can not be null");
		}
		$usersDB=M('users');
		$result=$usersDB->where("username='%s'",$username)->select();
		if(sizeof($result)==0){
			return false;
		}else{
			return $result;
		}		
	}

	private function getByEmail($email=null){
		if($email==null){
			msg(0,"Email can't be empty.");
		}
		$usersDB=M('users');
		$result=$usersDB->where("email='%s'",$email)->select();

		if(sizeof($result)==0){
			return false;
		}else{
			return $result;
		}
	}

	private function checkUserPass($email=null,$password=null){
		if($email==null || $password==null){
			msg(0,"Please input both email and password");
		}
		$usersDB=M('users');
		$saltedPwd=getSaltedMD5($password);
		$result=$usersDB->where("email='%s' and password='%s' and type <>0",$email,$saltedPwd)->select();
		if(sizeof($result)==0){
			return false;
		}else{
			return $result;
		}

	}



	public function test(){
		$qqwry=new \Common\Lib\qqwry('./Public/qqwry.dat');
		$ret=$qqwry->getlocation('3.4.5.7');
		dump($ret);
		dump(iconv('GB2312','UTF-8',$ret['country']));
		dump(iconv('GB2312','UTF-8',$ret['area']));

	}


}