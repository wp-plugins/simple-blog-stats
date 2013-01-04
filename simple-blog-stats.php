<?php 
/*
Plugin Name: Simple Blog Stats
Plugin URI: http://perishablepress.com/simple-blog-stats/
Description: Provides a bunch of shortcodes and template tags to display a variety of statistics about your site.
Author: Jeff Starr
Author URI: http://monzilla.biz/
Version: 20130104
License: GPL v2
Usage: Visit the plugin's settings page for shortcodes, template tags, and more information.
Tags: stats, statistics, posts, categories, tags
*/

// NO EDITING REQUIRED - PLEASE SET PREFERENCES IN THE WP ADMIN!

$sbs_plugin  = __('Simple Blog Stats');
$sbs_options = get_option('sbs_options');
$sbs_path    = plugin_basename(__FILE__); // 'simple-blog-stats/simple-blog-stats.php';
$sbs_homeurl = 'http://perishablepress.com/simple-blog-stats/';
$sbs_version = '20130104';

// require minimum version of WordPress
add_action('admin_init', 'sbs_require_wp_version');
function sbs_require_wp_version() {
	global $wp_version, $sbs_path, $sbs_plugin;
	if (version_compare($wp_version, '3.4', '<')) {
		if (is_plugin_active($sbs_path)) {
			deactivate_plugins($sbs_path);
			$msg =  '<strong>' . $sbs_plugin . '</strong> ' . __('requires WordPress 3.4 or higher, and has been deactivated!') . '<br />';
			$msg .= __('Please return to the ') . '<a href="' . admin_url() . '">' . __('WordPress Admin area') . '</a> ' . __('to upgrade WordPress and try again.');
			wp_die($msg);
		}
	}
}

// number of posts
add_shortcode('sbs_posts','sbs_posts');
function sbs_posts() {
	global $sbs_options;
	$count_posts = wp_count_posts();
	return $sbs_options['count_posts_before'] . $count_posts->publish . $sbs_options['count_posts_after'];
}

// number of pages
add_shortcode('sbs_pages','sbs_pages');
function sbs_pages() {
	global $sbs_options;
	$count_pages = wp_count_posts('page');
	return $sbs_options['count_pages_before'] . $count_pages->publish . $sbs_options['count_pages_after'];
}

// number of drafts
add_shortcode('sbs_drafts','sbs_drafts');
function sbs_drafts() {
	global $sbs_options;
	$count_drafts = wp_count_posts();
	return $sbs_options['count_drafts_before'] . $count_drafts->draft . $sbs_options['count_drafts_after'];
}

// number of comments (total)
add_shortcode('sbs_comments','sbs_comments');
function sbs_comments() {
	global $sbs_options;
	$count_comments = wp_count_comments();
	return $sbs_options['count_comments_before'] . $count_comments->total_comments . $sbs_options['count_comments_after'];
}

// number of comments (moderated)
add_shortcode('sbs_moderated','sbs_moderated');
function sbs_moderated() {
	global $sbs_options;
	$count_moderated = wp_count_comments();
	return $sbs_options['count_moderated_before'] . $count_moderated->moderated . $sbs_options['count_moderated_after'];
}

// number of comments (approved)
add_shortcode('sbs_approved','sbs_approved');
function sbs_approved() {
	global $sbs_options;
	$count_approved = wp_count_comments();
	return $sbs_options['count_approved_before'] . $count_approved->approved . $sbs_options['count_approved_after'];
}

// number of users
add_shortcode('sbs_users','sbs_users');
function sbs_users() {
	global $sbs_options;
	$count_users = count_users();
	return $sbs_options['count_users_before'] . $count_users['total_users'] . $sbs_options['count_users_after'];
}

// number of categories
add_shortcode('sbs_cats','sbs_cats');
function sbs_cats() {
	global $sbs_options;
	$cats = wp_list_categories('title_li=&style=none&echo=0');
	$cats_parts = explode('<br />', $cats);
	$cats_count = count($cats_parts) - 1;
	return $sbs_options['count_cats_before'] . $cats_count . $sbs_options['count_cats_after'];
}

// number of tags
add_shortcode('sbs_tags','sbs_tags');
function sbs_tags() {
	global $sbs_options;
	return $sbs_options['count_tags_before'] . wp_count_terms('post_tag') . $sbs_options['count_tags_after'];
}

