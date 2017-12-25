<?php

function msg($code=0,$msg=null,$data=null){
	$result=array(
		"code"=>$code,
		"message"=>$msg,
		"data"=>$data);
	exit(json_encode($result,JSON_UNESCAPED_UNICODE));
}


function getSaltedMD5($string=null){
	if($string==null){
		exit('错误！md5函数接收到一个空值！');
	}
	return md5(md5($string).C('PASS_SALT'));
}


function getIp() {
      $unknown = 'unknown';
      if ( isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown) ) {
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif ( isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown) ) {
      $ip = $_SERVER['REMOTE_ADDR'];
	}
/*
处理多层代理的情况
或者使用正则方式：$ip = preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : $unknown;
*/
if (false !== strpos($ip, ','))
     $ip = reset(explode(',', $ip));
     return $ip;
}


function randomStr( $length = 13,$type='str' ) {  
	// 密码字符集，可任意添加你需要的字符 
	if($type=='str'){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;:/?|';
	}else{
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
	}
	

	$password ='';  
	for ( $i = 0; $i < $length; $i++ )  
	{  
	// 这里提供两种字符获取方式  
	// 第一种是使用 substr 截取$chars中的任意一位字符；  
	// 第二种是取字符数组 $chars 的任意元素  
	// $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);  
	$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
	}  
	return $password;  
} 


function semanticPage($totalPage,$currPage,$urlFuncParam){
	// 参数为总页数，当前页码，U方法生成url使用的参数，比如说"Admin/Index/Article?p="
	$html="<div class='ui right floated pagination menu'>";
	if($currPage!=1){
		$html.='<a href="'.U($urlFuncParam.(1)).'" class="icon item">1</a>';
		$html.='<a href="'.U($urlFuncParam.($currPage-1)).'" class="icon item"><i class="left chevron icon"></i></a>';
	}
	if($totalPage<10){
		for($i=1;$i<$totalPage;$i++){
			if($i==$currPage){
				$html.='<a href="'.U($urlFuncParam.$i).'" class="item active">'.$i.'</a>';
			}else{
				$html.='<a href="'.U($urlFuncParam.$i).'" class="item">'.$i.'</a>';
			}
			
		}
	}else{
		if($currPage<5){
			for($i=1;$i<=10;$i++){
				if($i==$currPage){
					$html.='<a href="'.U($urlFuncParam.$i).'" class="item active">'.$i.'</a>';
				}else{
					$html.='<a href="'.U($urlFuncParam.$i).'" class="item">'.$i.'</a>';
				}
			}
		}else if($totalPage-$currPage<5){
			for($i=$totalPage-9;$i<=$totalPage;$i++){
				if($i==$currPage){
					$html.='<a href="'.U($urlFuncParam.$i).'" class="item active">'.$i.'</a>';
				}else{
					$html.='<a href="'.U($urlFuncParam.$i).'" class="item">'.$i.'</a>';
				}
			}
		}else{
			for($i=$currPage-4;$i<=$currPage+5;$i++){
				if($i==$currPage){
					$html.='<a href="'.U($urlFuncParam.$i).'" class="item active">'.$i.'</a>';
				}else{
					$html.='<a href="'.U($urlFuncParam.$i).'" class="item">'.$i.'</a>';
				}
			}
		}
	}
	if($currPage<$totalPage){
		$html.='<a href="'.U($urlFuncParam.($currPage+1)).'" class="icon item"><i class="right chevron icon"></i></a>';
		
	}
	if($currPage<($totalPage-5)){
		$html.='<a href="'.U($urlFuncParam.$totalPage).'" class="icon item">'.$totalPage.'</a>';
	}
	$html.="</div>";
	return $html;
}


function semanticPageDirect($totalPage,$currPage,$urlFuncParam){
	// 参数为总页数，当前页码，"Admin/Index/Article?p="
	$html="<div class='ui right floated pagination menu'>";
	if($currPage!=1){
		$html.='<a href="'.U($urlFuncParam.(1)).'" class="icon item">1</a>';
		$html.='<a href="'.$urlFuncParam.($currPage-1).'" class="icon item"><i class="left chevron icon"></i></a>';
	}
	if($totalPage<10){
		for($i=1;$i<$totalPage;$i++){
			if($i==$currPage){
				$html.='<a href="'.$urlFuncParam.$i.'" class="item active">'.$i.'</a>';
			}else{
				$html.='<a href="'.$urlFuncParam.$i.'" class="item">'.$i.'</a>';
			}
			
		}
	}else{
		if($currPage<5){
			for($i=1;$i<=10;$i++){
				if($i==$currPage){
					$html.='<a href="'.$urlFuncParam.$i.'" class="item active">'.$i.'</a>';
				}else{
					$html.='<a href="'.$urlFuncParam.$i.'" class="item">'.$i.'</a>';
				}
			}
		}else if($totalPage-$currPage<5){
			for($i=$totalPage-9;$i<=$totalPage;$i++){
				if($i==$currPage){
					$html.='<a href="'.$urlFuncParam.$i.'" class="item active">'.$i.'</a>';
				}else{
					$html.='<a href="'.$urlFuncParam.$i.'" class="item">'.$i.'</a>';
				}
			}
		}else{
			for($i=$currPage-4;$i<=$currPage+5;$i++){
				if($i==$currPage){
					$html.='<a href="'.$urlFuncParam.$i.'" class="item active">'.$i.'</a>';
				}else{
					$html.='<a href="'.$urlFuncParam.$i.'" class="item">'.$i.'</a>';
				}
			}
		}
	}
	if($currPage<$totalPage){
		$html.='<a href="'.$urlFuncParam.($currPage+1).'" class="icon item"><i class="right chevron icon"></i></a>';
	}
	if($currPage<($totalPage-5)){
		$html.='<a href="'.U($urlFuncParam.$totalPage).'" class="icon item">'.$totalPage.'</a>';
	}
	$html.="</div>";
	return $html;
}