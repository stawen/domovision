<?PHP
include_once 'config.php';
include_once '_include/logger.class.php';
include_once '/opt/domovision/core/_includes/eib-functions.php';


class adminConf{
    
    private $log = null;
	private $result = null;


	
	public function __construct() {
		$this->log = new Logger();
	}
	
	/*
Fonction d'execution de la requette sql
*/
	private function getSQL($query){
		
		$this->result =  mysql_query($query);
		
		$this->log->debug("Ajax | adminConf.php | GetSQL - ".$query);
	}
	
	private function sendResponse($req){
        
        $this->getSQL($req);
        
        $t['response'] = $this->result;
        
        header("Content-type: text/json");
		echo json_encode($t, JSON_NUMERIC_CHECK);
    }
 
    private function sendData($data){
        mysql_free_result($this->result);
		header("Content-type: text/json");
		echo json_encode($data, JSON_NUMERIC_CHECK);
    }

/*********************************************
 * *** GESTION EQUIPEMENT  *******************
 * ******************************************/
	public function getTypeEqt(){
	    
	    $req = "SELECT id, name FROM knx_type_equipement";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			$data[$r[0]] = $r[1];
		}
		$this->sendData($data);
	}
	
	
	public function getEqtGrpEtat(){
	    
	    $req = "SELECT id, group_addr, name FROM knx_equipement where knx_type_equipement_id=2 order by group_addr";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			$data[$r[0]] = $r[1].' - '.$r[2];
		}
		$this->sendData($data);
	}

    public function getAllDpt(){
        header("Content-type: text/json");
		echo json_encode(All_DPT(), JSON_NUMERIC_CHECK);
    }
    
    public function getEqt(){
	    
	    $req = "SELECT a.id, a.group_addr, a.dpt, a.name, knx_type_equipement.name, b.group_addr, b.name, c.type, a.is_track FROM knx_equipement as a ".
                "LEFT JOIN knx_type_equipement ON knx_type_equipement.id = a.knx_type_equipement_id ".
                "LEFT JOIN knx_equipement as b ON b.id = a.grp_state ".
                "LEFT JOIN knx_eqt_affichage as c ON a.knx_eqt_affichage_id = c.id ".
                "order by a.group_addr";
                
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "group_addr" => $r[1],
			                         "dpt"        => $r[2],
			                         "name"       => $r[3],
			                         "typeeqt"    => $r[4],
			                         "grpetat"    => $r[5].' - '.$r[6],
			                         "typeAffichage"  => $r[7],
			                         "is_track"     => $r[8]
			                     )
			         );
		}
		
		$this->sendData($data);
	}
    
    public function setEqt($s){
        
        
        if($s['typeeqt']==1){
        	$req = "INSERT INTO knx_equipement (group_addr, dpt, name, knx_type_equipement_id, grp_state,knx_eqt_affichage_id) ".
        	"values('".$s['grpaddress']."','".$s['dpt']."','".$s['name']."',".$s['typeeqt'].",".$s['grpetat'].",".$s['type'].")";
        }else{
        	$req = "INSERT INTO knx_equipement (group_addr, dpt, name, knx_type_equipement_id,knx_eqt_affichage_id,is_track) ".
        	"values('".$s['grpaddress']."','".$s['dpt']."','".$s['name']."',".$s['typeeqt'].",".$s['type'].",".$s['is_track'].")";
        }
        
        $this->sendResponse($req);
		
    }
    
    public function testEqExist($grpaddress){
    	$req = "SELECT COUNT(*) FROM knx_equipement where group_addr='".$grpaddress."'";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			$data['eqExist'] = $r[0];
		}
		$this->sendData($data);
    }
    
    public function updateEqt($s){
        if($s['typeeqt']==1){
            $req = "UPDATE knx_equipement set dpt='".$s['dpt']."', name='".$s['name']."',knx_type_equipement_id=".$s['typeeqt'].",knx_eqt_affichage_id=".$s['type']." ,grp_state=".$s['grpetat']." ";
        }else{
            $req = "UPDATE knx_equipement set dpt='".$s['dpt']."', name='".$s['name']."',knx_type_equipement_id=".$s['typeeqt'].",knx_eqt_affichage_id=".$s['type'].",is_track=".$s['is_track']." "; 
        }
        
        $req .= "WHERE group_addr ='".$s['grpaddress']."'";
        
        $this->sendResponse($req);
        
    }
    
    public function deleteEqt($s){
        //$req = "DELETE FROM knx_equipement WHERE group_addr ='".$s['grpaddress']."'";
        $req = "CALL deleteEqt('".$s['grpaddress']."')";
        $this->sendResponse($req);
    }
 
 /*********************************************
 * *** GESTION GROUPE ACTION  ****************
 * ******************************************/
 
    public function getGrpAction(){
        $req = "SELECT id, name, position FROM knx_groupe_action ORDER BY position,name";
                
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "id"       => $r[0],
			                         "name"     => $r[1],
			                         "position" => $r[2]
			                         )
			         );
		}
		
		$this->sendData($data);
    }


 
    public function testGrpActionExist($name){
        $req = "SELECT COUNT(*) FROM knx_groupe_action where name='".$name."'";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			$data['eqExist'] = $r[0];
		}
		$this->sendData($data);
    }
    
    public function getGrpActionPosition(){
        //$req = "SELECT position FROM knx_groupe_action where name <> '".$name."' ORDER BY position";
        $req = "SELECT position, name FROM knx_groupe_action ORDER BY position";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "position" => $r[0],
			                         "name"     => $r[1]
			                         )
			         );
		}
		$this->sendData($data);
    }
    
    public function getLastGrpActionPosition(){
        $req = "SELECT max(position) FROM knx_groupe_action";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "position" => $r[0])
			         );
		}
		$this->sendData($data);
    }
    
    public function setGrpAction($s){
        $req = "INSERT INTO knx_groupe_action (name, position) values('".$s['name']."',".$s['position'].")";
        $this->sendResponse($req);
    }
    
    public function updateGrpAction($s){
        $req = "UPDATE knx_groupe_action set name='".$s['name']."' WHERE name='".$s['refName']."'";
        $this->sendResponse($req);
    }
    
    public function deleteGrpAction($s){
        //prostoc : supprime le groupe et l'association eqt<->grpaction
        $req = "CALL deleteGrpAction('".$s['name']."')";
        $this->sendResponse($req);
    }
    
    public function getEqtAction(){
	    
	    $req = "SELECT id, group_addr, name FROM knx_equipement where knx_type_equipement_id=1 order by group_addr";
                
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "id"        => $r[0],
			                         "group_addr" => $r[1],
			                         "name"       => $r[2]
			                    )
			         );
		}
		
		$this->sendData($data);
	}
 
 /*********************************************
 * *** GESTION ASSO EQT GROUPE d'action *******
 * ******************************************/
 
    public function testAssoExist($id_groupe, $id_eqt){
        $req = "SELECT COUNT(*) FROM knx_asso_eq_grpaction where knx_groupe_action_id=".$id_groupe." AND knx_equipement_id=".$id_eqt;
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			$data['AssoExist'] = $r[0];
		}
		$this->sendData($data);
    }
    
    
    public function setAsso($s){
       $req = "INSERT INTO knx_asso_eq_grpaction (knx_groupe_action_id, knx_equipement_id, position) ".
        	"values(".$s['id_grp'].",".$s['id_eqt'].",".$s['position'].")";
        
        $this->sendResponse($req);
		
    }
    
    public function getAsso($id_grp_filtre){
        $req = "SELECT eqt.group_addr, eqt.name, etat.group_addr, etat.name, eqt.knx_eqt_affichage_id from knx_asso_eq_grpaction  ".
                "LEFT JOIN knx_groupe_action as groupe ON groupe.id = knx_asso_eq_grpaction.knx_groupe_action_id ".
                "LEFT JOIN knx_equipement as eqt ON eqt.id = knx_asso_eq_grpaction.knx_equipement_id ".
                "LEFT JOIN knx_equipement as etat ON eqt.grp_state = etat.id ".
                "WHERE knx_asso_eq_grpaction.knx_groupe_action_id =".$id_grp_filtre." ".
                "ORDER by groupe.position, eqt.name";
                
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "group_addr"   => $r[0],
			                         "name"         => $r[1],
			                         "grpetat"      => $r[2].' - '.$r[3],
			                         "etat_group_addr" => $r[2],
			                         "etat_name"    => $r[3],
			                         "aff_type"     => $r[4]
			                     )
			         );
		}
		
		$this->sendData($data);
    }
    
    public function deleteAsso($s){
        $req = "DELETE knx_asso_eq_grpaction FROM knx_asso_eq_grpaction ".
                "LEFT JOIN knx_equipement ON knx_equipement.id = knx_asso_eq_grpaction.knx_equipement_id ".
                "WHERE knx_asso_eq_grpaction.knx_groupe_action_id = ".$s['grp']." ".
                "AND knx_equipement.group_addr = '".$s['eqt']."'";
                
        $this->sendResponse($req);
    }
    
    public function updateAsso($s){
        
    }

