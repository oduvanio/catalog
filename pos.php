<?php

$data=cat_init(); // список всей продукции
$ans=array(//Оригинальные значения
	'val'=>$val,
	'prod'=>$prod,
	'type'=>$type,
	'art'=>$art
); // ?catalog имеется только type = rubrics, если имеется что-то после ?catalog, то type = search, art='',  val и prod = то что написано после ?catalog/
/*
	файл catalog.php реагирует на 4 параметра
	type - тип - их несколько и они определяются в общих чертах что должно вернуться и какие необходимы параметры из оставшихся


	//Далее строковые параметры, смысл которые определяется в type
	val - обычно это слово поиска, может быть название производителя, также есть слова исключения со специальной обработкой, например "Изменения"
	prod - обычно берётся из конфига и выполняет функцию фильтра по выбранному производителю
	art - обычно это артикул

	ответ возвращать пытаемся тандартизированным, например в type=search в ответе предусмотреы поля count, list, childs
	И при любом поиске или при странице группы или страницы производителя мы определяем эти стандартные значения. 
	И хотя страницы для пользователя визуально разные, но строятся они по похожим данным.

*/
$prod=infra_strtolower($prod);
$val=infra_strtolower($val);
$art=infra_strtolower($art);
if ($prod) {
	if (!xls_runPoss($data, function (&$pos) use ($prod) {
		if ($prod==infra_strtolower($pos['producer'])) {
			return true;
		}
	})) {
		$prod='';
	}
}
$ans['prod']=$prod; // записываем в массив ans производителя
if ($type=='pos') {
	/*echo 'Мы в pos';
	exit;*/
	$ans['pos']=false;
	/*echo $val;
	echo '<pre>';
	print_r($data);
	exit;*/
	$pos=&xls_runPoss($data, function (&$pos, $i, &$group) use (&$val, &$art) {
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
		/*echo '<pre>';
		var_dump($ans);
		exit;*/
		
		//$pos['images']=infra_load('*pages/list.php?src='.$conf['catalog']['dir'].$pos['Производитель'].'/'.$pos['article'].'/&onlyname=1&e=jpg,png,gif','fj');
		//$pos['text']=infra_load('*pages/get.php?src='.$conf['catalog']['dir'].$pos['Производитель'].'/'.$pos['article'].'/&onlyname=1&e=jpg,png,gif','fj');
	}
}
if ($type=='pos') {
	$ans['phone']=infra_loadJSON('*Телефон.json');
	$conf=infra_config();
	if ($ans['pos']) {
		$pos=$ans['pos'];
		xls_preparePosFiles($pos, $conf['catalog']['dir'], array('producer','article'));
		$files=explode(',', @$pos['Файлы']);
		foreach ($files as $f) {
			if (!$f) {
				continue;
			}
			$f=trim($f);
			xls_preparePosFiles($pos, $conf['catalog']['dir'].$f);
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
			infra_require('*files/files.inc.php');
			foreach ($pos['texts'] as $k => $t) {
				$pos['texts'][$k]=files_article($t);
			}
		}
		$ans['pos']=$pos;
	}
}
