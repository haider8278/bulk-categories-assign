<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
function bca_actions(){
	if(isset($_REQUEST['action']) && current_user_can('upload_files')){
		///////////////////Save Image Categories///////////////////
		if($_REQUEST['action']=='savecat'){
			$post_ID = sanitize_text_field($_REQUEST['postImage']);
			$post_ID = (int)( $post_ID );
			if(empty($post_ID) || !is_numeric($post_ID)){
				wp_redirect(admin_url().'admin.php?page=bulk_categories_assign&msg=error&paged='.$paged);
				exit();
			}
			$post_categories = $_REQUEST['post_category'];
			$cats = array();
			$paged = sanitize_text_field($_REQUEST['paged']);
			foreach($post_categories as $c){
					$cat_id = sanitize_text_field($c);
					$cats[] = (int)($cat_id);
			}
			
			 if(wp_set_post_categories( $post_ID, $cats)){
				 wp_redirect(admin_url().'admin.php?page=bulk_categories_assign&msg=success&paged='.$paged);
			 }else{
				 wp_redirect(admin_url().'admin.php?page=bulk_categories_assign&msg=error&paged='.$paged);
			 }
		}
		///////////////////Save Multiple Images Categories///////////////////
		elseif($_REQUEST['action']=='savecatmultiple'){
			$multiimages = sanitize_text_field( $_POST['multiimages'] );
			$images = explode( ',', $multiimages );
			$post_categories = $_POST['category_multiple'];
			$paged = sanitize_text_field( $_POST['paged'] );
			$cats = array();
			foreach($post_categories as $c){
				$cat_id = sanitize_text_field($c);
				$cats[] = (int)($cat_id);
			}
			$n= 0;
			foreach($images as $img){
				$img = sanitize_text_field($img);
				$img = (int)($img);
				if($img!=''){
					 if(wp_set_post_categories( $img, $cats)){
						$n++;
					 }
				}
			}
			wp_redirect(admin_url().'admin.php?page=bulk_categories_assign&paged='.$paged);
		}
	}
exit();
}
?>