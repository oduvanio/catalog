<h1>{conf_title|:Продукция}</h1>
{data.childs:cat.groups}
<ul class="nav nav-pills">
	{data.menu::rubitems}	
</ul>
{rubitems:}
	<li role="presentation"><a href="?{crumb}/{~key}">{title}</a></li>
{cat::}*catalog/cat.tpl