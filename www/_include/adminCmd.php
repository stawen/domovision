<?PHP
include_once 'config.php';
include_once '_include/logger.class.php';
//include_once '/opt/domovision/core/_includes/eib-functions.php';


class adminCmd{
    
    private $log = null;
	private $result = null;
	private $D_SNIFFER  = 'knx-sniffer';
	private $D_TRACKING = 'knx-tracking';
	private $D_KNX      = 'knx-daemon';
	private $D_BUS      = 'knx-bus';
	private $D_TRACE    = 'knx-trace'; 
	
	private $phpDaemon  = array();
	private $listScript = array();
	
	public function __construct() {
		$this->log = new Logger();
		$this->phpDaemon = array(
		                        $this->D_SNIFFER,
		                        $this->D_TRACKING
		                        );
		
		 $this->listScript = array_merge ($this->phpDaemon,array(
		                                                         $this->D_KNX,
		                                                         $this->D_BUS,
		                                                         $this->D_TRACE
		                                                        )  
		                                  );
		  
	}
	

	
	private function sendResponse($r){
        
        $t['response'] = $r;
        
        header("Content-type: text/json");
		echo json_encode($t, JSON_NUMERIC_CHECK);
    }
    
    private function execCmd($script, $action){
        exec('sudo /opt/domovision/bin/daemon/'.$script.' '.$action.' > /dev/null &');
        $this->sendResponse(1);
    }
    
    private function testRun($fpid){
        $status = 0;
        
        if (file_exists($fpid)){
            $pid = file_get_contents($fpid);
            exec('ps '.$pid, $ProcessState);
            $status = (count($ProcessState) >= 2)?1:0;
        }
        
        $this->sendResponse($status);
    }
  
    private function canBeExecuted($script){
        
        if(!in_array($script,$this->listScript)){
    		$this->sendResponse(-1);
    		exit;
    	}
    }
    
    public function process($script, $action){
    	//test si le script est authorisé
    	//var_dump($this->listScript); exit;
    	$this->canBeExecuted($script);
    	$this->execCmd($script,$action);
    }
    
    public function isRunning($script){
         
        if(in_array($script, $this->phpDaemon) ){
            $this->testRun('/var/run/'.$script.'.php/'.$script.'.php.pid');
        }else{
            $this->testRun('/var/run/'.$script.'.pid');
        }
    }
 
/*
DAEMON=/opt/domovision/core/$NAME.php
PIDFILE=/var/run/$NAME.php/$NAME.php.pid

*/

}

?>