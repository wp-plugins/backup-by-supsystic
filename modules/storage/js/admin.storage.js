var bupStorageFilesPerPage = 3;
jQuery(document).ready(function(){
	jQuery(document).on('click', '.restoreBup', function() {
		
		sendToRestore(jQuery(this).attr('name'), jQuery(this).attr('id'));
	});
	jQuery(".bupStorageOptions").click(function() {
		//reFresh();
	});
	
	//getSubersListBup();
	
	jQuery(document).on('click', '.delBackup', function() {
		var tmp = jQuery(this).attr('id');
		var arr = tmp.split('|');
		deleteBackup(arr[1], arr[2], jQuery(this).parent().parent(), arr[3]);
	});
	
	jQuery(document).on('click', '.bup_a_Send_to a', function() {
		jQuery(this).parent().next().css('display') == 'none' ? jQuery(this).parent().next().show() : jQuery(this).parent().next().hide();
	});
	

// I dont know WTF is there. This is part of the script where i'll be code

	jQuery(document).on('click', '.upload', function(clickEvent) {
		clickEvent.preventDefault();
		var providerModule = jQuery(this).attr('data-provider'),
			providerAction = jQuery(this).attr('data-action'),
			files          = jQuery(this).attr('rel'),
			id             = jQuery(this).attr('id');

		/*console.log('Module: ' + providerModule);
		console.log('Action: ' + providerAction);
		console.log('Files: '  + files);*/
		cloudStorage.upload(providerModule, providerAction, files, id);

	});
});

// cloudStorage will be object, where i'll be create useful functions, cuz 
// every line of code in this file - HARDCORE and maybe in future we are remove
// all 
var cloudStorage = {
	upload: function(providerModule, providerAction, files, identifier) {
		jQuery.sendFormBup({
			msgElID: 'MSG_EL_ID_' + identifier,
			data: {
				page:    providerModule, // Module
				action:  providerAction, // Action
				reqType: 'ajax',         // Request type
				sendArr: files           // Files
			}
		});
	}
};

// End of my part

/*function getSubersListBup(page) {
	this.page;	// Let's save page ID here, in static variable
	//alert(this.page + " typeof=" + typeof(this.page));
	if(typeof(this.page) == 'undefined')
		this.page = 0;
	if(typeof(page) != 'undefined')
		this.page = page;
	
	page = this.page;
	
	//alert(page + ' - ' + bupStorageFilesPerPage);
//	jQuery.sendFormBup({
//		msgElID: 'bupAdminStorageMsg'
//	,	data: {page: 'storage', action: 'getList', reqType: 'ajax', limitFrom: page * bupStorageFilesPerPage, limitTo: bupStorageFilesPerPage}
//	,	onSuccess: function(res) {
//			if(!res.error) {
//				if(page > 0 && res.data.count > 0 && res.data.list.length == 0) {	// No results on this page -
//					// Let's load next page
//					getSubersListBup(page - 1);
//				} else {
//					new toeListableBup({
//						table: '#bupAdminStorageTable'
//					,	paging: '#bupAdminStoragePaging'
//					,	list: res.data.list
//					,	count: res.data.count
//					,	perPage: bupStorageFilesPerPage
//					,	page: page
//					,	pagingCallback: getSubersListBup
//					});
//				}
//			}
//		}
//	});
}
*/
function reFreshStorageBup(){
	//getSubersListBup();
	/*jQuery(this).sendFormBup({
		  msgElID: 'MSG_EL_STORAGE',
		  data: { page: 'storage', action: 'displayStorage', reqType: 'ajax' },
		  onSuccess: function(res) {
			  jQuery('#storageFrame').html(res.data);
		  }
		});*/
}

function sendToRestore(fileName, id){
if (confirm('Are you sure?')) {	
	jQuery(this).sendFormBup({
		  msgElID: 'MSG_EL_ID_'+id,
		  data: {page: 'backup', action: 'restore', reqType: 'ajax', postData: {/*id : id,*/ fileName : fileName} },
		  onSuccess: function(res) {
			  if(!res.error) {
				  alert('asdasd');
				location.reload();
			  }
		  }
		});
		 }else {
	  return false;
  }
}

function deleteBackup(id, fileName, tabStr, name){
	if (confirm('Delete "' + name + '" backup?')) {
		jQuery(this).sendFormBup({
		  msgElID: 'MSG_EL_ID_'+id,
		  data: {page: 'backup', action: 'delete', reqType: 'ajax', postData: {id : id, fileName : fileName} },
		  onSuccess: function(res) {
			  tabStr.remove();
		  }
		});
	} else {
		return false;
	}
}

