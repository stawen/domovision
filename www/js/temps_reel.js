$(document).ready(function() {

/***************************************
 * ** function de gestion des bouttons *
 * ************************************/
/*
$(".btn").click(function() {
	var val = 0;
	//Si on click, nous voulons changer d'etat
	if ($(this).is(".btn-default") ){
		val = 1;
	}
	if ($(this).is(".btn-success") ){
		val = 0;
	}
	//console.log($(this).attr("id"));
	$.get( "ajax.php?action=setButton&grp=" + $(this).attr("id") + "&value=" + val );
	
    //$(this).toggleClass('btn-default btn-success');
	
});
*/

/***************************************
 * ** function de gestion switch       *
 * ************************************/
var justClick = new Array();

$('.switch').on('switchChange.bootstrapSwitch', function(event, state) {
    var val = state?1:0;
	justClick[$(this).attr("id")]=true;
	
	$.get( "ajax.php?action=setButton&grp=" + $(this).attr("id") + "&value=" + val );
	
});



$(".slider-volet").on('slideStop',function(){
    //console.log($(this).bootstrapSlider('getValue'));
    $.get( "ajax.php?action=setSlider&grp=" + $(this).attr("id") + "&value=" + $(this).val() );
});



function getdata(){
    $.getJSON("ajax.php?action=etat", function(json) {
                    //console.log(json);
    				//updatebt(json);
    				updateSwitch(json);
    				updateSlider(json);
    				updateLabel(json);
    				
    				setTimeout(function(){getdata();}, 1500);
    			})
    			.error(function() { 
    				console.log('error getJsonState');	
    			});
}


/***************************************
 * ** function de maj bouttons         *
 * ************************************/
function updatebt(json){
    
   	$(".btn").each(function() {
   	   
        if(json[$(this).attr("id")].value === 0){
            //console.log("OFF");
            $(this).switchClass('btn-success', 'btn-default',0);
        }else{
            //console.log("ON");
            $(this).switchClass('btn-default', 'btn-success',0);
        }
       
       
    });
}
/***************************************
 * ** function de maj des switchs      *
 * ************************************/


function updateSwitch(json){
    $(".switch").each(function() {
        
        var id = $(this).attr("id");
   	    
        if(!justClick[id]){
        	
	        if(json[id].value === 0){
	            //console.log("OFF");
	                $(this).bootstrapSwitch('state', false, 'skip');
	                $(this).bootstrapSwitch('offColor', 'primary');
	                $(this).bootstrapSwitch('onColor', 'warning');
	        }else{
	                $(this).bootstrapSwitch('state', true, 'skip');
	                $(this).bootstrapSwitch('onColor', 'success');
	                $(this).bootstrapSwitch('offColor', 'warning');
	        }
        }
       	justClick[id]=false;
       
    });
}

function updateSlider(json){
    $(".slider-volet").each(function(a) {
        $(this).bootstrapSlider('setValue', json[$(this).attr("id")].value);
    });
}

function updateLabel(json){
    
    $(".label").each(function() {
        $(this).text( json[$(this).attr("id")].value + " " + json[$(this).attr("id")].unite);
    });    
}


$(".slider-volet").each(function() {
    slider = $(this);
    slider.bootstrapSlider({
	    reversed : false,
        tooltip: 'always',
	    formatter: function(value) {
    		//return slider.attr('text')+' : ' + value + ' %';
    		return value + ' %';
	    }
    });
    
});
    




getdata();

});