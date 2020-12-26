<?php
  /*
   Plugin Name: Snazzy Archives
   Version: 0.1
   Plugin URI: http://www.prelovac.com/vladimir/wordpress-plugins/snazzy-archives
   Author: Vladimir Prelovac
   Author URI: http://www.prelovac.com/vladimir
   Description: Express your blog through a unique representation of your post archives.
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
  
  $exit_msg = 'Snazzy Archives require WordPress 2.6 or newer. <a href="http://codex.wordpress.org/Upgrading_WordPress">Please update!</a>';
  
  if (version_compare($wp_version, "2.6", "<")) {
      exit($exit_msg);
  }
  
  
  // Avoid name collisions.
  if (!class_exists('SnazzyArchives'))
      : class SnazzyArchives
      {
          // this variable will hold url to the plugin  
          var $plugin_url;
          
          // name for our options in the DB
          var $db_option = 'SnazzyArchives_Options';
          
          // path to store the cache file
          var $cache_path;
          
          // Initialize the plugin
          function SnazzyArchives()
          {
              $this->plugin_url = trailingslashit( WP_PLUGIN_URL.'/'. dirname( plugin_basename(__FILE__) );
           		$this->cache_path = ABSPATH .'wp-content/';

              
              // add shortcode handler
              add_shortcode('snazzy-archive', array(&$this, 'display'));
              
              // print scripts action
              add_action('wp_print_scripts', array(&$this, 'scripts_action'));
              
              // add options Page
              add_action('admin_menu', array(&$this, 'admin_menu'));
              
              // delete output cache
           		add_action( 'edit_post',  array(&$this,'delete_cache'));
							add_action( 'save_post',  array(&$this,'delete_cache')); 
          }
          
          // hook the options page
          function admin_menu()
          {
              add_options_page('Snazzy Archives Options', 'Snazzy Archives', 8, basename(__FILE__), array(&$this, 'handle_options'));
          }
          
          
          function scripts_action()
          {
              $options = $this->get_options();
              
              $mini = $options['mini'] ? 1 : 0;
              
              wp_enqueue_script('jquery');
              wp_enqueue_script('snazzy', $this->plugin_url . '/snazzy-archives.js', array('jquery'));
              
              // JavaScript options
              wp_localize_script('snazzy', 'SnazzySettings', array('snazzy_mini' => $mini));
              
              echo '<link rel="stylesheet" href="' . $this->plugin_url . '/snazzy-archives.css" type="text/css" />';
          }
          
          // handle plugin options
          function get_options()
          {
              // default values
              $options = array('years' => '2008#So far so good!', 'mini' => '', 'posts' => 'on', 'pages' => '');
              
              // get saved options
              $saved = get_option($this->db_option);
              
              // assign them
              if (!empty($saved)) {
                  foreach ($saved as $key => $option)
                      $options[$key] = $option;
              }
              
              // update the options if necessary
              if ($saved != $options)
                  update_option($this->db_option, $options);
              
              //return the options  
              return $options;
          }
          
          // Set up everything
          function install()
          {
              // set default options
              $this->get_options();
          }
          
          
          
          function display()
          {
              global $wpdb;
              
     					// try to retrieve cache
							$data = @file_get_contents($this->cache_path."snazzy_cache.htm");
							
							// return the cache data if it exists
							if ($data)
								return $data;

              
              // these variables store the current year, month and date processed            
              $curyear = '';
              $curmonth = '';
              $curday = '';
              
              // the beginning of our output
              $result = '
          <div class="snazzy">    
            <table cellspacing="15" cellpadding="0" border="0">
              <tbody>
                <tr>';
              
              $options = $this->get_options();
              
              // parse year descriptions
              if (!empty($options['years'])) {
                  $yrs = array();
                  foreach (explode("\n", $options['years']) as $line) {
                      list($year, $desc) = array_map('trim', explode("#", $line, 2));
                      if (!empty($year))
                          $yrs[$year] = stripslashes($desc);
                  }
              }
              
              //parse post options
              $types = array();
              if ($options['posts'])
                  array_push($types, "'post'");
              if ($options['pages'])
                  array_push($types, "'page'");
              
              $types = implode(',', $types);
              
              // query to get all published posts  
              $query = "SELECT * FROM $wpdb->posts WHERE post_status = 'publish' AND post_password='' AND post_type IN ($types) ORDER BY post_date_gmt DESC ";
              
              $posts = $wpdb->get_results($query);
              
              foreach ($posts as $post) {
                  // retrieve post information we need
                  $title = $post->post_title;
                  $excerpt = $this->get_excerpt($post->post_content);
                  $url = get_permalink($post->ID);
                  $date = strtotime($post->post_date);
                  
                  // format the date
                  $day = date('d', $date);
                  $month = date('M', $date);
                  $year = date('Y', $date);
                  
                  // look for image in the post content
                  $imageurl = "";
                  
                  preg_match('/<\s*img [^\>]*src\s*=\s*[\""\']?([^\""\'>]*)/i', $post->post_content, $matches);
                  $imageurl = $matches[1];
                  
                  // get comments for this post
                  $comcount = $wpdb->get_var("
                      SELECT COUNT(*)
                      FROM $wpdb->comments 
                      WHERE comment_approved = '1' AND comment_post_ID=$post->ID AND NOT (comment_type = 'pingback' OR comment_type = 'trackback')                        
                    ");
                  
                  // additional formatiing
                  if ($year != $curyear) {
                      // close the previous day/month
                      if ($curday)
                          $result .= "</div></div></td>";
                      
                      $curday = '';
                      $curmonth = '';
                      
                      // year start in a new column (<td>)
                      $result .= '<td valign="top"><div class="sz_date_yr">' . $year . '</div><div class="sz_cont">';
                      
                      if ($yrs[$year])
                          $result .= '<div class="sz_year">&#8220;' . $yrs[$year] . '&#8221;</div>';
                      
                      $result .= '</div></td>';
                      $curyear = $year;
                  }
                  
                  
                  if ($month != $curmonth) {
                      // close the previous day/month
                      if ($curday)
                          $result .= "</div></div></td>";
                      
                      $curday = '';
                      // month starts in a new column (<td>)
                      $result .= '<td valign="top"><div class="sz_date_mon">' . $month . '</div><div class="sz_month">';
                      
                      $curmonth = $month;
                  }
                  
                  if ($day != $curday) {
                      // close previous day
                      if ($curday)
                          $result .= "</div>";
                      
                      $result .= '<div class="sz_date_day">' . $day . '</div><div class="sz_day">';
                      $curday = $day;
                  }
                  
                  // retrieve the archive entry representation      
                  ob_start();
                  include('snazzy-layout-1.php');
                  $output = ob_get_contents();
                  ob_end_clean();
                  
                  $result .= $output;
              }
              
              // close the previous day/month
              if ($curday)
                  $result .= "</div></div></td>";
              
              // close the main page elements                    
              $result .= "</tr></tbody></table></div>";
						  
						  // write cache
							if (is_writeable($this->cache_path))
									@file_put_contents($this->cache_path."snazzy_cache.htm", $result);

              
              // return the result
              return $result;
          }
          
          function get_excerpt($text, $length = 15)
          {
              if (!$length)
                  return $text;
              
              $text = strip_tags($text);
              $words = explode(' ', $text, $length + 1);
              if (count($words) > $length) {
                  array_pop($words);
                  array_push($words, '...');
                  $text = implode(' ', $words);
              }
              return $text;
          }
          
          // handle the options page
          function handle_options()
          {
              $options = $this->get_options();
              
              if (isset($_POST['submitted'])) {
              		
              		//check security
              		check_admin_referer('snazzy-nonce');
              		
                  $options = array();
                  
                  $options['years'] = htmlspecialchars($_POST['years']);
                  $options['mini'] = $_POST['mini'];
                  $options['posts'] = $_POST['posts'];
                  $options['pages'] = $_POST['pages'];
                  
                  update_option($this->db_option, $options);
                  
                  echo '<div class="updated fade"><p>Plugin settings saved.</p></div>';
              }
              
              $layout = $options['layout'];
              $years = stripslashes($options['years']);
              $mini = $options['mini'] == 'on' ? 'checked' : '';
              $posts = $options['posts'] == 'on' ? 'checked' : '';
              $pages = $options['pages'] == 'on' ? 'checked' : '';
              
              // URL for form submit, equals our current page
              $action_url = $_SERVER['REQUEST_URI'];
              
              include('snazzy-archives-options.php');
          }
      }
  
  else
      : exit("Class SnazzyArchives already declared!");
  endif;
  
  // create new instance of the class
  $SnazzyArchives = new SnazzyArchives();
  if (isset($SnazzyArchives)) {
      // register the activation function by passing the reference to our instance
      register_activation_hook(__FILE__, array(&$SnazzyArchives, 'install'));
  }
?>