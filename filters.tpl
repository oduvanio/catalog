{producers:}
	<h3>Производители</h3>
	<ul>
		{data.list::prodlist}
	</ul>
	<div class="visible-xs visible-sm">
		<a onclick="infra.scroll='.breadcrumb'" href="?{infrajs.unicks.catalog.crumb}{:cat.mark.set}">Показать</a>
	</div>
	{prodlist:}
		<li><a onclick="infra.scroll=false"{data.fd.producer[~key]?:selprod} href="?{infrajs.unicks.catalog.crumb}{:cat.mark.add}producer.{~key}:1">{~key} - {.}</a></li>
	{selprod:} style="font-weight:bold"
{cat::}*catalog/cat.tpl
{filters:}
<div class="catfilters">
	<style scoped>
		.catfilters .checked {
			font-weight:bold;
		}
		.catfilters small {
			color:#aaa;
			font-size:80%;
		}
		.catfilters label {
			font-weight:normal;
		}
		.catfilters .disabled {
			color:#999;
		}
		
		
	</style>
	{~length(data.template)?:filtersbody}
</div>
	{filtersbody:}
		<h1>Фильтры</h1>
		<div class="space">
			{data.template::param}
		</div>
		<div class="space">
			Найдено <a onclick="infra.scroll='.breadcrumb'" href="?catalog{:cat.mark.set}">{data.search} {~words(data.search,:pos1,:pos2,:pos5)}</a>
		</div>
		{pos1:}позиция
		{pos2:}позиции
		{pos5:}позиций
{param:}
	<div style="margin-top:5px; border-bottom:1px solid #ccc">
		{:optionHead}
		{row::option}
	</div>
{option:}
	<div class="{filter??:disabled}">
		<label>
		  {:box} {title}&nbsp;<small>{filter}</small>
		</label>
	</div>
{optionHead:}
	<div>
		<label style="font-weight:bold;">
		  {data.count!count?:box}
		  {title}&nbsp;<small>{filter}</small>
		</label>
	</div>
{checked:}checked
{disabled:}disabled
{box:}<input onchange="infra.scroll=false; infra.Crumb.go('?catalog{:cat.mark.add}{add}')" {checked?:checked} type="checkbox">
