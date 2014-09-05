<?php
# Seafito version 0.1
# Seafile nasty frontend
# php-curl required


# Prevent direct access to a php include file http://stackoverflow.com/a/409738 (PHP4/5)
echo (count(get_included_files()) == 1) ? exit("Direct access not permitted.") : '';

# if no avatar, set default image.
$avatar_url = (isset($_SESSION['avatar_url'])) ? $_SESSION['avatar_url'] : 'avatars/default_avatar.png';

## Header template
# do not close <head> tag
#do not include <body> tag
$header_template = <<<HEADER
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>##TITLE##</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.png" />
    <!--[if IE]>
    <link rel="shortcut icon" href="img/favicon.png"/>
    <![endif]-->
    

      <!--Continue head -->


HEADER;

## Main template
# here we close <head> tag
# and include a <body> tag.
$main_template = <<<MAIN
    <!-- Footable modified styles -->
    <link href="css/footable.mod.core.css" rel="stylesheet" type="text/css" />
    <link href="css/footable.mod.standalone.css" rel="stylesheet" type="text/css" />
    
    <!-- 19px to make the container go all the way to the bottom of the topbar -->
    <style>
    .navbar-static-top {
    margin-bottom: 19px;
    }
    </style> 

    <!-- Bootstrap styles -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.min.js"></script>
    <![endif]-->
    
    <!-- navbar-toggle avatar -->
    <style>
    .navbar-toggle {
        padding: 2px 2px; 
        margin-top: 6px;
        margin-right: 15px;
        margin-bottom: 6px;
        background-color: transparent;
        background-image: none;
        border: 1px solid transparent;
        border-radius: 4px;
    }
    </style> 

    </head>
<body>
    <!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-static-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a href="#" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse"><img src="$avatar_url"></a>
            <span class="sr-only">Toggle navigation</span>
          <a class="navbar-brand" href="##BRAND_URL##">##BRAND_NAME##</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
              <li##ACTIVE_ITEM##><a href="?">Home</a></li>
              <li##ACTIVE_ITEM2##><a href="?shared_links">Shared links</a></li>
              <li><a href="?logout=1">Logout</a></li>
          </ul>
        <form class="navbar-form navbar-right" role="search">
            <div class="form-group">
                <div class="input-group input-group-sm">
                    <input id="filter" type="text" placeholder="Filter" type="text" class="form-control">
                    <span class="input-group-btn">
                    <button type="button" class="btn btn-default btn-xs" id="clear_filter">Clear</button>
                    </span>
                </div>
            </div>
        </form>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">
    ##OTHER_CONTENT##
    ##TABLE_RESULTS##
    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/footable.all.min.js" type="text/javascript"></script>

         <!-- Footable start -->
    <script type="text/javascript">
$('.footable').footable({
    breakpoints: {
        phone: 380,
        tablet: 690
    }
});

    $(document).ready(function(){

      $("#clear_filter").click(function(){
        $('.footable').trigger('footable_clear_filter');
      });
      

});


    </script> 

MAIN;



## Login template
# here we close <head> tag
# and include a <body> tag.
$login_template = <<<LOGIN
   
    <!-- Bootstrap styles -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="js/html5shiv.min.js"></script>
    <![endif]-->


      </head>
<body>
<div class="container">
    ##ERROR_ALERT##
    <form action="$basename_file" method="POST" class="form-signin" role="form">
        <h2 class="form-signin-heading">Login</h2>
        
        <div class="input-group input-group-sm">
            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
        <input type="username" class="form-control" name="username" id="username" placeholder="Username" required autofocus>
        </div>
        <div class="input-group input-group-sm">
            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
        <input type="password" class="form-control"  name="password" id="password" placeholder="Password" required>
        </div>
                
        <div class="input-group input-group-sm">
            <span class="input-group-addon"><i class="glyphicon glyphicon-globe"></i></span>
        <input type="hostname" class="form-control"  name="hostname" id="hostname" placeholder="Hostname" required>
        </div>
        <p></p>
        <input name="login" type="hidden" value="Login">
        <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
      </form>
      

</div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

LOGIN;

$pagination_footable_html=<<<PAGINATION_FOOTABLE

<tfoot>
    <tr>
        <td colspan="5" class="text-center row">	
            &nbsp;<div class="pagination hide-if-no-paging"></div> <!-- Pagination -->	
        
        </td>
    </tr>
</tfoot>
PAGINATION_FOOTABLE;


## Errors template
# timeout and logout alerts
$error_alert_timeout_msg=<<<ERROR_ALERT
<div class="alert alert-danger form-alert" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Logged off!</strong> Timeout exceeded time allowed.
    </div>
ERROR_ALERT;

$error_alert_logout_msg=<<<ERROR_ALERT
<div class="alert alert-success form-alert" role="alert">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <strong>Logged off!</strong> successfully logged off.
    </div>
ERROR_ALERT;

$error_alert_no_shared_files=<<<ERROR_ALERT
<div class="col-xs-8 col-md-6 alert alert-danger" role="alert">
    <strong>Error</strong> no shared links found!
    </div>
ERROR_ALERT;




## Footer template
# here we close <body> tag
# and <html> tag.
$footer_template = <<<FOOTER
  </body>
</html>
FOOTER;


?>
