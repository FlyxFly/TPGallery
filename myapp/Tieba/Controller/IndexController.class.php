<?php

namespace Tieba\Controller;
use Think\Controller;

class IndexController extends Controller{



    public function index($p=1,$keywords=null,$searchType=null,$ba=null){
        // dump(I('request.'));
    	if($keywords && in_array($searchType,C('search_type'))){
            $pageUrlParam="Tieba/Index/index?searchType=${searchType}&keywords=${keywords}&p=";
    		$ret=D('post')->imageList($p,$keywords,$searchType);
            $searchParams=['searchType'=>$searchType,'keywords'=>$keywords];
    	}else{
    		$ret=D('post')->imageList($p);
            $pageUrlParam="Tieba/Index/index?p=";
            $searchParams=['searchType'=>null,'keywords'=>null];
    	}
    	// dump($ret['data']);
        $isLogin=session('?user')?1:0;
        $this->assign('isLogin',$isLogin);
        // dump(in_array($searchType,C('search_type')));
    	$this->assign('data',json_encode($ret['data']));
        // dump(ceil($ret['total']/C('index_item_per_page')));
    	$this->assign('pageCode',semanticPage(ceil($ret['total']/C('index_item_per_page')),$p,$pageUrlParam));
        $this->assign('searchParams',json_encode($searchParams));
        $this->display();
    }

    public function thread($tid=null){
        if($tid===null){
            $this->redirect("Home/Index/index");
        }
        $ret=D('post')->threadInfo($tid);
        // dump($ret['data']);
        $this->assign('pageCode',semanticPage(ceil($ret['total']/C('post_per_page')),$p,$pageUrlParam));
        $this->assign('data',json_encode($ret['data']));
        $this->display();
    }

    public function api($action=null,$item=null,$id=0){
        header('Content-type: application/json');
        if(!session('?user')){
            msg(0,'Need log in!');
        }
        if(!in_array($action, ['delete'],false)){
            msg(1,'Wrong Action!');
        }
        if(!in_array($item, ['img','thread','user'],false)){
            msg(1,'Wrong item!');
        }
        if((int)$id<=0){
            msg(1,'Wrong ID!');
        }
        if($action='delete' && $item=='img'){
            echo D('post')->deleteImg($id);
        }
    }

    public function test($id){
        $ret=M('post')->where('thread_id=%s',$id)->select();
        dump($ret);
    }

    public function fileDelete($path){
        if(!session('?user')){
            msg(0,'非法操作');
        }
        $result=unlink($path);
        echo $result;
    }
}