
{data.breadcrumbs:cat.breadcrumbs}
<h1>{infra.conf.catalog.title}</h1>
{data.childs:cat.groups}
<ul class="nav nav-pills">
	{data.menu::rubitems}	
</ul>
{rubitems:}
	<li role="presentation"><a href="?{crumb}/{~key}">{title}</a></li>
{cat::}*catalog/cat.tpl
{cat.msrsave:}{:cat.mclsave}
{cat.msradd:}{:cat.mcladd}