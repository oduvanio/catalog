<?php

namespace itlife\catalog;

$ans=array();

//На главной странице каталога показываются и может быть эти даные используются для показа групп на главной траницы

$data=Catalog::init();
$data=$data['childs'];

foreach ($data as $k => &$gr) {
	$pos=&xls_runPoss($gr, function &(&$pos) {
		$conf=infra_config();
		xls_preparePosFiles($pos, $conf['catalog']['dir'], array('producer','article'));
		if (!$pos['images']) {
			return;
		}
		return $pos;
	});

	if ($pos) {
		unset($gr['desrc']);
		unset($gr['childs']);
		unset($gr['data']);

		$gr['pos']=array('article' => $pos['article'],'producer' => $pos['Производитель']);
	} else {
		unset($data[$k]);
	}
}
$ans['childs']=$data;