<?php

 /*

 * Plugin Name: Product Filter AJAX for Woo

 * Version: 1.0.0

 * Plugin URI: http://www.mlfactory.de

 * Description: Simple / Modern Ajax Product Filter Plugin for WooCommerce.

 * Author: Michael Leithold

 * Author URI: https://profiles.wordpress.org/mlfactory/

 * Requires at least: 4.0

 * Tested up to: 5.4

 * License: GPLv2 or later

 * Text Domain: product-filter-ajax-for-woo
 
 * Domain Path: /languages/
 
*/


//*******************************//
//*********FRONTEND PART*********//
//*******************************//
class wapf_frontend {
	
 
    public static function init() {

		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			add_action( 'woocommerce_before_shop_loop', __CLASS__ . '::wapf_frontend_search', 10 );
			
			add_action( 'wp_enqueue_scripts', __CLASS__ . '::wapf_frontend_scripts', 99 );
			
			add_action( 'wp_ajax_wapf_gfp', __CLASS__ . '::wapf_gfp' );
			
			add_action( 'wp_ajax_nopriv_wapf_gfp', __CLASS__ . '::wapf_gfp' ); 

			add_action( 'pre_get_posts', __CLASS__ . '::wapf_modify_post' );
			
			add_shortcode('wapf_filter', __CLASS__ . '::wapf_frontend_search'); 
		}

	}
	
	
	public static function wapf_modify_post( $query ) {

		if (is_product_category()) {
			
			update_option('wapf_queried_termid', get_queried_object()->term_id , false);
			
			update_option('wapf_queried_is_cat', 'true' , false);
			
			update_option('wapf_queried_is_shop', 'false' , false);
			
		}
		
		if (is_shop()) {
			
			update_option('wapf_queried_termid', 'empty' , false);
			
			update_option('wapf_queried_is_cat', 'false' , false);
			
			update_option('wapf_queried_is_shop', 'true' , false);
			
		}

	}

	public static function wapf_gfp() {
		
		if (isset($_POST['minimum_price'])) {
			$minimum_price = sanitize_text_field($_POST['minimum_price']);
		} else {
			$minimum_price = 0;
		}
		
		if (isset($_POST['maximum_price'])) {
			$maximum_price = sanitize_text_field($_POST['maximum_price']);
		} else {
			$maximum_price = 999999999999999;
		}				

		/****create filter datas from post request*****/	
		$filter_complete = array();
		
		if (isset($_POST['filters'])) {
			
			foreach ($_POST['filters'] as $filter) {

				$name = esc_attr($filter['attribute']);

				foreach ($filter['Item'] as $item) {
					
					$filter_complete[strtolower($name)][] = esc_attr($item);
					
				}
				
			}
			
		}	
					
		if (isset($filter_complete['cat'])) {
			if (isset($filter_complete['cat'][0])) {
				$filter_cat = $filter_complete['cat'][0];	
			}	
		} else {
			$filter_cat = "";
		}
			
			
		if (isset($_POST['is_shop']) && $_POST['is_shop'] == 'true' && $_POST['is_cat'] == 'false') {
				if ($filter_cat == "allcats") {
					$args = array(
						'post_type' => 'product',
						'post_status'           => 'publish',
						'posts_per_page' => -1,
						'orderby' => 'meta_value_num',
						'meta_key' => '_price',
						'order' => 'desc'				
						
					);	
				} else {
					$args = array(
						'post_type' => 'product',
						'post_status'           => 'publish',
						'posts_per_page' => -1,
						'orderby' => 'meta_value_num',
						'meta_key' => '_price',
						'order' => 'desc',
						'tax_query' => array(
										array(
											'taxonomy' => 'product_cat',
											'field'    => 'term_id',
											'terms'    => array($filter_cat),
											),
										),
											
						
					);						
				}				
		} else {
					$args = array(
						'post_type' => 'product',
						'post_status'           => 'publish',
						'posts_per_page' => -1,
						'orderby' => 'meta_value_num',
						'meta_key' => '_price',
						'order' => 'desc',
						'tax_query' => array(
										array(
											'taxonomy' => 'product_cat',
											'field'    => 'term_id',
											'terms'    => array(sanitize_text_field($_POST['term_id'])),
											),
										),
											
						
					);
		}

		$products = 0;
		
		$ajaxposts = new WP_Query( $args );

		$response = '';

		$prices = array();

		if ( $ajaxposts->posts ){
			
			while ( $ajaxposts->have_posts() ) {
				
				$ajaxposts->the_post();
				$product_id = get_the_ID();
				$product = wc_get_product( $product_id );
				$product_meta = get_post_meta($product_id);
				$product_meta = unserialize($product_meta['_product_attributes'][0]);	

				if ($product->get_price() > $minimum_price && $product->get_price() < $maximum_price or $product->get_price() > $minimum_price && $product->get_price() == $maximum_price) {	
							
					if (isset($product_meta) && !empty($product_meta)) {
						
						foreach ($product_meta as $meta) {

							$name = strtolower($meta['name']);
							$values = $meta['value'];
							$is_taxonomy = $meta['is_taxonomy'];								
							
							/**check if attribut exist***/
							if (isset($filter_complete[$name])) {
																
								if ($is_taxonomy == 1) {
									
									$values = "";	
									
									if (null !== get_the_terms(get_the_ID(), $name)) {
										
										foreach (get_the_terms(get_the_ID(), $name) as $termvalue) {
											
											$term_name = $termvalue->name;
											$term_slug = $termvalue->slug;
											$term_taxonomy_id = $termvalue->term_taxonomy_id;
											$taxonomy = $termvalue->taxonomy;
											$values .= $term_slug." |";
										}
										
									}
									
								}						
								
								$values = str_replace(' ', '', strtolower($values));
								
								$values = explode('|', $values);
								
								foreach ($values as $val) {
									
									if (in_array($val, $filter_complete[$name])) {
										
										setup_postdata($post);
										
										$response .= wc_get_template_part( 'content', 'product' );
										
										$prices[] = $product->get_price();
										
										$products = $products+1;
										
										break;
									}
									
								}
					
								
							/**if parent att not found diplay product***/	
							} else {
								
								/***Att size not found also display product***/
								
								setup_postdata($post);
								
								$response .= wc_get_template_part( 'content', 'product' );
								
								$prices[] = $product->get_price();
								
								$products = $products+1;
								
								break;
							}

						}
						
					} else {
						
						/****No Product meta set so display product*****/
						
						setup_postdata($post);
						
						$response .= wc_get_template_part( 'content', 'product' );
						
						$prices[] = $product->get_price();		
						
						$products = $products+1;		
						
					}
					
				}
			
			}
			
		} else {
			
			$response .= do_action( 'woocommerce_no_products_found' );
			
		}

		$highestprice = max($prices);

		echo "<span class='wapf-hide' id='wapf-max-price-val' data-wapf-maxval='1000'>".$highestprice ."</span>";
		
		echo "<span class='wapf-hide' id='wapf-itemcount'>".$products ."</span>";
		
		wp_die();
		
	}


	static public function wapf_frontend_search() {

	if (function_exists (is_shop)) {
			
	if (is_shop() or is_product_category()) {	
	
	$wapf_options = get_option('wapf_options');

		if (isset($wapf_options) && !empty($wapf_options)) {
		?>
		<div class="container <?php if (isset($wapf_options['wapf-layout'])) { echo 'wapf-'.$wapf_options['wapf-layout']; } else { echo 'wapf-horizontal';} ?>">

			<div class="wapf-row">
			
				<div class="wapf-row-inner">
				
				<?php 
					if (isset($wapf_options['wapf-selection-select'])) {

						$selection_select = $wapf_options['wapf-select-ftype'];

						$xo = 0;
						$all = array();
						
						foreach ($wapf_options['wapf-selection-select'] as $wapf_selection_key => $wapf_selection_value) {

							if ($selection_select[$wapf_selection_key] == "slider") {
								
								if( is_product_category()) {
									global $wp_query;
									$term_id = $wp_query->get_queried_object()->term_id;
									$args = array(
									'post_type' => 'product',
									'post_status'           => 'publish',
									'posts_per_page' => -1,
									'orderby' => 'meta_value_num',
									'meta_key' => '_price',
									'order' => 'desc',
									'tax_query' => array(
													array(
														'taxonomy' => 'product_cat',
														'field'    => 'term_id',
														'terms'    => array($term_id),
														),
													),
														
									
									);	
								} else if (is_shop()) {
									$args = array(
										'post_type' => 'product',
										'post_status'           => 'publish',
										'posts_per_page' => -1,
										'orderby' => 'meta_value_num',
										'meta_key' => '_price',
										'order' => 'desc'				
										
									);								
								}
								
									
									$ajaxposts = new WP_Query( $args );

									$response = '';

									$prices = array();

									if ( $ajaxposts->posts ){
										while ( $ajaxposts->have_posts() ) {
											
											$ajaxposts->the_post();
											$product_id = get_the_ID();
											$product = wc_get_product( $product_id);
											
											$prices[] = $product->get_price();
										}
									}	
									
									$maxval = max($prices);								
												
				?>

								<div class="wapf-filter-element">
									<a href="#" class="wapf-toggle-filter" data-id="<?php echo $wapf_selection_key; ?>">
										<span class="wapf-filter-element-title"><?php echo __('Price', 'product-filter-ajax-for-woo'); ?><span class="dashicons dashicons-arrow-down-alt2"></span></span>
									</a>
										
									<div class="wapf-filter-row <?php if (!isset($wapf_options['wapf-layout']) or $wapf_options['wapf-layout'] == 'horizontal') { echo 'wapf-hide'; } ?>" data-id="<?php echo $wapf_selection_key; ?>-filter">
										<div class="list-group wapf_filter_option">
											<input type="hidden" id="min_price_hide" value="0" />
											<input type="hidden" id="max_price_hide" value="<?php echo $maxval; ?>" />
											<p id="price_show"><span class="wapf-minval"><?php echo get_woocommerce_currency_symbol(); ?>10</span> - <span class="wapf-maxval"><?php echo get_woocommerce_currency_symbol(); ?><?php echo $maxval; ?></span></p>
											<div id="price_range" class="wapf-price-range wapf-price-filter-<?php echo $wapf_selection_key; ?>" data-wapf-fnum="<?php echo $wapf_selection_key; ?>"></div>
										</div>		
									</div>	
								</div>
				<?php
								
							} else if ($selection_select[$wapf_selection_key] == "checkbox" && $wapf_selection_value != "empty") {
								
								if (isset($wapf_selection_value)) {
									
									global $wpdb;
									
									$attribut_key = $wapf_selection_value;
														
									$all_atts = array();
									
									$all_att_values = array();

									$product_attributes = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE `meta_key`='_product_attributes' AND `meta_value` LIKE '%".$attribut_key."%'" );

									if (count($product_attributes)> 0){
										
										foreach ($product_attributes as $product_attribute) {
											
											/***check if post id set - if not isnot a prod_att***/
											
											if (isset($product_attribute->post_id) && !empty($product_attribute->post_id)) {
												
												$attributes = unserialize($product_attribute->meta_value);
												
												$all[$attribut_key] = $attributes;

												if (isset($attributes)) {
													
													foreach ($attributes as $attributes_key => $attributes_values) {
														$is_taxonomy = $attributes_values['is_taxonomy'];
														$attribute_name = $attributes_values['name'];
														$attribute_name = strtolower($attribute_name);

														if (isset($attributes[$attribute_name])) {
															
															if (strtolower($attributes[$attribute_name]['name']) == strtolower($attribut_key)) {
															
															
																if ($is_taxonomy == 0) {
															
																	$attribute_value = explode('|', $attributes_values['value']);
																	
																	if (isset($attribute_value)) {
																		$values = array();
																		foreach ($attribute_value as $value) {
																			$values[] = $value;
																			if (isset($value)) {
																				if (!empty($value)) {
																				$all_att_values[$attribut_key][] = $value;
														
																				}
																			}
																		}
																	}
																
																$all_atts[$attributes_key] = array('name' => $attribute_name, 'values' => $values);
																
																} else {
					
																	$pro_attributes = $wpdb->get_results( "SELECT * FROM $wpdb->term_taxonomy WHERE `taxonomy`='".$attributes[$attribute_name]['name']."'" );
																	if (count($pro_attributes)> 0){
																		foreach ($pro_attributes as $pro_attribute) {
																			
																			$term_id = $pro_attribute->term_id;
																			
																			$term_name = get_term( $term_id )->name;
																			
																			$pro_attributes_items = $wpdb->get_results( "SELECT * FROM $wpdb->terms WHERE `term_id`='".$term_id."'" );
																			
																			if (count($pro_attributes_items)> 0){
																				foreach ($pro_attributes_items as $pro_attributes_item) {
																					$name = $pro_attributes_item->name;
																					$slug = $pro_attributes_item->slug;
																					$tid = $pro_attributes_item->term_id;
																					$all_att_values[$attribut_key][] = $name;
																				}
																			
																			}
																		}
																	}
																	
																	$all_atts[$attributes_key] = array('name' => $attribute_name, 'values' => 'aaa');
																}
																
																


															} else {
																//echo "NEINEN";
															}
														} else {
															//echo "NOSTET";
														}

													}
												}
											}
										
										}
										
									}							
								
								} else {
									$attribut_key = "xxxxxx";
								}
						
						
								/***************************************************/
								/*******CHECK IF ATTRIBUTES FOUND*******************/
								/*******CHECK IF VALUE EXIST MORE THAN ONCE*********/
								/*******DISPLAY FILTER AND OPTIONS******************/
								/******if wapf_selection_value empty = slider and not an checkbox**************/
								/**************************************************/

								if (isset($all_att_values) && !empty($all_att_values) && $wapf_selection_value != "empty") {

									$all_att_values2 = array_unique($all_att_values);

									$atts_array = array();

									foreach ($all_att_values as $atts_key => $atts_values) {
										
										foreach ($atts_values as $atts_val) {
											
											$atts_val = str_replace(" ", "", strtolower($atts_val));
											
											if (!in_array($atts_val, $atts_array)) {
												$atts_array[$atts_val] = $atts_val;
												
											} 
										
										}
										
									}
									?>
			
									<?php if (isset($atts_array)) { ?>
									
									<?php
									if (isset($wapf_options['wapf-is-color'][$wapf_selection_key])) {
										if ($wapf_options['wapf-is-color'][$wapf_selection_key] == 1) {
											$is_color = true;
										} else {
											$is_color = false;
										}
									} else {
										$is_color = false;
									}
									?>
									<div class="wapf-filter-element">
										<div class="list-group wapf_filter_option wapf_cfc wapf_att_<?php echo $attribut_key; ?>" data-wapf-attname="<?php echo $attribut_key; ?>">
										
										
										
										<a href="#" class="wapf-toggle-filter" data-id="<?php echo $wapf_selection_key; ?>">
											<span class="wapf-filter-element-title"><?php echo ucfirst($attribut_key); ?><span class="dashicons dashicons-arrow-down-alt2"></span></span>
										</a>
										
										<span class="wapf-filter-row <?php if (!isset($wapf_options['wapf-layout']) or $wapf_options['wapf-layout'] == 'horizontal') { echo 'wapf-hide'; } ?>" data-id="<?php echo $wapf_selection_key; ?>-filter">				
										<?php foreach ($atts_array as $atkey => $atvalue) { ?>
											<div class="list-group-item checkbox wapf-checkbox-container">
												<label class="b-contain">
													<span><?php echo ucfirst($atvalue); ?></span>
													<input type="checkbox" class="filter_all wapf_<?php echo $attribut_key; ?>" value="<?php echo $atkey; ?>" data-att-value="<?php echo $attribut_key; ?>" data-value="<?php echo $atkey; ?>">
													<div class="b-input"></div>
													<?php
													if ($is_color == true) {
														?>
														<div class="b-input wapf-color-block" style="background:<?php echo $atvalue; ?>;border-color:<?php echo $atvalue; ?>"></div>
														<?php
													} else {
														?>
														<div class="b-input"></div>
														<?php
													} 
													?>
												</label>
											</div>	
										<?php } ?>
										</span>
										</div>
									</div>	
									<?php
									}
			
								}


							} else {
		
								if (isset($wapf_options['wapf-selection-select-cat'][$wapf_selection_key])) {
									
									$cat_id = $wapf_options['wapf-selection-select-cat'][$wapf_selection_key];
									
									if ($cat_id == "allcats") {
									?>
									<div class="wapf-filter-element">	
										<a href="#" class="wapf-toggle-filter" data-id="<?php echo $wapf_selection_key; ?>">
											<span class="wapf-filter-element-title"><?php echo __('Categorie', 'product-filter-ajax-for-woo'); ?><span class="dashicons dashicons-arrow-down-alt2"></span></span>
										</a>
										
										<span class="wapf-filter-row <?php if (!isset($wapf_options['wapf-layout']) or $wapf_options['wapf-layout'] == 'horizontal') { echo 'wapf-hide'; } ?>" data-id="<?php echo $wapf_selection_key; ?>-filter">
										<select class="filter_all wapf-select-cat" data-value="categorie" data-att-value="">

										<?php
										$args = array(
											'taxonomy'     => 'product_cat',
											'orderby'      => 'name',
											'show_count'   => 0,
											'pad_counts'   => 0,
											'hierarchical' => 1,
											'title_li'     => '',
											'hide_empty'   => 0
										);
										
										if (is_product_category()) {
											$pcat = $wp_query->get_queried_object()->term_id;
										} else {
											$pcat = "allcats";
										}

										$all_categories = get_categories( $args );

										echo '<option value="allcats" data-att-value="allcats">'.__('All Categories', 'product-filter-ajax-for-woo').'</option>';

										foreach ($all_categories as $cat) {
											
											if($cat->category_parent == 0) {

												$category_id = $cat->term_id;
												
												if ($pcat == $category_id) {
													$cat_selected = "selected";
												} else {
													$cat_selected = "";
												}
												?>
												<option value="<?php echo $cat->term_id; ?>" data-att-value="<?php echo $cat->term_id; ?>" <?php echo $cat_selected; ?>><?php echo $cat->name; ?></option>					
												<?php
												$args2 = array(
													'taxonomy'     => 'product_cat',
													'parent'       => $category_id,
													'orderby'      => 'name',
													'show_count'   => 0,
													'pad_counts'   => 0,
													'hierarchical' => 1,
													'title_li'     => '',
													'hide_empty'   => 0
												);

												$sub_cats = get_categories( $args2 );

												if($sub_cats) {

													foreach($sub_cats as $sub_category) {
														
														if ($pcat == $sub_category->term_id) {
															$sub_cat_selected = "selected";
														} else {
															$sub_cat_selected = "";
														}

														echo '<option value="'.$sub_category->term_id.'" data-att-value="'.$cat->term_id.'" '.$sub_cat_selected.'>- '. $sub_category->name.'</option>';
														
														//echo apply_filters( 'woocommerce_subcategory_count_html', ' (' . $sub_category->count . ')', $category );


														 $args3 = array(
															'taxonomy'     => 'product_cat',
															'parent'       =>  $sub_category->term_id,
															'orderby'      => 'name',
															'show_count'   => 0,
															'pad_counts'   => 0,
															'hierarchical' => 1,
															'title_li'     => '',
															'hide_empty'   => 0
														);

														$sub_cats3 = get_categories( $args3 );

														if($sub_cats3) {

															foreach($sub_cats3 as $sub_category3) {
																
																if ($pcat == $sub_category3->term_id) {
																	$sub_cat3_selected = "selected";
																} else {
																	$sub_cat3_selected = "";
																}									
																
																echo '<option value="'.$sub_category3->term_id.'" '.$sub_category3_selected.' '.$sub_cat3_selected.'>-- '. $sub_category3->name.'</option>';

																//echo apply_filters( 'woocommerce_subcategory_count_html', ' (' . $sub_category3->count . ')', $category );

															}

														}

													}
												}				
											
											}
										}
										
										
									} else {
										
												$args2 = array(
													'taxonomy'     => 'product_cat',
													'parent'       => $cat_id,
													'orderby'      => 'name',
													'show_count'   => 0,
													'pad_counts'   => 0,
													'hierarchical' => 1,
													'title_li'     => '',
													'hide_empty'   => 0
												);

												$cats = get_categories( $args2 );

												if($cats) {
													if( $term = get_term_by( 'id', $cat_id, 'product_cat' ) ){
														$cat_name = $term->name;
													}						
													?>
													<span class="wapf-filter-element-title"><?php echo __('Categorie', 'product-filter-ajax-for-woo'); ?><span class="dashicons dashicons-arrow-down-alt2"></span></span>	
													
													<select>
													<option value="<?php echo $cat_id; ?>"><?php echo $cat_name; ?></option>
													<?php
														foreach($cats as $category) {

															echo '<option value="'.$category->term_id.'" '.$sub_category_selected.'>- '. $category->name.'</option>';
															
														}
													?>
													</select>
													<?php						
												}
										
										
									}
									?>
									
									</select>
									</span>
									</div>
									<?php
								}
							}	
						
						}

					}

				?>
				</div>

				<div class="col-md-9">

					<div class="row filter_data">

					</div>

				</div>
		   
		   </div>

		<?php
		}
	}
	}
	}
	
	static public function wapf_frontend_scripts(){
		
		if (function_exists (is_shop)) {
			
			if (is_shop() or is_product_category()) {
		
				wp_enqueue_script( 'jquery-ui-widget' );
				
				wp_enqueue_script( 'jquery-ui-core' );
				
				wp_enqueue_script( 'jquery-ui-slider' );
				
				wp_enqueue_style( 'bootstrap-css',  plugin_dir_url(__FILE__).'core/assets/css/jquery-ui.css' );
				
				wp_enqueue_style( 'bootstrap-css',  plugin_dir_url(__FILE__).'core/assets/css/bootstrap.min.css' );
				
				wp_enqueue_style( 'plugin-css',  plugin_dir_url(__FILE__).'core/assets/css/wapf-css.css' );
				
				wp_enqueue_script( 'bootstrap-js',  plugin_dir_url(__FILE__).'core/assets/js/bootstrap.min.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget' ), true );	

				$parms = array(
					'plugindir' => plugin_dir_url(__FILE__),
					'ajaxurl' => admin_url('admin-ajax.php'),
					'queried_termid' => get_option('wapf_queried_termid', 'empty'),
					'queried_is_cat' => get_option('wapf_queried_is_cat', 'no'),
					'queried_is_shop' => get_option('wapf_queried_is_shop', 'no'),
					'sorting' => get_option( 'woocommerce_default_catalog_orderby' ),
					'woocurrency' => get_woocommerce_currency_symbol()
				);
				
				wp_register_script('plugin-js', plugin_dir_url(__FILE__).'core/assets/js/wapf-js.js');			
				
				wp_localize_script('plugin-js', 'wapfparms', $parms); 			
				
				wp_enqueue_script('plugin-js');
				
			}
			
		}	
		
	}	
	
}

