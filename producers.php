<?php
/**
 * Выводит список производителей с количеством позиций
 */
infra_require('*files/xls.php');
infra_require('*catalog/catalog.inc.php');
$type=infra_strtolower(@$_GET['type']);
infra_admin_modified();
$ans=infra_cache(array($conf['catalog']['dir']), 'producers.php', function ($type) {
	$ans=array();
	$conf=infra_config();
	if ($type=='producers') {
		$data=cat_init();
		$prods=array();
		xls_runPoss($data, function (&$pos) use (&$prods) {
			@$prods[$pos['Производитель']]++;
		});
		arsort($prods, SORT_NUMERIC);
		$ans['producers']=$prods;
	}
	return $ans;
}, array($type), isset($_GET['re']));

return infra_err($ans, 'Wrong type');
