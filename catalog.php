<?php
namespace itlife\catalog;

use itlife\files\Xlsx;

class Catalog
{
	public static function init()
	{
		return self::cache('cat_init', function () {
			$conf=infra_config();
			$data=&Xlsx::init($conf['catalog']['dir'], array('more' => true, 'Имя файла' => $conf['catalog']['Имя файла']));
			return $data;
		});
	}
	public static function cache($name, $call, $args = array(), $re = null)
	{

		if (is_null($re)) {
			$re=isset($_GET['re']);
		}
		$conf=infra_config();
		return infra_cache(array($conf['catalog']['dir']), 'cat-'.$name, $call, $args, $re);
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
		if ($pages<$plen) {
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
					$ar[]=$pages-$plen/2+$i-4;
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
	public static function getFilter(&$ans)
	{
		if (!isset($_GET['m'])) {
			$_GET['m']='';
		}
		$filter=new Filter($_GET['m']);
		$md=$filter->getData(); //Данные от пользователя... грусть печаль... надо как-то всё проверить
		
		$md = array_intersect_key($md, array_flip(array('sort','direct','count','producer','settings')));
		if (isset($md['sort'])) {
			$md['sort']=(string)$md['sort'];// price, name, def, group, producer
			if (!in_array($md['sort'], array('def', 'name', 'group', 'producer'))) {
				unset($md['sort']);
			}
		} else {
			unset($md['sort']);
		}
		if (isset($md['direct'])) {
			if (!is_bool($md['direct'])) {
				unset($md['direct']);
			}
		} else {
			unset($md['direct']);
		}
		
		if (isset($md['count'])) {
			$md['count']=(int)$md['count'];

			if ($md['count']<1) {
				unset($md['count']);
			}
		} else {
			unset($md['count']);
		}

		$filter->setData($md);

		$ans['m']=$filter->getMark();
		$md=$filter->getData();
		
		
		$ans['filter']=array('isold'=>$filter->isold, 'isadd'=>$filter->isadd, 'old'=>$filter->old, 'add'=>$filter->add);//Отладочные данные
		$ans['filter']['md']=$md;
		//Прежде чем устанавливать значения по умолчанию нужно удалить бредовые значения
		$md=array_merge(array(
			"settings"=>'',
			"count"=>5,
			"direct"=>true,
			"sort"=>"def"
		), $md);
		$ans['md']=$md;
		return $md;
	}
}
