<?php
infra_require('*catalog/catalog.inc.php');
$conf=infra_config();

$type=infra_strtolower(@$_GET['type']);
$val=strip_tags(@$_GET['val']);
$art=strip_tags(@$_GET['art']);
$re=isset($_GET['re']);
$prod=(string)$_REQUEST['prod'];
$prod=strip_tags($prod);
$args=array($type,$val,$art,$prod);

if ($type=='stat') { // ?contacts  type=rubrics, поэтому эту проверку пока пропускаем, проверим, когда $type будет stat
	// сбор статистики поиска по каталогу и отображение статистики
	if (!empty($_GET['submit'])) {
		$submit=true; // сбор статистики
	} else {
		$submit=false; // отображение статистики
	}
	$ans=array('result'=>1);
	$dirs = infra_dirs();
	$dir=$dirs['data'];

	$data=infra_loadJSON($dir.'catalog_stat.json');
	if ($submit) {
		if (!$val) {
			return infra_ans($ans);
		}
		infra_cache_no();
		$val=infra_forFS($val);
		if (!$data) {
			$data=array('users'=>array(),'cat_id'=>0,'time'=>time());
		}
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
		$search=infra_loadJSON('*catalog/catalog.php?type=search&val='.$val);
		$count=sizeof($search['list']);
		array_unshift($user['list'], array('val' => $val,'time' => time(),'count' => $count));

		if (sizeof($user['list'])>10) {
			$user['list']=array_slice($user['list'], 0, 10);
		}
		array_unshift($data['users'], $user);

		if (sizeof($data['users'])>100) {
			$data['users']=array_slice($data['users'], 0, 50);
		}
		file_put_contents($dir.'catalog_stat.json', infra_json_encode($data));
		$ans['data']=$data;
		return infra_ans($ans);
	}
}

$type=infra_strtolower($ans['type']);
	$art=infra_strtolower($ans['art']);
	$val=infra_strtolower($ans['val']);
	if (isset($_GET['submit'])&&$_GET['submit']) {
		$submit=true;
	} else {
		$submit=false;
	}
if ($type=='stat'&&!$submit) {
	$dirs=infra_dirs();
	$dir=$dirs['data'];
	$data=infra_loadJSON($dir.'catalog_stat.json');
	if (!$data) {
		$data=array('users' => array(),'cat_id' => 0,'time' => time());//100 10 user list array('val'=>$val,'time'=>time())
	}
	$ans['text']=infra_loadTEXT('*files/files.php?type=texts&id=Статистика поиска по каталогу&show');
	//time
	//Поиск, Поиск, Поиск
	$ans['stat']=$data;
	return $ans;
}