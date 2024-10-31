<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wrap">

<?php

global $wpdb;

$saved_datas = "";

if (isset($_POST['submit'])) {
		
	$privilegs = check_admin_referer( 'wapf-settings' );
	
	if ($privilegs == 1) {
		
		$settings_array = array();

		if (isset($_POST['wapf-selection-select'])) {
			
			$settings_array['wapf-selection-select'] = $_POST['wapf-selection-select'];
			
		}
		if (isset($_POST['wapf-select-ftype'])) {
			
			$settings_array['wapf-select-ftype'] = $_POST['wapf-select-ftype'];
			
		}
		if (isset($_POST['wapf-selection-select-cat'])) {
			
			$settings_array['wapf-selection-select-cat'] = $_POST['wapf-selection-select-cat'];
			
		}
		
		if (isset($_POST['wapf-is-color'])) {
			
			$settings_array['wapf-is-color'] = $_POST['wapf-is-color'];
			
		}	

		if (isset($_POST['wapf-layout'])) {
			
			$settings_array['wapf-layout'] = $_POST['wapf-layout'];
			
		}		
		
		update_option('wapf_options', $settings_array, false);
		
		?>
		
		<div class="notice-success notice">
			
			<p><?php echo __( 'Settings are successfully saved', 'product-filter-ajax-for-woo' ); ?>&nbsp;<span class="dashicons dashicons-yes"></span></p>
		
		</div>		
		
		<?php
		
	}
	
}

?>

<h2><?php echo __('WooCommerce Product Filter Options', 'product-filter-ajax-for-woo'); ?></h2>

<p>
	<?php echo __('Here you can setup your filters.', 'product-filter-ajax-for-woo'); ?>&nbsp;
	<?php echo __('The free version is limited to 3 filters.', 'product-filter-ajax-for-woo'); ?>

