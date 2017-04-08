<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016-03-03
 * Time: 21:38
 */
//建立数据库连接
$host='127.0.0.1';
$dbname='gg';
$dbuser='root';
$dbpass='3389';
$dsn='mysql:host='.$host.';dbname='.$dbname;
try{
    $d=new PDO($dsn,$dbuser,$dbpass);
}catch(PDOException $e){
    echo $e;
}

for($x=1;$x<5702;$x++) {
    $id=$x;
    $sql = "select url from imgs where id=? and height is null and cover=1";
    $process = $d->prepare($sql);
    $process->bindValue(1, $id, PDO::PARAM_INT);
    $process->execute();
    $res = $process->fetchAll(PDO::FETCH_ASSOC);
    if ($res) {
        $url = $res[0]['url'];
        $size = myGetImageSize($url);
        $ht = $size['height'];
        $wd = $size['width'];
        $upd = 'update imgs set height=?,width=? where id=?';
        $process2 = $d->prepare($upd);
        $process2->bindValue(1, $ht, PDO::PARAM_INT);
        $process2->bindValue(2, $wd, PDO::PARAM_INT);
        $process2->bindValue(3, $id, PDO::PARAM_INT);
        echo("正在处理id为" . $id . "的记录.<br>"."高度:".$ht."  宽度：".$wd);
        $res2 = $process2->execute();
        echo("  ".$res2."条记录添加成功<br>");
    } else {
        continue;
    };
}


echo("全部处理完毕");
$d=null;



function myGetImageSize($url, $type = 'curl', $isGetFilesize = false)
{
    // 若需要获取图片体积大小则默认使用 fread 方式
    $type = $isGetFilesize ? 'fread' : $type;

    if ($type == 'fread') {
        // 或者使用 socket 二进制方式读取, 需要获取图片体积大小最好使用此方法
        $handle = fopen($url, 'rb');

        if (! $handle) return false;

        // 只取头部固定长度168字节数据
        $dataBlock = fread($handle, 168);
    }
    else {
        // 据说 CURL 能缓存DNS 效率比 socket 高
        $ch = curl_init($url);
        // 超时设置
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        // 取前面 168 个字符 通过四张测试图读取宽高结果都没有问题,若获取不到数据可适当加大数值
        curl_setopt($ch, CURLOPT_RANGE, '0-3000');
        // 跟踪301跳转
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        // 返回结果
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $dataBlock = curl_exec($ch);

        curl_close($ch);

        if (! $dataBlock) return false;
    }

    // 将读取的图片信息转化为图片路径并获取图片信息,经测试,这里的转化设置 jpeg 对获取png,gif的信息没有影响,无须分别设置
    // 有些图片虽然可以在浏览器查看但实际已被损坏可能无法解析信息
    $size = getimagesize('data://image/jpeg;base64,'. base64_encode($dataBlock));
    if (empty($size)) {
        return false;
    }

    $result['width'] = $size[0];
    $result['height'] = $size[1];

    // 是否获取图片体积大小
    if ($isGetFilesize) {
        // 获取文件数据流信息
        $meta = stream_get_meta_data($handle);
        // nginx 的信息保存在 headers 里，apache 则直接在 wrapper_data
        $dataInfo = isset($meta['wrapper_data']['headers']) ? $meta['wrapper_data']['headers'] : $meta['wrapper_data'];

        foreach ($dataInfo as $va) {
            if ( preg_match('/length/iU', $va)) {
                $ts = explode(':', $va);
                $result['size'] = trim(array_pop($ts));
                break;
            }
        }
    }

    if ($type == 'fread') fclose($handle);

    return $result;
}

// 测试的图片链接
//echo '<pre>';
//$result = myGetImageSize('http://s6.mogujie.cn/b7/bao/120630/2kpa6_kqywusdel5bfqrlwgfjeg5sckzsew_345x483.jpg_225x999.jpg', 'curl');
//print_r($result);
//echo '<hr />';
//$result = myGetImageSize('http://s5.mogujie.cn/b7/bao/120629/6d3or_kqytasdel5bgevsugfjeg5sckzsew_801x1193.jpg', 'fread');
//print_r($result);
//echo '<hr />';
//$result = myGetImageSize('http://hiphotos.baidu.com/zhengmingjiang/pic/item/1c5f338c6d22d797503d92f9.jpg', 'fread', true);
//print_r($result);
//echo '<hr />';
//$result = myGetImageSize('http://www.vegandocumentary.com/wp-content/uploads/2009/01/imveganlogotransparentbackground.png', 'curl', true);
//print_r($result);
//echo '<hr />';
//$result = myGetImageSize('http://jiaoyou.ai9475.com/front/templates/jiaoyou/styles/default/image/ad_pic_1.gif', 'fread');
//print_r($result);