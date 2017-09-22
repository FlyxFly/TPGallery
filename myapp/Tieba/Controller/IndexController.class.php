<?php

namespace Tieba\Controller;
use Think\Controller;

class IndexController extends Controller{



    public function index($p=1,$keywords=null,$searchType=null,$ba=null){
    	if($keywords && in_array($searchType,C('search_type'))){
            $pageUrlParam="Tieba/Index/index?searchType=${searchType}&keywords=${keywords}&p=";
    		$ret=D('post')->imageList($p,$keywords,$searchType);
            $searchParams=['searchType'=>$searchType,'keywords'=>$keywords];
    	}else{
    		$ret=D('post')->imageList($p);
            $pageUrlParam="Tieba/Index/index?p=";
            $searchParams=['searchType'=>null,'keywords'=>null];
    	}
    	// dump($searchType);
        
        // dump(in_array($searchType,C('search_type')));
    	$this->assign('data',$ret['data']);
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
        dump($ret);
        $this->assign('pageCode',semanticPage(ceil($ret['total']/C('post_per_page')),$p,$pageUrlParam));
        $this->assign('data',json_encode($ret['data']));
        $this->display();
    }
}