// site last updated
add_shortcode('sbs_updated','sbs_updated');
function sbs_updated($d = '') {
	global $sbs_options;
	$count_posts = wp_count_posts();
	$published_posts = $count_posts->publish; 
	$recent = new WP_Query("showposts=1&orderby=date&post_status=publish");
	if ($recent->have_posts()) {
		while ($recent->have_posts()) {
			$recent->the_post();
			$last_update = get_the_modified_date($d) . ' <span class="sbs-site-updated-time">@ ' . get_the_time($d) . '</span>';
		}
		return $sbs_options['site_updated_before'] . $last_update . $sbs_options['site_updated_after'];
	} else {
		return $sbs_options['site_updated_before'] . 'awhile ago' . $sbs_options['site_updated_after'];
	}
}

// latest posts
add_shortcode('sbs_latest_posts','sbs_latest_posts');
function sbs_latest_posts($d = '') {
	global $sbs_options;
	$posts_number = $sbs_options['number_of_posts'];
	$post_length  = $sbs_options['post_length'];
	$latest = new WP_Query("showposts=$posts_number&orderby=date&post_status=publish");
	if ($latest->have_posts()) {
		$latest_posts = '<ul id="sbs-posts">';
		while ($latest->have_posts()) {
			$latest->the_post();
			$post_content = get_the_content();
			$post_excerpt = preg_replace('/\s+?(\S+)?$/', '', substr($post_content, 0, $post_length));
			$post_display = strip_tags($post_excerpt, '<p>');
			$latest_posts .= '<li class="sbs-post"><a href="' . get_permalink() . '">' . the_title_attribute(array('echo'=>0)) . '</a> ';
			$latest_posts .= '<span>' . $post_display . ' <small>[...]</small></span></li>';
		}
		$latest_posts .= '</ul>';
		return $sbs_options['latest_posts_before'] . $latest_posts . $sbs_options['latest_posts_after'];
	} else {
		return $sbs_options['latest_posts_before'] . 'nothing new' . $sbs_options['latest_posts_after'];
	}
}

// latest comments
add_shortcode('sbs_latest_comments','sbs_latest_comments');
function sbs_latest_comments() {
	global $sbs_options;
	$comments_number = $sbs_options['number_of_comments'];
	$comment_length  = $sbs_options['comment_length'];

	$recent_comments = get_comments(array('number'=>$comments_number, 'status'=>'approve'));
	$comments = '<ul id="sbs-comments">';
	foreach ($recent_comments as $recent_comment) {
		$comment_id        = $recent_comment->comment_ID;
		$comment_date      = $recent_comment->comment_date;
		$comment_author    = $recent_comment->comment_author;

		$comment_content   = $recent_comment->comment_content;
		$comment_excerpt   = preg_replace('/\s+?(\S+)?$/', '', substr($comment_content, 0, $comment_length));

		$line_breaks       = array("\r\n", "\n", "\r");
		$comment_display   = str_replace($line_breaks, " ", $comment_excerpt);
		$comment_display   = mysql_real_escape_string($comment_display);

		$comment_post_id   = $recent_comment->comment_post_ID;
		$comment_permalink = get_permalink($comment_post_id);

		$comments .= '<li class="sbs-comment">';
		$comments .= '<a href="' . $comment_permalink . '#comment-' . $comment_id . '" title="Posted: ' . $comment_date . '">' . $comment_author . '</a>: ';
		$comments .= '<span>' . $comment_display . '</span>';
		$comments .= '</li>';
	}
	$comments .= '</ul>';
	return $sbs_options['latest_comments_before'] . $comments . $sbs_options['latest_comments_after'];	
}

