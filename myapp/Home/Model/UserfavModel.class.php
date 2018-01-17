<?php
namespace Home\Model;
use Think\Model;
class UserfavModel extends Model{
    private $userfavDB='';

    public function __construct(){
        $this->$userfavDB=M('user_fav',Null);
    }

    public function add($type=null,$itemId=0,$description='',$userId=0){
        $updateTime=date('Y-m-d H:m:s',time());
        $insertData=[
            'type'=>$type,
            'description'=>$description,
            'item_id'=>$itemId,
            'user_id'=>$userId,
            'update_time'=>$updateTime,
        ];
        $ret=$this->$userfavDB->add($insertData);
        return $ret;
    }

    public function delete($recordId=0){
        $select=$this->$userfavDB->where('id=%d',$recordId)->select();
        if(!$select){
            return true;
        }else{
            $ret=$this->$userfavDB->delete($recordId);
            if($ret){
                return true;
            }else{
                return false;
            }
        }
    }

    public function search($onlyThisType='',$descKeyword='',$p=1){
        if($onlyThisType){
            $ret=$this->$userfavDB->where('type=%s',$onlyThisType)->page($p,30)->select();
            return $ret;
        }

        if($descKeyword){
            $ret=$this->$userfavDB->where('description like %s','%'.$onlyThisType.'%')->page($p,30)->select();
            return $ret;
        }


        return '';
    }
}