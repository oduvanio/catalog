<?php
namespace itlife\catalog;

use itlife\files\Xlsx;
class Extend
{
	public static $md = array(
		"count"=>5,
		"direct"=>true,
		"sort"=>false,
		"producer"=>false
	);
	public static function init()
	{
		$conf=infra_config();
		$data=&Xlsx::init($conf['catalog']['dir'], array(
			'more' => true,
			'Имя файла' => "Производитель",
			'Известные колонки'=>array("Наименование","Артикул","Производитель","Цена","Описание"))
		);
		return $data;
	}
	public static function filterData(&$md)
	{
		if (isset($md['sort'])) {
			$md['sort']=(string)$md['sort'];// price, name, def, group, producer
			if (!in_array($md['sort'], array('def', 'name', 'group', 'producer'))) {
				unset($md['sort']);
			}
		}
		if (isset($md['producer'])) {
			$md['producer']=(string)$md['producer'];
			if ($md['producer']=='') {
				unset($md['producer']);
			}
		}
		if (isset($md['direct'])) {
			if (!is_bool($md['direct'])) {
				unset($md['direct']);
			}
		}

		if (isset($md['count'])) {
			$md['count']=(int)$md['count'];

			if ($md['count']<1) {
				unset($md['count']);
			}
		}
	}
	public static function filtering(&$ans, $md)
	{
		if (!sizeof($ans['list'])) {
			return;
		}
		$ans['filters']=array();
		//Filter producer
		if (!empty($md['producer'])) {
			$ans['list']=array_filter($ans['list'], function ($pos) use ($md) {
				if ($md['producer']==$pos['producer']) {
					return true;
				}
				return false;
			});
			$ans['filters'][]=array(
				'title'=>'Производитель',
				'name'=>'producer',
				'value'=>$md['producer']
			);
		}
	}
}
