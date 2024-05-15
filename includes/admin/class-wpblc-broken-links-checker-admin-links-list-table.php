<?php
/**
 * The WPBLC_Broken_Links_Checker_Admin_Links_List_Table class.
 *
 * @package WPBLC_Broken_Links_Checker/Admin
 * @author Ilias Chelidonis.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
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
			parent::__construct(
				array(
					'singular' => 'link',
					'plural'   => 'links',
					'ajax'     => false,
				)
			);
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function prepare_items() {

			$columns  = $this->get_columns();
			$hidden   = array();
			$sortable = $this->get_sortable_columns();

			$this->_column_headers = array( $columns, $hidden, $sortable );

			$filter = array(
				'type'     => '',
				'status'   => '',
				'location' => '',
			);

			if ( isset( $_REQUEST['wpblc_type_filter'] ) && '' !== $_REQUEST['wpblc_type_filter'] ) {
				$filter['type'] = sanitize_text_field( $_REQUEST['wpblc_type_filter'] );
			}

			if ( isset( $_REQUEST['wpblc_status_filter'] ) && '' !== $_REQUEST['wpblc_status_filter'] ) {
				$filter['status'] = sanitize_text_field( $_REQUEST['wpblc_status_filter'] );
			}

			if ( isset( $_REQUEST['wpblc_location_filter'] ) && '' !== $_REQUEST['wpblc_location_filter'] ) {
				$filter['location'] = sanitize_text_field( $_REQUEST['wpblc_location_filter'] );
			}

			$per_page     = get_user_option( 'links_per_page' );
			$current_page = $this->get_pagenum();
			$total_items  = $this->record_count( $filter );

			if ( ! isset( $per_page ) || $per_page <= 0 ) {
				$per_page = 20;
			}

			$this->set_pagination_args(
				array(
					'total_items' => $total_items,
					'per_page'    => $per_page,
				)
			);

			$this->items = $this->get_links( $per_page, $current_page, $filter );
		}

		/**
		 * Get the links from the database.
		 *
		 * @param int   $per_page     Number of items to display per page.
		 * @param int   $page_number  Current page number.
		 * @param array $filter     The filter to apply.
		 *
		 * @since 1.0.0
		 *
		 * @return array
		 */
		public function get_links( $per_page = 5, $page_number = 1, $filter = array() ) {
			// Get the links from the database.
			$links = get_option( 'wpblc_broken_links_checker_links', array() );

			$data = array();
			if ( isset( $links['broken'] ) ) {
				// Apply the filter to the array.
				if ( ! empty( array_filter( $filter ) ) ) {
					foreach ( $filter as $key => $value ) {
						if ( '' !== $value ) {
							switch ( $key ) {
								case 'type':
									$links['broken'] = array_filter(
										$links['broken'],
										function ( $link ) use ( $value ) {
											return $link['link_source'] === $value;
										}
									);
									break;

								case 'status':
									$links['broken'] = array_filter(
										$links['broken'],
										function ( $link ) use ( $value ) {
											return $link['marked_fixed'] === $value;
										}
									);
									break;

								case 'location':
									$links['broken'] = array_filter(
										$links['broken'],
										function ( $link ) use ( $value ) {
											if ( $link['is_comment'] ) {
												return 'comment' === $value;
											}

											return get_post_type( $link['ID'] ) === $value;
										}
									);
									break;

								default:
									return $links['broken'];
								break;
							}
						}
					}
				}

				// Apply pagination to the array.
				$data = array_slice( $links['broken'], ( ( $page_number - 1 ) * $per_page ), $per_page );
			}

			return $data;
		}

		/**
		 * Get the total number of records.
		 *
		 * @param array $filter The filter to apply.
		 *
		 * @since 1.0.0
		 *
		 * @return int
		 */
		public function record_count( $filter ) {
			$links = get_option( 'wpblc_broken_links_checker_links', array() );

			if ( empty( array_filter( $filter ) ) ) {
				// Count the total number of items in the array.
				return isset( $links['broken'] ) ? count( $links['broken'] ) : 0;
			}

			foreach ( $filter as $key => $value ) {
				if ( '' !== $value ) {
					switch ( $key ) {
						case 'type':
							$links['broken'] = array_filter(
								$links['broken'],
								function ( $link ) use ( $value ) {
									return $link['link_source'] === $value;
								}
							);

							return isset( $links['broken'] ) ? count( $links['broken'] ) : 0;
						break;

						case 'status':
							$links['broken'] = array_filter(
								$links['broken'],
								function ( $link ) use ( $value ) {
									return $link['marked_fixed'] === $value;
								}
							);

							return isset( $links['broken'] ) ? count( $links['broken'] ) : 0;
						break;

						case 'location':
							$links['broken'] = array_filter(
								$links['broken'],
								function ( $link ) use ( $value ) {
									if ( $link['is_comment'] ) {
										return esc_html__( 'Comments', 'wpblc-broken-links-checker' ) === $value;
									} else {
										return get_post_type( $link['ID'] ) === $value;
									}
								}
							);

							return isset( $links['broken'] ) ? count( $links['broken'] ) : 0;
						break;

						default:
							return isset( $links['broken'] ) ? count( $links['broken'] ) : 0;
						break;
					}
				}
			}
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
				'link'        => esc_html__( 'Link', 'wpblc-broken-links-checker' ),
				'type'        => esc_html__( 'Status', 'wpblc-broken-links-checker' ),
				'code'        => esc_html__( 'Response', 'wpblc-broken-links-checker' ),
				'source'      => esc_html__( 'Source', 'wpblc-broken-links-checker' ),
				'post_type'   => esc_html__( 'Post Type', 'wpblc-broken-links-checker' ),
				'detected_at' => esc_html__( 'Date', 'wpblc-broken-links-checker' ),
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
				case 'type':
				case 'source':
				case 'post_type':
				case 'code':
				case 'text':
				case 'link':
				case 'detected_at':
					return $item[ $column_name ];

				default:
					return print_r( $item, true );
			}
		}

		/**
		 * Renders the source column.
		 *
		 * @since 1.0.0
		 *
		 * @param array $item The current item.
		 *
		 * @return string
		 */
		public function column_source( $item ) {
			$actions = array(
				'edit' => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( wpblc_get_post_or_comment_edit_link( $item ) ),
					esc_html__( 'Edit', 'wpblc-broken-links-checker' )
				),
				'view' => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( wpblc_get_post_or_comment_link( $item ) ),
					esc_html__( 'View', 'wpblc-broken-links-checker' )
				),
			);

			return sprintf( '<strong><a href="%1$s" target="_blank">%2$s</a></strong> %3$s', wpblc_get_post_or_comment_link( $item ), wpblc_get_post_or_comment_title( $item ), $this->row_actions( $actions ) );
		}

		/**
		 * Renders the link column.
		 *
		 * @since 1.0.0
		 *
		 * @param array $item The current item.
		 *
		 * @return string
		 */
		public function column_link( $item ) {
			$actions = array(
				'edit'          => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( wpblc_get_post_or_comment_edit_link( $item ) ),
					esc_html__( 'Edit', 'wpblc-broken-links-checker' )
				),
				'find'          => sprintf(
					'<a href="%s" target="_blank">%s</a>',
					esc_url( add_query_arg( 'broken-link', $item['link'], wpblc_get_post_or_comment_link( $item ) ) ),
					esc_html__( 'Find', 'wpblc-broken-links-checker' )
				),
				'mark-as-fixed' => sprintf(
					'<a id="wpblc-mark-as-fixed" class="wpblc-mark-as-fixed %s" data-link="%s" data-post-id="%s" href="#">%s</a>',
					esc_attr( $item['marked_fixed'] ),
					esc_attr( $item['link'] ),
					esc_attr( $item['ID'] ),
					'fixed' === $item['marked_fixed'] ? esc_html__( 'Mark as Broken', 'wpblc-broken-links-checker' ) : esc_html__( 'Mark as Fixed', 'wpblc-broken-links-checker' )
				),
			);

			return sprintf( '<strong><a href="%1$s" target="_blank">%1$s</a></strong> %2$s', esc_url( $item['link'] ), $this->row_actions( $actions ) );
		}

		/**
		 * Renders the type column.
		 *
		 * @since 1.0.0
		 *
		 * @param array $item The current item.
		 *
		 * @return string
		 */
		public function column_type( $item ) {
			$title = 'fixed' === $item['marked_fixed'] ? esc_html__( 'Fixed', 'wpblc-broken-links-checker' ) : $item['type'];
			return sprintf( '<div class="status-type %1$s">%2$s</div>', esc_attr( $item['marked_fixed'] ), ucwords( $title ) );
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
			return wpblc_get_post_or_comment_type( $item );
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
			return sprintf( '<code>%1$s<a href="%2$s" target="_blank">%3$s</a></code><span class="message">%4$s</span>', esc_html__( 'Code: ', 'wpblc-broken-links-checker' ), esc_url( 'https://http.dev/' . $item['code'] ), $item['code'], $item['text'] );
		}

		/**
		 * Renders the filter dropdown form.
		 *
		 * @param string $which The current navigation position.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		public function extra_tablenav( $which ) {
			if ( 'top' === $which ) {
				// The code that goes before the table is here.
				$selected_type     = isset( $_REQUEST['wpblc_type_filter'] ) ? sanitize_text_field( $_REQUEST['wpblc_type_filter'] ) : '';
				$selected_status   = isset( $_REQUEST['wpblc_status_filter'] ) ? sanitize_text_field( $_REQUEST['wpblc_status_filter'] ) : '';
				$selected_location = isset( $_REQUEST['wpblc_location_filter'] ) ? sanitize_text_field( $_REQUEST['wpblc_location_filter'] ) : '';
				?>
					<div class="alignleft actions">
						<select name="wpblc_type_filter">
							<option value="" <?php selected( $selected_type, '' ); ?>"><?php esc_html_e( 'All Types', 'wpblc-broken-links-checker' ); ?></option>
							<option value="internal" <?php selected( $selected_type, 'internal' ); ?>"><?php esc_html_e( 'Internal', 'wpblc-broken-links-checker' ); ?></option>
							<option value="external" <?php selected( $selected_location, 'external' ); ?>"><?php esc_html_e( 'External', 'wpblc-broken-links-checker' ); ?></option>
						</select>

						<select name="wpblc_status_filter">
							<option value="" <?php selected( $selected_status, '' ); ?>"><?php esc_html_e( 'All Statuses', 'wpblc-broken-links-checker' ); ?></option>
							<option value="fixed" <?php selected( $selected_status, 'fixed' ); ?>"><?php esc_html_e( 'Fixed', 'wpblc-broken-links-checker' ); ?></option>
							<option value="not-fixed" <?php selected( $selected_status, 'not-fixed' ); ?>""><?php esc_html_e( 'Broken', 'wpblc-broken-links-checker' ); ?></option>
						</select>

						<select name="wpblc_location_filter">
							<option value="" <?php selected( $selected_location, '' ); ?>"><?php esc_html_e( 'All Locations', 'wpblc-broken-links-checker' ); ?></option>
							<option value="post" <?php selected( $selected_location, 'post' ); ?>"><?php esc_html_e( 'Posts', 'wpblc-broken-links-checker' ); ?></option>
							<option value="page" <?php selected( $selected_location, 'page' ); ?>"><?php esc_html_e( 'Pages', 'wpblc-broken-links-checker' ); ?></option>
							<option value="comment" <?php selected( $selected_location, 'comment' ); ?>"><?php esc_html_e( 'Comments', 'wpblc-broken-links-checker' ); ?></option>
						</select>

						<input type="hidden" name="page" value="wpblc-broken-links-checker">
						<input type="hidden" name="tab" value="scan">

						<?php submit_button( 'Filter', 'button', 'filter_action', false ); ?>
					</div>
				<?php
			}
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
		public function display_tablenav( $which ) {
			echo '<div class="tablenav ' . esc_attr( $which ) . '">';

			$this->extra_tablenav( $which );
			$this->pagination( $which );

			echo '<br class="clear" />';
			echo '</div>';
		}
	}

endif;
