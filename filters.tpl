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
			{~words(data.count,:ser1,:ser2,:ser5)} <a onclick="infra.scroll='.breadcrumb'" href="?catalog{:cat.mark.set}">{data.count} {~words(data.count,:pos1,:pos2,:pos5)}</a>
		</div>
		{pos1:}позиция
		{pos2:}позиции
		{pos5:}позиций
		{ser1:}Найдена
		{ser2:}Найдены
		{ser5:}Нейдено
{param:}
	<div style="margin-top:5px; border-bottom:1px solid #ccc">
		{:optionHead}
		{row::option}
	</div>
{option:}
	<div class="checkbox {count??:disabled}">
	    <label>
	      {:box} {title}&nbsp;<small>{count}</small>
	    </label>
  	</div>
{optionHead:}
	<div class="checkbox">
	    <label style="font-weight:bold;">
	      {:box}
		  {title}&nbsp;<small>{count}</small>
	    </label>
  	</div>
{checked:}checked
{disabled:}disabled
{box:}<input onchange="infra.scroll=false; infra.Crumb.go('?catalog{:cat.mark.add}{add}')" {checked?:checked} type="checkbox">
