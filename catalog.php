<?php
namespace itlife\catalog;

use itlife\files\Xlsx;

class Catalog
{
	public static $filter=null;
	public static function init()
	{
		return self::cache('cat_init', function () {
			return Extend::init();
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
	public static function getFilter(&$ans = array())
	{
		$mark=infra_toutf(infra_seq_get($_GET, infra_seq_right('m')));
		$filter=Filter::getInstance($mark);
		$fd=$filter->getData();

		$admit=array_keys(Extend::$fd);
		$fd = array_intersect_key($fd, array_flip($admit));

		Extend::filterData($fd);
		foreach ($fd as $k => $v) {
			if (is_null($v)) {
				unset($fd[$k]); //Удаление
			}
		}

		$ans['m']=$filter->setData($fd);
		$fd=array_merge(Extend::$fd, $fd);
		$ans['fd']=$fd;
		return $fd;
	}
}
