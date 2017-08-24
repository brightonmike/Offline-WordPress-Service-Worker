<?php

/*
Plugin Name: Offline Service Worker
Plugin URI:
Description:
Version: 1.0.0
Author: 
Author URI: 
License: GPL2
*/

/**
 * Show admin notice
 */
function sw_offline_admin_notices(){
	$scheme = parse_url(get_option( 'siteurl' ), PHP_URL_SCHEME);
	// if($scheme == 'http'){
	// 	sw_offline_render_notice(__( 'WP Offline Fallback: Site of you need to have SSL, otherwise the plugin will not able to work.', 'sw_offline' ));
	// 	return;
	// }

	$page = get_page_by_path('/offline-fallback', OBJECT, 'page');
	if(!$page){
        echo '<div class="notice notice-error"><p>'.sprintf(__( 'WP Offline Fallback: You need to create a public page with the following url: <a href="%1$s/offline-fallback">%1$s/offline-fallback</a>', 'sw_offline' ),get_option( 'siteurl' )).'</p></div>';
		return;
	}

}
add_action( 'admin_notices', 'sw_offline_admin_notices' );
/**
 * Render sw file, replace cache version with page modified
 */
function sw_offline_render_sw_file(){
	$wsContent = file_get_contents(plugin_dir_path( __FILE__ ).'sw.js');
	$page = get_page_by_path('/offline-fallback', OBJECT, 'page');
	if($page){
		return str_replace('__VERSION__', md5($page->post_modified), $wsContent);
	}
}

function sw_offline_enqueue_scripts(){
	wp_enqueue_script('sw_offline-sw-register', plugin_dir_url(__FILE__).'sw-register.js', [], '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'sw_offline_enqueue_scripts');

function sw_offline_copy_sw(){
	$path = get_home_path();
	$sw = plugin_dir_path( __FILE__ ).'sw.js';
	copy($sw, $path . 'sw.js');
}
add_action('activated_plugin', 'sw_offline_copy_sw');

/**
 * Change page template for offline-fallback page.
 *
 * @param $template
 *
 * @return string
 */
function sw_offline_template_include($template){

	if(!is_page('offline-fallback')){
		return $template;
	}

	$file = plugin_dir_path(__FILE__).'page-templates/offline-fallback.php';
    $file = apply_filters('sw_offline_template_path', $file);
	if ( file_exists( $file ) ) {
		return $file;
	}

	return $template;
}
add_filter( 'template_include','sw_offline_template_include' );
