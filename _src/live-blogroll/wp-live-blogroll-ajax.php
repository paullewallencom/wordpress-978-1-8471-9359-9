<?php

/*
WP Live Blogroll Ajax script
Part of a WP Live Blog Roll plugin
*/

require_once("../../../wp-config.php");
require_once(ABSPATH . WPINC . '/rss.php');


// fetch information from GET method
$link_url = $_GET['link_url'];

// return the result
WPLiveRoll_HandleAjax($link_url);

function WPLiveRoll_GetExcerpt($text, $length = 20 )
{
		$text = strip_tags($text);		
		$words = explode(' ', $text, $length + 1);
		if (count($words) > $length) {
			array_pop($words);
			array_push($words, '[...]');
			$text = implode(' ', $words);
		}	
		return $text;
}
	
function WPLiveRoll_HandleAjax($link_url)
{
		// check security
		check_ajax_referer( "wp-live-blogroll" );

    // we will return final HTML code in this variable
    $result='';
    
    // number of posts we are showing
    $number = 5;
    
    $link_url=trailingslashit($link_url);
    
    // pick the rss feed based on the site
    if (strstr($link_url,"blogspot")) {
        // blogspot blog
        $feed_url=$link_url."feeds/posts/default/";
    } else if (strstr($link_url,"typepad")) {
        // typepad blog
        $feed_url=$link_url."atom.xml";
    } else {
        // own domain or wordpress blog
        $feed_url=$link_url."feed/";
    }
    
    
    // use WordPress to fetch the RSS feed
    $feedfile = fetch_rss($feed_url);
    
    
    // check if we got valid response
    if (is_array($feedfile->items ) && !empty($feedfile->items ) ) {
        
        // slice the number of items we need
        $feedfile->items = array_slice($feedfile->items, 0, $number);
        
        // create HTML out of posts
	        $result.= '<ul>';
        foreach($feedfile->items as $item ) {
            
            // fetch the information
            $item_title = $item['title'];
            $item_link = $item['link'];
            $item_description = WPLiveRoll_GetExcerpt($item['description']);
            
            // form result
	          $result.= '<li><a class="lb_link" target="'.$link_target.'" href="'.$item_link.'" >'.$item_title.'</a><p class="lb_desc">'.$item_description.'</p></li>';
        }
	        $result.= '</ul>';
    } else {
        // in case we were unable to parse the feed
        $result.= "No posts available.";
    }
    
    // return the HTML code
    die( $result );
}

?>


