<?php 

if (!function_exists('isNikken')) {
    function isNikken(){
        $result = strpos($_SERVER['HTTP_HOST'], 'nikken');
        if(gettype($result) === 'integer'){
            $result = true;
        }
        return true;
    }
}

if (!function_exists('isSCTP')) {
    function isSCTP(){
        $result = strpos($_SERVER['HTTP_HOST'], 'sctp');
        if(gettype($result) === 'integer'){
            $result = true;
        }
        return $result;
    }
}

if (!function_exists('isLaCentral')) {
    function isLaCentral(){
        $result = strpos($_SERVER['HTTP_HOST'], 'lacentral');
        if(gettype($result) === 'integer'){
            $result = true;
        }
        return $result;
    }
}
?>