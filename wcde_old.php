<?php
/**
 * Plugin Name: Wolf Custom Brand Selector OLD
 * Plugin URI: https://choclomedia.com/
 * Description: An eCommerce toolkit that helps you add a brand selector to your products.
 * Version: 1.0.0
 * Author: Jose Rodriguez
 * Author URI: https://choclomedia.com
 *
 * @package Wolf Custom Brand Selector
 */

defined( 'ABSPATH' ) || exit;

function wc_insert_js(){

    $args = array(
        'hide_empty' => false,
        'parent' => 0
    );
    $parent_term = get_terms('wc_brand', $args);

    wp_enqueue_style('wc_styles', plugins_url( '/css/wc-styles.css', __FILE__ ));

    wp_register_script('wc_script', plugins_url( '/js/wcde-scripts.js', __FILE__ ), array('jquery'), '1', true );
    wp_enqueue_script('wc_script');
    wp_localize_script(
        'wc_script',
        'wc_vars',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'), 
            'parent_terms' => $parent_term )
    );

}
add_action('wp_enqueue_scripts', 'wc_insert_js');

// Register Custom Taxonomy
function wc_bikes_tax() {

    $labels = array(
        'name'                       => _x( 'Brands & Models', 'Taxonomy General Name', 'wcde' ),
        'singular_name'              => _x( 'Brand & Model', 'Taxonomy Singular Name', 'wcde' ),
        'menu_name'                  => __( 'Brand & Model', 'wcde' ),
        'all_items'                  => __( 'All Brands & Models', 'wcde' ),
        'parent_item'                => __( 'Parent Item', 'wcde' ),
        'parent_item_colon'          => __( 'Parent Item:', 'wcde' ),
        'new_item_name'              => __( 'New Item Name', 'wcde' ),
        'add_new_item'               => __( 'Add New Item', 'wcde' ),
        'edit_item'                  => __( 'Edit Item', 'wcde' ),
        'update_item'                => __( 'Update Item', 'wcde' ),
        'view_item'                  => __( 'View Item', 'wcde' ),
        'separate_items_with_commas' => __( 'Separate items with commas', 'wcde' ),
        'add_or_remove_items'        => __( 'Add or remove items', 'wcde' ),
        'choose_from_most_used'      => __( 'Choose from the most used', 'wcde' ),
        'popular_items'              => __( 'Popular Items', 'wcde' ),
        'search_items'               => __( 'Search Items', 'wcde' ),
        'not_found'                  => __( 'Not Found', 'wcde' ),
        'no_terms'                   => __( 'No items', 'wcde' ),
        'items_list'                 => __( 'Items list', 'wcde' ),
        'items_list_navigation'      => __( 'Items list navigation', 'wcde' ),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'query_var'                  => 'wcde_brands',
        'show_in_rest'               => true,
        'rest_base'                  => 'wcde_brands',
    );
    register_taxonomy( 'wc_brand', array( 'product' ), $args );

}
add_action( 'init', 'wc_bikes_tax', 0 );

function mostraralgo($elemento){

    $html = '';

    $args = array(
        'hide_empty' => false,
        'parent' => 0
    );
    $parent_term = get_terms('wc_brand', $args);

    $html .= '<div id="wc_single_dropdown_brands" class="wc-single-dropdown">';
    $html .= '<select id="wc_single_dropdown_brand" name="wc-single-dropdown wc-single-dropdown-brand">';
    $html .= '<option>Select Brand</option>';
    foreach ( $parent_term as $term ){
        $html .= '<option value="' . $term->term_id . '">' . $term->name . '</option>';
    };
    $html .= '</select>';
    $html .= '</div>';
    return $html;
}
add_shortcode('show-brand-selector', 'mostraralgo');


function wc_get_child_terms(){
    $get = $_GET;
    $args = array(
        'hide_empty' => false,
        'parent' => $get['parentID']
    );
    $parent_term = get_terms('wc_brand', $args);
    echo json_encode($parent_term);
    wp_die();
}
add_action('wp_ajax_wc_get_child_terms', 'wc_get_child_terms');
add_action('wp_ajax_nopriv_wc_get_child_terms', 'wc_get_child_terms');

