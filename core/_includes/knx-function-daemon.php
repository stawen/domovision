<?php
// --------------------------------------
// --       Daemonise PHP script       --
// --------------------------------------

  
$runmode = array(
    'no-daemon' => false,
    'help' => false,
    'write-initd' => false,
);
  
// Scan command line attributes for allowed arguments
foreach ($argv as $k=>$arg) {
    if (substr($arg, 0, 2) == '--' && isset($runmode[substr($arg, 2)])) {
        $runmode[substr($arg, 2)] = true;
    }
}
 
// Help mode. Shows allowed argumentents and quit directly
if ($runmode['help'] == true) {
    echo 'Usage: '.$argv[0].' [runmode]' . "\n";
    echo 'Available runmodes:' . "\n";
    foreach ($runmode as $runmod=>$val) {
        echo ' --'.$runmod . "\n";
    }
    die();
}
 
// Make it possible to test in source directory
// This is for PEAR developers only
ini_set('include_path', ini_get('include_path').':..');
 
// Include Class
error_reporting(E_STRICT);
require_once 'System/Daemon.php';
$nom = basename($_SERVER['PHP_SELF']);
// Setup
$options = array(
    'appName' => $nom,
    'appDir' => dirname(__FILE__),
    'appDescription' => 'Parses KNX logfiles and stores them in MySQL for INIT',
    'authorName' => 'Stan',
    'authorEmail' => '',
    'sysMaxExecutionTime' => '0',
    'sysMaxInputTime' => '0',
    'sysMemoryLimit' => '128M',
    'appRunAsGID' => 0,
    'appRunAsUID' => 0
);
 
System_Daemon::setOptions($options);
 
// This program can also be run in the forground with runmode --no-daemon
if (!$runmode['no-daemon']) {
    // Spawn Daemon
    System_Daemon::start();
}
 
// With the runmode --write-initd, this program can automatically write a
// system startup file called: 'init.d'
// This will make sure your daemon will be started on reboot
if (!$runmode['write-initd']) {
    System_Daemon::info('not writing an init.d script this time');
} else {
    if (($initd_location = System_Daemon::writeAutoRun()) === false) {
        System_Daemon::notice('unable to write init.d script');
    } else {
        System_Daemon::info(
            'sucessfully written startup script: %s',
            $initd_location
        );
    }
}

?>