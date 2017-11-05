<?php

/*
* Plugin Name: Google Analytics Popular Rankings
*/


require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/classes/GoogleAnalyticsService.php';
require_once __DIR__ . '/classes/WPDatabaseService.php';

function ga_popular_rankings(){
    $KEY_FILE_LOCATION = __DIR__ . '/service-account-credentials.json';
    $VIEW_ID = '<REPLACE YOUR VIEW ID>';

    $analytics = new GoogleAnalyticsService($KEY_FILE_LOCATION,$VIEW_ID,5);
    $analytics->report_request();
    $response = $analytics->fetch_result_as_array();

    $db = new WPDatabaseService($response);
    $pop_posts =  $db->fetch_post_data();

    pop_posts_render($pop_posts);

}


function pop_posts_render($pop_posts){
    echo "<h2 class='widget-title'>人気記事</h2>";
    echo "<ul style='list-style:none;padding-left:0px;'>";
    foreach($pop_posts as $post){
        echo "<li><a href='".$post['url']."'>".$post['title']."</a></li>";
    }
    echo "</ul>";
}

add_filter('widget_text', 'do_shortcode');
add_shortcode( 'ga_popular_rankings', 'ga_popular_rankings' );
