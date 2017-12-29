<?php
namespace My\Controller;
use Think\Controller;
import("ORG\Util\Page");
class IndexController extends Controller{
	public function index(){
		$this->display();
	}
}