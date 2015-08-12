<?php

$ans=infra_loadJSON('*catalog/rubrics.php');

$ans['breadcrumbs']=array();
$conf=infra_config();
$ans['breadcrumbs'][]=array('href'=>'','title'=>$conf['catalog']['title']);
$menu=infra_loadJSON('*catalog/rubrics.json');
$ans['breadcrumbs'][]=array('href'=>'find','title'=>$menu['find']['title']);
return infra_ret($ans);
