<?php
define("DB_HOST", "localhost");
define("DB_NAME", "psw_stat");
define("DB_LOGIN", "...");
define("DB_PASS", "...");
	
$s_db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_LOGIN, DB_PASS);

if (!isset($_GET['result']))
{
	$ip = $_SERVER['REMOTE_ADDR'];
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	
	$patterns = array
	(
		"Google Chrome"=>"/[chrome\s\/0-9]{6,10}/i",
		"Some Mobile"=>"/Mobile/",
		"Microsoft Internet Explorer"=>"/[MSIE\/0-9\s]{4,8}/",
		"Firefox"=>"/[fireox\/0-9]{6,10}/i",
		"Opera"=>"/[opera\smin\/0-9]{5,12}/i"
	);
	
	foreach ($patterns as $browser=>$pattern)
	{
		if (preg_match($pattern, $u_agent, $matches))
		{
			$ver = substr($matches[0], strpos($matches[0], "/")+1);
			$ver = substr($ver, strpos($ver, " "));
			$u_agent = $browser." ".$ver;		
			break;
		}
	}
	
	$stmt = $s_db->prepare("INSERT INTO stat (ip, uagent, date) VALUES (?, ?, ?)");
	$stmt->bindValue(1, $ip, PDO::PARAM_STR);
	$stmt->bindValue(2, $u_agent, PDO::PARAM_STR);
	$stmt->bindValue(3, time(), PDO::PARAM_INT);
	$stmt->execute();
}
else if (empty($_GET['result']))
{	
	$img = imagecreatetruecolor(1000, 300);
	imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
	$black = imagecolorallocate($img, 0, 0, 0);
	$blue = imagecolorallocate($img, 0, 0, 255);
	
	imageline($img, 10, 10, 10, 280, $black);
	imageline($img, 10, 280, 990, 280, $black);
	
	$dates;
	$x = 990 - 68;
	for ($i = 0; $i < 10; $i++)
	{
		$date = mktime(0,0,0,
		(date("j") > $i)?date("n"):((date("n") > 1)?(date("n")-1):12),
		(date("j") > $i)?date("j")-$i:30-$i,
		(date("j") > $i && date("n") > 1)?date("Y"):date("Y")-1);
		
		imagestring($img, 3, $x, 280, date("d-m-Y", $date), $black);//w: 68
		$dates[9 - $i] = array($date, 0);
		$x -= 98;
	}
	
	$from = mktime(0,0,0,(date("j") > 9)?date("n"):((date("n") > 1)?date("n")-1:12),(date("j") > 9)?date("j")-9:30-9, (date("n") > 1 && date("j") > 9)?date("Y"):date("Y")-1);
	
	$stmt = $s_db->query("SELECT * FROM stat WHERE date >= ".$from." ORDER BY date");
	
	$max = 0;
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$dt = (int)$row['date'];
		
		for ($k=0; $k <= 9; $k++)
		{
			if (($k + 1) <= 9)
			{
				if ($dt >= $dates[$k][0] && $dt < $dates[$k+1][0])
				{
					$dates[$k][1]++;
					$max = ($dates[$k][1] > $max)?$dates[$k][1]:$max;
				}
				
			}
			else
			{
				
				if ($dt >= $dates[$k][0])
				{
					$dates[$k][1]++;
					$max = ($dates[$k][1] > $max)?$dates[$k][1]:$max;
				}
				
			}
		}
	}

	$x = 990 - 68;
	$prev_y;
	for ($i = 9; $i >= 0; $i--)
	{
		$amount = $dates[$i][1];
		if ($amount != 0)
		{
			$percent = $amount / $max;
			$y = 300 - (290 * $percent);
		}
		else
			$y = 280;
		
		if ($i == 9)
		{
			imageline($img, $x+34, $y, 990, $y, $blue);
			imagestring($img, 3, $x+34, $y-12, $amount, $blue);
			$prev_y = $y;
		}
		else
		{
			imageline($img, $x+34, $y, $x+131, $prev_y, $blue);
			imagestring($img, 3, $x+34, $y-12, $amount, $blue);
			$prev_y = $y;
		}
		
		$x -= 98;
	}
	
	imagejpeg($img, "img.jpg");
	imagedestroy($img);
	
	
