<?php
/**
 * Plugin Name: MCS WP-CLI
 */
if (defined('WP_CLI') && WP_CLI) {
    require_once __DIR__ . '/../vendor/autoload.php';
    WP_CLI::add_command('malcode', [\MCS\WPCLI\Command::class, 'scan']);
}
