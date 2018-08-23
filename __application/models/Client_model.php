<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Client_model extends CI_Model
{
	function __construct(){
		parent::__construct();
		$this->load->database('cloud');
	}
	
	function account_info($request)
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
	
	function balance($request)
	{
		list($success, $return) = $this->f->is_valid_token($request);
		if (!$success)
			return [FALSE, $return];
		
		if (!isset($request->params->simpi_id) || empty($request->params->simpi_id))
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'simpi_id')]];
		
		if (!isset($request->params->client_id) || empty($request->params->client_id))
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'client_id')]];
		
		if (!isset($request->params->position_date) || empty($request->params->position_date))
			$request->params->position_date = "(select PositionDate from mobc_last_position_date where simpiID = ".$request->params->simpi_id.")";
		
		if (isset($request->params->fields) && !empty($request->params->fields))
			$this->db->select($request->params->fields);
		
		$table = "(
			select t0.simpiID, t0.PositionDate, t0.ClientID, t0.PortfolioID, t0.UnitBalance, t0.UnitPrice, t0.CostPrice, t0.UnitValue, t0.CostTotal, 
			t1.PortfolioCode, t1.PortfolioNameFull, t1.PortfolioNameShort, t1.CcyID, t2.Ccy, t2.CcyDescription 
			from ata_balance t0 
			inner join master_portfolio t1 on t0.PortfolioID = t1.PortfolioID 
			inner join parameter_securities_country t2 on t1.CcyID = t2.CountryID
			) g0 ";
		$this->db->from($table)
			->where([
				'simpiID' => $request->params->simpi_id,
				'ClientID' => $request->params->client_id,
				'PositionDate' => $request->params->position_date
			], NULL, FALSE);
			
		// $this->db->get();
		// $this->f->debug($this->db->last_query());
		return $this->f->get_result();
	}

	function transaction($request)
	{
		list($success, $return) = $this->f->is_valid_token($request);
		if (!$success)
			return [FALSE, $return];
		
		if (!isset($request->params->simpi_id) || empty($request->params->simpi_id))
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'simpi_id')]];
		
		if (!isset($request->params->client_id) || empty($request->params->client_id))
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'client_id')]];
		
		if (isset($request->params->fields) && !empty($request->params->fields))
			$this->db->select($request->params->fields);
		
		$table = "(
			select t0.simpiID, TrxID, t0.PortfolioID, t0.ClientID, t0.SalesID, SalesCode, CorrespondenceEmail, 
			TrxDate, NAVDate, TrxDescription, TrxType1, TrxAmount, TrxUnit, TrxPrice, TrxCost, AverageCost, SellingFeePercentage, 
			RedemptionFeePercentage, PortfolioCode, PortfolioNameFull, PortfolioNameShort, CcyID, Ccy, CcyDescription 
			from ata_transaction t0 
			inner join master_portfolio t1 on t0.PortfolioID = t1.PortfolioID 
			inner join parameter_securities_country t2 on t1.CcyID = t2.CountryID 
			inner join master_sales t3 on t0.SalesID = t3.SalesID
			) g0 ";
		$this->db->from($table)
			->where([
				'simpiID' => $request->params->simpi_id, 
				'ClientID' => $request->params->client_id
			], NULL, FALSE)
			->order_by('TrxDate', 'desc');
		return $this->f->get_result();
	}

}
