<?php

namespace itlife\catalog;

use itlife\files\Xlsx;

$ans=array();

//На главной странице каталога показываются и может быть эти даные используются для показа групп на главной траницы

$data=Catalog::init();
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
$ans['menu']=infra_loadJSON('*catalog/rubrics.json');
return infra_ret($ans);
