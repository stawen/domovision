#!/usr/bin/php -q
<?php


include '/opt/domovision/core/_includes/knx-function-daemon.php';
include '/opt/domovision/core/_includes/knx-config.php';
include '/opt/domovision/core/_includes/knx-function.php';
include '/opt/domovision/core/_includes/eib-functions.php';
 
System_Daemon::info("######    KNX Tracking -> Insert in db knxtrace.json each minute   #####");

/*
$sniffed[ $data['group_addr'] ]   = array(
                                            "id"        => data['id'],
   	                                        "name"      => $data['name'],
   	                                        "dpt"       => $data['dpt'],
   	                                        "value"     => "",
   	                                        "unite"     => getDptUnite($data['dpt'])
   	                                        );

*/

while (true) {

	sleep(60);
    
    $json   = file_get_contents(PATH_JSON); 
    $trace  = json_decode($json,true);
    
    $jour   = date('Y-m-d');
    $heure  = date('H:i:s');
    
    foreach($trace as $groupaddr => $info){
   

        $req_eq_inser      = "INSERT INTO knx_tracking (knx_equipement_id,jour,heure,value) VALUES ('".$info['id']."','".$jour."','".$heure."','".$info['value']."')";
		//System_Daemon::notice("Requete SQL -> ".$req_eq_inser);
		// Et on update la BDD
		mysql_query($req_eq_inser); 
    
    }

	
}