// Adding Meta container admin shop_order pages
//add_action( 'add_meta_boxes', 'wc_add_meta_boxes' );
/*
if ( ! function_exists( 'wc_add_meta_boxes' ) )
{
    function wc_add_meta_boxes()
    {
        add_meta_box( 'wc_brnad_model_info', __('Brand & Model','woocommerce'), 'wc_add_metabox_for_brand_model', 'shop_order', 'side', 'core' );
    }
}

// Adding Meta field in the meta container admin shop_order pages
if ( ! function_exists( 'wc_add_metabox_for_brand_model' ) )
{
    function wc_add_metabox_for_brand_model()
    {
        global $post;

        $meta_field_data = get_post_meta( $post->ID, 'wc_brand_model', true ) ? get_post_meta( $post->ID, 'wc_brand_model', true ) : '';

        echo '<input type="hidden" name="wc_other_meta_field_nonce" value="' . wp_create_nonce() . '">
        <p style="border-bottom:solid 1px #eee;padding-bottom:13px;">
            <input type="text" style="width:250px;";" name="wc_brand_model" placeholder="' . $meta_field_data . '" value="' . $meta_field_data . '" readonly></p>';
    }
}
*/

// Save the data of the Meta field
add_action( 'save_post', 'wc_save_wc_order_other_fields', 10, 1 );
if ( ! function_exists( 'wc_save_wc_order_other_fields' ) )
{

    function wc_save_wc_order_other_fields( $post_id ) {

        // We need to verify this with the proper authorization (security stuff).

        // Check if our nonce is set.
        if ( ! isset( $_POST[ 'wc_other_meta_field_nonce' ] ) ) {
            return $post_id;
        }
        $nonce = $_REQUEST[ 'wc_other_meta_field_nonce' ];

        //Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce ) ) {
            return $post_id;
        }

        // If this is an autosave, our form has not been submitted, so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }

        // Check the user's permissions.
        if ( 'page' == $_POST[ 'post_type' ] ) {

            if ( ! current_user_can( 'edit_page', $post_id ) ) {
                return $post_id;
            }
        } else {

            if ( ! current_user_can( 'edit_post', $post_id ) ) {
                return $post_id;
            }
        }
        // --- Its safe for us to save the data ! --- //

        // Sanitize user input  and update the meta field in the database.
        update_post_meta( $post_id, 'wc_brand_model', $_POST[ 'wc_brand_model' ] );
        //update_post_meta( $post_id, 'wc_side_patches', $_POST[ 'wc_side_patches' ] );
        update_post_meta( $post_id, 'wc_front_patches', $_POST[ 'wc_front_patches' ] );
        update_post_meta( $post_id, 'wc_additional_notes', $_POST[ 'wc_additional_notes' ] );
    }
}


// Add the field to the product
/*
add_action('woocommerce_before_add_to_cart_button', 'my_custom_product_field');
function my_custom_product_field() {
    $html = '';

    $args = array(
        'hide_empty' => false,
        'parent' => 0
    );
    $parent_term = get_terms('wc_brand', $args);

    $html .= '<div id="wc_single_dropdown_brands" class="wc-single-dropdown">';
    $html .= '<select id="wc_single_dropdown_brand" name="wc_single_dropdown_brand" class="wc-single-dropdown wc-single-dropdown-brand" required>';
    $html .= '<option>Select Brand</option>';
    foreach ( $parent_term as $term ){
        $html .= '<option value="' . $term->term_id . '">' . $term->name . '</option>';
    };
    $html .= '</select>';
    $html .= '</div>';
    echo $html;
}
*/