</p>




	<div class="wapf-selection">

		<div class="wapf-options">
		
		<?php $wapf_options = get_option('wapf_options'); ?>

			
			
			<form action="" method="POST" class="wapf-selection-form">
			<span class="wapf-selection-select-container wapf100 wapf-general">
			<span class="wapf-filter-title wapf100"><?php echo __('Display Options', 'product-filter-ajax-for-woo'); ?></span>
				<span class="wapf-filter-content wapf100">
					<span class="wapf-option-row wapf100">
						<label><?php echo __('Location', 'product-filter-ajax-for-woo'); ?>:</label>

						<input type="checkbox" class="iscolo3r-checkbox" name="wapf-location" value="shopbeforeproducts" <?php if (!isset($wapf_options['wapf-location']) or $wapf_options['wapf-location'] == 'shopbeforeproducts') { echo 'checked';} ?>>
						<label for="male"><?php echo __('Shop Page [before products]', 'product-filter-ajax-for-woo'); ?></label>

					</span>
					<span class="wapf-option-row wapf100">
						<label><?php echo __('Layout', 'product-filter-ajax-for-woo'); ?>:</label>
						<select name="wapf-layout">
							<option value="horizontal" <?php if (isset($wapf_options['wapf-layout']) && $wapf_options['wapf-layout'] == 'horizontal') { echo 'selected';} ?>><?php echo __('Horizontal', 'product-filter-ajax-for-woo'); ?></option>
							<option value="vertical" <?php if (isset($wapf_options['wapf-layout']) && $wapf_options['wapf-layout'] == 'vertical') { echo 'selected';} ?>><?php echo __('Vertical', 'product-filter-ajax-for-woo'); ?></option>
						</select>
					</span>
					<span class="wapf-option-row wapf100">
						<label><?php echo __('Shortcode', 'product-filter-ajax-for-woo'); ?>:</label>
						<input type="text" value="[wapf_filter]" readonly/>
					</span><br /><br />
										<small><span class="dashicons dashicons-info"></span><?php echo __('Filter works only on WooCommerce Shop/Categorie Page + in Sidebar on Shop/Categorie Page.', 'product-filter-ajax-for-woo'); ?></small>
	
				</span>	
				
			</span>			
				<div class="wapf-selection-container">

				<?php
				
				$wapf_ftype_vals = array(	'slider' => __('Slider Filter (Price)', 'product-filter-ajax-for-woo'),
											'checkbox' => __('Checkbox Filter (Custom Attributes)', 'product-filter-ajax-for-woo'),
											'select' => __('Select Filter (Categories)', 'product-filter-ajax-for-woo'));

				if (isset($wapf_options) && !empty($wapf_options)) {
					
					$allattributes = wapf_gaas();
					
					$counter_1 = 0;
					
					if (isset($wapf_options['wapf-selection-select'])) {
						
						$count = 0;
						
						$count2 = 0;
						
						$count3 = 0;
						
						$count4 = 0;
						
						$count5 = 0;
						
						$item_count = 0;
						
						$filter_count = 1;
						
						foreach ($wapf_options['wapf-selection-select'] as $selection_select_key => $selection_select_value) {
							
							$count2 = $count2++;
							
							echo '<span class="wapf-selection-select-container wapfnum'.$count++.' wapf100">';
							
							echo '<span class="wapf-filter-title wapf100">'.__('Filter', 'product-filter-ajax-for-woo').'&nbsp;#'.$filter_count++.'<a href="#" class="wapf-delete-filter" title="" data-wapf-filter-id="'.$item_count++.'"><span class="dashicons dashicons-trash"></span></a></span>';
							
							echo '<span class="wapf-filter-content wapf100">';
							
							echo '<span class="wapf-option-row wapf100">';
							
							echo '<label>'.__('Filter Type', 'product-filter-ajax-for-woo').':</label>';
							
							echo '<select name="wapf-select-ftype[]" class="wapf-select-ftype wapf-select-load" data-wapf="wapf-type-'.$count2++.'" data-wapf-s-id="">';
							
							foreach ($wapf_ftype_vals as $wapf_ftype_key => $wapf_ftype_name) {
												
								if ($wapf_options['wapf-select-ftype'][$selection_select_key] == $wapf_ftype_key) {
									
									echo '<option value="'.$wapf_ftype_key.'" selected>'.$wapf_ftype_name.'</option>';
									
								} else {
									
									echo '<option value="'.$wapf_ftype_key.'">'.$wapf_ftype_name.'</option>';	
									
								}
								
							}
							
							echo '</select>';
							
							echo '</span>';

							if ($wapf_options['wapf-select-ftype'][$selection_select_key] == "checkbox") {
								
								$display_select = "";
								
							} else {
								
								$display_select = "wapf-hide";
								
							}
							if ($wapf_options['wapf-select-ftype'][$selection_select_key] == "checkbox" or $wapf_options['wapf-select-ftype'][$selection_select_key] == "select") {
								
								$display_label = "";
								
							} else {
								
								$display_label = "wapf-hide";
								
							}			
							
							echo '<span class="wapf-option-row wapf100">';
							
							echo '<label class="wapf-select-'.$count5++.'-label '.$display_label.'">'.__('Filter Attribut', 'product-filter-ajax-for-woo').':</label>';
							
							echo '<select name="wapf-selection-select[]" class="wapf-seletion-select '.$display_select.'" data-wapf="wapf-select-'.$count3++.'">';
							
							echo '<option value="empty">'.__('Please select...', 'product-filter-ajax-for-woo').'</option>';
							
							foreach ($allattributes as $attributekey => $attributename) {
								
								if ($attributename == "pa_brand" or $attributename == "Pa_brand") { $attributename = __('Brand', 'product-filter-ajax-for-woo'); }
								
								if ($attributekey == $selection_select_value) {
									
									echo '<option value="'.$attributekey.'" selected>'.ucfirst($attributename).'</option>';
									
								} else {
									
									echo '<option value="'.$attributekey.'">'.ucfirst($attributename).'</option>';
									
								}
								
							}
							
							echo '</select>';


							if ($wapf_options['wapf-select-ftype'][$selection_select_key] == "select") {
								
								$display_select_cat = "";
								
							} else {
								
								$display_select_cat = "wapf-hide";
								
							}
							
										
							if (isset($wapf_options['wapf-selection-select-cat'][$selection_select_key])) {
								
								$selected_cat = $wapf_options['wapf-selection-select-cat'][$selection_select_key];
								
							} else {
								
								$selected_cat = "";
								
							}
							
							echo '<select name="wapf-selection-select-cat[]" class="wapf-seletion-select-cat '.$display_select_cat.'" data-wapf-cat="wapf-select-'.$count4++.'">';

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

							echo '<option value="allcats">'.__('All Categories', 'product-filter-ajax-for-woo').'</option>';

							foreach ($all_categories as $cat) {

								if($cat->category_parent == 0) {

									$category_id = $cat->term_id;

									if ($selected_cat == $category_id) {
										
										$category_selected = "selected";
										
									} else {
										
										$category_selected = "";
										
									}
									
									echo '<option value="'.$cat->term_id.'" '.$category_selected.'>'. $cat->name .'</option>';
									
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
											
											if ($selected_cat == $sub_category->term_id) {
												
												$sub_category_selected = "selected";
												
											} else {
												
												$sub_category_selected = "";
												
											}

											echo '<option value="'.$sub_category->term_id.'" '.$sub_category_selected.'>- '. $sub_category->name.'</option>';
											
											echo apply_filters( 'woocommerce_subcategory_count_html', ' (' . $sub_category->count . ')', $category );

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
													
													if ($selected_cat == $sub_category3->term_id) {
														
														$sub_category3_selected = "selected";
														
													} else {
														
														$sub_category3_selected = "";
														
													}	
													
													echo '<option value="'.$sub_category3->term_id.'" '.$sub_category3_selected.'>-- '. $sub_category3->name.'</option>';

													echo apply_filters( 'woocommerce_subcategory_count_html', ' (' . $sub_category3->count . ')', $category );

												}

											}

										}
										
									}
									
								} 
								
							}
							
							echo '</select>';			
							
							echo '</span>';	


