<?php

namespace Home\Controller;
use Think\Controller;
class MyController extends Controller{
	public function index(){
		if(!session('?user')){
		}
			$this->redirect('admin/login/index');
		$this->display();
	}
}