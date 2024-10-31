
<?php
/**
 * Plugin Name:       R4E Cart
 * Plugin URI:        https://www.rewards4earth.com/
 * Description:       R4E Cart is the official hook method to enable your E-Commerce online store to use the Rewards4Earth ™ Platform and reward your customers with Erth ™ points, every time they make a purchase at your store. At the same time, we are all helping to save the Planet with every transaction registered through R4E Cart.
 * Version:           1.1.3
 * Requires at least: 5.7.1
 * Requires PHP:      7.2
 * Author:            Rewards4Earth
 * Author URI:        https://www.rewards4earth.com/
 * License:           GPL v2
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       r4ecart
 * Domain Path:       /languages
 * **/

/***********************IMPORT ALL FILES**********************/
//Display on checkout


require_once plugin_dir_path(__FILE__) . 'admin/adminpage.php';
require_once plugin_dir_path(__FILE__) . 'public/checkoutpage.php';

add_option('r4earth_biz_id', '');
add_option('r4earth_biz_email', '');
add_option('r4earth_biz_token', '');



//Action when plugin is deactivated
register_deactivation_hook( __FILE__, 'rwds4earth_delete_business_data_options' );

function rwds4earth_delete_business_data_options(){
    delete_option('r4earth_biz_id');
    delete_option('r4earth_biz_email');
    delete_option('r4earth_biz_token');
}


?>
