$(document).ready(function() {
	/************************************************
	 * ************ FONCTION ************************
	 * *********************************************/

	function hideGrpEtat() {

		if ($('#select_type_eqt option:selected').text() == "Etat") {
			$('#divgrpetat').hide();
			$('#divtracked').show();
		} else {
			$('#divgrpetat').show();
			$('#divtracked').hide();
		}
	}

	function editEquipement(row) {
		//console.log($row);
		grpadress = row.find("td:nth-child(1)").text();
		oldname = row.find("td:nth-child(2)").text();
		olddpt = row.find("td:nth-child(3)").text();
		oldtypeeqt = row.find("td:nth-child(4)").text();
		oldgrpetat = row.find("td:nth-child(5)").text();
		typeAffichage = row.find("td:nth-child(6)").text();
		is_track    = (row.find("td:nth-child(7)").text() == "X")?true:false;

		//$('#grpaddressExist').hide();

		$('#modal_editeqt').on('show.bs.modal', function(event) {

			$(this).find('#typemodal').val("edit");

			$(this).find('#divgrpaddress').hide();
			$(this).find('.modal-title').html("Edition de " + grpadress);
			$(this).find('#grpaddress').val(grpadress);
			$(this).find('#name').val(oldname);
			//console.log(oldtypeeqt);
			//$(this).find('#select_type_eqt').val(oldtypeeqt);
			$(this).find('#select_dpt option[value="' + olddpt + '"]').attr("selected", "selected");

			$(this).find('#select_type_eqt option:contains("' + oldtypeeqt + '")').attr("selected", "selected");
            
            $(this).find('#select_tracked').prop('checked', is_track);
			hideGrpEtat();

			$.getJSON("ajax.php?action=conf&data=grpetat", function(json) {

					$('#select_grpetat option[value!="0"]').remove();
					$.each(json, function(key, val) {
						$('#select_grpetat').append('<option value="' + key + '">' + val + '</option>');
					});
				})
				.error(function() {
					$.growlErreur("Impossible de récupérer la liste des equipements d'etat !!");
				})
				.done(function() {
					$('#select_grpetat option:contains("' + oldgrpetat + '")').attr("selected", "selected");
					//$('#select_grpetat').val(oldgrpetat);
				});
				
			$.getJSON("ajax.php?action=conf&data=getTypeAffichage", function(json) {

				    $('#select_typeAffichage').find('option').remove();
					$.each(json, function(key, val) {
						$('#select_typeAffichage').append('<option value="' + val.id + '">' + val.type + '</option>');
					});
				})
				.error(function() {
					$.growlErreur("Impossible de récupérer la liste des types d'affichage !!");
				})
				.done(function() {
					$('#select_typeAffichage option:contains("' + typeAffichage + '")').attr("selected", "selected");
					//$('#select_grpetat').val(oldgrpetat);
				});	
				
		});


	}

	function addEquipement() {

		$('#modal_editeqt').on('show.bs.modal', function() {

			$(this).find('#typemodal').val("add");
			$(this).find('#divgrpaddress').show();
			$(this).find('.modal-title').html("Ajout d'un equipement ");
			$(this).find('#grpaddress').val("");
			$(this).find('#name').val("");
			//console.log(oldtypeeqt);
			$(this).find('#select_type_eqt').val("");
			$(this).find('#select_dpt option[value="1.xxx"]').attr("selected", "selected");
			hideGrpEtat();

			$.getJSON("ajax.php?action=conf&data=grpetat", function(json) {

					$('#select_grpetat option[value!="0"]').remove();
					$.each(json, function(key, val) {
						$('#select_grpetat').append('<option value="' + key + '">' + val + '</option>');
					});
				})
				.error(function() {
					$.growlErreur("Impossible de récupérer la liste des equipements d'etat !!");
				});
			
			$.getJSON("ajax.php?action=conf&data=getTypeAffichage", function(json) {

				    $('#select_typeAffichage').find('option').remove();
					$.each(json, function(key, val) {
						$('#select_typeAffichage').append('<option value="' + val.id + '">' + val.type + '</option>');
					});
				})
				.error(function() {
					$.growlErreur("Impossible de récupérer la liste des types d'affichage !!");
				});
						
		});
	}

	function saveNewEqt() {

		var tab = {
			grpaddress: $('#modal_editeqt').find('#grpaddress').val(),
			name: $('#modal_editeqt').find('#name').val(),
			dpt: $('#modal_editeqt').find('#select_dpt').val(),
			typeeqt: $('#modal_editeqt').find('#select_type_eqt').val(),
			grpetat: $('#modal_editeqt').find('#select_grpetat').val(),
			type: $('#modal_editeqt').find('#select_typeAffichage').val(),
			is_track : $('#modal_editeqt').find('#select_tracked').is(":checked")?1:0,

		};
		
		//test si le groupe adrress n'est pas déja utilisé
		$.getJSON("ajax.php?action=conf&data=testeqexist&grpaddress=" + $('#modal_editeqt').find('#grpaddress').val(), function(json) {
			//console.log(json);
			if (json.eqExist === 0) {
				//so le groupe n'existe pas, on enregistre
				$.ajax({
					url: 'ajax.php?action=conf&data=saveeqt',
					type: 'POST',
					data: $.param(tab),
					//scriptCharset: "utf-8",
					//contentType: "text/html; charset=UTF-8",
					async: false,
					success: function(a) {

						$('#modal_editeqt').modal('hide');
						if (a.response === true) {
							$.growlValidate("Enregistrement OK");
							setTimeout(refreshTableEqt(), 1000);
						} else {
							$.growlErreur("Probleme lors de l'enregistrement de l'equipement");
						}

					}
				});


			} else {
				$.growlWarning("Attention, le Group Address existe déjà");
			}
		});


	}


	function updateEqt() {

		var tab = {
			grpaddress: $('#modal_editeqt').find('#grpaddress').val(),
			name: $('#modal_editeqt').find('#name').val(),
			dpt: $('#modal_editeqt').find('#select_dpt').val(),
			typeeqt: $('#modal_editeqt').find('#select_type_eqt').val(),
			grpetat: $('#modal_editeqt').find('#select_grpetat').val(),
			type: $('#modal_editeqt').find('#select_typeAffichage').val(),
			is_track : $('#modal_editeqt').find('#select_tracked').is(":checked")?1:0,

		};
		$.ajax({
			url: 'ajax.php?action=conf&data=updateeqt',
			type: 'POST',
			data: $.param(tab),
			async: false,
			success: function(a) {

				$('#modal_editeqt').modal('hide');
				if (a.response === true) {
					$.growlValidate("Modification réussi de " + tab.grpaddress);
					setTimeout(refreshTableEqt(), 1000);
				} else {
					$.growlErreur("Problême lors de la modification de l'equipement " + tab.grpaddress);
				}

			}
		});

	}

	function confirmeDeleteEqt(row) {
		grpaddress = row.find("td:nth-child(1)").text();

		$('#confirm-delete').on('show.bs.modal', function() {
			$(this).find('.modal-title').html("Confirmez-vous la suppresion de " + grpaddress + "?");
			$(this).find('#deleteGrpaddress').val(grpaddress);
		});
	}

	function deleteEqt() {
		//console.log($('#deleteGrpaddress').val());

		var tab = {
			grpaddress: $('#deleteGrpaddress').val()
		};
		$.ajax({
			url: 'ajax.php?action=conf&data=deleteeqt',
			type: 'POST',
			data: $.param(tab),
			async: false,
			success: function(a) {

				$('#confirm-delete').modal('hide');
				if (a.response === true) {
					$.growlValidate("Suppression réussi de " + tab.grpaddress);
					setTimeout(refreshTableEqt(), 1000);
				} else {
					$.growlErreur("Problême lors de la suppresion de l'equipement " + tab.grpaddress);
				}

			}
		});

	}

	function addrow(grpaddress, name, dpt, typeeq, grpetat, typeAffichage, is_track) {
        var histo = is_track?'X':'';
        
		$('#listeeqt > tbody:last').append('<tr><td>' + grpaddress + '</td> \
	                                                <td>' + name + '</td>  \
	                                                <td>' + dpt + '</td>  \
	                                                <td>' + typeeq + '</td>  \
	                                                <td>' + grpetat + '</td>  \
	                                                 <td>' + typeAffichage + '</td>  \
	                                                 <td>' + histo + '</td>  \
	                                                <td>       \
	                                                    <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#modal_editeqt"> \
                                                            <span class="glyphicon glyphicon-edit" aria-hidden="true"></span> \
                                                        </button> \
                                                        <button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#confirm-delete"> \
                                                            <span class="glyphicon glyphicon-trash" aria-hidden="true"></span> \
                                                        </button> \
                                                    </td></tr>');
	}


	function refreshTableEqt() {
		//affichage liste des types equipements possibles
		$("#listeeqt> tbody").html("");

		$.getJSON("ajax.php?action=conf&data=geteqt", function(json) {

				$.each(json, function(key, val) {
					//console.log(val);
					addrow(val.group_addr, val.name, val.dpt, val.typeeqt, val.grpetat, val.typeAffichage,val.is_track);
				});
			})
			.error(function() {
				$.growlErreur("Impossible de charger la liste des equipements !!");
			});
	}


	/************************************************
	 * ************ Chargement données **************
	 * *********************************************/

	//affichage liste des types equipements possibles
	$.getJSON("ajax.php?action=conf&data=typeeqt", function(json) {
			typeEq = json;
			$.each(json, function(key, val) {
				$('#select_type_eqt').append('<option value="' + key + '">' + val + '</option>');
			});
		})
		.error(function() {
			$.growlErreur("Impossible de récupérer la liste des types equipements !!");
		});



	//affiche la listes de Dpt
	$.getJSON("ajax.php?action=conf&data=alldpt", function(json) {

			$.each(json, function(key, val) {
				$.each(val, function(type, description) {
					$('#select_dpt').append('<option value="' + type + '">' + type + '-' + description.Name + ' ' + description.Unite + '</option>');
				});
			});
		})
		.error(function() {
			$.growlErreur("Impossible de récupérer la liste des data Point type !!");
		});


	/************************************************
	 * ************ Evenement ***********************
	 * *********************************************/
	//gestion de l'affichage ou pas de la liste de choix groupe etat
	$('#select_type_eqt').change(function() {
		hideGrpEtat();
	});

	//obligé d'utiliser "on()" car les boutons sont ajoutés apres le chargement de la page
	$("body").on("click", ".btn", function() {

		if ($(this).children().is(".glyphicon-edit")) {
			editEquipement($(this).closest("tr"));
		}
		if ($(this).children().is(".glyphicon-trash")) {
			confirmeDeleteEqt($(this).closest("tr"))
		}
		if ($(this).children().is(".glyphicon-plus")) {
			addEquipement();
		}
		if ($(this).children().is(".glyphicon-refresh")) {
			refreshTableEqt();
		}
		if ($(this).children().is(".glyphicon-ok")) {

			if ($('#typemodal').val() == "add") {
				saveNewEqt();
			}
			if ($('#typemodal').val() == "edit") {
				updateEqt();
			}
		}
		if ($(this).is('#deleteEqtConfirm')) {
			deleteEqt();
		}
	});




	refreshTableEqt();









});