// Insights for WordPress plugin

// send html to the editor
function send_wp_editor(html)
{	
	var win = window.dialogArguments || opener || parent || top;
	win.send_to_editor(html);
	
	// alternatively direct tinyMCE command for insert
	// tinyMCE.execCommand("mceInsertContent", false, html);
}

// insert link to the editor
function insert_link(html_link) {
		if ((typeof tinyMCE != "undefined") && ( edt = tinyMCE.getInstanceById('content') ) && !edt.isHidden() ) {
    	
    	var sel = edt.selection.getSel();
    	  	
    	if (sel)
    	{
    		var link = '<a href="' + html_link + '" >' + sel + '</a>';

      	send_wp_editor(link);
    	}
    }
}

// insert image to the editor
function insert_image(link, src, title) {
    
    var size = document.getElementById('img_size').value;
    var img = '<a href="' + link + '"><img src="' + src + size + '.jpg" alt="' + title + '" title="' + title + '" hspace="5" border="0" /></a>';

    send_wp_editor(img);
}

// setup everything when document is ready
jQuery(document).ready(function($) {
	
		// initialize the variables
		var last_query=undefined;
		
		// function to submit rhe query and get results
    function submit_me() {
    	
    		// check if the search string is empty
    		if ($('#insights-search').val().length==0) {
    			$('#insights-results').html('');
    			return;
    		}
    	    	       
        // get active radio checkbox
        var mode = $("input[@name='insights-radio']:checked").val();
        
        // create the query
        var query = InsightsSettings.insights_url + '/insights-ajax.php?search=' + escape($('#insights-search').val()) + '&mode=' + mode + '&_ajax_nonce=' + InsightsSettings.nonce;
        
        // check if already called
        if (query!=last_query)
        {        
        	$('#insights-results').html('Please wait...');
        	$('#insights-results').load(query);
        	last_query=query;
      	}
    }

		// search button click event
    $('#insights-submit').click(function() {
        submit_me();
    });

		// check for ENTER or ArrowDown keys
    $('#insights-search').keypress(function(e) {
        if (e.keyCode == 13 || e.keyCode == 40) {
            submit_me();
            return false;
        }

    });
		
});