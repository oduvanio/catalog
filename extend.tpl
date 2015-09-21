{pos-page:}{Цена?:poscostblock}
    {poscostblock:}
    	<div class="alert alert-success">
    		Цена: <span style="font-size:20px">{~cost(Цена)} руб.</span><br>
    		По вопросам приобретения обращайтесь по телефонам в <a href="?contacts">контактах</a>.
    	</div>
{pos-sign:}
    <p>
        Задать вопрос о {Производитель} {Артикул} можно с помощью <span class="a showContacts">формы для сообщений</span> или c помощью других <a href="?contacts">контактов</a>.
    </p>
    <p>
        Перейти к группе <a onclick="infrajs.scroll='.pagination'" href="?{crumb.parent.parent}/{group_title}{:cat.mark.client.set}">{group_title}</a><br>
    </p>
{pos-list:}
    <style scoped>
        .cat_item .title {
            display:block;
            background-color:#EFEFEF;
            text-decoration: none;
            color:#222222;
        }
        .cat_item .padding {
            padding:4px 8px;
        }
        .cat_item .title:hover {
            background-color:#009EC3;
            color:white;
        }
    </style>
{pos-item:}
    <div class="row cat_item">
        <div class="col-xs-4 col-sm-3">
            <a class="thumbnail" href="?{infrajs.unicks.catalog.crumb}/{producer}/{article}/">
                <img src="?*imager/imager.php?mark=1&w=256&h=256&src={infra.conf.catalog.dir}{producer}/{article}/&or=*imager/empty" />
            </a>
        </div>
        <div class="col-xs-8 col-sm-9">
            <a class="title padding" href="?{infrajs.unicks.catalog.crumb}/{producer}/{article}/">{Наименование}</a>
            <div class="padding">
                <b><a href="?{infrajs.unicks.catalog.crumb}/{producer}/{article}/">{Производитель} {Артикул}</a></b>
                <div class="pull-right"><a href="?{infrajs.unicks.catalog.crumb}/{group_title}">{group_title}</a></div>
            </div>
            {more?:havemore?:nomore}
        </div>
    </div>
    {havemore:}
        <div class="padding" style="font-family:Tahoma; font-size:85%">
            <a title="Посмотреть продукцию {Производитель}" href="?{infrajs.unicks.catalog.crumb}/{producer}" class="right">
                <img src="?*imager/imager.php?w=100&h=100&src={infra.conf.catalog.dir}{producer}/&or=*imager/empty" />
            </a>
            {more::cat_more}
        </div>
        <div class="padding">
            <span class="a" onclick="$(this).next().slideToggle();">Описание</span>
            <div style="display:none;">
                {Описание}
                <b><a href="?{infrajs.unicks.catalog.crumb}/{producer}/{article}/">Подробнее</a></b>
            </div>
        </div>
    {nomore:}
        <a title="Посмотреть продукцию {Производитель}" href="?{infrajs.unicks.catalog.crumb}/{producer}" class="right">
            <img src="?*imager/imager.php?w=100&h=100&src={infra.conf.catalog.dir}{producer}/&or=*imager/empty" />
        </a>
        <div class="padding">
            <div style="font-family:Tahoma; font-size:85%;">{Описание}</div>
            <b><a href="?{infrajs.unicks.catalog.crumb}/{producer}/{article}/">Подробнее</a></b>
        </div>
    {cat_more:}{(.&(.!:no))?:more}
    {more:}{~key}:&nbsp;{.}{~last()|:comma}
    {comma:}, 
    {no:}Нет
{cat::}*catalog/cat.tpl
