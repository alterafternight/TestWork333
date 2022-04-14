<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */

/*  and here starts the Magic */

/* включаем поддержку woo */
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
add_theme_support( 'woocommerce' );
}

add_action('admin_head', 'custom_styles');
function custom_styles() {
	echo '<style>body.wp-admin.post-type-product form img.wp-post-image,body.wp-admin.post-type-product #postimagediv{display:none!important;}.clear{clear:both}#cleandops{margin-right:10px}</style><script>jQuery(document).ready(function($){
		$("#cleandops").click(function(){
			$("#acf-group_6257d13758a56 a[data-name=remove]").click();
			$("#acf-group_6257d13758a56 select").val($("#acf-group_6257d13758a56 select option:first").attr("value"));
			$("#acf-group_6257d13758a56 .acf-date-time-picker input").val("");
			return false;
		});
		$("#publish2").click(function(){
			$("#publish").focus();
			$("#publish").click();
			return false;
		});
	})</script>';
}

add_action('add_meta_boxes', 'save_but', 1);

function save_but() {
	add_meta_box( 'save_but_area', 'Свои действия', 'save_but_func', 'product', 'side', 'high'  );
}

function save_but_func( $post ){ 
	echo '<div id="publishing-action">';
	echo '<input type="submit" name="save" id="publish2" class="button button-primary button-large" value="Обновить товар">';
	echo '</div>';
	echo '<div id="publishing-action">';
	echo '<input type="button" class="button" value="Очистить Dops" id="cleandops">';
	echo '</div>';
	echo '<div class="clear"></div>';
}

function my_acf_save_post( $post_id ) {
	if ( get_post_type( $post_id ) == 'product' ){
		$altimg = get_field( 'altimg', $post_id );
		if ( $altimg['ID'] > 0 ){
			set_post_thumbnail( $post_id, $altimg['ID'] );
		}
		update_field( '_regular_price', get_field( 'price', $post_id ), $post_id );
		update_field( '_price', get_field( 'price', $post_id ), $post_id );
	}
}
add_action('acf/save_post', 'my_acf_save_post', 11);

function my_pre_save_post( $post_id ) {

	if( $post_id != 'new' ) {
		return $post_id;
	}

	$post = array(
		'post_type'     => 'product', 
		'post_status'   => 'publish', // (publish, draft, private, etc.)
		'post_title'    => wp_strip_all_tags($_POST['acf']['field_625821cbfd9ba']),
		'_price'    => wp_strip_all_tags($_POST['acf']['field_625824ed3e29d']),
		'post_content'  => $_POST['acf']['group_6257d13758a56'], // Содержание ACF field key
	);

    $post_id = wp_insert_post( $post ); 

    $_POST['return'] = add_query_arg( array('post_id' => $post_id), $_POST['return'] );
		
	return $post_id;

}
add_filter('acf/pre_save_post' , 'my_pre_save_post', 10, 1 );


function tsm_update_existing_post_data( $post_edit_id ) {
	
	$post_edit = array(
		'ID'           => $post_edit_id,
		'post_type'     => 'product', 
		'post_status'  => 'publish',
		'post_title'    => wp_strip_all_tags($_POST['acf']['field_625821cbfd9ba']),
		'price'    => wp_strip_all_tags($_POST['acf']['field_625824ed3e29d']),
		'post_content' => $_POST['acf']['group_6257d13758a56'], // Содержание ACF field key
	);

	$post_edit_id = wp_insert_post( $post_edit );
	
}
add_action( 'acf/save_post', 'tsm_update_existing_post_data', 10 );