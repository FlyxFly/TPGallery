<?php
namespace Home\Model;
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

	public function getPost($p=1,$tagid=0,$private=0){
		$tagid=(int)$tagid;
		$private=(int)$private;
		$p=(int)$p?(int)$p:1;
		$entryPerPage=C("entryPerPage");
		$startItem=$entryPerPage*($p-1);
		if($tagid==0){
			if($private==0){
				$dataSQL="select * from entry left join imgs on entry.postid=imgs.postid where entry.private=0 and imgs.cover=1 ORDER BY entry.postid desc limit ${startItem},${entryPerPage}";
				$countSQL="select count(postid) from entry where private=0";
			}else{
				$dataSQL="select * from entry left join imgs on entry.postid=imgs.postid where imgs.cover=1 ORDER BY entry.postid desc limit ${startItem},${entryPerPage}";
				$countSQL="select count(postid) from entry";
			}
		}else{
			if($private==0){
				$dataSQL="select * from entry left join imgs on entry.postid=imgs.postid where imgs.cover=1 and entry.private=0 and entry.postid in (select pid from relationship where mid=${tagid}) ORDER BY entry.postid desc limit ${startItem},${entryPerPage}";
				$countSQL="select count(postid) from entry where postid in (select pid from relationship where mid=${tagid}) and entry.private=0";
			}else{
				$dataSQL="select * from entry left join imgs on entry.postid=imgs.postid where imgs.cover=1 and entry.postid in (select pid from relationship where mid=${tagid}) ORDER BY entry.postid desc limit ${startItem},${entryPerPage}";
				$countSQL="select count(postid) from entry where postid in (select pid from relationship where mid=${tagid})";
			}
		}
        $dataResult=M()->query($dataSQL);
        $countResult=M()->query($countSQL);
        return ["count"=>$countResult[0]['count(postid)'],"data"=>$dataResult];
        // return ["count"=>$countSQL,"data"=>$dataSQL];
	}

	public function getImgCount(){
		$ret=M()->query("select postid,count(url) from imgs group by postid");
		$result=[];
		foreach ($ret as $key => $value) {
			$result[$value['postid']]=$value['count(url)'];
		}
		return $result;
	}

	public function getCategoryIds(){
		$ret=M('meta')->field('mid')->where('type="category"')->select();
		$result=[];
		foreach ($ret as $value) {
			array_push($result, $value['mid']);
		}
		return $result;
	}

	public function getTagInfoById($tagid){
		$tagid=(int)$tagid;
		$ret=M('meta')->where("mid='%d'",$tagid)->select();
		return $ret[0];
	}

	public function addView($id){
		self::addLog('view','post',$id,null);
		$ret=M('entry')->where('postid=%d',$id)->field('view')->select();
		M('entry')->where('postid=%d',$id)->setField('view',$ret[0]['view']+1);
	}

	public function  addLike($id){
		self::addLog('like','post',$id,null);
		$ret=M('entry')->where('postid=%d',$id)->field('like')->select();
		M('entry')->where('postid=%d',$id)->setField('like',$ret[0]['like']+1);
	}

	public function addLog($action,$target_type,$target_id=null,$memo=null){
		$userInfo=session('user');
		$data=[
			'ip'=>getIp(),
			'action'=>$action,
			'target_type'=>$target_type,
			'target_id'=>$target_id,
			'memo'=>$memo,
			'timestamp'=>date('Y-m-d H:i:s')
		];
		if($userInfo){
			$data['user_id']=$userInfo['id'];
			$data['user_name']=$userInfo['username'];
		}
		$ret=M('log')->data($data)->add();
		return $ret;
	}
}
