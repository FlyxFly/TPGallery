<?php
namespace Gdb\Model;
use Think\Model;

class CompanyModel extends Model{
	private $_db='';
	public function __construct(){
		$this->_db=M('company');
	}

	public function getImgSubDir(){
		$ret=$this->_db->field('id,img_sub_dir')->select();
		$result=array();
		foreach ($ret as $key => $value) {
			$result[$value['id']]=$value['img_sub_dir'];
		}
		return $result;
	}

	public function getCompName(){
		$ret=$this->_db->field('id,name')->select();
		$result=array();
		foreach ($ret as $key => $value) {
			$result[$value['id']]=$value['name'];
		}
		return $result;
	}
}