$(document).ready(function() {

	$.growlValidate = function(text) {
		$.notify({
			icon: 'glyphicon glyphicon-save',
			message: text
		}, {
			z_index: 9999,
			type: 'success'
		});
	}

	$.growlErreur = function(text) {
		$.notify({
			icon: 'glyphicon glyphicon-exclamation-sign',
			message: text
		}, {
			z_index: 9999,
			type: 'danger'
		});
	}

	$.growlWarning = function(text) {
		$.notify({
			icon: 'glyphicon glyphicon-exclamation-sign',
			message: text
		}, {
			z_index: 9999,
			type: 'warning'
		});
	}
	
	$.fn.bootstrapSwitch.defaults.onColor = 'warning';
	$.fn.bootstrapSwitch.defaults.offColor = 'warning';
	$.fn.bootstrapSwitch.defaults.handleWidth = "20px";


});