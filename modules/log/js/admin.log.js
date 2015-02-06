jQuery(document).ready(function() {
	var j = jQuery.noConflict();
	
	j('.bupToggleLog').on('click', function() {
		j(this).next().toggle();
	});
});