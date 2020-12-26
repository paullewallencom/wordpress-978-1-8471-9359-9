// setup everything when document is ready
jQuery(document).ready(function($) {
	  
        $('#wallform').ajaxForm({ 
          // target identifies the element(s) to update with the server response 
        target: '#wallcomments', 
       
        // handler function for success event
        success: function(responseText, statusText) {            
            
            $('#wallresponse').html('<span class="wall-success">'+'Thank you for your comment!'+'</span>');               
        } ,
        
        // handler function for errors
        error: function(request) {                        
            
            // parse the response for WordPress error
            if (request.responseText.search(/<title>WordPress &rsaquo; Error<\/title>/) != -1) {
            	
							var data = request.responseText.match(/<p>(.*)<\/p>/);
							$('#wallresponse').html('<span class="wall-error">'+ data[1] +'</span>');
					} else {
							
							$('#wallresponse').html('<span class="wall-error">An error occurred, please notify the administrator.</span>');
					}                                    
        } ,
        beforeSubmit: function(formData, jqForm, options) { 
        	
        	// clear response div
        	$('#wallresponse').empty();                                
        }              
    });                  	
});