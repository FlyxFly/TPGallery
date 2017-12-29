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
		$tagidResult=$this->relation->where('video_id=%d and company_id=%d',array($videoid,$compid))->select();
		$result=array();
		if($tagidResult){
			foreach ($tagidResult as $key => $value) {
				$tagNameResult=$this->tag->where('tag_id=%d and company_id=%d',array($value['tag_id'],$value['company_id']))->select();
				array_push($result, $tagNameResult[0]);
			}
		}
		return  $result;
	}

	public function getVideosByTag($tagid,$compid,$p=1){
		return $this->relation->where('tag_id=%D and company_id=%d',array($tagid,$compid))->select();
	}

	public function getAllTags(){
		return $this->tag->distinct(true)->field('tag_name,tag_id,company_id')->select();
	}
}