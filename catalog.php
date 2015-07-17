<?php
infra_require('*files/xls.php');
infra_require('*catalog/catalog.inc.php');
$conf=infra_config();
@define('CATDIR', $conf['catalog']['dir']);
$type=infra_strtolower(@$_GET['type']);
$val=strip_tags(@$_GET['val']);
$art=strip_tags(@$_GET['art']);
$re=isset($_GET['re']);
$prod=(string)$_REQUEST['prod'];
$prod=strip_tags($prod);
$args=array($type,$val,$art,$prod);

if ($type=='stat') { // ?contacts  type=rubrics, поэтому эту проверку пока пропускаем, проверим, когда $type будет stat
	// сбор статистики поиска по каталогу и отображение статистики
	if (!empty($_GET['submit'])) {
		$submit=true; // сбор статистики
	} else {
		$submit=false; // отображение статистики
	}
	$ans=array('result'=>1);
	$dirs = infra_dirs();
	$dir=$dirs['data'];

	$data=infra_loadJSON($dir.'catalog_stat.json');
	if ($submit) {
		if (!$val) {
			return infra_echo($ans);
		}
		infra_cache_no();
		$val=infra_forFS($val);
		if (!$data) {
			$data=array('users'=>array(),'cat_id'=>0,'time'=>time());
		}
		$id=infra_view_getCookie('cat_id');
		$time=infra_view_getCookie('cat_time');
		if (!$time||!$id||$time!=$data['time']) {
			$id=++$data['cat_id'];
			infra_view_setCookie('cat_id', $id);
			infra_view_setCookie('cat_time', $data['time']);
		}
		$ans['cat_id']=$id;
		$ans['cat_time']=$time;

		$user=array('cat_id'=>$id,'list'=>array(),'time'=>time());
		foreach ($data['users'] as $k => $v) {
			if ($v['cat_id']==$id) {
				$user=$v;
				unset($data['users'][$k]);
				break;
			}
		}
		$data['users']=array_values($data['users']);

		foreach ($user['list'] as $k => $v) {
			if ($v['val']==$val) {
				unset($user['list'][$k]);
				break;
			}
		}
		$user['list']=array_values($user['list']);
		$search=infra_loadJSON('*catalog/catalog.php?type=search&val='.$val);
		$count=sizeof($search['list']);
		array_unshift($user['list'], array('val' => $val,'time' => time(),'count' => $count));

		if (sizeof($user['list'])>10) {
			$user['list']=array_slice($user['list'], 0, 10);
		}
		array_unshift($data['users'], $user);

		if (sizeof($data['users'])>100) {
			$data['users']=array_slice($data['users'], 0, 50);
		}
		file_put_contents($dir.'catalog_stat.json', infra_json_encode($data));
		$ans['data']=$data;
		return infra_echo($ans);
	}
}
infra_admin_modified(); // проверяет cache и если он актуальный, то мы выходим
$ans=infra_cache(array($conf['catalog']['dir']), 'catalog', function ($type, $val, $art, $prod) {
	$conf=infra_config();
	$data=cat_init(); // список всей продукции
	$ans=array(//Оригинальные значения
		'val'=>$val,
		'prod'=>$prod,
		'type'=>$type,
		'art'=>$art
	); // ?catalog имеется только type = rubrics, если имеется что-то после ?catalog, то type = search, art='',  val и prod = то что написано после ?catalog/
	/*
		файл catalog.php реагирует на 4 параметра
		type - тип - их несколько и они определяются в общих чертах что должно вернуться и какие необходимы параметры из оставшихся

	
		//Далее строковые параметры, смысл которые определяется в type
		val - обычно это слово поиска, может быть название производителя, также есть слова исключения со специальной обработкой, например "Изменения"
		prod - обычно берётся из конфига и выполняет функцию фильтра по выбранному производителю
		art - обычно это артикул

		ответ возвращать пытаемся тандартизированным, например в type=search в ответе предусмотреы поля count, list, childs
		И при любом поиске или при странице группы или страницы производителя мы определяем эти стандартные значения. 
		И хотя страницы для пользователя визуально разные, но строятся они по похожим данным.

	*/
	$prod=infra_strtolower($prod);
	$val=infra_strtolower($val);
	$art=infra_strtolower($art);
	if ($prod) {
		if (!xls_runPoss($data, function (&$pos) use ($prod) {
			if ($prod==infra_strtolower($pos['producer'])) {
				return true;
			}
		})) {
			$prod='';
		}
	}
	$ans['prod']=$prod; // записываем в массив ans производителя

	if ($type=='rubrics') {
		//На главной странице каталога показываются и может быть эти даные используются для показа групп на главной траницы
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
	} else if ($type=='pos') {
		/*echo 'Мы в pos';
		exit;*/
		$ans['pos']=false;
		/*echo $val;
		echo '<pre>';
		print_r($data);
		exit;*/
		$pos=&xls_runPoss($data, function (&$pos, $i, &$group) use (&$val, &$art) {
			if (infra_strtolower($pos['producer'])!==$val) {
				return;
			}
			if (infra_strtolower($pos['article'])!==$art) {
				return;
			}
			//$pos['path']=$group['path'];
			return $pos;
		});
		if ($pos) {
			$ans['result']=1;
			$ans['pos']=&$pos;
			$ans['path']=$pos['path'];
			/*echo '<pre>';
			var_dump($ans);
			exit;*/
			
			//$pos['images']=infra_load('*pages/list.php?src='.$conf['catalog']['dir'].$pos['Производитель'].'/'.$pos['article'].'/&onlyname=1&e=jpg,png,gif','fj');
			//$pos['text']=infra_load('*pages/get.php?src='.$conf['catalog']['dir'].$pos['Производитель'].'/'.$pos['article'].'/&onlyname=1&e=jpg,png,gif','fj');
		}
	} else if ($type=='producers') {
		$prods=array();

		xls_runPoss($data, function (&$pos) use (&$prods) {
			@$prods[$pos['Производитель']]++;
		});
		$ans['producers']=$prods;
	}
	return $ans;
}, $args, isset($_GET['re']));
$ans=infra_admin_cache('cat admin', function ($ans) {
	$type=infra_strtolower($ans['type']);
	$art=infra_strtolower($ans['art']);
	$val=infra_strtolower($ans['val']);
	if (isset($_GET['submit'])&&$_GET['submit']) {
		$submit=true;
	} else {
		$submit=false;
	}
	if ($type=='stat'&&!$submit) {
		$dirs=infra_dirs();
		$dir=$dirs['data'];
		$data=infra_loadJSON($dir.'catalog_stat.json');
		if (!$data) {
			$data=array('users' => array(),'cat_id' => 0,'time' => time());//100 10 user list array('val'=>$val,'time'=>time())
		}
		$ans['text']=infra_loadTEXT('*files/files.php?type=texts&id=Статистика поиска по каталогу&show');
		//time
		//Поиск, Поиск, Поиск
		$ans['stat']=$data;
		return $ans;
	}
	if ($type=='sale') {
		$list=infra_loadJSON('*sale.json');
		$items=array();
		if (is_array($list)) {
			foreach ($list as $item) {
				$pos=infra_loadJSON('*catalog/catalog.php?type=pos&val='.$item['producer'].'&art='.$item['article']);
				$pos=$pos['pos'];
				unset($pos['texts']);
				unset($pos['files']);
				$pos['sale']=$item;
				$items[]=$pos;
			}
		}
		$ans['items']=$items;
		return $ans;
	}
	if ($type=='search') {
		if (isset($ans['text'])) {
			$ans['text']=infra_loadTEXT($ans['text']);
		}
		if ($val=='change') {
			$data=cat_init();
			//Смотрим дату изменения папки для каждой позиции кэшируем на изменение XLS файлов как всё здесь...
			//И дату изменения файлов в папке
			//Позиции без папок игнорируются
			$poss=array();
			xls_runPoss($data, function (&$pos) use (&$poss) {
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
			$ans['list']=array_slice($poss, 0, 30);
			$ans['count']=sizeof($ans['list']);
		};
	}
	if ($type=='pos') {
		$ans['phone']=infra_loadJSON('*Телефон.json');
		$conf=infra_config();
		if ($ans['pos']) {
			$pos=$ans['pos'];
			xls_preparePosFiles($pos, $conf['catalog']['dir'], array('producer','article'));
			$files=explode(',', @$pos['Файлы']);
			foreach ($files as $f) {
				if (!$f) {
					continue;
				}
				$f=trim($f);
				xls_preparePosFiles($pos, $conf['catalog']['dir'].$f);
			}

			$files=array();
			foreach ($pos['files'] as $f) {
				if (is_string($f)) {
					$f = infra_theme($f); //убрали звездочку
					$d=infra_srcinfo(infra_toutf($f));
				} else {
					$d=$f;
					$f=$d['src'];
				}
				
				$d['size']=round(filesize(infra_tofs($f))/1000000, 2);
				if (!$d['size']) {
					$d['size']='0.01';
				}
				$files[]=$d;
			}
			$pos['files']=$files;
			if ($pos['texts']) {
				infra_require('*files/files.inc.php');
				foreach ($pos['texts'] as $k => $t) {
					$pos['texts'][$k]=files_article($t);
				}
			}
			$ans['pos']=$pos;
		}
	}
	return $ans;
}, array($ans), isset($_GET['re']));
/*echo '<pre>';
var_dump($ans);
exit;*/
return infra_ret($ans);
