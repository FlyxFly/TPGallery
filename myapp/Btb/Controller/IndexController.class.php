<?php

namespace btb\Controller;
use Think\Controller;
import("ORG\Util\Page");

class IndexController extends Controller{
	public function Index(){
		$btbDB=M("btb");
		$btbcodeDB=M("btbcode");
		if(!isset($_GET['btborder']) and !isset($_GET['custId']) and !isset($_GET['itemId'])){
			
			// $this->assign("allCustomers",json_encode($distCust));
			// $this->assign("allItems",json_encode($distItem));
			$this->assign("data",null);
		}else{
			$condition=array();
			if(isset($_GET['custId']) and !$_GET['custId']==null){$condition['B']=$_GET['custId'];}
			if(isset($_GET['itemId']) and !$_GET['itemId']==null){$condition['AB']=$_GET['itemId'];}
			if(isset($_GET['btborder']) and !$_GET['btborder']==null){$condition['S']=$_GET['btborder'];}
			if(isset($_GET['unFinished']) and $_GET['unFinished']=="yes"){$condition['AU']=array("neq",0);}
			if(!$_GET['startDate']==null or !$_GET['endDate']==null){
				$startDate=$_GET['startDate'] or date("Y-m-d");
				$endDate=$_GET['endDate']?$_GET['endDate']:date("Y-m-d");
				$condition['R']=array(array('gt',$startDate),array('lt',$endDate));
			}
			$result=$btbDB->where($condition)->select();
			// dump($result);
			// exit();
			// $result=array();
			// if(isset($_POST['unFinished']) and $_POST['unFinished']=="yes"){
			// 	foreach ($result as $key => $value) {
			// 		if(!$value['au']==0){
			// 			array_push($result, $value);
			// 		}
			// 	}
			// }
			$this->assign("data",$result);
		}
		// dump($_REQUEST);
		$this->assign("query",$_REQUEST);
		$this->display();
	}

	public function b($btborder=null){
		$btbDB=M("btb");
		$btbcodeDB=M("btbcode");

		if($btborder==null){
			$this->assign("data",null);
			$this->display();
		}else{
			$result=$btbDB->where("S = $btborder")->select();
			$this->assign("data",$result);
			$this->display();
		}
	}

	public function fh($fhdh=null){
		$btbDB=M("btb");
		$btbcodeDB=M("btbcode");

		if($fhdh==null){
			$this->assign("data",null);
			$this->display();
		}else{
			$whereParam["BO"]=array("like","%".$fhdh."%");
			$result=$btbDB->where($whereParam)->select();
			$this->assign("data",$result);
			$this->display();
		}
	}

	public function transport(){
		if(isset($_GET['fhdh'])){
			$fhdh=$_GET['fhdh'];
			$fhdhArray=explode(',',$fhdh);
			$transResultArr=array();
			foreach ($fhdhArray as $key => $value) {
				$transResultArr[$value]=self::getTransportInfo($value);
			}
			// dump($transResultArr);
			$this->assign("transport",$transResultArr);
		}
		else{
			$this->assign("transport",null);
		}
		$this->display();
		
	}

	public function getTransportInfo($fhdh=null){
		if($fhdh==null){
			return null;
		}
		$url='http://tcp.gongsuda.com/gsdoms/account/maintain_web_query_noauth_order_list.action';
		$data=array(
			'searchText'=>$fhdh,
			'draw'=>1,
			'start'=>0,
			'length'=>11);
		$html = self::post($url,$data);
		$js=json_decode($html,true);
		// dump($js);
		return $js['data'][0];
	}

	public function getRoute(){
		$url="http://tcp.gongsuda.com/gsdoms/account/trace_web_query_noauth_dispath_order_traces.action";
		// $data=array(
		// 	'systemNo':GDGZ1609130216
		// 	'enterpriseNo':10001
		// 	'dispatchNo':DNLSO160913001710
		// 	'sendNo':GDGZ1609130216A
		// 	);

	}

	public function refreshData(){
		$btbDB=M("btb");
		$distCust=$btbDB->distinct(true)->field("B,C")->select();
		$distItem=$btbDB->distinct(true)->field("AB,AC")->select();
		self::saveFile("./Public/BTBdata/customers.json",json_encode($distCust));
		self::saveFile("./Public/BTBdata/items.json",json_encode($distItem));
		msg(1,"更新成功!");
	}

	public function getData(){
		if($_GET["type"]=="cust"){
			$filePath="./Public/BTBdata/customers.json";
		}
		if($_GET["type"]=="item"){
			$filePath="./Public/BTBdata/items.json";
		}

		$fileHandle=fopen($filePath,"r") or msg(0,"打开文件失败");
		$fileContent=fread($fileHandle,filesize($filePath));
		fclose($fileHandle);
		echo($fileContent);
	}

	private function saveFile($filePath,$content){
		$fileHandle=fopen($filePath,"w") or msg(0,"打开文件"+filePath+"失败");
		fwrite($fileHandle,$content);
		fclose($fileHandle);
	}

	private function post($url,$contentArr){
		if(!$url || !$contentArr){
			msg(0,"请检查post参数");
		}
		$postdata=http_build_query($contentArr);
		$opts=array(
			'http'=>array(
				'method'=>'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content'=>$postdata
				)
			);
		$context=stream_context_create($opts);
		$result=file_get_contents($url,false,$context);
		
		return $result;
	}

	public function getInventory(){
		if(!isset($_POST['code']) or strlen($_POST['code'])<5){
			msg(0,"数据不合法");
		}
		$invDB=M("inventory");
		$code=$_POST['code'];
		$result=$invDB->field(array("warehouse_name","place_name","item_code","item_name","factory_price","jda","batch","avai_qty","avai_box_qty"))->where("item_code = '%s' OR jda = '%s'",$code,$code)->select();
		if($result){
			msg(1,"操作成功",json_encode($result));
		}else{
			msg(1,"操作成功，但是没有查询到数据");
		}

	}
};