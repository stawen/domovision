<?php

/*
Pense bete :

faire un menu de configuration pour :
    - faire le rapprochement entre les group addr dÃ©tectÃ© pour leur donner un nom, definir le dpt, definir leur type et si c'est une grpaddr d'action lui dire qui est le recepteur
    - definir les graphique qui doivent etre dans la page graphique :
        - 1 ou plusieurs graphiques pour une meme journÃ©e
        - les informations Ã  remontÃ©e
    - Pouvoir definir les icone a mettre pour les actions
    



*/



//include '/opt/domovision/core/_includes/knx-function-daemon.php';
include '/opt/domovision/core/_includes/knx-config.php';
//include '/opt/domovision/core/_includes/knx-function.php';
include '/opt/domovision/core/_includes/eib-functions.php';
/*
* 1.001: switching (on/off) (EIS1)
* 3.007: dimming (control of dimmer using up/down/stop) (EIS2)
* 5.xxx: 8bit unsigned integer (from 0 to 255) (EIS6)
* 6.xxx: 8bit signed integer (EIS14)
* 7.xxx: 16bit unsigned integer (EIS10)
* 9.xxx: 16 bit floating point number (EIS5)
* 10.001: time (EIS3)
* 11.001: date (EIS4)
* 12.xxx: 32bit unsigned integer (EIS11)
* 16.000: string (max 14 ASCII char) (EIS15)

169.254.193.159 : IP de ma passerelle KNX/IP ( ex chez moi c'est ABB IPS/S 2.1 ) 
3671 : le port de la passerelle KNX/IP

c'est un peu "brut" comme descriptions, mais ca devrait d'aider : 


wget "http://downloads.sourceforge.net/project/bcusdk/bcusdk/bcusdk_0.0.5.tar.gz?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fbcusdk%2F&ts=1334692790&use_mirror=freefr" -O bcusdk_0.0.5.tar.gz
tar -zxvf bcusdk_0.0.5.tar.gz
cd bcusdk-0.0.5/
export LD_LIBRARY_PATH=/usr/local/lib
./configure --enable-onlyeibd --enable-eibnetiptunnel --enable-usb --enable-eibnetipserver --enable-ft12
adduser --disabled-password eibd
su eibd


Pour lancer en mode routeur :
eibd --trace=1 -T -R -S -D --listen-tcp=3671 -u ipt:169.254.193.159:3671

Mode deamon :
eibd --daemon=/var/log/eibd.log --pid-file=/tmp/eibd.pid --trace=1 -T -R -D -S --listen-tcp=3671 ipt:169.254.193.159:3671


exemple : send "1" = "on" to the GA 1/1/1 with EIB Datapoint type is 1.001 :
groupswrite ip:127.0.0.1:3671 1/1/1 1

*/
//var_dump(All_DPT());
//

$time = time();//time stamp is in seconds, so now -1 would be the current date minus 1
$heureold  = date('H:i:s', $time - 1);
$now = date("H:i:s", $time);

echo "::::test time :::<br>";
print_r($heureold);
echo "<br>";
print_r($now);
echo "<br>";


/* 
test si equipement dÃ©ja connu doit etre suivi
*/
$req_sel_eq     = "SELECT group_addr,name,dpt FROM knx_equipement where knx_type_equipement_id=2" ;

//System_Daemon::info("Requete SQL -> ".$req_sel_eq);
$knx_eq     = mysql_query($req_sel_eq) or die (mysql_error());

$sniffed = array();
/*
Mise en forme des donnÃ©es dans un tableau pour les manipuler facilement
*/
//System_Daemon::notice("A");
while($data = mysql_fetch_array($knx_eq)) {
   	$sniffed[ $data['group_addr'] ]   = array(
   	                                        "name"      => $data['name'],
   	                                        "dpt"       => $data['dpt'],
   	                                        "value"     => "",
   	                                        "unite"     => getDptUnite($data['dpt'])
   	                                        );
} 

var_dump($sniffed);

var_dump( json_encode($sniffed, JSON_UNESCAPED_SLASHES));




var_dump( decodeState('9.017', 1447));

echo ":::::::<br>";
echo "<br>";
/* DPT 1 */
echo 'DPT  1.xxx - OK -';
	//$data = 0x0; //-> bin : 0 -> Off
	$data = 0x80; //-> bin 1 -> on
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	$res = $data;
	var_dump($res);
	echo dptSelectDecode('1.000', $data);
