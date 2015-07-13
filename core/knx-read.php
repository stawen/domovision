#!/usr/bin/php -q
<?php
DEFINE('CONTEXT',dirname($_SERVER['SCRIPT_FILENAME']));

include CONTEXT.'/core/_includes/eib-functions.php';
 
    $groupaddr  = $argv[1];
    $dpt 	= $argv[2];
    if(count($argv) == 4){
    	$rendu=true;
    }else{
    	$rendu=false;
    }

    $hexa      	= exec('eibread -s 127.0.0.1 '.$groupaddr);
    $data   	= hexdec($hexa);
   
    if(!$rendu){
    	print(dptSelectDecode($dpt,$data)."\n");
    }else{
    	print(decodeState($dpt,$data)."\n");
    }

   
?>
