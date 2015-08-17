<?php
/**
 * Выводит список производителей с количеством позиций
 */
namespace itlife\catalog;

use itlife\files\Xlsx;

$ans=array();
Catalog::getFilter($ans);

infra_admin_modified();



$list=Catalog::cache('producers.php', function () {
	$ans=array();
	$conf=infra_config();
	
	$data=Catalog::init();
	$prods=array();
	Xlsx::runPoss($data, function (&$pos) use (&$prods) {
		@$prods[$pos['Производитель']]++;
	});
	arsort($prods, SORT_NUMERIC);
	return $prods;
});
$ans['menu']=infra_loadJSON('*catalog/menu.json');
$ans['list']=$list;

return infra_ret($ans);
