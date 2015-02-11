jQuery(document).ready(function() { 
    // Login (Save credentials and bucket)
	jQuery('#bupAmazonCredentials').submit(function(event) {
		event.preventDefault();
        AmazonModule.login(this);
	});
    
    // Logout (Reset credentials and bucket)    
    jQuery('#bupAmazonEdit').submit(function(event) {
        event.preventDefault();
        AmazonModule.logout(this);
    });

    // Delete
    jQuery('.bupAmazonS3Delete').on('click', function() {
        var filename = jQuery(this).attr('data-filename'),
            rowId    = jQuery(this).attr('data-row-id');

            AmazonModule.deleteOnS3(filename, rowId);
    });

    // Restore
    jQuery('.bupAmazonS3Restore').on('click', function() {
        var filename = jQuery(this).attr('data-filename');
        AmazonModule.download(filename, function(response) {
            AmazonModule.restore(response.data.filename);
        });
    });
});

var AmazonModule = {

    login: function(element) {
        jQuery(element).sendFormBup({
            msgElID: 'bupAmazonAlerts',
            onSuccess: function() {
                location.reload(true);
            }
        });
    },
    logout: function(element) {
        jQuery(element).sendFormBup({
            msgElID: 'bupAmazonAlerts',
            onSuccess: function() {
                location.reload(true);
            }
        });        
    },
    deleteOnS3: function(filename, rowId) {
        jQuery.sendFormBup({
            msgElID:     'bupAmazonAlerts',
            data: {
                'reqType':  'ajax',
                'page':     'amazon',
                'action':   'deleteAction',
                'filename': filename,
            },
            onSuccess: function() {
                jQuery('#' + rowId).remove();
            }
        });
    },
    download: function(filename, cb) {
        jQuery.sendFormBup({
            msgElID: 'bupAmazonAlerts',
            data: {
                'reqType':  'ajax',
                'page':     'amazon',
                'action':   'downloadAction',
                'filename': filename
            },
            onSuccess: function(response) {
                cb(response);
            }
        });
    },
    restore: function(filename) {
        jQuery.sendFormBup({
            msgElID: 'bupAmazonAlerts',
            data: {
                'reqType': 'ajax',
                'page':    'backup',
                'action':  'restoreAction',
                'filename': filename
            },
            onSuccess: function(response) {
                if(response.error === false) {
                    location.reload(true);
                }
            }
        });
    },
}