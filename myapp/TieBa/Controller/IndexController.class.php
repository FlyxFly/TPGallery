<?php

namespace Tieba\Controller;
use Think\Controller;
import ("ORG\UtilPage");

class IndexController extends Controller{
	public function Index(){
		$tiebaDB=M("tieba");
		$author=$tiebaDB->distinct(true)->field("author")->select();
		dump(count($author));
	}
}