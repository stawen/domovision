<?php
include('_templates/header.php');
include('_templates/menu.php');
?>   

    <div class="container theme-showcase" role="main">
		
		<div class="page-header"> 
			<div class="col-md-10" >
				<span class="glyphicon glyphicon-hand-right"></span> --aaa-- : <span id="consoPellet" class="label label-primary">00,00 Kg</span> &nbsp;&nbsp;
				<span class="glyphicon glyphicon-arrow-up"></span> T°C Max (ext) : <span id="tcmax" class="label label-success">00,0 °C</span> &nbsp;&nbsp;
				<span class="glyphicon glyphicon-arrow-right"></span> T°C Moy (ext) : <span id="tcmoy" class="label label-info">00,0 °C</span> &nbsp;&nbsp;
				<span class="glyphicon glyphicon-arrow-down"></span> T°C Min (ext) : <span id="tcmin" class="label label-warning">00,0 °C</span> &nbsp;&nbsp;
				<span class="glyphicon glyphicon-cloud"></span> DJU : <span id="dju" class="label label-primary">0<span>
			</div>
		
			<div class="col-md-2" align="right">
						
			<?php
				$months = array('Janvier','Fevrier','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Decembre');
				echo '<select id="mois" name="mois">';
				
				foreach ($months as $index => $name){
					echo '<option value="' . ($index +1) . '"'; 
					if ($index +1 == date('m')) echo ' selected="selected"';
						echo ">$name</option>";
				}
				echo "</select>";
					
				
				echo '<select id="annee" name="annee">';	
					for($a=2014;$a <= 2035; $a++){
						echo '<option value="' . ($a) . '"';
						if ($a  == date('Y')) echo ' selected="selected"';
						echo ">$a</option>";
					}
				echo "</select>";
					
			?>
			</div>
			<div class="col-md-10">
				<span class="glyphicon glyphicon-hand-right"></span> Nb Cycle : <span id="cycle" class="label label-primary">0<span>
			</div>	
			<div id="histo-temperature" style="width:100%; height:400px;"></div>
			
		</div>
		
	</div>	
		
<?php
include('_templates/footer.php');
?>
<!--appel des scripts personnels de la page -->
	
	<script src="js/histo.js"></script>
	
	</body>
</html>