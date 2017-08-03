<?php
    error_reporting(E_ALL);

// if ($_SERVER['SERVER_PORT'] == 80)
// 	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);


	
if ($_SERVER['REQUEST_METHOD'] == "POST")
{
	if (isset($_POST['password']) && isset($_POST['mlogin']))
	{
		$password = $_POST['password'];
		$login = $_POST['mlogin'];
		if (!empty($password) && !empty($login))
		{
			$db = new PDO("sqlite:.htpsw1496480790");
			
			if (isset($_POST['adduser']))
			{
				$stmt = $db->prepare("INSERT INTO users (login,password) VALUES (?,?)");
				$stmt->bindValue(1, $login, PDO::PARAM_STR);
				$stmt->bindValue(2, sha1($password), PDO::PARAM_STR);
				$stmt->execute();
			}
			
			$stmt = $db->prepare("SELECT * FROM users WHERE login = ? AND password = ?");
			$stmt->bindValue(1, $login, PDO::PARAM_STR);
			$stmt->bindValue(2, sha1($password), PDO::PARAM_STR);
			$stmt->execute();
			
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			$uid = $row['id'];
			if (!$uid)
				exit(json_encode(array("error"=>"Вы ввели неправильный пароль или логин")));
			
				
				// TODO: ...
				if (isset($_POST['newpassword']))
				{
					$newpass = $_POST['newpassword'];
					if (!empty($newpass))
					{
						
						$stmt = $db->query("SELECT * FROM enc_accounts WHERE uid = $uid");
				
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
						{
							$i = $row['id'];
							$l = decrypt($row['link'], $login.$password);
							$lo = decrypt($row['login'], $login.$password);
							$p = decrypt($row['pass'], $login.$password);
							
							$s = $db->prepare("UPDATE enc_accounts SET link = ?, login = ?, pass = ? WHERE uid = ? AND id = ?");
							$s->bindValue(1, encrypt($l, $login.$newpass), PDO::PARAM_STR);
							$s->bindValue(2, encrypt($lo, $login.$newpass), PDO::PARAM_STR);
							$s->bindValue(3, encrypt($p, $login.$newpass), PDO::PARAM_STR);
							$s->bindValue(4, $uid, PDO::PARAM_INT);
							$s->bindValue(5, $i, PDO::PARAM_INT);
							$s->execute();
						}				
				
						$stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
						$stmt->bindValue(1, sha1($newpass), PDO::PARAM_STR);
						$stmt->bindValue(2, $uid, PDO::PARAM_INT);
						$stmt->execute();
					}
					else
						exit();
				}
				
				if (isset($_POST['delete']))
				{
					if (!is_numeric($_POST['delete']))
						exit("error");
						
					$num = $_POST['delete'];
					
					$stmt = $db->prepare("DELETE FROM enc_accounts WHERE id = ? AND uid = ?");
					$stmt->bindValue(1, $num, PDO::PARAM_INT);
					$stmt->bindValue(2, $uid, PDO::PARAM_INT);
					$stmt->execute();
					exit($_POST['num']);
				}
				
				if (isset($_POST['edit']))
				{
					if (!is_numeric($_POST['edit']))
						exit("error");
						
					$num = $_POST['edit'];
					
					$stmt = $db->prepare("UPDATE enc_accounts SET name=?,link=?,login=?,pass=? WHERE id = ? AND uid = ?");
					$stmt->bindValue(1, $_POST['name'], PDO::PARAM_STR);
					$stmt->bindValue(2, encrypt($_POST['link'], $login.$password), PDO::PARAM_STR);
					$stmt->bindValue(3, encrypt($_POST['login'], $login.$password), PDO::PARAM_STR);
					$stmt->bindValue(4, encrypt($_POST['pass'], $login.$password), PDO::PARAM_STR);
					$stmt->bindValue(5, $num, PDO::PARAM_INT);
					$stmt->bindValue(6, $uid, PDO::PARAM_INT);
					$stmt->execute();
					
					exit(json_encode(array($_POST['num'], $num)));
				}
				
				if (isset($_POST['add']))
				{
					$stmt = $db->prepare("INSERT INTO enc_accounts (name, link, login, pass, uid) VALUES (?, ?, ?, ?, ?)");
					$stmt->bindValue(1, $_POST['name'], PDO::PARAM_STR);
					$stmt->bindValue(2, encrypt($_POST['link'], $login.$password), PDO::PARAM_STR);
					$stmt->bindValue(3, encrypt($_POST['login'], $login.$password), PDO::PARAM_STR);
					$stmt->bindValue(4, encrypt($_POST['pass'], $login.$password), PDO::PARAM_STR);
					$stmt->bindValue(5, $uid, PDO::PARAM_INT);
					$stmt->execute();
					
					$sql = "SELECT id FROM enc_accounts WHERE login = ? AND pass = ? AND name = ?";
					$stmt = $db->prepare($sql);
					$stmt->bindValue(1, encrypt($_POST['login'], $login.$password), PDO::PARAM_STR);
					$stmt->bindValue(2, encrypt($_POST['pass'], $login.$password), PDO::PARAM_STR);
					$stmt->bindValue(3, $_POST['name'], PDO::PARAM_STR);
					$stmt->execute();
					
					$row = $stmt->fetch(PDO::FETCH_ASSOC);
					
					exit($row['id']);
				}
				
			$stmt = $db->query("SELECT id from enc_accounts WHERE uid = $uid");
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			
			if (!$row['id'])
			{
				$stmt = $db->query("SELECT * FROM accounts WHERE uid = $uid");
					
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{
					$l = encrypt($row['link'], $login.$password);
					$lo = encrypt($row['login'], $login.$password);
					$p = encrypt($row['pass'], $login.$password);
					$n = $row['name'];
					
					$s=$db->prepare("INSERT INTO enc_accounts (name, link, login, pass, uid) VALUES (?, ?, ?, ?, ?)");
					$s->bindValue(1, $n, PDO::PARAM_STR);
					$s->bindValue(2, $l, PDO::PARAM_STR);
					$s->bindValue(3, $lo, PDO::PARAM_STR);
					$s->bindValue(4, $p, PDO::PARAM_STR);
					$s->bindValue(5, $uid, PDO::PARAM_INT);
					$s->execute();
					
				}
				
				$stmt = $db->prepare("DELETE FROM accounts WHERE uid = ?");
				$stmt->bindValue(1, $uid, PDO::PARAM_INT);
				$stmt->execute();
				///////////////////CREATING NEW DB
				$db2name = ".htpsw".time();
				$db2 = new PDO("sqlite:".$db2name);
				
				$db2->exec("CREATE TABLE enc_accounts(id INTEGER PRIMARY KEY AUTOINCREMENT, name Text, link Text, login TEXT, pass TEXT, uid INTEGER)");
				$db2->exec("CREATE TABLE users(id INTEGER PRIMARY KEY AUTOINCREMENT, login VARCHAR(250), password VARCHAR(250), FOREIGN KEY(id) REFERENCES accounts(uid) ON DELETE CASCADE)");
				$db2->exec("CREATE TABLE accounts(id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR(250), link VARCHAR(250), login VARCHAR(50), pass VARCHAR(50), uid INTEGER)");
				////////////////////INSERTING USERS
				$stmt = $db->query("SELECT * FROM users");	
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{	
					$s=$db2->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
					$s->bindValue(1, $row['login'], PDO::PARAM_STR);
					$s->bindValue(2, $row['password'], PDO::PARAM_STR);
					$s->execute();
					
				}
				////////////////////INSERTING ACCOUNTS
				$stmt = $db->query("SELECT * FROM accounts");	
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{					
					$s=$db2->prepare("INSERT INTO accounts (name, link, login, pass, uid) VALUES (?, ?, ?, ?, ?)");
					$s->bindValue(1, $row['name'], PDO::PARAM_STR);
					$s->bindValue(2, $row['link'], PDO::PARAM_STR);
					$s->bindValue(3, $row['login'], PDO::PARAM_STR);
					$s->bindValue(4, $row['pass'], PDO::PARAM_STR);
					$s->bindValue(5, $row['uid'], PDO::PARAM_INT);
					$s->execute();
					
				}
				////////////////////INSERTING ENC_ACCOUNTS
				$stmt = $db->query("SELECT * FROM enc_accounts");	
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
				{					
					$s=$db2->prepare("INSERT INTO enc_accounts (name, link, login, pass, uid) VALUES (?, ?, ?, ?, ?)");
					$s->bindValue(1, $row['name'], PDO::PARAM_STR);
					$s->bindValue(2, $row['link'], PDO::PARAM_STR);
					$s->bindValue(3, $row['login'], PDO::PARAM_STR);
					$s->bindValue(4, $row['pass'], PDO::PARAM_STR);
					$s->bindValue(5, $row['uid'], PDO::PARAM_INT);
					$s->execute();
				}
				////////////////////CHANGING DEFAULT DB
				$indexfile = file_get_contents("index.php");
				$start = strpos($indexfile, 'PDO("sqlite:'); // PDO("sqlite:.htpsw");
				$end = strpos($indexfile, '");', $start);
				
				$newindexfile = substr($indexfile, 0, $start);
				$newindexfile .= 'PDO("sqlite:'.$db2name;
				$newindexfile .= substr($indexfile, $end);
				
				file_put_contents("index.php", $newindexfile);
				
				$olddbname = substr($indexfile, $start+strlen('PDO("sqlite:'), $end - $start+strlen('PDO("sqlite:'));
				
				if (file_exists($olddbname)) {
					unlink($olddbname);
				}
			}
			
			$stmt = $db->query("SELECT * FROM enc_accounts WHERE uid = $uid ORDER BY name");
			
			if (!$stmt)
				exit(json_encode(array(array())));
				
			$res = array(array());
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$res[] = array("id"=>$row['id'],"name"=>$row['name'], "link"=>decrypt($row['link'], $login.$password), "login"=>decrypt($row['login'], $login.$password), "pass"=>decrypt($row['pass'], $login.$password));
			}
			
			uniclear($res);
			
			exit(json_encode($res));
		}
		else
		{
			exit(json_encode(array("error"=>"Вы не ввели пароль или логин")));
		}
	}
	
	exit();
}
$uagent = $_SERVER['HTTP_USER_AGENT'];
$uas = array
(
	"iPad",
	"Mobile"
);

