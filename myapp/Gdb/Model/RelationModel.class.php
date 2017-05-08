<?php
namespace Gdb\Model;
use Think\Model;

class RelationModel extends Model{
	private $_db='';
	public function __construct(){
		$this->_db=M('relation');
	}

	public function getVideosByActorID($actorid=0,$compid=0){
		return $this->_db
		->field('internalvideoid')
		->where('internalactorid=%d and companyid=%d',array($actorid,$compid))
		->select();
	}

	public function getActorsIdByVideoId($videoid=0,$compid=0){
		return $this->_db
		->field('internalactorid,companyid')
		->where('internalvideoid = %d and companyid= %d',array($videoid,$compid))
		->select();
	}

	
}