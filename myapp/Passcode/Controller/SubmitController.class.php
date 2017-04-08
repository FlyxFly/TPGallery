<?php
namespace Passcode\Controller;
use Think\Controller;
class SubmitController extends Controller{
	public function index(){
        if(isset($_REQUEST["codes"])){
            $codes=$_REQUEST["codes"];
            $codesArray=explode("\r\n",$codes);
            $checkResult=D("code")->ifExist($codesArray);
            $batchNo=rand(10000,1000001);
            session("submittedCode",$checkResult);
            // dump($checkResult);
            session("batchNo",$batchNo);
            $this->assign("batchNo",$batchNo);
            $this->assign("checkedData",$checkResult);
            
        }
        $this->display("submit");
	}

    /**
     *检查代码是否已经存在
     * 输入：字符串
     * 输出：布尔值
     */


    public function save(){
            if(isset($_POST['action']) and isset($_POST['batchNo']) and $_POST['action']=='savedata' and $_POST['batchNo']==session("batchNo")){
                $submittedCode=session('submittedCode');
                $ip=getIp();
                $saveResult=array();
                $codeDB=M("code");
                foreach ($submittedCode as $key => $value) {
                    if(!$value['result']){
                        $insert['passcode']=$value['code'];
                        $insert['ip']=$ip;
                        $insert['createdate']=date("Y-m-d");
                        $insert['status']='pending';
                        $result=$codeDB->add($insert);
                        array_push($saveResult, array("code"=>$value["code"],"result"=>$result));
                    }
                }
                msg(1,"操作成功",$saveResult);
            }else{
                msg(0,"非法访问");
            }
    }

    public function test(){
        echo date("Y-m-d");
    }

};