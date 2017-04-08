<?php

namespace Home\Controller;
use Think\Controller;
class MytestController extends Controller{
	public function test(){
		$this->show("这是Mytest控制器里面的test方法");
	}
}