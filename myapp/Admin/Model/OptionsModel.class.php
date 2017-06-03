<?php
namespace Admin\Model;
use Think\Model;
class OptionsModel extends Model{
	private $_db='';
	public function __construct(){
		$this->_db=M('options');
	}
	public function getSysConfig(){
		$fromDB= $this->_db->where('user=0')->select();
		$result=array();
		foreach ($fromDB as $key => $value) {
			$result[$value['op']]=$value['value'];
		}
		return $result;
	}

	public function getCachedSysConfig(){
		if(F('sysconfig')){
			$content=json_decode(F('sysconfig'),true);
			// dump($content);exit();
			if($content['timestamp']-time()>86400){
				self::generateCache();
				self::getCachedSysConfig();
			}else{
				return $content['sysconfig'];
			}
		}
	}

	public function getUserConfig($userid){
		if(!$userid){
			return 0;
		}
		$fromDB= $this->_db->where('user=%d',$userid)->select();
		$result=array();
		foreach ($fromDB as $key => $value) {
			$result[$value['op']]=$value['value'];
		}
		return $result;
	}

	public function updateConfig($userid,$data){
		if($userid || !$data){
			return 0;
		}else{
			$allOptions=$this->_db->field("op")->select();$availableOption=array();
			foreach ($allOptions as $i => $v) {
				array_push($availableOption, $v["op"]);
			}
			$result=array();
			foreach ($data as $key => $value) {
				if(in_array($key, $availableOption)){
					$result[$key]=$this->_db->where("user=%d and `op`='%s'",$userid,$key)->setField("value",$value);
				}
			}
			self::generateCache();
			return $result;
		}


	}

	public function generateCache(){
		F('sysconfig',json_encode(array('timestamp'=>time(),'sysconfig'=>$this->getSysConfig())));
	}
}