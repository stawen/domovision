<?PHP
include_once 'config.php';
include_once '_include/logger.class.php';
include_once '/opt/domovision/core/_includes/eib-functions.php';

class writeData{
    
    private $log = null;
	private $result = null;
	private $filtre = null;

	
	public function __construct() {
		$this->log = new Logger();
	}
	
/*
Fonction d'execution de la requette sql
*/
	private function getSQL($query){
		
		$this->result =  mysql_query($query);
		
		$this->log->debug("Ajax | writeData.php | GetSQL - ".$query);
	}
	
	public function setButton($grp, $value){
	    
	    //On recupere le groupe address pour l'action
	    $req = "SELECT a.group_addr, a.dpt FROM knx_equipement a, knx_equipement b WHERE b.group_addr = '".$grp."' and a.grp_state = b.id";
	   
	    $this->getSQL($req);
		$data   = null;
	    $dptsrc = null;
	    
		while($r = mysql_fetch_row($this->result)) {
			  $groupaddr    = $r[0];
			  $dpt          = substr($r[1], 0, strpos($r[1], '.'));
			  $dptsrc       = $r[1];
			 // $dpt          = $tmp[0];
	    }
		
		mysql_free_result($this->result);
	   
	    $cmd = PATH_SENDCMD.' '.$groupaddr.' '.$dpt.' '.dptSelectEncode($dptsrc,$value) ;
	    //$cmd = PATH_SENDCMD.' '.$groupaddr.' '.$dpt.' '.$value ;
	    //$this->log->info($cmd);
	    
	    $trash      	= exec($cmd);
	    
	  
	}
	
	public function setSlider($groupaddr, $value){
	
	    //on recupere le dpt
	    $req = "SELECT dpt FROM knx_equipement WHERE group_addr = '".$groupaddr."'";
	    //$this->log->info($req);
	    $this->getSQL($req);
		$data   = null;
	    $dptsrc = null;
	    $dpt    = null;
	    
		while($r = mysql_fetch_row($this->result)) {
			  $dpt          = substr($r[0], 0, strpos($r[0], '.'));
			  $dptsrc       = $r[0];
	    }
        
        mysql_free_result($this->result);
	    
	    //on encode la donnée en fonction du dpt
	    //on envoi la commande sur le bus
	    $cmd = PATH_SENDCMD.' '.$groupaddr.' '.$dpt.' '.dptSelectEncode($dptsrc,$value) ;
	    //$this->log->info($cmd);
	    $trash      	= exec($cmd);
	    //$this->log->info($trash);
	}
	
}

?>