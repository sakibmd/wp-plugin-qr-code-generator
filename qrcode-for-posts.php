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

$pqrc_countries = array(
    __('Afganistan', 'posts-to-qrcode'),
    __('Bangladesh', 'posts-to-qrcode'),
    __('Bhutan', 'posts-to-qrcode'),
    __('India', 'posts-to-qrcode'),
    __('Maldives', 'posts-to-qrcode'),
    __('Nepal', 'posts-to-qrcode'),
    __('Pakistan', 'posts-to-qrcode'),
    __('Sri Lanka', 'posts-to-qrcode'),
);

function pqrc_init()
{
    global $pqrc_countries;
    $pqrc_countries = apply_filters('pqrc_countries_add_remove', $pqrc_countries);
}
add_action("init", 'pqrc_init');

function pqrc_countries_add_remove_callback($countries)
{

    array_push($countries, "Spain"); //add new item
    $countries = array_diff($countries, array('Nepal')); //remove nepal from countries array
    return $countries;
}

add_filter('pqrc_countries_add_remove', 'pqrc_countries_add_remove_callback');

function pqrc_load_textdomain()
{
    load_plugin_textdomain('posts-to-qrcode', false, dirname(__FILE__) . "/languages");
}
add_action("plugins_loaded", "pqrc_load_textdomain");

function pqrc_display_qr_code($content)
{
    $current_post_id = get_the_ID();
    $current_post_title = get_the_title($current_post_id);
    $current_post_url = urlencode(get_the_permalink($current_post_id));
    $current_post_type = get_post_type($current_post_id);

    // post type check
    $pqrc_excluded_type = apply_filters("pqrc_excluded_posts_types", array());
    if (in_array($current_post_type, $pqrc_excluded_type)) {
        return $content;
    }

    // dimension

    //Dimension Hook
    $height = get_option('pqrc_height');
    $width = get_option('pqrc_width');
    $height = $height ? $height : 180;
    $width = $width ? $width : 180;
    $dimension = apply_filters('pqrc_dimension_resize', "{$width}x{$height}");
    //$dimension = apply_filters('pqrc_dimension_resize', '150x150');

    $image_src = sprintf('https://api.qrserver.com/v1/create-qr-code/?size=%s&data=%s', $dimension, $current_post_url);
    $content .= sprintf('<div><img src="%s" alt="%s" /></div>', $image_src, $current_post_title);
    return $content;
}

add_filter("the_content", "pqrc_display_qr_code");

function pqrc_excluded_posts_types_callback($post_types)
{
    $post_types[] = "page";
    return $post_types;
}
add_filter("pqrc_excluded_posts_types", "pqrc_excluded_posts_types_callback");

function pqrc_dimension_resize_callback($size)
{
    return '150x150';
}
//add_filter("pqrc_dimension_resize", "pqrc_dimension_resize_callback");

//working with setting --give ability to admin so that can be easily maintain the qr code height/width
function pqrc_settings_init()
{

    add_settings_section('pqrc_section', __('Posts to QR Code', 'posts-to-qrcode'), 'pqrc_section_callback', 'general');

    // add_settings_field('pqrc_height', __('QR Code Height', 'posts-to-qrcode'), 'pqrc_display_height', 'general', 'pqrc_section'); //new column add. Always options table e add hobe
    // add_settings_field('pqrc_width', __('QR Code Width', 'posts-to-qrcode'), 'pqrc_display_width', 'general', 'pqrc_section'); //new column add

    //height & width er jonno alada callback create na kore common call e pathabo **** pqrc_display_field ****
    add_settings_field('pqrc_height', __('QR Code Height', 'posts-to-qrcode'), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_height'));
    add_settings_field('pqrc_width', __('QR Code Width', 'posts-to-qrcode'), 'pqrc_display_field', 'general', 'pqrc_section', array('pqrc_width'));
    add_settings_field('pqrc_select', __('Country', 'posts-to-qrcode'), 'pqrc_display_select_field', 'general', 'pqrc_section');
    add_settings_field('pqrc_checkbox', __('Select Hobbies', 'posts-to-qrcode'), 'pqrc_display_checkboxgroup_field', 'general', 'pqrc_section');

    register_setting('general', 'pqrc_height', array('sanitize_callback' => 'esc_attr')); //register new col
    register_setting('general', 'pqrc_width', array('sanitize_callback' => 'esc_attr')); //register new col
    register_setting('general', 'pqrc_select', array('sanitize_callback' => 'esc_attr')); //register select
    register_setting('general', 'pqrc_checkbox'); //register select

}

function pqrc_section_callback()
{
    echo "<p>" . __('Settings for Posts To QR Plugin', 'posts-to-qrcode') . "</p>";
}

function pqrc_display_checkboxgroup_field()
{

    //$countries = array("BD", "IND", "PAK");
    global $pqrc_countries;
    $option = get_option('pqrc_checkbox');
    foreach ($pqrc_countries as $coutry) {
        $selected = '';

        if (is_array($option) && in_array($coutry, $option)) {
            $selected = 'checked';
        }
        printf('<input type="checkbox" name="pqrc_checkbox[]" value="%s" %s /> %s <br/>', $coutry, $selected, $coutry);
    }
}

function pqrc_display_select_field()
{

    global $pqrc_countries;
    $option = get_option('pqrc_select') ?? '';

    //$countries = array("BD", "IND", "PAK");

    printf('<select id="%s" name="%s">', 'pqrc_select', 'pqrc_select');
    foreach ($pqrc_countries as $country) {
        $selected = '';
        if ($option == $country) {
            $selected = 'selected';
        }
        printf('<option value="%s" %s>%s</option>', $country, $selected, $country);
    }
    echo "</select>";
}

function pqrc_display_field($args)
{
    $option = get_option($args[0]);
    printf("<input type='text' id='%s' name='%s' value='%s'/>", $args[0], $args[0], $option);
}

function pqrc_display_height()
{
    $height = get_option('pqrc_height');
    printf("<input type='text' id='%s' name='%s' value='%s'/>", 'pqrc_height', 'pqrc_height', $height);
}

function pqrc_display_width()
{
    $width = get_option('pqrc_width');
    printf("<input type='text' id='%s' name='%s' value='%s'/>", 'pqrc_width', 'pqrc_width', $width);
}

add_action("admin_init", 'pqrc_settings_init');
