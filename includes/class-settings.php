<?php

class CF7_MailChimp_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function add_settings_page() {
        add_options_page(
            'CF7 MailChimp Sync',
            'CF7 MailChimp Sync',
            'manage_options',
            'cf7-mailchimp-sync',
            [$this, 'settings_page']
        );
    }

    public function register_settings() {
        register_setting('cf7_mc_settings', 'cf7_mc_api_key');
        register_setting('cf7_mc_settings', 'cf7_mc_list_id');
    }

    public function settings_page() {
        ?>
        <div class="wrap">
            <h2>Configuración de CF7 MailChimp Sync</h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('cf7_mc_settings');
                do_settings_sections('cf7_mc_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th><label for="cf7_mc_api_key">API Key de MailChimp</label></th>
                        <td><input type="text" name="cf7_mc_api_key" value="<?php echo esc_attr(get_option('cf7_mc_api_key')); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="cf7_mc_list_id">ID de la Lista de MailChimp</label></th>
                        <td><input type="text" name="cf7_mc_list_id" value="<?php echo esc_attr(get_option('cf7_mc_list_id')); ?>" class="regular-text"></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function enqueue_admin_assets() {
        wp_enqueue_script('cf7-mc-admin-js', CF7_MC_URL . 'assets/js/admin.js', [], '1.0.0', true);
        wp_enqueue_style('cf7-mc-admin-css', CF7_MC_URL . 'assets/css/admin.css', [], '1.0.0');
    }    
}
