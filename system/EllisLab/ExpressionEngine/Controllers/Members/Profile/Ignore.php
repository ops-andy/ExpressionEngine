<?php

namespace EllisLab\ExpressionEngine\Controllers\Members\Profile;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use CP_Controller;
use EllisLab\ExpressionEngine\Library\CP\URL;
use EllisLab\ExpressionEngine\Library\CP\Table;
use EllisLab\ExpressionEngine\Library\CP\Pagination;

/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2014, EllisLab, Inc.
 * @license		http://ellislab.com/expressionengine/user-guide/license.html
 * @link		http://ellislab.com
 * @since		Version 3.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * ExpressionEngine CP Member Profile Ignore Settings Class
 *
 * @package		ExpressionEngine
 * @subpackage	Control Panel
 * @category	Control Panel
 * @author		EllisLab Dev Team
 * @link		http://ellislab.com
 */
class Ignore extends Profile {

	private $base_url = 'members/profile/ignore';

	public function __construct()
	{
		parent::__construct();
		ee()->load->model('member_model');
		$this->index_url = $this->base_url;
		$this->base_url = new URL($this->base_url, ee()->session->session_id(), $this->query_string);
		$this->ignore_list = explode('|', $this->member->ignore_list);
	}

	/**
	 * Ignore index
	 */
	public function index()
	{
		$order_by = ($this->config->item('memberlist_order_by')) ? $this->config->item('memberlist_order_by') : 'member_id';
		$sort = ($this->config->item('memberlist_sort_order')) ? $this->config->item('memberlist_sort_order') : 'asc';
		$perpage = $this->config->item('memberlist_row_limit');
		$sort_col = ee()->input->get('sort_col') ?: $order_by;
		$sort_dir = ee()->input->get('sort_dir') ?: $sort;
		$page = ee()->input->get('page') > 0 ? ee()->input->get('page') : 1;

		$table = Table::create(array(
			'sort_col' => $sort_col,
			'sort_dir' => $sort_dir,
			'limit' => $perpage
		));

		$ignored = array();
		$data = array();
		$members = ee()->api->get('Member', $this->ignore_list)->order($sort_col, $sort_dir);

		if ( ! empty($search = ee()->input->post('search')))
		{
			$members = $members->filter('screen_name', 'LIKE', "%$search%");
		}

		$members = $members->limit($perpage)->offset(($page - 1) * $perpage)->all();

		if (count($members) > 0)
		{
			foreach ($members as $member)
			{
				$attributes = array();
				$group = $member->getMemberGroup()->group_title;

				if ($group == 'Banned')
				{
					$group = "<span class='st-banned'>" . lang('banned') . "</span>";
					$attributes['class'] = 'alt banned';
				}

				$email = "<a href = '" . cp_url('utilities/communicate') . "'>e-mail</a>";
				$ignored[] = array(
					'columns' => array(
						'id' => $member->member_id,
						'username' => "{$member->screen_name} ($email)",
						'member_group' => $group,
						array(
							'name' => 'selection[]',
							'value' => $member->member_id,
							'data'	=> array(
								'confirm' => lang('member') . ': <b>' . htmlentities($member->screen_name, ENT_QUOTES) . '</b>'
							)
						)
					),
					'attrs' => $attributes
				);
			}
		}

		$table->setColumns(
			array(
				'id',
				'username',
				'member_group',
				array(
					'type'	=> Table::COL_CHECKBOX
				)
			)
		);

		$table->setNoResultsText('no_search_results');
		$table->setData($ignored);

		$data['table'] = $table->viewData($this->base_url);

		// Set search results heading
		if ( ! empty($data['table']['search']))
		{
			ee()->view->cp_heading = sprintf(
				lang('search_results_heading'),
				$data['table']['total_rows'],
				$data['table']['search']
			);
		}

		if ( ! empty($data['table']['data']))
		{
			$pagination = new Pagination(
				$perpage,
				count($this->ignore_list),
				$page
			);
			$data['pagination'] = $pagination->cp_links($this->base_url);
		}

		$data['form_url'] = cp_url('members/profile/ignore/delete', $this->query_string);

		ee()->javascript->set_global('lang.remove_confirm', lang('members') . ': <b>### ' . lang('members') . '</b>');
		ee()->cp->add_js_script(array(
			'file' => array('cp/v3/confirm_remove'),
		));

		ee()->view->base_url = $this->base_url;
		ee()->view->cp_page_title = lang('blocked_members');
		ee()->cp->render('account/ignore_list', $data);
	}

	/**
	 * Remove users from ignore list 
	 * 
	 * @access public
	 * @return void
	 */
	public function delete()
	{
		$selection = $this->input->post('selection');
		$ignore = implode('|', array_diff($this->ignore_list, $selection));
		$this->member->ignore_list = $ignore;
		$this->member->save();

		ee()->functions->redirect(cp_url($this->index_url, $this->query_string));
	}

}
// END CLASS

/* End of file Ignore.php */
/* Location: ./system/expressionengine/controllers/cp/Members/Profile/Ignore.php */