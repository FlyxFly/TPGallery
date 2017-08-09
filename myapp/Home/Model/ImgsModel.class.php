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

	public function getPostCountByCid($cid=0,$private=0){
		$cid=(int)$cid;
		$private=(int)$private;
        if($private=0){
        	$dataSQL="select * from entry where postid in (select pid from relationship where mid={$cid})";
       		$countSQL="select count(postid) from entry where postid in (select pid from relationship where mid={$cid})";
        }else{
        	$dataSQL="select * from entry where private=1 and postid in (select pid from relationship where mid={$cid})";
    	    $countSQL="select count(postid) from entry where private=1 and postid in (select pid from relationship where mid={$cid})";
        }
        $dataRet=M()->query($dataSQL);
        $countSQL=M()->query($countSQL);
        return []
	}
}
