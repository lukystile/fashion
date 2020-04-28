<?php

function isDesignerProductCatParent($slug = false) {
    if (!$slug) {
        return false;
    }
    $catDesigners = get_terms([
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'slug'       => 'designers',
    ]);

    $designersChilds = get_terms([
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'child_of'  => $catDesigners[0]->term_id,
        'fields' => 'slugs'
    ]);

    if (in_array($slug, $designersChilds)) {
        return true;
    }
    return false;
}

add_action( 'after_setup_theme', 'theme_register_nav_menu' );
function theme_register_nav_menu() {
  register_nav_menu( 'children', 'Children Menu' );
  register_nav_menu( 'women', 'Women Menu' );
}

add_shortcode('menu_custom_choice', 'menu_custom_choice');
function menu_custom_choice(){
    global $wp_query;
    $current_url="https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    if(get_queried_object()->slug != null){
        $current_slug = get_queried_object()->slug;
    }else{
        $current_slug = [];
    }
    if($current_url === home_url('/') || is_page(['designers','contact-us','about-us','cart','checkout']) && !$_SESSION['shop_type'] || is_product() && !$_SESSION['shop_type']
        || (isDesignerProductCatParent($wp_query->query_vars['product_cat']) && !$_SESSION['shop_type'])){
        $menu_choice = wp_nav_menu(['menu' => 'shared pages']);
    }elseif($current_slug === "children" || $_SESSION['shop_type'] === "children" || is_page(['children-designer', 'whats-new-children'])){
        $menu_choice = wp_nav_menu(['menu' => 'Children Menu']);
    }elseif($current_slug === "women" || $_SESSION['shop_type'] === "women" || is_page(['women-designer', 'whats-new-women'])){
        $menu_choice = wp_nav_menu(['menu' => 'Women Menu']);
    }else{
        if($_SESSION['shop_type'] == 'children'){
            $menu_choice = wp_nav_menu(['menu' => 'Children Menu']);
        }elseif($_SESSION['shop_type'] == 'women'){
            $menu_choice = wp_nav_menu(['menu' => 'Women Menu']);
        }else{
            $menu_choice = wp_nav_menu(['menu' => 'shared pages']);
        }
    }

    return $menu_choice; 
}

function local_designers_get_styleshit() {
    get_template_part('user-function');
}
add_action('admin_enqueue_scripts', 'local_designers_get_styleshit');

add_shortcode('local_designers', 'page_local_designers');
function page_local_designers(){
    $local_designer_obj_id = get_terms ( array (
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'slug'       => 'local-designer',
        )
    );
    $local_designers_obj = get_terms( array(
        'child_of'   => $local_designer_obj_id[0]->term_id,
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        ) 
    );
    foreach($local_designers_obj as $designer){
        $first_letter = mb_strtoupper(substr($designer->name, 0, 1));
        $local_designers_list[$first_letter][] = $designer;
    }

    $count = 0;
    ob_start();
    ?>
        <?php foreach ($local_designers_list as $key => $value): ?>
            <?php if ($count % 4 === 0): ?>
            <?php if ($count !== 0): ?>
                </div>
            <?php endif; ?>
            <div class="row">
        <?php endif; ?>
        <div class="col medium-3 text-center">
            <h3><?=$key?></h3>
            <ul class="single-designer">
                <?php foreach ($value as $solo_designer): ?>
                    <li><a href="/product-category/designers/local-designer/<?=$solo_designer->slug?>"><?=$solo_designer->name?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($count+1 === count($local_designers_list)): ?>
            </div>
        <?php endif; ?>
        <?php $count++; endforeach; ?>
        <style type="text/css">
        #tab_local-designers ul {
            list-style-type: none;
        }
        #tab_local-designers li {
           margin-bottom: 0;
        }
        .entry-content ul li, .entry-summary ul li, .col-inner ul li {
            margin-left: 0;
        }
        </style>
    <?php
    return ob_get_clean();
}

