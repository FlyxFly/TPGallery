<?php
namespace Admin\Model;
use Think\Model;
class LogModel extends Model{
	public function getLog($p=1,$type=null,$keywords=null){
		$qqwry=new \Common\Lib\qqwry;
		$db=M('log')->order('id desc')->page($p,20);
		if($keywords){
			$db->where('%s like "%s"',array($type,'%'.$keywords.'%'));
		}
		$ret=$db->select();
		$total=M('log');
		if($keywords){
			$total->where('%s like "%s"',array($type,'%'.$keywords.'%'));
		}
		$totalCount=$total->count('id');
		if(count($ret)>0){
			foreach ($ret as $key => $value) {
				$ipAddr=$qqwry->getlocation($value['ip']);
				$ret[$key]['address']=self::gbkToUTF($ipAddr['country']).self::gbkToUTF($ipAddr['area']);
			}
		}
		return ['total'=>$totalCount,'data'=>$ret];
	}

	public function gbkToUTF($str){
		return iconv('GB2312','UTF-8',$str);
	}
}
