<?php
namespace itlife\catalog;

use itlife\files\Xlsx;
infra_require('*catalog/Extend.php');
class Catalog
{
	public static $md = array(
		"count"=>10,
		"reverse"=>false,
		"sort"=>false,
		"producer"=>array(),
		"group"=>array(),
		"search"=>false,
		"more"=>array(),
		"cost"=>array()
	);
	public static function init()
	{
		return self::cache('cat_init', function () {
			$conf=infra_config();
			$columns=array_merge(array("Наименование","Артикул","Производитель","Цена","Описание"),$conf['catalog']['columns']);
			$data=&Xlsx::init($conf['catalog']['dir'], array(
				'more' => true,
				'Имя файла' => $conf['catalog']['filename'],
				'Известные колонки'=>$columns
				)
			);
			Xlsx::runGroups($data,function(&$gr){
				$gr['data']=array_reverse($gr['data']); // Возвращает массив с элементами в обратном порядке
			});
			Extend::init($data);
			return $data;
		});
	}
	public static function getProducer($producer){
		$producer=mb_strtolower($producer);
		return Catalog::cache('getProducer', function &($producer){
			$data=Catalog::init();
			$pos=Xlsx::runPoss($data, function &($pos) use($producer) {
				if(mb_strtolower($pos['producer'])==$producer)return $pos;
			});
			return $pos['Производитель'];
		}, array($producer));
	}
	public static function markData(&$md)
	{
		if (isset($md['sort'])) {
			$md['sort']=(string)$md['sort'];// price, name, def, group, producer
			if (!in_array($md['sort'], array('name', 'group', 'producer','change','cost'))) {
				unset($md['sort']);
			}
		}
		if (isset($md['search'])) {
			$md['search']=(string)$md['search'];
			$md['search']=trim($md['search']);
			$ar=preg_split('/[\s,]+/', $md['search']);
			$res=array();
			foreach($ar as $k=>$v){
				if ($v=='') continue;
				$res[]=$v;
			}
			if ($res) {
				$md['search']=implode(' ', $res);
			}else{
				unset($md['search']);
			}
		}
		
		if (isset($md['producer'])) {
			if(!is_array($md['producer'])) $md['producer']=array();
			$md['producer']=array_filter($md['producer']);
			
			$producers=array_keys($md['producer']);
			$producers=array_filter($producers, function (&$value) {
				if(in_array($value,array('yes','no'))) return true;
				if(Catalog::getProducer($value))return true;
				return false;
			});
			$md['producer']=array_fill_keys($producers, 1);
			if (!$md['producer']) unset($md['producer']);
		}
		$key='group';
		if (isset($md[$key])) {
			if(!is_array($md[$key])){
				$val=$md[$key];
				$md[$key]=array();
				$md[$key][$val]=1;
			}
			$md[$key]=array_filter($md[$key]);
			$values=array_keys($md[$key]);
			$values=array_filter($values, function (&$value) {
				if(in_array($value,array('yes','no'))) return true;
				if(!$value)return false;
				if(!Catalog::getGroup($value))return false;
				return true;
			});
			$md[$key]=array_fill_keys($values, 1);
			if (!$md[$key]) unset($md[$key]);
		}
		if (isset($md['reverse'])) {
			$md['reverse']=(bool)$md['reverse'];
			if(!$md['reverse'])unset($md['reverse']);
		}
		if (isset($md['count'])) {
			$md['count']=(int)$md['count'];
			if ($md['count']<1) unset($md['count']);
			if ($md['count']>1000) unset($md['count']);
		}
		$name='cost';
		if (isset($md[$name])) {
			if(!is_array($md[$name])) $md[$name]=array();
			$md[$name]=array_filter($md[$name]);//Удаляет false значения
			$values=array_keys($md[$name]);
			$values=array_filter($values, function (&$val) {
				if(in_array($value,array('yes','no'))) return true;
				if (!$val) return false;
				return true;
			});
			$md[$name]=array_fill_keys($values, 1);
			if (!$md[$name]) unset($md[$name]);
		}
		if (isset($md['more'])) {
			if (!is_array($md['more'])) {
				unset($md['more']);
			} else {
				foreach($md['more'] as $k=>$v){
					if (!is_array($v)) {
						unset($md['more'][$k]);
					} else {
						foreach($v as $kk=>$vv){		
							if (!$vv) unset($md['more'][$k][$kk]);
						}
						if (!$md['more'][$k]) unset($md['more'][$k]);
					}		
				}
				if (!$md['more']) unset($md['more']);
			}
		}
		Extend::markData($md);
	}
	public static function getGroup($group){
		return Catalog::cache('getGroup', function &($group){
			$data=Catalog::init();
			if($group) $data=Xlsx::runGroups($data, function &($gr) use($group) {
				if($gr['title']==$group)return $gr;
			});
			unset($data['childs']);
			unset($data['data']);
			return $data;
		}, array($group));
	}
	/*
	* getParams Собирает в простую структуру все параметры и возможные значения фильтров для указанной группы
	*/
	public static function getParams($group = false){
		return Catalog::cache('getParams', function &($group){
			$conf = infra_config();
			$poss = Catalog::getPoss($group);
		
			$params = array();//параметры
			//ПОСЧИТАЛИ COUNT
			$count = sizeof($poss); //количество позиций
			$parametr = array(
				'posname' => null, //Артикул
				'posid' => null, //article
				'mdid' => null, //art
				'title' => null, //Уникальый Артикул
				'more' => null, 
				'separator' => ',',
				'count' => 0,
				'filter' => 0,
				'search' => 0,
				'option' => array()
			);
			$option = array(
				'id' => null,
				'title' => null,
				'count' => 0,
				'filter' => 0,
				'search' => 0
			);
			//more берутся все параметры, а из main только указанные, расширенные config.catalog.filters
			$main=$conf['catalog']['filters'];

			foreach($main as $k=>$prop){
				if ($prop['more']) continue;
				$prop['mdid']=$k;
				$params[$k] = array_merge($parametr, $prop);
			}

			foreach($poss as &$pos){
				foreach($main as $k=>$prop){
					if ($prop['more']) continue;
					$prop=$params[$k];
					$val=$pos[$prop['posid']];
					$name=$pos[$prop['posname']];
					if (preg_match("/[:]/", $val)) continue;//Зачем?
					if (!Xlsx::isSpecified($val)) continue;
					
					$r=false;
					if($prop['separator']){
						$arval=explode($prop['separator'], $val);
						$arname=explode($prop['separator'], $name);
					}else{
						$arval=array($val);
						$arname=array($name);
					}
					foreach($arval as $i => $value){
						$id=mb_strtolower(infra_forFS($value));
						if (!Xlsx::isSpecified($id)) continue;
						if (!isset($params[$k]['option'][$id])) {
							$params[$k]['option'][$id] = array_merge($option, array(
								'id' => $id,
								'title' => $arname[$i]
							));
						}
						$r=true;
						$params[$k]['option'][$id]['count']++;
					}
					if ($r)	$params[$k]['count']++;//Позиций с этим параметром
				}
				if($pos['more']){
					foreach($pos['more'] as $k=>$val){
						if (preg_match("/[:]/", $val)) continue;
						if (preg_match("/[:]/", $k)) continue;
						if (!Xlsx::isSpecified($val)) continue;

						if (!isset($params[$k])) {
							$params[$k] = array_merge($parametr,array(
								'posname' => $k,
								'posid' => $k,
								'mdid' => $k,
								'title' => $k,
								'more' => true
							));
						}
						$prop=$params[$k];
						$r=false;
						
						if($prop['separator']){
							$arval=explode($prop['separator'], $val);
						}else{
							$arval=array($val);
						}
						foreach($arval as $value){
							$id=mb_strtolower(infra_forFS($value));
							if (!Xlsx::isSpecified($id)) continue;
							$r=true;
							if (!isset($params[$k]['option'][$id])) {
								$params[$k]['option'][$id]=array_merge($option, array(
									'id' => $id,
									'title' => trim($value)
								));
							}
							$params[$k]['option'][$id]['count']++;
						}
						if ($r) $params[$k]['count']++;
					}
				}
			}
			foreach($main as $k=>$prop){
				if (!$prop['more']) continue;

				$prop['mdid']=$k;
				$params[$k] = array_merge($prop, $params[$k]);
			}
			uasort($params,function($p1, $p2){
				if (!empty($p1['group']) || !empty($p2['group'])) {
					if ($p1['group']==$p2['group']) return 0;
					if (!empty($p1['group'])) return 1;
					if (!empty($p2['group'])) return -1;
				}
				if($p1['count']>$p2['count'])return -1;
				if($p1['count']<$p2['count'])return 1;
				return 0;
			});
			return $params;
		},array($group),isset($_GET['re']));
	}
	public static function getPoss($mdgroup){
		if ($mdgroup) foreach ($mdgroup as $group=>$v) break;
		else $group = false;
		
		return Catalog::cache('getPoss', function &($group){
			$data=Catalog::init();
			if($group) $data=Xlsx::runGroups($data, function &($gr) use($group) {
				if($gr['title']==$group)return $gr;
			});
			$poss=array();
			Xlsx::runPoss($data, function (&$pos) use (&$poss) {
				$poss[]=&$pos;
			});
			
			return $poss;
		}, array($group));
	}
	public static function nocache($md) {
		$mdnocache=array_diff_key($md, array_flip(array("sort", "reverse", "count")));
		return $mdnocache;
	}
	public static function sort(&$poss, $md) {
		if ($md['sort']) {
			if ($md['sort']=='name') {
				usort($poss, function ($a, $b) {
					$a=$a['Наименование'];
					$b=$b['Наименование'];
					if ($a == $b) return 0;
					return ($a < $b) ? -1 : 1;
				});
			} else if ($md['sort']=='cost') {
				usort($poss, function ($a, $b) {
					$a=$a['Цена'];
					$b=$b['Цена'];
					if ($a == $b) return 0;
					return ($a < $b) ? 1 : -1;
				});
			} else if ($md['sort']=='change') {
				$args=array(Catalog::nocache($md));
				
				$poss=Catalog::cache('change', function($md) use($poss){
					foreach($poss as &$pos) {
						$conf=infra_config();
						$dir=infra_theme($conf['catalog']['dir'].$pos['producer'].'/'.$pos['article'].'/');
						if (!$dir) {
							$dir=infra_theme($conf['catalog']['dir']);
							$pos['time']=0; //filemtime($dir);
						} else {
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
						}
					}
					usort($poss, function ($a, $b) {
						$a=$a['time'];
						$b=$b['time'];
						if ($a == $b) return 0;
						return ($a < $b) ? -1 : 1;
					});
					return $poss;
				}, $args, isset($_GET['re']));
			}
		}
		if ($md['reverse']) {
			$poss=array_reverse($poss);
		}
	}
	public static function getGroups($list, $now = false) {
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
				array_walk($group['childs'], function ($g) use (&$subgroup) {
					$subgroup[]=array('title'=>$g['title'],'name'=>$g['name']);
				});
				$subgroups[$group['title']]=$subgroup;
			});
			return $subgroups;
		});
		$groups=array();
		foreach ($list as &$pos) {
			$path=$pos['path'];
			
			foreach ($list as &$pos) {
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
				if(!$now||$now!=$g){
					$groupchilds=array(array("name" => $g,"title" => $g));
				} else {
					$groupchilds=false;
				}
			}
		}
		$childs=array();
		if ($groupchilds) {
			foreach ($groupchilds as $g) {
				if (!$groups[$g['title']]) continue;
				$pos=Catalog::getPos($groups[$g['title']]['pos']);
				$pos=array('article'=>$pos['article'],'producer'=>$pos['producer'],'images'=>$pos['images']);
				$childs[]=array_merge($g, array('pos'=>$pos,'count'=>$groups[$g['title']]['count']));
			}
		}
		return $childs;
	}
	public static function searchTest($pos, $v) {
		$str=$pos['Артикул'];
		$str.=' '.implode(' ', $pos['path']);
		$str.=' '.$pos['article'];
		$str.=' '.$pos['Наименование'];
		$str.=' '.$pos['Производитель'];
		$str.=' '.$pos['producer'];
		$str.=' '.$pos['Описание'];
		
		if (!empty($pos['more'])) {
			$str.=' '.implode(' ', $pos['more']);
			$str.=' '.implode(' ', array_keys($pos['more']));
		}
		$str=infra_strtolower($str);
		foreach ($v as $s) {
			if (mb_strrpos($str, $s)===false) {
				return false;
			}
		}
		return true;
	}
	public static function cache($name, $call, $args = array(), $re = null)
	{
		if (is_null($re)) $re=isset($_GET['re']);
		$conf=infra_config();
		return infra_cache($conf['catalog']['cache'], 'cat-'.$name, $call, $args, $re);
	}
	public static function numbers($page, $pages, $plen = 11)
	{
		//$plen=11;//Только нечётные и больше 6 - количество показываемых циферок
		/*
		$pages=10
		$plen=6

		(1)2345-10
		1(2)345-10
		12(3)45-10
		123(4)5-10
		1-4(5)6-10
		1-5(6)7-10
		1-6(7)8910
		1-67(8)910
		1-678(9)10
		1-6789(10)

		$lside=$plen/2+1=4//Последняя цифра после которой появляется переход слева
		$rside=$pages-$lside-1=6//Первая цифра после которой справа появляется переход
		$islspace=$page>$lside//нужна ли пустая вставка слева
		$isrspace=$page<$rside
		$nums=$plen/2-2;//Количество цифр показываемых сбоку от текущей когда есть $islspace далее текущая


		*/

		if ($pages<=$plen) {
			$ar=array_fill(0, $pages+1, 1);
			$ar=array_keys($ar);
			array_shift($ar);
		} else {
			$plen=$plen-1;
			$lside=$plen/2+1;//Последняя цифра после которой появляется переход слева
			$rside=$pages-$lside-1;//Первая цифра после которой справа появляется переход
			$islspace=$page>$lside;
			$isrspace=$page<$rside+2;
			$ar=array(1);
			if ($isrspace&&!$islspace) {
				for ($i = 0; $i < $plen-2; $i++) {
					$ar[]=$i+2;
				}
				$ar[]=0;
				$ar[]=$pages;
			} else if (!$isrspace&&$islspace) {
				$ar[]=0;
				for ($i=0; $i<$plen-1; $i++) {
					$ar[]=$pages-$plen/2+$i-3;
				}
			} else if ($isrspace&&$islspace) {
				$nums=$plen/2-2;//Количество цифр показываемых сбоку от текущей когда есть $islspace далее текущая
				$ar[]=0;
				for ($i=0; $i<$nums*2+1; $i++) {
					$ar[]=$page-$plen/2+$i+2;
				}
				$ar[]=0;
				$ar[]=$pages;
			}
		}
		$ar=array_filter($ar, function (&$num) use ($page) {
			$n=$num;
			$num=array('num'=>$n,'title'=>$n);
			if (!$n) {
				$num['empty']=true;
				$num['num']='';
				$num['title']='&nbsp;';
			}
			if ($n==$page) {
				$num['active']=true;
			}
			return true;
		});
		if (sizeof($ar)<2) {
			return false;
		}
		$prev=array('num'=>$page-1,'title'=>'&laquo;');
		if ($page<=1) {
			$prev['empty']=true;
		}
		array_unshift($ar, $prev);
		$next=array('num'=>$page+1,'title'=>'&raquo;');
		if ($page>=$pages) {
			$next['empty']=true;
		}
		array_push($ar, $next);
		return $ar;
	}
	public static function initMark(&$ans = array())
	{
		//Нельзя добавлять в скрипте к метке новые значения. так как метка приходит во многие скрипты и везде должен получится один результат и все должны получить одинаковую новую метку содержающую изменения
		$mark=infra_toutf(infra_seq_get($_GET, infra_seq_right('m')));
		
		$mark=Mark::getInstance($mark);
		$md=$mark->getData();
		
		$conf=infra_config();

		$defmd=array_merge(Catalog::$md, $conf['catalog']['md']);	
		
		$admit=array_keys($defmd);
		$md = array_intersect_key($md, array_flip($admit));

		Catalog::markData($md);

		$ans['m']=$mark->setData($md);
		$md=array_merge($defmd, $md);
		$ans['md']=$md;
		return $md;
	}
	public static function urlencode($str)
	{
		$str = preg_replace("/\+/", "%2B", $str);
		$str = preg_replace("/\s/", "+", $str);
		return $str;
	}
	public static function filtering(&$poss, $md)
	{
		if (!sizeof($poss)) return;
		
		$params=Catalog::getParams();
		$filters=array();
		
		foreach($params as $prop){
			if ($prop['more']) {
				if (empty($md['more'])) continue; //Filter more
				if (empty($md['more'][$prop['mdid']])) continue; //Filter more

				$val=$md['more'][$prop['mdid']];
				$filter = array(
					'title' => $prop['title'], 
					'name' => infra_seq_short(array('more', Catalog::urlencode($prop['mdid'])))
				);
				
				$poss=array_filter($poss, function ($pos) use ($prop, $val) {

					foreach($val as $value => $one) {
						if ($value === 'yes' && Xlsx::isSpecified($option)) return true;
						if ($value === 'no' && !Xlsx::isSpecified($option)) return true;

						$option=$pos['more'][$prop['posid']];
						if ($prop['separator']) {
							$option=explode($prop['separator'], $option);
						} else {
							$option=array($option);
						}
						foreach($option as $opt){
							$opt=mb_strtolower(trim($opt));	
							if ((string)$value === $opt) return true;
						}
					}
					return false;
				});
				if ($val['no']) {
					unset($val['no']);
					$val['Не указано']=1;	
				}
				if ($val['yes']) {
					unset($val['yes']);
					$val['Указано']=1;
				}
				$filter['value']=implode(', ', array_keys($val));	
				$filters[]=$filter;
				
			} else {
				if (empty($md[$prop['mdid']])) continue;
				$val=$md[$prop['mdid']];
				$filter=array('title'=>$prop['title'], 'name'=>infra_seq_short(array(Catalog::urlencode($prop['mdid']))));
				$poss=array_filter($poss, function ($pos) use ($prop, $val) {
					$prop=mb_strtolower($pos[$prop['posid']]);
					foreach($val as $value => $one) {
						if ($value === 'yes' && Xlsx::isSpecified($prop)) return true;
						if ($value === 'no' && !Xlsx::isSpecified($prop)) return true;
						if ((string)$value === $prop) return true;
					}
					return false;
				});
				if ($val['no']) {
					unset($val['no']);
					$val['Не указано']=1;
				}
				if ($val['yes']) {
					unset($val['yes']);
					$val['Указано']=1;
				}
				$filter['value']=implode(', ', array_keys($val));
				$filters[]=$filter;
			}
		}
		
		//Filter group
		$key='group';
		if (!empty($md[$key])) {
			$title='Группа';
			$val=$md[$key];
			$filter=array('title'=>$title, 'name'=>infra_seq_short(array(Catalog::urlencode($key))));
			$poss=array_filter($poss, function ($pos) use ($key, $val) {
				$prop=$pos[$key];
				foreach ($val as $value => $one) {
					if ($value === 'yes') return true;
					foreach($pos['path'] as $path){
						if ((string)$value === $path) return true;
					}
				}
				return false;
			});
			if ($val['no']) {
				unset($val['no']);
				$val['Не указано']=1;
			}
			if ($val['yes']) {
				unset($val['yes']);
				$val['Указано']=1;
			}
			$filter['value']=implode(', ', array_keys($val));
			if ($md['search']) $filters[]=$filter;
		}
		//Filter search
		if (!empty($md['search'])) {
			$v=preg_split("/\s+/", mb_strtolower($md['search']));
			$poss=array_filter($poss, function ($pos) use ($v) {
				return Catalog::searchTest($pos, $v);
			});
			$filters[]=array(
				'title'=>'Поиск',
				'name'=>'search',
				'value'=>$md['search']
			);
		}
		Extend::filtering($poss, $md, $filters);
		return $filters;
	}
	public static function option($values, $count, $search, $showhard = false){
		foreach ($values as $value => $s) break;
		$opt=array('type' => '', 'values' => $values);
		$min=$value;
		$max=$value;
		$yes=0;
		$yesall=0;
		/*
			$values массив со всеми возможными занчениями каждого параметра
			каждое значение характеризуется
			count - сколько всего в текущем разделе, определяющего набор фильтров
			search - сколько всего найдено с md
			filter - сколько найдено если данный параметр не указана в md
		*/
		foreach($opt['values'] as $v=>$c){
			if(Xlsx::isSpecified($v)){
				$yes+=$c['search'];//Сколько найдено
				$yesall+=$c['count'];//Сколько в группе
			}
		}
		$opt['search']=$yes;
		$opt['count']=$yesall;
		if(!$showhard && $count > $yesall * 10){//Если отмеченных менее 10% то такие опции не показываются
			return false;
		}
		$type=false;
		foreach($opt['values'] as $val=>$c){//Слайдер
			if(is_string($val)){
				$type='string';
				break;
			}
			if($val<$min)$min=$val;
			if($val>$max)$max=$val;
		}
		if(!$type){
			$len=sizeof($opt['values']);
			if($len>5){//Слайдер
				$opt['min']=$min;
				$opt['max']=$max;
				$type='slider';
				unset($opt['values']);
			}else{
				$type='string';
			}
		}
		$opt['type']=$type;
		
		
		if($opt['type']=='string'){
			if(sizeof($opt['values'])>30){
				$opt['values']=array();
				if (!$showhard) {	
					return false;
				}
			}
			if ($showhard) {	
				foreach($showhard as $show => $one) {
					if ($show=='yes') continue;
					if ($show=='no') continue;
					if ($opt['values'][$show]) continue;
					$title=$show;
					$opt['values'][$show] = array('id'=>$show, 'title'=>$title);
				}
			}
			/*foreach($opt['values'] as $v){//Когда всех значений по 1
				if($v!=1){
					//Единичные опции
					$opt['values']=array();
					break;
				}
			}*/
			//if(sizeof($opt['values'])>10){
				//$opt['values_more']=array_slice($opt['values'],6,sizeof($opt['values'])-6,true);
				//$opt['values']=array_slice($opt['values'],0,6,true);
			//}
		}
		if($opt['type']=='string'){
			usort($opt['values'], function ($v1, $v2){
				//if ($v1['filter']>$v2['filter']) return -1;
				//if ($v1['filter']<$v2['filter']) return 1;
				if ($v1['count']>$v2['count']) return -1;
				if ($v1['count']<$v2['count']) return 1;
			});
		}
		/*if(sizeof($opt['values'])==1){
			if($opt['yes']==$count){//Значение есть у всех позиций и только один вариант
				unset($params[$k]);
				continue;
			}
		}*/
		if(!$opt['values']&&$opt['type']!='slider'){
			if($opt['count']==$count){//Слишком много занчений но при этом у всех позиций они указаны и нет no yes
				return false;
			}
		}
		$opt['nosearch']=$search-$opt['search']; //из общего количества вычесть количество указанных
		$opt['nocount']=$count-$opt['count']; //из общего количества вычесть количество с yes
		return $opt;
	}
	public static function getPos(&$pos){
		$args=array($pos['producer'],$pos['article']);
		return infra_admin_cache('getPos', function() use($pos){
			Xlsx::addFiles($pos);
			$files=explode(',', @$pos['Файлы']);
			foreach ($files as $f) {
				if (!$f) {
					continue;
				}
				$f=trim($f);
				$conf=infra_config();
				Xlsx::addFiles($pos, $conf['catalog']['dir'].$f);
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
				foreach ($pos['texts'] as $k => $t) {
					$pos['texts'][$k]=infra_loadTEXT('*files/get.php?'.$t);
				}
			}
			return $pos;
		},$args);
	}
	public static function search($md, &$ans=array()) {
		$args=array(Catalog::nocache($md));
		$res=Catalog::cache('search.php filter list', function ($md) {
			$conf=infra_config();
			$ans['list']=Catalog::getPoss($md['group']);
			//ЭТАП filters list
			$ans['filters']=Catalog::filtering($ans['list'], $md);
			foreach ($md['group'] as $now => $one) break;
			$ans['childs']=Catalog::getGroups($ans['list'], $now);
			
			$ans['count']=sizeof($ans['list']);
			
			return $ans;
		}, $args, isset($_GET['re']));
		$ans=array_merge($ans, $res);
		
		return $ans;
	}
}
