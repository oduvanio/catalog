<?php
/**
 * Выводит список производителей с количеством позиций
 */
use itlife\files\Xlsx;

infra_require('*catalog/catalog.inc.php');

infra_admin_modified();

$list=cat_cache('producers.php', function () {
	$ans=array();
	$conf=infra_config();
	
	$data=cat_init();
	$prods=array();
	Xlsx::runPoss($data, function (&$pos) use (&$prods) {
		@$prods[$pos['Производитель']]++;
	});
	arsort($prods, SORT_NUMERIC);
	return $prods;
});

$ans['list']=$list;

return infra_ret($ans);
