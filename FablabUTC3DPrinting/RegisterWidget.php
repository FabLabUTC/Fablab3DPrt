<?php
/**
 * Plugin Name: 3Dprt
 * Plugin URI: none
 * Description: none
 * Version: 1.0
 * Author: Alexandre THOUVENIN
 * Author URI: none
 * License: none
 */

const PLUGIN_DIR = "/sites/fablab/wp-content/plugins/3Dprt";
const HTML_PLUGIN_DIR = "/fablab/wp-content/plugins/3Dprt";

function _3Dprt_Register($atts)
{
    ob_start();
    include PLUGIN_DIR . '/Client/LoginService.php';
    return utf8_encode(ob_get_clean());
}
add_shortcode( '_3Dprt_Register', '_3Dprt_Register' );
/*
add_action( 'widgets_init', function(){
     register_widget( 'RegisterWidget' );
});

class RegisterWidget extends WP_Widget
{
    function __construct() {
		parent::__construct(
			'nem_3Dprt_widget', // Base ID
			__('Service impression 3D', 'text_domain'), // Name
			array( 'description' => __( 'Système de gestion d\'impression', 'text_domain' ), ) // Args
		);
	}
    
    public function widget( $args, $instance ) {
        
        
        echo $args['before_widget'];
		ob_start();
        include PLUGIN_DIR . '/Client/LoginService.php';
        echo utf8_encode(ob_get_clean());
        
		echo $args['after_widget'];
    }
    
    public function form( $instance ) {

	}
    public function update( $new_instance, $old_instance ) {

	}
}