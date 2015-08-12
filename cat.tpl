{groups:}
	<style>
		.catgrouplist .img {
			vertical-align:middle;
			width:100px;
			padding:4px;
			height:90px;
			text-align:center;
			background-color:white;
		}
		.catgrouplist .name {
			text-align:left;
			font-family:Premjera;
			vertical-align:middle;
			font-size:20px;
			padding-left:4px;
		}
		.catgrouplist a {
			//display:block;
			//width:300px;
			//float:left;
		}
		
	</style>
	<div class="catgrouplist">
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
			<a onclick="infrajs.scroll='#pathCatalog'" href="?{config.root}/{title}{:msrsave}">
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
{msradd:}&m={data.m}:
{mcladd:}&m={infra.Crumb.get.m}:
{msrsave:}{data.m?:msrsaveatr}
	{msrsaveatr:}&m={data.m}
{mclsave:}{infra.Crumb.get.m?:mclsaveatr}
	{mclsaveatr:}&m={infra.Crumb.get.m}

{breadcrumbs:}
	<ul class="breadcrumb">
		{::brcrumb}
	</ul>
	{brcrumb:}
		{~last()?:crumblast?:crumb}
	{crumb:}
		<li><a href="?catalog/{href}{:msrsave}">{title}</a></li>
	{crumblast:}
		<li class="active">{title}</li>