<?php

namespace itlife\catalog;

use itlife\files\Xlsx;

$orig_val=infra_toutf(strip_tags($_GET['val']));
$orig_art=infra_toutf(strip_tags($_GET['art']));
$val=infra_strtolower($orig_val);
$art=infra_strtolower($orig_art);
$prod=infra_toutf(infra_strtolower(strip_tags($_GET['prod'])));

$data=Catalog::init(); // список всей продукции
$ans=array(//Оригинальные значения
	'val'=>$val,
	'prod'=>$prod,
	'art'=>$art
);
$ans['breadcrumbs']=array();//Путь где я нахожусь
$conf=infra_config();
$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title']);
//if (empty($_GET['m'])) {
//	$_GET['m']='';
//}
//$mark=Catalog::getMark($_GET['m']);
//$ans['m']=$mark->getMark();
//$ans['m']=$_GET['m'];

/*
	val - обычно это слово поиска, может быть название производителя, также есть слова исключения со специальной обработкой, например "Изменения"
	prod - обычно берётся из конфига и выполняет функцию фильтра по выбранному производителю
	art - обычно это артикул

	ответ возвращать пытаемся тандартизированным, например в type=search в ответе предусмотреы поля count, list, childs
	И при любом поиске или при странице группы или страницы производителя мы определяем эти стандартные значения.
	И хотя страницы для пользователя визуально разные, но строятся они по похожим данным.

*/

if ($prod) {
	if (!Xlsx::runPoss($data, function (&$pos) use ($prod) {
		if ($prod==infra_strtolower($pos['producer'])) {
			return true;
		}
	})) {
		$prod='';
	}
}
$ans['prod']=$prod; // записываем в массив ans производителя

$ans['pos']=false;

$pos=&Xlsx::runPoss($data, function (&$pos, $i, &$group) use (&$val, &$art) {
	if (infra_strtolower($pos['producer'])!==$val) {
		return;
	}
	if (infra_strtolower($pos['article'])!==$art) {
		return;
	}
	//$pos['path']=$group['path'];
	return $pos;
});
if ($pos) {
	$ans['result']=1;
	$ans['pos']=&$pos;
	$ans['path']=$pos['path'];

}

if ($ans['pos']) {
	$pos=$ans['pos'];
	Xlsx::addFiles($pos);
	$files=explode(',', @$pos['Файлы']);
	foreach ($files as $f) {
		if (!$f) {
			continue;
		}
		$f=trim($f);
		$conf=infra_config();
		Xlsx::addFiles($pos, $conf['catalog']['dir'].$f);
	}

	$files=array();
	foreach ($pos['files'] as $f) {
		if (is_string($f)) {
			$f = infra_theme($f); //убрали звездочку
			$d=infra_srcinfo(infra_toutf($f));
		} else {
			$d=$f;
			$f=$d['src'];
		}

		$d['size']=round(filesize(infra_tofs($f))/1000000, 2);
		if (!$d['size']) {
			$d['size']='0.01';
		}
		$files[]=$d;
	}
	$pos['files']=$files;
	if ($pos['texts']) {
		foreach ($pos['texts'] as $k => $t) {
			$pos['texts'][$k]=infra_loadTEXT('*files/get.php?'.$t);
		}
	}
	$ans['pos']=$pos;
	array_map(function($p) use (&$ans){
			$ans['breadcrumbs'][]=array('href'=>$p,'title'=>$p);
	}, $pos['path']);
	$ans['breadcrumbs'][]=array('href'=>$orig_val,'add'=>'producer:'.$orig_val, 'title'=>$orig_val);
	$ans['breadcrumbs'][]=array('href'=>$orig_art,'title'=>$orig_art);
	return infra_ret($ans);
} else {
	$ans['breadcrumbs'][]=array('href'=>'producers','title'=>'Производители');
	$ans['breadcrumbs'][]=array('href'=>$orig_val,'title'=>$orig_val);
	$ans['breadcrumbs'][]=array('href'=>$orig_art,'title'=>$orig_art);
	return infra_err($ans);
}
