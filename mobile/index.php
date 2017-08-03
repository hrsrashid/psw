<?php
// if ($_SERVER['SERVER_PORT'] == 80)
// 	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	

?>
<!DOCTYPE HTML>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="author" content="Хурамшин Рашид">
<meta name="description" content="менеджер паролей для мобильных устройств онлайн">
<meta name="keywords" content="менеджер паролей для мобильных устройств">
<title>Менеджер паролей для мобильных устройств</title>
<link rel="stylesheet" href="/jqm.css" />
<script src="/jq.js"></script>
<script src="/jqm.js"></script>
<script type="text/javascript" src="/main.js"></script>
<script type="text/javascript">
$(document).ready(function()
{
	$("#actLogin").click(startMobile)
})

function startMobile()
{
	$.mobile.showPageLoadingMsg()
	var login = $("input[name=login]").val()
	var pass = $("input[name=password]").val()
	LOGIN = login
	PASSWORD = pass
	
	$.ajax({
		url: "../index.php",
		type: 'POST',
		data: 'password='+pass+'&mlogin='+login,
		success: function(data)
		{
			var resp = $.parseJSON(data)
			
			for (var i in resp)
			{
				if (i == "error")
				{
					$.mobile.hidePageLoadingMsg()
					var html = resp[i]
					if (html != "Вы не ввели пароль или логин")
						html += " <button data-icon='plus' onClick='AddMobUser()'>Создать учетную запись с введенными данными?</button>"
					$("#notifier").html(html)
					
					return
				}
			}
				
			ACCOUNTS = resp;
			FillMobileFirst()
			$.mobile.hidePageLoadingMsg()
			$.mobile.changePage($("#AccountsPage"))
		}
		})
}

function AddMobUser()
{
	$.mobile.showPageLoadingMsg()
	var login = $("input[name=login]").val()
	var pass = $("input[name=password]").val()
	LOGIN = login
	PASSWORD = pass
	
	$.ajax({
		url:"../index.php",
		type:"POST",
		data:"mlogin="+LOGIN+"&password="+PASSWORD+"&adduser=1",
		success:function(data)
		{
			var resp = $.parseJSON(data)
			// TODO: ...
			ACCOUNTS = resp;
			FillMobileFirst()
			$.mobile.hidePageLoadingMsg()
			$.mobile.changePage($("#AccountsPage"))
		}
		})
}

function FillMobileFirst(num)
{
	if (!num)
	{
		var html = "<button data-icon='plus' onClick='mAddNewAccount()'>Добавить</button>"
		ACCOUNTS['sorter'] = {}
		
		for (var i in ACCOUNTS)
		{
			if (i != 'sorter' && ACCOUNTS[i]['name'])
				html += "<button data-icon='arrow-r' data-iconpos='right' num='"+i+"' onClick='mShowAccountContent("+i+")'>"+ACCOUNTS[i]['name']+"</button>"
		}
		$("#AccPageContent").html("")
		$(html).appendTo("#AccPageContent")
		
	}
	else if(num > 0)
	{
		var html = "<button data-icon='arrow-r' data-iconpos='right' num='"+num+"' onClick='mShowAccountContent("+num+")'>"+ACCOUNTS[num]['name']+"</button>"
		$(html).appendTo("#AccPageContent")
		
	}
	else if (num < 0)
	{
		num = -num
		$("button[num="+num+"]").parent().hide()
	}
	
	$("#AccPageContent").trigger("create")
	mReIndex()
}

function mShowAccountContent(num, dir)
{	
	$("input[name=mViewLogin]").val(ACCOUNTS[num]['login'])
	$("input[name=mViewPass]").val(ACCOUNTS[num]['pass'])
	$("input[name=mViewBDID]").val(ACCOUNTS[num]['id'])
	$("input[name=mViewNUM]").val(num)
	
	if (ACCOUNTS[num]['link'] != "none")
	{
		$("#mViewLink").attr("href", ACCOUNTS[num]['link']).show()
	}
	else
		$("#mViewLink").hide()
	
	$.mobile.changePage($("#AccountPage"), (dir == "reverse")?{reverse:true}:{})
}

