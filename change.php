<?php
namespace itlife\catalog;

use itlife\files\Xlsx;

infra_admin_modified();

$ans=array();
$menu=infra_loadJSON('*catalog/rubrics.json');
$ans['is']='change';
$ans['result']=1;
$ans['title']=$menu['change']['title'];
$ans['descr']=$menu['change']['descr'];
$ans['name']=$menu['change']['title'];


$poss=Catalog::cache('change.php', function () {
	$data=Catalog::init();
	//Смотрим дату изменения папки для каждой позиции кэшируем на изменение XLS файлов как всё здесь...
	//И дату изменения файлов в папке
	//Позиции без папок игнорируются
	$poss=array();
	Xlsx::runPoss($data, function (&$pos) use (&$poss) {
		$conf=infra_config();
		$dir=infra_theme($conf['catalog']['dir'].$pos['producer'].'/'.$pos['article'].'/');
		if (!$dir) {
			return;
		}
		$pos['time']=filemtime($dir);

		array_map(function ($file) use (&$pos, $dir) {
			if ($file{0}=='.') {
				return;
			}
			$t=filemtime($dir.$file);
			if ($t>$pos['time']) {
				$pos['time']=$t;
			}
		}, scandir($dir));
		$poss[]=&$pos;
	});


	usort($poss, function ($a, $b) {
		if ($a['time']==$b['time']) {
			return 0;
		}
		return ($a['time']>$b['time'])?-1:1;
	});
	return $poss;
});

$ans['list']=array_slice($poss, 0, 30);
$ans['count']=sizeof($ans['list']);

return infra_ret($ans);
