jQuery(document).ready(function($) {

	snazzy_mini=parseInt(SnazzySettings.snazzy_mini);

  if (snazzy_mini)
		$('.sz_day').hide();
		
  $('.sz_date_mon').click(function(){     	    
 	    		$(this).next('.sz_month').children('.sz_day').toggle();	    
  });
  
  $('.sz_date_day').click(function(){     
 	    $(this).next('.sz_day').slideToggle();
  });



});