add_shortcode('international_designers', 'page_international_designers');
function page_international_designers(){
    $international_designer_obj_id =  get_terms ( array (
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'slug'       => 'international-designer',
        )
    );
    $international_designer_obj = get_terms( array(
        'child_of'   => $international_designer_obj_id[0]->term_id,
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        ) 
    );
    foreach($international_designer_obj as $designer){
        $first_letter = mb_strtoupper(substr($designer->name, 0, 1));
        $international_designer_list[$first_letter][] = $designer;
    }

    $count = 0;
    ob_start();
    ?>
        <?php foreach ($international_designer_list as $key => $value): ?>
        <?php if ($count % 4 === 0): ?>
            <?php if ($count !== 0): ?>
                </div>
            <?php endif; ?>
            <div class="row">
        <?php endif; ?>
        <div class="col medium-3 text-center">
            <h3><?=$key?></h3>
            <ul class="single-designer">
                <?php foreach ($value as $solo_designer): ?>
                    <li><a href="/product-category/designers/international-designer/<?=$solo_designer->slug?>"><?=$solo_designer->name?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($count+1 === count($international_designer_list)): ?>
            </div>
        <?php endif; ?>
        <?php $count++; endforeach; ?>
        <style type="text/css">
        #tab_international-designers ul {
            list-style-type: none;
        }
        #tab_international-designers li {
           margin-bottom: 0;
        }
        .entry-content ul li, .entry-summary ul li, .col-inner ul li {
            margin-left: 0;
        }
        </style>
    <?php
    return ob_get_clean();
}

add_filter( 'woocommerce_cart_item_name', 'ts_product_image_on_checkout', 10, 3 );
function ts_product_image_on_checkout( $name, $cart_item, $cart_item_key ) {
     
    if ( ! is_checkout() ) {
        return $name;
    }

    $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    $thumbnail = $_product->get_image();
    $image = '<div class="ts-product-image" style="width: 82px; height: auto; display: inline-block; padding-right: 7px; vertical-align: middle;">'
                . $thumbnail .
            '</div>'; 
    return $image . $name;
}

function add_shipping_rate_formula_setting( $settings ) {
    $updated_settings = [];
  
    foreach ( $settings as $section ) {
  
        if ( isset( $section['id'] ) && 'woocommerce_shipping_debug_mode' === $section['id'] ) {

            $updated_settings[] = [
                'name'     => __( 'International order base rate (flat)', 'woocommerce' ),
                // 'desc_tip' => __( 'The starting number for the incrementing portion of the order numbers, unless there is an existing order with a higher number.', 'woocommerce' ),
                'id'       => 'woocommerce_shipping_international_base_rate',
                'type'     => 'number',
                'css'      => 'max-width:100px;',
                'std'      => '0',  // WC < 2.0
                'default'  => '0',  // WC >= 2.0
                'desc'     => __( 'Applied to orders with 2 and more items', 'woocommerce' ),
            ];
        }
  
      $updated_settings[] = $section;
    }
  
    return $updated_settings;
}
add_filter( 'woocommerce_shipping_settings', 'add_shipping_rate_formula_setting' );

function woocommerce_package_rates( $rates, $package ) {
    $shipping_zone = WC_Shipping_Zones::get_zone_matching_package($package);
    $zone = $shipping_zone->get_zone_name();
    $baseRate = get_option('woocommerce_shipping_international_base_rate');
    $cartItemsCount = WC()->cart->cart_contents_count;

    if ($cartItemsCount < 2) return $rates;

    if ($baseRate && $zone === 'international') {
        foreach($rates as $key => $rate ) {
            $rates[$key]->cost = ($rates[$key]->cost+0) + ($baseRate+0);
        }
    } else {
        return $rates;
    }
    
    return $rates;
}
add_filter( 'woocommerce_package_rates', 'woocommerce_package_rates', 10, 2 );

function enqueue_remove_category_script() {
    if($_GET['post_type'] === 'product') {
        wp_enqueue_script('remove-category-btn', get_stylesheet_directory_uri(__FILE__) . '/assets/js/remove-category.js', ['jquery'], null, true);
    }
}
add_action('admin_enqueue_scripts', 'enqueue_remove_category_script');

