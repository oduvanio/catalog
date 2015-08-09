<h1 title="c {~date(:d.m.Y,data.stat.time)}">Последние запросы набранные в строке поиска по каталогу</h1>
<table class="table table-striped">
	<tr><td></td><td>Фразы</td></tr>
	{data.stat.users::statuser}
</table>
<p>
	<a href="?{crumb.parent}">Каталог</a>
</p>
{data.text}
{statuser:}
	<tr>
		<td style="vertical-align:bottom; font-size:20px; text-align:left; color:gray;"><b title="от {~date(:d.m.Y,time)}">{cat_id}</b></td>
		<td>{list::statitem}</td>
	</tr>
{statitem:}<a href="?{crumb.parent}/{val}" title="от {~date(:d.m.Y,time)}">{val}</a><sup>{count}</sup>{~last()|:statsep}
{statsep:} |  