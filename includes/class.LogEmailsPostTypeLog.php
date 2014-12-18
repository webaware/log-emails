<?php

/**
* custom post type for email log
*/
class LogEmailsPostTypeLog {

	const POST_TYPE = 'log_emails_log';

	/**
	* hooks
	*/
	public function __construct() {
		add_action('admin_init', array($this, 'init'));

		// register CPT early, so it beats other plugins that send emails on init (e.g. Easy Digital Downloads)
		add_action('init', array($this, 'register'), 1);
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

		add_action('admin_action_log_emails_view', array($this, 'viewLog'));

		if ($typenow == self::POST_TYPE) {
			add_filter('display_post_states', '__return_false');
			add_filter('bulk_actions-edit-' . self::POST_TYPE, array($this, 'adminBulkActionsEdit'));
			add_filter('parse_query', array($this, 'adminPostOrder'));
			add_filter('manage_' . self::POST_TYPE . '_posts_columns', array($this, 'adminManageColumns'));
			add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array($this, 'adminManageCustomColumn'), 10, 2);
			add_filter('gettext', array($this, 'removePublished'), 10, 3);
			add_action('admin_print_footer_scripts', array($this, 'adminPrintFooterScripts'));

			add_filter('views_edit-' . self::POST_TYPE, array($this, 'adminViewsEdit'));

			wp_enqueue_script('jquery');
		}
	}

	/**
	* register Custom Post Type
	*/
	public function register() {
		// register the post type
		register_post_type(self::POST_TYPE, array(
			'labels' => array (
				'name'					=> __('Email Logs', 'log-emails'),
				'singular_name'			=> __('Email Log', 'log-emails'),
				'search_items'			=> __('Search Email Log', 'log-emails'),
				'not_found'				=> __('No email logs found', 'log-emails'),
			),
			'description'				=> __('Keep temporary records of emails for review and diagnostics', 'log-emails'),
			'exclude_from_search'		=> true,
			'publicly_queryable'		=> false,
			'public'					=> false,
			'show_ui'					=> true,
			'show_in_admin_bar'			=> false,
			'show_in_menu'				=> 'tools.php',
			'menu_position'				=> 75,
			'hierarchical'				=> false,
			'has_archive'				=> false,
			'supports'					=> array('__nada__'),
			'rewrite'					=> false,
			'query_var'					=> false,
			'can_export'				=> false,
			'capabilities'				=> array (
				'create_posts'			=> 'do_not_allow',
				'edit_post'				=> 'activate_plugins',
				'edit_posts'			=> 'activate_plugins',
				'edit_others_posts'		=> 'activate_plugins',
				'delete_post'			=> 'activate_plugins',
				'delete_posts'			=> 'activate_plugins',
				'read_post'				=> 'activate_plugins',
				'read_private_posts'	=> 'do_not_allow',
				'publish_posts'			=> 'do_not_allow',
			),
			'map_meta_cap'				=> false,
		));
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
	* don't list logs as Published
	* @param string $translation
	* @param string $text
	* @param string $domain
	* @return string
	*/
	public function removePublished($translation, $text, $domain) {
		if ($domain == 'default' && $text == 'Published') {
			$translation = '';
		}

		return $translation;
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
	* change default order to ID descending, for better consistency when multiple logs land in the same second
	* @param WP_Query $query
	* @return WP_Query
	*/
	public function adminPostOrder($query) {
		// only for admin queries for this post type, with no specified order
		if ($query->is_admin && $query->get('post_type') == self::POST_TYPE && empty($query->query_vars['orderby'])) {
			$query->set('orderby', 'ID');
			$query->set('order', 'DESC');
		}

		return $query;
	}

	/**
	* filter to add columns to post list
	* @param array $posts_columns
	* @return array
	*/
	public function adminManageColumns($posts_columns) {
		unset($posts_columns['title']);

		$posts_columns['_log_emails_title'] = _x('Subject', 'email subject', 'log-emails');
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

			case '_log_emails_title':
				$post = get_post($post_id);
				if ($post) {
					$view_link = $this->getLogViewURL($post_id);
					printf('<strong><a class="row-title" href="%s">%s</a></strong>', $view_link, esc_html($post->post_title));

					// show log excerpt if viewing in excerpt mode
					global $mode;
					if ('excerpt' == $mode) {
						the_excerpt();
					}

					$actions = array();

					// add View link
					$label = _x('View', 'view email log', 'log-emails');
					$actions['view'] = sprintf('<a href="%s" title="%s">%s</a>', $view_link, esc_attr($label), $label);

					// add Delete link
					$label = _x('Delete', 'delete email log', 'log-emails');
					$actions['delete'] = sprintf('<a href="%s" title="%s" class="submitdelete">%s</a>',
						get_delete_post_link($post->ID, '', true), esc_attr($label), $label);

					$actions = apply_filters('post_row_actions', $actions, $post);

					echo '<div class="row-actions">';
					$sep = '';
					foreach ($actions as $action => $link) {
						echo "<span class='$action'>$sep$link</span>";
						$sep = ' | ';
					}
					echo '</div>';
				}
				break;

			case '_log_emails_log_to':
				$post = get_post($post_id);
				if ($post) {
					echo esc_html(get_post_meta($post_id, '_log_emails_log_to', true));
				}
				break;

		}
	}

	/**
	* view an email log
	*/
	public function viewLog() {
		global $wpdb;

		$post_id = empty($_GET['post_id']) ? 0 : absint($_GET['post_id']);
		if (!$post_id) {
			return;
		}

		$post = get_post($post_id);

		// get next / prev links
		$sql = "
			select ID
			from {$wpdb->posts}
			where ID < {$post->ID} and post_status='publish' and post_type = '" . self::POST_TYPE . "'
			order by ID desc limit 1
		";
		$previous = $wpdb->get_var($sql);
		$previous = $previous ? $this->getLogViewURL($previous) : false;
		$sql = "
			select ID
			from {$wpdb->posts}
			where ID > {$post->ID} and post_status='publish' and post_type = '" . self::POST_TYPE . "'
			order by ID asc limit 1
		";
		$next = $wpdb->get_var($sql);
		$next = $next ? $this->getLogViewURL($next) : false;

		// current page and list links
		$current = $this->getLogViewURL($post->ID);
		$list = admin_url('edit.php?post_type=' . self::POST_TYPE);

		// actions and filters just for this page
		add_filter('admin_body_class', array($this, 'logViewBodyClass'));
		add_filter('parent_file', array($this, 'fixLogViewMenuHierarchy'));
		add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));

		// show the view
		require_once ABSPATH . 'wp-admin/admin-header.php';
		require LOG_EMAILS_PLUGIN_ROOT . 'views/log-detail.php';
		require ABSPATH . 'wp-admin/admin-footer.php';
	}

	/**
	* add classes to the log view admin page body
	* @param string $classes
	* @return string
	*/
	public function logViewBodyClass($classes) {
		return ltrim($classes . ' log-emails-log-view');
	}

	/**
	* tell WordPress admin that Tools > Log Emails is parent page of single log view
	* @param string $parent_file
	* @return string
	*/
	public function fixLogViewMenuHierarchy($parent_file) {
		global $submenu_file;

		// set parent menu for filter return
		$parent_file = 'tools.php';

		// set submenu by side effect
		$submenu_file = 'edit.php?post_type=' . self::POST_TYPE;

		return $parent_file;
	}

	/**
	* enqueue scripts and stylesheets
	*/
	public function adminEnqueueScripts() {
		$ver = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? time() : LOG_EMAILS_PLUGIN_VERSION;
		wp_enqueue_style('log-emails-admin', plugins_url('css/admin.css', LOG_EMAILS_PLUGIN_FILE), false, $ver);
	}

	/**
	* replace Trash bulk actions with Delete, and remove Add New button
	* NB: WP admin already handles the delete action, it just doesn't expose it as a bulk action
	*/
	public function adminPrintFooterScripts() {
		?>

		<script>
		jQuery("select[name='action'],select[name='action2']").find("option[value='trash']").each(function() {
			this.value = 'delete';
			jQuery(this).text("<?php echo esc_attr_x('Delete', 'bulk delete email logs', 'log-emails'); ?>");
		});
		jQuery("a.add-new-h2").remove();
		</script>

		<?php
	}

	/**
	* get URL to view a log
	* @param int $post_id
	* @return string
	*/
	protected function getLogViewURL($post_id) {
		return add_query_arg(array('action' => 'log_emails_view', 'post_id' => $post_id), admin_url('admin.php'));
	}

	/**
	* create a new email log
	* @param string $subject
	* @param string $message
	* @param array $fields
	* @return int post ID of new log
	*/
	public static function createLog($subject, $message, $alt_message, $fields) {
		do_action('log_emails_cache_pause');

		// create post for message
		$post_id = wp_insert_post(array(
			'post_type' => self::POST_TYPE,
			'post_content' => $message,
			'post_title' => $subject,
			'post_status' => 'publish',
			'comment_status' => 'closed',
			'ping_status' => 'closed',
		));

		// add field values
		if ($post_id) {
			foreach ($fields as $name => $value) {
				if (strlen($value) > 0) {
					add_post_meta($post_id, $name, $value);
				}
			}

			// alternative body if present
			if ($alt_message) {
				add_post_meta($post_id, '_log_emails_log_altbody', $alt_message);
			}
		}

		do_action('log_emails_cache_resume');
	}

	/**
	* purge old logs
	* @param int $limit_days
	*/
	public static function purge($limit_days) {
		global $wpdb;

		$sql = sprintf("select ID from {$wpdb->posts} where post_type = '%s'", self::POST_TYPE);

		if ($limit_days > 0) {
			$cutoff = date_create("-$limit_days days");
			$sql .= $wpdb->prepare(" and post_date_gmt < %s", $cutoff->format('Y-m-d'));
		}

		$posts = $wpdb->get_col($sql);

		if ($posts) {
			do_action('log_emails_cache_pause');

			foreach ($posts as $post_id) {
				wp_delete_post($post_id, true);
			}

			do_action('log_emails_cache_resume');
		}
	}

}
