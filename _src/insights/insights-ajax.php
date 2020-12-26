<?php
  require_once('../../../wp-config.php');
  
  if ($_GET['search']) {
      // check security
      check_ajax_referer('insights-nonce');
      
      if ($_GET['mode'] == '2')
          // mode 2 is image search
          die(search_images($_GET['search']));
      else
          die(search_posts($_GET['search']));
  } else
      die('No results found.');
  
  
  // search posts
  function search_posts($search)
  {
      global $wpdb, $WPInsights;
      
      // create query
      $search = $wpdb->escape($search);
      $posts = $wpdb->get_results("SELECT ID, post_title, post_content FROM $wpdb->posts WHERE post_status = 'publish' AND (post_title LIKE '%$search%' OR post_content LIKE '%$search%') ORDER BY post_title LIMIT 0,5");
      
      // 
      if ($posts)
          foreach ($posts as $post) {
              // display every post link and excerpt
              $output .= '
              <p>
              <a onclick="insert_link(\'' . get_permalink($post->ID) . '\'); return false;" style="cursor:pointer;"  >
              <strong>' . $post->post_title . '</strong>
              </a><br />
              ' . get_excerpt($post->post_content, 25) . '</p>';
          } else
          $output .= 'No posts matched "' . stripslashes($search) . '"';
      
      return $output;
  }
  
  // handle Flickr photos
  function search_images($keyword)
  {
      // search by tags  
      $tag_images = search_flickr($keyword, 'tags');
      
      // search by description     
      $text_images = search_flickr($keyword, 'text');
      
      // if any results
      if ($tag_images || $text_images) {
          // output image size selection box
          $output = '
    Image size:<br /><select id="img_size">
    <option value="_s">Thumbnail (75px)</option>
    <option value="_t">Small (100px)</option>
    <option value="_m" selected="selected">Normal (240px)</option>
    <option value="">Medium (500px)</option>
    <option value="_b">Large (1024px)</option>
    </select>
    <br />';
          
          // output images
          if ($tag_images)
              $output .= $tag_images;
          
          if ($text_images)
              $output .= $text_images;
      } else
          $output = 'No images matched "' . stripslashes($keyword) . '"';
      
      return $output;
  }
  
  // call the Flickr Api
  function search_flickr($keyword, $mode = 'tags', $count = 16)
  {
      // prepare Flickr query
      $params = array('api_key' => '72c75157d9ef89547c5a7b85748106e4', 'method' => 'flickr.photos.search', 'format' => 'php_serial', 'tag_mode' => 'any', 'per_page' => $count, 'sort' => 'interestingness-desc', 'license' => '4,6,7', $mode => $keyword);
      
      $encoded_params = array();
      foreach ($params as $k => $v) {
          // encode parameters    
          $encoded_params[] = urlencode($k) . '=' . urlencode($v);
      }
      
      // call the Flickr API  
      $url = "http://api.flickr.com/services/rest/?" . implode('&', $encoded_params);
      
      
      $rsp = wp_remote_fopen($url);
      
      
      // decode the response
      $rsp_obj = unserialize($rsp);
      
      // if we have photos
      if ($rsp_obj && $rsp_obj['photos']['total'] > 0) {
          foreach ($rsp_obj['photos']['photo'] as $photo) {
              // link to photo page
              $link = 'http://www.flickr.com/photos/' . $photo['owner'] . '/' . $photo['id'];
              
              // img src link
              $src = 'http://farm' . $photo['farm'] . '.static.flickr.com/' . $photo['server'] . '/' . $photo['id'] . '_' . $photo['secret'];
              
              // create output      
              $output .= '<img hspace="2" vspace="2" src="' . $src . '_s.jpg" title="' . $photo['title'] . '" onclick="insert_image(\'' . $link . '\', \'' . $src . '\', \'' . str_replace("'", "&acute;", $photo['title']) . '\');" />';
          }
      }
      
      return $output;
  }
  
  // get the content excerpt
  function get_excerpt($text, $length = 25)
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
?>