<?php

function theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'avada-stylesheet' ) );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function avada_lang_setup() {
	$lang = get_stylesheet_directory() . '/languages';
	load_child_theme_textdomain( 'Avada', $lang );
}
add_action( 'after_setup_theme', 'avada_lang_setup' );

    
////////////////////////////////////////////////////////////////////////////////////////////////////


/* Load LESS */

function childtheme_scripts() {

wp_enqueue_style('less', get_stylesheet_directory_uri() .'/css/style.less');
add_filter('style_loader_tag', 'my_style_loader_tag_function');

wp_enqueue_script('less', get_stylesheet_directory_uri() .'/scripts/less.min.js', array('jquery'),'2.7.1');

}
add_action('wp_enqueue_scripts','childtheme_scripts', 150);

function my_style_loader_tag_function($tag){   
  return preg_replace("/='stylesheet' id='less-css'/", "='stylesheet/less' id='less-css'", $tag);
}


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Load Fonts dot com */

function extra_css () {
	wp_register_style( 'font', 'http://fast.fonts.net/cssapi/7ddb649f-3862-4a8f-8739-173dc8e52ae2.css' );
	wp_enqueue_style( 'font' );
} 

add_action('wp_print_styles', 'extra_css');


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Remove Date from Yoast SEO */

add_filter( 'wpseo_show_date_in_snippet_preview', false);


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Remove Dates from SEO on Pages */

function wpd_remove_modified_date(){
    if( is_page() ){
        add_filter( 'the_time', '__return_false' );
        add_filter( 'the_modified_time', '__return_false' );
        add_filter( 'get_the_modified_time', '__return_false' );
        add_filter( 'the_date', '__return_false' );
        add_filter( 'the_modified_date', '__return_false' );
        add_filter( 'get_the_modified_date', '__return_false' );
    }
}
add_action( 'template_redirect', 'wpd_remove_modified_date' );


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Remove Query String */

function _remove_script_version( $src ){
  $parsed = parse_url($src);

  if (isset($parsed['query'])) {
    parse_str($parsed['query'], $qrystr);
    if (isset($qrystr['ver'])) {
      unset($qrystr['ver']); 
    }
    $parsed['query'] = http_build_query($qrystr);
  }
  // return http_build_url($parsed); // elegant but not always available

  $src = '';
  $src .= (!empty($parsed['scheme'])) ? $parsed['scheme'].'://' : '';
  $src .= (!empty($parsed['host'])) ? $parsed['host'] : '';
  $src .= (!empty($parsed['path'])) ? $parsed['path'] : '';
  $src .= (!empty($parsed['query'])) ? '?'.$parsed['query'] : '';

  return $src;
}
add_filter( 'script_loader_src', '_remove_script_version', 15, 1 );
add_filter( 'style_loader_src', '_remove_script_version', 15, 1 );


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Add Field Visibility Section to Gravity Forms */		
		
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );

add_filter("gform_init_scripts_footer", "init_scripts");
function init_scripts() {
return true;
}


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Add Category Name to Body Class */	

add_filter('body_class','add_category_to_single');
function add_category_to_single($classes, $class) {
  if (is_single() ) {
    global $post;
    foreach((get_the_category($post->ID)) as $category) {
      // add category slug to the $classes array
      $classes[] = $category->category_nicename;
    }
  }
  // return the $classes array
  return $classes;
}


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Change Gravity Button Type To FontAwesome */

add_filter("gform_submit_button_2", "form_submit_button", 10, 2);
function form_submit_button($button, $form){
return "<button class='button' id='gform_submit_button_{$form["id"]}'><i class='fa fa-fw fa-envelope'></i></button>";
}


////////////////////////////////////////////////////////////////////////////////////////////////////


/* SVG Support */	

function bodhi_svgs_disable_real_mime_check( $data, $file, $filename, $mimes ) {
    $wp_filetype = wp_check_filetype( $filename, $mimes );

    $ext = $wp_filetype['ext'];
    $type = $wp_filetype['type'];
    $proper_filename = $data['proper_filename'];

    return compact( 'ext', 'type', 'proper_filename' );
}
add_filter( 'wp_check_filetype_and_ext', 'bodhi_svgs_disable_real_mime_check', 10, 4 );

remove_filter('the_content', 'wptexturize');


////////////////////////////////////////////////////////////////////////////////////////////////////


/* If Modified Since */

add_action('template_redirect', 'last_mod_header');

function last_mod_header($headers) {
     if( is_singular() ) {
            $post_id = get_queried_object_id();
            $LastModified = gmdate("D, d M Y H:i:s \G\M\T", $post_id);
            $LastModified_unix = gmdate("D, d M Y H:i:s \G\M\T", $post_id);
            $IfModifiedSince = false;
            if( $post_id ) {
                if (isset($_ENV['HTTP_IF_MODIFIED_SINCE']))
                    $IfModifiedSince = strtotime(substr($_ENV['HTTP_IF_MODIFIED_SINCE'], 5));  
                if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
                    $IfModifiedSince = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));
                if ($IfModifiedSince && $IfModifiedSince >= $LastModified_unix) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 304 Not Modified');
                    exit;
                } 
     header("Last-Modified: " . get_the_modified_time("D, d M Y H:i:s", $post_id) );
                }
        }
}


////////////////////////////////////////////////////////////////////////////////////////////////////


/* WooCommerce - Show Price Ex VAT for Trade and Stock In User Roles */

function sunstofey_override_woocommerce_tax_display( $value ) {
	if ( current_user_can( 'trade' ) ) {
		return 'excl';
	}

	return $value;
}

add_filter( 'pre_option_woocommerce_tax_display_shop', 'sunstofey_override_woocommerce_tax_display' );
add_filter( 'pre_option_woocommerce_tax_display_cart', 'sunstofey_override_woocommerce_tax_display' );

function sunstofey_override_woocommerce_tax_display1( $value ) {
	if ( current_user_can( 'stockin' ) ) {
		return 'excl';
	}

	return $value;
}

add_filter( 'pre_option_woocommerce_tax_display_shop', 'sunstofey_override_woocommerce_tax_display1' );
add_filter( 'pre_option_woocommerce_tax_display_cart', 'sunstofey_override_woocommerce_tax_display1' );


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Add User Role Class to Body */

if ( is_user_logged_in() ) {
    add_filter('body_class','add_role_to_body');
    add_filter('admin_body_class','add_role_to_body');
}
function add_role_to_body($classes) {
    $current_user = new WP_User(get_current_user_id());
    $user_role = array_shift($current_user->roles);
    if (is_admin()) {
        $classes .= 'role-'. $user_role;
    } else {
        $classes[] = 'role-'. $user_role;
    }
    return $classes;
}


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Remove Scripts */

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );


////////////////////////////////////////////////////////////////////////////////////////////////////


/* Gravity Forms Scroll to Form after submission */

add_filter( 'gform_confirmation_anchor', '__return_true' );
