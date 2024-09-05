<?php

/**
 * Plugin Name: Giyon Woo Shipping
 * Plugin URI: https://github.com/susantohenri/
 * Description: Custom WooCommerce Shipping Plugin for Giyon.
 * Version: 1.0.0
 * Author: Henrisusanto
 * Author URI: https://github.com/susantohenri/
 * Text Domain: giyon-woo-shipping
 * Domain Path: /i18n/languages/
 * Requires at least: 6.5
 * Requires PHP: 7.4
 *
 */

define('GIYON_VOLUME_TO_SHIPPING_CLASS', [
    'SL' => 400,
    'LPL' => 800,
    'LPP' => 1200,
    'BOX 60' => 5000,
    'BOX 80' => 11900,
    'BOX 100' => 24300,
    'BOX 120' => 49500,
    'BOX 140' => 80500,
    'BOX 160' => 122400,
    'BOX 170' => 153600
]);

add_action('woocommerce_shipping_init', function () {
    if (! class_exists('Giyon_Shipping_Method')) {
        class Giyon_Shipping_Method extends WC_Shipping_Method
        {

            public function __construct($instance_id = 0)
            {
                $this->id                 = 'giyon_shipping';
                $this->instance_id        = absint($instance_id);
                $this->method_title       = __('Giyon', 'text-domain');
                $this->method_description = __('Giyon Shipping for WooCommerce', 'text-domain');
                $this->title              = __('Giyon Shipping', 'text-domain');
                $this->supports           = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );

                $this->init();
            }


            public function init()
            {
                $this->init_form_fields();
                $this->init_instance_settings();
                $this->enabled = $this->get_option('enabled');
                return __('Giyon Shipping', 'text-domain');
                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            public function init_instance_settings()
            {
                $this->instance_form_fields = array(
                    'enabled'    => array(
                        'title'   => __('Enable/Disable'),
                        'type'    => 'checkbox',
                        'label'   => __('Enable this shipping method'),
                        'default' => 'yes',
                    ),
                    'title'      => array(
                        'title'       => __('Method Title'),
                        'type'        => 'text',
                        'description' => __('This controls the title which the user sees during checkout.'),
                        'default'     => __('Giyon Shipping'),
                        'desc_tip'    => true
                    ),
                    'tax_status' => array(
                        'title'   => __('Tax status', 'woocommerce'),
                        'type'    => 'select',
                        'class'   => 'wc-enhanced-select',
                        'default' => 'taxable',
                        'options' => array(
                            'taxable' => __('Taxable', 'woocommerce'),
                            'none'    => _x('None', 'Tax status', 'woocommerce'),
                        ),
                    ),
                    'cost'       => array(
                        'title'       => __('Cost', 'woocommerce'),
                        'type'        => 'text',
                        'placeholder' => '0',
                        'description' => __('Optional cost for Giyon.', 'woocommerce'),
                        'default'     => '',
                        'desc_tip'    => true,
                    ),
                );
            }

            public function calculate_shipping($package = array())
            {
                // $package->destination->state JP27 urut dropdown
                $giyon_product_ids = giyon_cart_to_product_ids($package);
                $shipping_classes = array_map(function ($product_id) {
                    return giyon_product_id_to_shipping_class($product_id);
                }, $giyon_product_ids);
                $shipping_class = giyon_shipping_classes_to_shipping_class($shipping_classes);

                if (giyon_any_free_shipping_class($shipping_classes)) $cost = 0;
                else {
                    $cost = 0;
                    $total_volume = 0;
                    foreach ($giyon_product_ids as $gpid) {
                        $total_volume += giyon_product_id_to_volume($gpid);
                    }
                }

                $this->title = $shipping_class;
                if ('BOX' == $this->title) $this->title = 'Yu Pakku';
                $this->add_rate(array(
                    'id'    => $this->id,
                    'label' => $this->title,
                    'cost'  => $cost,
                ));
            }
        }
    }
});

add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['giyon_shipping'] = 'Giyon_Shipping_Method';
    return $methods;
});

function giyon_cart_to_product_ids($package)
{
    return array_values(array_map(function ($content) {
        return $content['product_id'];
    }, $package['contents']));
}

function giyon_product_id_to_shipping_class($product_id)
{
    global $wpdb;
    return $wpdb->get_var("
        SELECT
            {$wpdb->prefix}terms.name
        FROM {$wpdb->prefix}posts
        LEFT JOIN {$wpdb->prefix}term_relationships ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id
        LEFT JOIN {$wpdb->prefix}term_taxonomy ON {$wpdb->prefix}term_relationships.term_taxonomy_id = {$wpdb->prefix}term_taxonomy.term_taxonomy_id
        LEFT JOIN {$wpdb->prefix}terms ON {$wpdb->prefix}terms.term_id = {$wpdb->prefix}term_taxonomy.term_id
        WHERE {$wpdb->prefix}posts.ID = {$product_id}
        AND {$wpdb->prefix}term_taxonomy.taxonomy = 'product_shipping_class'
    ");
}

function giyon_shipping_classes_to_shipping_class($shipping_classes)
{
    if (in_array('BOX', $shipping_classes)) return 'BOX';
    else if (0 < count(array_intersect(['LPP', 'LPPF'], $shipping_classes))) return 'Letter Pack Plus';
    else if (0 < count(array_intersect(['LPL', 'LPLF'], $shipping_classes))) return 'Letter Pack Light';
    else return 'Smart Letter';
}

function giyon_any_free_shipping_class($shipping_classes)
{
    return 0 < count(array_values(array_filter($shipping_classes, function ($shipping_class) {
        return -1 < strpos($shipping_class, 'F');
    })));
}

function giyon_product_id_to_volume($product_id)
{
    global $wpdb;
    $volume = 1;
    foreach (
        $wpdb->get_results("
        SELECT meta_value
        FROM {$wpdb->prefix}postmeta
        WHERE post_id = {$product_id}
        AND meta_key IN ('_length', '_width', '_height')
    ") as $dimension
    ) $volume *= $dimension->meta_value;
    return $volume;
}

function giyon_volume_to_shipping_class($volume)
{
    $shipping_class = '';
    $limits = array_reverse(GIYON_VOLUME_TO_SHIPPING_CLASS);
    foreach ($limits as $class => $value) {
        if ($volume <= $value) $shipping_class = $class;
    }
    return $shipping_class;
}
