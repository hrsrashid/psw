// JavaScript Document
var PASSWORD;
var LOGIN;
var ACCOUNTS;

function ChangeStylesheet(isChange)
{
	if (!isChange) isChange = "change"
	
	if (localStorage.style == "new" && isChange == "change")
	{
		localStorage.style = "old"
		$("link").remove()
		$("head").append($(document.createElement("LINK")).attr({"type":"text/css", "rel":"stylesheet", "href":"style.css"}))
		$("#ChangeStylesheet").html("Поменять стиль (только для самых современных браузеров, исключая IE9)")
	}
	else
	{
		localStorage.style = "new"
		$("link").remove()
		$("head").append($(document.createElement("LINK")).attr({"type":"text/css", "rel":"stylesheet", "href":"style2.css"}))
		$("#ChangeStylesheet").html("Вернуть старый стиль, поддерживаемый большинством браузеров")
	}
}

function CloseAll()
{
	location.href = "about:blank"
}

function goFunction()
	{
		$('#loading').fadeIn()
		$("#notifier").hide()
		var pass = $("#loginFormPassword").val()
		var login = $("#loginFormLogin").val()
		PASSWORD = pass;
		LOGIN = login;
		
		$.ajax({
			url: location.href,
			type: 'POST',
			data: 'password='+pass+'&mlogin='+login,
              error: function(xhr, textStatus, error)
              {
                  var html = "Ошибка, попробуйте позже: " + error
                  $("#notifier_content").html(html)
                  $("#loading").fadeOut()
                  $("#notifier").fadeIn()
               },
			success: function(data)
			{
				var resp = $.parseJSON(data)
				for (var i in resp)
				{
					if (i == "error")
					{
						var html = resp[i]
						if (html != "Вы не ввели пароль или логин")
							html += "<a href='#' onClick='AddUser()'>Создать учетную запись с введенными данными?</a>"
						
						$("#notifier_content").html(html)
						$('#loading').fadeOut()
						$("#notifier").fadeIn()
						
						return;
					}
				}
				
				// TODO: ...
				ACCOUNTS = resp
				$("#loginForm").fadeOut()
				$("#container").remove()
				$("#container2").fadeIn()
				FillFirst()
			}
		})
	}

$(document).ready(function()
{
	if (localStorage.style == "new" && !/mobile/.test(location.href))
	{
		ChangeStylesheet("keep")
		$("body").css({"min-height":((screen.availHeight)?screen.availHeight:(screen.height)?screen.height:"1000")+"px"})
	}
	
	$(document).keypress(function(evt)
	{
		if (evt.keyCode == 13)
		{
			if (evt.srcElement == document.getElementById("loginFormPassword"))
				goFunction()
		}
	})
})

