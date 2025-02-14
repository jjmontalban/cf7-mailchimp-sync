<div class="wrap cf7-mc-settings">
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
