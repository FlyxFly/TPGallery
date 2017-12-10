<?php

function PostURL($id=0){
	if(C('short_url')){
		return C('site_url').'boy/'.$id;
	}else{
		return U('Home/Index/post',['pid'=>$id]);
	}
}

