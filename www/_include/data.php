<?PHP
include_once 'config.php';
include_once '_include/logger.class.php';

class data{ 

	private $log = null;
	private $result = null;
	private $filtre = null;

	
	public function __construct() {
		$this->log = new Logger();
	}
	
/*
fonction retournant le contenu du fichier /run/shm/domotrace.json
*/
	public function getJsonState(){
		header("Content-type: text/json");
		echo file_get_contents(PATH_JSON);
	}
	
/*
Fonction d'execution de la requette sql
*/
	private function getSQL($query){
		
		$this->result =  mysql_query($query);
		
		$this->log->debug("Ajax | data.php | GetSQL - ".$query);
	}
	

/****
Fonction pour recuperer et structurer toutes les data associées au timestamp
***/


	private function getDataWithTime($q){	
		$this->getSQL($q);
		$data = null;
	
		while($r = mysql_fetch_row($this->result)) {
			
			$date = new DateTime($r[0]." ".$r[1], new DateTimeZone('Europe/Paris'));
			$utc = ($date->getTimestamp() + $date->getOffset()) * 1000;	
			$data .= "[".$utc.",".$r[2]."],";
			
		}
		
		$data = substr($data,0,strlen($data)-1);
		mysql_free_result($this->result);
		
		return '['.$data.']';
	}

/*
Fonction de mise en forme Json
*/
	private function getJson4graphe($f,$jour){
		
	
		$resultat = "";
		
		foreach ($f as $label => $knx_equipement_id){
		    $req = "SELECT jour, DATE_FORMAT(heure,'%H:%i:%s'), value FROM knx_tracking "
			        ."INNER JOIN knx_equipement	ON knx_tracking.knx_equipement_id = knx_equipement.id WHERE "
			        ."jour ='".$jour."' and knx_tracking.knx_equipement_id = ".$knx_equipement_id;
			        //." LIMIT 0,10";
			        
			//$this->log->info($req);
			
			$resultat .= '{ "name": "'.$label.'",';
			$resultat .= '"data": '.$this->getDataWithTime($req);
			$resultat .= '},';
			
			
		}
		//on retire la derniere virgule qui ne sert à rien
		$resultat = substr($resultat,0,strlen($resultat)-1);
		
		header("Content-type: text/json");
		return '['.$resultat.']';
	}
	
/*
Fonction d'appel Ajax
*/
	public function getTracking($jour){
		
		$categorie = array( 'Hygrometrie Cuisine' => 1,
							'Temperature Cuisine' => 2,
							'ActiveModeDyn' => 3,
							'Luminosite' => 5
						);
		//echo gzdeflate($this->getJson4graphe($categorie,$jour));
		echo $this->getJson4graphe($categorie,$jour);
	}
	
	public function getPower($jour){
		
		$categorie = array( 'Puissance' => 4
						);
		echo $this->getJson4graphe($categorie,$jour);
	}
	
	
	
	public function getDataForGraphe($id_graphe,$jour){
	    //il faut remplir dynamiquement $categorie
	    $req = "SELECT name FROM knx_graphe WHERE id=".$id_graphe;
	    $this->getSQL($req);
	    $name = null;
	    
	    while($r = mysql_fetch_row($this->result)) {
	        $name = $r[0];
	    }
	    mysql_free_result($this->result);
	    
	    
	    $req = "select eqt.name, eqt.id from knx_asso_eq_graphe as asso ".
	            "LEFT JOIN knx_equipement as eqt ON eqt.id = asso.knx_equipement_id  ".
	            "WHERE asso.knx_graphe_id=".$id_graphe." ORDER BY asso.position";
	    //$this->log->info($req);
	    $this->getSQL($req);
	    $categorie = array();
	    
	    while($r = mysql_fetch_row($this->result)) {
	        //array_push($categorie, array($r[0] => $r[1]));
	        $categorie[$r[0]] = $r[1];
	    }
	    mysql_free_result($this->result);
	    
	    echo  '{ "grapheName": "'.$name
	    	  .'", "data": '.$this->getJson4graphe($categorie,$jour)
	    	  .'}';
	 	
	 	//echo $this->getJson4graphe($categorie,$jour);
	 
	 }
	

}

?>
