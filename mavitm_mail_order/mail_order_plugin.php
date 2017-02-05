<?php
/*
Plugin Name: Mail Order Form - WooCommerce Gateway
Plugin URI: http://www.mavitm.com
Description: Mail order formu ile kredi kartı bilgileri almak için WooCommerce Eklentisi.
Version: 1.0.1
Author: MaviTm (Ayhan Eraslan)
Author URI: http://www.mavitm.com
*/
 

add_action( 'plugins_loaded', 'init_mavitm_mail_order_gateway', 0 );
function init_mavitm_mail_order_gateway() {
   
    if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
     
    include_once( 'WC_Gateway_mavitm_mail_order.php' );
 
    function add_mavitm_mail_order_gateway( $methods ) {
        $methods[] = 'wc_gateway_mavitm_mail_order';
        return $methods;
    }
    add_filter( 'woocommerce_payment_gateways', 'add_mavitm_mail_order_gateway' );


    function order_meta_customized_display( $item_id, $item, $product ){
        /** ITEMS INFO ADDED
        $all_meta_data = get_metadata( 'order_item', $item_id, "", "");
        $useless = array(
            "_qty","_tax_class","_variation_id","_product_id","_line_subtotal","_line_total","_line_subtotal_tax"
        );// Add key values that you want to ignore

        $customized_array= array();
        foreach($all_meta_data as $data_meta_key => $value)
        {
            if(!in_array($data_meta_key, $useless)){
                $newKey = ucwords(str_replace('_'," ",$data_meta_key ));//To remove underscrore and capitalize
                $customized_array[$newKey]=ucwords(str_replace('_'," ",$value[0])); // Pushing each value to the new array
            }
        }
        ?>

        <?php if (!empty($customized_array)) {?>
            <div class="order_data_column" style="float: left; width: 50%; padding: 0 5px;">
                <h4><?php _e( 'Customized Values' ); ?></h4>
                <?php
                foreach($customized_array as $data_meta_key => $value)
                {
                    echo '<p><span style="display:inline-block; width:100px;">' . __( $data_meta_key ) . '</span><span>:&nbsp;' . $value . '</span></p>';
                }
                ?>
            </div>
        <?php }
        /**/
        ?>
    <?php }
    add_action( 'woocommerce_after_order_itemmeta', 'order_meta_customized_display',10, 3 );


    /*
    function my_custom_checkout_field_process() {
        // Check if set, if its not set add an error.
        if ( ! $_POST['billing_phone_new'] )
            wc_add_notice( __( 'Phone 2 is compulsory. Please enter a value' ), 'error' );
    }
    add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');

    function my_custom_checkout_field_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['billing_phone_new'] ) ) {
            update_post_meta( $order_id, 'billing_phone_new', sanitize_text_field( $_POST['billing_phone_new'] ) );
        }
    }
    add_action( 'woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta' );
    */

    /* EXTRA CONTACT
    function my_custom_checkout_field_display_admin_order_meta($order){
        echo '<p><strong>'.__('Phone 2').':</strong> <br/>' . get_post_meta( $order->id, 'billing_phone_new', true ) . '</p>';
    }
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );
    */

    function mavitm_mail_order_view_in_admin( $order ){
        if(!is_admin()){
            return false;
        }
        $object = new WC_Gateway_mavitm_mail_order();
        $object->kartBilgisiGoster($order->id);
    }
    add_action( 'woocommerce_admin_order_data_after_order_details', 'mavitm_mail_order_view_in_admin' );

}
 
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'mavitm_mail_order_action_links' );
function mavitm_mail_order_action_links( $links ) {
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout' ) . '">' . __( 'Ayarlar', 'mavitm_mail_order' ) . '</a>',
    );
     return array_merge( $plugin_links, $links );    
}
