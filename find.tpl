{root:}
	{data.breadcrumbs:cat.breadcrumbs}
	<h1>Поиск по каталогу</h1>
	<form style="margin-bottom:30px" class="form-horizontal" onsubmit="
		var val=$(this).find('[type=text]').val();
		val=infra.forFS(val);
		var layer=infrajs.find('unick','catalog');
		infra.Crumb.go(layer.crumb.name+'/'+val);
		setTimeout(function(){
			$.getJSON(infra.theme('*catalog/stat.php?submit=1&val='+val));
		},1);
		return false;">

			<div class="row">
				<div class="col-md-6 col-sm-9" style="margin-bottom:15px;">
					<input class="form-control input-lg" name="search" type="text" placeholder="Поиск по каталогу">
				</div>
				<div class="col-md-3 col-sm-3" style="margin-bottom:15px">
					<input class="btn btn-primary btn-lg" type="submit" value="Искать">
				</div>
			</div>
	</form>
	{data.childs:cat.groups}
{cat::}*catalog/cat.tpl