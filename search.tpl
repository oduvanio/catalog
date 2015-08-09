<style>
	.href {
		text-decoration:none;
	}
	.position {
		margin-bottom:40px;
	}
</style>
{data.result?data:searchgood?data:searchbad}
{searchbad:}
	<h1>{val}</h1>
	<p>
		К сожалению ничего не найдено.
	</p>
	<p>
		<a href="?{crumb.parent}">{crumb.parent.name}</a>
	</p>
	{text}
{isproducer:}producer
{isgroup:}group
{issearch:}search
{searchgood:}
		<hr style="margin-bottom: 5px;">
		<style>
			#pathCatalog li{
				list-style-type:none;
				display: inline;
			}
			#pathCatalog li:last-child a{
				pointer-events: none;
				cursor: default;
				text-decoration: none;
				color: gray;
			}
			#pathCatalog li:first-child span {
				display:none;
			}
		</style>
		<ul id="pathCatalog">
			{breadcrumbs::cat_childsp}
		</ul>
		<hr style="margin-top: 5px;">
		{data.is=:isproducer?:Производитель}{data.is=:isgroup?:Группа}{data.is=:issearch?:Поиск}
		<h1 style="margin-bottom:5px; margin-top:5px;">{data.name}</h1>
		<div style="margin-bottom:5px">{data.count} {~words(data.count,:позиция,:позиции,:позиций)}</div>
		{~length(data.bread.prods)?:search_prods}
		{~length(data.bread.groups)?:search_groups}
		
	
	{data.childs:cat.groups}
	<div class="col-xs-12">
		{:pages}
		<hr style="margin-top:0;">
	</div>
	<div style="background-color:white;">
		{list::cat_item}
	</div>
	<div class="col-xs-12">
		<hr style="margin-bottom:0; margin-top: 0;">
		{:pages}
	</div>
	<p>{descr}</p>
	{text}
	{text?data.childs:cat.groups}
	{cat_childs:}
		<a style="font-size:16px; line-height:24px;" href="?{crumb.parent}/{title}" title="Показать группу «{title}»">{title}</a>{~last()|:br}
	{cat_childsp:}
		<li>
			<span> > </span>
			<a href="?catalog/{.=:catalog??.}" title="Показать группу «{.=:catalog?infra.conf.catalog.title?.}»">{.=:catalog?infra.conf.catalog.title?.}</a></li>
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
{bread_logo:}<a href="?{crumb.parent}/{data.sel}"><img class="right" style="margin:5px" src="?*imager/imager.php?h=40&or=img/bg.png&src=*Каталог/{data.sel}/"></a>
{bread_group:}
	{~even()?:s_tr}
	<td style="padding:2px 10px 2px 0;{title=crumb.name?:bread_sel}"><a href="?{crumb.parent}/{title}">{name}</a></td>
	{~odd()?:e_tr}
	{s_tr:}<tr>
	{e_tr:}</tr>
{cat_item:}
	<div class="position">
		<div style="text-align:right">{time?~date(:j F Y,time)}</div>
		<h2 style="clear: both;">
			<a class="href" href="?{crumb.parent}/{producer}/{article}">{Наименование}</a>
		</h2>
		<table style="width:100%">
		<tr>
		<td style="width:160px; padding-right:10px;">
			<a class="href" href="?{crumb.parent}/{producer}/{article}">
				<div class="pic">
					<img src="?*imager/imager.php?mark=1&w=160&src={infra.conf.catalog.dir}{producer}/{article}/&or=*imager/empty.png" />
				</div>
			</a>
		</td>
		<td style="padding-right:5px">
			{:producerSmall}
			<a class="href" href="?{crumb.parent}/{producer}/{article}">
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
		<a title="Посмотреть продукцию {Производитель}" href="?{crumb.parent}/{producer}">{Производитель}</a>, 
		<a title="Перейти к группе {group_title}" href="?{crumb.parent}/{group_title}">{group_title}</a>
	</div>

{producerSmall:}
	<div style="float:right; background-color:white; padding:5px; margin-left:5px; margin-bottom:5px;">
		<a title="Посмотреть продукцию {Производитель}" href="?{crumb.parent}/{producer}">
			<img  src="?*imager/imager.php?w=100&h=100&src={infra.conf.catalog.dir}{producer}/&or=*imager/empty.png" />
		</a>
	</div>

{pages:}
	<ul class="pagination">
		<li class="disabled"><a href="#">&laquo;</a></li>
		<li class="active"><a href="#">1 <span class="sr-only">(current)</span></a></li>
		<li><a href="#">2</a></li>
		<li><a href="#">3</a></li>
		<li><a href="#">4</a></li>
		<li><a href="#">5</a></li>
		<li><a href="#">6</a></li>
		<li><a href="#">7</a></li>
		<li><a href="#">8</a></li>
		<li><a href="#">...</a></li>
		<li><a href="#">55</a></li>
		<li><a href="#">&raquo;</a></li>
	</ul>
{cat::}*catalog/cat.tpl