<?php

DEFINE("PATH_LOG",CONTEXT."/core/tmp/knx-trace.log");
DEFINE("PATH_JSON",CONTEXT."/core/tmp/domotrace.json");


// Connexion avec la base de données mysql

$MYSQLHOST      = getenv('IP'); //"localhost";
$MYSQLLOGIN     = getenv('C9_USER'); //"stawen";
$MYSQLPWD       = "";
$MYSQLBASE      = "domovision";
$MYSQLPORT      = 3306;

//$db = mysql_connect($MYSQLHOST, $MYSQLLOGIN, $MYSQLPWD);
//$mysql=mysql_select_db($MYSQLBASE,$db);
$db = new mysqli($MYSQLHOST, $MYSQLLOGIN, $MYSQLPWD, $MYSQLBASE, $MYSQLPORT, '~/lib/mysql/socket/mysql.sock') ;

 
if ($db->connect_errno > 0 ) {
  //System_Daemon::notice('La connection avec la base de données a échoué.');
  echo('La connection avec la base de données a échoué.');
  exit();
}
  //System_Daemon::notice('Connected successfully ('.$db->host_info.')');
  echo('Connected successfully ('.$db->host_info.')');


?>
