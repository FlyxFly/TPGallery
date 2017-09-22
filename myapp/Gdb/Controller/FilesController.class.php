<?php

namespace Gdb\Controller;
use Think\Controller;

class FilesController extends Controller{
	public function index($p=1,$keywords=null){
		$itemPerPage=20;
		$p=(int)$p>1?(int)$p:1;
		if($keywords){
			$like="filename like '%${keywords}%'";
			$ret=M('myfiles')->where($like)->page($p,$itemPerPage)->select();
			$count=M('myfiles')->where($like)->count();
			$pageGeneratorParam="Gdb/Files/Index?keywords=${keywords}&p=";
			$this->assign("keywords",$keywords);
		}else{
			$ret=M('myfiles')->order('createdate desc')->page($p,20)->select();
			$count=M('myfiles')->order('createdate desc')->count();
			$pageGeneratorParam="Gdb/Files/Index?p=";
		}
		// dump($pageGeneratorParam);
		foreach ($ret as $key=>$value) {
			$pathArray=explode('\\', $value['filepath']);
			$ret[$key]['filepath']=str_replace($value['filename'], '', $value['filepath']);

		}
		$this->assign("data",json_encode($ret));
		$this->assign("pageCode",semanticPage(ceil($count/$itemPerPage),$p,$pageGeneratorParam));
		$this->display('list');
	}
}