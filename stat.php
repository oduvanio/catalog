<?php
namespace itlife\catalog;

$ans=array();
if(isset($_GET['seo'])){
	if(empty($_GET['link'])){
	    return infra_err($ans,'Wrong parameters');
	}
	$link=$_GET['link'];
	$link=$link.'/stat';
	$ans['external']='*catalog/seo.json';
	$ans['canonical']=infra_view_getPath().'?'.$link;
	return infra_ans($ans);
}
$ans['menu']=infra_loadJSON('*catalog/menu.json');
$submit=!empty($_GET['submit']); // сбор статистики

$conf=infra_config();
$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title'],'add'=>'group');
$ans['breadcrumbs'][]=array('href'=>'stat','title'=>'Статистика поиска');

$dirs=infra_dirs();
$dir=$dirs['data'];
$data=infra_loadJSON($dir.'catalog_stat.json');
if (!$data) {
	$data=array('users' => array(),'cat_id' => 0,'time' => time());//100 10 user list array('val'=>$val,'time'=>time())
}

if (!$submit) {
	$conf=infra_config();
	$ans['text']=infra_loadTEXT('*files/get.php?'+$conf['catalog']['dir'].'/articals/stat');
	$ans['stat']=$data;
	return infra_ret($ans);
}


$val=strip_tags(@$_GET['val']);
if (!$val) {
	return infra_err($ans, 'Incorrect parameters');
}
infra_cache_no();
$val=infra_forFS($val);
$val=infra_toutf($val);
$id=infra_view_getCookie('cat_id');
$time=infra_view_getCookie('cat_time');
if (!$time||!$id||$time!=$data['time']) {
	$id=++$data['cat_id'];
	infra_view_setCookie('cat_id', $id);
	infra_view_setCookie('cat_time', $data['time']);
}
$ans['cat_id']=$id;
$ans['cat_time']=$time;

$user=array('cat_id'=>$id,'list'=>array(),'time'=>time());
foreach ($data['users'] as $k => $v) {
	if ($v['cat_id']==$id) {
		$user=$v;
		unset($data['users'][$k]);
		break;
	}
}
$data['users']=array_values($data['users']);

foreach ($user['list'] as $k => $v) {
	if ($v['val']==$val) {
		unset($user['list'][$k]);
		break;
	}
}
$user['list']=array_values($user['list']);
$search=infra_loadJSON('*catalog/search.php?val='.$val);
array_unshift($user['list'], array('val' => $val,'time' => time(),'count' => $search['count']));

if (sizeof($user['list'])>10) {
	$user['list']=array_slice($user['list'], 0, 10);
}
array_unshift($data['users'], $user);

if (sizeof($data['users'])>100) {
	$data['users']=array_slice($data['users'], 0, 50);
}
file_put_contents($dir.'catalog_stat.json', infra_json_encode($data));
$ans['data']=$data;

return infra_ret($ans);
