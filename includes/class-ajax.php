<?php

declare(strict_types=1);

namespace ZipShield;

if (! defined('ABSPATH')) {
    exit;
}

final class Ajax
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action(
            'wp_ajax_zs_search_products',
            [$this, 'search_products']
        );

        // Core Dynamic Link: Step 1 Category Change Endpoint
        add_action(
            'wp_ajax_zs_get_products_by_category',
            [$this, 'get_products_by_category']
        );
    }

    /**
     * Search WooCommerce products.
     */
    public function search_products(): void
    {
        check_ajax_referer('zs_admin_nonce', 'nonce');

        if (! current_user_can('manage_woocommerce')) {
            wp_send_json_error();
        }

        $term = sanitize_text_field($_GET['term'] ?? '');

        $query = new \WP_Query([
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            's'              => $term,
        ]);

        $results = [];

        foreach ($query->posts as $product) {

            $results[] = [
                'id'   => $product->ID,
                'text' => $product->post_title,
            ];
        }

        wp_send_json($results);
    }

    /**
     * Fetch products filtered by category ID for Rule Assignment canvas.
     */
    public function get_products_by_category(): void
    {
        check_ajax_referer('zs_admin_nonce', 'nonce');

        if (! current_user_can('manage_woocommerce')) {
            wp_send_json_error('Unauthorized access.', 403);
        }

        $category_id = isset($_POST['category_id']) ? absint($_POST['category_id']) : 0;

        if (0 === $category_id) {
            wp_send_json_error('Invalid Category ID provided.');
        }

        // Fetch products associated with this target taxonomy term
        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => -1, // Get all matching items to prevent partial matrices
            'fields'         => 'ids',
            'tax_query'      => [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_id,
                    'operator' => 'IN',
                ],
            ],
        ];

        $query = new \WP_Query($args);
        $products = [];

        if (! empty($query->posts)) {
            foreach ($query->posts as $id) {
                $products[] = [
                    'id'   => $id,
                    'name' => get_the_title($id),
                ];
            }
        }

        wp_send_json_success($products);
    }
}
