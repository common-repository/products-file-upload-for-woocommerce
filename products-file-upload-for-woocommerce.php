<?php
/*
Plugin Name: Product File Upload for WooCommerce
Description: Allowing customers to upload files on the WooCommerce product page.
Author: add-ons.org
Version: 2.2.0
Requires Plugins: woocommerce
Author URI: https://add-ons.org
Plugin URI: https://wordpress.org/plugins/products-file-upload-for-woocommerce
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
define( 'SUPERADDONS_WOO_PRODUCTS_UPLOADS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SUPERADDONS_WOO_PRODUCTS_UPLOADS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
class Superaddon_Products_Upload_Init {
    function __construct(){
        include SUPERADDONS_WOO_PRODUCTS_UPLOADS_PLUGIN_PATH."frontend/index.php";
        include SUPERADDONS_WOO_PRODUCTS_UPLOADS_PLUGIN_PATH."backend/index.php";
        include SUPERADDONS_WOO_PRODUCTS_UPLOADS_PLUGIN_PATH."superaddons/check_purchase_code.php";
        new Superaddons_Check_Purchase_Code( 
            array(
                "plugin" => "products-file-upload-for-woocommerce/products-file-upload-for-woocommerce.php",
                "id"=>"3529",
                "pro"=>"https://add-ons.org/plugin/file-upload-on-woocommerce-product-page/",
                "plugin_name"=> "Product File Upload for WooCommerce",
                "document"=>"https://add-ons.org/document-file-upload-for-woocommerce-product-page/"
            )
        );
    }
}
new Superaddon_Products_Upload_Init;