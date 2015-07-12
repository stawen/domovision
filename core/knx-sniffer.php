#!/usr/bin/php -q
<?php


include '/opt/domovision/core/_includes/knx-function-daemon.php';
include '/opt/domovision/core/_includes/knx-config.php';
include '/opt/domovision/core/_includes/knx-function.php';
include '/opt/domovision/core/_includes/eib-functions.php';


System_Daemon::info("######    KNX sniffer -> make and update knxtrace.json   #####");

/* 
Requette permettant dre lister les equipement d'etzt et non d'action
*/
$req_sel_eq     = "SELECT group_addr,name,dpt,id,is_track FROM knx_equipement where knx_type_equipement_id=2" ;

//System_Daemon::info("Requete SQL -> ".$req_sel_eq);
$knx_eq     = mysql_query($req_sel_eq) or die (mysql_error());

$sniffed = array();
$oldSniffedValue = array();
/*
Mise en forme des donnees dans un tableau pour les manipuler facilement
*/

while($data = mysql_fetch_array($knx_eq)) {
   
   	$sniffed[ $data['group_addr'] ]   = array(
   	                                        "id"        => $data['id'],
   	                                        "name"      => $data['name'],
   	                                        "dpt"       => $data['dpt'],
   	                                        "value"     => "",
   	                                        "unite"     => getDptUnite($data['dpt']),
   	                                        "is_track"	=> $data['is_track']
   	                                        );

} 

/*
Initialisation du fichier knxtrace.json
*/
 System_Daemon::info("Init :: Recuperation des valeurs dans les groupes d'ecoute");

foreach($sniffed as $grpaddr => $value){
   
    $cmd = 'eibread -s 127.0.0.1 '.$grpaddr;
    // System_Daemon::info($cmd);
    $hexa      	= exec($cmd);
    $dec       	= hexdec($hexa);
    $sniffed[$grpaddr]['value'] = dptSelectDecode($sniffed[$grpaddr]['dpt'],$dec);     
    
    $oldSniffedValue[$grpaddr] = $sniffed[$grpaddr]['value'];
    
    if ( $sniffed[$grpaddr]['is_track'] == 1){
	        	//Inseetion en base de donnÃ©es car l'eqt is_track=true
	        	//System_Daemon::notice("SAVE");
	        	$jour   = date('Y-m-d');
				//$heure  = date('H:i:s');
				$time = time();//time stamp is in seconds, so now -1 would be the current date minus 1
                //$heureOld  = date('H:i:s', $time - 1);
                $heure = date("H:i:s", $time);
				
	        	$req_eq_inser      = "INSERT INTO knx_tracking (knx_equipement_id,jour,heure,value) VALUES ('".$sniffed[$grpaddr]['id']."','".$jour."','".$heure."','".$sniffed[$grpaddr]['value']."')";
				//System_Daemon::notice("Requete SQL -> ".$req_eq_inser);
				// Et on update la BDD
				mysql_query($req_eq_inser); 
				//System_Daemon::notice("OK");
        	}
    
    //System_Daemon::info("GroupAddr : ".$grpaddr." | hexa : ".$hexa." - value :".$sniffed[$grpaddr]['value'] );
}


//on ecrit les données dans le fichier json trace
System_Daemon::info("Initialisation du fichier knxtrace.json");
makeJsonTrace($sniffed);

/*
Mise a jour du ficheir knxtrace.json a chaque modification d'un equipement suivi
*/ 
System_Daemon::info("Mise a jour du fichier knxtrace.json a chaque modification d'un equipement suivi et sauvegarde en base s'il doit etre historisé");
//$lastpos = 0;
while (true) {
    // On tail le fichier de log
    $knxlisten = tail(PATH_LOG,$lastpos);

    // On r�agit d�s qu'on a un Write
    // Pour chaque ligne, on r�cup�re le Groupe d'Addresse et de la valeur qu'on converti
	$groupaddr   = get_string_between($knxlisten,'group addr: ',' -');
	$hexa        = get_string_between($knxlisten,'Hexa: ',' -');
    
    //on met a jour le valeur dans le tableau et on regenere le fichier json
    //recursive_array_search
    if (array_key_exists($groupaddr, $sniffed)) {
        
        $decimal	= hexdec($hexa);
        $value	    = dptSelectDecode($sniffed[$groupaddr]['dpt'],$decimal);    
        
        //ecriture en base si changement d'etat et bas simplement l'equipement qui redis la meme chose sur le bus
        //System_Daemon::notice("Old Value -> ".$groupaddr.":: ".$oldSniffedValue[$groupaddr]);
        //System_Daemon::notice("New Value -> ".$groupaddr.":: ".$value);
        if ( $oldSniffedValue[$groupaddr] != $value ){
        	//System_Daemon::notice("MAJ");
        	$sniffed[$groupaddr]['value']=$value;
        	makeJsonTrace($sniffed);
        	
        	//System_Daemon::notice("TRACKED::".$sniffed[$groupaddr]['is_track']);
        	if ( $sniffed[$groupaddr]['is_track'] == 1){
	        	//Inseetion en base de données car l'eqt is_track=true
	        	//System_Daemon::notice("SAVE");
	        	$jour   = date('Y-m-d');
				//$heure  = date('H:i:s');
				$time = time();//time stamp is in seconds, so now -1 would be the current date minus 1
                $heureOld  = date('H:i:s', $time - 1);
                $heure = date("H:i:s", $time);
				
	        	$req_eq_inser      = "INSERT INTO knx_tracking (knx_equipement_id,jour,heure,value) VALUES ('".$sniffed[$groupaddr]['id']."','".$jour."','".$heureOld."','".$oldSniffedValue[$groupaddr]."'),"
	        	                                                                                      ."('".$sniffed[$groupaddr]['id']."','".$jour."','".$heure."','".$value."')";
				//System_Daemon::notice("Requete SQL -> ".$req_eq_inser);
				// Et on update la BDD
				mysql_query($req_eq_inser); 
				//System_Daemon::notice("OK");
        	}
        	$oldSniffedValue[$groupaddr] = $value;
        }
        
        
        
		//System_Daemon::info("GroupAddr : ".$groupaddr." | name : ".$sniffed[$groupaddr]['name']." | Value : ".$value." ".$sniffed[$groupaddr]['unite'] );
		
	}
}



