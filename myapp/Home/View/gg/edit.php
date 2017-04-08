<?php
header("Content-Type:text/html;Charset=utf-8");

try{
    $a=$_GET[$i];
}catch(error){
}
if($a){
    echo $a;
}else{
    echo "i不存在";
}