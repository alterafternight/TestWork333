<?php
/*
    Template Name: Create
*/
acf_form_head();
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php 		
	$new_post = array(
		'post_id'            => 'new', 
		'post_type'          => 'product', 
		'field_groups'       => array(17), 
		'form'               => true,
		'return'             => '%post_url%', 
		'html_before_fields' => '',
		'html_after_fields'  => '',
		'submit_value'       => 'Добавить',
		'updated_message'    => 'Сохранено!'
	);
	acf_form( $new_post );
?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
do_action( 'storefront_sidebar' );
get_footer();
