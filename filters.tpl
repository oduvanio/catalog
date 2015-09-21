{producers:}
	<h3>Производители</h3>
	<ul>
		{data.list::prodlist}
	</ul>
	{prodlist:}
		<li><a{~key=data.fd.producer?:selprod} href="?{infrajs.unicks.catalog.crumb}/{~key}{:cat.mark.add}producer:{~key}">{~key} - {.}</a></li>
	{selprod:} style="font-weight:bold"
{cat::}*catalog/cat.tpl
