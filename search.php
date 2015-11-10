<?php
/**
 * Страница "search"
 */

namespace itlife\catalog;

use itlife\files\Xlsx;



$ans=array();
$md=Catalog::initMark($ans);

$val=infra_forFS(infra_toutf(strip_tags($_GET['val'])));
if ($val) $md['search']=$val;//Временное значение


if(isset($_GET['seo'])){
	$link=$_GET['seo'];
	if($md['group']){
		foreach($md['group'] as $val => $one) break;
		$link=$link.'&m=:group.'.$val.':1';
	} else if($md['producer']){
		foreach($md['producer'] as $val => $one) break;
		$link=$link.'&m=:producer.'.$val.':1';
	} else if($md['search']){
		$val=$md['search'];
		$link=$link.'&m=:search:'.$val;
	}
	
	
	unset($ans['md']);
	unset($ans['m']);
	$ans['external']='*catalog/seo.json';
	$ans['canonical']=infra_view_getPath().'?'.$link;
	return infra_ans($ans);
}


infra_cache_no();
if (isset($_GET['p'])) {
	$ans['page']=(int)$_GET['p'];
	if ($ans['page']<1) $ans['page']=1;
} else {
	$ans['page']=1;
}




$args=array($md);
$re=isset($_GET['re']);
if (!$re) {
	if ($ans['page'] != 1) $re = true;
	if ($ans['more']) $re = true;
}
$ans=Catalog::cache('search.php', function ($md) use($ans) {
	//1
	$ans['is']=''; //group producer search Что было найдено по запросу val (Отдельный файл is:change)
	$ans['descr']='';//абзац текста в начале страницы';
	$ans['text']=''; //большая статья снизу всего
	$ans['name']=''; //заголовок длинный и человеческий
	$ans['breadcrumbs']=array();//Путь где я нахожусь
	//$ans['val']=$val;//Заголовок страницы
	//$ans['title']=$val;//Что именно было найдено название для FS
	$ans['filters']=array();//Данные для формирования интерфейса фильтрации, опции и тп
	$ans['groups']=array();
	$ans['producers']=array();
	$ans['numbers']=array(); //Данные для построения интерфейса постраничной разбивки
	$ans['list']=array(); //Массив позиций
	Catalog::search($md, $ans);

	$conf=infra_config();
	//BREADCRUMBS TITLE
	if(!$md['group']&&$md['producer']&&sizeof($md['producer'])==1) { //ПРОИЗВОДИТЕЛЬ
		if($md['producer'])foreach ($md['producer'] as $producer => $v) break;
		else $producer=false;
		//is!, descr!, text!, name!, breadcrumbs!
		$ans['is']='producer';
		$name=Catalog::getProducer($producer);
		$ans['name']=$name;
		$ans['title']=$name;
		$conf=infra_config();
		$ans['breadcrumbs'][]=array('title'=>$conf['catalog']['title'], 'add'=>'producer:');
		$menu=infra_loadJSON('*catalog/menu.json');
		$ans['breadcrumbs'][]=array('href'=>'producers','title'=>$menu['producers']['title']);
		$ans['breadcrumbs'][]=array('add'=>'producer::producer.'.$name.':1','title'=>$name);
	} else if (!$md['group'] && $md['search']) {
		$ans['is']='search';
		$ans['name']=$md['search'];
		$ans['title']=infra_forFs($md['search']);
		$conf=infra_config();
		$ans['breadcrumbs'][]=array('title'=>$conf['catalog']['title'], 'add'=>'search:');
		$menu=infra_loadJSON('*catalog/menu.json');
		$ans['breadcrumbs'][]=array('href'=>'find','title'=>$menu['find']['title']);
		$ans['breadcrumbs'][]=array('title'=>$ans['name']);
	} else {
		//is!, descr!, text!, name!, breadcrumbs!, title
		if($md['group'])foreach ($md['group'] as $group => $v) break;
		else $group=false;
		$group=Catalog::getGroup($group);
		$ans['is']='group';	
		$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title'], 'add'=>'group:');
		array_map(function ($p) use (&$ans) {
			$group=Catalog::getGroup($p);
			$ans['breadcrumbs'][]=array('href'=>'','title'=>$group['name'], 'add'=>'group::group.'.$p.':1');
		}, $group['path']);
		if (sizeof($ans['breadcrumbs'])==1) {
			array_unshift($ans['breadcrumbs'],array('href'=>'/',"title"=>"Главная","nomark"=>true));
		}
		$ans['name']=$group['name'];//имя группы длинное
		$ans['descr']=@$group['descr']['Описание группы'];
		$ans['title']=$group['title'];
	}

	Catalog::sort($ans['list'], $md);

	//Numbers
	$pages=ceil(sizeof($ans['list'])/$md['count']);
	if ($pages<$ans['page']) {
		$ans['page']=$pages;
	}
	$ans['numbers']=Catalog::numbers($ans['page'], $pages, 11);
	$ans['list']=array_slice($ans['list'], ($ans['page']-1)*$md['count'], $md['count']);

	//Text
	$ans['text']=infra_loadTEXT('*files/get.php?'.$conf['catalog']['dir'].'articals/'.$ans['title']);//Изменение текста не отражается как изменение каталога, должно быть вне кэша
	foreach($ans['list'] as $k=>$pos){
		$pos=Catalog::getPos($pos);
		unset($pos['texts']);
		unset($pos['files']);
		$ans['list'][$k]=$pos;
	}
	return $ans;
}, $args, $re);

return infra_ret($ans);
