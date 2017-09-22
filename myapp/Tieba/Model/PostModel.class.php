<?php

namespace Tieba\Model;
use Think\Model;

class PostModel extends Model{

	public function urlConvert($filepath=null){
		if($filepath==null){
			return null;
		}
		$pathArray=explode('/', $filepath);
		$fileName=$pathArray[count($pathArray)-1];
		$subFolder=$pathArray[count($pathArray)-2];
		return C('img_url_prefix').$subFolder.'/'.$fileName;
	}
    public function imageList($p=1,$keywords=null,$searchType=null){
        $countSQL='select count(img.id) from img left join post on img.post_id=post.post_id left join user on post.user_id=user.id';
    	$resultObj=M('img');
    	if($keywords){
    		switch($searchType){
    			case 'userName':
                $countSQL.=" where user.user_name like '%${keywords}%'";
    			$resultObj=$resultObj->where('user.user_name like "%s"','%'.$keywords.'%');
    			break;

    			case 'userId':
    			$countSQL.=" where user.id=${keywords}";
    			$resultObj=$resultObj->where('user.id=%d',$keywords);
    			break;

    			case 'content':
    			break;
    		}

    	}
    	
        $ret=$resultObj->join('left join post on img.post_id=post.post_id')->join('left join forum on post.forum_id = forum.forum_id')->join('left join user on post.user_id=user.id')->page($p,C('index_item_per_page'))->order('img.id desc')->select();
    	$totalItem=M()->query($countSQL);

        foreach ($ret as $key=>$value) {
        	$ret[$key]['file_name']=$this->urlConvert($value['file_name']);
        }
        return ['total'=>$totalItem[0]['count(img.id)'],'data'=>$ret];
    }

    public function threadInfo($threadId=null,$p=1){
        if($threadId==null){
            return null;
        }
        $total=M('post')->where('thread_id=%f',$threadId)->count();
        $ret=M('post')->where('thread_id=%f',$threadId)->page($p,C('post_per_page'))->join('left join user on user.id=post.user_id')->join('left join img on post.post_id=img.post_id')->join('left join forum on forum.forum_id=post.forum_id')->order('post_index')->select();
        // foreach ($ret as $key=>$value) {
        //     $ret[$key]['content']=preg_replace($, replace, subject)
        // }
        return ['total'=>$total,'data'=>$ret];
    }


}