<?php
  
  /*
   Plugin Name: Insights
   Version: 0.1
   Plugin URI: http://www.prelovac.com/vladimir/wordpress-plugins/insights
   Author: Vladimir Prelovac
   Author URI: http://www.prelovac.com/vladimir
   Description: Quickly find relevant posts and Flickr images for your article
   
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
  
  $exit_msg = 'Insights for WordPress requires WordPress 2.6 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';
  
  if (version_compare($wp_version, "2.6", "<")) {
      exit($exit_msg);
  }
  
  
  // Avoid name collisions.
  if (!class_exists('WPInsights'))
      : class WPInsights
      {
          // name for our options in the DB
          var $DB_option = 'WPInsights_options';
          
          // the plugin URL
          var $plugin_url;
          
          // Initialize WordPress hooks
          function WPInsights()
          {
              $this->plugin_url = trailingslashit( WP_PLUGIN_URL.'/'. dirname( plugin_basename(__FILE__) );
              
              // admin_menu hook
              add_action('admin_menu', array(&$this, 'admin_menu'));
              
              // print scripts action
              add_action('admin_print_scripts-post.php', array(&$this, 'scripts_action'));
              add_action('admin_print_scripts-page.php', array(&$this, 'scripts_action'));
              add_action('admin_print_scripts-post-new.php', array(&$this, 'scripts_action'));
              add_action('admin_print_scripts-page-new.php', array(&$this, 'scripts_action'));
              
              // add tinyMCE handlig
              //  add_action( 'init', array( &$this, 'add_tinymce' ));
          }
          
          // Hook the admin menu
          function admin_menu()
          {
              // custom panel for edit post
              add_meta_box('WPInsights', 'Insights', array(&$this, 'draw_panel'), 'post', 'normal', 'high');
              
              // custom panel for edit page
              add_meta_box('WPInsights', 'Insights', array(&$this, 'draw_panel'), 'page', 'normal', 'high');
              
              //add_action('submitpost_box', array( &$this, 'my_sidebar' ) );      
          }
          
          // prints the scripts
          function scripts_action()
          {
              $nonce = wp_create_nonce('insights-nonce');
              
              wp_enqueue_script('jquery');
              wp_enqueue_script('insights', $this->plugin_url . '/insights.js', array('jquery'));
              wp_localize_script('insights', 'InsightsSettings', array('insights_url' => $this->plugin_url, 'nonce' => $nonce));
          }
          
          function my_sidebar()
          {
              echo '<p> Hello World! </p>';
          }
          
          // draw the panel
          function draw_panel()
          {
              echo '
    <p>Enter keywords you would like to search for and press the Search button.</p>
    
    <input name="insights-radio" type="radio" checked="" value="1" /><label> Posts </label>
    <input name="insights-radio" type="radio" value="2"/><label> Images </label>
    
    <br /> 
    
    <input type="text" id="insights-search" name="insights-search" size="25" autocomplete="off" />
    <input id="insights-submit" class="button" type="button" value="Search"  />';
              
              echo '<div id="insights-results"></div>';
          }
          
          // Set up everything
          function install()
          {
          }
          
          
          // tinyMCE functionality handler
          function add_tinymce()
          {
              if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
                  return;
              
              if (get_user_option('rich_editing') == 'true') {
                  add_filter('mce_external_plugins', array(&$this, 'add_tinymce_plugin'));
                  add_filter('mce_buttons', array(&$this, 'add_tinymce_button'));
              }
          }
          
          
          function add_tinymce_plugin($plugin_array)
          {
              $plugin_array['insights'] = $this->plugin_url . '/insights-mceplugin.js';
              return $plugin_array;
          }
          
          function add_tinymce_button($buttons)
          {
              array_push($buttons, "separator", 'btnInsights');
              return $buttons;
          }
      }
  
  endif;
  
  
  if (class_exists('WPInsights'))
      : $WPInsights = new WPInsights();
  if (isset($WPInsights)) {
      register_activation_hook(__FILE__, array(&$WPInsights, 'install'));
  }
  endif;
?>