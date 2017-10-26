<?php

namespace Gdb\Controller;
use Think\Controller;
class DnsController extends Controller{
	public function index(){
		if(isset($_GET['domain']) && $_GET['domain']!=''){
			$this->assign('data',dns_get_record($_GET['domain']));
			$this->assign('query',$_GET['domain']);
		}
		// dump(dns_get_record($_GET['domain']));
		$this->display();
	}

	public function GetRecord($domain=null){
		if($domain==null){
			return null;
		}else{
			$result = dns_get_record($domain);
			dump($result);
		}
	}
}