<?php
/**
 * Plugin Name: Raketech
 * Description: WordPress Developer Coding Exercise
 * Version: 1.0
 * Author: Alejandro Fernández Pradas
 * Author URI: https://alexpradas.com
 * License: GPL2
 */


if( ! defined( 'ABSPATH') ){
    exit;
}


if ( ! function_exists( 'raketech_enqueue_style' ) ) :

   /**
    * Enqueue default styles for Raketech Plugin and Font Awesome icons.
    *
    * @return void
    */

   function raketech_enqueue_style() {
       wp_enqueue_style( 'raketech-style', plugins_url('/raketech/assets/css/style.css'), array(), rand()); 
       wp_enqueue_style( 'load-fa', 'https://use.fontawesome.com/releases/v5.3.1/css/all.css' );
   }
endif;

add_action( 'wp_enqueue_scripts', 'raketech_enqueue_style' );