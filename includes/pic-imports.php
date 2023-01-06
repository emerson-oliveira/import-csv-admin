<?php
$load_csv_table = new Load_Import_CSV_List_Table();
$load_csv_table->prepare_items();
$screen = get_current_screen();
?>
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

	tr .row-actions {
		position: static;
	}
</style>

<div class="wrap">

	<div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
	<h2><?php _e('Import CSV', 'cltd_example') ?>
	</h2>

	<div class="_dt">
		<div class="_dtc" style="width: 40%;">
			<form method="post" id="import_csv_form" action="" enctype="multipart/form-data">
				<input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce(get_stylesheet_directory_uri() . '/import_csv/imports.php') ?>" />
				<div class="_dt">
					<div class="_dtc" style="width: 70%;">
						<input class="form-control" type="file" id="import_csv_file" name="import_csv_file" accept=".csv" required>
					</div>
					<div class="_dtc" style="width: 30%;">
						<input type="submit" name="import_csv_csv" class="button-primary" value="Import CSV" />
					</div>
				</div>
			</form>
		</div>
		<div class="_dtc">
			<form id="imports-filter" method="get">
				<?php $load_csv_table->search_box('Pesquisar', 'search'); ?>
			</form>
		</div>
	</div>

	<form id="compras-table" method="GET">
		<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
		<?php $load_csv_table->display(); ?>
	</form>

</div>