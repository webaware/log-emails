<?php
// settings text field for Log Emails

if (!defined('ABSPATH')) {
	exit;
}

$value = get_option($args['option_name']);
if ($value === false)
	$value = $args['default'];

$class = isset($args['class']) ? $args['class'] : 'regular-text';
?>

<input name="<?php echo esc_attr($args['option_name']); ?>" type="text" class="<?php echo esc_attr($class); ?>" value="<?php echo esc_attr($value, 1); ?>" />
<br />
<em><?php echo esc_html($args['label_text']); ?></em>
