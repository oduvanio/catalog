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
	/*
	 * Проверяем данные пользователя, удаляем лишнее, проверяем диапазоны и тп
	 *
	 */
	public static function filterData(&$fd)
	{
		$fd = array_intersect_key($fd, array_flip(array('sort','direct','page','count','producer')));
		if (isset($fd['sort'])) {
			$fd['sort']=(string)$fd['sort'];// price, name, def, group, producer
			if (!in_array($fd['sort'], array('def', 'name', 'group', 'producer'))) {
				unset($fd['sort']);
			}
		} else {
			unset($fd['sort']);
		}
		if (isset($fd['direct'])) {
			if (!is_bool($fd['direct'])) {
				unset($fd['direct']);
			}
		} else {
			unset($fd['direct']);
		}
		if (isset($fd['page'])) {
			$fd['page']=(int)$fd['page'];
			if ($fd['page']<1) {
				unset($fd['page']);
			}
		} else {
			unset($fd['page']);
		}
		if (isset($fd['count'])) {
			$fd['count']=(int)$fd['count'];

			if ($fd['count']<1) {
				unset($fd['count']);
			}
		} else {
			unset($fd['count']);
		}
	}
}