function enqueue_custom_script() {
    // if( is_product() ) {
    //     global $product;

    //     wp_enqueue_script('custom-product-gallery', get_stylesheet_directory_uri(__FILE__) . '/assets/js/custom-product-gallery.js', ['jquery'], null, true);
    //     wp_localize_script( 'custom-product-gallery', 'currentProductMainImage', [
    //         'image_url' => get_the_post_thumbnail_url($product->id),
    //         'thumbnail' => get_the_post_thumbnail_url($product->id, 'woocommerce_gallery_thumbnail'),
    //     ]);
    // }

    wp_enqueue_script('custom-scripts', get_stylesheet_directory_uri(__FILE__) . '/assets/js/custom.js', ['jquery'], null, true);

    $shopType = $_GET['shop_type'] ?? '';
    if ($shopType === 'children') {
        ?>
        <style>
        .woof_container_pa_size {
            display: none !important;
        }
        </style>
        <?php
    }
}
add_action('wp_enqueue_scripts', 'enqueue_custom_script');

function add_shop_type_url_param() {
    
    global $wp_query;

    $current_url="https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $parsedUrl = parse_url($current_url);
    $cleanUrl = "https://".$_SERVER['HTTP_HOST'].$parsedUrl['path'];
    $pathInfo = pathinfo($parsedUrl['path']);
    if (isset($pathInfo['extension']) && !empty($pathInfo['extension'])) return;

    if ($cleanUrl === home_url('/') || is_product() && !$_SESSION['shop_type']
        || is_admin() || is_page(['designers','contact-us','about-us','cart','checkout', 'my-account', 'wishlist','terms-of-service','return-policy'])
        || (isDesignerProductCatParent($wp_query->query_vars['product_cat']) && !$_SESSION['shop_type'])) {
        $_SESSION['shop_type'] = null;
        return;
    };

    if (isset($_GET['shop_type']) && !empty($_GET['shop_type'])) {
        if ($_GET['shop_type'] === 'children' || $_GET['shop_type'] === 'women') {
            $_SESSION['shop_type'] = $_GET['shop_type'];
            return;
        }
    }

    $params = [];
    $url_parts = parse_url("https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
    parse_str($url_parts['query'], $params);

    if (isset($_SESSION['shop_type']) && !empty($_SESSION['shop_type']) 
        && ($_SESSION['shop_type'] === 'children' || $_SESSION['shop_type'] === 'women')) {
        $params['shop_type'] = $_SESSION['shop_type'];
    } else {
        wp_redirect(home_url('/'));
        exit;
    }

    foreach (array_keys($_GET) as $n) {
        $params[$n] = $_GET[$n];
    }    global $product;
    $local_designer_obj_id = get_terms ( array (
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'slug'       => 'local-designer',
        )
    );
    $local_designer_obj = get_terms( array(
        'child_of'   => $local_designer_obj_id[0]->term_id,
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        ) 
    );

    $url_parts['query'] = http_build_query($params);
    wp_redirect(http_build_url($url_parts));
    exit;
}
add_action('template_redirect', 'add_shop_type_url_param'); 

function true_function(){
    $all_ids = get_posts([
        'post_type'     => 'product',
        'numberposts'   => -1,
        'post_status'   => 'publish',
        'fields'        => 'ids',
        'tax_query'     => [
            [
              'taxonomy' => 'product_cat',
              'field'    => 'slug',
              'terms'    => 'uncategorized',
              'operator' => 'IN',
            ]
        ],
    ]);

    foreach ( $all_ids as $id ) {
        $terms = get_the_terms ( $id, 'product_cat' );
        if (count($terms >= 2)) {
            wp_remove_object_terms($id, 'uncategorized', 'product_cat');
            // wp_remove_object_terms($id, wc_get_product_tag_list($id), 'product_tag');
        }
    }

    $return = array(
        'message'   => 'Done!',
        'status'    => 200
    );

    wp_send_json($return);
}
add_action('wp_ajax_category_cleanup', 'true_function');

add_shortcode('local_children_designers', 'page_local_children_designers');
function page_local_children_designers(){
    global $product;
    $local_designer_obj_id = get_terms ( array (
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'slug'       => 'local-designer',
        )
    );
    $local_designer_obj = get_terms( array(
        'child_of'   => $local_designer_obj_id[0]->term_id,
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        ) 
    );
    foreach($local_designer_obj as $designer){
        $args = array(
                'tax_query' => array(
                    'relation' => 'AND', 
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => 'children'
                    ),
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => $designer->slug
                    ),
                ),
            'post_type' => 'product',
            'orderby' => 'title',
        );
        $loop = new WP_Query( $args );
        
        if(sizeof($loop->posts) != null){
            $first_letter = mb_strtoupper(substr($designer->name, 0, 1));
            $local_designer_list[$first_letter][] = $designer;
        }
    }

    $count = 0;
    ob_start();
    ?>
        <?php foreach ($local_designer_list as $key => $value): ?>
            <?php if ($count % 4 === 0): ?>
            <?php if ($count !== 0): ?>
                </div>
            <?php endif; ?>
            <div class="row">
        <?php endif; ?>
        <div class="col medium-3 text-center">
            <h3><?=$key?></h3>
            <ul class="single-designer">
                <?php foreach ($value as $solo_designer): ?>
                    <li><a href="/product-category/designers/local-designer/<?=$solo_designer->slug?>"><?=$solo_designer->name?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($count+1 === count($local_designer_list)): ?>
            </div>
        <?php endif; ?>
        <?php $count++; endforeach; ?>
        <style type="text/css">
        #tab_local-designers ul {
            list-style-type: none;
        }
        #tab_local-designers li {
           margin-bottom: 0;
        }
        .entry-content ul li, .entry-summary ul li, .col-inner ul li {
            margin-left: 0;
        }
        </style>
    <?php
    return ob_get_clean();
}