// Store custom field
add_filter( 'woocommerce_add_cart_item_data', 'save_my_custom_product_field', 10, 2 );
function save_my_custom_product_field( $cart_item_data, $product_id ) {
    if( isset( $_REQUEST['wc_single_dropdown_brand'] ) and isset( $_REQUEST['wc_single_dropdown_model'] ) ) {

        $brand = $model = '';
        $add_prices = 0;
        $brand = get_term_by('term_id', $_REQUEST['wc_single_dropdown_brand'], 'wc_brand');
        $model = get_term_by('term_id', $_REQUEST['wc_single_dropdown_model'], 'wc_brand');
        $brand_model = $brand->name . ' - ' . $model->name;
        $cart_item_data[ 'brand_model' ] = $brand_model;

        //$cart_item_data[ 'side_patches' ] = $_REQUEST['wc_single_side_patches'];
        $cart_item_data[ 'front_patches' ] = $_REQUEST['wc_single_front_patches'];
        $cart_item_data[ 'additional_notes' ] = $_REQUEST['wc_single_additional_notes'];
        
        $product = wc_get_product( $product_id );
        $add_prices = $product->get_price();
        
        //if( $_REQUEST['wc_single_side_patches'] > 0 ){ $add_prices = $add_prices + 10; }
        if( $_REQUEST['wc_single_front_patches'] > 0 ){ $add_prices = $add_prices + 10; }
        $cart_item_data[ 'wc_add_prices' ] = $add_prices;

        // below statement make sure every add to cart action as unique line item
        $cart_item_data['unique_key'] = md5( microtime().rand() );
        WC()->session->set( 'my_order_brand_model', $brand_model );
        //WC()->session->set( 'my_order_side_patches', $_REQUEST['wc_single_side_patches'] );
        WC()->session->set( 'my_order_front_patches', $_REQUEST['wc_single_front_patches'] );
        WC()->session->set( 'my_order_additional_notes', $_REQUEST['wc_single_additional_notes'] );
    }
    return $cart_item_data;
}

// Add a hidden field with the correct value to the checkout
add_action( 'woocommerce_after_order_notes', 'my_custom_checkout_field' );
function my_custom_checkout_field( $checkout ) {
    $brand_model = WC()->session->get( 'my_order_brand_model' );
    //$side_patches = WC()->session->get( 'my_order_side_patches' );
    $front_patches = WC()->session->get( 'my_order_front_patches' );
    $additional_notes = WC()->session->get( 'my_order_additional_notes' );
    echo '<div id="my_custom_checkout_field">
            <input type="hidden" class="input-hidden" name="wc_brand_model" id="wc_brand_model" value="' . $brand_model . '">
            <input type="hidden" class="input-hidden" name="wc_front_patches" id="wc_front_patches" value="' . $front_patches . '">
            <input type="hidden" class="input-hidden" name="wc_additional_notes" id="wc_additional_notes" value="' . $additional_notes . '">
    </div>';
}

// Save the order meta with hidden field value
add_action( 'woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta' );
function my_custom_checkout_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['wc_brand_model'] ) ) {
        update_post_meta( $order_id, 'wc_brand_model', $_POST['wc_brand_model'] );
    }
    /*
    if ( ! empty( $_POST['wc_side_patches'] ) ) {
        update_post_meta( $order_id, 'wc_side_patches', $_POST['wc_side_patches'] );
    }
    */
    if ( ! empty( $_POST['wc_front_patches'] ) ) {
        update_post_meta( $order_id, 'wc_front_patches', $_POST['wc_front_patches'] );
    }
    if ( ! empty( $_POST['wc_additional_notes'] ) ) {
        update_post_meta( $order_id, 'wc_additional_notes', $_POST['wc_additional_notes'] );
    }
}