function mToAccsPage()
{
	$.mobile.changePage($("#AccountsPage"), {reverse:true})
	mFill()
}

function mToAccPage()
{
	$.mobile.changePage($("#AccountPage"), {reverse:true})
}

function mAddNewAccount()
{
	$.mobile.changePage($("#AddNewAccountPage"))
}

function mGeneratePassword(num)
{
	var dict = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0']
	// 0 - 61
	var res = "";
	for (var i = 1; i <= 16; i++)
	{
		res += dict[Math.round(Math.random()*61)]
	}
	
	if (num == -1)
		$("input[name=add_pass]").val(res)
	else if (num == 0)
		$("input[name=mChangePass]").val(res)
}

function mAdd()
{
	$.mobile.showPageLoadingMsg()
	var name = $("input[name=add_name]").val()
	var link = $("input[name=add_link]").val()
	var pass = $("input[name=add_pass]").val()
	var login = $("input[name=add_login]").val()
	
	if (!link)
		link = "none"
	else
	{
		if (link.indexOf("http://") == -1 && link.indexOf("https://") == -1)
			link = "http://"+link
	}
	
	if (!name || !pass || !login)
		return;
	
	$.ajax({
		url:"../index.php",
		type:"POST",
		data:"mlogin="+LOGIN+"&password="+PASSWORD+"&add=1&"+encodeURI("name="+name+"&link="+link+"&login="+login+"&pass="+pass),
		success:function(num)
		{
			ACCOUNTS[num] = {}
			ACCOUNTS[num]['name'] = name
			ACCOUNTS[num]['link'] = link
			ACCOUNTS[num]['login'] = login
			ACCOUNTS[num]['pass'] = pass
			ACCOUNTS[num]['id'] = num
			
			$("input[name=add_name]").val("")
			$("input[name=add_link]").val("")
			$("input[name=add_pass]").val("")
			$("input[name=add_login]").val("")
			
			FillMobileFirst(num)
			$.mobile.hidePageLoadingMsg()
			$.mobile.changePage($("#AccountsPage"), {reverse:true})
		}
	})
}

function mToChangeAcc()
{
	var num = $("input[name=mViewNUM]").val()

	$("input[name=mChangeName]").val(ACCOUNTS[num]['name'])
    $("input[name=mChangeLink]").val((ACCOUNTS[num]['link'] != "none")?ACCOUNTS[num]['link']:"")
	$("input[name=mChangeLogin]").val(ACCOUNTS[num]['login'])
	$("input[name=mChangePass]").val(ACCOUNTS[num]['pass'])
	
	$.mobile.changePage($("#ChangeAccountPage"))
}

function mChangeSave()
{
	$.mobile.showPageLoadingMsg()
	var name = $("input[name=mChangeName]").val()
    var link = $("input[name=mChangeLink]").val()
	var login = $("input[name=mChangeLogin]").val()
	var pass = $("input[name=mChangePass]").val()
	var bdid = $("input[name=mViewBDID]").val()
	var num = $("input[name=mViewNUM]").val()
	
	if (link == "")
		link = "none"
	if (!name || !pass || !login)
	{
		$.mobile.hidePageLoadingMsg()
		return;
	}
	
	$.ajax({
		url:"../index.php",
		type:"POST",
		data:"mlogin="+LOGIN+"&password="+PASSWORD+"&edit="+bdid+"&num="+num+"&"+encodeURI("name="+name+"&link="+link+"&login="+login+"&pass="+pass),
		success:function(data)
		{
			data = $.parseJSON(data)
			if (data)
			{
				ACCOUNTS[data[0]]['name'] = name;
				ACCOUNTS[data[0]]['link'] = link;
				ACCOUNTS[data[0]]['login'] = login;
				ACCOUNTS[data[0]]['pass'] = pass;
				ACCOUNTS[data[0]]['id'] = data[1];
				
				FillMobileFirst(-(parseInt(data[0])))
				FillMobileFirst((parseInt(data[0])))
				$.mobile.hidePageLoadingMsg()
				mShowAccountContent(data[0], "reverse")
			}
		}
	})
}

function mToDelAcc()
{
	$.mobile.changePage($("#DeleteAccountPage"))
}