add_shortcode('international_children_designers', 'page_international_children_designers');
function page_international_children_designers(){
    global $product;
    $international_designer_obj_id =  get_terms ( array (
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'slug'       => 'international-designer',
        )
    );
    $international_designer_obj = get_terms( array(
        'child_of'   => $international_designer_obj_id[0]->term_id,
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        ) 
    );
    foreach($international_designer_obj as $designer){
        $args = array(
                'tax_query' => array(
                    'relation' => 'AND', 
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => 'children'
                    ),
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => $designer->slug
                    ),
                ),
            'post_type' => 'product',
            'orderby' => 'title',
        );
        $loop = new WP_Query( $args );
        
        if(sizeof($loop->posts) != null){
            $first_letter = mb_strtoupper(substr($designer->name, 0, 1));
            $international_designer_list[$first_letter][] = $designer;
        }
    }

    $count = 0;
    ob_start();
    ?>
        <?php foreach ($international_designer_list as $key => $value): ?>
            <?php if ($count % 4 === 0): ?>
            <?php if ($count !== 0): ?>
                </div>
            <?php endif; ?>
            <div class="row">
        <?php endif; ?>
        <div class="col medium-3 text-center">
            <h3><?=$key?></h3>
            <ul class="single-designer">
                <?php foreach ($value as $solo_designer): ?>
                    <li><a href="/product-category/designers/international-designer/<?=$solo_designer->slug?>"><?=$solo_designer->name?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($count+1 === count($international_designer_list)): ?>
            </div>
        <?php endif; ?>
        <?php $count++; endforeach; ?>
        <style type="text/css">
        #tab_international-designers ul {
            list-style-type: none;
        }
        #tab_international-designers li {
           margin-bottom: 0;
        }
        .entry-content ul li, .entry-summary ul li, .col-inner ul li {
            margin-left: 0;
        }
        </style>
    <?php
    return ob_get_clean();
}

