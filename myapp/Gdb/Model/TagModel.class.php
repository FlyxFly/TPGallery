<?php
namespace Gdb\Model;
use Think\Model;
import("ORG\Util\Page");
class TagModel extends Model{
	private $tag='';
	private $relation='';
	public function __construct(){
		$this->tag=M('tag');
		$this->relation=M('tag_relation');
	}

	public function getTagsByVideo($videoid,$compid){
		return $this->relation->fetchSQL(true)->where('video_id=%d and company_id=%d',array($videoid,$compid))->select();
	}

	public function getVideosByTag($tagid,$compid,$p=1){
		return $this->relation->where('tag_id=%D and company_id=%d',array($tagid,$compid))->select();
	}

	public function getAllTags(){
		return $this->tag->distinct(true)->field('tag_name,tag_id,company_id')->select();
	}
}