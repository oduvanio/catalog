<style>
	#position {
		font-family: Tahoma;
		font-size: 13px;
		color: #404040;
	}
	#position a {
		/*color: #cb5b1e;*/
	}
	#position a img {
		border: none;
	}
	#position .bigimage {
		border-top:1px dotted gray;
		text-align:center;
		padding-top:10px;
		padding-bottom:10px;
	}
	#position h1 {
		margin-top: 0px;
		padding-top: 0px;
		padding-bottom: 4px;
	}
	#position h2 {
		margin:0;padding:0;
		padding-bottom: 4px;
		margin-top: 15px;
		margin-bottom: 9px;
	}
	#position h3 {
		margin:0;padding:0;
		padding-bottom: 4px;
		margin-top: 15px;
		margin-bottom: 9px;
	}
	#position .files {
		margin:0;padding:0;
		list-style: none;
		margin-top: 6px;
	}
		#position .files li {
			line-height: 18px;
			padding-left: 25px;
		}
		#position .files .ico {
			/*background-image: url("images/pdf_icon.png");*/
			background-repeat: no-repeat;
			background-position: 0px 1px;
		}
	/*#position .information {
		line-height: 18px;
		margin-top: 25px;
		font-weight: bold;
	}*/
</style>
{data.result?data.pos:start}

{start:}
	<div id="position">
		<div style="float:right">

		{:producer}
		</div>
		<h1>
			{Наименование}<br>{Производитель} {Артикул}
		</h1>
		{~length(images)?:images}
		{Цена?:poscost}
		<div style="color:gray; margin-bottom:30px">{Описание}</div>
		{texts::text}
		{~length(files)?:files}
		<p></p>
		{~parse(Подпись)}
		<p>
			Задать вопрос о {Производитель} {Артикул} можно с помощью <span class="a showContacts">формы для сообщений</span> или c помощью других <a href="?contacts">контактов</a>.
		</p>
		<p>
			Перейти к группе <a onclick="infrajs.scroll='.pagination'" href="?{crumb.parent.parent}/{group_title}{:cat.mclsave}">{group_title}</a><br>
			
		</p>
	</div>
{poscost:}
	<div class="alert alert-success">
		Цена: <span style="font-size:20px">{~cost(Цена)} руб.</span><br>
		{Наличие?: Есть в наличии.} По вопросам приобретения обращайтесь по телефонам в <a href="?contacts">контактах</a>.
	</div>
{files:}
	<h2>Файлы для {Продажа} {Производитель} {Артикул} </h2>
		<ul class="files">
			{files::file}
		</ul>
	{file:}
		<li class="ico" style="background-image:url('?*infra/theme.php?*/autoedit/icons/{ext}.png')">
			<a href="{src}">{name}</a> {size}&nbsp;Mb
		</li>
{text:}
	{.}
{imgsrc:}{.}
{images:}
	<div style="text-align:center; background-color:white; padding:10px; ">
		{images::image}
	</div>
	<div class="bigimage"></div>
	{image:}
	<a onclick="return false" title="{..Наименование}" href="?*imager/imager.php?src={:imgsrc}">
		 <img 
		title="{data.pos.Производитель} {data.pos.Артикул}"
		style="cursor:pointer"
		onclick="var img=document.getElementById('catimg{~key}'); if(img){ $(img).toggle(); return; }; 
				$('#position .bigimage').html('<img style=\'border-bottom:1px dotted gray;\' onclick=\'$(this).hide()\' id=\'catimg{~key}\' src=\'?*imager/imager.php?mark=1&w=590&src={:imgsrc}\' />')" 
		src="?*imager/imager.php?mark=1&h=100&src={:imgsrc}" />
		</a>
{producer:}
	<div style="float:right; background-color:white; padding:10px 10px 10px 10px; margin-left:5px; margin-bottom:5px;">
		<a onclick="infrajs.scroll='.pagination'" title="Посмотреть продукцию {producer}" href="?{crumb.parent.parent}/{producer}{cat.mclsave}">
			<img style="margin-left:5px" src="?*imager/imager.php?w=160&h=100&src={infra.conf.catalog.dir}{producer}/&or=*imager/empty.png" />
		</a>
	</div>
<!--	<div style="text-align:right; font-size: 11px; margin-top:5px;">
		{producer.Страна|}
	</div>
	-->
{cat::}*catalog/cat.tpl