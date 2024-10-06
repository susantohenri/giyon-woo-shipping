<?php

/**
 * Plugin Name: Giyon Woo Shipping
 * Plugin URI: https://github.com/susantohenri/giyon-woo-shipping
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

define('GIYON_SHIPPING_CLASS_VOLUME_LIMIT', [
    'Smart Letter' => 400,
    'Letter Pack Light' => 800,
    'Letter Pack Plus' => 1200,
    'Box 60' => 5000,
    'Box 80' => 11900,
    'Box 100' => 24300,
    'Box 120' => 49500,
    'Box 140' => 80500,
    'Box 160' => 122400,
    'Box 170' => 153600
]);
define('GIYON_BOXC', [
    'Box 60' => 280,
    'Box 80' => 330,
    'Box 100' => 440,
    'Box 120' => 720,
    'Box 140' => 720,
    'Box 160' => 720,
    'Box 170' => 720
]);
define('GIYON_CSV_ONGKIR', plugin_dir_path(__FILE__) . 'giyon-woo-shipping.csv');

add_action('admin_menu', function () {
    $page_title = 'Giyon Config';
    $menu_title = 'Giyon Config';
    $capability = 'administrator';
    $menu_slug = 'giyon-config';
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, function () {
        include_once(plugin_dir_path(__FILE__) . 'giyon-woo-shipping-admin.php');
    });
});

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
                $giyon_cart = [
                    'prefecture' => giyon_cart_to_prefecture($package),
                    'products' => giyon_cart_to_products($package),
                    'volume' => 0,
                    'shipping_class_by_volume' => '',
                    'shipping_class_by_products' => '',
                    'shipping_class_to_show' => '',
                    'is_under_dimension' => false,
                    'is_over_dimension' => false,
                    'shipping_cost_by_volume_shipping_class' => '',
                    'shipping_cost_by_products_shipping_class' => '',
                    'shipping_cost' => 0
                ];

                $giyon_cart['shipping_class_by_products'] = giyon_products_to_shipping_class($giyon_cart['products']);

                foreach ($giyon_cart['products'] as $gprod) $giyon_cart['volume'] += $gprod['quantity'] * $gprod['volume'];
                $giyon_cart['shipping_class_by_volume'] = giyon_volume_to_shipping_class($giyon_cart['volume']);

                // under or over dimension
                $giyon_cart['is_under_dimension'] = giyon_shipping_class_to_index($giyon_cart['shipping_class_by_volume']) < giyon_shipping_class_to_index($giyon_cart['shipping_class_by_products']);
                $giyon_cart['is_over_dimension'] = giyon_shipping_class_to_index($giyon_cart['shipping_class_by_volume']) > giyon_shipping_class_to_index($giyon_cart['shipping_class_by_products']);

                // shipping_class_to_show
                $giyon_cart['shipping_class_to_show'] = $giyon_cart['shipping_class_by_products'];
                if ($giyon_cart['is_over_dimension']) $giyon_cart['shipping_class_to_show'] = $giyon_cart['shipping_class_by_volume'];

                // base rule
                $giyon_cart['shipping_cost_by_volume_shipping_class'] = giyon_csv_to_cost($giyon_cart['prefecture'], $giyon_cart['shipping_class_by_volume']);
                $giyon_cart['shipping_cost_by_products_shipping_class'] = giyon_csv_to_cost($giyon_cart['prefecture'], $giyon_cart['shipping_class_by_products']);
                $giyon_cart['shipping_cost'] = $giyon_cart['shipping_cost_by_products_shipping_class'];

                // special rules
                switch ($giyon_cart['shipping_class_by_products']) {
                    case 'Smart Letter':
                        if ($giyon_cart['is_over_dimension']) {
                            // - Apabila volume melebihi volume Smart Letter (400 cm³), maka akan menggunakan packaging di atasnya dengan tarif selisih antara ongkir packaging yang dipakai (tarif yg dipakai dikurangi 210 yen).
                            $giyon_cart['shipping_cost'] = $giyon_cart['shipping_cost_by_volume_shipping_class'] - 210;
                        }
                        break;
                    case 'Letter Pack Light':
                        $has_lplf = giyon_products_contains_shipping_class($giyon_cart['products'], 'LPLF');

                        // if ($giyon_cart['is_under_dimension']) {} else
                        if ($giyon_cart['is_over_dimension']) {
                            if ($has_lplf) {
                                // - Apabila volume melebihi volume Letter Pack Light (800 cm³), dengan kombinasi shipping class LPLF, maka akan menggunakan packaging di atasnya dengan tarif selisih antara ongkir packaging yang dipakai (tarif yg dipakai dikurangi 430 yen).
                                $giyon_cart['shipping_cost'] = $giyon_cart['shipping_cost_by_volume_shipping_class'] - $giyon_cart['shipping_cost_by_products_shipping_class'];
                            } else {
                                // - Apabila volume melebihi volume Letter Pack Light (800 cm³), maka akan menggunakan packaging dan tarif packaging di atasnya
                                $giyon_cart['shipping_cost'] = $giyon_cart['shipping_cost_by_volume_shipping_class'];
                            }
                        } else {
                            if ($has_lplf) {
                                // - Apabila ada kombinasi dengan LPLF maka tidak dikenakan ongkir (free ongkir).
                                $giyon_cart['shipping_cost'] = 0;
                            }
                        }
                        break;
                    case 'Letter Pack Plus':
                        $has_lplf = giyon_products_contains_shipping_class($giyon_cart['products'], 'LPLF');
                        $has_lppf = giyon_products_contains_shipping_class($giyon_cart['products'], 'LPPF');
                        $has_no_lppf = !$has_lppf;

                        // if ($giyon_cart['is_under_dimension']) {} else
                        if ($giyon_cart['is_over_dimension']) {
                            if ($has_lplf && $has_no_lppf) {
                                // - Apabila volume melebihi volume Letter Pack Plus (1200 cm³), dengan kombinasi shipping class "LPLF" tanpa "LPPF", maka akan menggunakan packaging BOX dengan tarif selisih antara ongkir packaging BOX dikurangi 430 yen.
                                $giyon_cart['shipping_cost'] = $giyon_cart['shipping_cost_by_volume_shipping_class'] - giyon_csv_to_cost($giyon_cart['prefecture'], 'Letter Pack Light');
                            } else if ($has_lppf) {
                                // - Apabila volume melebihi volume Letter Pack Plus (1200 cm³), dengan kombinasi shipping class "LPPF", maka akan menggunakan packaging BOX dengan tarif selisih antara ongkir packaging BOX dikurangi 600 yen.
                                $giyon_cart['shipping_cost'] = $giyon_cart['shipping_cost_by_volume_shipping_class'] - $giyon_cart['shipping_cost_by_products_shipping_class'];
                            } else {
                                // - Apabila volume melebihi volume Letter Pack Plus (1200 cm³), maka akan menggunakan packaging dan tarif packaging di atasnya (BOX) sesuai wilayah
                                $giyon_cart['shipping_cost'] = $giyon_cart['shipping_cost_by_volume_shipping_class'];
                            }
                        } else {
                            if ($has_lplf && $has_no_lppf) {
                                // - Apabila ada kombinasi dengan "LPLF"  tanpa LPPF maka tidak dikenakan ongkir selisih dari Letter Pack Plus dikurangi Letter Pack Light (600 - 430 = 170)
                                $giyon_cart['shipping_cost'] = $giyon_cart['shipping_cost_by_products_shipping_class'] - giyon_csv_to_cost($giyon_cart['prefecture'], 'Letter Pack Light');
                            } else if ($has_lppf) {
                                // - Apabila ada kombinasi dengan "LPPF" maka tidak dikenakan ongkir (free ongkir).
                                $giyon_cart['shipping_cost'] = 0;
                            }
                        }

                        break;
                    case 'Box':
                        $giyon_cart['shipping_class_to_show'] = $giyon_cart['shipping_class_by_volume'];
                        if ($giyon_cart['is_under_dimension']) {
                            $giyon_cart['shipping_class_to_show'] = 'Box 60';
                        } else {
                            $giyon_cart['shipping_cost'] = $giyon_cart['shipping_cost_by_volume_shipping_class'];
                        }

                        if (giyon_products_contains_shipping_class($giyon_cart['products'], 'LPPF')) {
                            $giyon_cart['shipping_cost'] -= 600;
                        } else if (giyon_products_contains_shipping_class($giyon_cart['products'], 'LPLF')) {
                            $giyon_cart['shipping_cost'] -= 430;
                        }

                        if (1 == giyon_boxc_status()) {
                            if (giyon_products_contains_shipping_class($giyon_cart['products'], 'BOXC')) {
                                $boxc = GIYON_BOXC;
                                $giyon_cart['shipping_cost'] += isset($boxc[$giyon_cart['shipping_class_by_volume']]) ?
                                    $boxc[$giyon_cart['shipping_class_by_volume']] :
                                    reset($boxc);
                            }
                        }
                        break;
                }

                // Kalau di cart HANYA ada shipping class yang free (LPLF -DAN ATAU- LPPF) maka order berapapun tetep free
                if (0 != $giyon_cart['shipping_cost']) {
                    $collect_shipping_classes = array_map(function ($product) {
                        return $product['shipping_class'];
                    }, $giyon_cart['products']);
                    $non_free = array_filter($collect_shipping_classes, function ($ship_cl) {
                        return !in_array($ship_cl, ['LPLF', 'LPPF']);
                    });
                    if (0 == count($non_free)) $giyon_cart['shipping_cost'] = 0;
                }

                // - Belanja di atas 20.000 yen Free Ongkir
                if (20000 < $package['contents_cost']) $giyon_cart['shipping_cost'] = 0;

                // debugging
                if (isset($_POST['giyon_debug'])) {
                    unset($_POST['giyon_debug']);
                    echo json_encode($giyon_cart, JSON_PRETTY_PRINT) . '<br>';
                }

                // - Nama packaging muncul di front end di samping nominal ongkir (di keranjang maupun checkout)
                $this->title = $giyon_cart['shipping_class_to_show'];
                $this->add_rate(array(
                    'id'    => $this->id,
                    'label' => $this->title,
                    'cost'  => $giyon_cart['shipping_cost']
                ));
            }
        }
    }
});

if (giyon_debug_status()) {
    add_action('wp_footer', function () {
        echo '<form method="POST"><button name="giyon_debug">giyon debug</button></form>';
    });
}

add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['giyon_shipping'] = 'Giyon_Shipping_Method';
    return $methods;
});

function giyon_cart_to_products($package)
{
    return array_values(array_map(function ($content) {
        $product_id = $content['product_id'];
        $data_volume = giyon_product_id_to_volume($product_id, $content['variation_id']);
        $data_product = [
            'product_id' => $product_id,
            'quantity' => $content['quantity'],
            'shipping_class' => giyon_product_id_to_shipping_class($product_id)
        ];
        $data_product = array_merge($data_product, $data_volume);
        return $data_product;
    }, $package['contents']));
}

function giyon_cart_to_prefecture($package)
{
    $country_code = 'JP';
    $state_code = $package['destination']['state'];
    $states = WC()->countries->get_states($country_code);
    return $states[$state_code];
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

function giyon_products_to_shipping_class($products)
{
    $shipping_classes = giyon_products_to_shipping_classes($products);
    if (0 < count(array_intersect(['BOX', 'BOXC'], $shipping_classes))) return 'Box';
    else if (0 < count(array_intersect(['LPP', 'LPPF'], $shipping_classes))) return 'Letter Pack Plus';
    else if (0 < count(array_intersect(['LPL', 'LPLF'], $shipping_classes))) return 'Letter Pack Light';
    else return 'Smart Letter';
}

function giyon_products_contains_shipping_class($products, $shipping_class)
{
    $shipping_classes = giyon_products_to_shipping_classes($products);
    return -1 < array_search($shipping_class, $shipping_classes);
}

function giyon_products_to_shipping_classes($products)
{
    return array_map(function ($product) {
        return $product['shipping_class'];
    }, $products);
}

function giyon_product_id_to_volume($product_id, $variation_id)
{
    global $wpdb;
    $data_volume = ['volume_type' => 'parent'];
    $volume = 1;
    $query = "
        SELECT meta_key, meta_value
        FROM {$wpdb->prefix}postmeta
        WHERE post_id = %post_id
        AND meta_key IN ('_length', '_width', '_height')
    ";
    $dimensions = $wpdb->get_results(str_replace('%post_id', $product_id, $query));
    if (0 == count($dimensions)) {
        $data_volume['volume_type'] = 'variable';
        $dimensions = $wpdb->get_results(str_replace('%post_id', $variation_id, $query));
    }
    foreach ($dimensions as $dimension) {
        $data_volume[$dimension->meta_key] = $dimension->meta_value;
        $volume *= $dimension->meta_value;
    }
    $data_volume['volume'] = $volume;
    return $data_volume;
}

function giyon_volume_to_shipping_class($volume)
{
    $shipping_class = '';
    $limits = array_reverse(giyon_config_to_limit());
    foreach ($limits as $class => $value) {
        if ($volume <= $value) $shipping_class = $class;
    }
    return $shipping_class;
}

function giyon_shipping_class_to_index($shipping_class)
{
    $shipping_class = 0 === strpos($shipping_class, 'Box') ? 'Box 60' : $shipping_class;
    return array_search($shipping_class, array_keys(giyon_config_to_limit()));
}

function giyon_read_csv()
{
    $csv = [];
    $file = fopen(GIYON_CSV_ONGKIR, 'r');
    while (!feof($file)) {
        $line = fgetcsv($file);
        if (!!$line) $csv[] = $line;
    }
    fclose($file);
    return $csv;
}

function giyon_csv_to_cost($prefecture, $shipping_class)
{
    $shipping_class = 'Box' == $shipping_class ? 'Box 60' : $shipping_class;
    $rows = giyon_read_csv();
    $col_num = array_search($shipping_class, $rows[0]);
    $row = array_values(array_filter($rows, function ($cols) use ($prefecture) {
        return $cols[0] == $prefecture;
    }))[0];
    $cost = $row[$col_num];
    $cost = 'Free' == $cost ? 0 : $cost;
    return (float)$cost;
}

function giyon_boxc_status()
{
    return get_option('giyon_boxc_enable', 0);
}

function giyon_ongkir_cod()
{
    return get_option('giyon_ongkir_cod', 500);
}

function giyon_volume_smart_letter()
{
    return get_option('giyon_volume_smart_letter', 400);
}

function giyon_volume_letter_pack_light()
{
    return get_option('giyon_volume_letter_pack_light', 800);
}

function giyon_volume_letter_pack_plus()
{
    return get_option('giyon_volume_letter_pack_plus', 1200);
}

function giyon_config_to_limit()
{
    $limit = GIYON_SHIPPING_CLASS_VOLUME_LIMIT;
    $limit['Smart Letter'] = giyon_volume_smart_letter();
    $limit['Letter Pack Light'] = giyon_volume_letter_pack_light();
    $limit['Letter Pack Plus'] = giyon_volume_letter_pack_plus();
    return $limit;
}

add_action('wp_footer', function () {
    global $wp;
    $current_url = home_url(add_query_arg(array(), $wp->request));
    $order_id = explode('/', $current_url);
    $order_id = (int) end($order_id);

    if (is_cart() || is_page('cart')) {
        wp_register_script('giyon-woo-shipping-free', plugin_dir_url(__FILE__) . 'giyon-woo-shipping-free.js', array('jquery'));
        wp_enqueue_script('giyon-woo-shipping-free');
    }

    if (is_checkout() && empty($wp->query_vars['order-pay'])) {
        wp_register_script('giyon-woo-shipping-free', plugin_dir_url(__FILE__) . 'giyon-woo-shipping-free.js', array('jquery'));
        wp_enqueue_script('giyon-woo-shipping-free');

        wp_register_script('giyon-woo-shipping', plugin_dir_url(__FILE__) . 'giyon-woo-shipping.js', ['jquery'], '1.0.2');
        wp_enqueue_script('giyon-woo-shipping');
        wp_localize_script('giyon-woo-shipping', 'giyon_woo_shipping', [
            'arrival_form' => plugin_dir_url(__FILE__) . 'giyon-woo-shipping-arrival.php',
            'arrival_form_selector' => 'div.wp-block-woocommerce-checkout-arrival-methods-block',
            'paylater_form' => plugin_dir_url(__FILE__) . 'giyon-woo-shipping-paylater.php',
            'paylater_form_selector' => 'div.wp-block-woocommerce-checkout-paylater-methods-block',
            'shipping_form_selector' => 'div.wd-table-wrapper.wd-manage-on',
            'shipping_class_selector' => 'label[for="shipping_method_0_giyon_shipping"]',
            'order_id' => $order_id,
            'upload_local_storage' => site_url('wp-json/giyon-woo-shipping/v1/upload-local-storage'),
        ]);
    }
});

add_action('rest_api_init', function () {
    register_rest_route('giyon-woo-shipping/v1', '/upload-local-storage', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => function () {
            $order = wc_get_order($_POST['order_id']);
            $notes = $order->get_customer_note();
            $notes = '' == $notes ? [] : [$notes];

            if (isset($_POST['paylater_time'])) $notes[] = __('Waktu Pembayaran: ' . $_POST['paylater_time']);
            if (isset($_POST['arrival_hour'])) $notes[] = __('Jam kedatangan: ' . $_POST['arrival_hour']);

            $order->set_customer_note(implode(', ', $notes));
            $order->save();
            return 200;
        }
    ));
});

function giyon_debug_status()
{
    return get_option('giyon_debug_enable', 0);
}