function mDelAcc()
{
	$.mobile.showPageLoadingMsg()
	var bdid = $("input[name=mViewBDID]").val()
	var num = $("input[name=mViewNUM]").val()
	$.ajax({
		url:"../index.php",
		type: "POST",
		data: "mlogin="+LOGIN+"&password="+PASSWORD+"&delete="+bdid+"&num="+num,
		success: function(data)
		{
			if (data)
			{
				ACCOUNTS[data]['name'] = ""
				FillMobileFirst(-(parseInt(data)))
				$.mobile.hidePageLoadingMsg()
				mToAccsPage()
			}
		}
	})
	
	
}

function mReIndex()
{
	for (var i in ACCOUNTS)
	{	
		if (i == 'sorter')
			continue
			
		name = ACCOUNTS[i]['name']
		if (!name || name == 'undefined')
			continue
		name = name.substr(0, 1)
		name = name.toUpperCase()
		
		if (i == 0)
		{
			ACCOUNTS['sorter'][name] = [0]
			continue
		}
		var flag = true
		for (var k in ACCOUNTS['sorter'])
		{
			if (k == name)
			{
				flag = false
				break
			}
		}
		if (flag)
		{
			ACCOUNTS['sorter'][name] = [i]
		}
		else
		{
			var arr = ACCOUNTS['sorter'][name]
			arr.push(i)
			ACCOUNTS['sorter'][name] = arr
		}
	}
	
	var html = "<div data-role='navbar'><ul><li><a class='ui-btn-active' onClick='mFill()' href='#AccountsPage'>Все</a></li>"
	
	for (var i in ACCOUNTS['sorter'])
	{
		html += "<li><a sorter='"+i+"' onClick='mFill(\""+i+"\")' href='#AccountsPage'>"+i+"</a></li>"
	}
	html += "</ul></div>"
	
	$("#AccountsPage div[data-role=navbar]").remove()
	$(html).appendTo("#AccountsPage div[data-role=header]")
	
	$("#AccountsPage div[data-role=header]").trigger("create")
	mFill()
}

function mFill(sorter)
{
	var nums = ACCOUNTS['sorter'][sorter]
	
	if (sorter)
		$("#AccPageContent button").each(function(index, element)
		{
			for (var i in nums)
			{
				if ($(element).attr("num") == nums[i])
				{
					$(element).parent().show()
					break
				}
				else if ($(element).attr("num"))
				{
					$(element).parent().hide()
				}
			}
		})
	else
	{
		$("#AccPageContent button").each(function(index, element)
		{
			$(element).parent().show()
		})
	}
}

</script>
</head>

<body>
<div data-role="page" id="LoginPage">
    <div data-role="header">
        <h1>Менеджер паролей для мобильных устройств</h1>
    </div><!-- /header -->
    
    <div data-role="content">
        	<p>Логин:<br><input type="text" name="login"></p>
        	<p>Пароль:<br><input type="password" name="password"></p>
            <p><button data-icon="check" id="actLogin">Войти</button></p>
            <p id="notifier" style="color:red;"></p>
    </div><!-- /content -->
    
    <div data-role="footer">
        <h4><a href="../?mobile=no" rel="external">Версия для ПК</a> - <a href="http://jquerymobile.com/demos">Создано с помощью JQuery Mobile 1.0.1</a></h4>
    </div><!-- /header -->
    
</div><!-- /page -->


<div data-role="page" id="AccountsPage">
    <div data-role="header">
        <h1>Мои учетные записи</h1>
        	
    </div><!-- /header -->
    
    <div data-role="content" id="AccPageContent">
    </div><!-- /content -->
    
    <div data-role="footer">
        <h4><a href="#LoginPage">Войти другим пользователем</a> - <a href="../?mobile=no" rel="external">Версия для ПК</a> - <a href="http://jquerymobile.com/demos">Создано с помощью JQuery Mobile</a></h4>
    </div><!-- /header -->
    
</div><!-- /page -->

