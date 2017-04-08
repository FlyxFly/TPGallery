<?php
namespace Passcode\Controller;
use Think\Controller;
import("ORG\Util\Page");

class GuanliController extends Controller{
	// public function __construct(){
	// 	parent::__construct();
		
	// }

	public function index(){
		// 检测登陆状态
		if(!session("?login")){
			$this->redirect("Passcode/Guanli/Login");
		}
		// 注销操作
		if(isset($_POST['action']) and isset($_POST['action'])=="logout"){
			session("login",null);
			$this->redirect("Passcode/Index/index");
		}

		if(!isset($_POST['status']) or $_POST['status']=="all"){
			$whereCondition="";
		}
		if(isset($_POST['status']) and $_POST['status']=="pending"){
			$whereCondition="status = 'pending'";
		}
		if(isset($_POST['status']) and $_POST['status']=="deleted"){
			$whereCondition="status = 'deleted'";
		}

		// 展示所有信息
		$itemEveryPage=30;
		$codeDB=M("code");
		$pageInfo=array();
		$count=$codeDB->where($whereCondition)->count();
		$pageInfo['totalPage']=floor($count/$itemEveryPage)+2;

		if(!isset($_GET['p']) or $_GET['p']<0){
			$pageInfo['currPage']=1;	
		}else{
			$pageInfo['currPage']=(int)$_GET['p'];
		}
		$queryStart=($pageInfo['currPage']-1)*$itemEveryPage;
		// dump($count);
		$queryData=$codeDB->where($whereCondition)->order("id desc")->limit($queryStart,$itemEveryPage)->select();
		$resultData=array();
		foreach ($queryData as $key => $value) {
			if($value['yes']==null){$yes=0;}else{$yes=(int)$value['yes'];}
			if($value['no']==null){$no=0;}else{$no=(int)$value['no'];}
			if($yes+$no==0){
				$value['successRate']=0;
			}else{
				$value['successRate']=floor($yes/($yes+$no)*100);
			}
			$value['votes']=$yes+$no;
			$value['age']=self::dateConvert($value['createdate']);
			array_push($resultData, $value);
		}
		// dump($show);
		$this->assign("pageInfo",$pageInfo);
		$this->assign("data",$resultData);
		// dump($resultData);
		$this->display();
	}

	public function save(){
		//检测登陆状态
		if(!session("?login")){
			$this->redirect("Passcode/Guanli/Login");
		}
		//审核通过
		if(!isset($_POST['action'])){
			msg(0,"未指定操作");
		}
		if(!isset($_POST['id']) or count($_POST['id'])==0){
			msg(0,"未指定ID");
		}
		if($_POST['action']=="approve"){
			$result=array();
			foreach ($_POST['id'] as $key => $value) {
				$result=M("code")->where("id=%d",$value)->setField("status",null);
				$result[$value]=$result;
			}
			msg(1,"请在结果页面查看每一条记录的操作结果",json_encode($result));

		}
		//删除
		if($_POST['action']=="delete"){
			$result=array();
			foreach ($_POST['id'] as $key => $value) {
				$result=M("code")->where("id=%d",$value)->delete();
				$result[$value]=$result;
			}
			msg(1,"请刷新查看结果",json_encode($result));
		}
	}

	public function Login(){
		// 设置登陆密码 
		$password="369258qq";
		if(session("?login")){
			$this->redirect("Passcode/Guanli/index");
		}
		if(isset($_POST['action']) and $_POST['action']=="getname"){
			$username=self::getRandomString(8);
			session("username",$username);
			msg(1,"获取用户名成功",$username);
		}
		if(isset($_POST['username']) and isset($_POST['password']) and $_POST['username']==session("username") and $_POST['password']==$password){
			session("login","true");
			$this->redirect("Passcode/Guanli/index");
		}
		$this->display("login");
	}

	public function Logout(){
		session("login",null);
		$this->redirect("Passcode/Index/index");
	}

	private function getRandomString( $length = 5 ) {  
		// 密码字符集，可任意添加你需要的字符  
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%.';  
		$password = '';  
		for ( $i = 0; $i < $length; $i++ )  
		{  
		// 这里提供两种字符获取方式  
		// 第一种是使用 substr 截取$chars中的任意一位字符；  
		// 第二种是取字符数组 $chars 的任意元素  
		// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);  
		$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
		}  
		return $password;  
	} 
	
	private function dateConvert($dateString){
		$now=time();
		$input=strtotime($dateString);
		$daysCompare=round(($now-$input)/3600/24);
		if($daysCompare<31){
			return array($daysCompare,"day");
		}
		if($daysCompare/31<12){
			return array(round($daysCompare/31),"month");
		}
		return array(round($daysCompare/31/12),"year");
	}

}