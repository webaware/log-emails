<?php
// settings form for Log Emails
?>

<div class="wrap">
	<?php screen_icon(); ?>
	<h2><?php esc_html_e('Email Logs', 'log-emails'); ?></h2>

	<form action="<?php echo admin_url('options.php'); ?>" method="POST">
		<?php settings_fields('log-emails'); ?>
		<?php do_settings_sections('log-emails'); ?>
		<?php submit_button(); ?>
	</form>
</div>
