<?php

 
// --------------------------------------
// --            function tail         --
// --------------------------------------
 
// D'après http://php.net/manual/fr/function.inotify-init.php
 
function tail($file,&$pos) {
    // get the size of the file
    if(!$pos) $pos = filesize($file);
    // Open an inotify instance
    $fd = inotify_init();
    // Watch $file for changes.
    $watch_descriptor = inotify_add_watch($fd, $file, IN_ALL_EVENTS);
    // Loop forever (breaks are below)
    while (true) {
        // Read events (inotify_read is blocking!)
        $events = inotify_read($fd);
        // Loop though the events which occured
        foreach ($events as $event=>$evdetails) {
            // React on the event type
            switch (true) {
                // File was modified
                case ($evdetails['mask'] & IN_MODIFY):
                    // Stop watching $file for changes
                    inotify_rm_watch($fd, $watch_descriptor);
                    // Close the inotify instance
                    fclose($fd);
                    // open the file
                    $fp = fopen($file,'r');
                    if (!$fp) return false;
                    // seek to the last EOF position
                    fseek($fp,$pos);
                    // read until EOF
                    while (!feof($fp)) {
                        $buf .= fread($fp,8192);
                    }
                    // save the new EOF to $pos
                    $pos = ftell($fp); // (remember: $pos is called by reference)
                    // close the file pointer
                    fclose($fp);
                    // return the new data and leave the function
                    return $buf;
                    break;
                    // File was moved or deleted
                case ($evdetails['mask'] & IN_MOVE):
                case ($evdetails['mask'] & IN_MOVE_SELF):
                case ($evdetails['mask'] & IN_DELETE):
                case ($evdetails['mask'] & IN_DELETE_SELF):
                    // Stop watching $file for changes
                    inotify_rm_watch($fd, $watch_descriptor);
                    // Close the inotify instance
                    fclose($fd);
                    // Return a failure
                    return false;
                    break;
            }
        }
    }
}

function get_string_between($string, $start, $end){
    $string = " ".$string;
    $ini = strpos($string,$start);
    if ($ini == 0) return "";
    $ini += strlen($start);
    $len = strpos($string,$end,$ini) - $ini;
    return trim(substr($string,$ini,$len));
}


function makeJsonTrace($array){
    //creation du ficheir json trace
    $jsonFile = fopen(PATH_JSON, "w") or die("impossible de creer le fichier Json Trace");
    //on ouvre le fichier
    //on transforme le tableau en format json
    //on ecrit dans le fichier
    fwrite($jsonFile, json_encode($array, JSON_UNESCAPED_SLASHES) );
    //on ferme le fichier
    fclose($jsonFile);
    
}

?>
