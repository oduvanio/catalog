<?php
/**
 * Страница "search" формируется из блоков
 */

namespace itlife\catalog;

use itlife\files\Xlsx;

infra_admin_modified();

$val=infra_forFS(infra_toutf(strip_tags($_GET['val'])));

$sval=infra_strtolower($val);

$ans=array();

$fd=Catalog::getFilter($ans);


if (isset($_GET['page'])) {
	$ans['page']=(int)$_GET['page'];
	if ($ans['page']<1) {
		$ans['page']=1;
	}
} else {
	$ans['page']=1;
}


//1
$ans['is']=''; //group producer search Что было найдено по запросу val (Отдельный файл is:change)
$ans['descr']='';//абзац текста в начале страницы';
$ans['text']=''; //большая статья снизу всего
$ans['name']=''; //заголовок длинный и человеческий

$ans['breadcrumbs']=array();//Путь где я нахожусь
$ans['val']=$val;//Запрос поиска
$ans['title']=$val;//Что именно было найдено название для FS


$ans['filteroptions']=array();//Данные для формирования интерфейса фильтрации, опции и тп

$ans['groups']=array();
$ans['producers']=array();

$ans['numbers']=array(); //Данные для построения интерфейса постраничной разбивки
$ans['list']=array(); //Массив позиций


/*
	уже есть val, mark, filterdata
	--cache sval filterdata с исключением
		--cache sval 
			1 poss - найти все позиции согласно требованию val
			Определить is, descr, text, name, breadcrumbs
		--
		2 filteroptions - проанализирвать poss и сформировать 
		3 groups, producers - собрать, отдельно так как меняют val и вообще это самая главная навигация
		4 Применить filterdata к poss и получить list
	--
	5 Посчитать pages отсортировать, урезать
*/


$del = array('sort', 'page', 'direct', 'count');

