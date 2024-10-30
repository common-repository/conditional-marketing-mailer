jQuery(document).ready(function($) {
	$( "#click_to_send_email_test" ).on( "click", function() {

		var data = {
			'action': 'send_email',
			'title': $("#massage_title").val(),
			'massage': tinymce.get('massage').getContent(),			
		};
		jQuery.post(ajax_object.ajax_url, data, function(response) {
			response = JSON.parse(response);
			alert(response.massage + ' ' + response.email);
		});
	});
});