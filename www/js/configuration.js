$(document).ready(function() {

	var statusDaemon = new Array();
	//var waitReturnStatus;
	
    $('#submitIpKnx').click(function(){
        
        
        if(/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test($('#ipKnx').val())){
            console.log("true");
        }else{
            
            $.growlErreur('Adresse Ip Invalide !');
        }
    });
    
    $('[id^="knx"]:button').click(function(){
    	//console.log(daemon);
    	//on ne fait rien si le bouton est en attente (warning)
    	if($(this).is('.btn-success')){
    		cmd = 'stop';
    		$(this).switchClass('btn-success', 'btn-warning',0);
    		$(this).find('span').switchClass('glyphicon-play','glyphicon-refresh glyphicon-spin' ,0);
    		statusDaemon[$(this).attr('id')]=0;
    	}
    	if($(this).is('.btn-danger')){
    		cmd	='start';
    		$(this).switchClass('btn-danger', 'btn-warning',0);
    		$(this).find('span').switchClass('glyphicon-stop','glyphicon-refresh glyphicon-spin' ,0);
    		statusDaemon[$(this).attr('id')]=1;
    	}
    	
    	var daemon = $(this);
    	if (daemon.attr('id') != "knx_daemon"){
    		setCmd(daemon,cmd);
    	}else{
    		
    	}
    });
    
	function setCmd(daemon,cmd){
		$.getJSON("ajax.php?action=admin&script="+ daemon.attr('id') +"&cmd="+cmd, function(json) {
    	    
    	    if(json.response == 1){
    	          var waitReturnStatus = setInterval(
    	                                function() { 
    	                                    var newStatus = getStatus(daemon, false);
    	                                    if(statusDaemon[daemon.attr('id')] === newStatus ){
    	                                        //console.log('stop thread');
	    	                                    clearInterval(waitReturnStatus);
	    	                                    updateIconDaemon(daemon, newStatus);
	    	                                    //updateKnxDaemon();
	                                        }    
    	                               }, 1500);  
	    	}
    	    
    	})
    	.error(function() {
    		$.growlErreur("Impossible de communiquer avec "+ daemon.attr('id'));
    	});
	}
	
	
    function getStatus(daemon,async){
   		var response;
   		$.ajax({
			url: 'ajax.php?action=admin&script='+ daemon.attr('id') +'&cmd=isrun',
			type: 'GET',
			async: async,
			success: function(json) {
			    if(async){
			        updateIconDaemon(daemon, json.response);
			        updateKnxDaemon();
				}else{
			        response = json.response;
			    }
			    
			}
		});
		return response;
    }
      
    function refreshAllStatus(){
    	$('[id^="knx"]:button').each(function(){
    		if ($(this).attr('id') != "knx-daemon"){
    			getStatus($(this),true);
    		}
    	});
    }      
      
    function updateKnxDaemon(){
    	var numItems = $('.btn-success').length;
    	var daemon = $('#knx-daemon');
    	
		if (numItems === 3){ 
			console.log("green");
			updateIconDaemon(daemon,1);
		}else{
			console.log("red");
			updateIconDaemon(daemon,0);
		}
    }
    
    //1 : Run | 0 : Not running    
    function updateIconDaemon(icon,etat){
    	//console.log("updateIconDaemon");
    	if(etat === 1){
    		$(icon).switchClass('btn-warning', 'btn-success',0);
    		$(icon).find('span').switchClass('glyphicon-refresh glyphicon-spin', 'glyphicon-play',0);
    		//console.log($(icon).find('span'));
    		
    	}
    	if(etat === 0){
    		$(icon).switchClass('btn-warning', 'btn-danger',0);
    		$(icon).find('span').switchClass('glyphicon-refresh glyphicon-spin','glyphicon-stop' ,0);
    		//console.log($(icon).find('span'));
    	}
    	
    }        
    refreshAllStatus();           

       
    
});