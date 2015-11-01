<?php

namespace itlife\catalog;

use itlife\files\Xlsx;

$ans=array();

$fd=Catalog::initMark($ans);
//На главной странице каталога показываются

$data=infra_loadJSON('*catalog/search.php?m='.$ans['m']);

$ans['childs']=$data['childs'];

return infra_ret($ans);
