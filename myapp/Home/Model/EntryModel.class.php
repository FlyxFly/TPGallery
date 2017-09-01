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
}
