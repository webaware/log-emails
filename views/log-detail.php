<?php
// details of email log entry
?>

<style>

.log-emails-log-details {
	width: 96%;
}

.log-emails-log-details tr {
}

.log-emails-log-details th {
	text-align: right;
	vertical-align: top;
	padding-bottom: 1em;
}

.log-emails-log-details td {
	padding-bottom: 1em;
	border: 1px dotted #ccc;
}

.log-emails-next-prev {
	position: relative;
	width: 96%;
	margin-bottom: 2em;
}

.log-emails-next-prev a {
	position: absolute;
	text-decoration: none;
}

.log-emails-link-prev {
	left: 40%;
}

.log-emails-link-next {
	right: 40%;
}

</style>

<nav class="log-emails-next-prev">
	<?php if ($previous): ?>
	<a class="log-emails-link-prev" title="<?php echo esc_attr_x('previous', 'move to previous', 'log-emails'); ?>" href="<?php echo esc_url($previous); ?>"><div class="dashicons dashicons-arrow-left-alt"></div></a>
	<?php endif; ?>
	<?php if ($next): ?>
	<a class="log-emails-link-next" title="<?php echo esc_attr_x('next', 'move to next', 'log-emails'); ?>" href="<?php echo esc_url($next); ?>"><div class="dashicons dashicons-arrow-right-alt"></div></a>
	<?php endif; ?>
</nav>

<table class='log-emails-log-details'>

<tr>
	<th><?php echo esc_html_x('Sent', 'time and date email was sent', 'log-emails'); ?></th>
	<td>
		<?php echo date_i18n('Y-m-d H:i:s', strtotime($post->post_date)); ?>
		&nbsp;&nbsp;&nbsp;(<?php echo date_i18n('Y-m-d H:i:s', strtotime($post->post_date_gmt)); ?> UTC)
	</td>
</tr>

<tr>
	<th><?php echo esc_html_x('Subject', 'email subject', 'log-emails'); ?></th>
	<td><?php echo esc_html($post->post_title); ?></td>
</tr>

<tr>
	<th><?php echo esc_html_x('From', 'email sender (From:)', 'log-emails'); ?></th>
	<td><?php echo esc_html(get_post_meta($post->ID, '_log_emails_log_from', true)); ?></td>
</tr>

<tr>
	<th><?php echo esc_html_x('Recipients', 'email recipients (To:)', 'log-emails'); ?></th>
	<td><?php echo esc_html(get_post_meta($post->ID, '_log_emails_log_to', true)); ?></td>
</tr>

<?php if ($cc = get_post_meta($post->ID, '_log_emails_log_cc', true)): ?>
<tr>
	<th><?php echo esc_html_x('CC', 'courtesy copy addresses', 'log-emails'); ?></th>
	<td><?php echo esc_html($cc); ?></td>
</tr>
<?php endif; ?>

<?php if ($bcc = get_post_meta($post->ID, '_log_emails_log_bcc', true)): ?>
<tr>
	<th><?php echo esc_html_x('BCC', 'blind courtesy copy addresses', 'log-emails'); ?></th>
	<td><?php echo esc_html($bcc); ?></td>
</tr>
<?php endif; ?>

<?php if ($content_type = get_post_meta($post->ID, '_log_emails_log_content-type', true)): ?>
<tr>
	<th><?php _e('Content Type', 'log-emails'); ?></th>
	<td><?php echo esc_html($content_type); ?></td>
</tr>
<?php endif; ?>

<tr>
	<th><?php echo esc_html_x('Message', 'content of email', 'log-emails'); ?></th>
	<?php if (!empty($content_type) && strpos($content_type, 'text/html') !== false && empty($_GET['raw'])): ?>
		<td>
			<p><a href="<?php echo esc_url($current . '&raw=1'); ?>"><?php esc_html_e('view raw message', 'log-emails'); ?></a></p>
			<?php echo wp_kses_post($post->post_content); ?>
		</td>
	<?php else: ?>
		<td>
			<?php if (!empty($content_type) && strpos($content_type, 'text/html') !== false && !empty($_GET['raw'])): ?>
				<p><a href="<?php echo esc_url($current); ?>"><?php esc_html_e('view HTML message', 'log-emails'); ?></a></p>
			<?php endif; ?>
			<?php echo nl2br(esc_html($post->post_content)); ?>
		</td>
	<?php endif; ?>
</tr>

<?php if ($altbody = get_post_meta($post->ID, '_log_emails_log_altbody', true)): ?>
<tr>
	<th><?php esc_html_e('Alternative Content', 'log-emails'); ?></th>
	<td><?php echo nl2br(esc_html($altbody)); ?></td>
</tr>
<?php endif; ?>

</table>
