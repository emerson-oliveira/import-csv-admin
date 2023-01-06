<?php

global $wpdb;
$table_name = 'wp_import_csv'; // do not forget about tables prefix

$message = '';
$notice = '';

function cltd_example_validate_import_csv_2($item)
{
	$messages = array();
	if (empty($item['nome'])) $messages[] = __('Nome é obrigatório', 'cltd_example');
	if (empty($item['email'])) $messages[] = __('E-mail é obrigatório', 'cltd_example');
	if (empty($item['cpf'])) $messages[] = __('CPF é obrigatório', 'cltd_example');
	if (empty($messages)) return true;
	return implode('<br />', $messages);
}

// this is default $item which will be used for new records
$default = array(
	'id' => 0,
	'nome' => null,
	'email' => null,
	'cpf' => null,
	'ano' => null,
	'mes' => null,
	'salario' => null,
);

// here we are verifying does this request is post back and have correct nonce
if (isset($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {
	// combine our default item with request params
	$item = shortcode_atts($default, $_REQUEST);
	// validate data, and if all ok save item to database
	// if id is zero insert otherwise update

	$item_valid = cltd_example_validate_import_csv_2($item);

	if ($item_valid === true) {
		$item_id = $item['id'];
		$result = $wpdb->update($table_name, $item, array('id' => $item_id));

		if ($result) {
			$message = __('O item foi atualizado com sucesso', 'cltd_example');
		} else {
			$notice = __('Ocorreu um erro ao atualizar o item', 'cltd_example');
		}
	} else {
		// if $item_valid not true it contains error message(s)
		$notice = $item_valid;
	}
} else {
	// if this is not post back we load item to edit or give new one to create
	$item = $default;
	if (isset($_REQUEST['id'])) {
		$item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $_REQUEST['id']), ARRAY_A);
		if (!$item) {
			$item = $default;
			$notice = __('Item not found', 'cltd_example');
		}
	}
}

add_meta_box('import_csvs_form_meta_box', 'Import', 'cltd_example_import_csvs_form_meta_box_handler_2', 'import_csv', 'normal', 'default');

function cltd_example_import_csvs_form_meta_box_handler_2($item)
{
?>
	<table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table">
		<tbody>			
			<tr class="form-field">
				<th valign="top" scope="row"> <label for="nome"><?php _e('Nome') ?></label></th>
				<td><input id="nome" name="nome" type="text" style="width: 95%" value="<?php echo esc_attr($item['nome']) ?>" size="50" class="code" placeholder=""></td>
			</tr>
			<tr class="form-field">
				<th valign="top" scope="row"> <label for="email"><?php _e('Email') ?></label></th>
				<td><input id="email" name="email" type="text" style="width: 95%" value="<?php echo esc_attr($item['email']) ?>" size="50" class="code" placeholder=""></td>
			</tr>
			<tr class="form-field">
				<th valign="top" scope="row"> <label for="cpf"><?php _e('CPF') ?></label></th>
				<td><input id="cpf" name="cpf" type="text" style="width: 95%" value="<?php echo esc_attr($item['cpf']) ?>" size="50" class="code" placeholder=""></td>
			</tr>
			<tr class="form-field">
				<th valign="top" scope="row"> <label for="ano"><?php _e('Ano') ?></label></th>
				<td><input id="ano" name="ano" type="text" style="width: 95%" value="<?php echo esc_attr($item['ano']) ?>" size="50" class="code" placeholder=""></td>
			</tr>
			<tr class="form-field">
				<th valign="top" scope="row"> <label for="mes"><?php _e('Mês') ?></label></th>
				<td><input id="mes" name="mes" type="text" style="width: 95%" value="<?php echo esc_attr($item['mes']) ?>" size="50" class="code" placeholder=""></td>
			</tr>
			<tr class="form-field">
				<th valign="top" scope="row"> <label for="salario"><?php _e('Salário') ?></label></th>
				<td><input id="salario" name="salario" type="text" style="width: 95%" value="<?php echo esc_attr($item['salario']) ?>" size="50" class="code" placeholder=""></td>
			</tr>
		</tbody>
	</table>
<?php } ?>
<style type="text/css">
	._dt {
		display: table;
		width: 100%;
	}

	._dtc {
		display: table-cell;
		vertical-align: middle;
	}

	._debug {
		border: 1px solid red;
	}

	div.error {
		padding: 35px 15px;
		font-size: 24px;
		background-color: rgba(255, 0, 0, 0.05);
	}

	div.error p {
		font-size: 22px;
	}
</style>
<div class="wrap">
	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<h2><?php _e('Import CSV', 'cltd_example') ?> <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=import_csvs'); ?>"><?php _e('Voltar', 'cltd_example') ?></a>
	</h2>

	<?php if (!empty($notice)) : ?>
		<div id="notice" class="error">
			<p><?php echo $notice ?></p>
		</div>
	<?php endif; ?>
	<?php if (!empty($message)) : ?>
		<div id="message" class="updated">
			<p><?php echo $message ?></p>
		</div>
	<?php endif; ?>

	<form id="form" method="POST">
		<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__)) ?>" />
		<?php /* NOTICE: here we storing id to determine will be item added or updated */ ?>
		<input type="hidden" name="id" value="<?php echo $item['id'] ?>" />

		<div class="metabox-holder" id="poststuff">
			<div id="post-body">
				<div id="post-body-content">
					<?php /* And here we call our custom meta box */ ?>
					<?php do_meta_boxes('import_csv', 'normal', $item); ?>
					<input type="submit" value="Salvar" id="submit" class="button-primary" name="submit">
				</div>
			</div>
		</div>
	</form>
</div>