<div data-role="page" id="AccountPage">
    <div data-role="header">
        <h1>Учетная запись</h1>
    </div><!-- /header -->
    
    <div data-role="content" id="AccountContent">
    	<p>
        	<button data-icon='arrow-l' onClick='mToAccsPage()'>Назад</button>
            <button data-icon='grid' onClick='mToChangeAcc()'>Изменить...</button>
            <button data-icon='delete' onClick='mToDelAcc()'>Удалить</button>
        </p>
		<p>
        			<input type="hidden" name="mViewBDID">
                    <input type="hidden" name="mViewNUM">
        	Логин:<br><input type='text' name="mViewLogin" value=''><br>
			Пароль:<br><input type='text' name="mViewPass" value=''><br>
			<a href="#" rel="external" id="mViewLink" target="_blank">Ссылка</a>
        </p>
    </div><!-- /content -->
    
    <div data-role="footer">
        <h4><a href="#LoginPage">Войти другим пользователем</a> - <a href="../?mobile=no" rel="external">Версия для ПК</a> - <a href="http://jquerymobile.com/demos">Создано с помощью JQuery Mobile</a></h4>
    </div><!-- /header -->
    
</div><!-- /page -->

<div data-role="page" id="ChangeAccountPage">
    <div data-role="header">
        <h1>Изменение учетной записи</h1>
    </div><!-- /header -->
    
    <div data-role="content" id="ChangeAccountContent">
    	<p>
        	<button data-icon='arrow-l' onClick='mToAccPage()'>Назад</button>
        </p>
		<p>
            Имя:<br><input type="text" name="mChangeName"><br>
            Ссылка:<br><input type="url" name="mChangeLink"><br>
        	Логин:<br><input type='text' name="mChangeLogin" value=''><br>
			Пароль:<br><input type='text' name="mChangePass" value=''>
            <button data-icon="gear" onClick='mGeneratePassword(0)'>Сгенерировать новый пароль</button><br>
			<button data-icon="check" onClick='mChangeSave()'>Сохранить</button>
        </p>
    </div><!-- /content -->
    
    <div data-role="footer">
        <h4><a href="#LoginPage">Войти другим пользователем</a> - <a href="../?mobile=no" rel="external">Версия для ПК</a> - <a href="http://jquerymobile.com/demos">Создано с помощью JQuery Mobile</a></h4>
    </div><!-- /header -->
    
</div><!-- /page -->

<div data-role="page" id="DeleteAccountPage">
    <div data-role="header">
        <h1>Удаление учетной записи</h1>
    </div><!-- /header -->
    
    <div data-role="content" id="DeleteAccountContent">
    	<p><button data-icon='arrow-l' onClick='mToAccPage()'>Назад</button></p>
    	<p>Вы уверены?</p>
        <p><button data-icon="check" onClick="mDelAcc()">Да</button></p>
    </div><!-- /content -->
    
    <div data-role="footer">
        <h4><a href="#LoginPage">Войти другим пользователем</a> - <a href="../?mobile=no" rel="external">Версия для ПК</a> - <a href="http://jquerymobile.com/demos">Создано с помощью JQuery Mobile</a></h4>
    </div><!-- /header -->
    
</div><!-- /page -->

<div data-role="page" id="AddNewAccountPage">
    <div data-role="header">
        <h1>Добавить новую учетную запись</h1>
    </div><!-- /header -->
    
    <div data-role="content" id="AddNewAccountContent">
    	<p>
        	<button data-icon='arrow-l' onClick='mToAccsPage()'>Назад</button><br>
        	Имя:<br><input type='text' name='add_name'><br>
			Ссылка:<br><input type='url' name='add_link'><br>
			Логин:<br><input type='text' name='add_login'><br>
			Пароль:<br><input type='text' name='add_pass'>
			<button data-icon="gear" onClick='mGeneratePassword(-1)'>Сгенерировать пароль</button><br>
			<button data-icon="check" onClick='mAdd()'>Сохранить</button>
    	</p>
    </div><!-- /content -->
    
    <div data-role="footer">
        <h4><a href="#LoginPage">Войти другим пользователем</a> - <a href="../?mobile=no" rel="external">Версия для ПК</a> - <a href="http://jquerymobile.com/demos">Создано с помощью JQuery Mobile</a></h4>
    </div><!-- /header -->
    
</div><!-- /page -->
<?php //include "../stat.php"; ?>
</body>
</html>