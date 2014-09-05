<?php
# Seafito version 0.1
# Seafile nasty frontend
# php-curl required

# Prevent direct access to a php include file http://stackoverflow.com/a/409738
echo (count(get_included_files()) == 1) ? exit("Direct access not permitted.") : '';

# Login() function from https://github.com/combro2k/seafile-php
function seafileLogin($username,$password,$hostname) 
    {
        $fields = array(
            'username' => urlencode($username),
            'password' => urlencode($password),
        );

        $fields_string = '';
        foreach ($fields as $key => $value) {
            $fields_string .= $key . '=' . $value . '&';
        }
        rtrim($fields_string, '&');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $hostname . '/api2/auth-token/');
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                
        $result = json_decode(curl_exec($ch), true);

        curl_close($ch);
        return $result;
    }
# api() function from https://github.com/combro2k/seafile-php

function seafileApi($method = 'GET', $path = '', $data = array(), $token, $hostname)
    {
        $ch = curl_init();

        if (!preg_match('/^http(s|):/i', $path)) {
            $url = $hostname . $path;
        } else {
            $url = $path;
        }

        curl_setopt($ch, CURLOPT_URL, $url);

        switch ($method) {
            case "POST":
                curl_setopt($ch, CURLOPT_POST, count($data));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                break;
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Token ' . $token, 
            //'Accept: application/json; charset=utf-8; indent=4',
        ));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);

        if (curl_error($ch) || !isset($result)) {
            curl_error($ch);
        }

        curl_close($ch);

        return json_decode($result, true);
    }

# function from http://php.net/manual/es/function.strrpos.php#36548	
function cut_last_occurence($string,$cut_off) 
    {
        //   example: cut off the last occurence of "limit"
        #    $str = "select delta_limit1, delta_limit2, delta_limit3 from table limit 1,7";
        #    echo $str."\n";
        #    echo cut_last_occurence($str,"limit");
        return strrev(substr(strstr(strrev($string), strrev($cut_off)),strlen($cut_off)));
    }

# function from http://stackoverflow.com/a/2510468    
function formatBytes($size, $precision = 2)
{
    $base = log($size) / log(1024);
    $suffixes = array(' Bytes', ' KB', ' MB', ' GB', ' TB');   
    $result=round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    $result=($result>0) ? $result : "0 Bytes"; #NAN issue
    return $result;
}

# funtion from http://php.net/manual/es/function.time.php#109516
function time_elapsed_string($timestamp, $precision = 2) 
{ 
  $time = time() - $timestamp; 
  $a = array('decade' => 315576000, 'year' => 31557600, 'month' => 2629800, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'min' => 60, 'sec' => 1); 
  $i = 0; 
    foreach($a as $k => $v) { 
      $$k = floor($time/$v); 
      if ($$k) $i++; 
      $time = $i >= $precision ? 0 : $time - $$k * $v; 
      $s = $$k > 1 ? 's' : ''; 
      $$k = $$k ? $$k.' '.$k.$s.' ' : ''; 
      @$result .= $$k; 
    } 
  return $result ? $result.'ago' : '1 sec to go'; 
} 

# function from http://php.net/manual/en/session.security.php#86638
function check_timeout($max_idle_time)
{
    if (!isset($_SESSION['timeout_idle'])) {
        $_SESSION['timeout_idle'] = time() + $max_idle_time;
    } else {
        if ($_SESSION['timeout_idle'] < time()) {    
            //destroy session
            die (header("Location:?logout&timeout"));
        } else {
            $_SESSION['timeout_idle'] = time() + $max_idle_time;
        }
    }
}


function save_avatar($url)
{
   $dir='avatars/';
   $filename=basename($url);
    if (file_exists($dir.$filename)) {
        return $dir.$filename;
        } else {
        $avatar = file_get_contents($url); 
        $save_avatar= file_put_contents($dir.$filename,$avatar);
        chmod($dir.$filename, 0755);
        return $dir.$filename;
    }
}

?>