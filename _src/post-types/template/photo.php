<div class="wrap">	
	
	<?php	if (!empty($error)) : ?>	
		<div id="message" class="error fade">
			<p><?php echo $error; ?></p>
		</div>	
	<?php	elseif (!empty($published_id)) : ?>	
		<div id="message" class="updated fade">
			<p><strong><?php _e('Photo added.',$this->plugin_domain); ?></strong> <a href="<?php echo get_permalink($published_id); ?>"><?php _e('View post',$this->plugin_domain); ?> &raquo;</a></p>
		</div>	
	<?php endif; ?>
		
	<h2><?php _e('Add Photo',$this->plugin_domain); ?></h2>
	<form action="" method="post" enctype="multipart/form-data">
	<?php wp_nonce_field($_GET['page']); ?>
			
		<div id="poststuff">
				
			<div class="submitbox" id="submitpost">
				<div id="previewview"></div>
				<div class="inside"></div>
				<p class="submit"><input name="publish" type="submit" class="button button-highlighted" tabindex="5" value="<?php if (current_user_can('publish_posts')) _e('Publish', $this->plugin_domain); else _e('Submit', $this->plugin_domain); ?>" /></p>
			</div>
	
			<div id="post-body">
				
				<div id="titlediv">
					<h3><?php _e('Title',$this->plugin_domain); ?></h3>
					<div id="titlewrap"><input type="text" name="title"  tabindex="1" value="<?php echo $title; ?>" id="title" /></div>
				</div>
				
				<div class="postbox ">
					<h3><?php _e('Photo',$this->plugin_domain); ?></h3>
					<div class="inside">		
						 <p>
					  	<label for="url"><?php _e('Enter URL:',$this->plugin_domain); ?></label><br />
							<input style="width: 415px"  tabindex="2" type="text" name="url" id="url" value="<?php echo $url; ?>" />					
						</p>																		
					  <?php if ($uploadfile) : ?>
					 	<p>
							<label for="upload"><?php _e('or Upload Photo:',$this->plugin_domain); ?></label><br />
					  	<input type="file"  tabindex="3" name="upload" id="upload" />
					  </p>
						<?php endif; ?>
					</div>
				</div>
	
				<div class="postbox">
					<h3><?php _e('Description (optional)',$this->plugin_domain); ?></h3>					
					<div class="inside">					
						<textarea name="description" id="description" rows="5" style="width: 415px" tabindex="4"><?php echo $description; ?></textarea>	
					</div>
				</div>
			
			</div>
		</div>
	</form>	
</div>

