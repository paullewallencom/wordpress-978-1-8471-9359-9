<?php
  require_once("../../../wp-config.php");
  
  if ($_POST['submit_wall_post']) {
      // security check
      check_ajax_referer('wp-wall');
      
      $options = get_option('wp_wall');
      
      $comment_post_ID = $options['pageId'];
      $actual_post = get_post($comment_post_ID);
      
      // sanity check to see if our page exists
      if (!$comment_post_ID || !$actual_post || ($comment_post_ID != $actual_post->ID)) {
          wp_die('Sorry, there was a problem posting your comment. Please try again.');
      }
      
      // extract data we need  
      $comment_author = trim(strip_tags($_POST['author']));
      $comment_content = trim($_POST['comment']);
      
      // If the user is logged in get his name  
      $user = wp_get_current_user();
      if ($user->ID)
          $comment_author = $user->display_name;
      
      // check if the fields are filled    
      if ('' == $comment_author)
          wp_die('Error: please type a name.');
      
      if ('' == $comment_content)
          wp_die('Error: please type a comment.');
      
      // insert the comment
      $commentdata = compact('comment_post_ID', 'comment_author', 'comment_content', 'user_ID');
      
      $comment_id = wp_new_comment($commentdata);
      
      // check if the comment is approved
      $comment = get_comment($comment_id);
      
      if ($comment->comment_approved == 0)
          wp_die('Your comment is awaiting moderation.');
      
      // return status
      die(WPWall_ShowComments());
  }
?>