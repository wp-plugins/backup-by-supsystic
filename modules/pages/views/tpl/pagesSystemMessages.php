<script type="text/javascript">
// <!--
jQuery(document).ready(function(){
	var toeGlobalMessages = <?php echo utilsBup::jsonEncode($this->messages)?>;
	var toeGlobalMessagesStr = '';
	if(toeGlobalMessages.adminAlerts) {
		for(var i in toeGlobalMessages.adminAlerts) {
			toeGlobalMessagesStr += '<div class="toeSystemAlert">'+ 
				'<i style="font-size: 12px;"><?php echo $this->forAdminOnly?></i><br class="toeClear" />'+ 
				toeGlobalMessages.adminAlerts[ i ]+ 
				'</div>';
		}
	}
	if(toeGlobalMessagesStr != '') {
		jQuery('body:first').prepend(toeGlobalMessagesStr);
	}
		
});
// -->
</script>
