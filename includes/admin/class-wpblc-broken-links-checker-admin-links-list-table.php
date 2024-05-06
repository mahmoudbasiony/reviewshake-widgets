<?php
/**
 * The WPBLC_Broken_Links_Checker_Admin_Links_List_Table.php class.
 *
 * @package WPBLC_Broken_Links_Checker/Admin
 * @author
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

if ( ! class_exists( 'WPBLC_Broken_Links_Checker_Admin_Links_List_Table' ) ) :

	/**
	 * Links list table.
	 *
	 * Renders links in the back-end.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_List_Table
	 */
	class WPBLC_Broken_Links_Checker_Admin_Links_List_Table extends WP_List_Table {
		/**
		 * The constructor.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function __construct() {
			parent::__construct( array(
				'singular' => 'link',
				'plural'   => 'links',
				'ajax'     => false,
			) );
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function prepare_items() {

			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );
			

			$per_page     = get_user_option('links_per_page');
			$current_page = $this->get_pagenum();
			$total_items  = $this->record_count();

			if ( !isset($per_page) || $per_page <= 0 ) {
				$per_page = 10;
			}

			$this->set_pagination_args([
				'total_items' => $total_items,
				'per_page'    => $per_page
			]);

			$this->items = $this->get_links($per_page, $current_page);
		}

		public function get_links($per_page = 5, $page_number = 1) {
			$links = get_option( 'wpblc_broken_links_checker_links', [] );

			$data = [];

			if (isset($links['broken'])) {
				// Apply pagination to the array
				$data = array_slice($links['broken'], (($page_number - 1) * $per_page), $per_page);
			}

			return $data;
		}

		public function record_count() {
			$links = get_option( 'wpblc_broken_links_checker_links', [] );

			// Count the total number of items in the array
			return isset($links['broken']) ? count($links['broken']) : 0;
		}

		/**
		 * Returns the list of columns.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				'title' => __( 'Title', 'wpblc-broken-links-checker' ), // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				'post_type' => __( 'Type', 'wpblc-broken-links-checker' ),
				'link' => __( 'Link', 'wpblc-broken-links-checker' ),
				'type' => __( 'Status', 'wpblc-broken-links-checker' ),
				'code' => __( 'Code', 'wpblc-broken-links-checker' ),
				'text' => __( 'Message', 'wpblc_broken_links_checker' ),
				'detected_at' => __( 'Detected at', 'wpblc_broken_links_checker' ),
			);
		}

		/**
		 * Returns the list of sortable columns.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			return array();
		}

		/**
		 * Renders the default column.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $item        The current item.
		 * @param string $column_name The current column name.
		 *
		 * @return string
		 */
		public function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'title':
				case 'post_type':
				case 'code':
				case 'text':
				case 'link':
				case 'detected_at':
					return $item[$column_name];

				case 'type':
					return ucfirst( $item['type'] );

				default:
					return print_r( $item, true );
			}
		}

		/**
		 * Renders the title column.
		 *
		 * @since 1.0.0
		 *
		 * @param array $item The current item.
		 *
		 * @return string
		 */
		public function column_title( $item ) {
			$actions = array(
				'edit' => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( get_edit_post_link( $item['post_id'] ) ),
					__( 'Edit', 'wpblc-broken-links-checker' )
				),
				'find' => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( $this->get_page_url( $item['post_id'] ) ),
					__( 'Find', 'wpblc-broken-links-checker' )
				),
				'mark-as-fixed' => sprintf(
					'<a id="wpblc-mark-as-fixed" data-link="%s" href="#">%s</a>',
					esc_attr( $item['link'] ),
					__( 'Mark as Fixed', 'wpblc-broken-links-checker' )
				),
			);

			return sprintf( '<strong><a href="%1$s" target="_blank">%2$s</a></strong> %3$s', esc_url( get_permalink( $item['post_id'] ) ), $this->get_post_or_comment_title($item), $this->row_actions( $actions ) );
		}

		/**
		 * Renders the URL column.
		 *
		 * @since 1.0.0
		 *
		 * @param array $item The current item.
		 *
		 * @return string
		 */
		public function column_link( $item ) {
			$actions = array(
				'edit' => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( get_edit_post_link( $item['post_id'] ) ),
					__( 'Edit', 'wpblc-broken-links-checker' )
				),
				'find' => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( $this->get_page_url( $item['post_id'] ) ),
					__( 'Find', 'wpblc-broken-links-checker' )
				),
				'mark-as-fixed' => sprintf(
					'<a id="wpblc-mark-as-fixed" data-link="%s" href="#">%s</a>',
					esc_attr( $item['link'] ),
					__( 'Mark as Fixed', 'wpblc-broken-links-checker' )
				),
			);

			return sprintf( '<strong><a href="%1$s" target="_blank">%1$s</a></strong> %2$s', esc_url( $item['link'] ), $this->row_actions( $actions ) );
		}

		/**
		 * Renders the post type column.
		 *
		 * @since 1.0.0
		 *
		 * @param array $item The current item.
		 *
		 * @return string
		 */
		public function column_post_type( $item ) {
			$post_id = $item['post_id'];
			$post_type = get_post_type( $post_id );

			return $post_type;
		}

		/**
		 * 
		 */
		private function get_post_or_comment_title( $item ) {
			$post_id = $item['post_id'];

			return get_the_title( $post_id );
		}

		/**
		 * 
		 */
		private function get_page_url( $post_id ) {
			return get_permalink( $post_id );
		}


		/**
		 * Renders the status column.
		 *
		 * @since 1.0.0
		 *
		 * @param array $item The current item.
		 *
		 * @return string
		 */
		public function column_code( $item ) {
			return sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://http.dev/' . $item['code'] ), $item['code'] );
		}

		/**
		 * Renders the table.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		// public function display() {
		// 	$this->prepare_items();

		// 	echo '<div class="wrap">';
		// 	echo '<h1 class="wp-heading-inline">' . esc_html__( 'Broken Links', 'wpblc-broken-links-checker' ) . '</h1>';
		// 	$this->display_tablenav( 'top' );
		// 	$this->display();
		// 	echo '</div>';
		// }

		/**
		 * Renders the table navigation.
		 *
		 * @since 1.0.0
		 *
		 * @param string $which The current navigation position.
		 *
		 * @return void
		 */
		public function display_tablenav( $which ) {
			echo '<div class="tablenav ' . esc_attr( $which ) . '">';

			$this->extra_tablenav( $which );
			$this->pagination( $which );

			echo '<br class="clear" />';
			echo '</div>';
		}

		/**
		 * Renders the table navigation.
		 *
		 * @since 1.0.0
		 *
		 * @param string $which The current navigation position.
		 *
		 * @return void
		 */
		public function extra_tablenav( $which ) {
			$actions = array(
				'scan_site' => array(
					'confirmation' => __( 'Are you sure you want to scan the site for broken links?', 'wpblc-broken-links-checker' ),
					'button'       => __( 'Scan Now', 'wpblc-broken-links-checker' ),
				),
			);

			echo '<div class="alignleft actions">';

			foreach ( $actions as $action => $params) {
				echo '<a class="button button-primary" id="wpblc-manual-scan" href="' . wp_nonce_url( admin_url( 'admin.php?page=wpblc-broken-links-checker&tab=scan&action=' . $action ), $action, '_wpnonce' ) . '">' . $params['button'] . '</a>';
			}

			echo '</div>';
		}

	}

endif;