// display blog stats
add_shortcode('sbs_blog_stats','sbs_blog_stats');
function sbs_blog_stats() {
	global $sbs_options;

	$count_posts = wp_count_posts();
	$number_posts = $count_posts->publish;

	$count_pages = wp_count_posts('page');
	$number_pages = $count_pages->publish;

	$count_drafts = wp_count_posts();
	$number_drafts = $count_drafts->draft;

	$count_comments = wp_count_comments();
	$number_comments = $count_comments->total_comments;

	$count_moderated = wp_count_comments();
	$number_moderated = $count_moderated->moderated;

	$count_approved = wp_count_comments();
	$number_approved = $count_approved->approved;

	$count_users = count_users();
	$number_users = $count_users['total_users'];

	$cats = wp_list_categories('title_li=&style=none&echo=0');
	$cats_parts = explode('<br />', $cats);
	$cats_count = count($cats_parts) - 1;
	$number_cats = $cats_count;

	$number_tags = wp_count_terms('post_tag');
	
	$sbs_stats  = '<ul id="sbs-stats">';
	$sbs_stats .= '<li><span>' . $number_posts . '</span> ' . __('posts') . '</li>';
	$sbs_stats .= '<li><span>' . $number_pages . '</span> ' . __('pages') . '</li>';
	$sbs_stats .= '<li><span>' . $number_drafts . '</span> ' . __('drafts') . '</li>';
	$sbs_stats .= '<li><span>' . $number_comments . '</span> ' . __('total comments') . '</li>';
	$sbs_stats .= '<li><span>' . $number_moderated . '</span> ' . __('comments in queue') . '</li>';
	$sbs_stats .= '<li><span>' . $number_approved . '</span> ' . __('comments approved') . '</li>';
	$sbs_stats .= '<li><span>' . $number_users . '</span> ' . __('registered users') . '</li>';
	$sbs_stats .= '<li><span>' . $number_cats . '</span> ' . __('categories') . '</li>';
	$sbs_stats .= '<li><span>' . $number_tags . '</span> ' . __('tags') . '</li>';
	$sbs_stats .= '</ul>';

	return $sbs_options['blog_stats_before'] . $sbs_stats . $sbs_options['blog_stats_after'];
}

// display settings link on plugin page
add_filter ('plugin_action_links', 'sbs_plugin_action_links', 10, 2);
function sbs_plugin_action_links($links, $file) {
	global $sbs_path, $sbs_path;
	if ($file == $sbs_path) {
		$sbs_links = '<a href="' . get_admin_url() . 'options-general.php?page=' . $sbs_path . '">' . __('Settings') .'</a>';
		array_unshift($links, $sbs_links);
	}
	return $links;
}

// delete plugin settings
function sbs_delete_plugin_options() {
	delete_option('sbs_options');
}
if ($sbs_options['default_options'] == 1) {
	register_uninstall_hook (__FILE__, 'sbs_delete_plugin_options');
}

// define default settings
register_activation_hook (__FILE__, 'sbs_add_defaults');
function sbs_add_defaults() {
	$tmp = get_option('sbs_options');
	if(($tmp['default_options'] == '1') || (!is_array($tmp))) {
		$arr = array(
			'default_options'    => 0,
			'count_posts_before' => '<span class="sbs-count-posts">',
			'count_posts_after'  => '</span>',
			'count_pages_before' => '<span class="sbs-count-pages">',
			'count_pages_after'  => '</span>',
			'count_drafts_before' => '<span class="sbs-count-drafts">',
			'count_drafts_after'  => '</span>',
			'count_comments_before' => '<span class="sbs-count-comments">',
			'count_comments_after'  => '</span>',
			'count_moderated_before' => '<span class="sbs-count-moderated">',
			'count_moderated_after'  => '</span>',
			'count_approved_before' => '<span class="sbs-count-approved">',
			'count_approved_after'  => '</span>',
			'count_users_before' => '<span class="sbs-count-users">',
			'count_users_after'  => '</span>',
			'count_cats_before' => '<span class="sbs-count-cats">',
			'count_cats_after'  => '</span>',
			'count_tags_before' => '<span class="sbs-count-tags">',
			'count_tags_after'  => '</span>',
			'site_updated_before' => '<span class="sbs-site-updated">',
			'site_updated_after'  => '</span>',
			'latest_posts_before' => '<div class="sbs-latest-posts">',
			'latest_posts_after'  => '</div>',
			'latest_comments_before' => '<div class="sbs-latest-comments">',
			'latest_comments_after'  => '</div>',
			'blog_stats_before' => '<div class="sbs-blog-stats">',
			'blog_stats_after'  => '</div>',
			'number_of_comments' => '3',
			'number_of_posts' => '3',
			'comment_length' => '30',
			'post_length' => '30',
		);
		update_option('sbs_options', $arr);
	}
}

// whitelist settings
add_action ('admin_init', 'sbs_init');
function sbs_init() {
	register_setting('sbs_plugin_options', 'sbs_options', 'sbs_validate_options');
}

