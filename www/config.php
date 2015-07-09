<?php

include ('/opt/domovision/core/_includes/knx-config.php');

/* chemmin absolu de l'application */
DEFINE('ABS_PATH','/opt/domovision/www/');
/* activation mode debug */
//affiche les lignes de debug dans les logs
DEFINE('DEBUG', false);
//affiches les lignes de debug dans l'html
DEFINE('VIEW_DEBUG', false);


//PARAMETRE, ne pas toucher
DEFINE('LOGFILE',ABS_PATH.'_logs/domovision.log');
DEFINE("PATH_SENDCMD","/opt/domovision/core/knx-sendcmd.php");


?>