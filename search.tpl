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
	
	<br><a href="?{crumb}{:cat.filter.add}{name}" style="text-decoration:none;">
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
	
	<hr style="margin-top:0;">
	<div class="col-xs-12">
	{:pageset}
	</div>
	<div style="background-color:white;">
		{list::cat_item}
	</div>
	<hr style="margin-bottom:0; margin-top: 0;">
	{:pages}
	<p>{descr}</p>
	{text}
	{text?data.childs:cat.groups}
	{cat_childs:}
		<a style="font-size:16px; line-height:24px;" href="?{config.root}/{title}{:cat.filter.set}" title="Показать группу «{title}»">{title}</a>{~last()|:br}
	
	{br:}<br>
	{cat_plink:}/{title}
{cat_item:}
	<div class="position">
		<div style="text-align:right">{time?~date(:j F Y,time)}</div>
		<h2 style="clear: both;">
			<a class="href" href="?{config.root}/{producer}/{article}{:cat.filter.set}">{Наименование}</a>
		</h2>
		<table style="width:100%">
		<tr>
		<td style="width:160px; padding-right:10px;">
			<a class="href" href="?{config.root}/{producer}/{article}{:cat.filter.set}">
				<div class="pic">
					<img class="img-rounded" src="?*imager/imager.php?mark=1&w=160&src={infra.conf.catalog.dir}{producer}/{article}/&or=*imager/empty.png" />
				</div>
			</a>
		</td>
		<td style="padding-right:5px">
			{:producerSmall}
			<a onclick="infrajs.scroll='#pathCatalog'" class="href" href="?{config.root}/{producer}/{article}{:cat.filter.set}">
				<h3 style="margin-top:0; margin-bottom:10px;">
					{Производитель} {Артикул}
				</h3>
			</a>
			<div>
				{Описание}
			{:group}
			</div>
		</td>
		</tr>
		</table>
	</div>

{group:}
	<div style="margin-top:5px; font-size:90%;">
		<a title="Посмотреть продукцию {Производитель}" href="?{config.root}/{producer}{:cat.filter.add}producer:{producer}">{Производитель}</a>, 
		<a title="Перейти к группе {group_title}" href="?{config.root}/{group_title}{:cat.filter.set}">{group_title}</a>
	</div>

{producerSmall:}
	<div style="float:right; background-color:white; padding:5px; margin-left:5px; margin-bottom:5px;">
		<a onclick="infrajs.scroll='#pathCatalog'" title="Посмотреть продукцию {Производитель}" href="?{config.root}/{producer}{:cat.filter.add}producer:{producer}">
			<img  src="?*imager/imager.php?w=100&h=100&src={infra.conf.catalog.dir}{producer}/&or=*imager/empty.png" />
		</a>
	</div>

{pages:}
<div class="col-xs-12">
	{data.numbers?:pagenumbers}
</div>
{pagenumbers:}
	<ul class="pagination">
		{data.numbers::pagenum}
	</ul>
{pageset:}
	<a class="pull-right" onclick="$('.settings').slideToggle('fast')" style="cursor:pointer"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></a>
	<div class="settings alert alert-info" style="display:none">
		Сортировать <a style="font-weight:{data.fd.sort=:def?:bold}" onclick="infrajs.scroll='.pagination'" href='?{config.root}/{data.val}{:cat.filter.add}sort'>по умолчанию</a>, 
		<a style="font-weight:{data.fd.sort=:name?:bold}" onclick="infrajs.scroll='.pagination'" href='?{config.root}/{data.val}{:cat.filter.add}sort:name'>по названию</a><br>
		Показывать по 
		<select onchange="infrajs.scroll='.pagination'; infra.Crumb.go('?{config.root}/{data.val}{:cat.filter.add}count:'+$(this).val())">
			<option {data.fd.count=:5?:selected}>5</option>
			<option {data.fd.count=:20?:selected}>20</option>
			<option {data.fd.count=:100?:selected}>100</option>
		</select> позиций на странице<br>
		Показать в <a  onclick="infrajs.scroll='.pagination'" href='?{config.root}/{data.val}{:cat.filter.add}direct:{data.fd.direct?:false?:true}'>обратном порядке</a>.
	</div>
{pagenum:}
	<li class="{active?:pageact}{empty?:pagedis}">
		{empty?:pagenumt?:pagenuma}
	</li>
	{pagenumt:}<a>{title}</a>
	{pagenuma:}<a onclick="infrajs.scroll='.pagination'" href="?{crumb}{:cat.filter.set}&p={num}">{title}</a>
{pageact:} active
{pagedis:} disabled
{space:}&nbsp;
{cat::}*catalog/cat.tpl