foreach ($uas as $v)
{
	if (strpos($uagent, $v) !== false)
	{
		if (isset($_GET['mobile']) && !empty($_GET['mobile']) && $_GET['mobile'] == "no")
		{
			setcookie("mobile", "no", mktime(0,0,0,0,0,4444), "/", $_SERVER['HTTP_HOST'], true, true);
		}
		else if (!(isset($_COOKIE['mobile']) && !empty($_COOKIE['mobile']) && $_COOKIE['mobile'] == "no"))
		{
			header("Location: mobile");
		}
	}
}

function encrypt($text, $key)
{
	return mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB);
}

function decrypt($data, $key)
{
	return mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB);
}

function uniclear(&$arr)
{
	foreach ($arr as $i=>$v)
	{
		foreach ($v as $k=>$value)
		{
			if ($k == "login" || $k == "link" || $k == "pass")
			{
				$len = strpos(urlencode($value), "%0");
				
				if ($len !== false)
					$arr[$i][$k] = urldecode(substr(urlencode($value), 0, $len));
			}
		}
	}
}

?>
<!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="author" content="Хурамшин Рашид">
<meta name="description" content="менеджер паролей онлайн">
<meta name="keywords" content="менеджер паролей">
<title>Менеджер паролей</title>
<script type="text/javascript" src="/jq.js"></script>
<script type="text/javascript" src="main.js"></script>
<link type="text/css" rel="stylesheet" href="style.css">
</head>