// Display field value on the order edit page (not in custom fields metabox)
//add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );
add_action( 'woocommerce_admin_order_data_after_order_details', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );
function my_custom_checkout_field_display_admin_order_meta($order){
    $wc_brand_model = get_post_meta( $order->id, 'wc_brand_model', true );
    $wc_side_patches = get_post_meta( $order->id, 'wc_side_patches', true );
    $wc_front_patches = get_post_meta( $order->id, 'wc_front_patches', true );
    $wc_additional_notes = get_post_meta( $order->id, 'wc_additional_notes', true );

    echo '<h3 style="margin-top: 18px; display: inline-block;">Wolf Custom Seat information</h3>';

    if ( ! empty( $wc_brand_model ) ) {
        echo '<p style="line-height: 1;"><strong>'. __("Brand & Model", "woocommerce").':</strong> ' . get_post_meta( $order->id, 'wc_brand_model', true ) . '</p>';
    }
    /*
    if ( ! empty( $wc_side_patches ) ) {
        echo '<p><strong>'. __("Side patches", "woocommerce").':</strong> ' . get_post_meta( $order->id, 'wc_side_patches', true ) . '</p>';
    }
    */
    if ( ! empty( $wc_front_patches ) ) {
        echo '<p style="line-height: 1;"><strong>'. __("Front patches", "woocommerce").':</strong> ' . get_post_meta( $order->id, 'wc_front_patches', true ) . '</p>';
    }
    if ( ! empty( $wc_additional_notes ) ) {
        echo '<p style="line-height: 1;"><strong>'. __("Additional Notes", "woocommerce").':</strong> ' . get_post_meta( $order->id, 'wc_additional_notes', true ) . '</p>';
    }
}

// Cart page: Display dropdown value after the cart item name

add_filter( 'woocommerce_cart_item_name', 'display_dropdown_value_after_cart_item_name', 10, 3 );
function display_dropdown_value_after_cart_item_name( $name, $cart_item, $cart_item_key ) {
    if( is_cart() && isset($cart_item['brand_model']) ) {
        $name .= '<p style="line-height: 1;">'.__("Brand & Model:") . ' ' . esc_html($cart_item['brand_model']) . '</p>';
        //$name .= '<p style="line-height: 1;">'.__("Side patches:") . ' ' . esc_html($cart_item['side_patches']) . '</p>';
        $name .= '<p style="line-height: 1;">'.__("Front patches:") . ' ' . esc_html($cart_item['front_patches']) . '</p>';
        $name .= '<p style="line-height: 1;">'.__("Additional notes:") . ' ' . esc_html($cart_item['additional_notes']) . '</p>';
    }
    /*
    if( is_cart() && isset($cart_item_data[ 'side_patches' ]) ){
    }

    if( is_cart() && isset($cart_item_data[ 'front_patches' ]) ){
    }

    if( is_cart() && isset($cart_item_data[ 'additional_notes' ]) ){
    }
    */
    return $name;
}

// Checkout page: Display dropdown value after the cart item name
add_filter( 'woocommerce_checkout_cart_item_quantity', 'display_dropdown_value_after_cart_item_quantity', 10, 3 );
function display_dropdown_value_after_cart_item_quantity( $item_qty, $cart_item, $cart_item_key ) {
    if( isset($cart_item['brand_model']) ) {
        $item_qty .= '<p>'.__("Brand & Model:") . ' ' . esc_html($cart_item['brand_model']) . '</p>';
    }
    return $item_qty;
}


function add_the_date_validation( $passed ) { 
    if ( empty( $_REQUEST['wc_single_dropdown_brand'] )) {
        wc_add_notice( __( 'Please select a brand.', 'woocommerce' ), 'error' );
        $passed = false;
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'add_the_date_validation', 10, 5 );  


/**
 * Add item price to regular price
 */
function before_calculate_totals( $cart_obj ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }
    // Iterate through each cart item
    foreach( $cart_obj->get_cart() as $key=>$value ) {
        if( isset( $value['wc_add_prices'] ) ) {
            $price = $value['wc_add_prices'];
            $value['data']->set_price( ( $price ) );
        }
    }
}
add_action( 'woocommerce_before_calculate_totals', 'before_calculate_totals', 10, 1 );


/**
 * Order Admin custom columns
 */