wapf_frontend::init();


//*******************************//
//*********BACKEND PART*********//
//*******************************//
class wapf_backend {
	
    public static function init() {
		
		if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			
			add_action( 'admin_menu', __CLASS__ . '::wapf_backend_admin_menu' );
			
			add_action( 'wp_ajax_wapf_get_all_attributes', __CLASS__ . '::wapf_get_all_attributes' );
			
			add_action( 'wp_ajax_nopriv_wapf_get_all_attributes', __CLASS__ . '::wapf_get_all_attributes' );
			
			add_action( 'admin_enqueue_scripts', __CLASS__ . '::wapf_backend_js' );		
			
			add_action( 'wp_ajax_wapf_delete_filter', __CLASS__ . '::wapf_delete_filter' );
		
		} else {
			
			add_action( 'admin_notices',  __CLASS__ . '::wapf_woocommerce_disabled' );
			
		}
	
	}
	
	static public function wapf_woocommerce_disabled() {
	?>
		<div class="update-nag notice">
			<p><?php echo __( 'Woo Ajax Product Filter benötigt WooCommerce. Bitte installieren Sie WooCommerce.', 'product-filter-ajax-for-woo' ); ?></p>
		</div>
	<?php		
	}
	
	static public function wapf_delete_filter() {
		$output = "";
		if (isset($_POST['action']) && $_POST['action'] == 'wapf_delete_filter') {
			if (isset($_POST['fiter_id'])) {
				$filter_id = sanitize_text_field($_POST['fiter_id']);
				$wapf_options = get_option('wapf_options');
				$wapf_options_db = get_option('wapf_options');
				if (isset($wapf_options) && !empty($wapf_options)) {				
					
					if (isset($wapf_options['wapf-selection-select'])) {
						if (isset($wapf_options['wapf-selection-select'][$filter_id])) {
							unset($wapf_options['wapf-selection-select'][$filter_id]);
						}
					}
					
					if (isset($wapf_options['wapf-select-ftype'])) {
						if (isset($wapf_options['wapf-select-ftype'][$filter_id])) {
							unset($wapf_options['wapf-select-ftype'][$filter_id]);
						}
					}					
					
					if ($wapf_options != $wapf_options_db) {
						update_option('wapf_options', $wapf_options ,false);
						$output .= __('The Filter with the ID', 'product-filter-ajax-for-woo').' ';
						$output .= $filter_id+1;
						$output .= __(' was sucessfully deleted!', 'product-filter-ajax-for-woo');
					}
				}
			} else {
				$output .= __('No Filter deleted because no Filter ID!', 'product-filter-ajax-for-woo');
			}
			
		}
		
		wp_die($output);
	}
	
	static public function wapf_backend_js() {
		$parms = array(
			'plugindir' => plugin_dir_url(__FILE__),
			'ajaxurl' => admin_url('admin-ajax.php')
		);
		wp_register_script('wapf-admin-js', plugin_dir_url(__FILE__).'core/assets/js/wapf-admin.js');			
		wp_localize_script('wapf-admin-js', 'wapfadminparms', $parms); 			
		wp_enqueue_script('wapf-admin-js');		

		wp_enqueue_style('wapf-admin-css', plugin_dir_url(__FILE__).'core/assets/css/wapf-admin.css');		

	}
	
	
	static public function wapf_get_all_attributes() {
		
	$output = "";
	
	global $wpdb;
	
	$product_attributes = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE `meta_key`='_product_attributes'" );
	
		if (count($product_attributes)> 0){
			
			$wapf_options = get_option('wapf_options');
			$key = count($wapf_options['wapf-selection-select'])+1;
			$output .= '<span class="wapf-filter-title wapf100">'.__('Filter', 'product-filter-ajax-for-woo').' #'.$key.' <span class="wapf-small">['.__('Not saved', 'product-filter-ajax-for-woo').']</span><a href="#" class="wapf-remove-filter" title="" data-wapf-filter-id="'.$key.'"><span class="dashicons dashicons-trash"></span></a></span>';
			$output .= '<span class="wapf-filter-content wapf100">';
			$output .= '<span class="wapf-option-row wapf100">';
			$output .= '<label>'.__('Filter Type', 'product-filter-ajax-for-woo').':</label>';	
			$output .= '<select name="wapf-select-ftype[]" class="wapf-select-ftype" data-wapf="wapftypeid">';
			$output .= '<option value="empty">'.__('Please select...', 'product-filter-ajax-for-woo').'</option>';		
			$output .= '<option value="slider">'.__('Slider Filter (Price)', 'product-filter-ajax-for-woo').'</option>';	
			$output .= '<option value="checkbox">'.__('Checkbox Filter (Custom Attributes)', 'product-filter-ajax-for-woo').'</option>';	
			$output .= '<option value="select">'.__('Select Filter (Categories)', 'product-filter-ajax-for-woo').'</option>';		
			$output .= '</select>';	
			$output .= '</span>';		
			$output .= '<span class="wapf-option-row wapf100">';
			$output .= '<label class="wapfselectlabel wapf-hide">'.__('Filter Attribut', 'product-filter-ajax-for-woo').':</label>';		
			$output .= '<select name="wapf-selection-select[]" class="wapf-seletion-select wapf-hide" data-wapf="wapfselectid">';
			$output .= '<option value="empty">'.__('Please select...', 'product-filter-ajax-for-woo').'</option>';	
			$atts_array = array();
			
			foreach ($product_attributes as $product_attribute) {
				
				$attributes = unserialize($product_attribute->meta_value);
				
				if (isset($attributes)) {

						foreach ($attributes as $attributes_key => $attributes_values) {
							
							$attribute_name = $attributes_values['name'];
							
							if ($attribute_name == "pa_brand" or $attributename == "Pa_brand") { $attribute_name = __('Brand', 'product-filter-ajax-for-woo'); }
							$atts_array[$attributes_key] = $attribute_name;
						
							
						}
			
				}
			}
			
			if (isset($atts_array)) {
				
				foreach ($atts_array as $atts_array_key => $atts_array_values) {
					
					$output .= '<option value="'.$atts_array_key.'">'.ucfirst($atts_array_values).'</option>';
					
				}
				
			}
			
			$output .= '</select>';
			
			
			$output .= '<select name="wapf-selection-select-cat[]" class="wapf-seletion-select-cat wapf-hide" data-wapf-cat="wapfselectcatid">';
			
			$output .= '<option value="empty">'.__('Please select...', 'product-filter-ajax-for-woo').'</option>';			

			$taxonomy     = 'product_cat';
			$orderby      = 'name';  
			$show_count   = 0;      
			$pad_counts   = 0;      
			$hierarchical = 1;      
			$title        = '';  
			$empty        = 0;

			$args = array(
				'taxonomy'     => $taxonomy,
				'orderby'      => $orderby,
				'show_count'   => $show_count,
				'pad_counts'   => $pad_counts,
				'hierarchical' => $hierarchical,
				'title_li'     => $title,
				'hide_empty'   => $empty
			);

			$all_categories = get_categories( $args );
			
			$output .= '<option value="allcats">'.__('All Categories', 'product-filter-ajax-for-woo').'</option>';
			
			foreach ($all_categories as $cat) {

				if($cat->category_parent == 0) {

					$category_id = $cat->term_id;

					$output .= '<option value="'.$cat->term_id.'">'. $cat->name .'</option>';
					
					$args2 = array(
						'taxonomy'     => $taxonomy,
						'parent'       => $category_id,
						'orderby'      => $orderby,
						'show_count'   => $show_count,
						'pad_counts'   => $pad_counts,
						'hierarchical' => $hierarchical,
						'title_li'     => $title,
						'hide_empty'   => $empty
					);

					$sub_cats = get_categories( $args2 );

					if($sub_cats) {

						foreach($sub_cats as $sub_category) {
							
							$output .= '<option value="'.$sub_category->term_id.'">- '. $sub_category->name.'</option>';
							
							//echo apply_filters( 'woocommerce_subcategory_count_html', ' (' . $sub_category->count . ')', $category );

							$args3 = array(
								'taxonomy'     => $taxonomy,
								'parent'       =>  $sub_category->term_id,
								'orderby'      => $orderby,
								'show_count'   => $show_count,
								'pad_counts'   => $pad_counts,
								'hierarchical' => $hierarchical,
								'title_li'     => $title,
								'hide_empty'   => $empty
							);

							$sub_cats3 = get_categories( $args3 );

							if($sub_cats3) {

								foreach($sub_cats3 as $sub_category3) {
									
									$output .= '<option value="'.$sub_category3->term_id.'">-- '. $sub_category3->name.'</option>';
									
									//echo apply_filters( 'woocommerce_subcategory_count_html', ' (' . $sub_category3->count . ')', $category );

								}

							}

						}
					}
				}       
			}
			
			$output .= '</select>';

			$output .= '</span>';
			
			$output .= '<label class="wapfselectlabelcolor wapf-hide">'.__('Is Color', 'product-filter-ajax-for-woo').':</label>';			


$output .= '<div class="iscolor wapfselectcolorcheckbox wapf-hide">';
$output .= '<input type="hidden" name="iscolor[]" value="0" class="iscolor-checkbox" id="wapfiscolor0" tabindex="0">';
$output .= '<input type="checkbox" name="iscolor[]" value="1" class="iscolo3r-checkbox" id="wapfiscolor" tabindex="0" checked>';
$output .= '<label class="iscol3or-label" for="wapfiscolor">';
$output .= '<span class="iscolo3r-inner"></span>';
$output .= '<span class="iscolo3r-switch"></span>';
$output .= '</label>';
$output .= '</div>';
			
			$output .= '</span>';

			wp_die($output);
		}
	}
	
	static public function wapf_backend_admin_menu() {
		
		add_submenu_page( 'woocommerce', 'Product Filter', 'Product Filter', 'manage_options', 'wapf-settings', 'wapf_admin_settings' ); 
			
		function wapf_admin_settings() {
			
			include(plugin_dir_path(__FILE__).'core/inc/wapf_settings.php');
			
		}

		function wapf_gaas() {
			
			$attributes = array();
			
			global $wpdb;
			
			$product_attributes = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE `meta_key`='_product_attributes'" );
			
			if (count($product_attributes)> 0){
				
				foreach ($product_attributes as $product_attribute) {
					
					$attribut= unserialize($product_attribute->meta_value);
					
					if (isset($attribut)) {
						
						foreach ($attribut as $attributes_key => $attributes_values) {

							$attribute_name = $attributes_values['name'];

							$attributes[$attributes_key] = $attribute_name;

							if ($attribute_name == "pa_brand" or $attributename == "Pa_brand") { $attribute_name = __('Brand', 'product-filter-ajax-for-woo'); }

						}
					}

				}
			
			}

			return $attributes;
		
		}
		
	}
		
}

if (is_admin()) {
	wapf_backend::init();
}	