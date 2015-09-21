{groups:}
	<style>
		.catgrouplist .img {
			vertical-align:middle;
			text-align:center;
			width:100px;
			padding:4px;
			height:90px;
			background-color:white;
		}
		.catgrouplist .name {
			text-align:left;
			vertical-align:middle;
			font-size:20px;
		}

	</style>
	<div class="catgrouplist row">
		{::groups_group}
	</div>
	<script>
		infra.when(infrajs,'onshow',function(){
			var layer=infrajs.getUnickLayer("{unick}");
			$('#'+layer.div).find('.catgrouplist a').hover(function(){
				$(this).addClass('bigbtnover');
			},function(){
				$(this).removeClass('bigbtnover');
			});
		});
	</script>
	{groups_group:}
		<div class="col-sm-6">
			<a onclick="infrajs.scroll='#pathCatalog'" href="?{config.root}/{title}{:mark.set}">
				<table>
					<tr>
						<td class="img">
							{pos.producer?:gimg}
						</td>
						<td class="name">
							{name}
						</td>
					</tr>
				</table>
			</a>
		</div>
		{gimg:}<img src="?*imager/imager.php?src={infra.conf.catalog.dir}{pos.producer}/{pos.article}/&w=100&h=80">
{breadcrumbs:}
	<ul class="breadcrumb">
		{::brcrumb}
	</ul>
	{brcrumb:}
		{~last()?:crumblast?:crumb}
	{crumb:}
		<li><a href="?catalog{href?:/}{href}{add?:add?(nomark|:mark.set)}">{title}</a></li>
	{crumblast:}
		<li class="active">{title}</li>
	{add:}{:mark.add}{add}
{mark::}*catalog/mark.tpl
{/:}/
{menu:}
	<div style="margin-top:10px">
		<ul class="nav nav-pills">
			{::items}
		</ul>
	</div>
	{items:}
		<li role="presentation"><a href="?{config.root}/{~key}">{title}</a></li>
