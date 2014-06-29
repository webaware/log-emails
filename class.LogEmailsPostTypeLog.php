<?php

/**
* custom post type for email log
*/
class LogEmailsPostTypeLog {

	/**
	* hooks
	*/
	public function __construct() {
		add_action('admin_init', array($this, 'init'));
		add_action('init', array($this, 'register'));
	}

	/**
	* admin_init action
	*/
	public function init() {
		global $typenow;

        if (empty($typenow)) {
            // try to pick it up from the query string
            if (!empty($_GET['post'])) {
                $post = get_post($_GET['post']);
                $typenow = $post->post_type;
            }
        }

		if ($typenow == 'log_emails_log') {
			add_filter('display_post_states', '__return_false');
			add_action('edit_form_after_title', array($this, 'adminEditAfterTitle'), 100);
			add_filter('post_row_actions', array($this, 'adminPostRowActions'), 10, 2);
			add_filter('bulk_actions-edit-log_emails_log', array($this, 'adminBulkActionsEdit'));
			add_filter('manage_log_emails_log_posts_columns', array($this, 'adminManageColumns'));
			add_action('manage_log_emails_log_posts_custom_column', array($this, 'adminManageCustomColumn'), 10, 2);
			add_action('admin_print_footer_scripts', array($this, 'adminPrintFooterScripts'));

			add_action('in_admin_header', array($this, 'adminScreenLayout'));
			add_filter('views_edit-log_emails_log', array($this, 'adminViewsEdit'));

			if (is_admin()) {
				add_filter('gettext', array($this, 'adminGetText'), 10, 3);
			}

			wp_enqueue_script('jquery');
		}
	}

	/**
	* register Custom Post Type
	*/
	public function register() {
		// register the post type
		register_post_type('log_emails_log', array(
			'labels' => array (
				'name' => __('Email Logs', 'log-emails'),
				'singular_name' => __('Email Log', 'log-emails'),
				'add_new_item' => __('Add New Email Log', 'log-emails'),
				'edit_item' => __('View Email Log', 'log-emails'),
				'new_item' => __('New Email Log', 'log-emails'),
				'view_item' => __('View Email Log', 'log-emails'),
				'search_items' => __('Search Email Log', 'log-emails'),
				'not_found' => __('No email logs found', 'log-emails'),
				'not_found_in_trash' => __('No email logs found in Trash', 'log-emails'),
				'parent_item_colon' => __('Parent email logs', 'log-emails'),
			),
			'description' => __('Keep temporary records of emails for review and diagnostics', 'log-emails'),
			'exclude_from_search' => true,
			'publicly_queryable' => false,
			'public' => false,
			'show_ui' => true,
			'show_in_admin_bar' => false,
			'menu_position' => 75,
			'hierarchical' => false,
			'has_archive' => false,
			'supports' => array('nada'),
			'rewrite' => false,
			'can_export' => false,
			'capabilities' => array (
				'create_posts' => false,
				'edit_post' => 'manage_options',
				'read_post' => 'manage_options',
				'delete_post' => 'manage_options',
				'edit_posts' => 'manage_options',
				'edit_others_posts' => 'manage_options',
				'publish_posts' => 'manage_options',
				'read_private_posts' => 'manage_options',
			),
		));
	}

	/**
	* change some text on admin pages
	* @param string $translation
	* @param string $text
	* @param string $domain
	* @return string
	*/
	public function adminGetText($translation, $text, $domain) {
		if ($domain == 'default') {
			if ($text == 'Edit &#8220;%s&#8221;') {
				$translation = _x('View &#8220;%s&#8221;', 'title text for email log link', 'log-emails');
			}
		}

		return $translation;
    }

	/**
	* remove views we don't need from post list
	* @param array $views
	* @return array
	*/
	public function adminViewsEdit($views) {
		unset($views['publish']);
		unset($views['draft']);

		return $views;
	}

	/**
	* remove unwanted actions from post list
	* @param array $actions
	* @param WP_Post $post
	* @return array
	*/
	public function adminPostRowActions($actions, $post) {
		unset($actions['inline hide-if-no-js']);		// "quick edit"
		unset($actions['trash']);
		unset($actions['edit']);

		if ($post && $post->ID) {
			// add View link
			$label = _x('View', 'view email log', 'log-emails');
			$actions['view'] = sprintf('<a href="%s" title="%s">%s</a>',
				get_edit_post_link($post->ID), esc_attr($label), $label);

			// add Delete link
			$label = _x('Delete', 'delete email log', 'log-emails');
			$actions['delete'] = sprintf('<a href="%s" title="%s" class="submitdelete">%s</a>',
				get_delete_post_link($post->ID, '', true), esc_attr($label), $label);
		}

		return $actions;
	}

	/**
	* change the list of available bulk actions
	* @param array $actions
	* @return array
	*/
	public function adminBulkActionsEdit($actions) {
		unset($actions['edit']);

		return $actions;
	}

	/**
	* filter to add columns to post list
	* @param array $posts_columns
	* @return array
	*/
	public function adminManageColumns($posts_columns) {
		$posts_columns['title'] = _x('Subject', 'email subject', 'log-emails');
		$posts_columns['_log_emails_log_to'] = _x('Recipients', 'email recipients (To:)', 'log-emails');

		return $posts_columns;
	}

	/**
	* action to add custom columns to post list
	* @param string $column_name
	* @param int $post_id
	*/
	public function adminManageCustomColumn($column_name, $post_id) {
		switch ($column_name) {
			case '_log_emails_log_to':
				$post = get_post($post_id);
				if ($post) {
					echo esc_html(get_post_meta($post_id, '_log_emails_log_to', true));
				}
				break;
		}
	}

	/**
	* change the screen layout
	*/
	public function adminScreenLayout() {
		// set max / default layout as single column
		add_screen_option('layout_columns', array('max' => 1, 'default' => 1));
	}

	/**
	* drop all the metaboxes and output what we want to show
	*/
	public function adminEditAfterTitle($post) {
		global $wp_meta_boxes;
		global $wpdb;

		// remove all meta boxes
		$wp_meta_boxes = array('log_emails_log' => array(
			'advanced' => array(),
			'side' => array(),
			'normal' => array(),
		));

		// get next / prev links
		$sql = "
			select ID
			from {$wpdb->posts}
			where ID < {$post->ID} and post_status='publish' and post_type = 'log_emails_log'
			order by ID desc limit 1
		";
		$previous = $wpdb->get_var($sql);
		$previous = $previous ? admin_url("post.php?post=$previous&action=edit") : false;
		$sql = "
			select ID
			from {$wpdb->posts}
			where ID > {$post->ID} and post_status='publish' and post_type = 'log_emails_log'
			order by ID asc limit 1
		";
		$next = $wpdb->get_var($sql);
		$next = $next ? admin_url("post.php?post=$next&action=edit") : false;

		// current page link
		$current = admin_url("post.php?post={$post->ID}&action=edit");

		// show my admin form
		require LOG_EMAILS_PLUGIN_ROOT . 'views/log-detail.php';
	}

	/**
	* replace Trash bulk actions with Delete
	* NB: WP admin already handles the delete action, it just doesn't expose it as a bulk action
	*/
	public function adminPrintFooterScripts() {
		?>

		<script>
		jQuery("select[name='action'],select[name='action2']").find("option[value='trash']").each(function() {
			this.value = 'delete';
			jQuery(this).text("<?php echo esc_attr_x('Delete', 'bulk delete email logs', 'log-emails'); ?>");
		});
		</script>

		<?php
	}

}
