<?php

$ans=array(
	'title'=>'Проверка обработчиков каталога - позиция, группа, производители, рубрики и тп.'
);

$data=infra_loadJSON('*catalog/rubrics.php');
if (!$data) {
	return infra_err($ans, 'Ошибка rubrics.php');
}

$data=infra_loadJSON('*catalog/producers.php');
if (!$data) {
	return infra_err($ans, 'Ошибка producers.php');
}

return infra_ret($ans);
