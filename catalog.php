<?php
namespace itlife\catalog;

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
}