echo <<<HERE
<style>
.stat
{
	margin: 100px;
	padding: 20px;
	
	-webkit-box-shadow: 0 0 15px #000;
	background: -webkit-linear-gradient(top, #ccc, #eee, #ccc);
	
	-moz-box-shadow: 0 0 15px #000;
	background: -moz-linear-gradient(top, #ccc, #eee, #ccc);
	
	box-shadow: 0 0 15px #000;
	background: -ms-linear-gradient(top, #ccc, #eee, #ccc);
	background: -o-linear-gradient(top, #ccc, #eee, #ccc);
}
.stat img
{
	-webkit-box-shadow: 0 0 5px #000;
	-moz-box-shadow: 0 0 5px #000;
	box-shadow: 0 0 5px #000;
}
.stat table
{
	width: 100%;
	margin-top: 30px;
}
</style>
HERE;

	if (strpos($_SERVER['REQUEST_URI'], "stat.php") !== false)
	{
		echo "<script type='text/javascript' src='/jq.js'></script>";
	}
	
	echo "<div class='stat'><img src='img.jpg'><br><input type='hidden' value='".$from."' class='sFrom'>
	<button class='sNav' onClick='back()'>В прошлое</button>
	<button disabled class='sNav' onClick='forward()'>К настоящему</button>";
	
	
	$stmt = $s_db->query("SELECT * FROM stat WHERE date >= ".$from." ORDER BY id DESC");
	
	$groups = array();
	$wasGrouped = false;
	
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		foreach ($groups as $k=>$v)
		{
			if ($k == $row['ip'])
			{
				$groups[$k][] = array("id"=>$row['id'], "uagent"=>$row['uagent'], "date"=>$row['date']);
				$wasGrouped = true;
				break;
			}
			else
				$wasGrouped = false;
		}
		
		if (!$wasGrouped)
		{
			$groups[$row['ip']][] = array("id"=>$row['id'], "uagent"=>$row['uagent'], "date"=>$row['date']);
		}
	}
	
	foreach ($groups as $ip=>$v)
	{
		printf("<table class='expander' border='1' cellpadding='10'><tr><th>ID</th><th>IP: %s</th><th>Browser</th><th>Date</th></tr>", $ip);
		
		foreach ($v as $k=>$val)
		{
			printf("
				<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>
				   ", 
			$val['id'], $ip, $val['uagent'], date("H:i:s d-m-Y", (int)$val['date']));
		}
		
		echo "</table>";
	}
	
	echo "</div>";
	
echo <<<HEREDOC
<script type="text/javascript">

$(document).ready(go)

function go()
{
	$(".expander tr:has(td)").hide()
	$(".expander tr:has(th)").css({"cursor":"pointer"}).toggle(
	function()
	{
		$(this).parent().children("tr:has(td)").show()
	},
	function()
	{
		$(this).parent().children("tr:has(td)").hide()
	})
}

function back()
{
	$(".sNav").attr("disabled","")
	var res = $(".sFrom").val()
	
	$.ajax({
		url:"/psw/stat.php?result="+res,
		type:"GET",
		success:function(data)
		{
			$(".stat").remove()
			$(data).appendTo("body")
			go()
		}
		})
}

function forward()
{
	$(".sNav").attr("disabled","")
	
	var res = $(".sNext").val()
	
	$.ajax({
		url:"/psw/stat.php?result="+res,
		type:"GET",
		success:function(data)
		{
			$(".stat").remove()
			$(data).appendTo("body")
			go()
		}
		})
}

</script>
HEREDOC;

}
else if(!empty($_GET['result']) && $_GET['result'] != "hide")
{
	$to = (int)$_GET['result'];
    $from = mktime(0,0,0,(date("j", $to) > 9)?date("n", $to):((date("n", $to) > 1)?date("n", $to)-1:12),(date("j", $to) > 9)?date("j", $to)-9:30-9, (date("n", $to) > 1 && date("j", $to) > 9)?date("Y", $to):date("Y", $to)-1);
    $next = mktime(0,0,0,
    	(date("j", $to) <= 21)?date("n", $to):((date("n", $to) < 12)?date("n", $to)+1:1),
        (date("j", $to) <= 21)?date("j", $to)+9:10-(30-date("j")),
        (date("n", $to) < 12 && date("j", $to) <= 21)?date("Y", $to):date("Y", $to)+1);
    
    $img = imagecreatetruecolor(1000, 300);
	imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
	$black = imagecolorallocate($img, 0, 0, 0);
	$blue = imagecolorallocate($img, 0, 0, 255);
	
	imageline($img, 10, 10, 10, 280, $black);
	imageline($img, 10, 280, 990, 280, $black);
	
	$dates;
	$x = 990 - 68;
	for ($i = 0; $i < 10; $i++)
	{
		$date = mktime(0,0,0,
		(date("j", $to) > $i)?date("n", $to):((date("n", $to) > 1)?(date("n", $to)-1):12),
		(date("j", $to) > $i)?date("j", $to)-$i:30-$i,
		(date("j", $to) > $i && date("n", $to) > 1)?date("Y", $to):date("Y", $to)-1);
		
		imagestring($img, 3, $x, 280, date("d-m-Y", $date), $black);//w: 68
		$dates[9 - $i] = array($date, 0);
		$x -= 98;
	}
	
	$stmt = $s_db->query("SELECT * FROM stat WHERE date >= ".$from." and date <= ".$to." ORDER BY date");
	
	$max = 0;
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		$dt = (int)$row['date'];
		
		for ($k=0; $k <= 9; $k++)
		{
			if (($k + 1) <= 9)
			{
				if ($dt >= $dates[$k][0] && $dt < $dates[$k+1][0])
				{
					$dates[$k][1]++;
					$max = ($dates[$k][1] > $max)?$dates[$k][1]:$max;
				}
				
			}
			else
			{
				
				if ($dt >= $dates[$k][0])
				{
					$dates[$k][1]++;
					$max = ($dates[$k][1] > $max)?$dates[$k][1]:$max;
				}
				
			}
		}
	}

	$x = 990 - 68;
	$prev_y;
	for ($i = 9; $i >= 0; $i--)
	{
		$amount = $dates[$i][1];
		if ($amount != 0)
		{
			$percent = $amount / $max;
			$y = 300 - (290 * $percent);
		}
		else
			$y = 280;
		
		if ($i == 9)
		{
			imageline($img, $x+34, $y, 990, $y, $blue);
			imagestring($img, 3, $x+34, $y-12, $amount, $blue);
			$prev_y = $y;
		}
		else
		{
			imageline($img, $x+34, $y, $x+131, $prev_y, $blue);
			imagestring($img, 3, $x+34, $y-12, $amount, $blue);
			$prev_y = $y;
		}
		
		$x -= 98;
	}
	
	imagejpeg($img, "img.jpg");
	imagedestroy($img);
    
    echo "<div class='stat'><img src='img.jpg'><br><input type='hidden' value='".$from."' class='sFrom'><input type='hidden' value='".$next."' class='sNext'>
	<button class='sNav' onClick='back()'>В прошлое</button>
	<button class='sNav' onClick='forward()'>К настоящему</button>";
    
    $stmt = $s_db->query("SELECT * FROM stat WHERE date >= ".$from." and date <= ".$to." ORDER BY id DESC");
	
	$groups = array();
	$wasGrouped = false;
	
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
	{
		foreach ($groups as $k=>$v)
		{
			if ($k == $row['ip'])
			{
				$groups[$k][] = array("id"=>$row['id'], "uagent"=>$row['uagent'], "date"=>$row['date']);
				$wasGrouped = true;
				break;
			}
			else
				$wasGrouped = false;
		}
		
		if (!$wasGrouped)
		{
			$groups[$row['ip']][] = array("id"=>$row['id'], "uagent"=>$row['uagent'], "date"=>$row['date']);
		}
	}
	
	foreach ($groups as $ip=>$v)
	{
		printf("<table class='expander' border='1' cellpadding='10'><tr><th>ID</th><th>IP: %s</th><th>Browser</th><th>Date</th></tr>", $ip);
		
		foreach ($v as $k=>$val)
		{
			printf("
				<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>
				   ", 
			$val['id'], $ip, $val['uagent'], date("H:i:s d-m-Y", (int)$val['date']));
		}
		
		echo "</table>";
	}
	
	echo "</div>";
}

?>