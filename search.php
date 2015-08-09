<?php
infra_require('*files/xls.php');
infra_require('*catalog/catalog.inc.php');
$conf=infra_config();
@define('CATDIR', $conf['catalog']['dir']);
$type=infra_strtolower(@$_GET['type']);
$val=infra_strtolower(strip_tags(@$_GET['val']));
$art=strip_tags(@$_GET['art']);
$prod=(string)$_REQUEST['prod'];
$prod=infra_strtolower(strip_tags($prod));
$breadcrumb=array();

/*
	стандартизированный $ans
	is - group producer search change - что именно было найдено
	parent - array('title'=>'ссылка на родителя'); Для слова Каталог исключение сделано
	
	descr - абзац текста в начале страницы
	text - большая статья снизу всего
	name - заголовок длинный и человеческий
	title - заголовок для FS и для адресной строки
	list - Массив всех найденых позиций

*/
$data=cat_init(); // список всей продукции

$ans=array(//Оригинальные значения
	'val'=>$val,
	'prod'=>$prod,
	'type'=>$type,
	'art'=>$art
);
$ans['name']=$ans['val'];
$ans['list']=array();



$group=&xls_runGroups($data, function (&$group) use (&$val) {
	if (infra_strtolower($group['name'])==($val)) {
		return $group;
	}
	if (infra_strtolower($group['title'])==$val) {
		return $group;
	}
});
$posscount=0;
if ($group) {
	$ans['is']='group';
	$ans['result']=1;
	$ans['path']=$group['path'];
	$ans['breadcrumbs']=array_merge(array('catalog'), $group['path']);
	$ans['name']=$group['name'];//имя группы длинное
	$ans['title']=$group['title'];//
	$ans['descr']=@$group['descr']['Описание группы'];
	$ans['list']=$group['data'];

	if ($group['parent_title']) {
		$ans['parent']=array('title'=>$group['parent_title']);
	}
	if ($prod) {
		if (!xls_runPoss($group, function (&$pos) use ($prod) {
			if ($prod==infra_strtolower($pos['Производитель'])) {
				return true;
			}
		}) ) {
			$prod='';
			$ans['prod']=$prod;
		}
	}

	if ($group['childs']) {
		$ans['childs']=array();
		foreach ($group['childs'] as &$v) {
			if ($prod) {
				$r=false;
				xls_runPoss($v, function (&$pos) use ($prod, &$r, &$posscount) {
					if ($prod==infra_strtolower($pos['Производитель'])) {
						$posscount++;
						$r=true;
					}
				});
				if (!$r) {
					continue;//не найдено неодной нужной позиции, группу не добавляем в список.
				}
			} else {
				xls_runPoss($v, function (&$pos) use (&$posscount) {
					$posscount++;
				});
			}
			$pos=&xls_runPoss($v, function &(&$pos) {
				$conf=infra_config();
				xls_preparePosFiles($pos, $conf['catalog']['dir'], array('producer','article'));
				if (!$pos['images']) {
					return false;
				}
				return $pos;
			});
			if ($pos) {
				$pos=array('article'=>$pos['article'],'producer'=>$pos['producer']);
			} else {
				$pos=false;
			}
			$ans['childs'][]=array('name'=>$v['name'],'title'=>$v['title'],'pos'=>$pos);
		}
	}
	//Есть левый prod и перешли в группу где нет этого прода. группа найдена но нет подгрупп и нет позиций
	//
	//$ans['text']='*pages/get.php?'.CATDIR.$group['title'];
} else {
	$dir=infra_theme(CATDIR.$val.'/');
	$poss=array();
	xls_runPoss($data, function (&$pos) use (&$poss, &$val) {
		if (infra_strtolower(@$pos['producer'])==$val) {
			$poss[]=&$pos;
		}
	});

	if ($dir||sizeof($poss)) {
		$ans['is']='producer';
		if (sizeof($poss)) {
			$name=$poss[0]['producer'];
		} else {
			$dir=infra_toutf($dir);
			$p=explode('/', $dir);
			$folder=$p[sizeof($p)-2];
			$name=$folder;
		}
		$ans['parent']=array('title'=>$conf['catalog']['title']);
		$ans['title']='Производитель '.$name;
		$ans['result']=1;
		$ans['descr']=@$producer['Описание группы'];
		$ans['list']=$poss;

		$src = CATDIR.'articals/'. $name . '.tpl';
		if (!infra_theme($src)) {
			$src = CATDIR.'articals/'. $name . '.docx';
		}
		if (!infra_theme($src)) {
			$src = CATDIR.'articals/'. $name . '.mht';
		}
		if (infra_theme($src)) {
			$ans['text']='*pages/get.php?'. $src;
		}
	} else {//ищим позиции подходящие под запрос
		$ans['is']='search';
		$ans['parent']=array('title'=>$conf['catalog']['title']);

		$v=explode(' ', $val);
		foreach ($v as &$s) {
			$s=trim($s);
		}

		xls_runPoss($data, function (&$pos) use (&$v, &$poss) {
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
				if (strstr($str, $s)===false) {
					return;
				}
			}

			unset($pos['parent']);
			$poss[]=&$pos;
		});
		if (sizeof($poss)) {
			$ans['result']=1;
			$ans['title']='Поиск: '.$ans['val'];
			//$ans['descr']='Найдено позиций: '.sizeof($poss);
			$ans['list']=$poss;
		}
		$ans['text']='*pages/get.php?'.CATDIR.$val;
	}
}




