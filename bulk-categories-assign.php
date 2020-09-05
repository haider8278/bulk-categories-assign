<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
Plugin Name: Bulk Categories Assign
Description: A plugin that will used to select and assign bulk categories to gallery images.
Version: 1.0
Author: Haider Ali
Author URI: http://www.haiderali.cf/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

/*
Adding hooks function for Plugin.
*/

define( 'BCA_URL', plugins_url('', __FILE__) );

define( 'BCA_DIR', plugin_dir_path(__FILE__) );

function bca_admin_menu() {
	include( 'actions.php');
	$icon_url = BCA_URL.'/images/logo_icons.png';
    $page = add_menu_page('BCA', 'BCA', 'manage_options', 'bulk_categories_assign', 'bulk_categories_assign',$icon_url);
	add_submenu_page("bulk_categories_assign","Catagories", "Categories",'manage_options', 'edit-tags.php?taxonomy=category&post_type=attachment' );
	add_submenu_page(
          null            // -> Set to null - will hide menu link
        , 'BCA Actions'    // -> Page Title
        , 'BCA Actions'    // -> Title that would otherwise appear in the menu
        , 'manage_options' // -> Capability level
        , 'bca_actions'   // -> Still accessible via admin.php?page=menu_handle
		, 'bca_actions'   // -> Still accessible via admin.php?page=menu_handle
    );
    add_action('admin_enqueue_scripts', 'bca_add_admin_scripts');
}
add_action('admin_menu', 'bca_admin_menu');
function bca_add_admin_scripts() {
    wp_enqueue_style( 'jquey-ui-core');
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-selectable');
	wp_enqueue_style('bca-style', BCA_URL.'/bca-style.css');
	wp_enqueue_script('bca-script-admin', BCA_URL.'/js/bca_scripts-admin.js',array('jquery','jquery-ui-core','jquery-ui-selectable'),'1.0.0',true);
}
add_action( 'wp_enqueue_scripts', 'bca_add_gallery_scripts' );
function bca_add_gallery_scripts(){
	wp_enqueue_style( 'jssor', BCA_URL.'/css/jssor-style.css', array() );
	wp_enqueue_script('jssor',BCA_URL.'/js/jssor.js',array('jquery'), '1.0.0', true);
	wp_enqueue_script('jssor-slider',BCA_URL.'/js/jssor.slider.js',array('jssor'), '1.0.0', true);
	wp_enqueue_script('jssor-script',BCA_URL.'/js/jssor_script.js',array('jssor-slider'), '1.0.0', true);
}
add_action('wp_ajax_delete_bca_image','delete_bca_image');
add_action('wp_ajax_update_bca_image','update_bca_image');
//////////////// Delete Image///////////////////////////
function delete_bca_image(){
	$imgId = sanitize_text_field($_POST['imgId']);
	 if(wp_delete_attachment( $imgId, true )){
		 echo 'success';
	 }else{
		 echo 'error';
	 }
	 exit();
}
///////////////////Update Image///////////////////
function update_bca_image(){
	$imgId 		= sanitize_text_field($_POST['imgId']);
	$key	 	= sanitize_text_field($_POST['key']);
	$value	 	= sanitize_text_field($_POST['value']);
	$my_post = array(
      'ID' 	=> $imgId,
      $key 	=> $value,
  	);
	 if(wp_update_post($my_post)){
		 echo 'success';
	 }else{
		 echo 'error';
	 }
	 exit();
}
////  BCA Main function //////////
function bulk_categories_assign(){
wp_enqueue_media();
?>
<div class="wrap">
  <h1>Add Images to Gallery</h1>
  <div class="add-new-media">
    <label for="image_url">Click to upload images</label>
    <input type="button" name="upload-btn" id="upload-btn" class="button-secondary" value="Upload Image">
    <div class="filters">
    	<form  action="<?php echo admin_url().'admin.php'?>" method="get">
        	<?php wp_dropdown_categories('hide_empty=0&hierarchical=1'); ?>
            <input type="hidden" name="page" value="bulk_categories_assign" />
            <input type="submit" class="btn button" value="Filter" />
            <a class="btn button" href="<?php echo admin_url().'admin.php?page=bulk_categories_assign'?>">Reset Filters</a>
        </form>
    </div>
  </div>
  <div class="content">
    <div class="content-left">
      <form action="" id="assign_cat" >
        <?php
			$paged = ($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
				 $ppp = get_option('posts_per_page');
					if ($paged==1) {
						$custom_offset = 0;
					} else {
						$custom_offset = $ppp*($paged-1);
					}
			$cat = ($_REQUEST['cat']) ? $_REQUEST['cat'] : '';
			if($cat == '1'){
				$cat = '';
				//$categories_list = get_the_category_list( __( ', ', 'twentyeleven' ) );
				$categories = get_categories('hide_empty=0');
				foreach($categories as $c){
					$cat.='-'.$c->term_id.',';
				}
				$cat = substr($cat,0,-1);
			}
			//echo $cat;exit();
			$images = get_posts(
								array(
									'post_type'      => 'attachment',
									'post_mime_type' => 'image',
									'post_status'    => 'inherit',
									'category'		=> $cat,
									'exclude'		=> $categories_list,
									'numberposts' => $ppp,
									'offset' => $custom_offset,
								)
							);
			$query = query_posts(
								array(
									'post_type'      => 'attachment',
									'post_mime_type' => 'image',
									'post_status'    => 'inherit',
									'cat'			=> $cat
								)
							);
				$max_num_pages = $query->max_num_pages;
			require_once( 'BFI_Thumb.php' );
		?>
        <ol id="selectable" data-ff="<?php echo $max_num_pages;?>">
          <?php foreach($images as $img){
					$alt = get_post_meta($img->ID, '_wp_attachment_image_alt', true);
					$dim = wp_get_attachment_metadata( $img->ID);
					$image_title = $img->post_title;
					$caption = $img->post_excerpt;
					$description = $img->post_content;
					if($dim['width']>150){
					$params = array(
						'width' => 150,
						'height' => 150,
						'crop' => true
					);
					}else{
						$params = array();
					}
					$image = bfi_thumb( $img->guid, $params );
		  ?>
          <li class="ui-state-default" id="attc-<?=$img->ID?>"><img src="<?php echo $image;//BCA_URL.'/timthumb.php?src='. $img->guid.'&w=150&h=150';?>" data-src="<?php echo $image;?>" data-name="<?=$img->post_title?>" data-imgid="<?=$img->ID?>" data-date="<?=date("F d, Y",strtotime($img->post_date));?>" data-caption="<?=$caption?>" data-size="<?=size_format(filesize( get_attached_file( $img->ID ) ));?>" data-dim="<?=$dim['width'].' x '.$dim['height'];?>" />
          <span style="display:none;">
			  <?php
          $post_categories = wp_get_post_categories( $img->ID );
          //print_r($post_categories);
          $cats = '';
          $cats = implode(',',$post_categories);
		  //foreach($post_categories as $c){
		  	//$cats.= $c.',';
		 //}
			echo $cats;
		   ?></span></li>
          <?php }?>
        </ol>
      </form>
      <br class="clear" />
      <?php
	  pagination($max_num_pages);?>
    </div>
    <div class="content-right">
      <div class="single">
        <div class="attachment-details save-ready" tabindex="0" data-id="45">
          <h3> Attachment Details <span class="settings-save-status"> <span class="spinner"></span> <span class="saved">Saved.</span> </span> </h3>
          <div class="attachment-info">
            <div class="thumbnail thumbnail-image"> <img draggable="false" src="" id="thumb"> </div>
            <div class="details">
              <div class="filename"></div>
              <div class="uploaded"></div>
              <div class="file-size"></div>
              <div class="dimensions">259 × 194</div>
              <a target="_blank" href="" class="edit-attachment">Edit Image</a><a href="#" class="delete-attachment" data-id="">Delete Permanently</a>
              <div class="compat-meta"> </div>
            </div>
          </div>
          <form id="single_image" action="" method="post">
          	<input type="hidden" name="id" value="" id="post_ID" />
            <!--<label data-setting="url" class="setting"> <span class="name">URL</span>
              <input type="text" id="imgurl" readonly="" value="http://server1/projects-svn3/RHPC/Code/wp-content/uploads/2015/02/87.jpeg">
            </label>
            <label data-setting="title" class="setting"> <span class="name">Title</span>
              <input type="text" value="" id="post_title"  class="inputs">
            </label>-->
            <p>After making a change to either CAPTION or CATEGORIES you must hit SAVE at the bottom of the form for changes to take place.</p>
            <label data-setting="caption" class="setting"> <span class="name">Caption</span>
              <textarea id="post_excerpt" class="inputs"></textarea>
            </label>
            <!--<label data-setting="alt" class="setting"> <span class="name">Alt Text</span>
              <input type="text" value="" id="alttext" class="inputs">
            </label>
            <label data-setting="description" class="setting" > <span class="name">Description</span>
              <textarea id="post_content" class="inputs"></textarea>
            </label>
            -->
          </form>
          <form class="compat-item" method="post" id="cat_form" action="">
            <input type="hidden" id="postImage" value="" name="postImage">
            <table class="compat-attachment-fields">
              <tbody>
                <tr class="compat-field-category_metabox">
                  <td class="field"><div class="categorydiv" id="taxonomy-category">
                      <ul class="category-tabs" id="category-tabs">
                        <li class="tabs"><a tabindex="3" href="#category-all">All Categories</a></li>
                      </ul>
                      <div class="tabs-panel" id="category-all">
                        <input type="hidden" value="" name="post_id">
                        <?php
							$categories = get_categories('hide_empty=0&exclude=1');
							//echo '<pre>';
							//print_r($categories);
							//echo '</pre>';
						?>
                        <ul class="list:category categorychecklist form-no-clear" id="categorychecklist">
                          <?php foreach ($categories as $category) {
						  			if($category->parent==0){
						  ?>
                          <li id="category-<?=$category->term_id?>">
                            <label class="selectit">
                              <input class="cat-checkbox" type="checkbox" id="in-category-<?php echo esc_attr($category->term_id);?>" name="post_category[]" value="<?php echo esc_attr($category->term_id);?>">
                              <?=$category->cat_name?></label>
                              <?php
								$children = get_terms( $category->taxonomy, array(
								'parent'    => $category->term_id,
								'hide_empty' => false
								) );
								//print_r($children); // uncomment to examine for debugging
								 if($children) { // get_terms will return false if tax does not exist or term wasn't found.
									// term has children
							  ?>
                           	<?php //echo get_category_parents( $ch->term_id, false, ',' );?>
                            <?php foreach($children as $ch){?>
                           	<li style="margin-left:20px;" id="category-<?=$ch->term_id?>">
                              <label class="selectit">
                              <input class="cat-checkbox" type="checkbox" data-parents="<?php echo esc_attr($category->term_id);?>" id="in-category-<?php echo esc_attr($ch->term_id);?>" name="post_category[]" value="<?php echo esc_attr($ch->term_id);?>">
                              <?=$ch->name?></label>
                              		<?php
										$innerchildren = get_terms( $ch->taxonomy, array(
											'parent'    => $ch->term_id,
											'hide_empty' => false
											) );
										if($innerchildren){?>
								    <?php foreach($innerchildren as $in){?>
                                    	<li style="margin-left:40px;" id="category-<?=$in->term_id?>">
										<label class="selectit">
                              			<input class="cat-checkbox" type="checkbox" data-parents="<?php echo $category->term_id.','.$ch->term_id;?>" id="in-category-<?=$in->term_id?>" name="post_category[]" value="<?php echo esc_attr( $in->term_id);?>">
                              			<?php echo esc_attr($in->name)?></label>
                                        	<?php
											$level4 = get_terms( $in->taxonomy, array(
											'parent'    => $in->term_id,
											'hide_empty' => false
											) );
											if($level4){?>
												<?php foreach($level4 as $l4){?>
                                                    <li style="margin-left:60px;" id="category-<?php echo esc_attr($l4->term_id)?>">
                                                    <label class="selectit">
                                                    <input class="cat-checkbox" type="checkbox" data-parents="<?php echo esc_attr($category->term_id).','.esc_attr($ch->term_id).','.esc_attr($in->term_id);?>" id="in-category-<?php echo esc_attr($l4->term_id);?>" name="post_category[]" value="<?=$l4->term_id?>">
                                                    <?php echo esc_attr($l4->name)?></label>
                                                    </li>
                                                            <?php }?>
                                                    <?php }?>
                                        </li>
												<?php }?>
										<?php }?>
                            </li>
                             <?php
								}
							}
							  ?>
                          </li>
                          <?php
									}
						  }?>
                        </ul>
                        <br class="clear" />
                        <?php
						//wp_terms_checklists();?>
                      </div>
                      <br class="clear" />
                     </div>
                     <br class="clear" />
                     <input class="cat-checkbox" type="checkbox" data-parents="" id="in-category-1" name="post_category[]" value="1" style="display:none;">
                     <input type="button" class="button media-button button-primary right" value="save" id="save_cat" />
                        <br class="clear" />
                    </td>
                </tr>
              </tbody>
            </table>
          </form>
        </div>
      </div>
      <div class="multiple">
      	<form class="compat-item" method="post" id="cat_form_multiple" action="">
            <input type="hidden" value="0" id="multiimages" name="multiimages">
            <table class="compat-attachment-fields">
              <tbody>
                <tr class="compat-field-category_metabox">
                  <td class="field"><div class="categorydiv" id="taxonomy-category">
                      <ul class="category-tabs" id="category-tabs">
                        <li class="tabs"><a tabindex="3" href="#category-all">All Categories</a></li>
                      </ul>
                      <div class="tabs-panel" id="category-all">
                        <input type="hidden" value="" name="post_id">
                        <?php
							$categories = get_categories('hide_empty=0&exclude=1');
						?>
                        <ul class="list:category categorychecklist form-no-clear" id="categorychecklist">
                          <?php foreach ($categories as $category) {
						  			if($category->parent==0){
						  ?>
                          <li id="category-<?php echo esc_attr($category->term_id)?>">
                          <label class="selectit">
                              <input type="checkbox" id="in-category-<?php echo esc_attr($category->term_id);?>" data-parents="<?php echo esc_attr($category->term_id);?>" class="cat-checkbox" name="category_multiple[]" value="<?php echo esc_attr($category->term_id);?>">
                              <?php echo esc_html($category->cat_name)?></label>
                              <?php
							  	//$term = get_queried_object();
								$children = get_terms( $category->taxonomy, array(
								'parent'    => $category->term_id,
								'hide_empty' => false
								) );
								//print_r($children); // uncomment to examine for debugging
								 if($children) { // get_terms will return false if tax does not exist or term wasn't found.
									// term has children
							  ?>
                            <?php foreach($children as $ch){?>
                           	<li style="margin-left:20px;" id="category-<?php echo esc_attr($ch->term_id);?>">
                              <label class="selectit">
                              <input type="checkbox" id="in-category-<?php echo esc_attr($ch->term_id);?>" data-parents="<?php echo $category->term_id.','.$ch->term_id;?>" class="cat-checkbox" name="category_multiple[]" value="<?php echo esc_attr($ch->term_id);?>">
                              <?php echo esc_html($ch->name);?></label>
                              		<?php
									$innerchildren = get_terms( $ch->taxonomy, array(
											'parent'    => $ch->term_id,
											'hide_empty' => false
											) );
									if($innerchildren){?>
								    <?php foreach($innerchildren as $in){?>
                                    	<li style="margin-left:40px;" id="category-<?php echo esc_attr($ch->term_id);?>">
										<label class="selectit">
                              			<input type="checkbox" id="in-category-<?php echo esc_attr($in->term_id);?>" data-parents="<?php echo $category->term_id.','.$ch->term_id.','.$in->term_id;?>" class="cat-checkbox" name="category_multiple[]" value="<?php echo esc_attr($in->term_id);?>">
                              			<?php echo esc_html($in->name);?></label>
                                        	<?php
											$level4 = get_terms( $in->taxonomy, array(
											'parent'    => $in->term_id,
											'hide_empty' => false
											) );
											if($level4){?>
												<?php foreach($level4 as $l4){?>
                                                    <li style="margin-left:60px;" id="category-<?php echo esc_attr($l4->term_id);?>">
                                                    <label class="selectit">
                                                    <input type="checkbox" id="in-category-<?php echo esc_attr($l4->term_id);?>" data-parents="<?php echo $category->term_id.','.$ch->term_id.','.$in->term_id;?>" class="cat-checkbox" name="category_multiple[]" value="<?php echo esc_attr($l4->term_id);?>">
                                                    <?php echo esc_html($l4->name);?></label>
                                                    </li>
                                                            <?php }?>
                                                    <?php }?>
                                        </li>
												<?php }?>
										<?php }?>
                            </li>
                             <?php
								}
							}
							  ?>
                          </li>
                          <?php
									}
						  }?>
                        </ul>
                        <input class="cat-checkbox" type="checkbox" data-parents="" id="in-category-1" name="category_multiple[]" value="1" style="display:none;">
                        <input type="button" class="button media-button button-primary right" value="save" id="save_cat_mulitple" />
                        <span class="spinner"></span>
                        <br class="clear" />
                      </div>
                     </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </form>
          <input type="hidden" name="pageno" id="pageno" value="<?php echo $_REQUEST['paged']; ?>" />
		  <input type="hidden" name="admin_url" id="admin_url" value="<?php echo admin_url();?>">
      </div>
      </div>
  </div>
</div>
<?php
}
function pagination($pages = '', $range = 4){
    $showitems = ($range * 2)+1;
	global $paged;
	 if(isset($_REQUEST['paged'])){
				$paged = ($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
			}else{
				$p = substr($_SERVER['REQUEST_URI'],-2);
				$paged = substr($p,0,1);
			}
	 //$paged = ($_REQUEST['paged']) ? $_REQUEST['paged'] : 1;
     if($paged=='' || !is_numeric($paged)){ $paged = 1;}
     if($pages == '')
     {
         global $wp_query;
         $pages = $wp_query->max_num_pages;
         if(!$pages){
             $pages = 1;
         }
     }
     if(1 != $pages){
         //echo "<div class=\"pagination\"><span>Page ".$paged." of ".$pages."</span>";
		 echo '<div class="gallery_control">';
         //if($paged > 1 && $showitems < $pages) echo '<div class="gallery_previous"><a href="'.get_pagenum_link($paged - 1).'">&lsaquo; Previous</a></div>';
 		echo '<div class="gallery_previous"><a class="btn" href="'.get_pagenum_link($paged - 1).'"></a></div>';
		if($paged > 2 && $paged > $range+1 && $showitems < $pages) echo "<a class='fandl' href='".get_pagenum_link(1)."'>&laquo; First</a>";
         for ($i=1; $i <= $pages; $i++){
             if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )){
                 echo ($paged == $i)? "<a class=\"active\">".$i."</a>":"<a href='".get_pagenum_link($i)."' class=\"inactive\">".$i."</a>";
             }
         }
         //if ($paged < $pages && $showitems < $pages) echo '<div class="gallery_next"><a href="'.get_pagenum_link($paged + 1).'">Next &rsaquo;</a></div>';
		 echo '<div class="gallery_next"><a class="btn" href="'.get_pagenum_link($paged + 1).'"></a></div>';
         if ($paged < $pages-1 &&  $paged+$range-1 < $pages && $showitems < $pages) echo "<a class='fandl' href='".get_pagenum_link($pages)."'>Last &raquo;</a>";
         echo "</div>\n";
     }
}
function page_refresh_on_media_upload() {
	global $page;
	if($page=="bulk_categories_assign"){
echo "
<script>
jQuery(document).ready(function(e) {
	$(document).on('click','.media-modal-close',function(e){
	location.reload();
    });
	$(document).on('click','.media-toolbar-primary',function(e){
	location.reload();
    });
});
</script>";
	}
}
add_action( 'admin_footer', 'page_refresh_on_media_upload'  );

