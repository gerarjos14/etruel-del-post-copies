<?php

// Exit if accessed directly
if (!defined('ABSPATH'))
    exit;

/**
 * WPEDPC_Select2 Class
 * 
 * Build menu fields with the select2 library.
 * 
 */
class WPEDPC_Select2 {

    public function __construct() {
        add_action( 'admin_enqueue_scripts', array ( 'WPEDPC_Select2' , 'enqueue_select2_scripts' ) );
        
        /******* Ajax */
        add_action( 'wp_ajax_request_excluded_posts', array ( $this , 'request_excluded_posts' ) );
        add_action( 'wp_ajax_nopriv_request_excluded_posts', array ( $this , 'request_excluded_posts' ) );        
    }
    /**
     * Enqueue the select2 library
     */
    public static function enqueue_select2_scripts() {
        wp_enqueue_style( 'wpedpc-select2-css' , 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css' );
        wp_enqueue_script( 'wpedpc-select2-js' , 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js', array( 'jquery' ) );
        wp_enqueue_script( 'ajx-select2-js' , WPEDPC_PLUGIN_URL .'/includes/js/ajax-select2.js', array( 'jquery', 'wpedpc-select2-js' ) );
    }
    
    public static function display_select2_menu( $excluded_posts ){
        
        $html = '';
        
        $list_excluded_posts = explode( ',', $excluded_posts );
        
        $list_posts_saved = '';
        
        foreach ($list_excluded_posts as $id ) {
            $title = get_the_title( $id );
            
            $title_to_display = ( mb_strlen( $title ) > 30 ) ? mb_substr( $title, 0, 29 ) . '...' : $title;
            
            $list_posts_saved .=  '<option value="' . $id . '" selected="selected">' . $title_to_display . '</option>';
        }
        
        $html .= '<select id="excluded_posts" name="excluded_posts[]" multiple="multiple" style="width:99%;max-width:25em;">'; 
        $html .= $list_posts_saved;
        $html .= '</select>';
        
        return $html;
    }
    public function request_excluded_posts(){
	$return = array();
	$search_results = new WP_Query( array( 
		's' => $_GET['q'],
		'post_status' => 'publish',
                'post_type' => 'post' ,
                'orderby' => 'title', 
                'order' => 'ASC'
	) );
	if( $search_results->have_posts() ) :
		while( $search_results->have_posts() ) : $search_results->the_post();	
			// shorten the title a little
			$title = ( mb_strlen( $search_results->post->post_title ) > 30 ) ? mb_substr( $search_results->post->post_title, 0, 29 ) . '...' : $search_results->post->post_title;
			$return[] = array( $search_results->post->ID, $title ); // array( Post ID, Post Title )
		endwhile;
	endif;
	echo json_encode( $return );
	
        die();        
    }

}

return new WPEDPC_Select2();
