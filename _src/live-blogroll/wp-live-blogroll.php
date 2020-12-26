<?php

/*
Plugin Name: Live Blogroll
Version: 0.1
Description: Shows a number of 'recent posts' for each link in your Blogroll using Ajax.
Author: Vladimir Prelovac
Author URI: http://www.prelovac.com/vladimir
Plugin URI: http://www.prelovac.com/vladimir/wordpress-plugins/live-blogroll
*/

/*  
Copyright 2008  Vladimir Prelovac  (email : vprelovac@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


global $wp_version;	

$exit_msg='Live BlogRoll requires WordPress 2.6 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';

if (version_compare($wp_version,"2.6","<"))
{
	exit ($exit_msg);
}

$wp_live_blogroll_plugin_url = trailingslashit( WP_PLUGIN_URL.'/'. dirname( plugin_basename(__FILE__) );

require_once(ABSPATH . WPINC . '/rss.php');

//add_filter('get_bookmarks', WPLiveRoll_GetBookmarksFilter);

function WPLiveRoll_GetBookmarksFilter($items)
{
    
    // do nothing if in the admin menu
    if (is_admin()) {
        return $items;
    }
   
    // parse all blogroll items
    foreach($items as $item)
    {
    		// check if the link is public
    		if ($item->link_visible=='Y') {
		        $link_url=trailingslashit($item->link_url);
		        
		        // simple feed guessing
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
		        		// this is the last post
		            $feeditem=$feedfile->items[0];
		            
		            // replace name and url with post link and title
		            $item->link_url=$feeditem['link'];
		            $item->link_name=$feeditem['title'];		            
		        }
      	}
        
    }
    // return the items back
    return $items;
}


add_filter('wp_list_bookmarks', WPLiveRoll_ListBookmarksFilter);

function WPLiveRoll_ListBookmarksFilter($content)
{
	return '<span class="livelinks">'.$content.'</span>';
}

add_action('wp_print_scripts', 'WPLiveRoll_ScriptsAction');

function WPLiveRoll_ScriptsAction() 
{
	global $wp_live_blogroll_plugin_url;
	
	if (!is_admin())
	{
		// create a nonce
		$nonce = wp_create_nonce('wp-live-blogroll');
		
		wp_enqueue_script('jquery');
		wp_enqueue_script('wp_live_roll_script', $wp_live_blogroll_plugin_url.'/wp-live-blogroll.js', array('jquery')); 				
		
		// pass parameters to JavaScript
		wp_localize_script('wp_live_roll_script', 'LiverollSettings', array('plugin_url' => $wp_live_blogroll_plugin_url, 'nonce' => $nonce));
	}
}

add_action('wp_head', 'WPLiveRoll_HeadAction' );

function WPLiveRoll_HeadAction()
{
	global $wp_live_blogroll_plugin_url;
	
	echo '<link rel="stylesheet" href="'.$wp_live_blogroll_plugin_url.'/wp-live-blogroll.css" type="text/css" />'; 
}


?>