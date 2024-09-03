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
                $this->title   = __('Giyon Shipping', 'text-domain');
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
                $giyon_product_ids = [];
                foreach ($package['contents'] as $content) $giyon_product_ids[] = $content['product_id'];
                $giyon_product_ids = implode(',', $giyon_product_ids);

                global $wpdb;
                $giyon_shipping_classes = [];
                foreach (
                    $wpdb->get_results("
                    SELECT
                        {$wpdb->prefix}terms.name
                    FROM {$wpdb->prefix}posts
                    LEFT JOIN {$wpdb->prefix}term_relationships ON {$wpdb->prefix}posts.ID = {$wpdb->prefix}term_relationships.object_id
                    LEFT JOIN {$wpdb->prefix}term_taxonomy ON {$wpdb->prefix}term_relationships.term_taxonomy_id = {$wpdb->prefix}term_taxonomy.term_taxonomy_id
                    LEFT JOIN {$wpdb->prefix}terms ON {$wpdb->prefix}terms.term_id = {$wpdb->prefix}term_taxonomy.term_id
                    WHERE {$wpdb->prefix}posts.ID IN ({$giyon_product_ids})
                    AND {$wpdb->prefix}term_taxonomy.taxonomy = 'product_shipping_class'
                ") as $classes
                ) $giyon_shipping_classes[] = $classes->name;

                if (in_array('BOX', $giyon_shipping_classes)) $this->title   = 'BOX';
                else if (0 < count(array_intersect(['LPP', 'LPPF'], $giyon_shipping_classes))) $this->title   = 'Letter Pack Plus';
                else if (0 < count(array_intersect(['LPL', 'LPLF'], $giyon_shipping_classes))) $this->title   = 'Letter Pack Light';
                else $this->title = 'Smart Letter';

                $this->add_rate(array(
                    'id'    => $this->id,
                    'label' => $this->title,
                    'cost'  => 123,
                ));
            }
        }
    }
});

add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['giyon_shipping'] = 'Giyon_Shipping_Method';
    return $methods;
});
