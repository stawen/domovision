<?php

DEFINE('CONTEXT',dirname($_SERVER['SCRIPT_FILENAME']));
include (CONTEXT.'/core/_includes/knx-config.php');

/* chemmin absolu de l'application */
DEFINE('ABS_PATH',CONTEXT.'/www');
/* activation mode debug */
//affiche les lignes de debug dans les logs
DEFINE('DEBUG', false);
//affiches les lignes de debug dans l'html
DEFINE('VIEW_DEBUG', false);


//PARAMETRE, ne pas toucher
DEFINE('LOGFILE',ABS_PATH.'/_logs/domovision.log');
DEFINE("PATH_SENDCMD",CONTEXT."/core/knx-sendcmd.php");


?>