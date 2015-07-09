<?php
include('_templates/header.php');
include('_templates/menu.php');

?>   

    <div class="container theme-showcase" role="main">
        <div id="indicateur" class="page-header"> 
            <span class="glyphicon glyphicon-hand-right"></span> T°C intérieur : <span id="11/1/1" class="label label-primary">00,00 °C</span> &nbsp;&nbsp;
            <span class="glyphicon glyphicon-hand-right"></span> Hygrometrie : <span id="11/2/1" class="label label-success">00,00</span> &nbsp;&nbsp;
    	</div>
    	
    	<div id="action-middle" class="page-header"> 
    	
        </div>
	    <div>
	    <!-- input class="slider-volet" id="1/4/4" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="25" data-slider-orientation="vertical"/ -->
	    <!-- input class="slider-volet" id="1/4/6" type="text" data-slider-min="0" data-slider-max="100" data-slider-step="1" data-slider-value="55" data-slider-orientation="vertical"/ -->
	    
	    </div>
		
		


	
<?php
include('_templates/footer.php');
?>
<!--appel des scripts personnels de la page -->
    <script src="js/index.js"></script>
	<script src="js/temps_reel.js"></script>
    </body>
</html>