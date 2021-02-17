<?php

/**
 * Plugin Name:       QR Code For Posts
 * Plugin URI:        https://sakibmd.xyz/
 * Description:       Generate QR Code for every single post.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Sakib Mohammed
 * Author URI:        https://sakibmd.xyz/
 * License:           GPL v2 or later
 * License URI:       
 * Text Domain:       posts-to-qrcode
 * Domain Path:       /languages
 */


// function wordcount_activation_hook(){}
// register_activation_hook(__FILE__, "wordcount_activation_hook");


// function wordcount_deactivation_hook(){}
// register_deactivation_hook(__FILE__, "wordcount_deactivation_hook");


function pqrc_load_textdomain(){
    load_plugin_textdomain('posts-to-qrcode', false, dirname(__FILE__)."/languages");
}
add_action("plugins_loaded", "pqrc_load_textdomain");


function pqrc_display_qr_code($content){
    $current_post_id = get_the_ID();
    $current_post_title = get_the_title($current_post_id);
    $current_post_url = urlencode(get_the_permalink($current_post_id));
    $current_post_type = get_post_type($current_post_id);


    // post type check
    $pqrc_excluded_type = apply_filters("pqrc_excluded_posts_types", array());
    if(in_array($current_post_type, $pqrc_excluded_type)){
        return $content;
    }

    // dimension

    $dimension = apply_filters('pqrc_dimension_resize', '150x150');




    $image_src = sprintf('https://api.qrserver.com/v1/create-qr-code/?size=%s&data=%s', $dimension, $current_post_url);
    $content .= sprintf('<div><img src="%s" alt="%s" /></div>', $image_src, $current_post_title);
    return $content;
}

add_filter("the_content", "pqrc_display_qr_code");



 function pqrc_excluded_posts_types_callback($post_types){
    $post_types[] = "page";
    return $post_types;

 }
 add_filter("pqrc_excluded_posts_types", "pqrc_excluded_posts_types_callback");



 function pqrc_dimension_resize_callback($size){
    return '150x150';

 }
 add_filter("pqrc_dimension_resize", "pqrc_dimension_resize_callback");