if ($wapf_options['wapf-select-ftype'][$selection_select_key] == "checkbox") {
	$is_color_class = "";
	} else {
	$is_color_class = "wapf-hide";
}	
							if (isset($wapf_options['wapf-is-color'][$selection_select_key])) {
								if ($wapf_options['wapf-is-color'][$selection_select_key] == 1) {
									$is_color_checked = "checked";
								} else {
									$is_color_checked = "";
								}
							} else {
								$is_color_checked = "";
							
							}
							echo '<span class="wapf-select-'.$selection_select_key.'-colorcheckbox '.$is_color_class.'">';
echo '<label>'.__('Is Color', 'product-filter-ajax-for-woo').':</label>';				
echo '<input type="hidden" name="wapf-is-color['.$selection_select_key.']" value="0" class="iscolo3r-checkbox" id="wapfiscolor" tabindex="0">';
echo '<input type="checkbox" name="wapf-is-color['.$selection_select_key.']" value="1" class="iscolo3r-checkbox" id="wapfiscolor" tabindex="0" '.$is_color_checked.'>';
echo '</span>';	

							
							echo '</span>';	
							

							
							echo '</span>';	
							
						}

					}

				}

				?>
				</div>
				
				<div class="wapf-actions">
				
					<a href="#" class="button button-primary wapf-add-selection"><?php echo __('Add Filter', 'product-filter-ajax-for-woo'); ?><span class="dashicons dashicons-plus-alt"></span></a>

					<?php
					wp_nonce_field( 'wapf-settings');
					submit_button(__('Save Settings', 'product-filter-ajax-for-woo'), 'button button-primary wapf_add_selection');
					?>
					
				</div>

			</form>

		</div>
		
		<div class="wapf-sidebar">
		
			<div class="wapf-sidebar-container">
			
				<div class="wapf-sidebar-header"><h2><?php echo __('Infos', 'product-filter-ajax-for-woo'); ?></h2></div>
				
				<div class="wapf-sidebar-content">
				
					<?php echo __('Support', 'product-filter-ajax-for-woo'); ?>:&nbsp;<a href="https://wordpress.org/support/plugin/product-filter-ajax-for-woo/" target="blank"><?php echo __('WordPress Forum', 'product-filter-ajax-for-woo'); ?></a><br />
					
					<?php echo __('FAQ', 'product-filter-ajax-for-woo'); ?>:&nbsp;<a href="https://wordpress.org/support/plugin/product-filter-ajax-for-woo/" target="blank"><?php echo __('Go to Support Forum', 'product-filter-ajax-for-woo'); ?></a><br/>
					
					<?php echo __('Contact', 'product-filter-ajax-for-woo'); ?>:&nbsp;<a href="mailto:michaelleithold18@gmail.com"><?php echo __('michaelleithold18@gmail.com', 'product-filter-ajax-for-woo'); ?></a>
				
				</div>
			
			</div>
			
			
			
			<div class="wapf-sidebar-container">
			
				<div class="wapf-sidebar-header"><h2><?php echo __('Pro Version', 'product-filter-ajax-for-woo'); ?></h2></div>
				
				<div class="wapf-sidebar-content">
				
				<ul>
					
					<li><span class="dashicons dashicons-plus-alt2"></span><?php echo __('Unlimited Filters', 'product-filter-ajax-for-woo'); ?></li>
					
					<li><span class="dashicons dashicons-plus-alt2"></span><?php echo __('Customize all Colors', 'product-filter-ajax-for-woo'); ?></li>
					
					<li><span class="dashicons dashicons-plus-alt2"></span><?php echo __('More Checkbox Designs', 'product-filter-ajax-for-woo'); ?></li>
					
					<li><span class="dashicons dashicons-plus-alt2"></span><?php echo __('More Select Box Designs', 'product-filter-ajax-for-woo'); ?></li>
					
					<li><span class="dashicons dashicons-plus-alt2"></span><?php echo __('More Price Filter Designs', 'product-filter-ajax-for-woo'); ?></li>
					
					<li><span class="dashicons dashicons-plus-alt2"></span><?php echo __('Premium Updates', 'product-filter-ajax-for-woo'); ?></li>
					
					<li><span class="dashicons dashicons-plus-alt2"></span><?php echo __('Premium Support', 'product-filter-ajax-for-woo'); ?></li>
				
				</ul>
				
				<p class="wapf-price"><?php echo __('for only <b>€19,99,-</b>', 'product-filter-ajax-for-woo'); ?></p>
				
				<p class="wapf-small supportupdates"><?php echo __('+ Lifetime Support & Updates', 'product-filter-ajax-for-woo'); ?></p>
				
				<p class="wapf-small"><?php echo __('To buy the Pro Version send me an email to michaelleithold18@gmail.com.<br />You will get all infos.', 'product-filter-ajax-for-woo'); ?></p>
				
				<a href="mailto:michaelleithold18@gmail.com" class="button button-primary wapf-add-selection"><?php echo __('Buy the Pro Version', 'product-filter-ajax-for-woo'); ?><span class="dashicons dashicons-cart"></span></a>	
				
				</div>
			
			</div>
		
		</div>

	</div>

</div>