add_shortcode('local_women_designers', 'page_local_women_designers');
function page_local_women_designers(){
    global $product;
    $local_designer_obj_id = get_terms ( array (
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'slug'       => 'local-designer',
        )
    );
    $local_designer_obj = get_terms( array(
        'child_of'   => $local_designer_obj_id[0]->term_id,
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        ) 
    );
    foreach($local_designer_obj as $designer){
        $args = array(
                'tax_query' => array(
                    'relation' => 'AND', 
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => 'women'
                    ),
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => $designer->slug
                    ),
                ),
            'post_type' => 'product',
            'orderby' => 'title',
        );
        $loop = new WP_Query( $args );
        
        if(sizeof($loop->posts) != null){
            $first_letter = mb_strtoupper(substr($designer->name, 0, 1));
            $local_designer_list[$first_letter][] = $designer;
        }
    }
    $count = 0;
    ob_start();
    ?>
        <?php foreach ($local_designer_list as $key => $value): ?>
            <?php if ($count % 4 === 0): ?>
            <?php if ($count !== 0): ?>
                </div>
            <?php endif; ?>
            <div class="row">
        <?php endif; ?>
        <div class="col medium-3 text-center">
            <h3><?=$key?></h3>
            <ul class="single-designer">
                <?php foreach ($value as $solo_designer): ?>
                    <li><a href="/product-category/designers/local-designer/<?=$solo_designer->slug?>"><?=$solo_designer->name?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($count+1 === count($local_designer_list)): ?>
            </div>
        <?php endif; ?>
        <?php $count++; endforeach; ?>
        <style type="text/css">
        #tab_local-designers ul {
            list-style-type: none;
        }
        #tab_local-designers li {
           margin-bottom: 0;
        }
        .entry-content ul li, .entry-summary ul li, .col-inner ul li {
            margin-left: 0;
        }
        </style>
    <?php
    return ob_get_clean();
}

add_shortcode('international_women_designers', 'page_international_women_designers');
function page_international_women_designers(){
    global $product;
    $international_designer_obj_id =  get_terms ( array (
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'slug'       => 'international-designer',
        )
    );
    $international_designer_obj = get_terms( array(
        'child_of'   => $international_designer_obj_id[0]->term_id,
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        ) 
    );
    foreach($international_designer_obj as $designer){
        $args = array(
                'tax_query' => array(
                    'relation' => 'AND', 
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => 'women'
                    ),
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => 'slug',
                        'terms' => $designer->slug
                    ),
                ),
            'post_type' => 'product',
            'orderby' => 'title',
        );
        $loop = new WP_Query( $args );
        
        if(sizeof($loop->posts) != null){
            $first_letter = mb_strtoupper(substr($designer->name, 0, 1));
            $international_designer_list[$first_letter][] = $designer;
        }
    }
    $count = 0;
    ob_start();
    ?>
        <?php foreach ($international_designer_list as $key => $value): ?>
            <?php if ($count % 4 === 0): ?>
            <?php if ($count !== 0): ?>
                </div>
            <?php endif; ?>
            <div class="row">
        <?php endif; ?>
        <div class="col medium-3 text-center">
            <h3><?=$key?></h3>
            <ul class="single-designer">
                <?php foreach ($value as $solo_designer): ?>
                    <li><a href="/product-category/designers/international-designer/<?=$solo_designer->slug?>"><?=$solo_designer->name?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php if ($count+1 === count($international_designer_list)): ?>
            </div>
        <?php endif; ?>
        <?php $count++; endforeach; ?>
        <style type="text/css">
        #tab_international-designers ul {
            list-style-type: none;
        }
        #tab_international-designers li {
           margin-bottom: 0;
        }
        .entry-content ul li, .entry-summary ul li, .col-inner ul li {
            margin-left: 0;
        }
        </style>
    <?php
    return ob_get_clean();
}

add_filter('woof_sort_terms_before_out', function($terms) {
    foreach ($terms as $key => $term) {
        $childsCount = count($term['childs']);
        if ($childsCount) {
            foreach ($term['childs'] as $childKey => $child) {
                if (!$child['count']) {
                    unset($terms[$key]['childs'][$childKey]);
                }
            }
        }
        $childsCount = count($terms[$key]['childs']);
        if (!$term['count'] && !$childsCount) {
            unset($terms[$key]);
        }
    }
    return $terms;
});

add_filter( 'woocommerce_order_button_html', function( $button_html ) {
    $beforeBtn = '<div class="woocommerce-privacy-policy-text">
                    <p>Estimated delivery time is within 3 to 7 days</p>
                </div>' . $button_html;
	return $beforeBtn;
});
 
function custom_remove_downloads_my_account( $items ) {
    unset($items['downloads']);
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'custom_remove_downloads_my_account', 999 );

