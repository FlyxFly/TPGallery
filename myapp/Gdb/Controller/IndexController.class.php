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

	public function actor($p=1){
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
			// $actorInfo['localcover']=U('Gdb/Index/img',array('url'=>$actorInfo['officialcover'],'type'=>'avatar','compId'=>$actorInfo['companyid']));
			$actorInfo['localcover']=self::urlQuery(array('url'=>$actorInfo['officialcover'],'type'=>'avatar','companyId'=>$actorInfo['companyid']));
			$imgSubDirRet=M('company')->field('img_sub_dir')->where('id=%d',array($actorInfo['companyid']))->select();
			$imgSubDir=$imgSubDirRet[0]['img_sub_dir'];
			$actorVideoIDs=D('relation')->getVideosByActorID($actorInfo['internalid'],$actorInfo['companyid']);
			$actorVideoIDsArr=array();
			foreach ($actorVideoIDs as $key => $value) {
				array_push($actorVideoIDsArr, $value['internalvideoid']);
			}
			$actorVideoInfos=D('video')->getVideosByID($actorVideoIDsArr,$actorInfo['companyid']);
			foreach ($actorVideoInfos as $key => $value) {
				// $actorVideoInfos[$key]['localcover']=U('img',array('type'=>'video','compId'=>$value['companyid'],'url'=>$value['listcover']));
				$actorVideoInfos[$key]['localcover']=self::urlQuery(array('type'=>'video','companyId'=>$value['companyid'],'url'=>$value['listcover']));
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
			// $actorsInfo[$key]['localcover']=U('img',array('url'=>$value['officialcover'],'type'=>'avatar','compId'=>$value['companyid']));
			$actorsInfo[$key]['localcover']=self::urlQuery(array('url'=>$value['officialcover'],'type'=>'avatar','companyId'=>$value['companyid']));
			if(!$value['birthday']==null){
				$actorsInfo[$key]['age']=round((strtotime(date("Y-m-d"))-strtotime($value['birthday']))/3600/24/365);
			}
		}
		// 封面图处理和年龄计算结束
		// dump($actorsInfo);
		$this->assign('gdbImgServer',C('gdb_img_server'));
		$this->assign('actorsInfo',$actorsInfo);
		$this->display();

		
	}

	public function videoSearch(){
		dump(D('tag')->getAllTags());
	}

	public function video($p=1){
		$companyIdtoName=D('company')->getCompName();
		if(isset($_GET['id']) && $_GET['id']!==''){
			$ret=D('video')->getVideoByUniqueId($_GET['id']);
			$videoInfo=$ret[0];
			$videoInfo['actorsInfo']=array();
			$actorsId=D('relation')->getActorsIdByVideoId($ret[0]['internalid'],$ret[0]['companyid']);
			$tags=D('tag')->getTagsByVideo($videoInfo['internalid'],$videoInfo['companyid']);
			// dump($actorsId);
			$videoInfo['tags']=$tags;
			foreach ($actorsId as $key => $value) {
				$actor=D('actor')->getActorByInternalId($value['internalactorid'],$value['companyid']);
				// dump($value);
				array_push($videoInfo['actorsInfo'], $actor[0]);
			}
			$this->assign('tagColors',array('red','orange','yellow','olive','green','teal','blue','violet','purple','pink','brown','gery','grey','black'));
			$this->assign('videoDetail',$videoInfo);
			// dump($videoInfo);
			$videoGallery=json_decode($videoInfo['officialgallery']);
			// dump($videoInfo['officialgallery']);
			// dump($videoGallery);
			
			foreach ($videoGallery as $key => $value) {
				$videoGallery[$key]=str_replace('300h', '1000w', $value);
			}
			$this->assign('videoGallery',$videoGallery);
		}else if(isset($_GET['keywords']) && $_GET['keywords']!==''){
			$ret=M('video')->where("name like '%s'",array('%'.$_GET['keywords'].'%'))->select();
			$this->assign('videosInfo',$ret);
		}else{
			$pageInfo=D('video')->getVideosByPage($p);
			$this->assign('pagingCode',semanticPage($pageInfo['totalPage'],$p,"Gdb/Index/Video?p="));
			$this->assign('videosInfo',$pageInfo['data']);
			// dump($pageInfo['data']);
		}
		$this->assign('gdbImgServer',C('gdb_img_server'));
		$this->assign('companyIdtoName',$companyIdtoName);
		// dump($pageInfo['data']);
		$this->display();
	}

	public function Mark($id,$message){
		header('Content-type: application/json; charset=urf-8',true);
		if(!session('user')){
			msg(0,'无权限');
		}
		if($id>0){
			switch($message){
				case 'downloaded':
					$ret=M('video')->where('id=%d',array($id))->setField('have',1);
					if($ret){
						msg(200,'Update Success!');
					}else{
						msg(1,'Server Error or Already Marked');
					}
				break;

				case 'notDownloaded':
					$ret=M('video')->where('id=%d',array($id))->setField('have',0);
						if($ret){
							msg(200,'Update Success!');
						}else{
							msg(1,'Server Error or Already Marked');
						}

				default:
				msg(0,'Illegal Message');
				
			}
		}else{
			msg(0,'Illegal Id');
		}
		
	}

	public function videoList($p=1){
		$videosInfo=D('video')->getVideosByPage($p);
		// $videosInfo['data'][$key]['actorsInfo']=D('video')->getActorsByVideo($value['internalid'],$value['companyid']);
		foreach ($videosInfo['data'] as $key => $value) {
			$videosInfo['data'][$key]['actorsInfo']=D('video')->getActorsByVideo($value['internalid'],$value['companyid']);
			// dump($videosInfo['data'][$key]['actorsInfo']);
			// $actorsId=D('relation')->getActorsIdByVideoId($value['internalid'],$value['companyid']);
			// foreach ($actorsId as $actorKey => $actor) {
			// 	$videosInfo['data'][$key]['actorsInfo']=D('actor')->getActorByInternalId($actor['internalactorid'],$actor['companyid']);
			// 	// dump($videosInfo['data'][$key]['actorsInfo']);
			// 	// dump($actor);
			// }
		}
		// dump($videosInfo);
		$this->assign('videosInfo',json_encode($videosInfo));
		$companyName=D('company')->getCompName();
		// dump($companyName);
		$this->assign('allActors',json_encode(M('actor')->select()));
		$this->assign('companyName',json_encode($companyName));
		$this->assign('pagingCode',semanticPage($videosInfo['totalPage'],$p,"Gdb/Index/VideoList?p="));
		// dump($videosInfo);
		$this->display('list');
	}

	public function add(){
		if(!session('user')){
			msg(0,'无权限');
		}
		$po=$_POST;
		if($po['type']=='video'){
			$ret=M('video')->add($po);
			msg('200','Save success',$ret);
		}else if($po['type']=='actor'){
			$result=array();
			$result['actorDB']=M('actor')->add($po);
			$companyid=$po['companyid'];
			$videoid=$po['internalid'];
			if(count($po['actors'])>1){
				foreach ($po['actors'] as $key => $value) {
					$relationData=array(
					'internalactorid'=>$value,
					'internalvideoid'=>$videoid,
					'companyid'=>$companyid);
					$result['relationDB']=M('relation')->add($relationData);
				}
			}else{
				$relationData=array(
					'internalactorid'=>$po['actors'][0],
					'internalvideoid'=>$videoid,
					'companyid'=>$companyid);
					$result['relationDB']=M('relation')->add($relationData);
			}
			
			msg('200','Save success',$result);
		}else{
			msg('0','Action not defined;');
		}
		
	}

	public function RealtimeSearch($keywords){
		$resultVideos=array();
		$searchByVideoName=M('video')->where("name like '%s'",array('%'.$keywords.'%'))->select();

		$searchByActorName=M('actor')->where("name like '%s'",array('%'.$keywords.'%'))->select();
		if($searchByActorName){
			$searchByActorNameVideo=array();
			foreach ($searchByActorName as $key => $value) {
				$ret=M('relation')->where('internalactorid = %d and internalvideoid = %d',array($value['internalid'],$value['companyid']))->select();
				foreach ($ret as $k => $v) {
					$actorInfo=M('video')->where('internalid =%d and companyid=%d',array($v['internalvideoid'],$v['companyid']))->select();
					$searchByActorNameVideo[]=$actorInfo[0];
				}
			}
			$result=array_merge($searchByVideoName,$searchByActorNameVideo);
		}else{
			$result=$searchByVideoName;
		}
		echo json_encode($result);

	}

	public function img($type,$compId,$url=null,$force=0){
		$urlArr=explode('/', $url);
		$filename=$urlArr[sizeof($urlArr)-1];
		$imageHostPrefix=C('gdb_img_dir');
		$compIdtoImgSubDir=D('company')->getImgSubDir();
		$path=$imageHostPrefix.$compIdtoImgSubDir[$compId].$type.'/'.$filename;
		// dump($path);
		// return;
		
		// echo 234;
		if($force==1){
			self::remoteDownload($url,$path);
		}else{
			if(filesize($path)>0){
				$image = fread(fopen($path,r),filesize($path));
				header('Content-type: image/jpeg',true);
				echo $image;
				return;
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

	private function urlQuery($param=array()){
		$url=C("gdb_img_server");
		// $url="https://gg.d-hl.com/img";
		if($url==null || $param==array()){
			return false;
		}
		$url.="?";
		foreach ($param as $key => $value) {
			$url.=$key."=".$value."&";
		}
		return $url;
	}

	public function search($type=null,$content=null,$p=1){
		if(!in_array($type, ['video','actor','tag','novel'])){
			msg(1,'无法找到搜索结果');
		}
		if($content==null){
			msg(1,'未找到搜索结果');
		}

		switch($type){
			case 'video':
			$total=M('video')->where('name like "%s"','%'.$content.'%')->count('id');
			$ret=M('video')->where('name like "%s"','%'.$content.'%')->page($p,30)->select();
			break;

			case 'actor':
			$total=M('actor')->where('name like "%s"','%'.$content.'%')->count('id');
			$ret=M('actor')->where('name like "%s"','%'.$content.'%')->page($p,30)->select();
			break;

			case 'tag':

			break;

			case 'novel':
			$total=M('stnovel_info')->where('title like "%s"','%'.$content.'%')->count('threadid');
			$ret=M('actor')->where('title like "%s"','%'.$content.'%')->page($p,30)->select();
			break;
		}
		dump($total);
		dump($ret);
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
		// $videoid=4186;
		// $compid=1;
		// $tagidResult=M('tag_relation')->where('video_id=%d and company_id=%d',array($videoid,$compid))->select();
		// $result=array();
		// if($ret){
		// 	foreach ($tagidResult as $key => $value) {
		// 		$tagNameResult=M('tag')->where('tag_id=%d and company_id=%d',array($value['tag_id'],$value['company_id']))->select();
		// 		array_push($result, $tagNameResult);
		// 	}
		// }
		// echo json_encode($tagidResult)  ;
		echo C("gdb_img_server");
	}
}