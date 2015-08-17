<?php
namespace itlife\catalog;

use itlife\files\Xlsx;

class Extend
{
	public static $fd = array(
		"count"=>5,
		"direct"=>true,
		"sort"=>false,
		"producer"=>false,
		"use"=>false
	);
	public static function init()
	{
		$conf=infra_config();
		$data=&Xlsx::init($conf['catalog']['dir'], array('more' => true, 'Имя файла' => $conf['catalog']['Имя файла']));
		return $data;
	}
	public static function filterData(&$fd)
	{
		if (isset($fd['sort'])) {
			$fd['sort']=(string)$fd['sort'];// price, name, def, group, producer
			if (!in_array($fd['sort'], array('def', 'name', 'group', 'producer'))) {
				unset($fd['sort']);
			}
		}
		if (isset($fd['producer'])) {
			$fd['producer']=(string)$fd['producer'];
			if ($fd['producer']=='') {
				unset($fd['producer']);
			}
		}
		if (isset($fd['direct'])) {
			if (!is_bool($fd['direct'])) {
				unset($fd['direct']);
			}
		}
		
		if (isset($fd['count'])) {
			$fd['count']=(int)$fd['count'];

			if ($fd['count']<1) {
				unset($fd['count']);
			}
		}
		if (isset($fd['use'])) {
			$fd['use']=(string)$fd['use'];

			if ($fd['use']=='') {
				unset($fd['use']);
			}
		}
	}
	public static function filtering(&$ans, $fd)
	{
		if (!sizeof($ans['list'])) {
			return;
		}
		$ans['filters']=array();
		//Filter producer
		if (!empty($fd['producer'])) {
			$ans['list']=array_filter($ans['list'], function ($pos) use ($fd) {
				if ($fd['producer']==$pos['producer']) {
					return true;
				}
				return false;
			});
			$ans['filters'][]=array(
				'title'=>'Производитель',
				'name'=>'producer',
				'value'=>$fd['producer']
			);
		}
		if (!empty($fd['use'])) {
			$ans['list']=array_filter($ans['list'], function ($pos) use ($fd) {

				$group=Catalog::cache('group', function ($title) {
					$data=Catalog::init();
					return Xlsx::runGroups($data, function ($group) use ($title) {
						if ($group['title']==$title) {
							unset($group['data']);
							unset($group['childs']);
							return $group;
						}
					});
				}, array($pos['group_title']));

				if (stripos($group['tparam'], $fd['use'])===false) {
					return false;
				} else {
					return true;
				}
			});
			$ans['filters'][]=array(
				'title'=>'Назначение',
				'name'=>'use',
				'value'=>$fd['use']
			);
		}
	}
}
