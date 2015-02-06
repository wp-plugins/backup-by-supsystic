var bupAdminSheduleCheckboxChanged = false;
var goToScheduleTab = false;
jQuery(document).ready(function(){

	jQuery('#bupAdminScheduleForm').submit(function(){
	  bupAdminSheduleCheckboxChanged = false;
	  jQuery(this).sendFormBup({
		msgElID: 'bupAdminScheduleMsg'
	  });
	  return false;
	 });
	 
	 jQuery('.everyScheduleBup input[type=checkbox]').change(function() {
		bupAdminSheduleCheckboxChanged = true;
	 });
	 
	 checkActSchedule()
	 
	 jQuery('.bupScheduleEnableCheckbox').change(function() {
		 checkActSchedule();
	 });
	 
 
});

function checkActSchedule(){
if (jQuery('.bupScheduleEnableCheckbox').prop('checked')){
		 activateSchedule();
	 } else {
		 deactivateSchedule();
 	 }
}
function activateSchedule(){
	jQuery('.bupScheluleFieldset').removeClass('disableScheduleFieldset');
	jQuery('.bupScheduleCheckbox').prop('disabled', false);
	jQuery('#bupScheduleTime').prop('disabled', false);
}

function deactivateSchedule(){
	jQuery('.bupScheluleFieldset').addClass('disableScheduleFieldset');
	jQuery('.bupScheduleCheckbox').prop('disabled', true);
	jQuery('#bupScheduleTime').prop('disabled', true);
}