function hide_shipping_title( $label ) {
	$pos = strpos( $label, ': ' );
	return substr( $label, ++$pos );
}
add_filter( 'woocommerce_cart_shipping_method_full_label', 'hide_shipping_title' );

function remove_shipping_label_thnakyou_page_cart($label, $method) {
    $shipping_label = '';
    return $shipping_label;
}
add_filter( 'woocommerce_order_shipping_to_display_shipped_via', 'remove_shipping_label_thnakyou_page_cart', 10, 2 );

function remove_billing_postcode_checkout( $fields ) {
  unset($fields['billing']['billing_postcode']);
  return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'remove_billing_postcode_checkout' );

add_filter( 'woocommerce_default_address_fields' , 'custom_override_default_address_fields' );
 
function custom_override_default_address_fields( $fields ) {
    $fields['state']['required'] = false;
    return $fields;
}

 
function logout_confirmation() {
    global $wp;
 
    if ( isset( $wp->query_vars['customer-logout'] ) ) {
        wp_redirect( str_replace( '&amp;', '&', wp_logout_url( wc_get_page_permalink( 'myaccount' ) ) ) );
        exit;
    }
}
add_action( 'template_redirect', 'logout_confirmation' );

function custom_size_charts(){
    if (is_product()){
        global $product;
        $prod_cats = $product->category_ids;

            foreach($prod_cats as $cat_id){
                $thumbnail_id = get_term_meta( $cat_id, 'thumbnail_id', true );
                if($thumbnail_id!=0){
                    $image = wp_get_attachment_url( $thumbnail_id, 'full' );
                }
            }
            if($image != null){
                ob_start()?>
                <p><a class="btn btn-primary btn-lg" href="#myModal1" data-toggle="modal">Size guide</a></p>
                <div id="myModal1" class="modal fade" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header"><button class="close" type="button" data-dismiss="modal">×</button></div>
                            <div class="modal-body"><a href="<?=$image?>"><img class="alignnone wp-image-1933 size-full" src="<?=$image?>" alt="" width="898" height="2067" /></a></div>
                            <div class="modal-footer"><button class="btn btn-default" type="button" data-dismiss="modal">Close</button></div>
                        </div>
                    </div>
                </div> 
            <?php
            return ob_get_clean();
            }
    }
}
add_shortcode('custom_size_charts', 'custom_size_charts');



function custom_fragrance_text(){
    global $product;
    $isHook = current_filter();
    $prod_cats = $product->category_ids;
    foreach($prod_cats as $value){
        if(get_the_category_by_ID($value) === 'Fragrance'){
            ob_start();?>
            <p class="fragrance-deliver-text"> * Fragrances are available only for Kuwait customers </p>
            <style>
                .fragrance-deliver-text{
                    color:red;
                }
            </style>
            <?php
            $ob = ob_get_clean();
            if ($isHook) {
                echo $ob;
                return;
            } else {
                return $ob;
            }
        }
    }
}
add_shortcode('custom_fragrance_text','custom_fragrance_text');
add_action('woocommerce_single_product_lightbox_summary', 'custom_fragrance_text', 31);

function symbol_currency_change($currency_symbol){
    if( ICL_LANGUAGE_CODE === 'en'){
        return ' KWD';
    }
    return $currency_symbol;
}
add_filter( 'woocommerce_currency_symbol', 'symbol_currency_change');




