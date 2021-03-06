<?php

namespace Tieba\Model;
use Think\Model;

class PostModel extends Model{

    protected $tablePrefix = 'tieba_'; 

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
        $countSQL='select count(tieba_img.id) from tieba_img';
    	$resultObj=M('img');
    	if($keywords){
            $countSQL.=' left join tieba_post on tieba_img.post_id=tieba_post.post_id left join tieba_user on tieba_post.user_id=tieba_user.user_id';
    		switch($searchType){
    			case 'userName':
                $countSQL.=" where tieba_user.user_name like '%${keywords}%'";
    			$resultObj=$resultObj->where('tieba_user.user_name like "%s"','%'.$keywords.'%');
    			break;

    			case 'userId':
    			$countSQL.=" where tieba_user.user_id=${keywords}";
    			$resultObj=$resultObj->where('tieba_user.user_id=%d',$keywords);
    			break;

    			case 'content':
    			break;
    		}

    	}
    	
        $ret=$resultObj->join('left join tieba_post on tieba_img.post_id=tieba_post.post_id')->join('left join tieba_forum on tieba_post.forum_id = tieba_forum.forum_id')->join('left join tieba_user on tieba_post.user_id=tieba_user.user_id')->page($p,C('index_item_per_page'))->order('tieba_img.id desc')->select();
    	$totalItem=M()->query($countSQL);

        foreach ($ret as $key=>$value) {
        	$ret[$key]['file_name']=$this->urlConvert($value['file_name']);
        }
        return ['total'=>$totalItem[0]['count(tieba_img.id)'],'data'=>$ret];
    }

    public function threadInfo($threadId=null,$p=1){
        if($threadId==null){
            return null;
        }
        $imgTagPattern='/<img([\w\W]+?)>/';
        $total=M('post')->where('thread_id=%f',$threadId)->count();
        $ret=M('post')->where('thread_id=%f',$threadId)->page($p,C('post_per_page'))->join('left join tieba_user on tieba_user.user_id=tieba_post.user_id')->join('left join img on tieba_post.post_id=tieba_img.post_id')->join('left join tieba_forum on tieba_forum.forum_id=tieba_post.forum_id')->order('post_index')->select();
        
        foreach ($ret as $key => $value) {
            $ret[$key]['file_name']=$this->urlConvert($value['file_name']);
            $ret[$key]['content']=preg_replace($imgTagPattern, '', $value['content']);
            $ret[$key]['content']=$ret[$key]['content']."<img src='".$ret[$key]['file_name']."'>";
        }
        return ['total'=>$total,'data'=>$ret];
    }

    public function deleteImg($id){
        $imgInfo=M('img')->where('id=%s',$id)->select();
        if(!$imgInfo){
            return json_encode(['code'=>1,'message'=>'ID不存在']);
        }
        $samePost=M('img')->where('post_id=%s',$imgInfo[0]['post_id'])->select();
        $imgDBDeletion=M('img')->where('id=%s',$id)->delete();
        $imgFileDeletion=unlink($imgInfo[0]['file_name']);
        $tip='';
        if($imgDBDeletion>0){
            $tip.='图片已从数据库删除。';
        }else{
            $tip.='从数据库中删除图片失败，id：'.$id;
        }
        if($imgFileDeletion){
            $tip.='图片文件删除成功。';
        }else{
            $tip.='图片文件删除失败。请手动删除，路径：'.$imgInfo[0]['file_name'];
        }
        if(!$samePost){
            $postDeletion=M('post')->where('post_id=%s',$imgInfo[0]['post_id'])->delete();
            if($postDeletion>0){
                $tip.='图片对应发帖删除成功';
            }else{
                $tip.='图片对应发帖删除失败，id：'.$imgInfo[0]['post_id'];
            }
        }
        return json_encode(['code'=>200,'message'=>$tip]);
    }
}