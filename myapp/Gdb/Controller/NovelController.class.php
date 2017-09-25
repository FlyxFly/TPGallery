<?php
namespace Gdb\Controller;
use Think\controller;
import("ORG\Util\Page");
class NovelController extends Controller{
	protected $i='';
	protected $c='';
	public function __construct(){
		parent::__construct();
		$this->i=D('stnovel_info');
		$this->c=D('stnovel_content');
	}
	public function Index(){
		$this->redirect('Catalog');
	}

	public function catalog($p=1,$keywords=null,$authorID=null){
		if($authorId){
			$totalItem=$this->i->where('authorid=%d',$authorId)->count('threadid');
		}else{
			$totalItem=$this->i->count('threadid');
		}
		$totalPage=ceil($totalItem/30);
		if($p<$totalPage){
			if($authorid){
				$ret=$this->i->where('authorid=%d',$authorid)->page($p,30)->select();
			}else{
				$ret=$this->i->page($p,30)->select();
			}
			
			$this->assign('pageCode',semanticPage($totalPage,$p,'Gdb/Novel/Catalog?p='));
		}else{
			$ret=array(array('title'=>'非法页码'));
		}
		$this->assign('novelList',$ret);
		$this->display();
	}

	public function content($tid=null,$p=1){
		$threadInfo=$this->i->where('threadid=%d',$tid)->select();
		$threadInfo=$threadInfo[0];
		$content=$this->c->where('threadid=%d',array($tid))->order('uniquepostid')->select();
		foreach ($content as $key => $value) {
			$addNewline=str_replace('\r\n', '<br/>', $value);
			$nonbsp=preg_replace('/\&nbsp;{3,}/', '<br/>&nbsp;&nbsp;', $addNewline);
			$content[$key]=preg_replace('/\s{3,}/', '<br/>', $nonbsp);
			// $content[$key]=nl2br($value);

		}
		$this->assign('threadInfo',$threadInfo);
		$this->assign('novelTheads',$content);
		$this->display();
	}

	public function Search($keywords=null,$type='title',$authorId=null,$p=1,$fav=0){
		if($keywords=='' && $authorId==null && $fav==0){
			$ret=array(array('title'=>'关键字为空'));
		}else if($fav==1){
			$totalItem=$this->i->where('fav=1')->count('threadid');
			$ret=$this->i->where('fav=1')->page($p,30)->select();
		}else{
			$sqlLike='%'.$keywords.'%';
			switch($type){
				case 'title':
				$totalItem=$this->i->where('title like "%s"',array($sqlLike))->count('threadid');
				$ret=$this->i->where('title like "%s"',array($sqlLike))->page($p,30)->select();
				break;

				case 'author':
				$totalItem=$this->i->where('authorid = "%d"',array($authorId))->count('threadid');
				$ret=$this->i->where('authorid = "%d"',array($authorId))->page($p,30)->select();
				break;

				case 'category':
				$totalItem=$this->i->where('category like "%s"',array($sqlLike))->count('threadid');
				$ret=$this->i->where('category like "%s"',array($sqlLike))->page($p,30)->select();
				break;

				default:
				$ret=array(array('title'=>'非法操作'));
				break;
			}
			
			
		}
		// dump($ret);
		$totalPage=ceil($totalItem/30);
		// dump($totalPage);
		if($totalPage){
			$this->assign('pageCode',semanticPage($totalPage,$p,'Gdb/Novel/Search?$type='.$type."&keywords=".$keywords.'&p='));
		}
		
		$this->assign('searchKeywords',$keywords);
		$this->assign('novelList',$ret);
		$this->display('catalog');
	}

	public function Random(){
		$ret=$this->i->order('rand()')->limit(20)->select();
		$this->assign('novelList',$ret);
		$this->assign('pageCode','<a class="ui button primary" href="javascript:window.scrollTo(0,0);location.reload()">刷新</a>');
		$this->display('catalog');
	}

	public function Mark($tid){
		$ret=$this->i->where('threadid = %d',$tid)->select();
		header('Content-type:application/json');
		if($ret[0]['fav']!=1){
			$this->i->where('threadid = %d',$tid)->setField('fav','1');
			msg(200,'加入收藏成功');
		}else{
			$this->i->where('threadid = %d',$tid)->setField('fav','0');
			msg(200,'取消收藏成功');
		}
		
	}


}