// sanitize and validate input
function sbs_validate_options($input) {

	if (!isset($input['default_options'])) $input['default_options'] = null;
	$input['default_options'] = ($input['default_options'] == 1 ? 1 : 0);

	$input['count_posts_before'] = wp_kses_post($input['count_posts_before']);
	$input['count_posts_after'] = wp_kses_post($input['count_posts_after']);

	$input['count_pages_before'] = wp_kses_post($input['count_pages_before']);
	$input['count_pages_after'] = wp_kses_post($input['count_pages_after']);

	$input['count_drafts_before'] = wp_kses_post($input['count_drafts_before']);
	$input['count_drafts_after'] = wp_kses_post($input['count_drafts_after']);

	$input['count_comments_before'] = wp_kses_post($input['count_comments_before']);
	$input['count_comments_after'] = wp_kses_post($input['count_comments_after']);

	$input['count_moderated_before'] = wp_kses_post($input['count_moderated_before']);
	$input['count_moderated_after'] = wp_kses_post($input['count_moderated_after']);

	$input['count_approved_before'] = wp_kses_post($input['count_approved_before']);
	$input['count_approved_after'] = wp_kses_post($input['count_approved_after']);

	$input['count_users_before'] = wp_kses_post($input['count_users_before']);
	$input['count_users_after'] = wp_kses_post($input['count_users_after']);

	$input['count_cats_before'] = wp_kses_post($input['count_cats_before']);
	$input['count_cats_after'] = wp_kses_post($input['count_cats_after']);

	$input['count_tags_before'] = wp_kses_post($input['count_tags_before']);
	$input['count_tags_after'] = wp_kses_post($input['count_tags_after']);

	$input['site_updated_before'] = wp_kses_post($input['site_updated_before']);
	$input['site_updated_after'] = wp_kses_post($input['site_updated_after']);

	$input['latest_posts_before'] = wp_kses_post($input['latest_posts_before']);
	$input['latest_posts_after'] = wp_kses_post($input['latest_posts_after']);

	$input['latest_comments_before'] = wp_kses_post($input['latest_comments_before']);
	$input['latest_comments_after'] = wp_kses_post($input['latest_comments_after']);

	$input['blog_stats_before'] = wp_kses_post($input['blog_stats_before']);
	$input['blog_stats_after'] = wp_kses_post($input['blog_stats_after']);

	$input['number_of_comments'] = wp_filter_nohtml_kses($input['number_of_comments']);
	$input['number_of_posts'] = wp_filter_nohtml_kses($input['number_of_posts']);
	$input['comment_length'] = wp_filter_nohtml_kses($input['comment_length']);
	$input['post_length'] = wp_filter_nohtml_kses($input['post_length']);

	return $input;
}

// add the options page
add_action ('admin_menu', 'sbs_add_options_page');
function sbs_add_options_page() {
	add_options_page('Simple Blog Stats', 'Simple Blog Stats', 'manage_options', __FILE__, 'sbs_render_form');
}

