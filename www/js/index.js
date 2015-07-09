$(document).ready(function() {
	$.ajaxSetup({
		async: false
	});
    
    function refreshMiddlePage(){
        $.getJSON("ajax.php?action=conf&data=getGrpAction", function(json) {
            
            $.each(json, function(key, val) {
            	//$('#action-middle').append('<div><h3>' + val.name +'</h3><br/>');
            	html = '<div><h3>' + val.name +'</h3><br/>';
            	$.getJSON("ajax.php?action=conf&data=getAsso&grp=" + val.id, function(json) {
    				
    				//$('#action-middle').append('<div class="row">');
    				html = html.concat('<div class="row actionneur">');
    				
    				$.each(json, function(key, val) {
    					//console.log(val.group_addr);
    					//$('#action-middle').append('<div class="col-md-2 actionneur">'+ val.etat_name);
    					html = html.concat('<div class="col-md-3 vertical-align"><label class="actionneur">'+ val.etat_name +' : ');
	    					if(val.aff_type == 1){ //c'est un bouton 
	        				    //$('#action-middle').append('<button type="button" class="btn btn-lg btn-default" id="'+ val.etat_group_addr +'">'+ val.etat_name +'</button>&nbsp;');
	    							//$('#action-middle').append('<input class="switch" type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="primary" id="'+ val.etat_group_addr +'">');
	    							html = html.concat('<input class="switch" type="checkbox" id="'+ val.etat_group_addr +'">');
	    						
	    					}
	    					if(val.aff_type == 2){ // c'est un slider
	    							//$('#action-middle').append('<input class="slider-volet" id="'+ val.etat_group_addr +'" text="'+ val.etat_name +'" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="100" data-slider-orientation="vertical" />&nbsp;');
	    							html = html.concat('<input class="slider-volet" id="'+ val.etat_group_addr +'" text="'+ val.etat_name +'" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="100" data-slider-orientation="vertical" />&nbsp;');
	    					}
    					
    					//$('#action-middle').append('</div>');
    					html = html.concat('</label></div>');
    				});
    				
    				//$('#action-middle').append('</div>');
    				html = html.concat('</div>');
    			    $('#action-middle').append(html);
    			})
    			.error(function() {
    				$.growlErreur("Impossible de charger les elements du groupe d'action");
    			});
            
                
            	$('#action-middle').append('</div>');   
            });
            $('.switch').bootstrapSwitch();
            
        })
        .error(function() {
				$.growlErreur("Impossible de charger les groupes d'actions");
		});
    }    
    
    refreshMiddlePage();
    
    
    $.ajaxSetup({
		async: true
	});
});    