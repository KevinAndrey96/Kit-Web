<?php
/**
 * Client custom meta fields.
 *
 * @package Betheme
 * @author Muffin group
 * @link https://muffingroup.com
 */

/**
 * Create new post type
 */

if( ! function_exists( 'mfn_client_post_type' ) )
{
	function mfn_client_post_type()
	{
		$client_item_slug = mfn_opts_get( 'client-slug', 'client-item' );

		$labels = array(
			'name' => esc_html__( 'Clients', 'mfn-opts' ),
			'singular_name' => esc_html__( 'Client', 'mfn-opts' ),
			'add_new' => esc_html__( 'Add New', 'mfn-opts' ),
			'add_new_item' => esc_html__( 'Add New Client', 'mfn-opts' ),
			'edit_item' => esc_html__( 'Edit Client', 'mfn-opts' ),
			'new_item' => esc_html__( 'New Client', 'mfn-opts' ),
			'view_item' => esc_html__( 'View Clients', 'mfn-opts' ),
			'search_items' => esc_html__( 'Search Clients', 'mfn-opts' ),
			'not_found' => esc_html__( 'No clients found', 'mfn-opts' ),
			'not_found_in_trash' => esc_html__( 'No clients found in Trash', 'mfn-opts' ),
		);

		$args = array(
			'labels' => $labels,
			'menu_icon' => 'dashicons-businessman',
			'public' => false,
			'show_ui' => true,
			'supports' => array('title', 'thumbnail', 'page-attributes'),
		);

		register_post_type( 'client', $args );

		register_taxonomy( 'client-types', 'client', array(
			'label' =>  esc_html__( 'Client categories', 'mfn-opts' ),
			'hierarchical' => true,
		));
	}
}
add_action( 'init', 'mfn_client_post_type' );

/**
 * Edit columns
 */

if( ! function_exists( 'mfn_client_edit_columns' ) )
{
	function mfn_client_edit_columns( $columns )
	{
		$newcolumns = array(
			"cb" => '<input type="checkbox" />',
			"client_thumbnail" => esc_html__( 'Thumbnail', 'mfn-opts' ),
			"title" => esc_html__( 'Title', 'mfn-opts' ),
			"client_types" => esc_html__( 'Categories', 'mfn-opts' ),
			"client_order" => esc_html__( 'Order', 'mfn-opts' ),
		);
		$columns = array_merge( $newcolumns, $columns );

		return $columns;
	}
}
add_filter( 'manage_edit-client_columns', 'mfn_client_edit_columns' );

/**
 * Custom columns
 */

if( ! function_exists( 'mfn_client_custom_columns' ) )
{
	function mfn_client_custom_columns( $column )
	{
		global $post;

		switch( $column )
		{
			case "client_thumbnail":
				if( has_post_thumbnail() ){
					the_post_thumbnail('50x50');
				}
				break;
			case "client_types":
				echo get_the_term_list( $post->ID, 'client-types', '', ', ','' );
				break;
			case "client_order":
				echo esc_html($post->menu_order);
				break;
		}
	}
}
add_action( 'manage_posts_custom_column', 'mfn_client_custom_columns' );

/**
 * Define Metabox Fields
 */

$mfn_client_meta_box = array(
	'id' => 'mfn-meta-client',
	'title' => esc_html__('Client Options', 'mfn-opts'),
	'page' => 'client',
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(

		array(
			'id' => 'mfn-post-desc',
			'type' => 'custom',
			'title' => __('Featured Image size', 'mfn-opts'),
			'sub_desc' => __('recommended', 'mfn-opts'),
			'desc' => __('150px x 75px', 'mfn-opts'),
			'action' => 'description',
		),

		array(
			'id' => 'mfn-post-link',
			'type' => 'text',
			'title' => __('Link', 'mfn-opts'),
			'sub_desc' => __('Link to client`s site', 'mfn-opts'),
		),

	),
);

/**
 * Add metabox to edit page
 */

if( ! function_exists( 'mfn_client_meta_add' ) )
{
	function mfn_client_meta_add() {
		global $mfn_client_meta_box;
		add_meta_box($mfn_client_meta_box['id'], $mfn_client_meta_box['title'], 'mfn_client_show_box', $mfn_client_meta_box['page'], $mfn_client_meta_box['context'], $mfn_client_meta_box['priority']);
	}
}
add_action('admin_menu', 'mfn_client_meta_add');

/**
 * Callback function to show fields in meta box
 */

if( ! function_exists( 'mfn_client_show_box' ) )
{
	function mfn_client_show_box() {
		global $MFN_Options, $mfn_client_meta_box, $post;
		$MFN_Options->_enqueue();

		echo '<div id="mfn-wrapper">';
			echo '<input type="hidden" name="mfn-builder-nonce" value="', wp_create_nonce('mfn-builder-nonce'), '" />';
			echo '<table class="form-table">';
				echo '<tbody>';
					foreach ($mfn_client_meta_box['fields'] as $field) {
						$meta = get_post_meta($post->ID, $field['id'], true);
						if( ! key_exists('std', $field) ) $field['std'] = false;
						$meta = ( $meta || $meta==='0' ) ? $meta : stripslashes(htmlspecialchars(( $field['std']), ENT_QUOTES ));
						mfn_meta_field_input( $field, $meta );
					}
				echo '</tbody>';
			echo '</table>';
		echo '</div>';
	}
}

/**
 * Save data when post is edited
 */

if( ! function_exists( 'mfn_client_save_data' ) )
{
	function mfn_client_save_data($post_id) {
		global $mfn_client_meta_box;

		// verify nonce

		if( key_exists( 'mfn-builder-nonce', $_POST ) ) {
			if ( ! wp_verify_nonce( $_POST['mfn-builder-nonce'], 'mfn-builder-nonce' ) ) {
				return $post_id;
			}
		}

		// check autosave

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		// check permissions

		if ( (key_exists('post_type', $_POST)) && ('page' == $_POST['post_type']) ) {
			if (!current_user_can('edit_page', $post_id)) {
				return $post_id;
			}
		} elseif (!current_user_can('edit_post', $post_id)) {
			return $post_id;
		}

		foreach ($mfn_client_meta_box['fields'] as $field) {
			$old = get_post_meta($post_id, $field['id'], true);
			if( key_exists($field['id'], $_POST) ) {
				$new = $_POST[$field['id']];
			} else {
				continue;
			}

			if ( isset($new) && $new != $old) {
				update_post_meta($post_id, $field['id'], $new);
			} elseif ('' == $new && $old) {
				delete_post_meta($post_id, $field['id'], $old);
			}
		}
	}
}
add_action('save_post', 'mfn_client_save_data');
