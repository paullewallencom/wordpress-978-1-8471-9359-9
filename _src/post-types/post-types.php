<?php

// pluginname Post Types
// shortname PostTypes
// dashname post-types

/*
Plugin Name: Post Types
Version: 0.1
Plugin URI: http://www.prelovac.com/vladimir/wordpress-plugins/post-types
Author: Vladimir Prelovac
Author URI: http://www.prelovac.com/vladimir
Description: Provides pre-defined post templates to quickly add a photo or a link to your blog

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


// Avoid name collisions.
if ( !class_exists('PostTypes') ) :

class PostTypes {
			
	// localization domain
	var $plugin_domain='PostTypes';
	
	// Initialize the plugin
	function PostTypes() {	
		global $wp_version, $pagenow;	
		
		// pages where our plugin needs translation
		$local_pages=array('plugins.php', 'post-new.php', 'edit.php');
				
		if (in_array($pagenow, $local_pages))
			$this->handle_load_domain();

		$exit_msg=__('Post Types requires WordPress 2.5 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>', $this->plugin_domain);
		
		if (version_compare($wp_version,"2.5","<"))
		{
			exit ($exit_msg);
		}
		
		// initialize the error class
		$this->error = new WP_Error();
		$this->init_errors();			
		
		// add admin_menu action
		add_action('admin_menu',  array(&$this, 'admin_menu')); 			
	}
	
	// Hook to admin menu
	function admin_menu() {		
		global $submenu, $menu;
		
		// remove 'Link' from Write menu
		unset($submenu['post-new.php'][15]); 
		
		
		// submenu pages				  
	  add_submenu_page('post-new.php', __('Add Photo',$this->plugin_domain) , __('Photo', $this->plugin_domain)	, 1 	, 'add-photo',  array(&$this, 'display_form') ); 	  		
	  add_submenu_page('post-new.php', __('Add URL', $this->plugin_domain) ,  __('URL', $this->plugin_domain)	, 1 	, 'add-url',  array(&$this, 'display_form') ); 	  	 
		
		
		// handle Manage page hooks
		add_action('load-edit.php', array(&$this, 'handle_load_edit') );  
	} 
	
	// Init error messages	
	function init_errors()
	{
		$this->error->add('e_image', __('Please upload a valid image.',$this->plugin_domain));
		$this->error->add('e_title', __('You need to enter a title and add a photo.',$this->plugin_domain));
		$this->error->add('e_url', __('You need to enter a URL.',$this->plugin_domain));			
	}
	
	// Retrieve an error message
	function my_error($e = '') {
		
		$msg = $this->error->get_error_message($e);
		
		if ($msg == null) {
			return __("Unknown error occured, please contact the administrator.", $this->plugin_domain);
		}
		return $msg;
	}

	// Display the Post form	
	function display_form() {
									
			global $wpdb;
			
			$page=$_GET['page'];
			$published=isset($_POST['publish']);
			$title=$_POST['title'];	
			$description=$_POST['description'];										
			
			
			if ($published)
			{										
					check_admin_referer($page);									
					$post_status = current_user_can('publish_posts') ? 'publish' : 'pending'; 
			}
					
			switch ($page) :
			
				case 'add-photo':	
					// WordPress upload dir (wp-content/uploads)
					$uploads = wp_upload_dir();											
					
					// check permissions
					if (is_writable($uploads['path']) && current_user_can('upload_files')) {						
						$uploadfile=true;
					} 		
									
					$url=$_POST['url'];	
					$upload=$_FILES['upload'];
					
					if ($published)
					{ 
						if (!empty($title) && (!empty($upload['tmp_name']) || !empty($url)))
						{
							// if file uploaded
							if ($upload['tmp_name'])
							{																				
								// handle uploaded image
								$file=$this->handle_image_upload($upload);
								
								if ($file)
								{
									$image_url=$file['url'];
									
									// create a thumbnail
									$size='medium';
									$resized = image_make_intermediate_size( $file['file'], get_option("{$size}_size_w"), get_option("{$size}_size_h"), get_option("{$size}_crop") ); 															
										
									if ($resized)									
										$image_src=$uploads['url'] .'/'.$resized['file'];
									else 
										$image_src=$image_url;
																				
									$image_uploaded=true;																		
								}
								else
									$error=$this->my_error('e_image');										
							}
							else // if file uploaded
							{
								$image_url=$url;
								$image_src=$url;
							}			
							
							if (!$error) {
								// create post content			
								$content='<a href="'.$image_url.'"><img src="'.$image_src.'"></a><p>'.$description.'</p>';
								
								// post information
								$data = array(
									'post_title' => $wpdb->escape($title),
									'post_content' => $wpdb->escape($content),							
									'post_status' => $post_status							
								);
														
								// insert post
								$published_id = wp_insert_post($data); 														
								
								if ($image_uploaded)
								{
									$attachment = array(
										'post_mime_type' => $file['type'],
										'guid' => $image_url,
										'post_parent' => $published_id,
										'post_title' => $wpdb->escape($title),
										'post_content' => $wpdb->escape($description),		
									);
									
									// add a custom field
									add_post_meta($published_id, "post-type",	 __('Photo',$this->plugin_domain)); 
	
									// insert post attachment
									$aid = wp_insert_attachment($attachment, $file['file'], $published_id);	
									
									// update metadata
									if ( !is_wp_error($aid) ) {		
										wp_update_attachment_metadata( $aid, wp_generate_attachment_metadata( $aid, $file['file'] ) );
									}
									
								}
								
								// clear all fields
								$title=''; $url=''; $description='';															
							}							
						}
						else						
							$error=$this->my_error('e_title');			
					}
						
					include( 'template/photo.php'); 
					break;
					
				case 'add-url':						
					$url=$_POST['url'];				
										
					if ($published)
					{
						if (!empty($url))
						{
							if (empty($title))
								$title=$url;
								
							$content='<a href="'.$url.'">'.$title.'</a><p>'.$description.'</p>';
							$data = array(
								'post_title' => $wpdb->escape($title),
								'post_content' => $wpdb->escape($content),							
								'post_status' => $post_status							
							);
													
							// insert post
							$published_id = wp_insert_post($data); 
							
							// add a custom field
							add_post_meta($published_id, "post-type", __('Link',$this->plugin_domain)); 
							
							// clear all fields						
							$title=''; $url=''; $description='';								
						}
						else						
							$error=$this->my_error('e_url');							
					}
					
					include( 'template/link.php'); 
					break;
														
			endswitch;							
		
	}	
	
	function array_change_key_name( $orig, $new, &$array )
	{
    foreach ( $array as $k => $v )
        $return[ ( $k === $orig ) ? $new : $k ] = $v;
    return ( array ) $return;
	}
	
	function handle_image_upload($upload)
	{
		// check if image				
		if (file_is_displayable_image( $upload['tmp_name'] ))
		{
			// handle the uploaded file
			$overrides = array('test_form' => false); 
			$file=wp_handle_upload($upload, $overrides);																		
		}
		return $file;
	}
	
	// Handle Column header
	function handle_posts_columns($columns) {
    // add 'type' column
    $columns['type'] = __('Type',$this->plugin_domain);
    
    // remove 'author' column
    //unset($columns['author']);
    
    // change 'date' column
    $columns = $this->array_change_key_name( 'date', 'date_new', $columns );    
    	    
    return $columns;
	}
	
	// Handle Type column display
	function handle_posts_custom_column($column_name, $id) {
	    // 'type' column handling based on post type
	    if( $column_name == 'type' ) {
	        $type=get_post_meta($id, 'post-type', true);	        
	        echo $type ? $type : __('Normal',$this->plugin_domain);
	    }
	    
	    // new date column handling
      if( $column_name == 'date_new' ) {
	        the_time('Y-m-d <br \> g:i:s a');
	    }
	}	

	// Manage page hooks
	function handle_load_edit() {  
		
		// handle Manage screen functions
	  add_filter('manage_posts_columns', array(&$this, 'handle_posts_columns'));
		add_action('manage_posts_custom_column', array(&$this, 'handle_posts_custom_column'), 10, 2); 	
		
		// handle search box filter
    add_filter('posts_where', array(&$this, 'handle_posts_where'));  
    add_action('restrict_manage_posts', array(&$this, 'handle_restrict_manage_posts'));  
	}  
	
	 // Handle select box for Manage page
	 function handle_restrict_manage_posts() {  
    ?>
     <select name="post_type" id="post_type" class="postform">  
         <option value="0">View all types</option>  
         <option value="normal" <?php if( $_GET['post_type']=='normal') echo 'selected="selected"' ?>><?php _e('Normal',$this->plugin_domain); ?></option>  
         <option value="photo" <?php if( $_GET['post_type']=='photo') echo 'selected="selected"' ?>><?php _e('Photo',$this->plugin_domain); ?></option>           
         <option value="link" <?php if( $_GET['post_type']=='link') echo 'selected="selected"' ?>><?php _e('Link',$this->plugin_domain); ?></option>  
     </select>  
     <?php 
    }
  
  // Handle query for Manage page  
  function handle_posts_where($where) {  
     global $wpdb;  
     if( $_GET['post_type'] == 'photo' ) {  
         $where .= " AND ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='post-type' AND meta_value='".__('Photo',$this->plugin_domain)."' )";  
     }
     else if( $_GET['post_type'] == 'link' ) {  
         $where .= " AND ID IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='post-type' AND meta_value='".__('Link',$this->plugin_domain)."' )";  
     }  
     else if( $_GET['post_type'] == 'normal' ) {  
         $where .= " AND ID NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='post-type' )";  
     }  
     return $where;  
 	} 
	
	// Localization support
	function handle_load_domain() {
		// get current language
		$locale = get_locale();
		
		// locate translation file		
		$mofile = WP_PLUGIN_DIR.'/'.plugin_basename(dirname(__FILE__)).'/lang/' . $this->plugin_domain . '-' . $locale . '.mo';		
		
		// load translation
		load_textdomain($this->plugin_domain, $mofile); 	
	}
	
	// Set up default values
	function install() {				
	}	
}

endif; 

if ( class_exists('PostTypes') ) :
	
	$PostTypes = new PostTypes();
	if (isset($PostTypes)) {
		register_activation_hook( __FILE__, array(&$PostTypes, 'install') );
	}
endif;

?>