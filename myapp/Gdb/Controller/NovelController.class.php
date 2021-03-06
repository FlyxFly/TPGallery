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
		$this->fav=D('stnovel_fav');
		$optionsModel  = new \Admin\Model\OptionsModel();
    	$this->options=$optionsModel->getCachedSysConfig();
    	$this->userFav=new \Home\Model\UserfavModel();
	}
	public function Index(){
		$this->redirect('Catalog');
	}


	public function catalog($p=1,$keywords=null,$authorID=null){
		if($authorId){
			$totalItem=$this->i->where('del is null')->where('authorid=%d',$authorId)->count('threadid');
		}else{
			$totalItem=$this->i->where('del is null')->count('threadid');
		}
		$totalPage=ceil($totalItem/30);
		if($p<$totalPage){
			if($authorid){
				$ret=$this->i->where('del is null')->where('authorid=%d',$authorid)->page($p,30)->select();
			}else{
				$ret=$this->i->where('del is null')->page($p,30)->order('threadid desc')->select();
			}
			$this->assign('pageCode',semanticPageDirect($totalPage,$p,'/novels/p/'));
		}else{
			$ret=array(array('title'=>'非法页码'));
		}
		$this->assign("statCode",$this->options['statcode']);
		$this->assign('novelList',$ret);
		$this->display();
	}

	public function content($tid=null,$p=1){
		if(!session('?user')){
			$this->redirect('admin/login/index');
		};
		// dump(session('user')['id']);
		// dump($this->i->query('show variables like "%character%"'));
		$threadInfo=$this->i->where('threadid=%d',$tid)->select();
		// dump($threadInfo);
		$threadInfo=$threadInfo[0];
		$content=$this->c->where('threadid=%d',array($tid))->order('uniquepostid')->select();

		$authorArticles=$this->i->where('authorid=%d',$threadInfo['authorid'])->order('threadid desc')->select();
		$filteredAuthorArticles=[];
		foreach ($authorArticles as $key => $value) {
			if($value['threadid']!=$tid){
				array_push($filteredAuthorArticles, $value);
			}
		}
		// dump($filteredAuthorArticles==[]);
		$this->assign('authorArticles',$filteredAuthorArticles);
		// dump($content);
		foreach ($content as $key => $value) {

			// $value=mb_convert_encoding($value, "UTF-8", "ASCII");
			$addNewline=str_replace('\r\n', '<br/>', $value);
			$addNewline=str_replace('/\s{3,}/', '<br/>', $addNewline);
			$nonbsp=preg_replace('/\&nbsp;{3,}/', '<br/>&nbsp;&nbsp;', $addNewline);
			$content[$key]=preg_replace('/\s{3,}/', '<br/>', $nonbsp);

			// $content[$key]=nl2br($value);

		}
		$this->assign("statCode",$this->options['statcode']);
		$this->assign('threadInfo',$threadInfo);
		$this->assign('novelTheads',$content);
		$this->display();
	}

	public function Search($keywords=null,$type='title',$authorId=null,$p=1,$fav=0){
		if($keywords=='' && $authorId==null && $fav==0){
			$ret=array(array('title'=>'关键字为空'));
		}else if($fav==1){
			if(session('?user')){
				$userInfo=session('user');
				$userId=$userInfo['id'];
				$totalItem=$this->fav->where('user_id=%d',$userId)->count('thread_id');
				$ret=$this->fav->where('user_id=%d',$userId)->join('left join stnovel_info on stnovel_info.threadid = stnovel_fav.thread_id')->page($p,30)->select();
			}else{
				$totalItem=$this->fav->count('thread_id');
				$ret=$this->fav->join('left join stnovel_info on stnovel_info.threadid = stnovel_fav.thread_id')->page($p,30)->select();
			}
			
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
			// $this->assign('pageCode',semanticPage($totalPage,$p,'Gdb/Novel/Search?$type='.$type."&keywords=".$keywords.'&p='));
			switch($type){
				case 'title':
				$this->assign('pageCode',semanticPageDirect($totalPage,$p,'/novels/title/'.$keywords.'/'));
				break;

				case 'author':
				$this->assign('pageCode',semanticPageDirect($totalPage,$p,'/novels/author/'.$authorID.'/'));
				break;

				case 'tag':
				$this->assign('pageCode',semanticPageDirect($totalPage,$p,'/novels/tag/'.$keywords.'/'));
				break;
			}
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
		header('Content-type:application/json');
		if(!session('?user')){
			msg(0,'登陆了才能操作哦');
		}
		$userInfo=session('user');

		$ret=M('user_fav')->where('type="novel" and item_id=%d and user_id=%d',[$tid,$userInfo['id']])->select();

		// $ret=$this->fav->where('thread_id = %d and user_id = %d',[$tid,$userInfo['id']])->select();
		$ret=$ret[0];
		if(count($ret)<1){
			$description=$this->i->where('threadid=%d',$tid)->getField('title');
			// dump($this->userFav);
			$this->userFav->add('novel',$tid,$description,$userInfo['id']);
			// $this->fav->data(['thread_id'=>$tid,'user_id'=>$userInfo['id'],'add_date'=>date("Y-m-d H:i:s")])->add();
			msg(200,'加入收藏成功');
		}else{
			$sql=$this->userFav->delete($ret['id']);
			// msg(0,json_encode($sql));
			msg(200,'取消收藏成功');
		}
	}

	public function api($action=null,$tid){
		switch($action){
			case 'delete':
			$ret=$this->i->where('threadid = %d',$tid)->setField('del',1);
			if($ret){
				msg(200,'删除成功');
			}else{
				msg(0,'删除失败');
			}
			break;


			default:
			msg('0','未定义的操作');
			break;
		}
	}

	public function test(){
		$uf=new \Home\Model\UserfavModel();
		$ret=$uf->add('entry',123,'bala bala',2);
		dump($ret);
	}


}