<body>

    
  <div id="loginForm">
        
            <header>Необходима авторизация</header>
            
            <div id="loginForm_left"></div>
            
            <div id="loginForm_content">
            <table cellpadding="10" style="margin-left: 30px;">
            	<tr><td>Логин:</td><td><input type="text" id="loginFormLogin"></td></tr>
                <tr><td>Пароль:</td><td><input type="password" id="loginFormPassword"></td></tr>
             </table>
            </div>
            
            <div id="loginForm_right"></div>
            
            <footer>
            	<div id="loading"><img src="images/loading.gif"></div>
                <div id="loginFormCloseButton" onClick="CloseAll()">Закрыть</div>
                <div id="loginFormGoButton" onClick="goFunction()">Войти</div>
            </footer>
        </div>
        
        <div id="notifier">
        
            <div id="notifier_left"></div>
            
            <div id="notifier_content"></div>
            
            <div id="notifier_right"></div>
        </div>

<div id="container2">
	<div id="container2_top"></div>
    <div id="container2_header"></div>
    
	<div id="content">
    	<div id="content_top"></div>
		<div id="content_content">
        </div>
        <div id="content_bottom"></div>
    </div>

    <div id="container2_bottom"></div>
</div>

<button id="exit" onClick='location.href = "about:blank"'>Выйти</button>
<button id="changePass" onClick='ChangePassword()'>Поменять пароль для входа</button>
<button id="ChangeStylesheet" onClick='ChangeStylesheet()' style="position:fixed; left:10px; bottom:100px; width: 15%;">Поменять стиль (только для самых современных браузеров, исключая IE9)</button>
<?php //include "stat.php"; ?>
</body>
</html>