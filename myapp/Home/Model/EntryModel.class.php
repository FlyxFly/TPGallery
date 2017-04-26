<?php
namespace Common\Model;
use Think\Model;
class EntryModel extends Model{
	private $entryDB='';
	private $imgsDB='';
	private $metaDB='';

	public function __construct(){
		$this->entryDB=M('entry');
		$this->imgDB=M('imgs');
		$this->metaDb=M('meta');
	}

	public function test(){
		dump($this->entryDB->select());
	}

	public function getPostImgCount($postid){
		return $this->imgsDB->where('postid=%d',$postid)->count();
	}
}