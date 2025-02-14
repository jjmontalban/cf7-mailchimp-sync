<?php
/**
 * Plugin Name: CF7 MailChimp Sync
 * Description: Sincroniza los formularios de Contact Form 7 con MailChimp y asigna tags automáticamente.
 * Version: 1.0.0
 * Author: JJMontalban
 * Author URI: https://jjmontalban.github.io/
 * Text Domain: cf7-mailchimp-sync
 */

if (!defined('ABSPATH')) exit;

// Definir rutas del plugin
define('CF7_MC_PATH', plugin_dir_path(__FILE__));
define('CF7_MC_URL', plugin_dir_url(__FILE__));

// Incluir archivos necesarios
require_once CF7_MC_PATH . 'includes/class-mailchimp-api.php';
require_once CF7_MC_PATH . 'includes/class-cf7-handler.php';
require_once CF7_MC_PATH . 'includes/class-settings.php';

// Inicializar la configuración del plugin
add_action('plugins_loaded', function() {
    new CF7_MailChimp_Settings();
    new CF7_MailChimp_Handler();
});
