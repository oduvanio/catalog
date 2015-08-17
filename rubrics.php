<?php

namespace itlife\catalog;

use itlife\files\Xlsx;

infra_admin_modified();

$ans=array();

Catalog::getFilter($ans);
//На главной странице каталога показываются и может быть эти даные используются для показа групп на главной траницы

$data=infra_loadJSON('*catalog/search.php?m='.$ans['m']);

$ans['childs']=$data['childs'];
/*$data=Catalog::init();
$data=$data['childs'];

foreach ($data as $k => &$gr) {
	$pos=&Xlsx::runPoss($gr, function &(&$pos) {
		Xlsx::addFiles($pos);
		if (!$pos['images']) {
			return;
		}
		return $pos;
	});

	if ($pos) {
		unset($gr['desrc']);
		unset($gr['childs']);
		unset($gr['data']);

		$gr['pos']=array('images'=>$pos['images'],'article' => $pos['article'],'producer' => $pos['Производитель']);
	} else {
		unset($data[$k]);
	}
}
$ans['childs']=array_values($data);
/*
$ans['menu']=infra_loadJSON('*catalog/menu.json');
$ans['breadcrumbs']=array();
$conf=infra_config();
$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title']);
*/
return infra_ret($ans);
