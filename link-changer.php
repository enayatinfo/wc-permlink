<?php
/**
 * Plugin Name: Presta Permlink
 * Description: Change Woocommerce product link to Prestashop permlink
 * Author: Usef-Enayati
 * Version: 1.0
 * Author URI:  http://Diad.ir
 */

defined('ABSPATH') || exit;

if (!class_exists('ChangeWcPath')) {
	final class ChangeWcPath {

		protected static $_instance = null;
		public static function instance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		public function __clone() {}
		public function __wakeup() {}
		public function __construct() {

			add_action('after_switch_theme', 'flush_rewrite_rules');

			register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

			register_activation_hook(__FILE__, array($this, 'flush_rewrites'));

			add_action('init', array(__CLASS__, 'rewrites_init'));

			add_filter('post_type_link', array(__CLASS__, 'change_post_type_link'), 1, 3);

			if (!is_admin()) {

				add_filter('user_trailingslashit', array(__CLASS__, 'untrailingslash_single'), 10, 2);
			}
		}

		static public function untrailingslash_single($url, $type) {
			if ('single' === $type) {
				return untrailingslashit($url);
			}
			return trailingslashit($url);
		}

		static public function flush_rewrites() {
			self::rewrites_init();
			flush_rewrite_rules();
		}
		static public function change_post_type_link($link, $post = 0, $leavename = '') {
			if ($post->post_type == 'product') {
			    $pr = wc_get_product($post);
                $cat = get_the_terms( $pr->get_id(), 'product_cat');
                $link = home_url( $cat[0]->slug .'/' . $post->ID . '-' . $post->post_name . '.html');
				return $link;
			} else {
				return untrailingslashit($link);
			}
		}
		static public function rewrites_init() {

            add_rewrite_rule('\D.+/([0-9]+)-\D.+.html?', 'index.php?post_type=product&p=$matches[1]', 'top');
		}

	}
	$GLOBALS['ChangeWcPath'] = ChangeWcPath::instance();
}
?>