<?php


// Add new Rewards4Earth log in fields in checkout form
add_action( 'woocommerce_after_order_notes', 'rwds4earth_identificator_user_login_form' );

function rwds4earth_identificator_user_login_form() {


    //echo '<form><h2>Rewards4Earth</h2><div id="r4earth_div">';
    woocommerce_form_field( 'r4earth_field_transaction_code', array(
        'type'          => 'number',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => __('Erth Points transaction code'),
        'placeholder'   => __('Enter your transaction code'),
	), WC()->checkout->get_value('r4earth_field_transaction_code'));


}

add_action('woocommerce_checkout_create_order', 'rwds4earth_on_custom_meta_data', 10 , 2);
function rwds4earth_on_custom_meta_data($order, $data){
    if(isset($_POST['r4earth_field_transaction_code'])){
        $order->update_meta_data('r4earth_field_transaction_code', sanitize_key($_POST['r4earth_field_transaction_code']));
    }
}

//On payment is completed
add_action( 'woocommerce_payment_complete', 'rwds4earth_on_payment_complete_triggered' );

function rwds4earth_on_payment_complete_triggered( $order_id ) {

    $error = false;

    //GET ORDER details
    $business_id = get_option('r4earth_biz_id');

    if($business_id == "" || $business_id == null) $error = true;

    if(!$error){
        $order = wc_get_order($order_id);
        if($order->get_meta('r4earth_field_transaction_code') !== null && $order->get_meta('r4earth_field_transaction_code') !== "" ){
            $transaction_code = $order->get_meta('r4earth_field_transaction_code');
            $currency = $order->get_currency();
            $amount = $order->get_subtotal();

            rwds4earth_send_payment_database($currency, $amount, $business_id, $transaction_code);
        }

    }

}



function rwds4earth_send_payment_database($currency, $amount, $business_id, $transaction_code){

    $url = "https://live.rewards4earth.com/api/v1/payments";
    $token = get_option('r4earth_biz_token');

    $body = array(
        "currency" => esc_sql($currency),
        "amount" => esc_sql($amount),
        "transaction_code" => sanitize_key($transaction_code),
        "business_id" => sanitize_key($business_id),
        "from" => sanitize_key("plugin")
    );


    $args = array(
        "method" => "POST",
        "headers" => array(
            "Authorization" => "Bearer ". sanitize_token($token),
            "Content-Type" => "application/json",
        ),
        "body" => json_encode($body),
    );

    $request = wp_remote_post($url, $args);

    if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
        error_log( print_r( $request, true ) );
    }
    $response = wp_remote_retrieve_body( $request );

}

 ?>
