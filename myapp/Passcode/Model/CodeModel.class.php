<?php

namespace Passcode\Model;
use Think\Model;

class CodeModel extends Model{
    /**
     * @param $code,string or array
     * return,array(code,bool)
     */
    public function ifExist($code){
        $codeDB=M("code");
        $result=array();
        if(is_array($code)){
            foreach ($code as $item) {
                $codeData=$codeDB->where("passcode ='%s'",$item)->select();
                array_push($result,array('code'=>$item,'result'=>$codeData));
            }
            return $result;
        }else{
            $result['code']=$code;
            $result['result']=$codeDB->getByPasscode($code);
            return $result;
        }
    }

	public function randomlyGetOne(){
		$codeDB=M("code");
		$maxid=$codeDB->field("max(id)")->select();
		$maxid=$maxid[0]["max(id)"];
		$minid=$codeDB->field("min(id)")->select();
		$minid=$minid[0]["mind(id)"];
		$radom=$codeDB->query("select RAND()");
		$radom=$radom[0]["rand()"];
		$param=floor((float)$radom*((int)$maxid-(int)$minid)+(int)$minid);
		$param=(int)$param;
		$ret=$codeDB->where("id>=%d",$param)->order("id")->limit(1)->select();
		return $ret[0];	
	}

    public function save(){
        
    }

}