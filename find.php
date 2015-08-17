<?php

$ans=infra_loadJSON('*catalog/search.php');

$ans['breadcrumbs']=array();
$conf=infra_config();
$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title']);
$menu=infra_loadJSON('*catalog/menu.json');
$ans['breadcrumbs'][]=array('href'=>'find','title'=>$menu['find']['title']);
$ans['menu']=$menu;
return infra_ret($ans);