function gallery($atts , $content = null){
	// Attributes
	extract( shortcode_atts(
		array(
			'cat' => '',
		), $atts )
	);

	$ppp = get_option('posts_per_page');
	$images = get_posts(
					array(
						'post_type'      => 'attachment',
						'post_mime_type' => 'image',
						'post_status'    => 'inherit',
						'numberposts' => $ppp,
						'cat' => $cat
					)
				);
	global $wpdb;
	require_once( 'BFI_Thumb.php' );
	$params = array(
					'width' => 150,
					'height' => 150,
					'crop' => true
				);
	$html = '<!-- Jssor Slider Begin -->
    <!-- To move inline styles to css file/block, please specify a class name for each element. --> 
    <div id="slider1_container" style="position: relative; top: 0px; left: 0px; width: 800px;
        height: 456px; background: #191919; overflow: hidden;">

        <!-- Loading Screen -->
        <div u="loading" style="position: absolute; top: 0px; left: 0px;">
            <div style="filter: alpha(opacity=70); opacity:0.7; position: absolute; display: block;
                background-color: #000000; top: 0px; left: 0px;width: 100%;height:100%;">
            </div>
            <div style="position: absolute; display: block; background: url('.plugins_url('/img/loading.gif',__FILE__).') no-repeat center center;
                top: 0px; left: 0px;width: 100%;height:100%;">
            </div>
        </div>

        <!-- Slides Container -->
        <div u="slides" style="cursor: move; position: absolute; left: 0px; top: 0px; width: 800px; height: 356px; overflow: hidden;">';
		foreach($images as $img){
			$image = bfi_thumb( $img->guid, $params );
			$html.= '<div><img u="image" src="'.$img->guid.'" /><img u="thumb" src="'.$image.'" /></div>';
	?>
	<?php
		}
		$html.= '</div>
        <!--#region Arrow Navigator Skin Begin -->
        <!-- Arrow Left -->
        <span u="arrowleft" class="jssora05l" style="top: 158px; left: 8px;">
        </span>
        <!-- Arrow Right -->
        <span u="arrowright" class="jssora05r" style="top: 158px; right: 8px">
        </span>
        <!-- thumbnail navigator container -->
        <div u="thumbnavigator" class="jssort01" style="left: 0px; bottom: 0px;">
            <!-- Thumbnail Item Skin Begin -->
            <div u="slides" style="cursor: default;">
                <div u="prototype" class="p">
                    <div class="w"><div u="thumbnailtemplate" class="t"></div></div>
                    <div class="c"></div>
                </div>
            </div>
            <!-- Thumbnail Item Skin End -->
        </div>
        <!--#endregion Thumbnail Navigator Skin End -->
        <a style="display: none" href="http://www.jssor.com">Bootstrap Slider</a>
    </div>
    <!-- Jssor Slider End -->';
		 return $html;
}
add_shortcode('image-gallery', 'gallery');

