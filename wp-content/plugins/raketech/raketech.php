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

        //Sort array ASC by 'position'
        array_multisort( array_column($raketech_reviews_array, "position"), SORT_ASC, $raketech_reviews_array );
        //var_dump($raketech_reviews_array);
        return $raketech_reviews_array;
    }
endif;


if ( ! function_exists( 'raketech_show_reviews_table' ) ) :

	/**
	 * Display the reviews table
	 *
     * @param array $raketech_array  array(
     *           'position', 
     *           'features', 
     *           'logo',  
     *           'brand_id', 
     *           'rating', 
     *           'bonus', 
     *           'play_url', 
     *           'terms_and_conditions')
     *
     *
     * @return string template for displaying the reviews.
	 */

    function raketech_show_reviews_table($raketech_array){
        
        ob_start()
    ?>
        <div class="raketech-plugin">
            <div class="raketech-reviews">
                <div class="raketech-container">
                    <div class="raketech-header">
                        <p>Casino</p>  
                        <p>Bonus</p>  
                        <p>Features</p>  
                        <p>Play</p>  
                    </div>
                    <?php for ($i = 0; $i < count($raketech_array); $i++) : ?>

                        <div class="raketech-content">
                            <div class="logo-wrapper">
                                <img src="<?php echo $raketech_array[$i]['logo']; ?>" alt="">
                                <a href="<?php echo $raketech_array[$i]['brand_id'];; ?>">Review</a>
                            </div>
                            <div class="stars-wrapper">
                                <div class="container-stars">
                                    <?php for ($k = 0 ; $k < $raketech_array[$i]['rating'];  $k++) : ?>
                                        <i class="fas fa-star" aria-hidden="true"></i>
                                    <?php endfor; ?>
                                    <?php for ($k = $raketech_array[$i]['rating'];  $k < 5; $k++) : ?>
                                        <i class="far fa-star" aria-hidden="true"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="bonus">
                                    <?php echo $raketech_array[$i]['bonus'] ?>
                                </div>
                            </div>
                            <div class="features">
                                <?php foreach ($raketech_array[$i]['features'] as $feature) : ?>
                                    <div class="feature">
                                        <i class="fa fa-clock"></i><?php echo $feature; ?>
                                    </div>
                                <?php endforeach ?>
                            </div>
                            <div class="cta-content">
                                <a class="raketech-button" href="<?php echo $raketech_array[$i]['play_url'] ?>" rel="noopener nofollow" target="_blank"><strong>PLAY NOW</strong></a>
                                <div class="terms-and-conditions">
                                    <?php echo $raketech_array[$i]['terms_and_conditions']; ?>
                                </div>
                            </div>
                        </div>
                    <?php endfor ?>
                </div>
            </div>
        </div>


    <?php   $reviews_display = ob_get_contents();
        ob_end_clean();
        //var_dump($reviews_display);
        return $reviews_display;
    }
endif;



if ( ! function_exists( 'raketech_add_reviews_home_page' ) ) :

	/**
	 * Adds reviews table to the content of the home page
	 *
     * @param string $content.
     *
     *
     * @return string Reviews Table.
	 */
    function raketech_add_reviews_home_page($content){
        if (is_front_page()){
            $content = raketech_show_reviews_table(raketech_get_reviews());
            return $content;
        }
        return $content;
    }
endif;
add_filter('the_content', 'raketech_add_reviews_home_page');