function FillFirst()
{
	$("#exit").fadeIn()
	$("#changePass").fadeIn()
	var html = "<div id='add'>+ Добавить</div>"
	html += "<div id='add_content'>"
	html += "<table><tr><td>Имя:</td><td><input type='text' name='name'></td></tr>"
	html += "<tr><td>Ссылка:</td><td><input type='text' name='link'></td></tr>"
	html += "<tr><td>Логин:</td><td><input type='text' name='login'></td></tr>"
	html += "<tr><td>Пароль:</td><td><input type='text' name='pass'></td>"
	html += "<td><button onClick='GeneratePassword(-1)'>Сгенерировать</button></td></tr>"
	html += "<tr><td><button onClick='Add()'>Добавить</button></td></tr></table>"
	html += "</div>"
	
	ACCOUNTS['sorter'] = {}
		
	for (var i in ACCOUNTS)
	{
		if (i != 'sorter' && ACCOUNTS[i]['name'])
			html += "<div id='account' num='"+i+"' bdid='"+ACCOUNTS[i]['id']+"'>"+ACCOUNTS[i]['name']+" <div id='delete'></div> <div id='edit'></div> </div> <div id='account_content' num='"+i+"'></div>"
		
	}
		
	$("#content_content").html(html)
	
	ReIndex()
	
	$("div[id=account]").click(function()
	{	
		var num = this.getAttribute("num")
		var html;

		if ($("div[id=account_content][num="+num+"]").css('display') == 'none')
		{
			if (ACCOUNTS[num]['link'] == 'none')
				html = "<table><tr><td>Логин:</td><td><textarea onKeyPress='this.blur()'>"+ACCOUNTS[num]['login']+"</textarea></td></tr><tr><td>Пароль:</td><td><textarea onKeyPress='this.blur()'>"+ACCOUNTS[num]['pass']+"</textarea></td></tr></table>"
			else
				html = "<table><td><table><tr><td>Логин:</td><td><textarea onKeyPress='this.blur()'>"+ACCOUNTS[num]['login']+"</textarea></td></tr><tr><td>Пароль:</td><td><textarea onKeyPress='this.blur()'>"+ACCOUNTS[num]['pass']+"</textarea></td</tr></table></td><td><a href='"+ACCOUNTS[num]['link']+"' target='_blank'>Перейти</a></td></table>"
			
			$("div[id=account_content][num="+num+"]").html(html).slideDown()
		}
		else
		{
			$("div[id=account_content][num="+num+"]").slideUp()
		}	
	})
	
	$("div[id=delete]").click(function(evt)
	{
		evt.originalEvent.cancelBubble = true
		var num = this.parentNode.getAttribute("num")
		
		if ($("div[id=account_content][num="+num+"]").css('display') == 'none')
		{
			var html = "<p>Вы уверены?</p><p><button onClick='Delete("+num+")'>Да</button></p>"
			
			$("div[id=account_content][num="+num+"]").html(html).slideDown()
		}
		else
		{
			$("div[id=account_content][num="+num+"]").slideUp()
		}
	})
	
	$("div[id=edit]").click(function(evt)
	{
		var num = this.parentNode.getAttribute("num")
		evt.originalEvent.cancelBubble = true
		if ($("div[id=account_content][num="+num+"]").css('display') == 'none')
		{
			var html = "<table><tr><td>Имя:</td><td><input name='name' type='text' value='"+ACCOUNTS[num]['name']+"'></td></tr>"
			html += "<tr><td>Ссылка:</td><td><input name='link' type='text' value='"+((ACCOUNTS[num]['link'] == 'none')?"ссылка":ACCOUNTS[num]['link'])+"'></td></tr>"
			html += "<tr><td>Логин:</td><td><input name='login' type='text' value='"+ACCOUNTS[num]['login']+"'></td></tr>"
			html += "<tr><td>Пароль:</td><td><input name='pass' type='text' pass='true' value='"+ACCOUNTS[num]['pass']+"'></td>"
			html += "<td><button onClick='GeneratePassword("+num+")'>Сгенерировать</button></td></tr>"
			html += "<tr><td><button onClick='SaveEdit("+num+")'>Сохранить</button></td></tr></table>"
			
			$("div[id=account_content][num="+num+"]").html(html).slideDown()
		}
		else
		{
			$("div[id=account_content][num="+num+"]").slideUp()
		}	
	})
	
	$("#add").click(function()
	{
		if ($("#add_content").css('display') == 'none')
		{
			$("#add_content input").val("")
			$('#add_content').slideDown()
		}
		else
			$('#add_content').slideUp()
	})
}

function Delete(num)
{
	var bdid = $("div[id=account][num="+num+"]").attr("bdid")
	$.ajax({
		url:location.href,
		type: "POST",
		data: "mlogin="+LOGIN+"&password="+PASSWORD+"&delete="+bdid+"&num="+num,
		success: function(data)
		{
			if (data)
			{
				$("div[id=account][num="+data+"]").remove()
				$("div[id=account_content][num="+data+"]").remove()
				ACCOUNTS[data]['name'] = ""
				
				Fill('all')
			}
		}
	})
}

function GeneratePassword(num)
{
	var dict = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','1','2','3','4','5','6','7','8','9','0']
	// 0 - 61
	var res = "";
	for (var i = 1; i <= 16; i++)
	{
		res += dict[Math.round(Math.random()*61)]
	}
	
	if (num == -1)
		$("#add_content input[name=pass]").val(res)
	else
		$("div[num="+num+"] input[pass=true]").val(res)
}

function SaveEdit(num)
{
	var name = $("div[num="+num+"] input[name=name]").val()
	var link = $("div[num="+num+"] input[name=link]").val()
	var pass = $("div[num="+num+"] input[name=pass]").val()
	var login = $("div[num="+num+"] input[name=login]").val()
	var bdid = $("div[id=account][num="+num+"]").attr('bdid')
	
	if (link == "ссылка" || link == "")
		link = "none"
	if (!name || !pass || !login)
		return;
	
	$.ajax({
		url:location.href,
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
				$("div[num="+data[0]+"]").html("<div id='account' num='"+data[0]+"' bdid='"+data[1]+"'>"+ACCOUNTS[data[0]]['name']+" <div id='delete'></div> <div id='edit'></div> </div> <div id='account_content' num='"+data[0]+"'></div>")
				
				Fill('all')
			}
		}
	})
}

