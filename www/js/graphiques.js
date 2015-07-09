$(document).ready(function() {

	/**************************************
	 **** Graphique ***********************
	 * ***********************************/
    function grapheWithTime(json, where, titre){
	
		var chart = new Highcharts.Chart({
			chart: {
				renderTo: where,
				type: 'spline',
				zoomType: 'x',
				panning: true,
				panKey: 'shift'
			},
			title: {
				text: titre
			},
		
			xAxis: {
				type: 'datetime',
                dateTimeLabelFormats: { 
                    minute: '%H:%M',
                    hour: '%H:%M'
                   
                },
				labels: {
					rotation : -45,
				},
				title: {
					text: 'Heures',
				}
			},
			yAxis: [{
					title: {
						text: '...',
					},
					min : 0 //,	max : 100
				}],
			credits: {
				enabled : true,
				text : 'DomoVision',
				href: 'http://www.domovision.org'
			},
			plotOptions: {
				spline: {
					marker: {
						enabled: false
					}
				}
			},
			tooltip: {
                shared: true,
                crosshairs: true
            },
			series: json
		});
	
	}
	
	
	function graphe_error(where,titre){
		var chart = new Highcharts.Chart({
			chart: {
				renderTo: where,
				type: 'line'
			},
			title: {
				text: titre
			},
			subtitle: {
				text: 'Problème lors de la récupération des données !'
			},
			credits: {
				enabled : true,
				text : 'domoVision',
				href: 'http://www.domovision.org'
			}
		});
	
	}
	
	
	/**************************************
	 **** Creation de la structure de la page 
	 * ***********************************/
	function createPageGraphe(){
		//$(".se-pre-con").fadeIn();
		
    	$.getJSON("ajax.php?action=conf&data=getGraphe", function(json) {
    
    				$.each(json, function(key, val) {
    					//console.log(val.group_addr);
        				$('.container-graphe').append('<div class="page-header"> \
        				                       <div class="graphique" id="'+ val.id +'" style="width:100%; height:400px;"></div> \
        				                        </div>');
    				});
    			    
    			})
    			.done(function() {
    			    refreshAllGraphe();
    			})
    			.error(function() {
    				$.growlErreur("Impossible de charger la liste des groupes d'actions !!");
    			});
	}
	
	/**************************************
	 **** Peuplement des graphiques *******
	 * ***********************************/
	function refreshAllGraphe(){
	    var jour = $.datepicker.formatDate('yy-mm-dd',$.datepicker.parseDate('dd/mm/yy', $( "#date_encours" ).val()));
	    
	    $.each($(".graphique"), function (key, val){
	        
	        $.getJSON("ajax.php?action=getGraphe&id="+ val.id +"&jour=" + jour, function(json) {
				//console.log(json.grapheName);	
				grapheWithTime(json.data,val.id,json.grapheName);
				
			})
			.error(function() { 
			    //console.log('error');	
				graphe_error(val.id,'erreur');
				
			});
		
	
	  
			
	    });
	}
	
	/**************************************
	 **** EVENEMENT ***********************
	 * ***********************************/
	
	$( "#date_avant" ).click(function() {
		var newdate = $.datepicker.parseDate('dd/mm/yy', $( "#date_encours" ).val());
		newdate.setDate(newdate.getDate()-1);
		
		$( "#date_encours" ).val(
								$.datepicker.formatDate('dd/mm/yy', newdate)
							);
		refreshAllGraphe();					
	});
	
	$( "#date_apres" ).click(function() {
		var newdate = $.datepicker.parseDate('dd/mm/yy', $( "#date_encours" ).val());
		newdate.setDate(newdate.getDate()+1);
		
		$( "#date_encours" ).val(
								$.datepicker.formatDate('dd/mm/yy', newdate)
							);
		refreshAllGraphe();
	});
	
	$( "#date_encours" ).change(function() {
		
		refreshAllGraphe();
	});

	/**************************************
	 **** Attente preload *****************
	 * ***********************************/	
	
    $(document).ajaxStart(function () {
            $(".se-pre-con").fadeIn();
    });
      
    $(document).ajaxStop(function () {
            $(".se-pre-con").fadeOut();
    });	

	/**************************************
	 **** Execution au chargement de la page  
	 * ***********************************/	
	
	createPageGraphe();
    
    
});