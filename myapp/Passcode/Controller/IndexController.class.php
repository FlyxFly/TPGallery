<?php

namespace Passcode\Controller;
use Think\Controller;
import("ORG\Util\Page");

class IndexController extends Controller{
	public function index(){
		$codeDB=M("code");
		$pageInfo=array();
		$count=$codeDB->where("status is null")->count();
		$pageInfo['totalPage']=floor($count/C("items_per_page"))+2;

		if(!isset($_GET['p']) or $_GET['p']<0){
			$pageInfo['currPage']=1;	
		}else{
			$pageInfo['currPage']=(int)$_GET['p'];
		}
		$queryStart=($pageInfo['currPage']-1)*C("items_per_page");
		// dump($count);
		$queryData=$codeDB->where("status is null")->order("id desc")->limit($queryStart,C("items_per_page"))->select();
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
		// $codeDB->
		$this->assign("pageInfo",$pageInfo);
		$this->assign("data",$resultData);
		// dump($resultData);
		$this->display();

	}

	public function vote(){
		$id=(int)$_REQUEST["id"];
		$vote=$_REQUEST["v"];
		if($id==0){msg(0,"非法ID");}
		if(!$vote=="yes" and !$vote=="no"){msg(0,"非法操作");}
		//重复投票检测，session控制
		$votedArr=session("voted")?session("voted"):array();
		if(in_array($id, $votedArr)){
			msg(2,"你已经为这个代码投过票了");
		}
		array_push($votedArr,$id);
		session("voted",$votedArr);
		//重复投票检测结束！！
		$codeDB=M("code");
		$ret=$codeDB->where("id=%d",$id)->select();
		if(!$ret){msg(0,"ID不存在");}
		if($vote=="yes"){
			$yes=$ret[0]["yes"]?$ret[0]["yes"]:0;
			$yes=$yes+1;
			$codeDB->where("id=%d",$id)->setField("yes",$yes);
			msg(1,"投票成功");
		}
		if($vote=="no"){
			$no=$ret[0]["no"]?$ret[0]["no"]:0;
			$no=$no+1;
			$codeDB->where("id=%d",$id)->setField("no",$no);
			msg(1,"投票成功");
		}
	}

	private function radonmlyGetCode(){
		$codeDB=M("code");
		$maxid=$codeDB->field("max(id)")->select();
		$maxid=$maxid[0]["max(id)"];
		$minid=$codeDB->field("min(id)")->select();
		$minid=$minid[0]["mind(id)"];
		$radom=$codeDB->query("select RAND()");
		$radom=$radom[0]["rand()"];
		$param=floor((float)$radom*((int)$maxid-(int)$minid)+(int)$minid);
		$param=(int)$param;
		$ret=$codeDB->where("id>=%d",$param)->order("id")->limit(1)->select();
		return $ret[0];
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

	public function test(){
		echo "测试语言";
		echo L("test");
	}
	
}
