<?php
namespace Gdb\Model;
use Think\Model;

class VideoModel extends Model{
	private $_db='';
	private $items_every_page="";
	public function __construct(){
		$this->_db=M('video');
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
}