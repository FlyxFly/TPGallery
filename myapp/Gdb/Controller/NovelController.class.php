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

	public function Catalog($p=1,$keywords=null){
		$totalItem=$this->i->count('threadid');
		$totalPage=ceil($totalItem/30);
		if($p<$totalPage){
			$ret=$this->i->page($p,30)->select();
			$this->assign('pageCode',semanticPage($totalPage,$p,'Gdb/Novel/Catalog?p='));
		}else{
			$ret=array(array('title'=>'非法页码'));
		}
		$this->assign('novelList',$ret);
		$this->display();
	}

	public function Content($tid=null,$p=1){
		$threadInfo=$this->i->where('threadid=%d',$tid)->select();
		$threadInfo=$threadInfo[0];
		$content=$this->c->where('threadid=%d',array($tid))->order('uniquepostid desc')->select();
		$this->assign('threadInfo',$threadInfo);
		$this->assign('novelTheads',$content);
		$this->display();
	}

	public function Search($keywords,$type='title',$p=1){
		if($keywords==''){
			$ret=array(array('title'=>'关键字为空'));
		}else{
			$sqlLike='%'.$keywords.'%';
			switch($type){
				case 'title':
				$totalItem=$this->i->where('title like "%s"',array($sqlLike))->count('threadid');
				$ret=$this->i->where('title like "%s"',array($sqlLike))->page($p,30)->select();
				break;

				case 'authorname':
				$totalPage=$this->i->where('authorname like "%s"',array($sqlLike))->count('threadid');
				$ret=$this->i->where('authorname like "%s"',array($sqlLike))->page($p,30)->select();
				break;

				case 'category':
				$totalPage=$this->i->where('category like "%s"',array($sqlLike))->count('threadid');
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
		$this->display('Catalog');
	}


}