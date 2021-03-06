<?php 
include( '_templates/header.php'); 
include( '_templates/menu.php'); 

?>

<div class="container theme-showcase" role="main">
    <div class="page-header">
        <h3> <small>Nom des graphiques Journaliers :</small></h3>
    </div>

    <div>
        <button type="button" class="btn btn-xs btn-default" id="openModalAddGraphique" data-toggle="modal" data-target="#modal_graphique">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Ajouter
        </button>
    </div>

    <table id="listeGraphique" class="table table-hover">
        <thead>
            <tr>
                <th class="col-md-2">Position</th>
                <th class="col-md-8">Nom</th>
                <th class="col-md-2"></th>
            </tr>
        </thead>

        <tbody>
        </tbody>

    </table>

    <p>&nbsp;</p>
    <div class="page-header">
        <h3> <small>Affectation Indicateurs - Graphiques :</small></h3>
    </div>
    <div class="col-md-6" align="left">
        <button type="button" class="btn btn-xs btn-default" id="openModalAsso" data-toggle="modal" data-target="#modal_asso">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> Ajouter
        </button>
    </div>    
    <div class="col-md-6" align="right">Filtre :
        <select id="select_graphique">
        </select>
    </div>
    

    <table id="listeAsso" class="table table-hover">
        <thead>
            <tr>
                <th class="col-md-2">Position</th>
                <th class="col-md-2">Groupe Address</th>
                <th class="col-md-3">Nom</th>
                <th class="col-md-2"></th>
            </tr>
        </thead>

        <tbody>
        </tbody>

    </table>

    <div class="modal fade" id="modal_graphique" tabindex="-1" role="dialog" aria-labelledby="graphiqueLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="GraphiqueTitre"></h4>
                </div>
                <div class="modal-body">
                    <div class="hidden">
                        <input type="text" id="typeModal">
                        <input type="text" id="refName">
                        <input type="text" id="position">
                    </div>
                    <form>

                        <div class="form-group">
                            <label for="recipient-name" class="control-label">Nom du graphique:</label>
                            <input type="text" class="form-control" id="name">
                        </div>
                        
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                    </button>
                    <button type="button" id="addGraphique" class="btn btn-default btn-sm">
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modal_asso" tabindex="-1" role="dialog" aria-labelledby="assoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="AssoTitre">Affectation d'un equipement dans un graphique</h4>
                </div>
                <div class="modal-body">
                    <div class="hidden">
                        <input type="text" id="typeModal">
                        <input type="text" id="position">
                    </div>
                    <form>
                        <div class="form-group" id="divgroupe">
                            <label for="message-text" class="control-label">Graphique :</label>
                            <select class="form-control" id="select_graphe">
                            </select>
                        </div>
                        <div class="form-group" id="diveqt">
                            <label for="message-text" class="control-label">Equipement d'etat :</label>
                            <select class="form-control" id="select_eqt">
                            </select>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                        <span class="glyphicon glyphicon-remove" aria-hidden="true"></span>
                    </button>
                    <button type="button" id="addAsso" class="btn btn-default btn-sm">
                        <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="deleteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="deleteTitre"></h4>
                </div>
                <div class="hidden">
                    <input type="text" id="deleteid">
                    <input type="text" id="typeModal">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger btn-ok" id="deleteConfirm">Confirmer</button>
                </div>
            </div>
        </div>
    </div>

<?php include( '_templates/footer.php'); ?>
<!--appel des scripts personnels de la page -->
<script src="js/gstrapport.js"></script>
 