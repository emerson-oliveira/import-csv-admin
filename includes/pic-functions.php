<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

add_action("admin_init", "import_csv_csv");

add_action('admin_menu', function () {
    add_menu_page(
        'Import CSV',
        'Import CSV',
        'manage_options',
        'import_csvs',
        function () {
            include plugin_dir_path(__FILE__) . 'pic-imports.php';
        },
        'dashicons-chart-bar',
        30
    );
});

add_action('admin_menu', function () {
    add_submenu_page(
        null,
        'Import CSV Form',
        'Import CSV Form',
        'activate_plugins',
        'import_csvs_form',
        function () {            
            include plugin_dir_path(__FILE__) . 'pic-imports_form.php';
        },
        null
    );
});


function import_csv_csv()
{
    global $wpdb;    

    if (isset($_POST['import_csv_csv']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], get_stylesheet_directory_uri() . '/import_csv/imports.php')) {

		if ($_FILES['import_csv_file']) {		

            /* IMPORT CSV */
            $file_csv = fopen($_FILES['import_csv_file']['tmp_name'], 'r');

            $importData_arr = array();
            $i = 0;

            while (($filedata = fgetcsv($file_csv, 1000, ";")) !== FALSE) {
                $num = count($filedata);
                if ($i == 0) {
                    $arrayConf = array(0 => "nome", 1 => "email", 2 => "cpf", 3 => "ano", 4 => "mes", 5 => "salario");
                    if ($arrayConf != $filedata) {
						$json['msg'] = 'Cabeçalho diferente do esperado!';
                        echo json_encode($json);
                        die();
                    }
                    $i++;
                    continue;
                }
                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = utf8_encode(trim($filedata[$c]));
                }
                $i++;
            }
            fclose($file_csv);

            $i = 0;

            $wpdb->query('TRUNCATE TABLE wp_import_csv');

            // Insert to MySQL database
			foreach ($importData_arr as $importData) {
				$insert = $wpdb->insert('wp_import_csv', array(
					"nome" => $importData[0],
					"email" => $importData[1],
					"cpf" => $importData[2],
					"ano" => $importData[3],
					"mes" => $importData[4],
					"salario" => $importData[5],
				));
			}
        }
    }
}

class Load_Import_CSV_List_Table extends WP_List_Table
{
    function __construct()
    {
        parent::__construct(array(
            'singular' => 'wp_list_import_csvs',
            'plural' => 'wp_list_import_csvs',
            'ajax' => false
        ));
    }

    function column_acao($item)
    {
        // links going to /admin.php?page=[your_plugin_page][&other_params]
        // notice how we used $_REQUEST['page'], so action will be done on curren page
        // also notice how we use $this->_args['singular'] so in this example it will
        // be something like &compra=2
        $actions = array(
            'edit' => sprintf('<a href="?page=import_csvs_form&id=%s" class="button">%s</a>', $item['id'], __('Editar', 'cltd_example')),
            /*'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', $_REQUEST['page'], $item['id'], __('Apagar', 'cltd_example')),*/
        );

        return sprintf(
            '%s %s',
            '',
            $this->row_actions($actions)
        );
    }

    function column_default($item, $column_name)
    {

        switch ($column_name) {
            case 'id':
            case 'nome':
            case 'email':
            case 'acao':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    function get_columns()
    {
        $columns = array(
            'id' =>  __('ID', 'cltd_example'), //Render a checkbox instead of text
            'nome' => __('Nome', 'cltd_example'),
            'email' => __('E-mail', 'cltd_example'),
            'acao' => __('Ação', 'cltd_example'),
        );
        return $columns;
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'nome' => array('nome', true),
            'email' => array('email', false),
            'acao' => array('acao', false),
        );

        return $sortable_columns;
    }

    function prepare_items($table_name = 'wp_import_csv')
    {
        global $wpdb;

        $per_page = 10; // constant, how much records will be shown per page

        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        // here we configure table headers, defined in our methods
        $this->_column_headers = array($columns, $hidden, $sortable);

        // [OPTIONAL] process bulk action if any
        $this->process_bulk_action();

        $search = isset($_GET['s']) ? preg_replace('/\s+/', '.+', trim($_GET['s'])) : '';
        // prepare query params, as usual current page, order by and order direction
        $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] - 1) * $per_page) : 0;
        $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'id';
        $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'desc';


        // will be used in pagination settings
        $total_items = $wpdb->get_var("SELECT COUNT(id) FROM $table_name");

        $where = '1';
        if (!empty($search)) {
            $where .= " and (nome like '%{$search}%' or email like '%{$search}%') ";
        }

        $this->items = $wpdb->get_results("SELECT * FROM $table_name WHERE $where ORDER BY $orderby $order LIMIT $per_page OFFSET $paged", ARRAY_A);

        // [REQUIRED] configure pagination
        $this->set_pagination_args(array(
            'total_items' => $total_items, // total items defined above
            'per_page' => $per_page, // per page constant defined at top of method
            'total_pages' => ceil($total_items / $per_page) // calculate pages count
        ));
    }
}
