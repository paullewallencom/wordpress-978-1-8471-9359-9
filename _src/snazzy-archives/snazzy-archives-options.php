<div class="wrap" style="max-width:950px !important;">
	<h2>Snazzy Archives</h2>
				
	<div id="poststuff" style="margin-top:10px;">

	 <div id="mainblock" style="width:710px">
	 
		<div class="dbx-content">
		 	<form name="SnazzyArchives" action="<?php echo $action_url ?>" method="post">
					<input type="hidden" name="submitted" value="1" /> 
					
					<?php wp_nonce_field('snazzy-nonce'); ?>
					
					<h3>Usage</h3>		
					<p>Create a new page for your snazzy archive, and insert the code <strong>[snazzy-archive]</strong> into the post. Additionaly you may use the page template provided with the plugin. </p>
					<br />
					
					<h3>Options</h3>
					<p>You can choose what pages you want to show in the archives.</p>
					<input type="checkbox" name="posts"  <?php echo $posts ?> /><label for="posts"> Show Posts</label>  <br />
					<input type="checkbox" name="pages"  <?php echo $pages ?> /><label for="pages"> Show Pages</label>  <br />
					<br />											
					
					<h3>Display</h3>	
					<p>Mini mode can gain you a lot of space, and the user can expand/shrink archives by clicking on the date headings.</p>		
					<input type="checkbox" name="mini"  <?php echo $mini ?> /><label for="mini"> Start in mini mode (collapsed archives)</label>  <br />				
					<br />
														
					<h3>Year book</h3>	
					<p>You can specify unique text to print with any year, describing it. Year book shows below the year and is useful for sharing your thoughts.</p>
					<p>Use description in the form year#description, one per line, HTML allowed. </p>					
					<textarea name="years"  rows="10" cols="80"><?php echo $years ?></textarea>	<br />
																			
					<div class="submit"><input type="submit" name="Submit" value="Update" /></div>
			</form>
		</div>
				
	 </div>

	</div>
	
</div>