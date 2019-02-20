<?php
/**
 * WPEDPC_Campaign Object
 *
 * @package     WPEDPC
 * @subpackage  Classes/Campaign
 * @copyright   Copyright (c) 2015, Esteban Truelsegaard
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       5.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//Custom functions to use without call an object or the class
/**
 * Retrieve is_actived
 *
 * @since 5.0
 * @return bool
 */
function wpedpc_is_campaign_active($post_id ) {
	$active = get_post_meta( $post_id, 'active', true );
	if ( $active ) {
		$active = (bool)$active ;
	} else {
		$active = false;
	}
	//*** Override the wpedpcampaign active.
	return apply_filters( 'wpedpcampaign_is_activated', $active, $post_id );
}
add_filter( 'wpedpc_is_campaign_active','wpedpc_is_campaign_active', 10, 1 );


/**
 * WPEDPC_Campaign Class
 *
 * @since 5.0
 */



class WPEDPC_Campaign {

	public $ID = 0;
	public $active = 0;
	public $movetotrash;
	public $deletemedia;
	public $delimgcontent;
	public $period;
	public $wpedpc_limit;
	public $titledel;
	public $contentdel;
	public $allcat = 0;
	public $categories = array();
	public $cpostypes;
	public $cposstatuses;
	public $minmax;
	public $excluded_ids = '';
	public $schedule;
	public $logs = array();

	public $doingcron;

	/**
	 * Declare the default properties in WP_Post as we can't extend it
	 * Anything we've declared above has been removed.
	 */
	public $post_author = 0;
	public $post_date = '0000-00-00 00:00:00';
	public $post_date_gmt = '0000-00-00 00:00:00';
	public $post_content = '';
	public $post_title = '';
	public $post_excerpt = '';
	public $post_status = 'publish';
	public $comment_status = 'open';
	public $ping_status = 'open';
	public $post_password = '';
	public $post_name = '';
	public $to_ping = '';
	public $pinged = '';
	public $post_modified = '0000-00-00 00:00:00';
	public $post_modified_gmt = '0000-00-00 00:00:00';
	public $post_content_filtered = '';
	public $post_parent = 0;
	public $guid = '';
	public $menu_order = 0;
	public $post_mime_type = '';
	public $comment_count = 0;
	public $filter;

	/**
	 * Get things going
	 *
	 * @since 5.0
	 */
	public function __construct( $_id = false, $_args = array() ) {

		$wpedpcampaign = WP_Post::get_instance( $_id );

		return $this->setup_wpedpcampaign( $wpedpcampaign );

	}

	/**
	 * Given the wpedpcampaign data, let's set the variables
	 *
	 * @since  5.0
	 * @param  object $wpedpcampaign The WPEDPC_Campaign Object
	 * @return bool             If the setup was successful or not
	 */
	function setup_wpedpcampaign( $wpedpcampaign ) {

		if( ! is_object( $wpedpcampaign ) ) {
			return false;
		}

		if( ! is_a( $wpedpcampaign, 'WP_Post' ) ) {
			return false;
		}

		if( 'wpedpcampaign' !== $wpedpcampaign->post_type ) {
			return false;
		}
		
		if ( $wpedpcampaign->ID ){
			$custom_field_keys = get_post_custom($wpedpcampaign->ID);
			foreach ( $custom_field_keys as $key => $value ) {
				$custom_field_keys[$key] = maybe_unserialize($value[0]);
			}
			$custom_field_keys = apply_filters('wpedpc_clean_campaign_fields', $custom_field_keys );
			foreach ( $custom_field_keys as $key => $value ) {
				$this->$key = $value;
			}
		}
		
		foreach ( $wpedpcampaign as $key => $value ) {
			switch ( $key ) {
				default:
					$this->$key = $value;
					break;
			}
		}

		return true;

	}

	/**
	 * Magic __save all vars to post meta data
	 *
	 * @since 5.0
	 */
	public function __save( ) {

		return $this->save_wpedpcampaign();

	}

