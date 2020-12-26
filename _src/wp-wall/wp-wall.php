<?php
  /*
   Plugin Name: WP Wall
   Version: 0.1
   Description: "Wall" widget that appears in your blog’s side bar. Users can add a quick comment and it will appear in the sidebar immediately (without reloading the page).
   Author: Vladimir Prelovac
   Author URI: http://www.prelovac.com/vladimir
   Plugin URI: http://www.prelovac.com/vladimir/wordpress-plugins/wp-wall
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
  
  $exit_msg = 'WP Wall requires WordPress 2.6 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';
  
  if (version_compare($wp_version, "2.6", "<")) {
      exit($exit_msg);
  }
  
  $wp_wall_plugin_url =  trailingslashit( WP_PLUGIN_URL.'/'. dirname( plugin_basename(__FILE__) );
  
  function WPWall_WidgetControl()
  {
      // get saved options
      $options = get_option('wp_wall');
      
      // handle user input
      if ($_POST["wall_submit"]) {
          // retireve wall title from the request
          $options['wall_title'] = strip_tags(stripslashes($_POST["wall_title"]));
          
          // update the options to database
          update_option('wp_wall', $options);
      }
      
      $title = $options['wall_title'];
      
      // print out the widget control    
      include('wp-wall-widget-control.php');
  }
  
  
  function WPWall_Widget($args = array())
  {
      global $user_ID, $user_identity, $wp_wall_plugin_url;
      
      // extract the parameters
      extract($args);
      
      // get our options
      $options = get_option('wp_wall');
      $title = $options['wall_title'];
      
      // print the theme compatibility code
      echo $before_widget;
      echo $before_title . $title . $after_title;
      
      // include our widget
      include('wp-wall-widget.php');
      
      echo $after_widget;
  }
  
  function WPWall_Init()
  {
      // register widget
      register_sidebar_widget('WP Wall', 'WPWall_Widget');
      
      // alternative way
      //$widget_optionss = array('classname' => 'WPWall_Widget', 'description' => "A comments 'Wall' for your sidebar." );
      //wp_register_sidebar_widget('WPWall_Widget', 'WP Wall', 'WPWall_Widget', $widget_options);
      
      // register widget control
      register_widget_control('WP Wall', 'WPWall_WidgetControl');
      
      $options = get_option('wp_wall');
      
      // get our wall pageId
      $pageId = $options['pageId'];
      
      // check if the actual post exists
      $actual_post = get_post($pageId);
      
      // check if the page is already created  
      if (!$pageId || !$actual_post || ($pageId != $actual_post->ID)) {
          // create the page and save it's ID
          $options['pageId'] = WPWall_CreatePage();
          
          update_option('wp_wall', $options);
      }
  }
  
  add_action('init', 'WPWall_Init');
  
  function WPWall_CreatePage()
  {
      // create post object
      class mypost
      {
          var $post_title;
          var $post_content;
          // draft, published… 
          var $post_status;
          // can be 'page' or 'post' 
          var $post_type;
          // open or closed for commenting
          var $comment_status;
      }
      
      // initialize the post object
      $mypost = new mypost();
      
      // fill it with data
      $mypost->post_title = 'WP Wall Guestbook';
      $mypost->post_content = 'Welcome to my WP Wall Guestbook!';
      $mypost->post_status = 'draft';
      $mypost->post_type = 'page';
      $mypost->comment_status = 'open';
      
      // insert the post and return it's ID
      return wp_insert_post($mypost);
  }
  
  add_action('wp_head', 'WPWall_HeadAction');
  
  function WPWall_HeadAction()
  {
      global $wp_wall_plugin_url;
      
      echo '<link rel="stylesheet" href="' . $wp_wall_plugin_url . '/wp-wall.css" type="text/css" />';
  }
  
  add_action('wp_print_scripts', 'WPWall_ScriptsAction');
  
  function WPWall_ScriptsAction()
  {
      if (!is_admin()) {
          global $wp_wall_plugin_url;
          
          wp_enqueue_script('jquery');
          wp_enqueue_script('jquery-form');
          wp_enqueue_script('wp_wall_script', $wp_wall_plugin_url . '/wp-wall.js', array('jquery', 'jquery-form'));
      }
  }
  
  function WPWall_ShowComments()
  {
      global $wpdb;
      
      // get our page id  
      $options = get_option('wp_wall');
      $pageId = $options['pageId'];
      
      // number of comments to display
      $number = 5;
      
      $result = '';
      
      // get comments from WordPress database  
      $comments = $wpdb->get_results("
                      SELECT *
                      FROM $wpdb->comments 
                      WHERE comment_approved = '1' AND comment_post_ID=$pageId AND NOT (comment_type = 'pingback' OR comment_type = 'trackback')
                      ORDER BY comment_date_gmt DESC 
                      LIMIT $number
                    ");
      
      
      
      if ($comments) {
          // display comments one by one
          foreach ($comments as $comment) {
              $result .= '<p><span class="wallauthor">' . $comment->comment_author . '</span><span class="wallcomment">: ' . $comment->comment_content . '</span></p>';
          }
      }
      
      return $result;
  }
?>