<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Eliminar opciones almacenadas en la base de datos
delete_option('cf7_mc_api_key');
delete_option('cf7_mc_list_id');
