<?php
# Seafito version 0.1
# Seafile nasty frontend
# php-curl required


### Start config

# Enable pagination
$pagination_footable=1; # 1 enable / 0 disable

# Pagination rows per page
$pagination_footable_pages=30;

# Timeout session
$max_idle_time=1440; #seconds

# Brand
$brand_name=''; # empty = current repo or section (For Menu)
#$brand_name='Seafito'; # Brand 

$brand_url=''; # empty = current repo or section 
#$brand_url='index.php'; # Brand link
$brand_title=''; # empty = current repo/dir or section (For page title)
#$brand_title='Seafito'; # Seafito - repo/dir

### End config











## Session
session_start();
$basename_file=basename(__FILE__);

include("functions.php");	
include("template.php");	


## Pagination if then
$pagination_footable_pages=($pagination_footable!=1) ? "9999": $pagination_footable_pages;

## php.net site logout style.

if (isset($_GET['logout'])){
    if (isset($_GET['timeout'])){
        $logout_url=$basename_file.'?timeout';
        }else{
        $logout_url=$basename_file.'?logged_out';
        }
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
session_destroy();

header("Location:$logout_url");
}
##

# check timeout session if logged in (if token exists)
(isset($_SESSION['token'])) ? check_timeout($max_idle_time) :'' ;

## Login

if (isset($_POST['login'])){
    $username=$_POST['username'];
    $password=$_POST['password'];
    $hostname=$_POST['hostname'];
    $token=1;
    $token=seafileLogin($username,$password,$hostname);
    if (!isset($token['token'])){
        $brand_title=($brand_title!='') ? $brand_title.' - Login' : 'Login';
        $header_html = str_replace("##TITLE##", $brand_title, $header_template);
        echo $header_html;
        $error_msg= '
        <div class="alert alert-danger form-alert" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Error!</strong> '  .$token['non_field_errors']['0'].'
        </div>';
        
        $login_template = str_replace("##ERROR_ALERT##", $error_msg, $login_template);

        echo $login_template;
        die($footer_template);
    }
    $_SESSION['username']=$_POST['username'];
    $_SESSION['hostname']=$_POST['hostname'];
    $_SESSION['token']=$token['token'];
    
    $avatar_seafile_api="/api2/avatars/user/".$username."/resized/32/";
    $avatar_seafile = seafileApi('GET',$avatar_seafile_api,'',$_SESSION['token'],$_SESSION['hostname']);
    $_SESSION['avatar_url'] = save_avatar($avatar_seafile['url']);
    header("Location:$basename_file");
}
##


## Library content

