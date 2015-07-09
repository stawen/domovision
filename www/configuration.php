<?php
include('_templates/header.php');
include('_templates/menu.php');

?>   

    <div class="container theme-showcase" role="main">
        <div class="page-header">
            <h2> <small>Gestion des processus serveurs</small></h2>
        </div>
        <table  class="table table-hover">
            <thead>
              <tr>
                <th class="col-md-1">Etat</th>
                <th class="col-md-2">Processus</th>
                <th class="col-md-6">Descrption</th>
                <th class="col-md-2"></th>
              </tr>
            </thead>
            <tbody>
            	<tr>
	              	<td> <button id="knx-bus" type="button" class="btn btn-warning disabled"><span class="glyphicon glyphicon-refresh glyphicon-spin" aria-hidden="true"></span></button></td>
	              	<td>Knx bus</td>
	              	<td>Permet de se connecter sur la passerelle KNX (IP defini en bas de page)</td>
	              	<td></td>
              	</tr>
              	<tr>
	              	<td> <button id="knx-trace" type="button" class="btn btn-warning disabled" ><span class="glyphicon glyphicon-refresh glyphicon-spin" aria-hidden="true"></span></button></td>
	              	<td>Knx trace</td>
	              	<td>Genere un fichier de log en Ram du RPI permettant le traitement des informations transitants sur le bus KNX</td>
	              	<td></td>
              	</tr>
            	<tr>
	              	<td> <button id="knx-sniffer" type="button" class="btn btn-warning" ><span class="glyphicon glyphicon-refresh glyphicon-spin" aria-hidden="true"></span></button></td>
	              	<td>Knx Sniffer</td>
	              	<td>Ecoute seulement sur le bus knx les equipements definis dans DOMOVISION. Ce processus decra etre relancé si la liste des équipements est modifiée</td>
	              	<td></td>
              	</tr>
              	<tr>
	              	<td> <button id="knx-tracking" type="button" class="btn btn-warning" ><span class="glyphicon glyphicon-refresh glyphicon-spin" aria-hidden="true"></span></button></td>
	              	<td>Knx tracking</td>
	              	<td>Sauvegarde en base de données toutes les 60 secondes les equipements de type "etat"</td>
	              	<td></td>
              	</tr>
              	<tr>
	              	<td> <button  id="knx-daemon" type="button" class="btn btn-warning" ><span class="glyphicon glyphicon-refresh glyphicon-spin"></span></button></td>
	              	<td>Knx Daemon</td>
	              	<td>Daemon global. Il permet de gerer en même temps tous les damons ci-dessus</td>
	              	<td></td>
              	</tr>
            </tbody>
         </table>
         
         <hr>
         
        <form >
			<div class="form-inline" >
                <label for="recipient-name" class="control-label">Ip de la passerelle KNX:</label>
                            <input type="text" class="form-control " id="ipKnx" placeholder="xxx.xxx.xxx.xxx">
                <button id="submitIpKnx" type="button" class="btn btn-default"><span class="glyphicon glyphicon-floppy-disk" aria-hidden="true"></span> </button>
        	</div>
        	 
        </form>
    
<?php
include('_templates/footer.php');
?>
<!--appel des scripts personnels de la page -->
    <script src="js/configuration.js"></script>

    </body>
</html>    