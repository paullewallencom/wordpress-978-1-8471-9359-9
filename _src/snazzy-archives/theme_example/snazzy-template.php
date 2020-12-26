<?php
/*
Template Name: Snazzy Archives
*/

?>

<?php get_header(); ?>

<div id="content">
	<p align="center">
		<?php if (isset($SnazzyArchives)) echo $SnazzyArchives->display(); ?>
	</p>
</div>

<?php get_footer(); ?>
