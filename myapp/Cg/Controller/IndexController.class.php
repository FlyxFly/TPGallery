<?php

namespace Cg\Controller;
use Think\Controller;

class IndexController extends Controller{
	public function index(){
		header("Charset:GBK");
		exec("python F:\kiwi\py\tieba_img.py",$opt);
		dump($opt);
	}
}