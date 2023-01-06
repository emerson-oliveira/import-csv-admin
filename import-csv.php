<?php
/*
Nome do Plugin: Importar CSV
Descrição: Este é o plugin para importação de CSV!
Autor: Alberto
*/

/**
 * Plugin Name: Importar CSV
 * Description: Este é o plugin para importação de CSV!
 * Author: Alberto
 * Version: 1.0
 * Text Domain: import-csv
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

register_activation_hook(__FILE__, 'import_csv_create_db');
function import_csv_create_db()
{
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$table_name = $wpdb->prefix . 'import_csv';

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		nome varchar(256) NOT NULL,
		email varchar(256) NOT NULL,
		cpf varchar(256) NOT NULL,
		ano varchar(256) NOT NULL,
		mes varchar(256) NOT NULL,
		salario varchar(256) NOT NULL,
		data TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY id (id)
	) $charset_collate;";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

// Inclui o mfp-functions.php, usa o require_once para interromper o script caso o mfp-functions.php não seja encontrado
require_once plugin_dir_path(__FILE__) . 'includes/pic-functions.php';
