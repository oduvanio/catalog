<h1>Производители!</h1>
<div style="padding:10px; font-size:12px; margin-bottom:20px;">
	{data.list::catprod1}
</div>
<div style="background-color:white; padding:10px; text-align:center; margin-bottom:20px;">
	{data.list::catprod}
</div>
{data.text}
<div style="margin-top:10px">
	<a href="?{crumb.parent}">Каталог</a>
</div>
{catprod1:}
<a href="?{crumb.parent}/{~key}" title="{~key} {.}">{~key}</a>{~last()?:point?:comma} 
{comma:}, 
{point:}.
{catprod:}
	<a href="?{crumb.parent}/{~key}" title="{~key} {.}"><img alt="{~key}" style="margin-bottom:10px" src="?*imager/imager.php?w=100&src={infra.conf.catalog.dir}{~key}/&or=*imager/empty.png"></a>