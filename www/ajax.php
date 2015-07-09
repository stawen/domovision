<?PHP
include('config.php');
include_once('_include/data.php');
include_once('_include/writeData.php');
include_once('_include/adminConf.php');
include_once('_include/adminCmd.php');

function is_ajax() {
 //return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
  return true;
}

if (is_ajax()) {
	$d = new data();
	$w = new writeData();
	$a = new adminConf();
	$c = new adminCmd();
	
	
	if (isset($_GET['action']) ){
	
		switch ($_GET['action']){
			case "etat":
				$d->getJsonState();
				break;
			case "getGraphe":
			    if (isset($_GET['jour']) && isset($_GET['id']) ){
			   	    $d->getDataForGraphe($_GET['id'],$_GET['jour']);
			   	}
			    break;
			case "setButton":
			    if (isset($_GET['grp']) && isset($_GET['value']) ){
				    $w->setButton($_GET['grp'],$_GET['value']);
			    }    
				break; 
			case "setSlider":
			    if (isset($_GET['grp']) && isset($_GET['value']) ){
				    $w->setSlider($_GET['grp'],$_GET['value']);
			    }    
				break; 
			case "admin":
				if (isset($_GET['script']) && isset($_GET['cmd']) ){
			        switch ($_GET['cmd']){
			     		case "isrun":
			     			$c->isRunning($_GET['script']);
			                break;
			            default:
			     			$c->process($_GET['script'],$_GET['cmd']);
			                break;
			        }
				}
			case "conf":
			    if (isset($_GET['data'])){
			        switch ($_GET['data']){
			            case "typeeqt":
			                $a->getTypeEqt();
			                break;
			            case "grpetat":
			                $a->getEqtGrpEtat();
			                break;
			            case "alldpt":
			                $a->getAllDpt();
			                break;
			            case "geteqt":
			                $a->getEqt();
			                break; 
			            case "getTypeAffichage":
			                $a->getTypeAffichage();
			                break;
			            case "saveeqt":
			                $a->setEqt($_POST);
			                break;
			            case "testeqexist":
			                if( isset($_GET['grpaddress']) ){
			                    $a->testEqExist($_GET['grpaddress']);
			                }
			                break;
			            case "updateeqt":
			                $a->updateEqt($_POST);
			                break;
			            case "deleteeqt":
			                $a->deleteEqt($_POST);
			                break;
			            case "testGrpexist":
			                if( isset($_GET['grp']) ){
			                    $a->testGrpActionExist($_GET['grp']);
			                }
			                break;  
			            case "addGrp":
			                $a->setGrpAction($_POST);
			                break; 
			            case "updateGrp":
			                $a->updateGrpAction($_POST);
			                break;
			            case "deleteGrp":
			                $a->deleteGrpAction($_POST);
			                break;
			            case "getGrpAction":
			            	$a->getGrpAction();
			            	break;
			            case "getGrpActionPosition":
			            	$a->getGrpActionPosition();
			            	break;
			            case "getLastGrpActionPosition":
			            	$a->getLastGrpActionPosition();
			            	break;
			            case "getEqtAction":
			            	$a->getEqtAction();
			            	break; 
			            case "testAssoExist":
			                if( isset($_GET['grp']) && isset($_GET['eqt']) ){
			                    $a->testAssoExist($_GET['grp'],$_GET['eqt'] );
			                }
			                break;
			            case "addAsso":
			                $a->setAsso($_POST);
			                break; 
			            case "updateAsso":
			                $a->updateAsso($_POST);
			                break; 
			            case "getAsso":
			                if( isset($_GET['grp'])){
			            	    $a->getAsso($_GET['grp']);
			                }
			            	break;
			            case "deleteAsso":
			                $a->deleteAsso($_POST);
			                break;
			            case "updateAsso":
			                $a->updateAsso($_POST);
			                break;
			            case "getLastGraphePosition":
			                $a->getLastGraphePosition();
			                break;
			            case "testGrapheExist":
			                if( isset($_GET['name']) ){
			                    $a->testGrapheExist($_GET['name']);
			                }
			                break;
			            case "addGraphe":
			                $a->addGraphe($_POST);
			                break;
			            case "getGraphe":
			                $a->getGraphe();
			                break;
			            case "updateGraphe":
			                $a->updateGraphe($_POST);
			                break;
			            case "deleteGraphe":
			                $a->deleteGraphe($_POST);
			                break;
			            case "getEqtEtat":
			                $a->getEqtEtat();
			                break;
			            case "testAssoGrapheExist":
			                if( isset($_GET['graphe']) && isset($_GET['eqt']) ){
			                    $a->testAssoGrapheExist($_GET['graphe'],$_GET['eqt']);
			                }
			                break;
			            case "addGrapheAsso":
			                $a->addGrapheAsso($_POST);
			                break;
			            case "getGrapheAsso":
			                if( isset($_GET['graphe'])){
			                    $a->getGrapheAsso($_GET['graphe']);
			                }
			                break;
			            case "updateGrapheAsso":
			                $a->updateGrapheAsso($_POST);
			                break;
			            case "deleteAssoGraphe":
			                $a->deleteAssoGraphe($_POST);
			                break;
			                
			            
			        } 
			    }    
			    break;
		}		
	}



}


?>