$args=array($sval, array_diff_key($fd, array_flip($del)));
$res=Catalog::cache('search.php filter list', function ($sval, $fd) use ($val) {
	$ans=array();
	$args=array($sval);
	$res=Catalog::cache('search.php just list', function ($sval) use ($val) {
		$ans=array();
		

		$data=Catalog::init();

		//CHANGE
		if ($val == 'change') {
			$poss=array();
			Xlsx::runPoss($data, function (&$pos) use (&$poss) {
				$poss[]=&$pos;
			});
			$ans['list']=$poss;
			$ans['is']='change';
			$ans['title']="Изменения";
			$ans['descr']="Последнии позиций, у которых изменился текст полного описания.";
			$ans['name']="Изменения";
			$ans['list']=array_filter($ans['list'], function (&$pos) {
				$conf=infra_config();
				$dir=infra_theme($conf['catalog']['dir'].$pos['producer'].'/'.$pos['article'].'/');
				if (!$dir) {
					return false;
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
				return true;
			});
			usort($ans['list'], function ($a, $b) {
				if ($a['time']==$b['time']) {
					return 0;
				}
				return ($a['time']>$b['time'])?-1:1;
			});
			$conf=infra_config();
			$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title']);
			$menu=infra_loadJSON('*catalog/menu.json');
			$ans['breadcrumbs'][]=array('href'=>'change','title'=>$menu['change']['title']);
			return $ans;
		}
		//Группа
		if (!$sval) {
			$group=$data;
		} else {
			$group = &Xlsx::runGroups($data, function (&$group) use (&$sval) {
				if (infra_strtolower($group['title'])==$sval) {
					return $group;
				}
			});
		}
		if ($group) {
			//is!, descr!, text!, name!, breadcrumbs!, title
			$ans['is']='group';
			$conf=infra_config();
			$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title']);
			array_map(function ($p) use (&$ans) {
				$ans['breadcrumbs'][]=array('href'=>$p,'title'=>$p);
			}, $group['path']);
			if (sizeof($ans['breadcrumbs'])==1) {
				unset($ans['breadcrumbs']);
			}

			$ans['name']=$group['name'];//имя группы длинное
			$ans['descr']=@$group['descr']['Описание группы'];
			$ans['title']=$group['title'];

			$poss=array();
			Xlsx::runPoss($group, function (&$pos) use (&$poss) {
				$poss[]=&$pos;
			});
			$ans['list']=$poss;
			return $ans;
		}

		//ПРОИЗВОДИТЕЛЬ
		$dir=infra_theme(CATDIR.$val.'/');
		$poss=array();
		Xlsx::runPoss($data, function (&$pos) use (&$poss, &$sval) {
			if (infra_strtolower(@$pos['producer'])==$sval) {
				$poss[]=&$pos;
			}
		});
		if ($dir||sizeof($poss)) {
			//is!, descr!, text!, name!, breadcrumbs!
			$ans['is']='producer';
			$ans['list']=$poss;
			if (sizeof($poss)) {
				$name=$poss[0]['producer'];
			} else {
				$dir=infra_toutf($dir);
				$p=explode('/', $dir);
				$folder=$p[sizeof($p)-2];
				$name=$folder;
			}
			
			$ans['descr']='';
			$ans['name']=$name;
			$ans['title']=$name;
			$conf=infra_config();
			$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title']);
			$menu=infra_loadJSON('*catalog/menu.json');
			$ans['breadcrumbs'][]=array('href'=>'producers','title'=>$menu['producers']['title']);
			$ans['breadcrumbs'][]=array('href'=>$name,'title'=>$name);

			return $ans;
		}

		//ПОИСК
		//ищим позиции подходящие под запрос
		//is!, descr, text, name, breadcrumbs
		$ans['is']='search';
		$ans['name']=$val;
		$poss=array();
		$v=explode(' ', $sval);
		Xlsx::runPoss($data, function (&$pos) use (&$v, &$poss) {
			$str=$pos['Артикул'];
			$str.=' '.implode(' ', $pos['path']);
			$str.=' '.$pos['article'];
			$str.=' '.$pos['Наименование'];
			$str.=' '.$pos['Производитель'];
			$str.=' '.$pos['producer'];
			$str.=' '.$pos['Описание'];
			if (!empty($pos['more'])) {
				$str.=' '.implode(' ', $pos['more']);
			}
			$str=infra_strtolower($str);
			foreach ($v as $s) {
				if ($s&&strstr($str, $s)===false) {
					return;
				}
			}
			$poss[]=&$pos;
		});
		$conf=infra_config();
		$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title']);
		$menu=infra_loadJSON('*catalog/menu.json');

		$ans['breadcrumbs'][]=array('href'=>'find','title'=>$menu['find']['title']);
		$ans['breadcrumbs'][]=array('href'=>$val,'title'=>$val);
		
		$ans['descr']='Найдено позиций: '.sizeof($poss);
		$ans['list']=$poss;
		$ans['title']=infra_forFS($val);
		return $ans;
	}, $args, isset($_GET['re']));
	$ans=array_merge($ans, $res);
	//ЭТАП filters list
	Extend::filtering($ans, $fd);
	//Groups
	$subgroups=Catalog::cache('search.php subgroups', function () {
		//Микров вставка всё ради того чтобы не пользоваться $data на этом уровне
		//данный кэш один для любой страницы каталога
		$subgroups=array();
		$data=Catalog::init();
		Xlsx::runGroups($data, function ($group) use (&$subgroups) {
			if (empty($group['childs'])) {
				return;
			}
			$subgroup=array();
			array_map(function ($g) use (&$subgroup) {
				$subgroup[]=array('title'=>$g['title'],'name'=>$g['name']);
			}, $group['childs']);
			$subgroups[$group['title']]=$subgroup;
		});
		return $subgroups;
	});
	$groups=array();
	foreach ($ans['list'] as &$pos) {
		$path=$pos['path'];
		foreach ($ans['list'] as &$pos) {
			foreach ($pos['path'] as $v) {
				if (!isset($groups[$v])) {
					$groups[$v]=array('pos'=>$pos, 'count'=>0);
				};
				$groups[$v]['count']++;
			}
			$rpath=array();
			foreach ($path as $k => $p) {
				if ($pos['path'][$k]==$p) {
					$rpath[$k]=$p;
				} else {
					break;
				}
			}
			$path=$rpath;
		}
		break;
	}
	if (!sizeof($path)) {
		$conf=infra_config();
		$groupchilds=$subgroups[$conf['catalog']['title']];
	} else {
		$g=$path[sizeof($path)-1];
		if (isset($subgroups[$g])) {
			$groupchilds=$subgroups[$g];
		} else {
			$groupchilds=false;
		}
	}
	if ($groupchilds) {
		$ans['childs']=array();
		foreach ($groupchilds as $g) {
			//0 упоминаний
			if (!$groups[$g['title']]) {
				continue;
			}
			$pos=$groups[$g['title']]['pos'];
			$pos=array('article'=>$pos['article'],'producer'=>$pos['producer']);
			$ans['childs'][]=array_merge($g, array('pos'=>$pos,'count'=>$groups[$g['title']]['count']));
		}
	}
	$ans['count']=sizeof($ans['list']);
	return $ans;
}, $args, isset($_GET['re']));

$ans=array_merge($ans, $res);


//ЭТАП numbers list

if ($fd['sort']!='def') {
	if ($fd['sort']=='name') {
		usort($ans['list'], function ($a, $b) {
			$a=$a['Наименование'];
			$b=$b['Наименование'];
			if ($a == $b) {
		        return 0;
			}
		    return ($a < $b) ? -1 : 1;
		});
	}
}
if (!$fd['direct']) {
	$ans['list']=array_reverse($ans['list']);
}


$pages=ceil(sizeof($ans['list'])/$fd['count']);
if ($pages<$ans['page']) {
	$ans['page']=$pages;
}

$ans['numbers']=Catalog::numbers($ans['page'], $pages, 11);

$ans['list']=array_slice($ans['list'], ($ans['page']-1)*$fd['count'], $fd['count']);


$conf=infra_config();
$ans['text']=infra_loadTEXT('*files/get.php?'.$conf['catalog']['dir'].'articals/'.$ans['title']);//Изменение текста не отражается как изменение каталога, должно быть вне кэша

return infra_ret($ans);
