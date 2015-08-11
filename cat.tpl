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
			<a href="?{config.root}/{title}{data.fm}">
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