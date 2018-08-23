<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}
//
class Mservices extends CI_Model{
	function __construct(){
		parent::__construct();
		$this->auth = unserialize(base64_decode($this->session->userdata('pgasolklender')));
	}
	
	function getdata($type="", $balikan="", $p1="", $p2="",$p3="",$p4=""){
		$where  = " WHERE 1=1 ";
		$where2 = "";
		
		$dbdriver = $this->db->dbdriver;
		if($dbdriver == "postgre"){
			$select = " ROW_NUMBER() OVER (ORDER BY A.id DESC) as rowID, ";
		}else{
			$select = "";
		}
		
		switch($type){
			case "currency":
				$sql = "
					SELECT DISTINCT B.FundCcy
					FROM tbl_saldo A
					LEFT JOIN tbl_fund B ON B.FundID = A.FundID
					WHERE A.ClientID = '".$p1."'
				";
			break;
			
			case "redeemlog":
				$sql = "
					SELECT A.*
					FROM tbl_order_redeem_log A
					WHERE A.OrderID = '".$p1."'
					ORDER BY A.LogDatetime DESC
				";
			break;
			case "redeem":
				$sql = "
					SELECT A.*, B.*, C.StatusCode
					FROM tbl_order_redeem A
					LEFT JOIN tbl_fund B ON B.FundID = A.FundID
					LEFT JOIN tbl_parameter_status_redeem C ON C.StatusID = A.StatusID
					WHERE A.ClientID = '".$p1."'
					ORDER BY A.OrderDate DESC
				";
			break;
			case "fundbankaccount":
				$sql = "
					SELECT A.*, B.FundCode
					FROM tbl_fund_bankaccount A
					LEFT JOIN tbl_fund B ON B.FundID = A.FundID
					WHERE A.FundID = '".$p1."'
				";
			break;
			case "fund":
				$sql = "
					SELECT A.*, B.*
					FROM tbl_fund A
				";
			break;
			case "subscribelog":
				$sql = "
					SELECT A.*
					FROM tbl_order_subscribe_log A
					WHERE A.OrderID = '".$p1."'
					ORDER BY A.LogDatetime DESC
				";
			break;
			case "subscribe":
				$sql = "
					SELECT A.*, B.*, 
						C.*, D.StatusCode
					FROM tbl_fund A
					LEFT JOIN tbl_order_subscribe B ON B.FundID = A.FundID
					LEFT JOIN tbl_fund_bankaccount C ON C.AccountID = B.AccountID
					LEFT JOIN tbl_parameter_status_subscribe D ON D.StatusID = B.StatusID
					WHERE B.ClientID = '".$p1."'
					ORDER BY B.OrderDate DESC
				";
			break;
			case "transaksi":
				$sql = "
					SELECT A.*, B.*, C.TypeCode
					FROM tbl_fund A
					LEFT JOIN tbl_transaction B ON B.FundID = A.FundID
					LEFT JOIN tbl_parameter_trxtype C ON C.TypeID = B.TypeID
					WHERE B.ClientID = '".$p1."'
					ORDER BY B.TrxDate DESC
				";
			break;
			case "saldo":
				$sql = "
					SELECT A.*, B.*
					FROM tbl_fund A
					LEFT JOIN tbl_saldo B ON B.FundID = A.FundID
					WHERE B.ClientID = '".$p1."'
				";
			break;
			case "akun_data":
				$sql = "
					SELECT A.*, B.StatusCode
					FROM tbl_client A
					LEFT JOIN tbl_parameter_status_client B ON B.StatusID = A.StatusID
					WHERE ClientID = '".$p1."'
				";
			break;
			case "login_user":
				$sql = "
					SELECT A.*
					FROM tbl_client A
					WHERE ClientEmail = '".$p1."'
				";
			break;
			default:
				if($balikan=='get'){$where .=" AND A.id=".$this->input->post('id');}
				$sql="SELECT $select A.* FROM ".$type." A ".$where;
				if($balikan=='get')return $this->db->query($sql)->row_array();
			break;
		}
		
		if($balikan == 'json'){
			return $this->lib->json_grid($sql,$type);
		}elseif($balikan == 'row_array'){
			return $this->db->query($sql)->row_array();
		}elseif($balikan == 'result'){
			return $this->db->query($sql)->result();
		}elseif($balikan == 'result_array'){
			return $this->db->query($sql)->result_array();
		}elseif($balikan == 'json_encode'){
			$data = $this->db->query($sql)->result_array(); 
			return json_encode($data);
		}elseif($balikan == 'variable'){
			return $array;
		}
		
	}
		
}
