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





if ( ! function_exists( 'raketech_get_reviews' ) ) :

	/**
	 * Get reviews array from Raketech API data.json. Only get those under the key 575, and ordered by the 'position' key
	 *
	 *
	 * @return array  reviews array array(
     *           'position', 
     *           'features', 
     *           'logo',  
     *           'brand_id', 
     *           'rating', 
     *           'bonus', 
     *           'play_url', 
     *           'terms_and_conditions')
	 */

    if ( ! function_exists( 'raketech_get_final_url' ) ) :

        /**
         * Get last url after redirection
         *
         * @param string $url Nav menu item start element.
         *
         *
         * @return string Final url after redirects.
         */
        function raketech_get_final_url($url) {
            $curlhandle = curl_init();
            curl_setopt($curlhandle, CURLOPT_URL, $url);
            curl_setopt($curlhandle, CURLOPT_HEADER, 1);
            curl_setopt($curlhandle, CURLOPT_USERAGENT, 'googlebot');
            curl_setopt($curlhandle, CURLOPT_FOLLOWLOCATION, 0);
            curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1);
            $final = curl_exec($curlhandle);
            if (preg_match('~Location: (.*)~i', $final, $lasturl)) {
                $loc = trim($lasturl[1]);
                return $loc;
            } 
            else {
                return $loc= $url; 
            }
        }
    endif;


    function raketech_get_reviews() {
        $request_reviews = wp_remote_get('https://alexpradas.com/wp-content/themes/Divi/raketechapi.json' );
        $response_reviews = wp_remote_retrieve_body($request_reviews);
        $data_reviews = json_decode( $response_reviews );
        $raketech_reviews = $data_reviews->{'toplists'}->{'575'};
        foreach ($raketech_reviews as $widget){
            $logo= raketech_get_final_url($widget->{'logo'});
            $brand_id =   $widget->{'brand_id'};
            $rating = $widget->{'info'}->{'rating'};
            $bonus = $widget->{'info'}->{'bonus'};
            $play_url = $widget->{'play_url'};
            $terms_and_conditions = $widget->{'terms_and_conditions'};
            $features = $widget->{'info'}->{'features'};
            $position = $widget->{'position'};

            $raketech_reviews_array[] = array(
                'position' => $position, 
                'features' => $features, 
                'logo' => $logo,  
                'brand_id' => $brand_id, 
                'rating' => $rating, 
                'bonus' => $bonus, 
                'play_url' => $play_url, 
                'terms_and_conditions' => $terms_and_conditions
            );
        }    

        array_multisort( array_column($raketech_reviews_array, "position"), SORT_DESC, $raketech_reviews_array );
        //var_dump($raketech_reviews_array);
        return $raketech_reviews_array;
    }
endif;


