<?php
# Seafito version 0.1
# Seafile nasty frontend
# php-curl required
session_start();
include("functions.php");	


### Start config

# Enable pagination
$pagination_footable=1; # 1 enable / 0 disable

# Pagination rows per page
$pagination_footable_pages=30;

### End config


## Pagination if then
$pagination_footable_pages=($pagination_footable!=1) ? "9999": $pagination_footable_pages;
##

## Header html
$header_html=
'
<!DOCTYPE html>
<html>
<head>
<title>##TITLE##</title>
<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/x-icon" href="img/favicon.png" />
<!--[if IE]>
<link rel="shortcut icon" href="img/favicon.png"/>
<![endif]-->
<link href="css/footable.core.css" rel="stylesheet" type="text/css" />
<link href="css/footable.standalone.css" rel="stylesheet" type="text/css" />

<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="js/footable.js" type="text/javascript"></script>
<script src="js/footable.sort.js" type="text/javascript"></script>
<script src="js/footable.filter.js" type="text/javascript"></script>
<script src="js/footable.paginate.js" type="text/javascript"></script>
<style type="text/css">

</style>
<script type="text/javascript">
$(function () {
	$(\'.footable\').footable();
});
$(document).ready(function(){

  $("#clear_filter").click(function(){
	$(\'.footable\').trigger(\'footable_clear_filter\');
  });
});
</script>
</head>
<body bgcolor="#FFFFFF">
';
##

## Footer html
$footer_html=
'
</body>
</html>
';
##

## Login html
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
##

## php.net site logout style.
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
##

## Login
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
	$_SESSION['username']=$_POST['username'];
	$_SESSION['hostname']=$_POST['hostname'];
	$_SESSION['token']=$token['token'];
	header("Location:?");
}
##

## Library content
if(isset($_GET['dir']) AND isset($_SESSION['token'])){

	$table_results= '<table class="footable toggle-arrow-small" data-filter="#filter" data-page-size="'.$pagination_footable_pages.'">  <thead>';
	$table_results.= '<tr>';
	$table_results.= '<th>Name</th>';
	$table_results.= '<th data-type="numeric">Size</th>';
	$table_results.= '<th data-type="numeric" data-hide="phone,tablet">Last modified</th>';
	$table_results.= '<th data-hide="all">Download </th>';
	$table_results.= '</tr></thead><tbody>';
	
	$library="/api2/repos/" . $_GET['repo'] . "/dir/?p=/" . $_GET['dir'] . "/";
	$repo_list = seafileApi('GET',$library,'',$_SESSION['token'],$_SESSION['hostname']);
	$repo_name = $_GET['repo_name'];
	$dirc = cut_last_occurence($_GET['dir'],"/");

	if ($_GET['dir']!=""){
		$table_results.= '<tr class="footable-disabled"><td colspan="3" class="footable-disabled"><a href="?dir=' .$dirc. '&repo=' . $_GET['repo'] . '&repo_name='.$repo_name.'"><img src="img/folder.png" alt="Back" height="24" width="24">..</a></td>';
		$table_results.= '</tr>';
		}else{
		$table_results.= '<tr class="footable-disabled"><td colspan="3" class="footable-disabled"><a href="?"><img src="img/folder.png" alt="Back" height="24" width="24">..</a></td>';
		$table_results.= '</tr>';
	}

	foreach ($repo_list as $array_value) {
		if($array_value['type']=="dir"){
			$table_results.= '<tr>';
			$table_results.= '<td><img src="img/folder.png" alt="Dir" height="24" width="24"><a href="?dir=' .$_GET['dir'] . "/" . $array_value['name'] . '&repo=' . $_GET['repo'] . '&repo_name='.$repo_name.'">' . $array_value['name'] . '</td>';
			$table_results.= '<td>&nbsp;</td>';
			$table_results.= '<td data-value="'.$array_value['mtime'].'">' . time_elapsed_string($array_value['mtime'],'1') . '</td>';
			$table_results.= '</tr>';
			}else{
			$table_results.= '<tr><td><img src="img/file.png" alt="File" height="24" width="24">' . $array_value['name'] . '</td>';
			$table_results.= '<td data-value="'.$array_value['size'].'">' . formatBytes($array_value['size']) . '</a> </td>';
			$table_results.= '<td data-value="'.$array_value['mtime'].'">' . time_elapsed_string($array_value['mtime'],'1') . '</td>';
			$table_results.= '<td><a href="?download=' .  $_GET['repo']  . '/file/?p=/' .$_GET['dir'] . '/&file=' . $array_value['name'] . '&repo_name='.$repo_name.'">' . $array_value['name'] . '</a> </td>';
			$table_results.= '</tr>';
		}
	} 
	$table_results.= '  </tbody>';
	$pagination_footable_html='
	<tfoot>
		<tr>
			<td colspan="5">
				<div class="pagination pagination-centered hide-if-no-paging"></div>
			</td>
		</tr>
	</tfoot>';
	$table_results.=($pagination_footable!=1) ? "" : $pagination_footable_html;
	$table_results.='</table>';
	$logout_html="<br><address>". $_SESSION['username']." logged on ".$_SESSION['hostname']." <a href='?logout=1'>logout</a></address>";
	$header_html = str_replace("##TITLE##", 'Seafito - Index of /' .$repo_name . $_GET['dir'], $header_html);
	echo $header_html;
	echo '<h1>Index of /' .$repo_name. $_GET['dir'] . '</h1>';	
	echo '<div style="padding:5px;">&nbsp;Filter : <input id="filter" type="text" />&nbsp;[<a href="#" id="clear_filter">Clear filter</a>]</div>';
	echo $table_results;
	echo $logout_html;
	die($footer_html);
}
##

## Download file
if (isset($_GET['download']) AND isset($_SESSION['token'])){
	$library="/api2/repos/" .  $_GET['download'] . rawurlencode($_GET['file']);
	$repo_list = seafileApi('GET',$library,'',$_SESSION['token'],$_SESSION['hostname']);
	header("Location:$repo_list");
	die();
}
##

## Library list
if (isset($_SESSION['token'])){
	$table_results= '<table class="footable toggle-arrow-small" data-filter="#filter" data-page-size="'.$pagination_footable_pages.'"><thead>';
	$table_results.= '<tr>';
	$table_results.= '<th data-sort-initial="ascending">Library</th>';
	$table_results.= '<th data-hide="phone,tablet">Description</th>';
	$table_results.= '<th data-sort-ignore="true" data-hide="all">Permissions</th>';
	$table_results.= '<th data-type="numeric">Last modified</th>';
	$table_results.= '<th data-sort-ignore="true" data-hide="all">Owner</th>';
	$table_results.= '<th data-sort-ignore="true" data-hide="all">Encripted</th>';
	$table_results.= '</tr>  </thead>  <tbody>
    ';

	$repo_list = seafileApi('GET','/api2/repos/','',$_SESSION['token'],$_SESSION['hostname']);
	foreach ($repo_list as $array_value) {
		$table_results.= '<tr>';
		$table_results.=  '<td><a href="?repo=' . $array_value['id'] . '&repo_name='.$array_value['name'].'&dir=">' . $array_value['name'] . '</a></td>';
		$table_results.=  '<td>' . $array_value['desc'] . '</td>';
		$table_results.=  '<td>' . $array_value['permission'] . '</td>';
		$table_results.= '<td data-value="'.$array_value['mtime'].'">' . time_elapsed_string($array_value['mtime'],'1') . '</td>';
		$table_results.=  '<td>' . $array_value['owner'] . '</td>';
		$table_results.=  '<td>';
		$table_results.=   ($array_value['encrypted']!="") ? "Yes" : "No";
		$table_results.=  '</td>';	
		$table_results.=  '</tr>';	
	} 


	$table_results.=  '</tbody>';  
	$pagination_footable_html='
	<tfoot>
		<tr>
			<td colspan="5">
				<div class="pagination pagination-centered hide-if-no-paging"></div>
			</td>
		</tr>
	</tfoot>';
	$table_results.=($pagination_footable!=1) ? "" : $pagination_footable_html;
	$table_results.= '</table>';
	$logout_html="<div style=\"padding:5px;\">". $_SESSION['username']." logged on ".$_SESSION['hostname']." <a href='?logout=1'>logout</a></div>";
	$header_html = str_replace("##TITLE##", "Seafito - Index of /", $header_html);
	echo $header_html;
	echo '<h1>Index of /</h1>';	
	echo '<div style="padding:5px;">&nbsp;Filter : <input id="filter" type="text" />&nbsp;[<a href="#" id="clear_filter">Clear filter</a>]</div>';

	echo $table_results;
	echo $logout_html;
	die($footer_html);
}
##

## Not logged. Show login.
if (!isset($_SESSION['token'])){
	$header_html = str_replace("##TITLE##", 'Seafito - Login', $header_html);
	echo $header_html;
	echo $login_html;
	die($footer_html);
}
?>