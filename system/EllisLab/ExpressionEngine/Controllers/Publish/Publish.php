<?php

namespace EllisLab\ExpressionEngine\Controllers\Publish;

use EllisLab\ExpressionEngine\Controllers\Publish\AbstractPublish as AbstractPublishController;

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2015, EllisLab, Inc.
 * @license		http://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 3.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ExpressionEngine CP Publish Class
 *
 * @package		ExpressionEngine
 * @subpackage	Control Panel
 * @category	Control Panel
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class Publish extends AbstractPublishController {

	public function autosave($channel_id, $entry_id)
	{
		$site_id = ee()->config->item('site_id');

		$autosave = ee('Model')->get('ChannelEntryAutosave')
			->filter('original_entry_id', $entry_id)
			->filter('site_id', $site_id)
			->filter('channel_id', $channel_id)
			->first();

		if ( ! $autosave)
		{
			$autosave = ee('Model')->make('ChannelEntryAutosave');
			$autosave->original_entry_id = $entry_id;
			$autosave->site_id = $site_id;
			$autosave->channel_id = $channel_id;
		}

		$autosave->edit_date = ee()->localize->now;
		$autosave->entry_data = $_POST;

		// This is currently unused, but might be useful for display purposes
		$autosave->author_id = ee()->input->post('author_id');

		// This group of columns is unused
		$autosave->title = (ee()->input->post('title')) ?: 'autosave_' . ee()->localize->now;
		$autosave->url_title = (ee()->input->post('url_title')) ?: 'autosave_' . ee()->localize->now;
		$autosave->status = ee()->input->post('status');

		// This group of columns is also unused
		$autosave->entry_date = 0;
		$autosave->year = 0;
		$autosave->month = 0;
		$autosave->day = 0;

		$autosave->save();

		$time = ee()->localize->human_time(ee()->localize->now);
		$time = trim(strstr($time, ' '));

		$alert = ee('Alert')->makeInline()
			->asWarning()
			->cannotClose()
			->addToBody(lang('autosave_success') . $time);

		ee()->output->send_ajax_response(array(
			'success' => $alert->render(),
			'autosave_entry_id' => $autosave->entry_id,
			'original_entry_id'	=> $entry_id
		));
	}


	public function create($channel_id, $autosave_id = NULL)
	{
		$channel = ee('Model')->get('Channel', $channel_id)
			->filter('site_id', ee()->config->item('site_id'))
			->first();

		if (!$channel)
		{
			show_error(lang('no_channel_exists'));
		}

		$entry = ee('Model')->make('ChannelEntry');
		$entry->channel_id = $channel_id;
		$entry->site_id =  ee()->config->item('site_id');
		$entry->author_id = ee()->session->userdata('member_id');
		$entry->ip_address = ee()->session->userdata['ip_address'];

		ee()->view->cp_page_title = sprintf(lang('create_entry_with_channel_name'), $channel->channel_title);

		$form_attributes = array(
			'class' => 'settings ajax-validate',
		);

		$vars = array(
			'form_url' => cp_url('publish/create/' . $channel_id),
			'form_attributes' => $form_attributes,
			'errors' => new \EllisLab\ExpressionEngine\Service\Validation\Result,
			'button_text' => lang('btn_publish')
		);

		if ($autosave_id)
		{
			$autosaved = ee('Model')->get('ChannelEntryAutosave', $autosave_id)
				->filter('site_id', ee()->config->item('site_id'))
				->first();

			if ($autosaved)
			{
				$entry->set($autosaved->entry_data);
			}
		}

		if (count($_POST))
		{
			$entry->set($_POST);
			$result = $entry->validate();

			if (AJAX_REQUEST)
			{
				$field = ee()->input->post('ee_fv_field');
				// Remove any namespacing to run validation for the parent field
				$field = preg_replace('/\[.+?\]/', '', $field);

				if ($result->hasErrors($field))
				{
					ee()->output->send_ajax_response(array('error' => $result->renderError($field)));
				}
				else
				{
					ee()->output->send_ajax_response('success');
				}
				exit;
			}

			if ($result->isValid())
			{
				$entry->save();

				ee('Alert')->makeInline('entry-form')
					->asSuccess()
					->withTitle(lang('create_entry_success'))
					->addToBody(sprintf(lang('create_entry_success_desc'), $entry->title))
					->defer();

				ee()->functions->redirect(cp_url('publish/edit/entry/' . $entry->entry_id, ee()->cp->get_url_state()));
			}
			else
			{
				$vars['errors'] = $result;
				// Hacking
				ee()->load->library('form_validation');
				ee()->form_validation->_error_array = $result->renderErrors();
				ee('Alert')->makeInline('entry-form')
					->asIssue()
					->withTitle(lang('create_entry_error'))
					->addToBody(lang('create_entry_error_desc'))
					->now();
			}
		}

		$channel_layout = ee('Model')->get('ChannelLayout')
			->filter('site_id', ee()->config->item('site_id'))
			->filter('channel_id', $channel_id)
			->with('MemberGroups')
			->filter('MemberGroups.group_id', ee()->session->userdata['group_id'])
			->first();

		$vars = array_merge($vars, array(
			'entry' => $entry,
			'layout' => $entry->getDisplay($channel_layout),
		));

		$this->setGlobalJs($entry, TRUE);

		ee()->cp->add_js_script(array(
			'plugin' => array(
				'ee_url_title',
				'ee_filebrowser',
				'ee_fileuploader',
			),
			'file' => array('cp/v3/publish')
		));

		ee()->cp->render('publish/edit/entry', $vars);
	}
}
// EOF