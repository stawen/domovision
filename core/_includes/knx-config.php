<?php

DEFINE("PATH_LOG",CONTEXT."/core/tmp/knx-trace.log");
DEFINE("PATH_JSON",CONTEXT."/core/tmp/domotrace.json");


// Connexion avec la base de données mysql

$MYSQLHOST      = "localhost";
$MYSQLLOGIN     = "root";
$MYSQLPWD       = "";
$MYSQLBASE      = "domovision";

$db = mysql_connect($MYSQLHOST, $MYSQLLOGIN, $MYSQLPWD);
$mysql=mysql_select_db($MYSQLBASE,$db);
 
if ($mysql!=1) {
  System_Daemon::notice('La connection avec la base de données a échoué.');
  exit();
}


?>
