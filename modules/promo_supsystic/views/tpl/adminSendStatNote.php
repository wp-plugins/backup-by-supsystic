<script type="text/javascript">
// <!--
jQuery(document).ready(function(){
	jQuery('#wp-admin-bar-site-name').pointer({
		content: '<h3>Help improve <?php echo S_WP_PLUGIN_NAME?></h3><p>Send anonymus statistics of using functions and modules <?php echo S_WP_PLUGIN_NAME?> plugin to help us improve our software. Statistics is fully anonymus and contain no private data.</p>',
		position: {edge: 'top', align: 'center'},
		buttons: function (event, t) {
			// \\\ is for escape ' in js 
			var button = jQuery('<a id="toeHideSendUsageStatButt" style="margin-left:5px" class="button-secondary">' + '<?php _e('No, don\\\'t send', BUP_LANG_CODE); ?>' + '</a>');
			button.bind('click.pointer', function () {
				jQuery.sendForm({
					msgElID: 'toeSendUsageStatMsg',
					data: {page: 'promo_supsystic', action: 'hideUsageStat', reqType: 'ajax'},
					onSuccess: function(res) {
						if(!res.error) {
							t.element.pointer('close');
						}
					}
				});
			});
			return button;
		},
		close: function() {}
	}).pointer('open');

	jQuery('#toeHideSendUsageStatButt').after('<a id="toeSendUsageStatButt" class="button-primary">' + '<?php _e('Yes, send statistics', BUP_LANG_CODE); ?>' + '</a><div id="toeSendUsageStatMsg"></div>');

	jQuery('#toeSendUsageStatButt').click(function(){
		var self = this;
		jQuery.sendForm({
			msgElID: 'toeSendUsageStatMsg',
			data: {page: 'promo_supsystic', action: 'sendUsageStat', reqType: 'ajax'},
			onSuccess: function(res) {
				if(!res.error) {
					setTimeout(function(){
						jQuery(self).parents('.wp-pointer:first').hide('slow');
					}, 1000);
				}
			}
		});
		return false;
	});
});
// -->
</script>