if(isset($_GET['dir']) AND isset($_SESSION['token'])){

    $table_results=<<<TABLE_RESULTS
    
<table class="footable" data-filter="#filter" data-page-size="$pagination_footable_pages">
    <thead>
        <tr>
            <th data-type="alpha">Name</th>
            <th data-type="numeric" data-hide="phone">Size</th>
            <th data-type="numeric" data-hide="phone,tablet">Last modified</th>
        </tr>
    </thead>
<tbody>
TABLE_RESULTS;

    $library="/api2/repos/" . $_GET['repo'] . "/dir/?p=/" . $_GET['dir'] . "/";
    $repo_list = seafileApi('GET',$library,'',$_SESSION['token'],$_SESSION['hostname']);
    $repo_name = $_GET['repo_name'];
    $dirc = cut_last_occurence($_GET['dir'],"/");

    if ($_GET['dir']!=""){
        $table_results.=<<<TABLE_RESULTS
        
    <tr class="footable-disabled">
        <td colspan="3" class="footable-disabled">
            <a href="?dir=$dirc&repo={$_GET['repo']}&repo_name=$repo_name"><img src="img/folder.png" alt="Back" height="24" width="24">..</a>
        </td>
    </tr>
TABLE_RESULTS;

        }else{
        $table_results.=<<<TABLE_RESULTS
        
    <tr class="footable-disabled">
        <td colspan="3" class="footable-disabled">
            <a href="?"><img src="img/folder.png" alt="Back" height="24" width="24">..</a>
        </td>
    </tr>
TABLE_RESULTS;

    }


    foreach ($repo_list as $array_value) {
 
        if($array_value['type']=="dir"){
            $time_elapsed=time_elapsed_string($array_value['mtime'],'1');
            $table_results.=<<<TABLE_RESULTS
            
    <tr>
        <td><img src="img/folder.png" alt="Dir" height="24" width="24"><a href="?dir={$_GET['dir']}/{$array_value['name']}&repo={$_GET['repo']}&repo_name=$repo_name">{$array_value['name']}</td>
        <td>&nbsp;</td>
        <td data-value="{$array_value['mtime']}">$time_elapsed</td>

    </tr>
TABLE_RESULTS;
            
            }else{
            $time_elapsed=time_elapsed_string($array_value['mtime'],'1');
            $format_bytes=formatBytes($array_value['size']);        
            
            $table_results.=<<<TABLE_RESULTS
    <tr>
        <td><img src="img/file.png" alt="File" height="24" width="24">{$array_value['name']} <div class="pull-right"><a href="?download={$_GET['repo']}/file/?p=/{$_GET['dir']}/&file={$array_value['name']}&repo_name=repo_name" title="Download {$array_value['name']}"><button type="button" class="btn btn-default  btn-xs"><span class="glyphicon glyphicon-download"></span></button></a></div></td>
        
        <td data-value="{$array_value['size']}">$format_bytes</a> </td>
        <td data-value="{$array_value['mtime']}">$time_elapsed</td>
    </tr>
TABLE_RESULTS;

        }
    } 
    $table_results.= '  </tbody>';

    $table_results.=($pagination_footable!=1) ? "" : $pagination_footable_html;
    $table_results.='</table>';
        
    $dirbreadcrumb = explode( '/', (ltrim ($_GET['dir'],'/') ) );

    $other_content= '
    <ol class="breadcrumb">
        <li><a href="?">Home</a></li>';
    
    if($_GET['dir']==''){
        $other_content.='<li class="active">' . $_GET['repo_name'] . '</li>';		
        }else{
        $other_content.='<li><a href="?repo=' . $_GET['repo'] . '&repo_name='.$_GET['repo_name'].'&dir=">' . $_GET['repo_name'] . '</a></li>';	
        $numItems = count($dirbreadcrumb);
        $i = 0;
        foreach($dirbreadcrumb as $value){
                if(++$i === $numItems) {
                    $other_content.='<li class="active">'.$value.'</li>';
                    break;
                }
            $other_content.= '<li><a href="?repo=' . $_GET['repo'] . '&repo_name='.$_GET['repo_name'].'&dir=/'.$value.'">'.$value.'</a> </li>';
        }
    }
    $other_content.="</ol>";
 
    
    $main_template = str_replace("##TABLE_RESULTS##", $table_results, $main_template);
    $main_template = str_replace("##OTHER_CONTENT##", $other_content, $main_template);
    
    $brand_name= ($brand_name!='') ? $brand_name : '<small>library </small>' .$_GET['repo_name'];
    $brand_url= ($brand_url!='') ? '?repo=' . $_GET['repo'] . '&repo_name='.$_GET['repo_name'].'&dir=' : $brand_url;
    
    $main_template = str_replace("##BRAND_NAME##",  $brand_name, $main_template);
    $main_template = str_replace("##BRAND_URL##", $brand_url, $main_template);
    
    $main_template = str_replace("##ACTIVE_ITEM##", '', $main_template);
    $main_template = str_replace("##ACTIVE_ITEM2##", '', $main_template);
    
    $brand_title=($brand_title!='') ? $brand_title.' - Index of /' .$repo_name . $_GET['dir'] : 'Index of /' .$repo_name . $_GET['dir'];
    $header_template = str_replace("##TITLE##", $brand_title, $header_template);
    
    echo $header_template;
    echo $main_template;

    die($footer_template);
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



## List shared links

if(isset($_GET['shared_links']) AND isset($_SESSION['token'])){

    if(isset($_GET['delete_link'])){
    echo $_GET['delete_link'];
    
    die ("lala");
    }
    
    $table_results=<<<TABLE_RESULTS
    
    <table class="footable" data-filter="#filter" data-page-size="$pagination_footable_pages">
        <thead>
            <tr>
                <th data-sort-initial="ascending">Links</th>
                <th data-hide="tablet,phone">Library</th>
                <th data-sort-ignore="true" data-hide="all">Owner</th>
                <th data-sort-ignore="true" data-hide="all">Link</th>
                <th data-sort-ignore="true">Actions</th>
            </tr>
        </thead>
    <tbody>
TABLE_RESULTS;

    $shared_list=seafileApi('GET','/api2/shared-links/','',$_SESSION['token'],$_SESSION['hostname']);

    foreach ($shared_list as $v1) {
        foreach ($v1 as $v2) {
        
            $table_results.= '<tr>';
            if ($v2['s_type']=="d"){
            $table_results.=  '<td><img src="img/folder.png" alt="Dir" height="24" width="24">'.$_SESSION['repo_list'][$v2['repo_id']].$v2['path'].'</td>';
            }else{
            $table_results.=  '<td><img src="img/file.png" alt="File" height="24" width="24">'.$_SESSION['repo_list'][$v2['repo_id']].$v2['path'].'</td>';
            }
            
            $dirc = cut_last_occurence($v2['path'],"/");
            $table_results.=  '<td><a href="?repo='.$v2['repo_id'].'&repo_name=' . $_SESSION['repo_list'][$v2['repo_id']] . '&dir='.$dirc.'">'. $_SESSION['repo_list'][$v2['repo_id']].'</a></td>';
            $table_results.=  '<td>' . $v2['username'] . '</td>';
            
            if ($v2['s_type']=="d"){
            $table_results.=  '<td><a href="'.$_SESSION['hostname'].'d/'.$v2['token'].'/'.'" target="_blank">'.$_SESSION['hostname'].'d/'.$v2['token'].'/'.'</a></td>';
            }else{
            $table_results.=  '<td><a href="'.$_SESSION['hostname'].'f/'.$v2['token'].'/'.'" target="_blank">'.$_SESSION['hostname'].'f/'.$v2['token'].'/'.'</a></td>';
            }
            
            
            $table_results.=  '<td><a href="?shared_links&delete_link='.$v2['token'].'" title="Remove shared link" ><i class="glyphicon glyphicon-remove"></i></a></td>';
            $table_results.=  '</tr>';	

            
        }
    }
    
    $table_results.=  '</tbody>';  

    $table_results.=($pagination_footable!=1) ? "" : $pagination_footable_html;
    $table_results.= '</table>';

    $table_results=(count($shared_list['fileshares'])==0) ? $error_alert_no_shared_files : $table_results;
    $other_content= '
    <ul class="breadcrumb">
        <li><a href="?">Home</a></li>
        <li class="active">Shared links</li>
    </ul>';	
    $main_template = str_replace("##OTHER_CONTENT##", $other_content, $main_template);
    $main_template = str_replace("##ACTIVE_ITEM##", '', $main_template);
    $main_template = str_replace("##ACTIVE_ITEM2##",' class="active"', $main_template);
    $main_template = str_replace("##TABLE_RESULTS##", $table_results, $main_template);

    $brand_name= ($brand_name!='') ? $brand_name : "Shared links";
    $brand_url=($brand_url!='') ? '?shared_links' : $brand_url;
    
    $main_template = str_replace("##BRAND_NAME##", $brand_name, $main_template);
    $main_template = str_replace("##BRAND_URL##", $brand_url, $main_template);  
    
    $brand_title=($brand_title!='') ? $brand_title.' - Shared links' : 'Shared links';
    $header_template = str_replace("##TITLE##", $brand_title, $header_template);
    
    echo $header_template;
    echo $main_template;

    die($footer_template);
}
##




## Library list (must be next to last)

if (isset($_SESSION['token'])){
    $table_results=<<<TABLE_RESULTS

<table class="footable" data-filter="#filter" data-page-size="$pagination_footable_pages">
    <thead>
        <tr>
            <th data-sort-initial="ascending">Libraries</th>
            <th data-hide="phone,tablet">Description</th>
            <th data-sort-ignore="true" data-hide="all">Permissions</th>
            <th data-type="numeric">Last modified</th>
            <th data-sort-ignore="true" data-hide="all">Owner</th>
            <th data-sort-ignore="true" data-hide="all">Encripted</th>
        </tr>  
    </thead>
<tbody>
TABLE_RESULTS;

    $_SESSION['repo_list']=array();
    $repo_list = seafileApi('GET','/api2/repos/','',$_SESSION['token'],$_SESSION['hostname']);
    
    foreach ($repo_list as $array_value) {
        $encrypted=($array_value['encrypted']!="") ? "Yes" : "No";
        $time_elapsed=time_elapsed_string($array_value['mtime'],'1');
        
        $table_results.=<<<TABLE_RESULTS
        
    <tr>
        <td><a href="?repo=${array_value['id']}&repo_name=${array_value['name']}&dir=">${array_value['name']}</a></td>
        <td>${array_value['desc']}</td>
        <td>${array_value['permission']}</td>
        <td data-value="${array_value['mtime']}">$time_elapsed</td>
        <td>${array_value['owner']}</td>
        <td>
        $encrypted
        </td>
    </tr>
    
TABLE_RESULTS;
        $_SESSION['repo_list'][$array_value['id']]=$array_value['name'];

    }


    $table_results.=  '</tbody>';  

    $table_results.=($pagination_footable!=1) ? "" : $pagination_footable_html;
    $table_results.= '</table>';
    $other_content= '
    <ol class="breadcrumb">
        <li class="active">Home</li>
        </ol>';
    $main_template = str_replace("##OTHER_CONTENT##", $other_content, $main_template);
    $main_template = str_replace("##TABLE_RESULTS##", $table_results, $main_template);
    $main_template = str_replace("##ACTIVE_ITEM##", ' class="active"', $main_template);
    $main_template = str_replace("##ACTIVE_ITEM2##", '', $main_template);
    
    $brand_name= ($brand_name!='') ? $brand_name : "<small>my</small> Libraries";
    $brand_url=($brand_url!='') ? '?' : $brand_url;
    
    $main_template = str_replace("##BRAND_NAME##", $brand_name, $main_template);
    $main_template = str_replace("##BRAND_URL##", $brand_url, $main_template);
    
    $brand_title=($brand_title!='') ? $brand_title.' - Libraries' : 'Libraries';
    $header_template = str_replace("##TITLE##", $brand_title, $header_template);
    
    echo $header_template;
    echo $main_template;
    
    die($footer_template);
}
##





## Not logged. Show login.

if (!isset($_SESSION['token'])){
    $brand_title=($brand_title!='') ? $brand_title.' - Login' : 'Login';
    $header_template = str_replace("##TITLE##", $brand_title, $header_template);
    echo $header_template;
    $error_msg='';
        if (isset($_GET['timeout'])){
            $error_msg=$error_alert_timeout_msg;
        }
        if (isset($_GET['logged_out'])){
            $error_msg=$error_alert_logout_msg;
        }

    $login_template = str_replace("##ERROR_ALERT##", $error_msg, $login_template);
    echo $login_template;
    die($footer_template);
}
##

?>