<div class="box">
	<div class="tbl-ctrls">
		<?=form_open($form_url)?>
			<fieldset class="tbl-search right">
				<a class="btn tn action" href="<?=$new_url?>"><?=lang('create_new')?></a>
			</fieldset>
			<h1><?=$cp_heading?><br><i><?=$cp_heading_desc?></i></h1>
			<?=ee('Alert')->getAllInlines()?>
			<?php if (isset($filters)) echo $filters; ?>
			<?php $this->embed('ee:_shared/table', $table); ?>
			<?=$pagination?>
			<?php if ( ! empty($table['columns']) && ! empty($table['data'])): ?>
			<fieldset class="tbl-bulk-act">
				<select name="bulk_action">
					<option value="">-- <?=lang('with_selected')?> --</option>
					<option value="remove" data-confirm-trigger="selected" rel="modal-confirm-remove-admin"><?=lang('remove')?></option>
				</select>
				<button class="btn submit" data-conditional-modal="confirm-trigger"><?=lang('submit')?></button>
			</fieldset>
			<?php endif; ?>
		<?=form_close()?>
	</div>
</div>

<?php $this->startOrAppendBlock('modals'); ?>

<?php
$modal_vars = array(
	'name'		=> 'modal-confirm-remove-admin',
	'form_url'	=> $form_url,
	'hidden'	=> array(
		'bulk_action'	=> 'remove'
	)
);

$this->embed('ee:_shared/modal_confirm_remove', $modal_vars);
?>

<?php $this->endBlock(); ?>