/////////////// Short code button on tinymce ////////////////////
add_action('admin_head', 'bca_add_my_tc_button');
function bca_add_my_tc_button() {
    global $typenow;
    // check user permissions
    if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
    return;
    }
    // verify the post type
    if( ! in_array( $typenow, array( 'post', 'page' ) ) )
        return;
    // check if WYSIWYG is enabled
    if ( get_user_option('rich_editing') == 'true') {
        add_filter("mce_external_plugins", "bca_add_tinymce_plugin");
        add_filter('mce_buttons', 'bca_register_my_tc_button');
    }
}
function bca_add_tinymce_plugin($plugin_array) {
    $plugin_array['bca_tc_button'] = plugins_url( '/js/text-buttons.js', __FILE__ ); // CHANGE THE BUTTON SCRIPT HERE
    return $plugin_array;
}
function bca_register_my_tc_button($buttons) {
   array_push($buttons, "bca_tc_button");
   return $buttons;
}
function bca_tc_css() {
	global $page;
    wp_enqueue_style('bca-tc', plugins_url('/style.css', __FILE__));

	if( is_admin() && $page == 'bulk_categories_assign' ){
		wp_enqueue_script('bca-script-admin', plugins_url('/js/script-admin.js', __FILE__));
	}
}
add_action('admin_enqueue_scripts', 'bca_tc_css');
?>