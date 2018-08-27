<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Lottery_model extends CI_Model
{
	function __construct(){
		parent::__construct();
		$this->load->database('localhost');
	}
	
	function receipt($request)
	{
		list($success, $return) = $this->f->is_valid_token($request);
		if (!$success) 
			return [FALSE, $return];
		
		if (!isset($request->params->email) || empty($request->params->email))
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'email')]];
		
		if (isset($request->params->fields) && !empty($request->params->fields))
			$this->db->select($request->params->fields);
		
		$this->db->from('master_client')->where(['CorrespondenceEmail' => $request->params->email]);
		return $this->f->get_row();
	}
	
}
