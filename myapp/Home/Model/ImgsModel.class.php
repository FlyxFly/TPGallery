<?php
namespace Common\Model;
use Think\Model;
class ImgsModel extends Model{
	private $_db='';
	public function __construct(){
		$this->_db=M('imgs');
	}

	public function getPostImgCount($postid=0){
		return $this->_db->where('postid = %d',$postid)->count();
	}


}
