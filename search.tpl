{root:}
	<style scoped>
		.href {
			text-decoration:none;
		}
		.position {
			margin-bottom:40px;
		}
	</style>
	{data.breadcrumbs:cat.breadcrumbs}
	<div class="pull-right">{data.is=:isproducer?:Производитель}{data.is=:isgroup?:Группа}{data.is=:issearch?:Поиск}</div>
	{~length(data.list)?data:searchgood?data:searchbad}
{showfilters:}
	<style scoped>
		.showfilters a {
			color:inherit;
			text-decoration:none;
		}
		.showfilters a:hover {
			color:red;
			text-decoration:none;
		}
	</style>
	<div class="showfilters alert alert-success" role="alert">
		Фильтры:
		{data.filters::showfilter}
	</div>
	{showfilter:}
		<div class="item" style="cursor:pointer" onclick="infra.scroll='.breadcrumb'; infra.Crumb.go('?{infrajs.unicks.catalog.crumb}{:cat.mark.add}{name}:'); return false;">
			<a onclick="return false;" href="?{infrajs.unicks.catalog.crumb}{:cat.mark.add}{name}:">
				<span class="glyphicon glyphicon-remove" aria-hidden="true" style="color:red; font-size:80%"></span>
				{title}: <b>{value}</b>
			</a>
		</div>
{searchbad:}
	<h1>{title}</h1>
	<p>К сожалению позиции не найдены.</p>
	{~length(data.filters)?:showfilters}
	
	{text}
{isproducer:}producer
{isgroup:}group
{issearch:}search
{searchgood:}
	<h1>{data.name}</h1>
	<p>{data.count} {~words(data.count,:позиция,:позиции,:позиций)}</p>
	{~length(data.filters)?:showfilters}
	
	{data.childs:cat.groups}
	{:pages}
	{:pageset}
	<hr>
	{:extend.pos-list}

	{list::cat_item}


	<hr>
	{:pages}
	<p>{descr}</p>
	{text}
	<hr>
	<h2>{data.name}</h2>
	{~length(data.filters)?:showfilters}
	<p>
		<a onclick="infra.scroll='.breadcrumb';" href="?{infrajs.unicks.catalog.crumb}{:cat.mark.set}">{data.count} {~words(data.count,:позиция,:позиции,:позиций)}</a>
	</p>
{cat_item:}
	<div class="position">
		<div style="text-align:right">{time?~date(:j F Y,time)}</div>
		{:extend.pos-item}
	</div>
{pages:}

{data.numbers?:pagenumbers}

{pagenumbers:}
	<ul class="pagination">
		{data.numbers::pagenum}
	</ul>
{pageset:}
	<div class="clearfix"></div>
	<a class="pull-right" onclick="$('.settings').slideToggle('fast')" style="cursor:pointer"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></a>
	<div class="settings alert alert-info" style="display:none">
		Сортировать <a style="font-weight:{data.md.sort??:bold}" onclick="infrajs.scroll='.pagination'" href='?{infrajs.unicks.catalog.crumb}{:cat.mark.add}sort'>по умолчанию</a>,
			<a style="font-weight:{data.md.sort=:name?:bold}" onclick="infrajs.scroll='.pagination'" href='?{infrajs.unicks.catalog.crumb}{:cat.mark.add}sort:name'>по названию</a>, 
			<a style="font-weight:{data.md.sort=:cost?:bold}" onclick="infrajs.scroll='.pagination'" href='?{infrajs.unicks.catalog.crumb}{:cat.mark.add}sort:cost'>по цене</a>, 
			<a style="font-weight:{data.md.sort=:change?:bold}" onclick="infrajs.scroll='.pagination'" href='?{infrajs.unicks.catalog.crumb}{:cat.mark.add}sort:change'>по дате</a><br>
		Показывать по
		<select onchange="infrajs.scroll='.pagination'; infra.Crumb.go('?{infrajs.unicks.catalog.crumb}{:cat.mark.add}count:'+$(this).val())">
			<option {data.md.count=:5?:selected}>5</option>
			<option {data.md.count=:10?:selected}>10</option>
			<option {data.md.count=:20?:selected}>20</option>
			<option {data.md.count=:100?:selected}>100</option>
		</select> позиций на странице<br>
		Показать в <a style="font-weight:{data.md.reverse?:bold}" onclick="infrajs.scroll='.pagination'" href='?{infrajs.unicks.catalog.crumb}{:cat.mark.add}reverse:{data.md.reverse??:1}'>обратном порядке</a>.
	</div>
{pagenum:}
	<li class="{active?:pageact}{empty?:pagedis}" style="padding-top:10px">
		{empty?:pagenumt?:pagenuma}
	</li>
	{pagenumt:}<a>{title}</a>
	{pagenuma:}<a onclick="infrajs.scroll='.pagination'" href="?{crumb}{:cat.mark.set}&p={num}">{title}</a>
{pageact:} active
{pagedis:} disabled
{space:}&nbsp;
{cat::}*catalog/cat.tpl
{extend::}*catalog/extend.tpl
