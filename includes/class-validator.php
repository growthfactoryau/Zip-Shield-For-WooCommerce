<?php

declare(strict_types=1);

namespace ZipShield;

if (! defined('ABSPATH')) {
    exit;
}

final class Validator
{
    /**
     * Removed cart items tracker.
     */
    private array $removed_items = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action(
            'woocommerce_checkout_update_order_review',
            [$this, 'validate_checkout']
        );

        add_action(
            'woocommerce_check_cart_items',
            [$this, 'validate_cart_items']
        );

        add_action(
            'woocommerce_after_checkout_validation',
            [$this, 'final_validation'],
            10,
            2
        );
    }

    /**
     * Global cart validation.
     *
     * Supports:
     * - Classic Checkout
     * - Checkout Blocks
     * - Cart Blocks
     * - Store API
     */
    public function validate_cart_items(): void
    {
        $zipcode = '';

        if (! empty($_POST['shipping_postcode'])) {

            $zipcode = sanitize_text_field(
                wp_unslash($_POST['shipping_postcode'])
            );
        } elseif (! empty($_POST['billing_postcode'])) {

            $zipcode = sanitize_text_field(
                wp_unslash($_POST['billing_postcode'])
            );
        }

        /*
		|--------------------------------------------------------------------------
		| Session fallback
		|--------------------------------------------------------------------------
		*/

        if (
            empty($zipcode) &&
            WC()->customer
        ) {

            $zipcode = WC()->customer->get_shipping_postcode();

            if (empty($zipcode)) {
                $zipcode = WC()->customer->get_billing_postcode();
            }
        }

        if (empty($zipcode)) {
            return;
        }

        $this->validate_cart($zipcode);
    }

    /**
     * AJAX checkout validation.
     */
    public function validate_checkout(string $posted_data): void
    {
        parse_str($posted_data, $data);

        $zipcode = $this->extract_zipcode($data);

        if (empty($zipcode)) {
            return;
        }

        $this->validate_cart($zipcode);
    }

    /**
     * Final checkout validation.
     */
    public function final_validation(
        array $data,
        \WP_Error $errors
    ): void {

        $zipcode = $this->extract_zipcode($data);

        if (empty($zipcode)) {
            return;
        }

        $this->validate_cart($zipcode);
    }

    /**
     * Validate cart against ZIP rules.
     */
    private function validate_cart(string $zipcode): void
    {
        if (
            ! WC()->cart ||
            WC()->cart->is_empty()
        ) {
            return;
        }

        $rules = $GLOBALS['zs_rules']->get_active_rules();

        if (empty($rules)) {
            return;
        }

        $zipcode = strtoupper(trim($zipcode));

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {

            $product_id   = (int) $cart_item['product_id'];
            $variation_id = (int) $cart_item['variation_id'];

            /*
			|--------------------------------------------------------------------------
			| Validate both parent + variation
			|--------------------------------------------------------------------------
			*/

            $check_ids = array_filter([
                $product_id,
                $variation_id,
            ]);

            foreach ($rules as $rule) {

                $matched = false;

                foreach ($check_ids as $check_id) {

                    if ($this->matches_rule($check_id, $rule)) {

                        $matched = true;

                        break;
                    }
                }

                if (! $matched) {
                    continue;
                }

                $allowed = $this->zipcode_allowed(
                    $zipcode,
                    $rule
                );

                if ($allowed) {
                    continue;
                }

                /*
				|--------------------------------------------------------------------------
				| Prevent duplicate removals
				|--------------------------------------------------------------------------
				*/

                if (isset($this->removed_items[$cart_item_key])) {
                    continue;
                }

                /*
				|--------------------------------------------------------------------------
				| Remove restricted product
				|--------------------------------------------------------------------------
				*/

                if (WC()->cart->get_cart_item($cart_item_key)) {

                    WC()->cart->remove_cart_item($cart_item_key);

                    $this->removed_items[$cart_item_key] = true;
                }

                /*
				|--------------------------------------------------------------------------
				| Display notice
				|--------------------------------------------------------------------------
				*/

                $message = ! empty($rule['message'])
                    ? $rule['message']
                    : __(
                        'Due to State Laws, purchase of this product online is restricted. Please contact our local dealer to purchase it.',
                        'zip-shield-for-woocommerce'
                    );

                $this->add_notice_once($message);
            }
        }
    }

    /**
     * Add unique WooCommerce notice.
     */
    private function add_notice_once(string $message): void
    {
        $existing = wc_get_notices('error');

        if (! empty($existing)) {

            foreach ($existing as $notice) {

                if (
                    isset($notice['notice']) &&
                    $notice['notice'] === $message
                ) {
                    return;
                }
            }
        }

        wc_add_notice($message, 'error');
    }

    /**
     * Check if product matches rule.
     */
    private function matches_rule(
        int $product_id,
        array $rule
    ): bool {

        /*
		|--------------------------------------------------------------------------
		| Direct Product Match
		|--------------------------------------------------------------------------
		*/

        if (
            ! empty($rule['products']) &&
            in_array($product_id, $rule['products'], true)
        ) {
            return true;
        }

        /*
		|--------------------------------------------------------------------------
		| Category Match
		|--------------------------------------------------------------------------
		*/

        if (! empty($rule['categories'])) {

            $product_categories = wc_get_product_term_ids(
                $product_id,
                'product_cat'
            );

            $all_categories = $product_categories;

            /*
			|--------------------------------------------------------------------------
			| Include ancestor categories
			|--------------------------------------------------------------------------
			*/

            foreach ($product_categories as $cat_id) {

                $ancestors = get_ancestors(
                    $cat_id,
                    'product_cat'
                );

                $all_categories = array_merge(
                    $all_categories,
                    $ancestors
                );
            }

            $all_categories = array_unique($all_categories);

            foreach ($rule['categories'] as $category_id) {

                if (
                    in_array(
                        (int) $category_id,
                        $all_categories,
                        true
                    )
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if ZIP is allowed.
     */
    private function zipcode_allowed(
        string $zipcode,
        array $rule
    ): bool {

        $zipcodes = array_map(
            'strtoupper',
            (array) $rule['zipcodes']
        );

        /*
		|--------------------------------------------------------------------------
		| Allow Only Mode
		|--------------------------------------------------------------------------
		*/

        if ($rule['rule_type'] === 'allow_only') {

            return in_array(
                $zipcode,
                $zipcodes,
                true
            );
        }

        /*
		|--------------------------------------------------------------------------
		| Block Mode
		|--------------------------------------------------------------------------
		*/

        if ($rule['rule_type'] === 'block') {

            return ! in_array(
                $zipcode,
                $zipcodes,
                true
            );
        }

        return true;
    }

    /**
     * Extract ZIP code.
     */
    private function extract_zipcode(array $data): string
    {
        $zipcode = '';

        if (! empty($data['shipping_postcode'])) {

            $zipcode = $data['shipping_postcode'];
        } elseif (! empty($data['billing_postcode'])) {

            $zipcode = $data['billing_postcode'];
        }

        return sanitize_text_field($zipcode);
    }
}
