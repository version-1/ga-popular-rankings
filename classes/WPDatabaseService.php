<?php
Class WPDatabaseService{
    private $posts;

    function __construct($posts){
        $this->posts = $posts;
    }

    private function get_tilte_by_path_name($post_name){
        global $wpdb;
        $query = $wpdb->prepare('select post_title from wp_posts where post_name = %s',$post_name);
        return $wpdb->get_var($query,0,0);
    }

    function fetch_post_data(){
        $result = [];
        foreach($this->posts as $post){
           $path_name = urlencode(mb_convert_encoding(str_replace('/','',$post['url']), 'UTF-8', 'auto'));
           $post['title'] = $this->get_tilte_by_path_name($path_name);
           $result[] = $post;
        }
        return $result;
    }
}