/* DPT 2 */
echo 'DPT  2.xxx - OK -'; // control type
	//$data = 0x2; //-> bin : 10 -> Off
	$data = 0x3; //-> bin 01 -> on
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	//si control = 0 on garde la sortie a 0
	$res = 0x0;
	if(($data >> 1)){ // si le control est a 1, on met en sortie la valeur du premier bit
		$res = $data & 0x1;
	}
	var_dump($res);

/* DPT 3 */
echo 'DPT  3.xxx - OK -';
//$data = 0x8A24; //-> 35364 -> -30°c
	$data = 0xB; //-> 5364 -> 50,72
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	$ctrl     = ($data & 0x08) >> 3;
	$stepCode = $data & 0x07;
	
	$res = Array(
				$ctrl,
				pow(2, $stepCode - 1)
				);
	
	
	
	var_dump($res);
	
/* DPT 4 */
echo 'DPT  4.xxx - OK -';
	$data = 0x55; //-> U en char

	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	$res = chr($data);	
	var_dump($res);
/* DPT 5 */
echo 'DPT  5.001 - OK -';
	$data = 0xC0; //-> 192 -> 75%
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	$res = round((intval($data) * 100) / 255);
	var_dump($res);
echo 'DPT  5.003 - OK -';
	$data = 0x80; //-> 128 -> 180°
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	$res = round((intval($data) * 360) / 255);
	var_dump($res);	
echo 'DPT  5.004 - OK -';
	$data = 0x64; //->  -> 100%
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	$res = round(intval($data));
	var_dump($res);	
echo 'DPT  5.010 - OK -';
	$data = 0x32; //->  -> 50
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	$res = $data;
	var_dump($res);	
	
/* DPT 6 */
echo 'DPT  6.xxx - OK -';
	$data = 0x92; //-> 146 -> -110
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	if ($data >= 0x80)
        $res = -(($data - 1) ^ 0xff); # invert twos complement
    else
        $res = $data;
	
	var_dump($res);
/* DPT 7 */
echo 'DPT  7.xxx - OK -';
	$data = 0xF494; //-> 62612
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	$res = $data;
	var_dump($res);
/* DPT 8 */
	echo 'DPT  8.001 - OK -';
	$data = 0xF494; // -2924
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	if ($data >= 0x8000){
        $data = -(($data - 1) ^ 0xffff); # invert twos complement
    }
    var_dump($data);

	echo 'DPT  8.010 - OK -';
	$data = 0xF494; // -2924
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	if ($data >= 0x8000){
        $data = -(($data - 1) ^ 0xffff) ; # invert twos complement
    }
    var_dump($data /100);


/* DPT 9 */
	echo 'DPT  9.xxx - OK -';
	//$data = 0x8A24; //-> 35364 -> -30°c
	$data = 0x1447; //-> 5364 -> 50,72
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;

	$sign     = 1;
	$exponent = ($data >> 11) & 0xF;
	$mantissa = ($data & 0x7FF);
	// bit = 0 positif et bit = 1 negatif
	if ($data >> 15) {
		//$data = ~ $data;
		$mantissa = $mantissa ^ 0x7FF;
		$sign     = -1;
	}
	$res = $sign * $mantissa * 0.01 * pow(2, $exponent);

	var_dump($res);
	
	//echo " - ".dptSelectDecode('9.xxx', $data);

/* DPT 10 */
echo 'DPT 10 -';
//$data = 0x8A24; //-> 35364 -> -30°c
//$data = 0x14F4; //-> 5364 -> 50,72
echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
var_dump($res);
/* DPT 11 */
echo 'DPT 11 -';
//$data = 0x8A24; //-> 35364 -> -30°c
//$data = 0x14F4; //-> 5364 -> 50,72
echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
var_dump($res);


/* DPT 14 */
echo 'DPT 14 - OK -';
$data = 0x44200000; //  640 W
//$data = 0xC4200000;
echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;

//$sign = ($data >> 31);
$exp = ($data >> 23 & 0xFF) - 127;
$mantissa = ($data & ((1 << 23) - 1)) + (1 << 23) * ($data >> 31 | 1);

$res = $mantissa * pow(2, $exp - 23);
var_dump($res);

/* DPT 17 */
echo 'DPT  17.xxx - OK -';

	$data = 0xBC; //-> ctrl = 1 et scene = 60
	echo ' Decode : ' . $data . " - binaire : ".decbin($data)." - " ;
	$ctrl     = $data >> 7;
	$scene_number = $data & 0x3F;
	
	$res = Array(
				$ctrl,
				$scene_number
				);
	
	
	
	var_dump($res);



	

?>