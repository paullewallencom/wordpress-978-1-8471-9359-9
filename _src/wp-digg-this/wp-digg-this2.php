<?php

/*
Plugin Name: WP Digg This
Version: 0.1
Description: Automatically adds Digg This button to your posts.
Author: Vladimir Prelovac
Author URI: http://www.prelovac.com/vladimir
Plugin URI: http://www.prelovac.com/vladimir/wordpress-plugins/wp-digg-this
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

/* Version check */
global $wp_version;	

$exit_msg='WP Digg This requires WordPress 2.5 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';

if (version_compare($wp_version,"2.5","<"))
{
	exit ($exit_msg);
}

/* Return a Digg link */
function WPDiggThis_Link()
{
	global $post;
	
	// get the URL to the post
	$link=urlencode(get_permalink($post->ID));
	
	// get the post title
	$title=urlencode($post->post_title);
	
	// get first 350 characters of post content and strip it off HTML tags
	$text=urlencode(substr(strip_tags($post->post_content), 0, 350));
	
	// create a Digg link and return it	
	return '<a href="http://digg.com/submit?url='.$link.'&title='.$title.'&bodytext='.$text.'">Digg This</a>';				
}

/* Return a Digg button */
function WPDiggThis_Button()
{
	global $post;
	
	// get the URL to the post
	$link=get_permalink($post->ID);
	
	// get the post title
	$title=$post->post_title;
	
	// get the content	
	$text=strip_tags($post->post_content);	
	
	// replace new lines with \n for JavaScript	
	$text=str_replace(array("\r\n", "\r", "\n"), '\n', $text);		
	$text=substr($text, 0, 350);
	
	// create a Digg button and return it	
	$button="
	<script type=\"text/javascript\">
	digg_url = '$link';
	digg_title = '$title';
	digg_bodytext = '$text';
	</script>
	<script src=\"http://digg.com/tools/diggthis.js\" type=\"text/javascript\"></script>";

	// encapsulate the button in a div
	$button='
	<div style="float: left; margin-right: 10px; margin-bottom: 4px;">	
	'.$button.'
	</div>';
		
	return $button;	
}

/* Add Digg This to the post */
function WPDiggThis_ContentFilter($content)
{		
	if (is_single() || is_page())
		return WPDiggThis_Button().$content;		
//	else
	//	return $content.WPDiggThis_Link();		
	else return $content;
}

add_filter('the_content', 'WPDiggThis_ContentFilter');

//add_filter('the_title', 'WPDiggThis_ContentFilter');


	                
?>
