#!/usr/bin/php -q
<?php

    $groupaddr  = $argv[1];
    $dpt 	= $argv[2];
    $cmd 	= $argv[3];
    
    $hexa      	= exec('eibcommand -s 127.0.0.1 '.$groupaddr.' '.$dpt.' '.$cmd)
   
?>