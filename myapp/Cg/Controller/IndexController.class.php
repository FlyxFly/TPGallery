<?php

namespace Cg\Controller;
use Think\Controller;

class IndexController extends Controller{
	public function index(){
		$layer  =   C('DEFAULT_C_LAYER');
        dump($layer);
	}
}