function Add()
{
	var name = $("#add_content input[name=name]").val()
	var link = $("#add_content input[name=link]").val()
	var pass = $("#add_content input[name=pass]").val()
	var login = $("#add_content input[name=login]").val()
	
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
		url:location.href,
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
			$("#add_content").slideUp()
			Fill('all')
		}
	})
}

function Fill(sorter)
{
	if (sorter == 'all')
	{
		FillFirst()
		return;
	}
	
	if (!$("#container2_header").attr("prev"))
	{
		$("#container2_header").attr("prev", sorter)
		$("div[id=navigation][sorter="+sorter+"]").css({'background':'url(images/nav_used.gif)'})
	}
	else
	{
		var prev = $("#container2_header").attr("prev")
		$("div[id=navigation][sorter="+prev+"]").css({'background':''})
		
		if (prev == sorter)
		{
			FillFirst()
			 $("#container2_header").attr("prev","")
			return;
		}
		
		$("#container2_header").attr("prev", sorter)
		$("div[id=navigation][sorter="+sorter+"]").css({'background':'url(images/nav_used.gif)'})
	}
		
	var nums = ACCOUNTS['sorter'][sorter]
	
	$("div[id=account]").each(function(index, element)
	{
		for (var i in nums)
		{
			if ($(element).attr("num") == nums[i])
			{
				$(element).slideDown()
				break
			}
			else
			{
				$(element).slideUp()
				$("div[id=account_content][num="+$(element).attr('num')+"]").slideUp()
			}
		}
	})
}

function ReIndex()
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
	
	var html = ""
	var count = 0;
	
	for (var i in ACCOUNTS['sorter'])
	{
		html += "<div id='navigation' sorter='"+i+"' onClick='Fill(\""+i+"\")'>"+i+"</div>"
		count++
	}
	
	$("#container2_header").html(html)
	var margin = (766 - (35 * count)) / (count + 1)
	$("div[id=navigation]").css({'margin-left':margin+"px"})
}

function AddUser()
{
	LOGIN = $("#loginFormLogin").val()
	PASSWORD = $("#loginFormPassword").val()
	$("#notifier").fadeOut()
	
	$.ajax({
		url:location.href,
		type:"POST",
		data:"mlogin="+LOGIN+"&password="+PASSWORD+"&adduser=1",
		success:function(data)
		{
			
				var resp = $.parseJSON(data)
				for (var i in resp)
				{
					if (i == "error")
					{
						var html = resp[i]
						html += "<a href='#' onClick='AddUser()'>Создать учетную запись с введенными данными?</a>"
						
						$("#notifier_content").html(html)
						$("#notifier").fadeIn()
						
						return;
					}
				}
				
				// TODO: ...
				ACCOUNTS = resp
				$("#loginForm").fadeOut()
				$("#container").remove()
				$("#container2").fadeIn()
				FillFirst()
		}
		})
}

function ChangePassword()
{
	$(document.createElement("DIV")).css({
		"background":"#000",
		"opacity":"0.5",
		"filter":"alpha(opacity=50)",
		"position":"fixed",
		"top":"0px",
		"left":"0px",
		"z-index":"3",
		"width":"100%",
		"height":"100%"
		}).attr("id","layer").appendTo($(document.body))
		
	var html = "<table cellpadding='10'>"
	html += "<td>Новый пароль:</td>"
	html += "<td><input type='password' id='newmpass'></td>"
	html += "</table>"
	
	$("#loginForm_content").html(html)
	$("#loginForm header").html("Смена мастер-пароля")
	$("#loading").hide()
	
	$("#loginFormCloseButton").attr("onClick","CancelChangePassword()")
	$("#loginFormGoButton").attr("onClick","ChangePasswordStep2()").html("Изменить").css({"padding":"8px 10px 0 10px"})
	
	$("#loginForm").css(
	{
		"position":"absolute",
		"top":"20%",
		"left":"30%",
		"z-index":"4"
	}).fadeIn()
	
	
}

function ChangePasswordStep2()
{
	var newPassword = $("#newmpass").val();
	
	if (!newPassword)
	{
		return;
	}
	$('#loading').fadeIn()
	
	$.ajax({
		url:location.href,
		type:"POST",
		data:"mlogin="+LOGIN+"&password="+PASSWORD+"&newpassword="+newPassword,
		success:function(data)
		{
			$('#loading').fadeOut()
			CancelChangePassword()
		}
		})
}

function CancelChangePassword()
{
	$("#layer").remove()
	$("#loginForm").fadeOut()
}