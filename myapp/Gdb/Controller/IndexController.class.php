<?php
namespace Gdb\Controller;
use Think\Controller;
import("ORG\Util\Page");
class IndexController extends Controller{
	public function __construct(){
		parent::__construct();
	}

	public function Index(){
		$this->redirect('Index/Actor');
	}

	public function Actor($p=1){
		// dump($_GET);
		$imageHostPrefix=C('image_host_prefix');
		$imageDir=C('image_dir');
		$compIdtoImgSubDir=D('company')->getImgSubDir();
		if(isset($_GET['keywords']) && $_GET['keywords']!==''){
			$actorsInfo=D('actor')->search($_GET['keywords']);
			$this->assign('keywords',$_GET['keywords']);
		}else if(isset($_GET['id']) && $_GET['id']!==''){
			$actorInfo=D('actor')->getActorById($_GET['id']);
			$actorInfo=$actorInfo[0];
			if(!$actorInfo['birthday']==null){
					$actorInfo['age']=round((strtotime(date('Y-m-d'))-strtotime($actorInfo['birthday']))/3600/24/365);
			}
			$imgFileSuffix=explode('.', $actorInfo['localcover']);
			$actorInfo['localcover']='http://127.0.0.1'.U('Gdb/Index/img',array('url'=>$actorInfo['officialcover'],'type'=>'avatar','compId'=>$actorInfo['companyid']));
			$imgSubDirRet=M('company')->field('img_sub_dir')->where('id=%d',array($actorInfo['companyid']))->select();
			$imgSubDir=$imgSubDirRet[0]['img_sub_dir'];
			$actorVideoIDs=D('relation')->getVideosByActorID($actorInfo['internalid'],$actorInfo['companyid']);
			$actorVideoIDsArr=array();
			foreach ($actorVideoIDs as $key => $value) {
				array_push($actorVideoIDsArr, $value['internalvideoid']);
			}
			$actorVideoInfos=D('video')->getVideosByID($actorVideoIDsArr,$actorInfo['companyid']);
			foreach ($actorVideoInfos as $key => $value) {
				$actorVideoInfos[$key]['localcover']=U('img',array('type'=>'video','compId'=>$value['companyid'],'url'=>$value['listcover']));
				$actorVideoInfos[$key]['actorInfo']=array();
				$actorsId=D('relation')->getActorsIdByVideoId($value['internalid'],$value['companyid']);
				foreach ($actorsId as $k => $v) {
					$ret=D('actor')->getActorByInternalId($v['internalactorid'],$v['companyid']);
					array_push($actorVideoInfos[$key]['actorInfo'], $ret[0]);
					// dump($ret[0]);
				}
			}
			// dump($actorInfo);
			$this->assign('actorDetail',$actorInfo);
			$this->assign('companyName',D('company')->getCompName());
			$this->assign('actorVideoInfos',$actorVideoInfos);

		}else{
			$pageInfo=D('actor')->getActorsByPage($p);
			$actorsInfo=$pageInfo['data'];
			$this->assign('pagingCode',semanticPage($pageInfo['totalPage'],$p,"Gdb/Index/Actor?p="));
		}
		//封面图处理和年龄计算
		
		foreach ($actorsInfo as $key => $value) {
			$actorsInfo[$key]['localcover']='http://127.0.0.1/'.U('img',array('url'=>$value['officialcover'],'type'=>'avatar','compId'=>$value['companyid']));
			if(!$value['birthday']==null){
				$actorsInfo[$key]['age']=round((strtotime(date("Y-m-d"))-strtotime($value['birthday']))/3600/24/365);
			}
		}
		// 封面图处理和年龄计算结束
		// dump($actorsInfo);
		
		$this->assign('actorsInfo',$actorsInfo);
		$this->display();

		
	}

	public function videoSearch(){
		dump(D('tag')->getAllTags());
	}

	public function Video($p=1){
		$companyIdtoName=D('company')->getCompName();
		if(isset($_GET['id']) && $_GET['id']!==''){
			$ret=D('video')->getVideoByUniqueId($_GET['id']);
			$videoInfo=$ret[0];
			$videoInfo['actorsInfo']=array();
			$actorsId=D('relation')->getActorsIdByVideoId($ret[0]['internalid'],$ret[0]['companyid']);
			$tags=D('tag')->getTagsByVideo($videoInfo['id'],$videoInfo['companyid']);
			dump($tags);
			foreach ($actorsId as $key => $value) {
				$actor=D('actor')->getActorByInternalId($value['internalactorid'],$value['companyid']);
				array_push($videoInfo['actorsInfo'], $actor[0]);
			}
			$this->assign('videoDetail',$videoInfo);
			$videoGallery=json_decode($videoInfo['officialgallery']);
			foreach ($videoGallery as $key => $value) {
				$videoGallery[$key]=str_replace('300h', '1000w', $value);
			}
			$this->assign('videoGallery',$videoGallery);
		}else{
			$pageInfo=D('video')->getVideosByPage($p);
			$this->assign('pagingCode',semanticPage($pageInfo['totalPage'],$p,"Gdb/Index/Video?p="));
			$this->assign('videosInfo',$pageInfo['data']);
			// dump($pageInfo['data']);
		}
		
		
		
		
		$this->assign('companyIdtoName',$companyIdtoName);
		// dump($pageInfo['data']);
		$this->display();
	}

	public function img($type,$compId,$url=null,$force=0){
		if($force){

		}
		$urlArr=explode('/', $url);
		$filename=$urlArr[sizeof($urlArr)-1];
		$imageHostPrefix=C('img_local_dir');
		$compIdtoImgSubDir=D('company')->getImgSubDir();
		$path=$imageHostPrefix.$compIdtoImgSubDir[$compId].$type.'/'.$filename;
		// dump($path);
		// return;
		$image = fread(fopen($path,r),filesize($path));
		if($force){
			self::remoteDownload($url,$path);
		}else{
			if($image){
				header('Content-type: image/jpeg',true);
				echo $image;
				exit();
			}else if($url){
				self::remoteDownload($url,$path);
			}else{
				Header("HTTP/1.1 404 Not Found");
			}
		}

	}

	private function remoteDownload($url=null,$path=null){
		if(!$url || !$path){
			return null;
		}
		ob_start();
        readfile($url);
        $img=ob_get_contents();
        ob_end_clean();
		$fp2=@fopen($path,'a');
        fwrite($fp2,$img);
        fclose($fp2);
		$image = fread(fopen($path,r),filesize($path));
		header('Content-type: image/jpeg',true);
		echo $image;
	}

	public function imgtest(){
		$url='http://sm.staxus.com/content/contentthumbs/74489.jpg';
		$path='D:\dht\2.jpg';

		ob_start();
        readfile($url);
        $img=ob_get_contents();
        ob_end_clean();
		$fp2=@fopen($path,'a');
        fwrite($fp2,$img);
        fclose($fp2);;

		// $fgOption = array(
		//     'http' => array(
		//         'proxy' => 'http://127.0.0.1:19891',
		//         'request_fulluri' => true,
		//     ),
		// );

		// $fgContext = stream_context_create($fgOption);
		// $img = file_get_contents($url, False, $cxContext);


		// $fp2=@fopen($path,'a');
  //       fwrite($fp2,$img);
  //       fclose($fp2);;
		// $image = fread(fopen($path,r),filesize($path));
		header('Content-type: image/jpeg',true);
		echo $img;
        
       
	}

	public function test(){
		dump($_GET);
	}
}