/*********
 * TODO **
 *********
-- ** Grp Action **--
- Incrementation position a la creation ne marche pas
- Mise a jour de la position a traiter dans update sql
- suppresion du grp avec delete cascade sur la table d'asso

******************/

$(document).ready(function() {
	/************************************************
	 * ************ FONCTION ************************
	 * *********************************************/

	function initModalAddGrp(){
	    $('#modal_action').on('show.bs.modal', function() {
	        $(this).find('#name').val("");
	        $(this).find('#typeModal').val("add");
	        $(this).find('#actionTitre').html("Création d'un nouveau groupe");
	        
	        $.getJSON("ajax.php?action=conf&data=getLastGrpActionPosition", function(json) {

				    newPosition = (json[0].position ===null)?1:parseInt(json[0].position) + 1;
					$('#modal_action').find('#position').val(newPosition);
				
			})
			.error(function() {
				$.growlErreur("Impossible de récupérer la derniere position");
			});
			
	    });
	}
	
	function initModalUpdateGrp(row){
	    $('#modal_action').on('show.bs.modal', function() {
	    
	        var name = row.find("td:nth-child(2)").text();
	        $(this).find('#refName').val(name);
	        $(this).find('#name').val(name);
	        $(this).find('#typeModal').val("edit");
	        $(this).find('#actionTitre').html("Modification de " + name);
	   });
	}
	
	function InitModalDeleteGrp(row) {
		var name = row.find("td:nth-child(2)").text();

		$('#confirm-delete').on('show.bs.modal', function() {
			$(this).find('.modal-title').html("Confirmez-vous la suppresion de " + name + "?");
			$(this).find('#deleteid').val(name);
			$(this).find('#typeModal').val('Grp');
		});
	}
	
	function addGrp() {
	    
	   	var tab = {
			name: $('#modal_action').find('#name').val(), 
			position: $('#modal_action').find('#position').val()
		};
		//console.log(tab.position);
		//test si le groupe adrress n'est pas déja utilisé
		$.getJSON("ajax.php?action=conf&data=testGrpexist&grp=" + $('#modal_action').find('#name').val(), function(json) {
			//console.log(json);
			if (json.eqExist === 0) {
				//so le groupe n'existe pas, on enregistre
				$.ajax({
					url: 'ajax.php?action=conf&data=addGrp',
					type: 'POST',
					data: $.param(tab),
					async: false,
					success: function(a) {

						$('#modal_action').modal('hide');
						if (a.response === true) {
							$.growlValidate("Enregistrement OK");
							setTimeout(refreshTableGrp(), 1000);
						} else {
							$.growlErreur("Probleme lors de l'enregistrement du groupe");
						}

					}
				});


			} else {
				$.growlWarning("Attention, le groupe d'action existe déjà");
			}
		});
	}

	function updateGrp() {
        
        var tab = {
			refName: $('#modal_action').find('#refName').val(),
			name: $('#modal_action').find('#name').val()
		};
		//test si le groupe adrress n'est pas déja utilisé
		$.getJSON("ajax.php?action=conf&data=testGrpexist&grp=" + tab.name, function(json) {
			//console.log(json);
			if (json.eqExist === 0) {
				//so le groupe n'existe pas, on enregistre
				$.ajax({
					url: 'ajax.php?action=conf&data=updateGrp',
					type: 'POST',
					data: $.param(tab),
					async: false,
					success: function(a) {

						$('#modal_action').modal('hide');
						if (a.response === true) {
							$.growlValidate("Modification réussi de " + tab.name);
							setTimeout(refreshTableGrp(), 1000);
						} else {
							$.growlErreur("Probleme lors de l'enregistrement du groupe");
						}

					}
				});


			} else {
				$.growlWarning("Attention, le groupe d'action existe déjà");
			}
		});
	}

	function deleteGrp() {
        var tab = {
			name:  $('#confirm-delete').find('#deleteid').val()
		};
		$.ajax({
			url: 'ajax.php?action=conf&data=deleteGrp',
			type: 'POST',
			data: $.param(tab),
			async: false,
			success: function(a) {

				$('#confirm-delete').modal('hide');
				if (a.response === true) {
					$.growlValidate("Suppression réussi de " + tab.name);
					setTimeout(refreshTableGrp(), 1000);
				} else {
					$.growlErreur("Problême lors de la suppresion de l'equipement " + tab.name);
				}

			}
		});
	}

	function refreshTableGrp() {
        $("#listeGrpAction> tbody").html("");
        $('#select_grpAction').find('option').remove();
       // $('#select_groupe').find('option').remove();
        
		$.getJSON("ajax.php?action=conf&data=getGrpAction", function(json) {

				$.each(json, function(key, val) {
					//console.log(val.group_addr);
    				$('#listeGrpAction > tbody:last').append('<tr>  <td> \
    																	<button type="button" class="btn btn-default btn-sm"> \
    																		<span class="glyphicon glyphicon-chevron-up upGrp" aria-hidden="true"></span> \
    																	</button> \
                                                                        <button type="button" class="btn btn-default btn-sm"> \
                                                                        	<span class="glyphicon glyphicon-chevron-down downGrp" aria-hidden="true"></span> \
                                                                        </button> \
                                                                    </td> \
                	                                                <td>' + val.name + '</td>  \
                	                                                <td>       \
                	                                                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_action"> \
                                                                            <span class="glyphicon glyphicon-edit" aria-hidden="true"></span> \
                                                                        </button> \
                                                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#confirm-delete"> \
                                                                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span> \
                                                                        </button> \
                                                                    </td></tr>');
					//on rempli les listes box pour le tableau d'asso
					$('#select_grpAction').append('<option value="' + val.id + '">' + val.name + '</option>');
				//	$('#select_groupe').append('<option value="' + val.id + '">' + val.name + '</option>');
				    
				});
			    refreshTableAsso();	
			})
			.error(function() {
				$.growlErreur("Impossible de charger la liste des groupes d'actions !!");
			});
	}

    function initModalAddAsso(){
        $('#modal_asso').on('show.bs.modal', function() {
	       
	        $(this).find('#typeModal').val("add");
	        $(this).find('#actionTitre').html("Ajout d'un equipement dans un groupe d'action");
	        
	        $.getJSON("ajax.php?action=conf&data=getGrpAction", function(json) {

                    $('#select_groupe').find('option').remove();
					
					$.each(json, function(key, val) {
					    //console.log(val);
					    $('#select_groupe').append('<option value="' + val.id + '">' + val.name + '</option>');
					   
					});
					$('#select_groupe option[value='+ $('#select_grpAction').val() +']').attr("selected", "selected");
		    })
		    .error(function() {
				$.growlErreur("Impossible de récupérer la liste des Groupes d'action");
			});
			
			$.getJSON("ajax.php?action=conf&data=getEqtAction", function(json) {

                    $('#select_eqt').find('option').remove();
					
					$.each(json, function(key, val) {
					    //console.log(val);
						$('#select_eqt').append('<option value="' + val.id + '">' + val.group_addr +' - ' + val.name + '</option>');
					});
		    })
		    .error(function() {
				$.growlErreur("Impossible de récupérer la liste des Groupes d'action");
			});
			
			
	    });
        
    }
    
    function initModalUpdateAsso(row){
        $('#modal_asso').on('show.bs.modal', function() {
	       
	        $(this).find('#typeModal').val("edit");
	        $(this).find('#actionTitre').html("Modification de l'association");
	        
	        $.getJSON("ajax.php?action=conf&data=getGrpAction", function(json) {

                    $('#select_groupe').find('option').remove();
					
					$.each(json, function(key, val) {
					    //console.log(val);
					    $('#select_groupe').append('<option value="' + val.id + '">' + val.name + '</option>');
					   
					});
					$('#select_groupe option[value='+ $('#select_grpAction').val() +']').attr("selected", "selected");
		    })
			.error(function() {
				$.growlErreur("Impossible de récupérer la liste des Groupes d'action");
			});
			
			$.getJSON("ajax.php?action=conf&data=getEqtAction", function(json) {

                    $('#select_eqt').find('option').remove();
					
					$.each(json, function(key, val) {
					    if(val.group_addr ==  row.find("td:nth-child(2)").text()){
					        $('#select_eqt').append('<option value="' + val.id + '" selected=selected>' + val.group_addr +' - ' + val.name + '</option>');
					    }else{
						    $('#select_eqt').append('<option value="' + val.id + '">' + val.group_addr +' - ' + val.name + '</option>');
					    }
					});
		    })
		     .done(function (){
						$('#select_eqt').attr('disabled', 'disabled');
			})
		    .error(function() {
				$.growlErreur("Impossible de récupérer la liste des Groupes d'action");
			});
			
	    });
        
    }  

    function initModalDeleteAsso(row){
        var name = $('#select_grpAction option:selected').text() + " - " + row.find("td:nth-child(3)").text();
    
    		$('#confirm-delete').on('show.bs.modal', function() {
    			$(this).find('.modal-title').html("Confirmez-vous la suppresion de " + name + "?");
    			$(this).find('#deleteid').val(row.find("td:nth-child(2)").text());
    			$(this).find('#typeModal').val('Asso');
    		});
    }
    
	function addAsso() {
        var tab = {
			id_grp: $('#modal_asso').find('#select_groupe').val(), 
			id_eqt : $('#modal_asso').find('#select_eqt').val(),
			position : 1
		};
		//console.log(tab.position);
		//test si le groupe adrress n'est pas déja utilisé
		$.getJSON("ajax.php?action=conf&data=testAssoExist&grp=" + $('#modal_asso').find('#select_groupe').val() + "&eqt=" + $('#modal_asso').find('#select_eqt').val(), function(json) {
			//console.log(json);
			if (json.AssoExist === 0) {
				//so l'asso n'existe pas, on enregistre
				$.ajax({
					url: 'ajax.php?action=conf&data=addAsso',
					type: 'POST',
					data: $.param(tab),
					async: false,
					success: function(a) {

						$('#modal_asso').modal('hide');
						if (a.response === true) {
							$.growlValidate("Enregistrement OK");
							setTimeout(refreshTableAsso(),1000);
						} else {
							$.growlErreur("Probleme lors de l'enregistrement de l'association");
						}

					}
				});


			} else {
				$.growlWarning("Attention, le couple Groupe d'action + Equipement existe déjà");
			}
		});
	}

	function updateAsso() {
        var tab = {
			id_grp: $('#modal_asso').find('#select_groupe').val(), 
			id_eqt : $('#modal_asso').find('#select_eqt').val(),
			position : 1
		};
		//test si le groupe adrress n'est pas déja utilisé
		$.getJSON("ajax.php?action=conf&data=testAssoExist&grp=" + $('#modal_asso').find('#select_groupe').val() + "&eqt=" + $('#modal_asso').find('#select_eqt').val(), function(json) {
			//console.log(json);
			if (json.AssoExist === 0) {
				//so l'asso n'existe pas, on enregistre
				$.ajax({
					url: 'ajax.php?action=conf&data=updateAsso',
					type: 'POST',
					data: $.param(tab),
					async: false,
					success: function(a) {

						$('#modal_asso').modal('hide');
						if (a.response === true) {
							$.growlValidate("Modification réussi");
							setTimeout(refreshTableAsso(),1000);
						} else {
							$.growlErreur("Probleme lors de la mise à jour de l'association");
						}

					}
				});


			} else {
				$.growlWarning("Attention, le couple Groupe d'action + Equipement existe déjà");
			}
		});
		
	}

	function deleteAsso() {
        var tab = {
			eqt:  $('#confirm-delete').find('#deleteid').val(),
			grp: $('#select_grpAction').val()
		};
		$.ajax({
			url: 'ajax.php?action=conf&data=deleteAsso',
			type: 'POST',
			data: $.param(tab),
			async: false,
			success: function(a) {

				$('#confirm-delete').modal('hide');
				if (a.response === true) {
					$.growlValidate("Suppression réussi de " + tab.eqt);
					setTimeout(refreshTableGrp(), 1000);
				} else {
					$.growlErreur("Problême lors de la suppresion de l'association");
				}

			}
		});
	}

	function refreshTableAsso() {
	    $("#listeAsso > tbody").html("");
        
		$.getJSON("ajax.php?action=conf&data=getAsso&grp=" + $('#select_grpAction').val(), function(json) {

				$.each(json, function(key, val) {
					//console.log(val.group_addr);
    				$('#listeAsso > tbody:last').append('<tr>  <td> \
    																	<button type="button" class="btn btn-default btn-sm"> \
    																		<span class="glyphicon glyphicon-chevron-up upGrp" aria-hidden="true"></span> \
    																	</button> \
                                                                        <button type="button" class="btn btn-default btn-sm"> \
                                                                        	<span class="glyphicon glyphicon-chevron-down downGrp" aria-hidden="true"></span> \
                                                                        </button> \
                                                                    </td> \
                	                                                <td>' + val.group_addr + '</td>  \
                	                                                <td>' + val.name + '</td>  \
                	                                                <td>' + val.grpetat + '</td>  \
                	                                                <td>       \
                	                                                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_asso"> \
                                                                            <span class="glyphicon glyphicon-edit" aria-hidden="true"></span> \
                                                                        </button> \
                                                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#confirm-delete"> \
                                                                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span> \
                                                                        </button> \
                                                                    </td></tr>');
					
				});
			})
			.error(function() {
				$.growlErreur("Impossible de charger la liste des associations !!");
			});

	}

	/************************************************
	 * ************ Evenements **********************
	 * *********************************************/
    	//obligé d'utiliser "on()" car les boutons sont ajoutés apres le chargement de la page
	$("body").on("click", ".btn", function() {

		if ($(this).is("#openModalAddGrp")) {
			initModalAddGrp();
		}
		if ($(this).is("#addGrpAction")) {
		    //console.log($("#modal_action").find('#typeModal').val());
			if( $("#modal_action").find('#typeModal').val() == "add"){
			    addGrp();
			}
			if( $("#modal_action").find('#typeModal').val() == "edit"){
			    //console.log('update');
			    updateGrp();
			}
		}
		if ($(this).children().is(".glyphicon-edit") && $(this).closest('table').is("#listeGrpAction") ) { //;
			//console.log('edit');
			initModalUpdateGrp($(this).closest("tr"));
		}
		if ($(this).children().is(".glyphicon-trash") && $(this).closest('table').is("#listeGrpAction")) {
			//console.log('delete');
			InitModalDeleteGrp($(this).closest("tr"));
		}
		if ($(this).children().is(".glyphicon-edit") && $(this).closest('table').is("#listeAsso") ) { //;
			//console.log('edit');
			initModalUpdateAsso($(this).closest("tr"));
		}
		if ($(this).children().is(".glyphicon-trash") && $(this).closest('table').is("#listeAsso")) {
			//console.log('delete');
			initModalDeleteAsso($(this).closest("tr"));
		}
		if ($(this).is('#deleteConfirm') ) {
		    if ( $('#confirm-delete').find('#typeModal').val()== 'Grp'){
		        deleteGrp();
		    }
		    if( $('#confirm-delete').find('#typeModal').val()== 'Asso' ){
		        deleteAsso();
		    }
		
		}
		
		if($(this).children().is('.upGrp')){
		    row = $(this).parents("tr:first");
	    	row.insertBefore(row.prev());
	    }
	    if($(this).children().is('.downGrp')){
	        row = $(this).parents("tr:first");
	    	row.insertAfter(row.next());
	    }
	    
	    if ($(this).is("#openModalAsso")) {
			initModalAddAsso();
		}
		
		if ($(this).is("#addAsso")) {
		    //console.log($("#modal_action").find('#typeModal').val());
			if( $("#modal_asso").find('#typeModal').val() == "add"){
			    addAsso();
			}
			if( $("#modal_asso").find('#typeModal').val() == "edit"){
			    //console.log('update');
			    updateAsso();
			}
		}
		
	    
		
		
	});
	
    $('#select_grpAction').change(function(){
        refreshTableAsso();
    });
  	
  	
    refreshTableGrp();
    


});