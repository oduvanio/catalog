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
{searchbad:}
	<h1>{val}</h1>
	<p>
		К сожалению ничего не найдено.
	</p>
	{text}
{isproducer:}producer
{isgroup:}group
{issearch:}search
{searchgood:}
		<hr style="margin-bottom: 5px;">
		
		<hr style="margin-top: 5px;">
		{data.is=:isproducer?:Производитель}{data.is=:isgroup?:Группа}{data.is=:issearch?:Поиск}
		<h1 style="margin-bottom:5px; margin-top:5px;">{data.name}</h1>
		<div style="margin-bottom:5px">{data.count} {~words(data.count,:позиция,:позиции,:позиций)}</div>
		{~length(data.bread.prods)?:search_prods}
		{~length(data.bread.groups)?:search_groups}
		
	
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
		<a style="font-size:16px; line-height:24px;" href="?{crumb.parent}/{title}{:cat.msradd}" title="Показать группу «{title}»">{title}</a>{~last()|:br}
	
	{br:}<br>
	{cat_plink:}/{title}
{search_groups:}
	<table cellspacing="0" cellpadding="0" style="margin:5px 0 10px 0;">
		{data.bread.groups::bread_group}
	</table>
{search_prods:}
	<hr style="margin-bottom: 5px;">
	<div style="font-size:12px;">
		Производители: {data.bread.prods::bread_prod}
	</div>
	<hr style="margin-top: 5px; margin-bottom: 5px;">
	<script type="text/javascript">
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer('{unick}');
			if(!layer.config)layer.config={ };
			var data=infrajs.getData(layer);
			if(data.prodpage){
				layer.config.sel=layer.crumb.name;
			}
			$('#'+layer.div).find('.someprod').click(function(){
				var sel=$(this).data('name');
				if(layer.config.sel==sel){
					layer.config.sel='ПРОДУКЦИЯ';
				}else{
					layer.config.sel=sel;						
				}
				infrajs.run(infrajs.getAllLayers(),function(l){
					if(!layer.conf_prod)return;
					if(!l.config)l.config={ };
					l.config.sel=layer.config.sel;
				});
				infrajs.check();
			});
		});
	</script>
{bread_prod:}
	<button style="padding:4px 8px; cursor:pointer; margin-right:6px; {.=data.sel?:bread_sel2}" data-name="{.}" class="someprod{.=data.sel?: sel}">
		{.}
	</button> 
{bread_sel2:} border: 2px inset gray;
{bread_sel:} font-weight:bold
{bread_logo:}<a href="?{crumb.parent}/{data.sel}{:cat.msrsave}"><img class="right" style="margin:5px" src="?*imager/imager.php?h=40&or=img/bg.png&src=*Каталог/{data.sel}/"></a>
{bread_group:}
	{~even()?:s_tr}
	<td style="padding:2px 10px 2px 0;{title=crumb.name?:bread_sel}"><a href="?{crumb.parent}/{title}{:cat.msrsave}">{name}</a></td>
	{~odd()?:e_tr}
	{s_tr:}<tr>
	{e_tr:}</tr>
{cat_item:}
	<div class="position">
		<div style="text-align:right">{time?~date(:j F Y,time)}</div>
		<h2 style="clear: both;">
			<a class="href" href="?{crumb.parent}/{producer}/{article}{:cat.msrsave}">{Наименование}</a>
		</h2>
		<table style="width:100%">
		<tr>
		<td style="width:160px; padding-right:10px;">
			<a class="href" href="?{crumb.parent}/{producer}/{article}{:cat.msrsave}">
				<div class="pic">
					<img src="?*imager/imager.php?mark=1&w=160&src={infra.conf.catalog.dir}{producer}/{article}/&or=*imager/empty.png" />
				</div>
			</a>
		</td>
		<td style="padding-right:5px">
			{:producerSmall}
			<a onclick="infrajs.scroll='#pathCatalog'" class="href" href="?{crumb.parent}/{producer}/{article}{:cat.msrsave}">
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
	<div style="margin-top:5px; font-size:12px;">
		<a title="Посмотреть продукцию {Производитель}" href="?{crumb.parent}/{producer}{:cat.msrsave}">{Производитель}</a>, 
		<a title="Перейти к группе {group_title}" href="?{crumb.parent}/{group_title}{:cat.msrsave}">{group_title}</a>
	</div>

{producerSmall:}
	<div style="float:right; background-color:white; padding:5px; margin-left:5px; margin-bottom:5px;">
		<a onclick="infrajs.scroll='#pathCatalog'" title="Посмотреть продукцию {Производитель}" href="?{crumb.parent}/{producer}{:cat.msrsave}">
			<img  src="?*imager/imager.php?w=100&h=100&src={infra.conf.catalog.dir}{producer}/&or=*imager/empty.png" />
		</a>
	</div>

{pages:}
<div class="col-xs-12">
	{data.numbers?:pagenumbers?:pagesettings}
</div>
{pagenumbers:}
	<ul class="pagination">
		{data.numbers::pagenum}
	</ul>
{pageset:}
	<a class="pull-right" onclick="$('.settings').slideToggle('fast')" style="cursor:pointer"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></a>
	<div class="settings alert alert-info" style="display:none">
		Сортировать <a style="font-weight:{data.md.sort=:def?:bold}" onclick="infrajs.scroll='.pagination'" href='?{crumb.parent}/{data.val}{:cat.msradd}sort:null'>по умолчанию</a>, 
		<a  style="font-weight:{data.md.sort=:name?:bold}" onclick="infrajs.scroll='.pagination'" href='?{crumb.parent}/{data.val}{:cat.msradd}sort:"name"'>по названию</a><br>
		Показывать по 
		<select onchange="infrajs.scroll='.pagination'; infra.Crumb.go('?{crumb.parent}/{data.val}{:cat.msradd}count:'+$(this).val())">
			<option {data.md.count=:5?:selected}>5</option>
			<option {data.md.count=:20?:selected}>20</option>
			<option {data.md.count=:100?:selected}>100</option>
		</select> позиций на странице<br>
		Показать в <a href='?{crumb.parent}/{data.val}{:cat.msradd}direct:{data.md.direct?:false?:true}'>обратном порядке</a> 
	</div>
{pagenum:}
	<li class="{active?:pageact}{empty?:pagedis}">
		{empty?:pagenumt?:pagenuma}
	</li>
	{pagenumt:}<a>{title}</a>
	{pagenuma:}<a onclick="infrajs.scroll='.pagination'" href="?{crumb.parent}/{data.val}{:cat.msrsave}&p={num}">{title}</a>
{pageact:} active
{pagedis:} disabled
{space:}&nbsp;
{cat::}*catalog/cat.tpl