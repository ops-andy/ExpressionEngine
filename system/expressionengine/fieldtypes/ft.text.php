<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2003 - 2011, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */

// --------------------------------------------------------------------

/**
 * ExpressionEngine Text Fieldtype Class
 *
 * @package		ExpressionEngine
 * @subpackage	Fieldtypes
 * @category	Fieldtypes
 * @author		ExpressionEngine Dev Team
 * @link		http://expressionengine.com
 */
class Text_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> 'Text Input',
		'version'	=> '1.0'
	);

	// Parser Flag (preparse pairs?)
	var $has_array_data = FALSE;

	/**
	 * Constructor
	 *
	 * @access	public
	 */
	function Text_ft()
	{
		parent::EE_Fieldtype();
	}
	
	// --------------------------------------------------------------------
	
	function validate($data)
	{
		if ($data == '')
		{
			return TRUE;
		}
		
		if ( ! isset($this->field_content_types))
		{
			$this->EE->load->model('field_model');
			$this->field_content_types = $this->EE->field_model->get_field_content_types();
		}

		if ( ! isset($this->settings['field_content_type']))
		{
			return TRUE;
		}

		$content_type = $this->settings['field_content_type'];
		
		if (in_array($content_type, $this->field_content_types['text']) && $content_type != 'any')
		{
			if ( ! $this->EE->form_validation->$content_type($data))
			{
				return $this->EE->lang->line($content_type);
			}
		}
		
		return TRUE;
	}
	
	// --------------------------------------------------------------------
	
	function display_field($data)
	{
		return form_input(array(
			'name'		=> $this->field_name,
			'id'		=> $this->field_name,
			'value'		=> $data,
			'dir'		=> $this->settings['field_text_direction'],
			'maxlength'	=> $this->settings['field_maxl'], 
			'field_content_type' => $this->settings['field_content_type']
		));
	}
	
	// --------------------------------------------------------------------
	
	function replace_tag($data, $params = '', $tagdata = '')
	{
		if ($data !== '' && isset($params['number_format']) && ctype_digit(str_replace('.', '', $data)) === TRUE)
		{
			$data = number_format($data, $params['number_format']);
		}

		return $this->EE->typography->parse_type(
			$this->EE->functions->encode_ee_tags($data),
			array(
				'text_format'	=> $this->row['field_ft_'.$this->field_id],
				'html_format'	=> $this->row['channel_html_formatting'],
				'auto_links'	=> $this->row['channel_auto_link_urls'],
				'allow_img_url' => $this->row['channel_allow_img_urls']
			)
		);
	}
	
	// --------------------------------------------------------------------

	function display_settings($data)
	{
		$prefix = 'text';
		$field_maxl			= ($data['field_maxl'] == '') ? 128 : $data['field_maxl'];
		//$field_content_text	= ($data['field_content_text'] == '') ? 'any' : $data['field_content_text'];
		$field_content_options = array('all' => lang('all'), 'numeric' => lang('numeric'), 'integer' => lang('integer'));
		
		$extra = '';

		if ($data['field_id'] != '')
		{
			$extra .= '<div class="notice update_content_type js_hide">';
			$extra .= '<p>'.sprintf(
								lang('content_type_changed'), 
								$data['field_content_type']).'</p></div>';
		}
		
		
		$this->EE->table->add_row(
			lang('field_max_length', 'field_max1'),
			form_input(array('id'=>'field_maxl','name'=>'field_maxl', 'size'=>4,'value'=>$field_maxl))
		);
		
		$this->field_formatting_row($data, $prefix);
		$this->text_direction_row($data, $prefix);
		
		$this->EE->table->add_row(
			lang('field_content_type', 'field_content_type'),
			form_dropdown('field_content_type', $field_content_options, $data['field_content_type'], 'id="text_field_content_type"').$extra
		);

		$this->field_show_smileys_row($data, $prefix);
		$this->field_show_glossary_row($data, $prefix);
		$this->field_show_spellcheck_row($data, $prefix);
		$this->field_show_file_selector_row($data, $prefix);
		
		$this->EE->javascript->output('
		$("#text_field_content_type").change(function() {
			$(this).nextAll(".update_content_type").show();
		});
		');		
		
		
	}

	// --------------------------------------------------------------------

	function save_settings($data)
	{		
		return array(
			'field_maxl'			=> $this->EE->input->post('field_maxl'),
			'field_content_text'	=> $this->EE->input->post('field_content_text'),
			'field_content_type'	=> $this->EE->input->post('field_content_type')
		);
	}
	

	// --------------------------------------------------------------------
	
	function settings_modify_column($data)
	{

		$settings = unserialize(base64_decode($data['field_settings']));

		switch($settings['field_content_type'])
		{
			case 'numeric':
				$fields['field_id_'.$data['field_id']]['type'] = 'FLOAT';
				$fields['field_id_'.$data['field_id']]['default'] = 0;
				break;
			case 'integer':
				$fields['field_id_'.$data['field_id']]['type'] = 'INT';
				$fields['field_id_'.$data['field_id']]['default'] = 0;
				break;
			default:
				$fields['field_id_'.$data['field_id']]['type'] = 'text';
				$fields['field_id_'.$data['field_id']]['null'] = TRUE;
		}
		
		return $fields;
	}
}

// END Text_Ft class

/* End of file ft.text.php */
/* Location: ./system/expressionengine/fieldtypes/ft.text.php */