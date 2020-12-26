<?php
require_once('../../../wp-config.php');
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Insights</title>
		
	<script type='text/javascript'>
/* <![CDATA[ */
	var insights_url="<?php echo get_option('siteurl') ?>/wp-content/plugins/insights";
/* ]]> */
</script>
	
	<script type='text/javascript' src='<?php echo get_option('siteurl') ?>/wp-includes/js/jquery/jquery.js'></script>
	
	<script type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-content/plugins/insights/insights.js"></script>		
	
	<link rel="stylesheet" href="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/themes/advanced/skins/wp_theme/dialog.css?ver=311"/>
</head>

<body> 
	<p>Enter keywords you would like to search for and press Search button.</p>
						
	<input name="insights-radio" type="radio" checked="" value="1" /><label> Posts </label>
	<input name="insights-radio" type="radio" value="2"/><label> Images </label>
	<br />
	
	<input type="text" id="insights-search" name="insights-search" size="25" />
	<input id="insights-submit" class="button" type="button" value="Search" autocomplete="off" />
	
	<div id="insights-results"></div>			
	
</body>
</html>