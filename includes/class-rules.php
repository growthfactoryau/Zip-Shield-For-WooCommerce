<?php

declare(strict_types=1);

namespace ZipShield;

if (! defined('ABSPATH')) {
    exit;
}

final class Rules
{
    /**
     * Rules table.
     */
    private string $table;

    /**
     * Product relation table.
     */
    private string $products_table;

    /**
     * Cached active rules.
     */
    private ?array $cached_active_rules = null;

    /**
     * Category relation table.
     */
    private string $categories_table;
    

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->table            = $wpdb->prefix . 'zs_rules';
        $this->products_table   = $wpdb->prefix . 'zs_rule_products';
        $this->categories_table = $wpdb->prefix . 'zs_rule_categories';
    }

    /**
     * Create rule.
     */
    public function create(array $data): int
    {
        global $wpdb;

        $wpdb->insert(
            $this->table,
            [
                'name'       => sanitize_text_field($data['name']),
                'rule_type'  => sanitize_text_field($data['rule_type']),
                'zipcodes'   => wp_json_encode($data['zipcodes']),
                'message'    => sanitize_textarea_field($data['message']),
                'enabled'    => ! empty($data['enabled']) ? 1 : 0,
                'created_at' => current_time('mysql'),
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
            ]
        );

        $rule_id = (int) $wpdb->insert_id;

        $this->save_products($rule_id, $data['products'] ?? []);
        $this->save_categories($rule_id, $data['categories'] ?? []);

        return $rule_id;
    }

    /**
     * Get active rules.
     */
    public function get_active_rules(): array
    {
        if (null !== $this->cached_active_rules) {
            return $this->cached_active_rules;
        }

        global $wpdb;

        $rules = $wpdb->get_results(
            "SELECT * FROM {$this->table} WHERE enabled = 1",
            ARRAY_A
        );

        if (empty($rules)) {
            $this->cached_active_rules = [];

            return [];
        }

        $prepared = [];

        foreach ($rules as $rule) {

            $full_rule = $this->get_rule((int) $rule['id']);

            if ($full_rule) {
                $prepared[] = $full_rule;
            }
        }

        $this->cached_active_rules = $prepared;

        return $prepared;
    }

    /**
     * Update rule.
     */
    public function update(int $rule_id, array $data): void
    {
        global $wpdb;

        $wpdb->update(
            $this->table,
            [
                'name'      => sanitize_text_field($data['name']),
                'rule_type' => sanitize_text_field($data['rule_type']),
                'zipcodes'  => wp_json_encode($data['zipcodes']),
                'message'   => sanitize_textarea_field($data['message']),
                'enabled'   => ! empty($data['enabled']) ? 1 : 0,
            ],
            [
                'id' => $rule_id,
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
            ],
            [
                '%d',
            ]
        );

        $this->delete_relations($rule_id);

        $this->save_products($rule_id, $data['products'] ?? []);
        $this->save_categories($rule_id, $data['categories'] ?? []);
    }

    /**
     * Delete rule.
     */
    public function delete(int $rule_id): void
    {
        global $wpdb;

        $this->delete_relations($rule_id);

        $wpdb->delete(
            $this->table,
            [
                'id' => $rule_id,
            ],
            [
                '%d',
            ]
        );
    }

    /**
     * Get all rules.
     */
    public function get_rules(): array
    {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT * FROM {$this->table} ORDER BY id DESC",
            ARRAY_A
        );
    }

    /**
     * Get single rule.
     */
    public function get_rule(int $rule_id): ?array
    {
        global $wpdb;

        $rule = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE id = %d",
                $rule_id
            ),
            ARRAY_A
        );

        if (! $rule) {
            return null;
        }

        $rule['products']   = $this->get_products($rule_id);
        $rule['categories'] = $this->get_categories($rule_id);
        $rule['zipcodes']   = json_decode($rule['zipcodes'], true);

        return $rule;
    }

    /**
     * Save products.
     */
    private function save_products(int $rule_id, array $products): void
    {
        global $wpdb;

        foreach ($products as $product_id) {

            $wpdb->insert(
                $this->products_table,
                [
                    'rule_id'    => $rule_id,
                    'product_id' => absint($product_id),
                ],
                [
                    '%d',
                    '%d',
                ]
            );
        }
    }

    /**
     * Save categories.
     */
    private function save_categories(int $rule_id, array $categories): void
    {
        global $wpdb;

        foreach ($categories as $category_id) {

            $wpdb->insert(
                $this->categories_table,
                [
                    'rule_id'     => $rule_id,
                    'category_id' => absint($category_id),
                ],
                [
                    '%d',
                    '%d',
                ]
            );
        }
    }

    /**
     * Delete relations.
     */
    private function delete_relations(int $rule_id): void
    {
        global $wpdb;

        $wpdb->delete(
            $this->products_table,
            [
                'rule_id' => $rule_id,
            ],
            [
                '%d',
            ]
        );

        $wpdb->delete(
            $this->categories_table,
            [
                'rule_id' => $rule_id,
            ],
            [
                '%d',
            ]
        );
    }

    /**
     * Get products.
     */
    private function get_products(int $rule_id): array
    {
        global $wpdb;

        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT product_id FROM {$this->products_table} WHERE rule_id = %d",
                $rule_id
            )
        );
    }

    /**
     * Duplicate rule.
     */
    public function duplicate(int $rule_id): int
    {
        $rule = $this->get_rule($rule_id);

        if (! $rule) {
            return 0;
        }

        unset($rule['id']);

        $rule['name'] .= ' (Copy)';

        return $this->create($rule);
    }

    /**
     * Get categories.
     */
    private function get_categories(int $rule_id): array
    {
        global $wpdb;

        return $wpdb->get_col(
            $wpdb->prepare(
                "SELECT category_id FROM {$this->categories_table} WHERE rule_id = %d",
                $rule_id
            )
        );
    }
}