function change_city_to_dropdown( $fields ) {
    $fields['billing']['billing_city_text'] = $fields['billing']['billing_city'];
    $fields['billing']['billing_city_text']['class'][] = 'hidden';

    $city_args = array(
        'type' => 'select',
        'options' => array(
            "Abdullah Al-Mubarak"   => "Abdullah Al-Mubarak",
            "Abdullah as-Salim"     => "Abdullah as-Salim",
            "Abu Al Hasaniya"       => "Abu Al Hasaniya",
            "Abu Fteira"            => "Abu Fteira",
            "Abu Hulaifa"           => "Abu Hulaifa",
            "Adan"                  => "Adan",
            "Adiliya"               => "Adiliya",
            "Ahmadi"                => "Ahmadi",
            "Ali As-Salim"          => "Ali As-Salim",
            "Al-dhahar"             => "Al-dhahar",
            "Al Nahda / East Sulaibikhat"   => "Al Nahda / East Sulaibikhat",
            "Al Qurain"             => "Al Qurain",
            "Al-Qusour"             => "Al-Qusour",
            "Andalous"              => "Andalous",
            "Anjafa"                => "Anjafa",
            "Aqila"                 => "Aqila",
            "Ashbelya"              => "Ashbelya",
            "Bar Jahra"             => "Bar Jahra",
            "Bayān"                 => "Bayān",
            "Bidaa"                 => "Bidaa",
            "Bneidar"               => "Bneidar",
            "Bneid il-Gār"          => "Bneid il-Gār",
            "Da'iya"                => "Da'iya",
            "Dasma"                 => "Dasma",
            "Dasman"                => "Dasman",
            "Dhaher"                => "Dhaher",
            "Dhajeej"               => "Dhajeej",
            "Doha"                  => "Doha",
            "Fahad Al-Ahmad"        => "Fahad Al-Ahmad",
            "Fahaheel"              => "Fahaheel",
            "Faiha'"                => "Faiha'",
            "Farwaniya"             => "Farwaniya",
            "Fintas"                => "Fintas",
            "Funaitees"             => "Funaitees",
            "Granada"               => "Granada",
            "Hadiya"                => "Hadiya",
            "Hawally"               => "Hawally",
            "Hittin"                => "Hittin",
            "Jabriya"               => "Jabriya",
            "Jaber Al-Ahmad City"   => "Jaber Al-Ahmad City",
            "Jaber Al-Ali"          => "Jaber Al-Ali",
            "Jahra"                 => "Jahra",
            "Jibla"                 => "Jibla",
            "Jilei'a"               => "Jilei'a",
            "Jleeb Al-Shuyoukh"     => "Jleeb Al-Shuyoukh",
            "Kaifan"                => "Kaifan",
            "Khairan"               => "Khairan",
            "Khaldiya"              => "Khaldiya",
            "Mahbula"               => "Mahbula",
            "Maidan Hawalli"        => "Maidan Hawalli",
            "Mangaf"                => "Mangaf",
            "Mansouriya"            => "Mansouriya",
            "Miqwa"                 => "Miqwa",
            "Mirgab"                => "Mirgab",
            "Mishrif"               => "Mishrif",
            "Messila"                => "Messila",
            "Mubarak Al-Jabir"      => "Mubarak Al-Jabir",
            "Mubarak Al-Kabeer"     => "Mubarak Al-Kabeer",
            "Naeem"                 => "Naeem",
            "Nahdha"                => "Nahdha",
            "Nasseem"               => "Nasseem",
            "New Khairan City"      => "New Khairan City",
            "New Wafra"             => "New Wafra",
            "Nigra"                 => "Nigra",
            "Nuzha"                 => "Nuzha",
            "Omariya"               => "Omariya",
            "Oyoun"                 => "Oyoun",
            "Qadsiya"               => "Qadsiya",
            "Qasr"                  => "Qasr",
            "Qurtuba"               => "Qurtuba",
            "Rabiya"                => "Rabiya",
            "Rawda"                 => "Rawda",
            "Riggae"                => "Riggae",
            "Rihab"                 => "Rihab",
            "Riqqa"                 => "Riqqa",
            "Rumaithiya"            => "Rumaithiya",
            "Saad Al Abdullah City" => "Saad Al Abdullah City",
            "Sabah Al-Ahmad City"   => "Sabah Al-Ahmad City",
            "Sabah Al-Ahmad Nautical City"  => "Sabah Al-Ahmad Nautical City",
            "Sabah Al-Nasser"               => "Sabah Al-Nasser",
            "Sabah Al-Salem"        => "Sabah Al-Salem",
            "Sabahiya"              => "Sabahiya",
            "Sabhan"                => "Sabhan",
            "Salam"                 => "Salam",
            "Salhiya"               => "Salhiya",
            "Salmiya"               => "Salmiya",
            "Salwa"                 => "Salwa",
            "Sawabir"               => "Sawabir",
            "Sha'ab"                => "Sha'ab",
            "Shamiya"               => "Shamiya",
            "Sharq"                 => "Sharq",
            "Shuhada"               => "Shuhada",
            "Shuwaikh"              => "Shuwaikh",
            "Shuwaikh Industrial Area"  => "Shuwaikh Industrial Area",
            "Siddiq"                => "Siddiq",
            "South Doha / Qairawān" => "South Doha / Qairawān",
            "South Sabahiya"        => "South Sabahiya",
            "South Surra"           => "South Surra",
            "Sulaibikhat"           => "Sulaibikhat",
            "Sulaibiya"             => "Sulaibiya",
            "Surra"                 => "Surra",
            "Taima"                 => "Taima",
            "Wafra"                 => "Wafra",
            "Waha"                  => "Waha",
            "Yarmouk"               => "Yarmouk",
            "Zahra"                 => "Zahra",
            "Zoor"                  => "Zoor",
        ),
    );

    $fields['billing']['billing_city_select'] = $fields['billing']['billing_city'];
    $fields['billing']['billing_city_select']['type'] = $city_args['type'];
    $fields['billing']['billing_city_select']['options'] = $city_args['options'];
    $fields['billing']['billing_city_select']['class'][] = 'hidden';

    if (WC()->customer->get_shipping_country() === "KW"){        
        $fields['billing']['billing_city'] = $fields['billing']['billing_city_select'];
        $fields['billing']['billing_city']['class'] = [
            'form-row-wide',
            'address-field'
        ];
    }

    

	return $fields;

}
add_filter( 'woocommerce_checkout_fields', 'change_city_to_dropdown' );


