<div id="wp_wall">
	
	<div id="wallcomments">
			<?php echo WPWall_ShowComments(); ?>
	</div> 
	
	<div id="wall_post">
		<form action="<?php echo $wp_wall_plugin_url.'/wp-wall-ajax.php'; ?>" method="post" id="wallform">
			<?php wp_nonce_field('wp-wall'); ?>
		
			<?php if ( $user_ID ) : ?>
			
			<p>Logged in as <a href="<?php echo get_bloginfo('wpurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>.</p>
			
			<?php else : ?>
			
			<p>
			<label for="author"><small>Name</small></label><br/>
			<input type="text" name="author" id="author" value=""  tabindex="1"  />
			</p>
			
			<?php endif; ?>
			
			<p>
			<label for="comment"><small>Comment</small></label><br/>
			<textarea name="comment" id="comment" rows="3" tabindex="2"></textarea>
			</p>
					
			<p><input name="submit_wall_post" type="submit" id="submit_wall_post" tabindex="3" value="Submit" /></p>
			
		</form> 								
	</div>
	
	<div id="wallresponse"></div>
	
</div>