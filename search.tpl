{root:}
	<style>
		.href {
			text-decoration:none;
		}
		.position {
			margin-bottom:40px;
		}
	</style>
	{data.breadcrumbs:cat.breadcrumbs}
	{~length(data.list)?data:searchgood?data:searchbad}
{showfilters:}
	<div class="alert alert-success" role="alert">
		Фильтры:
		{data.filters::showfilter}
	</div>

	{showfilter:}

	<br><a href="?{crumb}{:cat.mark.add}{name}" style="text-decoration:none;">
  		<span class="glyphicon glyphicon-remove" aria-hidden="true" style="color:red; font-size:80%"></span>
	</a> {title}: {value}


{searchbad:}
	<h1>{val}</h1>
	{~length(data.filters)?:showfilters}
	<p>
		К сожалению ничего не найдено.
	</p>
	{text}
{isproducer:}producer
{isgroup:}group
{issearch:}search
{searchgood:}
	{data.is=:isproducer?:Производитель}{data.is=:isgroup?:Группа}{data.is=:issearch?:Поиск}
	<h1 style="margin-bottom:5px; margin-top:5px;">{data.name}</h1>
	<div style="margin-bottom:5px">{data.count} {~words(data.count,:позиция,:позиции,:позиций)}</div>
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
	{text?data.childs:cat.groups}
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
		Сортировать <a style="font-weight:{data.fd.sort=:def?:bold}" onclick="infrajs.scroll='.pagination'" href='?{config.root}/{data.val}{:cat.mark.add}sort'>по умолчанию</a>,
		<a style="font-weight:{data.fd.sort=:name?:bold}" onclick="infrajs.scroll='.pagination'" href='?{config.root}/{data.val}{:cat.mark.add}sort:name'>по названию</a><br>
		Показывать по
		<select onchange="infrajs.scroll='.pagination'; infra.Crumb.go('?{config.root}/{data.val}{:cat.mark.add}count:'+$(this).val())">
			<option {data.fd.count=:5?:selected}>5</option>
			<option {data.fd.count=:20?:selected}>20</option>
			<option {data.fd.count=:100?:selected}>100</option>
		</select> позиций на странице<br>
		Показать в <a  onclick="infrajs.scroll='.pagination'" href='?{config.root}/{data.val}{:cat.mark.add}direct:{data.fd.direct?:false?:true}'>обратном порядке</a>.
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