/*********************************************
 * *** GESTION Rendu graphique d'un equipement
 * ******************************************/    
    public function getTypeAffichage(){
        $req = "SELECT id, type FROM knx_eqt_affichage ORDER BY type";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "id"   => $r[0],
			                         "type" => $r[1]
			                         )
			         );
		}
		$this->sendData($data);
    }
    
/*********************************************
 * *** GESTION des Graphes  *******************
 * ******************************************/

	public function getLastGraphePosition(){
		$req = "SELECT max(position) FROM knx_graphe";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "position" => $r[0])
			         );
		}
		$this->sendData($data);
	}

	public function testGrapheExist($name){
		$req = "SELECT COUNT(*) FROM knx_graphe where name='".$name."'";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			$data['exist'] = $r[0];
		}
		$this->sendData($data);
	}
	
	public function addGraphe($s){
		$req = "INSERT INTO knx_graphe (name, position) values('".$s['name']."',".$s['position'].")";
        $this->sendResponse($req);
	}
	
	public function getGraphe(){
		$req = "SELECT id, name, position FROM knx_graphe ORDER BY position,name";
                
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "id"       => $r[0],
			                         "name"     => $r[1],
			                         "position" => $r[2]
			                         )
			         );
		}
		
		$this->sendData($data);
	}
	
	public function updateGraphe($s){
		$req = "UPDATE knx_graphe set name='".$s['name']."' WHERE name='".$s['refName']."'";
        $this->sendResponse($req);
	}
	
	public function deleteGraphe($s){
	   // $req = "DELETE FROM knx_graphe WHERE name='".$s['name']."'";
	    $req = "CALL deleteGraphe('".$s['name']."')";
        $this->sendResponse($req);
	}
	
	public function getEqtEtat(){
	    
	    $req = "SELECT id, group_addr, name FROM knx_equipement where knx_type_equipement_id=2 AND is_track=1 order by group_addr";
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "id"       => $r[0],
			                         "group_addr"     => $r[1],
			                         "name" => $r[2]
			                         )
			         );
		}
		$this->sendData($data);
	}
	
	public function testAssoGrapheExist($id_graphe,$id_eqt){
		$req = "SELECT COUNT(*) FROM knx_asso_eq_graphe where knx_graphe_id=".$id_graphe." AND knx_equipement_id=".$id_eqt;
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			$data['exist'] = $r[0];
		}
		$this->sendData($data);
	}
	
	public function addGrapheAsso($s){
		$req = "INSERT INTO knx_asso_eq_graphe (knx_graphe_id, knx_equipement_id, position) ".
        	"values(".$s['id_graphe'].",".$s['id_eqt'].",".$s['position'].")";
        
        $this->sendResponse($req);
	}
	
	public function getGrapheAsso($id_graphe_filtre){
		$req = "SELECT eqt.group_addr, eqt.name from knx_asso_eq_graphe  ".
                "LEFT JOIN knx_graphe as graphe ON graphe.id = knx_asso_eq_graphe.knx_graphe_id ".
                "LEFT JOIN knx_equipement as eqt ON eqt.id = knx_asso_eq_graphe.knx_equipement_id ".
                "WHERE knx_asso_eq_graphe.knx_graphe_id =".$id_graphe_filtre." ".
                "ORDER by eqt.group_addr, knx_asso_eq_graphe.position";
                
	    $this->getSQL($req);
		$data = array();
		
		while($r = mysql_fetch_row($this->result)) {
			array_push($data, array( "group_addr"   => $r[0],
			                         "name"         => $r[1]
			                     )
			         );
		}
		
		$this->sendData($data);
	}
	
	public function updateGrapheAsso($s){
	    //$req = "UPDATE knx_graphe set name='".$s['name']."' WHERE name='".$s['refName']."'";
        //$this->sendResponse($req);
	}
	
	public function deleteAssoGraphe($s){
	    $req = "DELETE knx_asso_eq_graphe FROM knx_asso_eq_graphe ".
                "LEFT JOIN knx_equipement ON knx_equipement.id = knx_asso_eq_graphe.knx_equipement_id ".
                "WHERE knx_asso_eq_graphe.knx_graphe_id = ".$s['id_graphe']." ".
                "AND knx_equipement.group_addr = '".$s['eqt']."'";
                
        $this->sendResponse($req);
	}

    
}

?>