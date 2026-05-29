<?php

/**
 * Zip Shield Admin Dashboard Template
 * 
 * @package ZipShield
 * @version 2.1.1 (Persistence Update)
 */

$editing = ! empty($editing_rule);
$rules_count = ! empty($rules) ? count($rules) : 0;

// Fetch saved arrays safely for pre-selection checks
$saved_categories = ! empty($editing_rule['categories']) ? array_map('intval', $editing_rule['categories']) : [];
$saved_products   = ! empty($editing_rule['products']) ? array_map('intval', $editing_rule['products']) : [];
$active_category  = ! empty($saved_categories) ? $saved_categories[0] : 0;
?>

<div class="zs-admin-wrapper">
    <!-- Top Navigation -->
    <header class="zs-navbar">
        <div class="zs-brand">
            <div class="zs-logo-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <div>
                <h1><?php esc_html_e('Zip Shield', 'zip-shield-for-woocommerce'); ?></h1>
                <p><?php esc_html_e('Advanced geographic restriction engine for WooCommerce.', 'zip-shield-for-woocommerce'); ?></p>
            </div>
        </div>
        <div class="zs-nav-meta">
            <span class="zs-pill-count">
                <strong><?php echo esc_html($rules_count); ?></strong> <?php echo esc_html(_n('Active Rule', 'Total Rules', $rules_count, 'zip-shield-for-woocommerce')); ?>
            </span>
        </div>
    </header>

    <!-- Main Workspace Layout -->
    <main class="zs-workspace">

        <!-- Workspace Form Card -->
        <section class="zs-panel format-form-panel">
            <div class="zs-panel-header">
                <div>
                    <h2><?php echo $editing ? esc_html__('Modify Rule', 'zip-shield-for-woocommerce') : esc_html__('Create Rule', 'zip-shield-for-woocommerce'); ?></h2>
                </div>
                <span class="zs-status-badge <?php echo $editing ? 'badge-editing' : 'badge-new'; ?>">
                    <?php echo $editing ? esc_html__('Editing Mode', 'zip-shield-for-woocommerce') : esc_html__('Draft', 'zip-shield-for-woocommerce'); ?>
                </span>
            </div>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="zs-submittable-form">
                <input type="hidden" name="action" value="zs_save_rule">
                <input type="hidden" name="rule_id" value="<?php echo esc_attr($editing_rule['id'] ?? 0); ?>">
                <?php wp_nonce_field('zs_save_rule'); ?>

                <div class="zs-form-group">
                    <label for="zs_rule_name"><?php esc_html_e('Rule Title', 'zip-shield-for-woocommerce'); ?></label>
                    <input type="text" id="zs_rule_name" name="name" required placeholder="e.g., California THC Restrictions" value="<?php echo esc_attr($editing_rule['name'] ?? ''); ?>">
                </div>

                <div class="zs-form-group">
                    <label for="zs_rule_type"><?php esc_html_e('Restriction Mechanism', 'zip-shield-for-woocommerce'); ?></label>
                    <select id="zs_rule_type" name="rule_type" class="zs-select-native">
                        <option value="allow_only" <?php selected($editing_rule['rule_type'] ?? '', 'allow_only'); ?>><?php esc_html_e('Whitelist: Allow matching ZIPs only', 'zip-shield-for-woocommerce'); ?></option>
                        <option value="block" <?php selected($editing_rule['rule_type'] ?? '', 'block'); ?>><?php esc_html_e('Blacklist: Block matching ZIPs', 'zip-shield-for-woocommerce'); ?></option>
                    </select>
                </div>

                <!-- STEP 1: Contextual Category Selector -->
                <div class="zs-form-group">
                    <label for="zs_category_drilldown"><?php esc_html_e('Step 1: Select Target Category', 'zip-shield-for-woocommerce'); ?></label>
                    <!-- FIX: Added name="categories[]" to ensure submission to class-rules.php -->
                    <select id="zs_category_drilldown" name="categories[]" class="zs-select-native">
                        <option value=""><?php esc_html_e('-- Choose a Category to Filter Products --', 'zip-shield-for-woocommerce'); ?></option>
                        <?php
                        $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
                        foreach ($categories as $category) :
                        ?>
                            <option value="<?php echo esc_attr($category->term_id); ?>" <?php selected($active_category, $category->term_id); ?>>
                                <?php echo esc_html($category->name); ?> (<?php echo esc_html($category->count); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- STEP 2: Scoped Product Canvas List Container -->
                <div class="zs-form-group">
                    <label><?php esc_html_e('Step 2: Assign Target Products', 'zip-shield-for-woocommerce'); ?></label>
                    <div class="zs-product-drilldown-scroller">
                        <!-- FIX: Embed selected state targets into data attributes so JavaScript can read them on edit mode load -->
                        <div id="zs_product_checkbox_matrix" class="zs-checkbox-matrix" data-selected-products="<?php echo esc_attr(wp_json_encode($saved_products)); ?>">
                            <?php if ($editing && ! empty($active_category)) : ?>
                                <!-- Pre-render structural placeholders if editing so there is zero layout shift -->
                                <p class="zs-matrix-notice"><?php esc_html_e('Restoring matrix rules...', 'zip-shield-for-woocommerce'); ?></p>
                            <?php else : ?>
                                <p class="zs-matrix-notice"><?php esc_html_e('Please pick a product category above to populate matching items.', 'zip-shield-for-woocommerce'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="zs-form-group">
                    <label for="zs_zipcodes"><?php esc_html_e('Postal & ZIP Codes', 'zip-shield-for-woocommerce'); ?></label>
                    <textarea id="zs_zipcodes" name="zipcodes" rows="4" placeholder="90210&#10;10001"><?php echo esc_textarea(! empty($editing_rule['zipcodes']) ? implode("\n", $editing_rule['zipcodes']) : ''); ?></textarea>
                </div>

                <div class="zs-form-group">
                    <label for="zs_message"><?php esc_html_e('Frontend Intercept Message', 'zip-shield-for-woocommerce'); ?></label>
                    <textarea id="zs_message" name="message" rows="3" placeholder="Due to regulatory limitations..."><?php echo esc_textarea($editing_rule['message'] ?? ''); ?></textarea>
                </div>

                <div class="zs-switch-card">
                    <div class="zs-switch-meta">
                        <span class="zs-switch-title"><?php esc_html_e('Rule Dispatcher Status', 'zip-shield-for-woocommerce'); ?></span>
                    </div>
                    <label class="zs-switch-toggle">
                        <input type="checkbox" name="enabled" value="1" <?php checked($editing_rule['enabled'] ?? 1, 1); ?>>
                        <span class="zs-switch-track"></span>
                    </label>
                </div>

                <div class="zs-form-actions">
                    <button type="submit" class="zs-btn-primary">
                        <span><?php echo $editing ? esc_html__('Update Engine', 'zip-shield-for-woocommerce') : esc_html__('Deploy Rule', 'zip-shield-for-woocommerce'); ?></span>
                    </button>
                </div>
            </form>
        </section>

        <!-- Rule Directory Matrix Panel -->
        <section class="zs-panel format-table-panel">
            <div class="zs-panel-header">
                <div>
                    <h2><?php esc_html_e('Configured Rule Matrix', 'zip-shield-for-woocommerce'); ?></h2>
                </div>
            </div>

            <div class="zs-matrix-container">
                <?php if (! empty($rules)) : ?>
                    <?php foreach ($rules as $rule) : ?>
                        <div class="zs-matrix-row <?php echo ((int)$rule['enabled'] !== 1) ? 'is-row-disabled' : ''; ?>">
                            <div class="zs-matrix-identity">
                                <div class="zs-matrix-icon-wrapper <?php echo $rule['rule_type'] === 'allow_only' ? 'type-allow' : 'type-block'; ?>">
                                    <?php if ($rule['rule_type'] === 'allow_only') : ?>
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    <?php else : ?>
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" />
                                            <path d="M4.93 4.93l14.14 14.14" stroke="currentColor" stroke-width="2" />
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="zs-matrix-details">
                                    <h3><?php echo esc_html($rule['name']); ?></h3>
                                    <div class="zs-matrix-meta-pills">
                                        <span class="zs-meta-tag format-type"><?php echo $rule['rule_type'] === 'allow_only' ? esc_html__('Whitelist', 'zip-shield-for-woocommerce') : esc_html__('Blacklist', 'zip-shield-for-woocommerce'); ?></span>
                                        <span class="zs-meta-tag format-id">ID: #<?php echo esc_html($rule['id']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="zs-matrix-state">
                                <span class="zs-status-dot <?php echo ((int)$rule['enabled'] === 1) ? 'state-active' : 'state-disabled'; ?>">
                                    <?php echo ((int)$rule['enabled'] === 1) ? esc_html__('Live', 'zip-shield-for-woocommerce') : esc_html__('Inactive', 'zip-shield-for-woocommerce'); ?>
                                </span>
                            </div>
                            <div class="zs-matrix-actions">
                                <a class="zs-action-btn action-edit" href="<?php echo esc_url(admin_url('admin.php?page=zip-shield&edit=' . $rule['id'])); ?>">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7M18.5 2.5a2.121 2.121 0 113 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                                <a class="zs-action-btn action-delete" href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=zs_delete_rule&rule_id=' . $rule['id']), 'zs_delete_rule')); ?>" onclick="return confirm('Delete?');">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>