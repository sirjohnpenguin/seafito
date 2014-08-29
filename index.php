<?php
# Seafito version 0.1
# Seafile nasty frontend
# php-curl required
session_start();
include("functions.php");	

$header_html=
'
<HTML>
<HEAD>
<TITLE>##TITLE##</TITLE>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<link rel="icon" type="image/x-icon" href="img/favicon.png" />
<!--[if IE]>
<link rel="shortcut icon" href="img/favicon.png"/>
<![endif]-->
<style type="text/css">
.tg  {border-collapse:collapse;border-spacing:0;width:100%;margin:0px;}
.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;}
.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:0px;overflow:hidden;word-break:normal;}
.tg .tg-k6pi{font-size:12px}
.tg .tg-icon{font-size:12px;width:24px}
</style>
</HEAD>
<BODY BGCOLOR="#FFFFFF">
';

$footer_html=
'
</BODY>
</HTML>
';

$login_html='
<form action="' . basename(__FILE__) . '" method="post" name="login_form" id="login_form">
Username<br>
<input name="username" type="text" id="username" tabindex="0">
<br>
Password<br>
<input name="password" type="password" id="password">
<br>
Hostname<br>
<input name="hostname" type="text" id="hostname">
<br>
<br>
<input name="login" type="submit" value="Login">
<input name="login" type="hidden" value="Login">
<br>

</form>
';


// php.net site logout style.
if (isset($_GET['logout'])){
	$_SESSION = array();
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
		);
	}
session_destroy();
header("Location:?");
}

if (isset($_POST['login'])){
	$username=$_POST['username'];
	$password=$_POST['password'];
	$hostname=$_POST['hostname'];
	$token=1;
	$token=seafileLogin($username,$password,$hostname);
	
		
	if (!isset($token['token'])){
		echo $header_html;
		echo $login_html;
		echo "ERROR: " .$token['non_field_errors']['0'];
		die($footer_html);
	}
	# si esta todo bien, vuelvo al index
	# y muestro la lista principal de
	# librerias, registro la token y me voy al joraca.
	$_SESSION['username']=$_POST['username'];
	$_SESSION['hostname']=$_POST['hostname'];
	$_SESSION['token']=$token['token'];
	header("Location:?");
}

if(isset($_GET['dir']) AND isset($_SESSION['token'])){
	$table_results= '
	<table class="tg">
	';
	$library="/api2/repos/" . $_GET['repo'] . "/dir/?p=/" . $_GET['dir'] . "/";
	$library_name="/api2/repos/" . $_GET['repo'] . "/";
	$repo_list = seafileApi('GET',$library,'',$_SESSION['token'],$_SESSION['hostname']);
	$repo_name = seafileApi('GET',$library_name,'',$_SESSION['token'],$_SESSION['hostname']);
	$dirc = cut_last_occurence($_GET['dir'],"/");
	$table_results.= '<tr>';
	$table_results.= '<td></td>';
	$table_results.= '<td><b>Name</b></td>';
	$table_results.= '<td><b>Size</b></td>';
	$table_results.= '<td><b>Last modified</b></td></tr>';
	$table_results.= '<tr><td colspan="4"><hr></td></tr>';

	if ($_GET['dir']!=""){
		$table_results.= '<tr><td class="tg-icon"><img src="img/folder.png" alt="Dir" height="24" width="24"></td>';
		$table_results.= '<td class="tg-k6pi"><a href="?dir=' .$dirc. '&repo=' . $_GET['repo'] . '">..</a></td>';
		$table_results.= '</tr>';
		}else{
		$table_results.= '<tr><td class="tg-icon"><img src="img/folder.png" alt="Dir" height="24" width="24"></td>';
		$table_results.= '<td class="tg-k6pi"><a href="?">..</a></td>';
		$table_results.= '</tr>';
	}

	foreach ($repo_list as $array_value) {
		if($array_value['type']=="dir"){
			$table_results.= '<tr>';
			$table_results.= '<td class="tg-icon"><img src="img/folder.png" alt="Dir" height="24" width="24"></td>';
			$table_results.= '<td class="tg-k6pi"><a href="?dir=' .$_GET['dir'] . "/" . $array_value['name'] . '&repo=' . $_GET['repo'] . '">' . $array_value['name'] . '</a> </td></tr>';
			}else{
			$table_results.= '<td class="tg-icon"><img src="img/file.png" alt="File" height="24" width="24"></td>';
			$table_results.= '<td class="tg-k6pi"><a href="?download=' .  $_GET['repo']  . '/file/?p=/' .$_GET['dir'] . '/&file=' . $array_value['name'] . '">' . $array_value['name'] . '</a> </td>';
			$table_results.= '<td class="tg-k6pi">' . formatBytes($array_value['size']) . '</a> </td>';
			$table_results.= '<td class="tg-k6pi">' . time_elapsed_string($array_value['mtime'],'1') . '</td>';
			$table_results.= '</tr>';

			
		}
	
	} 

	$table_results.= '  </table>';
	$logout_html="<hr><address>". $_SESSION['username']." logged on ".$_SESSION['hostname']." <a href='?logout=1'>logout</a></address>";
	$header_html = str_replace("##TITLE##", 'Seafito - Index of /' .$repo_name['name'] . $_GET['dir'], $header_html);
	echo $header_html;
	echo '<h1>Index of /' .$repo_name['name'] . $_GET['dir'] . '</h1>';	
	echo $table_results;
	echo $logout_html;
	die($footer_html);
}

if (isset($_GET['download']) AND isset($_SESSION['token'])){
	$library="/api2/repos/" .  $_GET['download'] . rawurlencode($_GET['file']);
	$repo_list = seafileApi('GET',$library,'',$_SESSION['token'],$_SESSION['hostname']);
	header("Location:$repo_list");
	die();
}

if (isset($_SESSION['token'])){
	$table_results= '
	<table class="tg">
  ';
	$table_results.= '<tr>';
	$table_results.= '<td class="tg-icon"></td>';
	$table_results.= '<td><b>Name</b></td>';
	$table_results.= '<td><b>Desc</b></td>';
	$table_results.= '<td><b>Last modified</b></td></tr>';
	$table_results.= '<tr><td colspan="4"><hr></td></tr>';

	$repo_list = seafileApi('GET','/api2/repos/','',$_SESSION['token'],$_SESSION['hostname']);
	foreach ($repo_list as $array_value) {
		$table_results.= '<tr>';
		$table_results.=  '<td class="tg-icon"><img src="img/library.png" alt="Libraries" height="24" width="24"></td>';
		$table_results.=  '<td class="tg-k6pi"><a href="?repo=' . $array_value['id'] . '&repo_name='.$array_value['name'].'&dir=">' . $array_value['name'] . '</a></td>';
		$table_results.=  '<td class="tg-k6pi">' . $array_value['desc'] . '</td>';
		$table_results.= '<td class="tg-k6pi">' . time_elapsed_string($array_value['mtime'],'1') . '</td>';
		$table_results.=  '</tr>';	
	} 


	$table_results.=  '  </table>';
	$logout_html="<hr><address>". $_SESSION['username']." logged on ".$_SESSION['hostname']." <a href='?logout=1'>logout</a></address>";
	$header_html = str_replace("##TITLE##", "Seafito - Index of /", $header_html);
	echo $header_html;
	echo '<h1>Index of /</h1>';	
	echo $table_results;
	echo $logout_html;
	die($footer_html);
}

if (!isset($_SESSION['token'])){
	$header_html = str_replace("##TITLE##", 'Seafito - Login', $header_html);
	echo $header_html;
	echo $login_html;
	die($footer_html);
}
?>