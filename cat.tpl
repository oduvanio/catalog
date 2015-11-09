{groups:}
	<style>
		.catgrouplist .img {
			vertical-align:middle;
			text-align:center;
			width:120px;
			padding-right:10px;
			padding-left:0;
			height:90px;
			background-color:white;
		}
		.catgrouplist .name {
			text-align:left;
			vertical-align:middle;
			font-size:140%;
		}

		@media(max-width:992px) { 	/*md*/
			.catgrouplist .img {
				width:100px;
			}
			.catgrouplist .name {
				font-size:90%;
			}
		}
		@media(max-width:768px) { 	/*sm*/
			.catgrouplist .img {
				width:120px;
			}
			.catgrouplist .name {
				font-size:120%;
			}
		}

		
	</style>
	<div class="catgrouplist row">
		{::groups_group}
	</div>
	{groups_group:}
		<div class="col-sm-6">
			<a class="thumbnail" onclick="infrajs.scroll='.breadcrumb'" href="?{infrajs.unicks.catalog.crumb}{:mark.add}group::.{title}:1">
				<table>
					<tr>
						<td class="img">
							{pos.images.0?:gimg}
						</td>
						<td class="name">
							{name}
						</td>
					</tr>
				</table>
			</a>
		</div>
		{gimg:}<img src="?*imager/imager.php?src={pos.images.0}&w=110&h=80">
{breadcrumbs:}
	<ul class="breadcrumb">
		{::brcrumb}
	</ul>
	{brcrumb:}
		{~last()?:crumblast?:crumb}
	{crumb:}
		<li><a onclick="infrajs.scroll='.breadcrumb'" href="?{infrajs.unicks.catalog.crumb}{href?:/}{href}{add?:add?(nomark|:mark.set)}">{title}</a></li>
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
		<li role="presentation"><a onclick="infrajs.scroll='.breadcrumb'" href="?{config.root}/{~key}{:mark.set}">{title}</a></li>