/*
add_filter( 'manage_edit-shop_order_columns', 'bbloomer_add_new_order_admin_list_column' );

function bbloomer_add_new_order_admin_list_column( $columns ) {
    $columns['billing_country'] = 'Country';
    return $columns;
}

add_action( 'manage_shop_order_posts_custom_column', 'bbloomer_add_new_order_admin_list_column_content' );

function bbloomer_add_new_order_admin_list_column_content( $column ) {

    global $post;

    if ( 'billing_country' === $column ) {

        $order = wc_get_order( $post->ID );
        echo $order->get_billing_country();

    }
}

*/

/*

// Display a custom dropdown in single product pages before add_to_cart button
add_action( 'woocommerce_before_add_to_cart_button', 'display_dropdown_in_ends');
function display_dropdown_in_ends() {
    $html = '';

    $args = array(
        'hide_empty' => false,
        'parent' => 0
    );
    $parent_term = get_terms('wc_brand', $args);

    $html .= '<div id="wc_single_dropdown_brands" class="wc-single-dropdown">';
    $html .= '<select id="wc_single_dropdown_brand" name="wc_single_dropdown_brand" class="wc-single-dropdown wc-single-dropdown-brand">';
    $html .= '<option>Select Brand</option>';
    foreach ( $parent_term as $term ){
        $html .= '<option value="' . $term->term_id . '">' . $term->name . '</option>';
    };
    $html .= '</select>';
    $html .= '</div>';
    echo $html;
}

// Add dropdown value as custom cart item data, on add to cart
add_filter( 'woocommerce_add_cart_item_data', 'add_dropdown_value_to_cart_item_data', 10, 4 );
function add_dropdown_value_to_cart_item_data( $cart_item_data, $product_id, $variation_id) {
    if( isset($_POST['wc_single_dropdown_brand']) && ! empty($_POST['wc_single_dropdown_brand']) ) {
        // Add the dropdown value as custom cart item data
        $cart_item_data['wc_brand'] = esc_attr($_POST['wc_single_dropdown_brand']);
        $cart_item_data['unique_key'] = md5(microtime().rand()); // Make each added item unique
    }
    return $cart_item_data;
}

// Cart page: Display dropdown value after the cart item name
add_filter( 'woocommerce_cart_item_name', 'display_dropdown_value_after_cart_item_name', 10, 3 );
function display_dropdown_value_after_cart_item_name( $name, $cart_item, $cart_item_key ) {
    if( is_cart() && isset($cart_item['wc_brand']) ) {
        $name .= '<p>'.__("I ene enden:") . ' ' . esc_html($cart_item['wc_brand']) . '</p>';
    }
    return $name;
}

// Checkout page: Display dropdown value after the cart item name
add_filter( 'woocommerce_checkout_cart_item_quantity', 'display_dropdown_value_after_cart_item_quantity', 10, 3 );
function display_dropdown_value_after_cart_item_quantity( $item_qty, $cart_item, $cart_item_key ) {
    if( isset($cart_item['one_end']) ) {
        $item_qty .= '<p>'.__("I ene enden:") . ' ' . esc_html($cart_item['one_end']) . '</p>';
    }
    return $item_qty;
}



/*

add_action( 'woocommerce_admin_order_data_after_order_details', 'misha_editable_order_meta_general' );

function misha_editable_order_meta_general( $order ){  ?>

		<br class="clear" />
		<h4>Seat Brand and Model</h4>
		<?php $brand_model = get_post_meta( $order->id, 'wc_brand_model', true ); ?>
		<div class="edit_address"><?php

			woocommerce_wp_text_input( array(
				'id' => 'wc_brand_model',
				'label' => 'Recipient name:',
				'value' => $gift_name,
				'wrapper_class' => 'form-field-wide'
			) );

?></div>


<?php }

add_action( 'woocommerce_process_shop_order_meta', 'misha_save_general_details' );
function misha_save_general_details( $ord_id ){
	update_post_meta( $ord_id, 'wc_brand_model', wc_clean( $_POST[ 'wc_brand_model' ] ) );
}

*/