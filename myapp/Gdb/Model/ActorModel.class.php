<?php
namespace Gdb\Model;
use Think\Model;
import("ORG\Util\Page");
class ActorModel extends Model{
	private $_db='';
	private $items_every_page='';
	public function __construct(){
		$this->_db=M('actor');
		$this->items_every_page=C('items_every_page');
	}

	public function radomGetOne(){
		return $this->_db->limit(1)->select();
	}

	public function getActorsByPage($p=1){
		$items_every_page=$this->items_every_page;
		$result['data']=$this->_db->page($p,$items_every_page)->select();
		$result['totalItem']=$this->_db->count('id');
		$result['totalPage']=ceil($result['totalItem']/$items_every_page);
		$result['currPage']=$p;
		// dump($result);
		return $result;
	}

	public function search($keyword,$p=1){
		return $this->_db->where("name like '%s'",'%'.$keyword.'%')->page($p,$this->items_every_page)->select();
	}

	public function getActorById($id=1){
		$ret=$this->_db->where("id=%d",$id)->select();
		if($ret){
			return $ret;
		}else{
			return $this->_db->where("internalid=%d",$id)->select();
		}
	}

	public function getActorByInternalId($id=null,$compid=null){
		return $this->_db->where('internalid=%d and companyid=%d',array($id,$compid))->select();
	}


}