<?php

declare(strict_types=1);

namespace ZipShield;

if (! defined('ABSPATH')) {
    exit;
}

final class Plugin
{
    /**
     * Plugin instance.
     */
    private static ?Plugin $instance = null;

    /**
     * Get instance.
     */
    public static function instance(): Plugin
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct()
    {
        $this->init_hooks();
    }

    /**
     * Init hooks.
     */
    private function init_hooks(): void
    {
        $GLOBALS['zs_rules'] = new Rules();

        new Admin();
        new Ajax();
        new Validator();
    }
}
