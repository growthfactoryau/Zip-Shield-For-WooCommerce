<?php

declare(strict_types=1);

namespace ZipShield;

if (! defined('ABSPATH')) {
    exit;
}

final class Admin
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_post_zs_save_rule', [$this, 'save_rule']);
        add_action('admin_post_zs_delete_rule', [$this, 'delete_rule']);
        add_action('admin_post_zs_duplicate_rule', [$this, 'duplicate_rule']);
    }

    /**
     * Enqueue assets.
     */
    public function enqueue(): void
    {
        wp_enqueue_style('select2');
        wp_enqueue_script('select2');

        wp_enqueue_style(
            'zs-admin',
            ZS_PLUGIN_URL . 'assets/css/admin.css',
            [],
            ZS_VERSION
        );
        
        wp_enqueue_script(
            'zs-admin',
            ZS_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery', 'select2'],
            ZS_VERSION,
            true
        );

        wp_localize_script(
            'zs-admin',
            'zsAdmin',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('zs_admin_nonce'),
            ]
        );
    }

    /**
     * Register menu.
     */
    public function register_menu(): void
    {
        add_menu_page(
            __('Zip Shield', 'zip-shield-for-woocommerce'),
            __('Zip Shield', 'zip-shield-for-woocommerce'),
            'manage_woocommerce',
            'zip-shield',
            [$this, 'rules_page'],
            'dashicons-shield',
            56
        );
    }

    /**
     * Rules page.
     */
    public function rules_page(): void
    {
        $rules = $GLOBALS['zs_rules']->get_rules();

        $editing_rule = null;

        if (! empty($_GET['edit'])) {

            $editing_rule = $GLOBALS['zs_rules']->get_rule(
                absint($_GET['edit'])
            );
        }

        include ZS_PLUGIN_PATH . 'templates/admin-rules.php';
    }

    /**
     * Save rule.
     */
    public function save_rule(): void
    {
        check_admin_referer('zs_save_rule');

        if (! current_user_can('manage_woocommerce')) {
            wp_die();
        }

        $data = [
            'name'       => $_POST['name'] ?? '',
            'rule_type'  => $_POST['rule_type'] ?? 'allow_only',
            'message'    => $_POST['message'] ?? '',
            'enabled'    => ! empty($_POST['enabled']),
            'products'   => array_map('absint', $_POST['products'] ?? []),
            'categories' => array_map('absint', $_POST['categories'] ?? []),
            'zipcodes'   => $this->sanitize_zipcodes($_POST['zipcodes'] ?? ''),
        ];

        $rule_id = absint($_POST['rule_id'] ?? 0);

        if ($rule_id > 0) {
            $GLOBALS['zs_rules']->update($rule_id, $data);
        } else {
            $GLOBALS['zs_rules']->create($data);
        }

        wp_safe_redirect(admin_url('admin.php?page=zip-shield'));
        exit;
    }

    /**
     * Duplicate rule.
     */
    public function duplicate_rule(): void
    {
        check_admin_referer('zs_duplicate_rule');

        if (! current_user_can('manage_woocommerce')) {
            wp_die();
        }

        $rule_id = absint($_GET['rule_id'] ?? 0);

        if ($rule_id > 0) {
            $GLOBALS['zs_rules']->duplicate($rule_id);
        }

        wp_safe_redirect(admin_url('admin.php?page=zip-shield'));

        exit;
    }

    /**
     * Delete rule.
     */
    public function delete_rule(): void
    {
        check_admin_referer('zs_delete_rule');

        if (! current_user_can('manage_woocommerce')) {
            wp_die();
        }

        $rule_id = absint($_GET['rule_id'] ?? 0);

        if ($rule_id > 0) {
            $GLOBALS['zs_rules']->delete($rule_id);
        }

        wp_safe_redirect(admin_url('admin.php?page=zip-shield'));

        exit;
    }

    /**
     * Sanitize zipcodes.
     */
    private function sanitize_zipcodes(string $zipcodes): array
    {
        $lines = explode("\n", $zipcodes);

        $lines = array_map('trim', $lines);

        $lines = array_filter($lines);

        $lines = array_unique($lines);

        return array_values($lines);
    }
}
