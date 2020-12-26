// WP Live Blogroll JavaScript

// setup everything when document is ready
jQuery(document).ready(function($) {

		// connect to hover event of <a> in .livelinks
    $('.livelinks a').hover(function(e) {
                        
        // set the text we want to display
        // this.tip="Recent posts from " + this.href + " will be displayed here...";
        
        // create a new div and append it to the link
        $(this).append('<div id="lb_popup"></div>');					
				
				// get coordinates
        var mouseX = e.pageX || (e.clientX ? e.clientX + document.body.scrollLeft: 0);
        var mouseY = e.pageY || (e.clientY ? e.clientY + document.body.scrollTop: 0);

				// move the top left corner to the left and down
        mouseX -= 260;
        mouseY += 5;
				
				// position our div
        $('#lb_popup').css({
            left: mouseX + "px",
             top: mouseY + "px"
        });

				$.ajax({
				    type: "GET",
				    url: LiverollSettings.plugin_url + '/wp-live-blogroll-ajax.php',
				    timeout: 3000,				    
				    data: {
				        link_url: this.href,
				        _ajax_nonce: LiverollSettings.nonce
				    },
				    success: function(msg) {
				       
				        $('#lb_popup').html(msg);
				        $('#lb_popup').fadeIn(300);
				        
				    },
				    error: function(msg) {
				    	 jQuery('#lb_popup').html('Error: ' + msg.responseText);
				    	
				    }
				})
            			
    },
    // when the mouse hovers out
    function() {
				// fade out the div
        $('#lb_popup').fadeOut(100);
        
        // remove it
        $(this).children().remove();
    });

});
