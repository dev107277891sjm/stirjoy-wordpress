<?php

function twitter_time($a) {
    //get current timestampt
    $b = strtotime("now"); 
    //get timestamp when tweet created
    $c = strtotime($a);
    //get difference
    $d = $b - $c;
    //calculate different time values
    $minute = 60;
    $hour = $minute * 60;
    $day = $hour * 24;
    $week = $day * 7;
        
    if(is_numeric($d) && $d > 0) {
        //if less then 3 seconds
        if($d < 3) return "right now";
        //if less then minute
        if($d < $minute) return floor($d) . " seconds ago";
        //if less then 2 minutes
        if($d < $minute * 2) return "about 1 minute ago";
        //if less then hour
        if($d < $hour) return floor($d / $minute) . " minutes ago";
        //if less then 2 hours
        if($d < $hour * 2) return "about 1 hour ago";
        //if less then day
        if($d < $day) return floor($d / $hour) . " hours ago";
        //if more then day, but less then 2 days
        if($d > $day && $d < $day * 2) return "yesterday";
        //if less then year
        if($d < $day * 365) return floor($d / $day) . " days ago";
        //else return more than a year
        return "over a year ago";
    }
}


/**

||-> Shortcode: Recent Tweets

*/
if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
    function themeslr_setup_shortcode_tweetslider( $params, $content ) {
        extract( shortcode_atts( array(
            'no'            => 1,
            'tweet_font_size'     => '',
            'username'     => '',
            'tweet_line_height'     => '',
            'tweet_color'     => '',
            ), $params ) );


        global $thecrate_redux;
        require_once( 'twitter/twitteroauth/twitteroauth.php' );
        # Get Theme Options Twitter Details
        $tw_username            = $username;
        $consumer_key           = $thecrate_redux['thecrate_tw_consumer_key'];
        $consumer_secret        = $thecrate_redux['thecrate_tw_consumer_secret'];
        $access_token           = $thecrate_redux['thecrate_tw_access_token'];
        $access_token_secret    = $thecrate_redux['thecrate_tw_access_token_secret'];
        $no = $no+1;



        # Create the connection
        $twitter = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);

        # Migrate over to SSL/TLS
        $twitter->ssl_verifypeer = true;
        # Load the Tweets
        $tweets = $twitter->get('statuses/user_timeline', array('screen_name' => $tw_username, 'exclude_replies' => 'true', 'include_rts' => 'false', 'count' => $no));
        // var_dump( $twitter);
        
        if(!empty($tweets)) {
            $html = '';
            $html .= '<div class="tslr_tweets_slider owl-carousel owl-theme">';
                foreach($tweets as $tweet) {
                    $html .= '<div class="single-tweet item">';
                        $html .= '<div class="text-center">';
                            $html .= '<span class="tweet-author">@'.$tw_username.'</span>';
                            $html .= '<div class="tweet-content" style="color:'.$tweet_color.'; font-size:'.$tweet_font_size.'; line-height:'.$tweet_line_height.';">'.$tweet->text.'</div>';
                            $html .= '<span class="tweet-date">'.twitter_time($tweet->created_at).'</span>';
                        $html .= '</div>';
                    $html .= '</div>';
                }
            $html .= '</div>';
            
            return $html;
        }
    }
    add_shortcode('tslr_tweetslider', 'themeslr_setup_shortcode_tweetslider');
}



/**

||-> Map Shortcode in Visual Composer with: vc_map();

*/
add_action( 'vc_before_init', 'themeslr_vc_map__tslr_tweetslider' );
function themeslr_vc_map__tslr_tweetslider() {
    if (function_exists('vc_map')) {
        vc_map( 
            array(
                "name" => esc_attr__("ThemeSLR - Tweets Slider", 'themeslr'),
                "base" => "tslr_tweetslider",
                "category" => esc_attr__('ThemeSLR', 'themeslr'),
                "icon" => "themeslr_shortcode",
                "params" => array(
                    array(
                        "group" => "Options",
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__("Twitter Username(to show feeds from)", 'themeslr'),
                        "param_name" => "username",
                        "value" => "",
                        "description" => ""
                    ),
                    array(
                        "group" => "Options",
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__("Number of tweets to show", 'themeslr'),
                        "param_name" => "no",
                        "value" => "",
                        "description" => "Default value: 5"
                    ),
                    array(
                        "group" => "Options",
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__("Font size(eg: 20px)", 'themeslr'),
                        "param_name" => "tweet_font_size",
                        "value" => "",
                        "description" => "Default value: 20px"
                    ),
                    array(
                        "group" => "Options",
                        "type" => "textfield",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__("Line Height(eg: 28px)", 'themeslr'),
                        "param_name" => "tweet_line_height",
                        "value" => "",
                        "description" => "Default value: 28px"
                    ),
                    array(
                        "group" => "Styling",
                        "type" => "colorpicker",
                        "holder" => "div",
                        "class" => "",
                        "heading" => esc_attr__("Color", 'themeslr'),
                        "param_name" => "tweet_color",
                        "value" => "",
                    )
                )
            )
        );
    }
}