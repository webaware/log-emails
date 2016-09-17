<?php
// settings form for Log Emails

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">
	<h2><?php esc_html_e('Log Emails', 'log-emails'); ?></h2>

	<form action="<?php echo admin_url('options.php'); ?>" method="POST">
		<?php settings_fields('log-emails'); ?>
		<?php do_settings_sections('log-emails'); ?>
		<?php submit_button(); ?>
	</form>
</div>
