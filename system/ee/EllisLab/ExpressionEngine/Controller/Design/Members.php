<?php
/**
 * ExpressionEngine (https://expressionengine.com)
 *
 * @link      https://expressionengine.com/
 * @copyright Copyright (c) 2003-2018, EllisLab, Inc. (https://ellislab.com)
 * @license   https://expressionengine.com/license
 */

namespace EllisLab\ExpressionEngine\Controller\Design;

use EllisLab\ExpressionEngine\Controller\Design\AbstractDesign as AbstractDesignController;
use EllisLab\ExpressionEngine\Library\CP\Table;

/**
 * Design\Members Controller
 */
class Members extends AbstractDesignController {

	protected $template_group_map = array();

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		if ( ! ee()->cp->allowed_group('can_access_design', 'can_admin_mbr_templates'))
		{
			show_error(lang('unauthorized_access'), 403);
		}

		$this->stdHeader();

		ee()->lang->loadfile('specialty_tmp');

		$this->template_group_map = array(
			'avatar_folder_list.html' => 'profile',
			'basic_profile.html' => 'profile',
			'breadcrumb.html' => 'breadcrumb',
			'breadcrumb_current_page.html' => 'breadcrumb',
			'breadcrumb_trail.html' => 'breadcrumb',
			'browse_avatars.html' => 'profile',
			'buddies_block_list.html' => 'private_messages',
			'buddies_block_row.html' => 'private_messages',
			'bulletin.html' => 'bulletin_board',
			'bulletin_board.html' => 'bulletin_board',
			'bulletin_form.html' => 'bulletin_board',
			'copyright.html' => 'common',
			'custom_profile_fields.html' => 'member',
			'delete_confirmation_form.html' => 'account',
			'edit_avatar.html' => 'profile',
			'edit_ignore_list_form.html' => 'profile',
			'edit_ignore_list_rows.html' => 'profile',
			'edit_photo.html' => 'profile',
			'edit_preferences.html' => 'profile',
			'edit_profile_form.html' => 'profile',
			'email_form.html' => 'email',
			'email_prefs_form.html' => 'email',
			'email_user_message.html' => 'email',
			'emoticon_page.html' => 'profile',
			'empty_list.html' => 'private_messages',
			'error.html' => 'common',
			'forgot_form.html' => 'account',
			'full_profile.html' => 'profile',
			'home_page.html' => 'profile',
			'html_footer.html' => 'common',
			'html_header.html' => 'common',
			'localization_form.html' => 'account',
			'login_form.html' => 'registration',
			'member_page.html' => 'member',
			'member_results.html' => 'member',
			'member_results_row.html' => 'member',
			'memberlist.html' => 'member',
			'memberlist_rows.html' => 'member',
			'menu.html' => 'common',
			'message_attachment_link.html' => 'private_messages',
			'message_attachment_rows.html' => 'private_messages',
			'message_attachments.html' => 'private_messages',
			'message_compose.html' => 'private_messages',
			'message_edit_folders.html' => 'private_messages',
			'message_edit_folders_row.html' => 'private_messages',
			'message_error.html' => 'private_messages',
			'message_folder.html' => 'private_messages',
			'message_folder_rows.html' => 'private_messages',
			'message_menu.html' => 'private_messages',
			'message_menu_rows.html' => 'private_messages',
			'message_no_folder_rows.html' => 'private_messages',
			'message_submission_error.html' => 'private_messages',
			'message_success.html' => 'private_messages',
			'no_subscriptions_message.html' => 'subscriptions',
			'notepad_form.html' => 'profile',
			'page_header.html' => 'common',
			'page_subheader.html' => 'common',
			'password_change_warning.html' => 'account',
			'preview_message.html' => 'private_messages',
			'public_custom_profile_fields.html' => 'profile',
			'public_profile.html' => 'profile',
			'registration_form.html' => 'registration',
			'reset_password_form.html' => 'account',
			'search_members.html' => 'private_messages',
			'signature_form.html' => 'profile',
			'stylesheet.html' => 'common',
			'subscription_pagination.html' => 'subscriptions',
			'subscription_result_heading.html' => 'subscriptions',
			'subscription_result_rows.html' => 'subscriptions',
			'subscriptions_form.html' => 'subscriptions',
			'success.html' => 'common',
			'update_un_pw_form.html' => 'account',
			'username_change_disallowed.html' => 'account',
			'username_password_form.html' => 'account',
			'username_row.html' => 'account',
			'view_message.html' => 'private_messages',
		);
	}

	public function index($theme = NULL)
	{

		$path = '';
		$this->load->helper('directory');
		$theme_folders = (directory_map(PATH_THIRD_THEMES . 'member/')) ?: array();


		if ($theme)
		{
			$path = ee('Theme')->getPath('member/' . ee()->security->sanitize_filename($theme));
		}
		elseif ($theme_folders)
		{
			$theme = $theme_folders[0];
			$path = ee('Theme')->getPath('member/' . ee()->security->sanitize_filename($theme));
		}



		if ( ! is_dir($path))
		{
		//	show_error(lang('unable_to_find_templates'));
		}


		$files = (directory_map($path, TRUE)) ?: array();
		$no_results = sprintf(lang('template_path_not_user'), DOC_URL.'member/index.html#member-profile-templates');


		$vars = array();

		$base_url = ee('CP/URL')->make('design/members/index/' . $theme);

		$table = ee('CP/Table', array('autosort' => TRUE, 'subheadings' => TRUE));
		$table->setColumns(
			array(
				'template',
				'manage' => array(
					'type'	=> Table::COL_TOOLBAR
				),
			)
		);
		$table->setNoResultsText($no_results);

		//exit('a');

		$data = array();
		foreach ($files as $file)
		{
			if (strpos($file, '.') === FALSE)
			{
				continue;
			}

			$human = substr($file, 0, -strlen(strrchr($file, '.')));
			$edit_url = ee('CP/URL')->make('design/members/edit/' . $theme . '/' . $human);

			$data['profile_' . $this->template_group_map[$file]][] = array(
				array(
					'content' => (lang($human) == FALSE) ? $human : lang($human),
					'href' => $edit_url
					),
				array('toolbar_items' => array(
					'edit' => array(
						'href' => $edit_url,
						'title' => lang('edit')
					),
				))
			);
		}

		$table->setData($data);

		//exit('a');


		$vars['table'] = $table->viewData($base_url);
		$vars['form_url'] = $vars['table']['base_url'];
		$vars['themes'] = '';

		ee()->load->model('member_model');

		$themes = array();
		foreach (ee()->member_model->get_profile_templates(PATH_THIRD_THEMES . 'member/') as $dir => $name)
		{
			$themes[ee('CP/URL')->make('design/members/index/' . $dir)->compile()] = $name;
		}




		if ($theme_folders)
		{
			$vars['themes'] = form_dropdown('theme', $themes, ee('CP/URL')->make('design/members/index/' . $theme));
		}




		$this->generateSidebar('members');
		ee()->view->cp_page_title = lang('template_manager');
		ee()->view->cp_heading = lang('member_profile_templates');

		ee()->javascript->change("select[name=\'theme\']", 'window.location.href = $(this).val()');

		ee()->cp->render('design/members/index', $vars);
	}

	private function isUserDir($path)
	{
		return (strpos($path, PATH_THIRD_THEMES) === 0) ? TRUE : FALSE;
	}

	public function edit($theme, $file)
	{
		$path = ee('Theme')->getPath('member/'
			.ee()->security->sanitize_filename($theme)
			.'/'
			.ee()->security->sanitize_filename($file . '.html'));

		if ( ! file_exists($path))
		{
			show_error(lang('unable_to_find_template_file'));
		}

		if ( ! $this->isUserDir($path))
		{
			show_error(sprintf(lang('template_path_not_user'), DOC_URL.'member/index.html#member-profile-templates'));
		}

		$template_name = (lang($file) == FALSE) ? $file : lang($file);

		if ( ! empty($_POST))
		{
			if ( ! write_file($path, ee()->input->post('template_data')))
			{
				show_error(lang('error_opening_template'));
			}
			else
			{

				$alert = ee('CP/Alert')->makeInline('template-form')
					->asSuccess()
					->withTitle(lang('update_template_success'))
					->addToBody(sprintf(lang('update_template_success_desc'), $template_name));

				if (ee()->input->post('submit') == 'finish')
				{
					$alert->defer();
					ee()->functions->redirect(ee('CP/URL')->make('design/members'));
				}

				$alert->now();
			}
		}

		if ( ! is_really_writable($path))
		{
			ee('CP/Alert')->makeInline('message-warning')
				->asWarning()
				->cannotClose()
				->withTitle(lang('file_not_writable'))
				->addToBody(lang('file_writing_instructions'))
				->now();
		}

		$fp = fopen($path, 'r');
		$fstat = fstat($fp);
		fclose($fp);

		$vars = array(
			'form_url'      => ee('CP/URL')->make('design/members/edit/' . $theme . '/' . $file),
			'edit_date'     => ee()->localize->human_time($fstat['mtime']),
			'template_data' => file_get_contents($path),
		);

		$this->loadCodeMirrorAssets();

		ee()->view->cp_page_title = sprintf(lang('edit_template'), $template_name);
		ee()->view->cp_breadcrumbs = array(
			ee('CP/URL')->make('design')->compile() => lang('template_manager'),
			ee('CP/URL')->make('design/members/')->compile() => sprintf(lang('breadcrumb_group'), lang('members'))
		);

		ee()->cp->render('design/members/edit', $vars);
	}
}

// EOF
