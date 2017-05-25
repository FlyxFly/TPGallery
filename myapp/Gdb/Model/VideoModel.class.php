<?php
namespace Gdb\Model;
use Think\Model;

class VideoModel extends Model{
	private $_db='';
	private $relation='';
	private $actor='';
	private $items_every_page="";
	public function __construct(){
		$this->_db=M('video');
		$this->relation=M('relation');
		$this->actor=M('actor');
		$this->items_every_page=C('items_every_page');
	}

	public function getVideosByID($id=null,$compid=null){
		if(!$id or !$compid){
			return null;
		}
		$result=array();
		if(is_array($id)){
			foreach ($id as $key => $value) {
				$ret=$this->_db->where('internalid=%d and companyid=%d',array($value,$compid))->select();
				array_push($result, $ret[0]);
			}
			return $result;
		}else{
			return $this->_db->where('internalid=%d and company=%d',array($id,$compid))->select();
		}

	}

	public function getVideosByPage($p=1){
		$items_every_page=$this->items_every_page;
		$result['data']=$this->_db->order('releasedate desc')->page($p,$items_every_page)->select();
		$result['totalItem']=$this->_db->count('id');
		$result['totalPage']=ceil($result['totalItem']/$items_every_page);
		$result['currPage']=$p;
		// dump($result);
		return $result;
	}

	public function getVideoByUniqueId($id=0){
		return $this->_db->where('id=%d',array($id))->select();
	}

	public function getActorsByVideo($internalid,$companyid){
		// 根据视频id和厂家id，返回演员信息数组

		//根据厂商视频id和厂商id查找视频信息
		$ret=$this->_db->where('internalid =%d and companyid=%d',array($internalid,$companyid))->select();
		$result=array();
		//遍历视频信息，根据视频id和厂商id查找演员id
		if($ret){
			foreach ($ret as $key => $value) {
				$actors=$this->relation->where('internalvideoid=%d and companyid=%d',array($value['internalid'],$value['companyid']))->select();
				// 遍历演员id，获取演员信息
				foreach ($actors as $k => $actor) {

					$actorInfo=$this->actor->where('internalid=%d and companyid=%d',array($actor['internalactorid'],$actor['companyid']))->select();
					if($actorInfo){
						$result[]=$actorInfo[0];
					}
					
				}
			}
		}
		return $result;
	}
}