// create the options page
function sbs_render_form() {
	global $sbs_plugin, $sbs_options, $sbs_path, $sbs_homeurl, $sbs_version; ?>

	<style type="text/css">
		.mm-panel-overview { padding-left: 135px; background: url(<?php echo plugins_url(); ?>/simple-blog-stats/sbs-logo.png) no-repeat 15px 0; }

		#mm-plugin-options h2 small { font-size: 60%; }
		#mm-plugin-options h3 { cursor: pointer; }
		#mm-plugin-options h4, 
		#mm-plugin-options p { margin: 15px; line-height: 18px; }
		#mm-plugin-options ul { margin: 15px 15px 25px 40px; }
		#mm-plugin-options li { margin: 10px 0; list-style-type: disc; }
		#mm-plugin-options abbr { cursor: help; border-bottom: 1px dotted #dfdfdf; }

		#setting-error-settings_updated { margin: 10px 0; }
		#setting-error-settings_updated p { margin: 5px; }
		#mm-plugin-options .button-primary { margin: 0 0 15px 15px; }

		.mm-table-wrap { margin: 15px; }
		.mm-table-wrap td { padding: 5px 10px; vertical-align: middle; }
		.mm-item-caption { margin: 3px 0 0 3px; font-size: 80%; color: #777; }
		.mm-number-option { margin-top: 3px; }
		#mm-panel-toggle { margin: 5px 0; }
		#mm-credit-info { margin-top: -5px; }
		#mm-iframe-wrap { width: 100%; height: 250px; overflow: hidden; }
		#mm-iframe-wrap iframe { width: 100%; height: 100%; overflow: hidden; margin: 0; padding: 0; }
	</style>

	<div id="mm-plugin-options" class="wrap">
		<?php screen_icon(); ?>

		<h2><?php _e('Simple Blog Stats'); ?> <small><?php echo 'v' . $sbs_version; ?></small></h2>
		<div id="mm-panel-toggle"><a href="<?php get_admin_url() . 'options-general.php?page=' . $sbs_path; ?>"><?php _e('Toggle all panels'); ?></a></div>

		<form method="post" action="options.php">
			<?php $sbs_options = get_option('sbs_options'); settings_fields('sbs_plugin_options'); ?>

			<div class="metabox-holder">
				<div class="meta-box-sortables ui-sortable">
					<div id="mm-panel-overview" class="postbox">
						<h3><?php _e('Overview'); ?></h3>
						<div class="toggle default-hidden">
							<div class="mm-panel-overview">
								<p>
									<strong><?php echo $sbs_plugin; ?></strong> <?php _e('(SBS) provides a bunch of shortcodes and template tags to display a variety of statistics about your site.'); ?>
									<?php _e('Use the shortcodes to display various stats on a post or page. Use the template tags to display stats anywhere in your theme template.'); ?>
								</p>
								<ul>
									<li><?php _e('For shortcodes, visit the'); ?> <a id="mm-panel-primary-link" href="#mm-panel-primary"><?php _e('SBS Shortcodes'); ?></a>.</li>
									<li><?php _e('For template tags, visit'); ?> <a id="mm-panel-secondary-link" href="#mm-panel-secondary"><?php _e('SBS Template Tags'); ?></a>.</li>
									<li><?php _e('For more information check the <code>readme.txt</code> and'); ?> <a href="<?php echo $sbs_homeurl; ?>"><?php _e('SBS Homepage'); ?></a>.</li>
								</ul>
							</div>
						</div>
					</div>
					<div id="mm-panel-primary" class="postbox">
						<h3><?php _e('SBS Shortcodes'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<p><?php _e('Here you&rsquo;ll find your shortcodes along with options to customize the corresponding text and/or markup. Leave the before/after fields blank to disable.'); ?></p>
							<div class="mm-table-wrap">
								<table class="widefat">
									<thead>
										<tr>
											<th><?php _e('Display before shortcode'); ?></th>
											<th><?php _e('Shortcode / Options'); ?></th>
											<th><?php _e('Output'); ?></th>
											<th><?php _e('Display after shortcode'); ?></th>
										</tr>
									</thead>
									<tbody>
										<tr>
											<td>
												<label class="description" for="sbs_options[count_posts_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_posts_before]"><?php echo esc_textarea($sbs_options['count_posts_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_posts]</code>
												<div class="mm-item-caption"><?php _e('number of posts'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_posts]'); ?></td>
											<td>
												<label class="description" for="sbs_options[count_posts_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_posts_after]"><?php echo esc_textarea($sbs_options['count_posts_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[count_pages_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_pages_before]"><?php echo esc_textarea($sbs_options['count_pages_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_pages]</code>
												<div class="mm-item-caption"><?php _e('number of pages'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_pages]'); ?></td>
											<td>
												<label class="description" for="sbs_options[count_pages_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_pages_after]"><?php echo esc_textarea($sbs_options['count_pages_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[count_drafts_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_drafts_before]"><?php echo esc_textarea($sbs_options['count_drafts_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_drafts]</code>
												<div class="mm-item-caption"><?php _e('number of drafts'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_drafts]'); ?></td>
											<td>
												<label class="description" for="sbs_options[count_drafts_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_drafts_after]"><?php echo esc_textarea($sbs_options['count_drafts_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[count_comments_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_comments_before]"><?php echo esc_textarea($sbs_options['count_comments_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_comments]</code>
												<div class="mm-item-caption"><?php _e('number of comments'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_comments]'); ?></td>
											<td>
												<label class="description" for="sbs_options[count_comments_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_comments_after]"><?php echo esc_textarea($sbs_options['count_comments_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[count_moderated_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_moderated_before]"><?php echo esc_textarea($sbs_options['count_moderated_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_moderated]</code>
												<div class="mm-item-caption"><?php _e('moderated comments'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_moderated]'); ?></td>
											<td>
												<label class="description" for="sbs_options[count_moderated_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_moderated_after]"><?php echo esc_textarea($sbs_options['count_moderated_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[count_approved_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_approved_before]"><?php echo esc_textarea($sbs_options['count_approved_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_approved]</code>
												<div class="mm-item-caption"><?php _e('approved comments'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_approved]'); ?></td>
											<td>
												<label class="description" for="sbs_options[count_approved_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_approved_after]"><?php echo esc_textarea($sbs_options['count_approved_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[count_users_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_users_before]"><?php echo esc_textarea($sbs_options['count_users_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_users]</code>
												<div class="mm-item-caption"><?php _e('number of users'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_users]'); ?></td>
											<td>
												<label class="description" for="sbs_options[count_users_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_users_after]"><?php echo esc_textarea($sbs_options['count_users_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[count_cats_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_cats_before]"><?php echo esc_textarea($sbs_options['count_cats_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_cats]</code>
												<div class="mm-item-caption"><?php _e('number of categories'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_cats]'); ?></td>
											<td>
												<label class="description" for="sbs_options[count_cats_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_cats_after]"><?php echo esc_textarea($sbs_options['count_cats_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[count_tags_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_tags_before]"><?php echo esc_textarea($sbs_options['count_tags_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_tags]</code>
												<div class="mm-item-caption"><?php _e('number of tags'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_tags]'); ?></td>
											<td>
												<label class="description" for="sbs_options[count_tags_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[count_tags_after]"><?php echo esc_textarea($sbs_options['count_tags_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[site_updated_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[site_updated_before]"><?php echo esc_textarea($sbs_options['site_updated_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_updated]</code>
												<div class="mm-item-caption"><?php _e('site last updated'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_updated]'); ?></td>
											<td>
												<label class="description" for="sbs_options[site_updated_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[site_updated_after]"><?php echo esc_textarea($sbs_options['site_updated_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[latest_posts_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[latest_posts_before]"><?php echo esc_textarea($sbs_options['latest_posts_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_latest_posts]</code>
												<div class="mm-item-caption"><?php _e('displays recent posts'); ?></div>
												<div class="mm-number-option">
													<label class="description" for="sbs_options[number_of_posts]"><?php _e('Number of posts:'); ?></label> 
													<input type="text" size="2" maxlength="10" name="sbs_options[number_of_posts]" value="<?php echo $sbs_options['number_of_posts']; ?>" />
												</div>
												<div class="mm-number-option">
													<label class="description" for="sbs_options[post_length]"><?php _e('Length of posts:'); ?></label> 
													<input type="text" size="2" maxlength="10" name="sbs_options[post_length]" value="<?php echo $sbs_options['post_length']; ?>" />
												</div>
											</td>
											<td><?php echo do_shortcode('[sbs_latest_posts]'); ?></td>
											<td>
												<label class="description" for="sbs_options[latest_posts_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[latest_posts_after]"><?php echo esc_textarea($sbs_options['latest_posts_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[latest_comments_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[latest_comments_before]"><?php echo esc_textarea($sbs_options['latest_comments_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_latest_comments]</code>
												<div class="mm-item-caption"><?php _e('displays recent comments'); ?></div>
												<div class="mm-number-option">
													<label class="description" for="sbs_options[number_of_comments]"><?php _e('Number of comments:'); ?></label> 
													<input type="text" size="2" maxlength="10" name="sbs_options[number_of_comments]" value="<?php echo $sbs_options['number_of_comments']; ?>" />
												</div>
												<div class="mm-number-option">
													<label class="description" for="sbs_options[comment_length]"><?php _e('Length of comments:'); ?></label> 
													<input type="text" size="2" maxlength="10" name="sbs_options[comment_length]" value="<?php echo $sbs_options['comment_length']; ?>" />
												</div>
											</td>
											<td><?php echo do_shortcode('[sbs_latest_comments]'); ?></td>
											<td>
												<label class="description" for="sbs_options[latest_comments_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[latest_comments_after]"><?php echo esc_textarea($sbs_options['latest_comments_after']); ?></textarea>
												</label>
											</td>
										</tr>
										<tr>
											<td>
												<label class="description" for="sbs_options[blog_stats_before]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[blog_stats_before]"><?php echo esc_textarea($sbs_options['blog_stats_before']); ?></textarea>
												</label>
											</td>
											<td>
												<code>[sbs_blog_stats]</code>
												<div class="mm-item-caption"><?php _e('displays all blog stats'); ?></div>
											</td>
											<td><?php echo do_shortcode('[sbs_blog_stats]'); ?></td>
											<td>
												<label class="description" for="sbs_options[blog_stats_after]">
													<textarea class="textarea" cols="20" rows="2" name="sbs_options[blog_stats_after]"><?php echo esc_textarea($sbs_options['blog_stats_after']); ?></textarea>
												</label>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
							<input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" />
						</div>
					</div>
					<div id="mm-panel-secondary" class="postbox">
						<h3><?php _e('SBS Template Tags'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<p><?php _e('Here you&rsquo;ll find code to display your blog stats anywhere in your theme template. These tags are based on the shortcodes, so check out the Shortcodes panel to customize output and for more information.'); ?></p>
							<ul>
								<li><code>&lt;?php echo do_shortcode('[sbs_posts]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_pages]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_drafts]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_comments]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_moderated]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_approved]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_users]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_cats]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_tags]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_updated]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_latest_posts]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_latest_comments]'); ?&gt;</code></li>
								<li><code>&lt;?php echo do_shortcode('[sbs_blog_stats]'); ?&gt;</code></li>
							</ul>
						</div>
					</div>
					<div id="mm-restore-settings" class="postbox">
						<h3><?php _e('Restore Default Options'); ?></h3>
						<div class="toggle<?php if (!isset($_GET["settings-updated"])) { echo ' default-hidden'; } ?>">
							<p>
								<input name="sbs_options[default_options]" type="checkbox" value="1" id="sbs_restore_defaults" <?php if (isset($sbs_options['default_options'])) { checked('1', $sbs_options['default_options']); } ?> /> 
								<label class="description" for="sbs_options[default_options]"><?php _e('Restore default options upon plugin deactivation/reactivation.'); ?></label>
							</p>
							<p>
								<small>
									<?php _e('<strong>Tip:</strong> leave this option unchecked to remember your settings. Or, to go ahead and restore all default options, check the box, save your settings, and then deactivate/reactivate the plugin.'); ?>
								</small>
							</p>
							<input type="submit" class="button-primary" value="<?php _e('Save Settings'); ?>" />
						</div>
					</div>
					<div id="mm-panel-current" class="postbox">
						<h3><?php _e('Updates &amp; Info'); ?></h3>
						<div class="toggle default-hidden">
							<div id="mm-iframe-wrap">
								<iframe src="http://perishablepress.com/current/index-sbs.html"></iframe>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div id="mm-credit-info">
				<a target="_blank" href="<?php echo $sbs_homeurl; ?>" title="<?php echo $sbs_plugin; ?> Homepage"><?php echo $sbs_plugin; ?></a> by 
				<a target="_blank" href="http://twitter.com/perishable" title="Jeff Starr on Twitter">Jeff Starr</a> @ 
				<a target="_blank" href="http://monzilla.biz/" title="Obsessive Web Design &amp; Development">Monzilla Media</a>
			</div>
		</form>
	</div>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			// toggle panels
			jQuery('.default-hidden').hide();
			jQuery('#mm-panel-toggle a').click(function(){
				jQuery('.toggle').slideToggle(300);
				return false;
			});
			jQuery('h3').click(function(){
				jQuery(this).next().slideToggle(300);
			});
			jQuery('#mm-panel-primary-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#mm-panel-primary .toggle').slideToggle(300);
				return true;
			});
			jQuery('#mm-panel-secondary-link').click(function(){
				jQuery('.toggle').hide();
				jQuery('#mm-panel-secondary .toggle').slideToggle(300);
				return true;
			});
			// prevent accidents
			if(!jQuery("#sbs_restore_defaults").is(":checked")){
				jQuery('#sbs_restore_defaults').click(function(event){
					var r = confirm("<?php _e('Are you sure you want to restore all default options? (this action cannot be undone)'); ?>");
					if (r == true){  
						jQuery("#sbs_restore_defaults").attr('checked', true);
					} else {
						jQuery("#sbs_restore_defaults").attr('checked', false);
					}
				});
			}
		});
	</script>

<?php } ?>