function add_error_msg() {
    
    $payment_status = $_GET['cancel_order'] ?? '';
    if ($payment_status === 'true') {
        ?>
        <div class="isa_error text-center">
        <i class="fa fa-times-circle"></i>
            Thank you for shopping with us. However, the transaction has been declined. Please try again.
        </div>
        <style>
            .isa_info, .isa_success, .isa_warning, .isa_error {
            margin: 10px 0px;
            padding:12px;
            }
            .isa_error {
            color: #D8000C;
            background-color: #FFD2D2;
            }
            .isa_info i, .isa_success i, .isa_warning i, .isa_error i {
            margin:10px 22px;
            font-size:2em;
            vertical-align:middle;
            }
        </style>
        <?php
    }
}
add_action('woocommerce_before_cart_table', 'add_error_msg');

function filter_product_topic( $tax_query, $query ) {
    if(is_admin() || !is_product_category()) return $tax_query;

    $term = get_queried_object();
    $parent_cat_ids = get_ancestors( $term->term_taxonomy_id, 'product_cat' );
    $designers_cat =  get_terms ( array (
        'taxonomy'   => 'product_cat',
        'orderby'    => 'name',
        'order'      => 'asc',
        'hide_empty' => false,
        'slug'       => 'designers',
        )
    );

    if (!in_array($designers_cat[0]->term_taxonomy_id, $parent_cat_ids)) return $tax_query;

    $shop_type = $_GET['shop_type'] ?? '';
    $taxonomy = 'product_cat';

    switch ($shop_type) {
        case 'children':
            $tax_query[] = [
                'taxonomy'       => $taxonomy,
                'field'      => 'slug',
                'terms'      => [
                    'women'
                ],
                'operator'   => 'NOT IN'
            ];
            break;
        
        case 'women':
            $tax_query[] = [
                'taxonomy'       => $taxonomy,
                'field'      => 'slug',
                'terms'      => [
                    'children'
                ],
                'operator'   => 'NOT IN'
            ];
            break;

        default:
            break;
    }

    return $tax_query;
}
add_filter( 'woocommerce_product_query_tax_query', 'filter_product_topic', 10, 2 );

// add_filter( 'woocommerce_variation_is_active', 'grey_out_variations_out_of_stock', 10, 2 );
 
// function grey_out_variations_out_of_stock( $is_active, $variation ) {
//     if ( ! $variation->is_in_stock() ) return false;
//     return $is_active;
// }