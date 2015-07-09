<?php
include('_templates/header.php');
include('_templates/menu.php');

?>   

    <div class="container theme-showcase" role="main">
        <div class="page-header">
            <h2> <small>Gestion des equipements Knx</small></h2>
        </div>
        <div class="col-md-9">
			<button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal_editeqt">
				<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Ajouter
			</button>
		</div>
		<div class="col-md-3" align="right">
			<button type="button" class="btn btn-xs btn-default">
				<span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
			</button>
		</div>
		<table id="listeeqt" class="table table-hover">
            <thead>
              <tr>
                <th class="col-md-1">Grp Add</th>
                <th class="col-md-3">Nom</th>
                <th class="col-md-1">Dpt</th>
                <th class="col-md-1">Type eqt</th>
                <th class="col-md-3">Groupe d'état</th>
                <th class="col-md-1">Affichage</th>
                <th class="col-md-1">Histo</th>
                <th class="col-md-1"></th>
              </tr>
            </thead>
            <tbody>
              
            </tbody>
         </table>

        <div class="modal fade" id="modal_editeqt" tabindex="-1" role="dialog" aria-labelledby="editeqtLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editeqtTitre"></h4>
              </div>
              <div class="modal-body">
                <div class="hidden">
                    <input type="text" id="typemodal">
                </div>
                <form>
                  <div class="form-group" id="divgrpaddress">
                    <label for="recipient-name" class="control-label">Group Address:</label>
                    <input type="text" class="form-control" id="grpaddress">
                  </div>
                  <div class="form-group" id="divname">
                    <label for="recipient-name" class="control-label">Nom :</label>
                    <input type="text" class="form-control" id="name">
                  </div>
                  <div class="form-group" id="divdpt">
                    <label for="message-text" class="control-label">Dpt :</label>
                    <select class="form-control" id="select_dpt"></select>
                  </div> 
                 <div class="form-group" id="divtypeeqt">
                    <label for="message-text" class="control-label">Type eqt :</label>
                    <select class="form-control" id="select_type_eqt"></select>
                  </div>
                  <div class="form-group" id="divgrpetat">
                    <label for="message-text" class="control-label">Groupe d'état :</label>
                    <select class="form-control" id="select_grpetat">
                    	<option value="0"></option>
                    </select>
                  </div>
                  <div class="form-group" id="divtypeAffichage">
                    <label for="message-text" class="control-label">Type Affichage :</label>
                    <select class="form-control" id="select_typeAffichage">
                    </select>
                  </div>
                  <div class="checkbox" id="divtracked">
                    <label>
                      <input type="checkbox" id="select_tracked"> Historiser (permet de le voir dans un graphique)
                    </label>
                  </div>
                </form>
              </div>
              
              <div class="modal-footer">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                    <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                </button>
                <button type="button" class="btn btn-default btn-sm">
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="deleteEqtLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                         <h4 class="modal-title" id="deleteTitre"></h4>
                         Si c'est un eqt d'etat : Toutes les données seront supprimées, même celles historisées (graphiques). 
                         Si un eqt d'action lui est associé, l'association sera supprimé !
                    </div>
                    <div class="hidden">
                        <input type="text" id="deleteGrpaddress">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-danger btn-ok" id="deleteEqtConfirm">Confirmer</button>
                    </div>
                </div>
            </div>
        </div>

	
<?php
include('_templates/footer.php');
?>
<!--appel des scripts personnels de la page -->
	<script src="js/gsteqt.js"></script>
    </body>
</html>