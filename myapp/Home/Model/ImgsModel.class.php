<?php
namespace Home\Model;
use Think\Model;
class ImgsModel extends Model{
	private $_db='';
	public function __construct(){
		$this->_db=M('imgs');
	}

}