	/**
	 * Given the wpedpcampaign data, let's set the variables
	 *
	 * @since  5.0
	 * @param  object $wpedpcampaign The WPEDPC_Campaign Object
	 * @return bool             If the setup was successful or not
	 */
	private function save_wpedpcampaign( ) {
		/*
		if( ! is_a( $this, 'WP_Post' ) ) {
			return false;
		}
		*/

		if ( $this->ID ){
			$post_id = $this->ID;
			$custom_field_keys = get_object_vars($this);
			$custom_field_keys = apply_filters('wpedpc_clean_campaign_fields', $custom_field_keys );
			
			
			foreach ( $custom_field_keys as $field => $value ) {
				
				if ( !empty( $value ) ) {
					$new = apply_filters( 'wpedpc_metabox_save_' . $field, $value );  //filtra cada campo antes de grabar
					
					if (isset($this->$field)) {
						update_post_meta( $post_id, $field, $new );
					}
					
					
				} else {
					delete_post_meta( $post_id, $field );
				}
			}
		}

		return true;

	}

	/**
	 * Magic __get function to dispatch a call to retrieve a private property
	 * 
	 * extended if not method try to create with a generic custom function to get meta data 
	 *
	 * @since 5.0
	 */
	public function __get( $key ) {
		if( method_exists( $this, 'get_' . $key ) ) {
			return call_user_func( array( $this, 'get_' . $key ) );
			
		} elseif ( isset( $this->$key ) ) {
			return $this->$key ;
			
		}else{
			$result = apply_filters( 'wpedpc_get_campaign_'.$key, function() use ($key) {
				if ( !isset( $this->$key ) ) {
					$this->$key = get_post_meta( $this->ID, $key, true );
				}
				return $this->$key;
			});
			
			if(!empty($result)){
				return $result;
			}else{
				return new WP_Error( 'wpedpc-wpedpcampaign-invalid-property', sprintf( __( 'Can\'t get property %s', 'etruel-del-post-copies' ), $key ) );
			}
		}
	}

	/**
	 * Creates a wpedpcampaign
	 *
	 * @since  2.3.6
	 * @param  array  $data Array of attributes for a wpedpcampaign
	 * @return mixed  false if data isn't passed and class not instantiated for creation, or New WPEDPC_Campaign ID
	 */
	public function create( $data = array() ) {

		if ( $this->id != 0 ) {
			return false;
		}

		$defaults = array(
			'post_type'   => 'wpedpcampaign',
			'post_status' => 'draft',
			'post_title'  => __( 'New Campaign of Deletes', 'etruel-del-post-copies' )
		);

		$args = wp_parse_args( $data, $defaults );

		do_action( 'wpedpc_wpedpcampaign_pre_create', $args );

		$id = wp_insert_post( $args, true );

		$wpedpcampaign = WP_Post::get_instance( $id );

		do_action( 'wpedpc_wpedpcampaign_post_create', $id, $args );

		return $this->setup_wpedpcampaign( $wpedpcampaign );

	}

	/**
	 * Retrieve the ID
	 *
	 * @since 5.0
	 * @return int
	 */
	public function get_ID() {

		return $this->ID;

	}


	/**
	 * Retrieve is_actived
	 *
	 * @since 5.0
	 * @return bool
	 */
	public function is_activated() {
		if ( ! isset( $this->active ) ) {
			$this->active = get_post_meta( $this->ID, 'active', true );
			if ( $this->active ) {
				$this->active = (bool)$this->active ;
			} else {
				$this->active = false;
			}
		}
		/**
		 * Override the wpedpcampaign active.
		 */
		return apply_filters( 'wpedpcampaign_is_activated', $this->active, $this->ID );
	}


	/**
	 * Updates a single meta entry for the wpedpcampaign
	 *
	 * @since  5.0
	 * @access private
	 * @param  string $meta_key   The meta_key to update
	 * @param  string|array|object $meta_value The value to put into the meta
	 * @return bool             The result of the update query
	 */
	private function update_meta( $meta_key = '', $meta_value = '' ) {

		global $wpdb;

		if ( empty( $meta_key ) || empty( $meta_value ) ) {
			return false;
		}

		// Make sure if it needs to be serialized, we do
		$meta_value = maybe_serialize( $meta_value );

		if ( is_numeric( $meta_value ) ) {
			$value_type = is_float( $meta_value ) ? '%f' : '%d';
		} else {
			$value_type = "'%s'";
		}

		$sql = $wpdb->prepare( "UPDATE $wpdb->postmeta SET meta_value = $value_type WHERE post_id = $this->ID AND meta_key = '%s'", $meta_value, $meta_key );

		if ( $wpdb->query( $sql ) ) {

			clean_post_cache( $this->ID );
			return true;

		}

		return false;
	}

}
