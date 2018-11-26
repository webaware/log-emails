<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
* custom post type for email log
*/
class LogEmailsPostTypeLog {

	const POST_TYPE		= 'log_emails_log';
	const POST_STATUS	= 'log_emails_log';

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
				$typenow = get_post_type((int) $_GET['post']);
			}
			elseif (!empty($_GET['post_id'])) {
				$typenow = get_post_type((int) $_GET['post_id']);
			}
		}

		add_action('admin_action_log_emails_view', array($this, 'viewLog'));

		if ($typenow === self::POST_TYPE) {
			add_filter('display_post_states', '__return_false');
			add_filter('bulk_actions-edit-' . self::POST_TYPE, array($this, 'adminBulkActionsEdit'));
			add_filter('bulk_post_updated_messages', array($this, 'adminBulkPostUpdatedMessages'), 10, 2);
			add_filter('parse_query', array($this, 'adminPostOrder'));
			add_filter('posts_search', array($this, 'adminPostsSearch'), 10, 2);
			add_action('manage_edit-' . self::POST_TYPE . '_sortable_columns', array($this, 'adminSortableColumns'));
			add_filter('manage_' . self::POST_TYPE . '_posts_columns', array($this, 'adminManageColumns'), 100);
			add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array($this, 'adminManageCustomColumn'), 10, 2);
			add_filter('post_date_column_status', '__return_empty_string');
			add_filter('post_row_actions', array($this, 'postRowActions'), 10, 2);
			add_filter('gettext', array($this, 'removePublished'), 10, 3);
			add_action('admin_print_footer_scripts', array($this, 'adminPrintFooterScripts'));

			add_filter('views_edit-' . self::POST_TYPE, array($this, 'adminViewsEdit'));

			wp_enqueue_script('jquery');
			add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
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
				'menu_name'				=> __('Log Emails', 'log-emails'),
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
		unset($views['mine']);

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
		if ($domain === 'default' && $text === 'Published') {
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
	* custom messages for actions
	* @param array $messages
	* @param array $bulk_counts
	* @return array
	*/
	public function adminBulkPostUpdatedMessages($messages, $bulk_counts) {
		$messages[self::POST_TYPE] = array(
			'deleted' => _n('%s email log permanently deleted.', '%s email logs permanently deleted.', $bulk_counts['deleted'], 'log-emails'),
		);

		return $messages;
	}

	/**
	* change default order to ID descending, for better consistency when multiple logs land in the same second
	* @param WP_Query $query
	* @return WP_Query
	*/
	public function adminPostOrder($query) {
		// only for admin queries for this post type, with no specified order
		if ($query->is_admin && $query->get('post_type') === self::POST_TYPE) {
			if (empty($query->query_vars['orderby'])) {
				$query->set('orderby',  'ID');
				$query->set('order',    'DESC');
			}
			elseif ($query->query_vars['orderby'] === 'log_emails_log_to') {
				$query->set('orderby',  'meta_value');
				$query->set('meta_key', '_log_emails_log_to');
			}

			// make sure it picks up posts with the custom post status
			$query->set('post_status', 'any');
		}

		return $query;
	}

	/**
	* add custom columns to sort by
	* @param array $columns
	* @return array
	*/
	public function adminSortableColumns($columns) {
		$columns['_log_emails_title']	= 'title';
		$columns['_log_emails_log_to']	= 'log_emails_log_to';

		return $columns;
	}

	/**
	* add query condition for custom meta
	* @param string $search the search string so far
	* @param WP_Query $query
	* @return string
	*/
	public function adminPostsSearch($search, $query) {
		global $wpdb;

		if ($query->is_main_query() && !empty($query->query['s'])) {
			$sql    = "
				or exists (
					select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
					and meta_key in ('_log_emails_log_from','_log_emails_log_to','_log_emails_log_cc','_log_emails_log_bcc')
					and meta_value like %s
				)
			";
			$like   = '%' . $wpdb->esc_like($query->query['s']) . '%';
			$search = preg_replace("#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#", $wpdb->prepare($sql, $like), $search);
		}

		return $search;
	}

	/**
	* filter to add columns to post list
	* @param array $posts_columns
	* @return array
	*/
	public function adminManageColumns($posts_columns) {
		$new_columns = array();

		// bring across bulk action checkbox column
		if (isset($posts_columns['cb'])) {
			$new_columns['cb'] = $posts_columns['cb'];
		}

		$new_columns['_log_emails_title']		= _x('Subject', 'email subject', 'log-emails');
		$new_columns['_log_emails_log_to']		= _x('Recipients', 'email recipients (To:)', 'log-emails');
		$new_columns['_log_emails_warnings']	= _x('Warnings', 'list column title', 'log-emails');

		// bring across date column
		if (isset($posts_columns['date'])) {
			$new_columns['date'] = $posts_columns['date'];
		}

		return $new_columns;
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
					printf('<strong><a class="row-title" href="%s">%s</a></strong>', esc_url($view_link), esc_html($post->post_title));

					// show log excerpt if viewing in excerpt mode
					global $mode;
					if ('excerpt' === $mode) {
						echo '<p>', wp_trim_words($post->post_content), '</p>';
					}
				}
				break;

			case '_log_emails_log_to':
				$post = get_post($post_id);
				if ($post) {
					echo esc_html(get_post_meta($post_id, '_log_emails_log_to', true));
				}
				break;

			case '_log_emails_warnings':
				$post = get_post($post_id);
				if ($post) {
					$warnings = $this->getWarnings($post);
					if (!empty($warnings)) {
						echo '<p>', implode('</p><p>', $warnings), '</p>';
					}
				}
				break;

		}
	}

	/**
	* customise the table list row actions
	* @param array $actions
	* @param WP_Post $post
	* @return return
	*/
	public function postRowActions($actions, $post) {
		$actions = array();

		// add View link
		$label = _x('View', 'view email log', 'log-emails');
		$actions['view'] = sprintf('<a href="%s" title="%s">%s</a>', esc_url($this->getLogViewURL($post->ID)), esc_attr($label), esc_html($label));

		// add Delete link
		$label = _x('Delete', 'delete email log', 'log-emails');
		$actions['delete'] = sprintf('<a href="%s" title="%s" class="submitdelete">%s</a>',
			esc_url(get_delete_post_link($post->ID, '', true)), esc_attr($label), esc_html($label));

		return $actions;
	}

	/**
	* view an email log
	*/
	public function viewLog() {
		global $wpdb;

		$post_id = empty($_GET['post_id']) ? 0 : absint($_GET['post_id']);
		$post = $post_id ? get_post($post_id) : false;

		if (!$post) {
			return;
		}

		$post_type = get_post_type_object($post->post_type);

		if ($post->post_type !== self::POST_TYPE) {
			wp_die(__('This post is not an email log.', 'log-emails'));
		}

		if (!current_user_can($post_type->cap->edit_posts)) {
			wp_die(sprintf('<h1>%s</h1><p>%s</p>',
				__('Cheatin&#8217; uh?', 'log-emails'),
				__('You are not allowed to view email logs.', 'log-emails')),
				403);
		}

		// get next / prev links
		$sql = "
			select ID
			from {$wpdb->posts}
			where ID < {$post->ID} and post_type = '" . self::POST_TYPE . "'
			order by ID desc limit 1
		";
		$previous = $wpdb->get_var($sql);
		$previous = $previous ? $this->getLogViewURL($previous) : false;
		$sql = "
			select ID
			from {$wpdb->posts}
			where ID > {$post->ID} and post_type = '" . self::POST_TYPE . "'
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

		// check for any warnings (missing fields)
		$warnings = $this->getWarnings($post);

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
		$dev = SCRIPT_DEBUG ? '.dev' : '.min';
		$ver = SCRIPT_DEBUG ? time() : LOG_EMAILS_PLUGIN_VERSION;
		wp_enqueue_style('log-emails-admin', plugins_url("css/admin$dev.css", LOG_EMAILS_PLUGIN_FILE), false, $ver);
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
	* get warning messages for email log
	* @param WP_POST $post
	* @return array
	*/
	public function getWarnings($post) {
		$warnings = array();

		$sender = get_post_meta($post->ID, '_log_emails_log_from', true);
		if (empty($sender)) {
			$warnings[] = esc_html_x('Email has no sender', 'log warning', 'log-emails');
		}

		$recipients = get_post_meta($post->ID, '_log_emails_log_to', true);
		$cc         = get_post_meta($post->ID, '_log_emails_log_cc', true);
		$bcc        = get_post_meta($post->ID, '_log_emails_log_bcc', true);
		if (empty($recipients) && empty($cc) && empty($bcc)) {
			$warnings[] = esc_html_x('missing recipients', 'log warning', 'log-emails');
		}
		elseif (empty($recipients)) {
			$warnings[] = esc_html_x('no direct recipients, only CC/BCC', 'log warning', 'log-emails');
		}

		if (empty($post->post_title)) {
			$warnings[] = esc_html_x('missing subject', 'log warning', 'log-emails');
		}

		if (empty($post->post_content)) {
			$warnings[] = esc_html_x('missing body', 'log warning', 'log-emails');
		}

		return $warnings;
	}

	/**
	* create a new email log
	* @param string $subject
	* @param string $message
	* @param array $fields
	* @return int post ID of new log
	*/
	public static function createLog($subject, $message, $alt_message, $fields) {
		// prevent sanitising of email body and alt-body, so that we can access full email content in raw log view
		remove_all_filters('pre_post_content');
		remove_all_filters('content_save_pre');
		remove_all_filters('sanitize_' . self::POST_TYPE . '_meta__log_emails_log_altbody');

		// allow plugins to add back some filtering
		do_action('log_emails_pre_insert');

		// create post for message
		$post_id = wp_insert_post(array(
			'post_type'			=> self::POST_TYPE,
			'post_content'		=> $message,
			'post_title'		=> $subject,
			'post_status'		=> self::POST_STATUS,
			'comment_status'	=> 'closed',
			'ping_status'		=> 'closed',
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
			foreach ($posts as $post_id) {
				wp_delete_post($post_id, true);
			}
		}
	}

}
