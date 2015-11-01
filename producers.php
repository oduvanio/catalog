<?php
/**
 * Выводит список производителей с количеством позиций
 */
namespace itlife\catalog;

use itlife\files\Xlsx;

$ans=array();
if(isset($_GET['seo'])){
	if(empty($_GET['link'])){
	    return infra_err($ans,'Wrong parameters');
	}
	$link=$_GET['link'];
	$link=$link.'/producers';
	$ans['external']='*catalog/seo.json';
	$ans['canonical']=infra_view_getPath().'?'.$link;
	return infra_ans($ans);
}
$fd=Catalog::initMark($ans);

if (isset($_GET['lim'])) {
	$lim = $_GET['lim'];
} else {
	$lim='0,20';
}
$p = explode(',', $lim);
if(sizeof($p)!=2){
	return infra_err($ans, 'Is wrong paramter lim');
}
$start = (int)$p[0];
$count = (int)$p[1];
$args=array($start, $count);
$list=Catalog::cache('producers.php', function ($start, $count) {
	$ans=array();
	$conf=infra_config();

	$data=Catalog::init();
	$prods=array();
	Xlsx::runPoss($data, function (&$pos) use (&$prods) {
		@$prods[$pos['Производитель']]++;
	});
	arsort($prods, SORT_NUMERIC);
	$prods=array_slice($prods, $start, $count);
	return $prods;
},$args,isset($_GET['re']));
$ans['menu']=infra_loadJSON('*catalog/menu.json');
$ans['list']=$list;

$conf=infra_config();
$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title'],'add'=>'group');
$ans['breadcrumbs'][]=array('href'=>'producers','title'=>'Производители');

return infra_ret($ans);
