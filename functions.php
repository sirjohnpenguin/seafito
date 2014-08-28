<?php
# Seafito version 0.1
# Seafile nasty frontend
# php-curl required

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

function cut_last_occurence($string,$cut_off) 
	{
		//   example: cut off the last occurence of "limit"
		#    $str = "select delta_limit1, delta_limit2, delta_limit3 from table limit 1,7";
		#    echo $str."\n";
		#    echo cut_last_occurence($str,"limit");
		return strrev(substr(strstr(strrev($string), strrev($cut_off)),strlen($cut_off)));
	}
    
function formatBytes($size, $precision = 2)
{
	$base = log($size) / log(1024);
    $suffixes = array(' Bytes', ' KB', ' MB', ' GB', ' TB');   
	$result=round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
	$result=($result>0) ? $result : "0 Bytes";
	return $result;
}

function replace_between($str, $needle_start, $needle_end, $replacement) 
{
    //replace between tags ej:
	#$string = "<tag>i dont know what is here</tag>";
	#echo replace_between($string, '<tag>', '</tag>', 'replacement');
	$pos = strpos($str, $needle_start);
    $start = $pos === false ? 0 : $pos + strlen($needle_start);

    $pos = strpos($str, $needle_end, $start);
    $end = $start === false ? strlen($str) : $pos;
 
    return substr_replace($str,$replacement,  $start, $end - $start);
}

function time_elapsed_string($datetime, $full = false)
{
    // last update in seconds,minutes, hours,days,etc. ej:
	// echo time_elapsed_string('2013-05-01 00:22:35');
	// echo time_elapsed_string('@1367367755'); # timestamp input
	// echo time_elapsed_string('2013-05-01 00:22:35', true);
	$now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>