//BREAD
$bread=array();

$prods=array();
if ($ans['is']=='group') {
	xls_runPoss($group, function &(&$pos) use (&$prods) {
		$prods[infra_strtolower($pos['Производитель'])]=$pos['Производитель'];
		$r=null;
		return $r;
	});
} else if ($ans['is']=='search') {
	infra_forr($ans['list'], function &(&$pos) use (&$prods) {
		$prods[infra_strtolower($pos['Производитель'])]=$pos['Производитель'];
		$r=null;
		return $r;
	});
} else {
	xls_runPoss($data, function (&$pos) use (&$prods) {
		$prods[infra_strtolower($pos['Производитель'])]=$pos['Производитель'];
	});
}

if (($ans['is']!='producer')&&$prod) {
	$list2=array();
	for ($i=0, $l=sizeof($ans['list']); $i<$l; $i++) {
		if ($prod==infra_strtolower($ans['list'][$i]['Производитель'])) {
			$list2[]=$ans['list'][$i];
		}
	}
	$ans['list']=$list2;
	//$ans['descr'].='<p>Найдено позиций: '.($posscount+sizeof($ans['list'])).'</p>';
} else {
	//$ans['descr'].='<p>Найдено позиций: '.($posscount+sizeof($ans['list'])).'</p>';
}

$ans['count']=$posscount+sizeof($ans['list']);

$prodpage=isset($prods[$val]);
if (!$prodpage) {
	$conf=infra_config();
	if (infra_theme($conf['catalog']['dir'].$val.'/')) {
		$prodpage=true;
	}
}

if ($prod) {
	$ans['sel']=$prods[$prod];//Правильное имя параметра sel - клик пользователя
	if (!$ans['sel']) {
		$prod='';
	}
}
if ($prodpage) {
	$ans['sel']=$prods[$val];//Правильное имя параметра sel - клик пользователя
}

$prods=array_values($prods);
//if(sizeof($prods)<2)$prods=array();

$bread['prodpage']=$prodpage;

if ($prodpage) {
	$prod=$val;
	$prods=array();
}
$groups=array();
if ($ans['sel']) {//Выбран производитель
	if ($ans['is']=='group' && sizeof($ans['path'])<2) {//Группа 1ого уровня
		infra_forr($data['childs'], function &(&$g) use (&$groups, $prod) {
			xls_runPoss($g, function &(&$pos) use (&$g, &$groups, $prod) {
				$p=mb_strtolower($pos['producer']);
				if ($p==$prod) {
					$title=$g['title'];
					$name=$g['descr']['Наименование'];
					if (!$name) {
						$name=$title;
					}
					if (!$title) {
						return;
					}
					$groups[]=array('name'=>$name, 'title'=>$title);
					return false;
				}
				$r=null;
				return $r;
			});
			$r=null;
			return $r;
		});
	}
}

$bread['prods']=$prods;

if ($ans['sel'] && $ans['is']!='producer') {
	unset($ans['title']);
	unset($ans['text']);
	unset($ans['descr']);
	if (!$ans['list']) {
		if ($ans['is']=='group') {
			$list=array();
			xls_runPoss($group, function (&$pos) use (&$list, &$ans) {
				if ($pos['Производитель']!=$ans['sel']) {
					return;
				}
				$list[]=$pos;
			});
			$ans['list']=$list;
		}
	}
}
if (sizeof($groups)==1) {
	$groups=array();
}
$bread['groups']=$groups;
$ans['bread']=$bread;


if (isset($ans['text'])) {
	$ans['text']=infra_loadTEXT($ans['text']);
}

return infra_ret($ans);
