<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */

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