<?php
// details of email log entry

if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wrap">

	<h1><?php esc_html_e('Log Emails', 'log-emails'); ?></h1>

	<?php if ($warnings): ?>
	<div class="log-emails-warnings notice notice-warning">
		<?php foreach ($warnings as $warning): ?>
		<p><?php echo $warning; ?></p>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<nav class="log-emails-next-prev">
		<?php if ($previous): ?>
		<a class="log-emails-link-prev" title="<?php echo esc_attr_x('previous', 'move to previous log', 'log-emails'); ?>" href="<?php echo esc_url($previous); ?>"><div class="dashicons dashicons-arrow-left-alt2" aria-hidden="true"></div></a>
		<?php endif; ?>
		<a class="log-emails-link-list" title="<?php esc_attr_e('return to list', 'log-emails'); ?>" href="<?php echo esc_url($list); ?>"><div class="dashicons dashicons-list-view" aria-hidden="true"></div></a>
		<?php if ($next): ?>
		<a class="log-emails-link-next" title="<?php echo esc_attr_x('next', 'move to next log', 'log-emails'); ?>" href="<?php echo esc_url($next); ?>"><div class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></div></a>
		<?php endif; ?>
	</nav>

	<table class='log-emails-log-details'>

		<tr>
			<th scope="row"><?php echo esc_html_x('Sent', 'time and date an email was sent', 'log-emails'); ?></th>
			<td>
				<?php echo date_i18n('Y-m-d H:i:s', strtotime($post->post_date)); ?>
				&nbsp;&nbsp;&nbsp;(<?php echo date_i18n('Y-m-d H:i:s', strtotime($post->post_date_gmt)); ?> UTC)
			</td>
		</tr>

		<tr>
			<th scope="row"><?php echo esc_html_x('Subject', 'email subject', 'log-emails'); ?></th>
			<td><?php echo esc_html($post->post_title); ?></td>
		</tr>

		<tr>
			<th scope="row"><?php echo esc_html_x('From', 'email sender (From:)', 'log-emails'); ?></th>
			<td><?php echo esc_html(get_post_meta($post->ID, '_log_emails_log_from', true)); ?></td>
		</tr>

		<tr>
			<th scope="row"><?php echo esc_html_x('Recipients', 'email recipients (To:)', 'log-emails'); ?></th>
			<td><?php echo esc_html(get_post_meta($post->ID, '_log_emails_log_to', true)); ?></td>
		</tr>

		<?php if ($cc = get_post_meta($post->ID, '_log_emails_log_cc', true)): ?>
		<tr>
			<th scope="row"><?php echo esc_html_x('CC', 'courtesy copy addresses', 'log-emails'); ?></th>
			<td><?php echo esc_html($cc); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ($bcc = get_post_meta($post->ID, '_log_emails_log_bcc', true)): ?>
		<tr>
			<th scope="row"><?php echo esc_html_x('BCC', 'blind courtesy copy addresses', 'log-emails'); ?></th>
			<td><?php echo esc_html($bcc); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ($content_type = get_post_meta($post->ID, '_log_emails_log_content-type', true)): ?>
		<tr>
			<th scope="row"><?php esc_html_e('Content Type', 'log-emails'); ?></th>
			<td><?php echo esc_html($content_type); ?></td>
		</tr>
		<?php endif; ?>

		<tr>
			<th scope="row"><?php echo esc_html_x('Message', 'content of email', 'log-emails'); ?></th>
			<?php if (!empty($content_type) && strpos($content_type, 'text/html') !== false && empty($_GET['raw'])): ?>
				<td class="log-emails-content log-emails-content-html">
					<p><a href="<?php echo esc_url($current . '&raw=1'); ?>"><?php esc_html_e('view raw message', 'log-emails'); ?></a></p>
					<?php echo wp_kses_post($post->post_content); ?>
				</td>
			<?php else: ?>
				<td class="log-emails-content log-emails-content-raw">
					<?php if (!empty($content_type) && strpos($content_type, 'text/html') !== false && !empty($_GET['raw'])): ?>
						<p><a href="<?php echo esc_url($current); ?>"><?php esc_html_e('view HTML message', 'log-emails'); ?></a></p>
					<?php endif; ?>
					<?php echo nl2br(esc_html($post->post_content)); ?>
				</td>
			<?php endif; ?>
		</tr>

		<?php if ($altbody = get_post_meta($post->ID, '_log_emails_log_altbody', true)): ?>
		<tr>
			<th scope="row"><?php esc_html_e('Alternative Content', 'log-emails'); ?></th>
			<td><?php echo nl2br(esc_html($altbody)); ?></td>
		</tr>
		<?php endif; ?>

	</table>

	<br class="clear" />
</div>
