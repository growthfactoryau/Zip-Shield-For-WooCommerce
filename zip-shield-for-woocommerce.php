<?php

/**
 * Plugin Name: Zip Shield for WooCommerce
 * Plugin URI: https://syedzeeshanali.com/zip-shield-for-woocommerce
 * Author: Syed Zeeshan Ali
 * Author URI: https://syedzeeshanali.com
 * Description: Restrict WooCommerce product purchases by ZIP/postal codes.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: zip-shield-for-woocommerce
 * Domain Path: /languages
 * Requires Plugins: woocommerce
 * WC requires at least: 8.0
 * WC tested up to: 10.0
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/*
|--------------------------------------------------------------------------
| Constants
|--------------------------------------------------------------------------
*/

define('ZS_VERSION', '1.0.0');
define('ZS_PLUGIN_FILE', __FILE__);
define('ZS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ZS_PLUGIN_URL', plugin_dir_url(__FILE__));

/*
|--------------------------------------------------------------------------
| HPOS Compatibility
|--------------------------------------------------------------------------
*/

add_action(
    'before_woocommerce_init',
    static function () {
        if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                __FILE__,
                true
            );
        }
    }
);

/*
|--------------------------------------------------------------------------
| Autoloader
|--------------------------------------------------------------------------
*/

spl_autoload_register(
    static function ($class) {

        if (strpos($class, 'ZipShield\\') !== 0) {
            return;
        }

        $class = str_replace('ZipShield\\', '', $class);
        $class = strtolower(str_replace('\\', '-', $class));

        $file = ZS_PLUGIN_PATH . 'includes/class-' . $class . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
);

/*
|--------------------------------------------------------------------------
| Boot Plugin
|--------------------------------------------------------------------------
*/

add_action(
    'plugins_loaded',
    static function () {

        if (! class_exists('WooCommerce')) {
            return;
        }

        \ZipShield\Plugin::instance();
    }
);

/*
|--------------------------------------------------------------------------
| Activation Hook
|--------------------------------------------------------------------------
*/

register_activation_hook(
    __FILE__,
    static function () {
        \ZipShield\Installer::install();
    }
);
