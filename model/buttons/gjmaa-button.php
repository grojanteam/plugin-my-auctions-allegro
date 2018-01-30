<?php
add_action( 'init', 'gjmaa_button' );
function gjmaa_button() {
    add_filter( "mce_external_plugins", "gjmaa_add_button" );
    add_filter( 'mce_buttons', 'gjmaa_register_button' );
}
function gjmaa_add_button( $plugin_array ) {
    $plugin_array['gjmaa'] = GJMAA_URL . 'js/gjmaa-button-plugin.js';
	wp_enqueue_script( 'jquery-ui-dialog');
	wp_enqueue_style( 'jquery_ui_css', GJMAA_URL . 'css/jquery/jquery-ui.min.css' );
    return $plugin_array;
}
function gjmaa_register_button( $buttons ) {
    array_push( $buttons, 'gjmaa' );
    return $buttons;
}