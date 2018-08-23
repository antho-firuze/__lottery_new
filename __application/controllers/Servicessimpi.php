<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

//Controller1 Utk aa goy
class Servicessimpi extends CI_Controller {
	
	function __construct(){
		parent::__construct();
		$this->load->model('mservices');
		$this->load->library(array('encryption','lib','f'));
		$this->host	= $this->config->item('base_url');
		$this->nsmarty->assign('host',$this->host);
		$this->nsmarty->assign('acak', md5(date('H:i:s')) );		
	}
		
	function index(){
		echo "everything oke";
	}
	
	function oye(){
		echo $this->lib->uniq_id();
	}
	
	function login(){
		$user = $this->input->post('username');
		$pass = $this->input->post('password');
		
		$data = [];
		$result = [];
		
		if (isset($user) && isset($pass)){
			if ($user != "" && $pass !=  ""){
				$getdata = $this->mservices->getdata("login_user", "row_array", $user);
				
				if($getdata){
					if($pass == $this->encryption->decrypt($getdata["ClientPassword"]) ){
						$KeyLog = $this->lib->uniq_id();
						
						$data['ClientID'] = $getdata["ClientID"];
						$data['ClientEmail'] = $getdata["ClientEmail"];
						$data['ClientCIF'] = $getdata["ClientCIF"];
						$data['ClientName'] = $getdata["ClientName"];
						$data['ClientHP'] = $getdata["ClientHP"];
						$data['KeyLog'] = $KeyLog;
		
		// $this->f->debug($data);
						
						$this->db->update("tbl_client", array("KeyLog"=>$KeyLog), array("ClientID"=>$getdata["ClientID"]) );
						
						$respon = 1;
					}else{
						$data['pesan'] = "Password Salah";
						$respon = 0;
					}
				}else{
					$data['pesan'] = "User Tidak Ditemukan";
					$respon = 0;
				}
			}else{
				$data['pesan'] = "User & Password Tidak Boleh Kosong";
				$respon = 0;
			}
		}else{
			$data['pesan'] = "Error!";
			$respon = 0;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}
	
	function imagelogo(){
		$data = [];
		$result = [];
		
		$data["logo"] = $this->host."__repository/logo/logo.png";
		$data["splashscreen"] = $this->host."__repository/logo/splashscreen.png";
		$respon = 1;
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}
	
	function imageslider(){
		$data = [];
		$result = [];
		
		$file = preg_grep('/^([^.])/', scandir('./__repository/slider/', 1));
		if($file){
			$count = (count($file)-1);
			for($i = 0; $i <= $count; $i++ ){
				$data[$i]["url"] = $this->host."__repository/slider/".$file[$i];
			}
			$respon = 1;
		}else{
			$data["pesan"] = "Tidak Ada File Slider";
			$respon = 0;
		}
		 
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}
	
	function akundata(){
		$clientID = $this->input->post('ClientID');
		$KeyLog = $this->input->post('KeyLog');
		$data = [];
		$result = [];
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($clientID)){
				$getdata = $this->mservices->getdata("akun_data", "row_array", $clientID);
					
				if($getdata){
					$data['ClientID'] = (int)$getdata["ClientID"];
					$data['ClientCIF'] = $getdata["ClientCIF"];
					$data['ClientEmail'] = $getdata["ClientEmail"];
					$data['ClientName'] = $getdata["ClientName"];
					$data['ClientHP'] = $getdata["ClientHP"];
					$data['StatusCode'] = $getdata["StatusCode"];
					$data['StatusDescription'] = $getdata["StatusDescription"];
					$data['DateUpdate'] = $getdata["UpdateDate"];
					$respon = 1;
				}else{
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}
	
	function currency(){
		$clientID = $this->input->post('ClientID');
		$KeyLog = $this->input->post('KeyLog');
		$data = [];
		$result = [];
		
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($clientID)){
				$getdata = $this->mservices->getdata("currency", "result_array", $clientID);
					
				if($getdata){
					foreach($getdata as $k => $v){
						$data[$k]['FundCcy'] = $v["FundCcy"];
					}
					$respon = 1;
				}else{
					$data["pesan"] = "Data Tidak Ada";
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}

	function saldo(){
		$clientID = $this->input->post('ClientID');
		$KeyLog = $this->input->post('KeyLog');
		$data = [];
		$result = [];
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($clientID)){
				$getdata = $this->mservices->getdata("saldo", "result_array", $clientID);
					
				if($getdata){
					foreach($getdata as $k => $v){
						$data[$k]['FundID'] = $v["FundID"];
						$data[$k]['FundCode'] = $v["FundCode"];
						$data[$k]['FundName'] = $v["FundName"];
						$data[$k]['FundImage'] =  $this->host."__repository/fund/".$v["FundImage"];
						$data[$k]['FundCcy'] = $v["FundCcy"];
						$data[$k]['DataDate'] = $v["DataDate"];
						$data[$k]['DataUnit'] = $v["DataUnit"];
						$data[$k]['DataPrice'] = $v["DataPrice"];
						$data[$k]['DataValue'] = $v["DataValue"];
						$data[$k]['DataCost'] = $v["DataCost"];
						$data[$k]['DateUpdate'] = $v["UpdateDate"];
						$data[$k]["pesan"] = "Berhasil";
					}
					$respon = 1;
				}else{
					$data["pesan"] = "Data Tidak Ada";
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}
	
	function transaksi(){
		$clientID = $this->input->post('ClientID');
		$KeyLog = $this->input->post('KeyLog');
		$data = [];
		$result = [];
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($clientID)){
				$getdata = $this->mservices->getdata("transaksi", "result_array", $clientID);
					
				if($getdata){
					foreach($getdata as $k => $v){
						$data[$k]['FundCode'] = $v["FundCode"];
						$data[$k]['FundName'] = $v["FundName"];
						$data[$k]['FundImage'] = $this->host."__repository/fund/".$v["FundImage"];
						$data[$k]['FundCcy'] = $v["FundCcy"];
						$data[$k]['TrxID'] = $v["TrxID"];
						$data[$k]['TrxDate'] = $v["TrxDate"];
						$data[$k]['TypeCode'] = $v["TypeCode"];
						$data[$k]['TrxUnit'] = (int)$v["TrxUnit"];
						$data[$k]['TrxPrice'] =(int) $v["TrxPrice"];
						$data[$k]['TrxAmount'] = (int)$v["TrxAmount"];
						$data[$k]['TrxFeePercent'] = (int)$v["TrxFeePercent"];
						$data[$k]['TrxFeeAmount'] = (int)$v["TrxFeeAmount"];
						$data[$k]['TrxNet'] = (int)$v["TrxNet"];
						$data[$k]['DateUpdate'] = $v["UpdateDate"];
						$data[$k]["pesan"] = "Berhasil";
					}
					
					$respon = 1;
				}else{
					$data["pesan"] = "Data Tidak Ada";
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}	
	
	function subscribe(){
		$clientID = $this->input->post('ClientID');
		$KeyLog = $this->input->post('KeyLog');
		$data = [];
		$result = [];
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($clientID)){
				$getdata = $this->mservices->getdata("subscribe", "result_array", $clientID);
					
				if($getdata){
					foreach($getdata as $k => $v){
						$data[$k]['OrderID'] = $v["OrderID"];
						$data[$k]['FundCode'] = $v["FundCode"];
						$data[$k]['FundName'] = $v["FundName"];
						$data[$k]['FundImage'] =  $this->host."__repository/fund/".$v["FundImage"];
						$data[$k]['FundCcy'] = $v["FundCcy"];
						$data[$k]['OrderDate'] = $v["OrderDate"];
						$data[$k]['StatusCode'] = $v["StatusCode"];
						$data[$k]['StatusDescription'] = $v["StatusDescription"];
						$data[$k]['OrderAmount'] = (int)$v["OrderAmount"];
						$data[$k]['BankName'] = $v["BankName"];
						$data[$k]['AccountNo'] = $v["AccountNo"];
						$data[$k]['TransferProofFile'] = $this->host."__repository/subscribe/".$v["TransferProofFile"];
						$data[$k]['DateUpdate'] = $v["UpdateDate"];
						
						$data[$k]['HistoryOrder'] = array();
						$log = $this->mservices->getdata("subscribelog", "result_array", $v["OrderID"]);
						foreach($log as $x => $r){
							$data[$k]['HistoryOrder'][$x]["LogID"] = $r["LogID"];
							$data[$k]['HistoryOrder'][$x]["StatusCode"] = $r["StatusID"];
							$data[$k]['HistoryOrder'][$x]["LogDatetime"] = $r["LogDatetime"];
						}
						
						$data[$k]["pesan"] = "Berhasil";
					}
					
					$respon = 1;
				}else{
					$data["pesan"] = "Data Tidak Ada";
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}	

	function subscribelog(){
		$OrderID = $this->input->post('OrderID');
		$KeyLog = $this->input->post('KeyLog');
		$data = "";
		$result = "";
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($OrderID)){
				$getdata = $this->mservices->getdata("subscribelog", "result_array", $OrderID);
					
				if($getdata){
					foreach($getdata as $k => $v){
						$data[$k]['OrderID'] = $v["OrderID"];
						$data[$k]['StatusCode'] = $v["StatusCode"];
						$data[$k]['LogDatetime'] = $v["LogDatetime"];
						$data[$k]["pesan"] = "Berhasil";
					}
					
					$respon = 1;
				}else{
					$data["pesan"] = "Data Tidak Ada";
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}	

	function fund(){
		$KeyLog = $this->input->post('KeyLog');
		$data = "";
		$result = "";
			
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			$getdata = $this->mservices->getdata("fund", "result_array");
			if($getdata){
				foreach($getdata as $k => $v){
					$data[$k]['FundID'] = $v["FundID"];
					$data[$k]['FundCode'] = $v["FundCode"];
					$data[$k]['FundName'] = $v["FundName"];
					$data[$k]['FundCcy'] = $v["FundCcy"];
					$data[$k]['FundImage'] = $this->host."__repository/fund/".$v["FundImage"];
					$data[$k]["pesan"] = "Berhasil";
				}
				$respon = 1;
			}else{
				$data["pesan"] = "Data Tidak Ada";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}	
	
	function fundbankaccount(){
		$FundID = $this->input->post('FundID');
		$KeyLog = $this->input->post('KeyLog');
		$data = "";
		$result = "";
		
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($FundID)){
				$getdata = $this->mservices->getdata("fundbankaccount", "result_array", $FundID);
					
				if($getdata){
					foreach($getdata as $k => $v){
						$data[$k]['AccountID'] = $v["AccountID"];
						$data[$k]['FundCode'] = $v["FundCode"];
						$data[$k]['BankName'] = $v["BankName"];
						$data[$k]['AccountNo'] = $v["AccountNo"];
						$data[$k]['AccountName'] = $v["AccountName"];
						$data[$k]["pesan"] = "Berhasil";
					}
					$respon = 1;
				}else{
					$data["pesan"] = "Data Tidak Ada";
					$respon = 0;
				}
			}else{
				$data["pesan"] = "Data Tidak Ada";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}	

	function fundredeem(){
		$clientID = $this->input->post('ClientID');
		$KeyLog = $this->input->post('KeyLog');
		$data = "";
		$result = "";
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($clientID)){
				$getdata = $this->mservices->getdata("saldo", "result_array", $clientID);
					
				if($getdata){
					foreach($getdata as $k => $v){
						$data[$k]['FundID'] = $v["FundID"];
						$data[$k]['FundCode'] = $v["FundCode"];
						$data[$k]['FundName'] = $v["FundName"];
						$data[$k]['FundImage'] =  $this->host."__repository/fund/".$v["FundImage"];
						$data[$k]['FundCcy'] = $v["FundCcy"];
						$data[$k]['DataDate'] = $v["DataDate"];
						$data[$k]['DataUnit'] = $v["DataUnit"];
						$data[$k]['DataPrice'] = $v["DataPrice"];
						$data[$k]['DataValue'] = $v["DataValue"];
						$data[$k]['DataCost'] = $v["DataCost"];
						$data[$k]['DateUpdate'] = $v["UpdateDate"];
						$data[$k]["pesan"] = "Berhasil";
					}
					$respon = 1;
				}else{
					$data["pesan"] = "Data Tidak Ada";
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}	
	
	function redeem(){
		$clientID = $this->input->post('ClientID');
		$KeyLog = $this->input->post('KeyLog');
		$data = "";
		$result = "";
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($clientID)){
				$getdata = $this->mservices->getdata("redeem", "result_array", $clientID);
					
				if($getdata){
					foreach($getdata as $k => $v){
						$data[$k]['OrderID'] = $v["OrderID"];
						$data[$k]['FundCode'] = $v["FundCode"];
						$data[$k]['FundName'] = $v["FundName"];
						$data[$k]['FundImage'] =  $this->host."__repository/fund/".$v["FundImage"];
						$data[$k]['FundCcy'] = $v["FundCcy"];
						$data[$k]['OrderDate'] = $v["OrderDate"];
						$data[$k]['StatusCode'] = $v["StatusCode"];
						$data[$k]['StatusDescription'] = $v["StatusDescription"];
						$data[$k]['OrderAmount'] = $v["OrderAmount"];
						$data[$k]['OrderUnit'] = $v["OrderUnit"];
						$data[$k]['DateUpdate'] = $v["UpdateDate"];
						
						$data[$k]['HistoryOrder'] = array();
						$log = $this->mservices->getdata("redeemlog", "result_array", $v["OrderID"]);
						foreach($log as $x => $r){
							$data[$k]['HistoryOrder'][$x]["LogID"] = $r["LogID"];
							$data[$k]['HistoryOrder'][$x]["StatusCode"] = $r["StatusID"];
							$data[$k]['HistoryOrder'][$x]["LogDatetime"] = $r["LogDatetime"];
						}
						
						$data[$k]["pesan"] = "Berhasil";
					}
					
					$respon = 1;
				}else{
					$data["pesan"] = "Data Tidak Ada";
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}	
	
	function redeemlog(){
		$OrderID = $this->input->post('OrderID');
		$KeyLog = $this->input->post('KeyLog');
		$data = "";
		$result = "";
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($OrderID)){
				$getdata = $this->mservices->getdata("redeemlog", "result_array", $OrderID);
					
				if($getdata){
					foreach($getdata as $k => $v){
						$data[$k]['OrderID'] = $v["OrderID"];
						$data[$k]['StatusCode'] = $v["StatusCode"];
						$data[$k]['LogDatetime'] = $v["LogDatetime"];
						$data[$k]["pesan"] = "Berhasil";
					}
					
					$respon = 1;
				}else{
					$data["pesan"] = "Data Tidak Ada";
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}	
	
	function forget_password(){
		$ClientEmail = $this->input->post('ClientEmail');
		$data = "";
		$result = "";
		
		if (isset($ClientEmail)){
			$dataakun = $this->db->get_where("tbl_client", array("ClientEmail"=>$ClientEmail) )->row_array();
			if($dataakun){
				$password_asli = $this->encryption->decrypt($dataakun["ClientPassword"]);
				$email = $this->lib->kirimemail('email_forgot_password', $dataakun["ClientEmail"], $dataakun["ClientEmail"], $password_asli);
				if($email){
					$data["pesan"] = "Berhasil Mengirim Password";
					$respon = 1;
				}else{
					$data["pesan"] = "Tidak Dapat Mengirim Email";
					$respon = 0;
				}
				
			}else{
				$data["pesan"] = "Data Akun Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "Informasi Client Tidak Ditemukan";
			$respon = 0;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}
	
	// Service Insert
	function change_password(){
		$clientID = $this->input->post('ClientID');
		$newpassword = $this->input->post('NewPassword');
		$KeyLog = $this->input->post('KeyLog');
		
		$data = "";
		$result = "";
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($newpassword)){
				if ($newpassword !=  ""){
					$updatepassword = array(
						"ClientPassword" => $this->encryption->encrypt($newpassword)
					);
					
					$update = $this->db->update("tbl_client", $updatepassword, array("ClientID"=>$clientID) );
					if($update){
						$data["pesan"] = "Berhasil Reset Password";
						$respon = 1;
					}else{
						$data["pesan"] = "Gagal Reset Password";
						$respon = 0;
					}
				}else{
					$data["pesan"] = "Tidak Ada Inputan NewPassword";
					$respon = 0;
				}
			}else{
				$data["pesan"] = "Tidak Ada Inputan NewPassword";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}

	function add_redeem(){
		$clientID = $this->input->post('ClientID');
		$amount = $this->input->post('amount');
		$unit = $this->input->post('unit');
		$FundID = $this->input->post('FundID');
		$KeyLog = $this->input->post('KeyLog');
		
		$data = "";
		$result = "";
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($clientID)){
				$array_insert = array(
					"ClientID" => $clientID,
					"FundID" => $FundID,
					"OrderDate" => date("Y-m-d H:i:s"),
					"OrderAmount" => $amount,
					"OrderUnit" => $unit,
					"StatusID" => 1,
					"StatusDescription" => "Input Redeem",
				);
				$insert = $this->db->insert("tbl_order_redeem", $array_insert);
				$id = $this->db->insert_id();
				if($insert){
					$array_client = array(
						"ClientID" => $clientID,
						"FlagNew" => 1,
					);
					$this->db->insert("tbl_order_redeem_client", $array_client);
					
					$array_log = array(
						"OrderID" => $id,
						"StatusID" => 1,
						"LogDatetime" => date("Y-m-d H:i:s"),
					);
					$this->db->insert("tbl_order_redeem_log", $array_log);
					
					$cetak = $this->cetak("redeem_document", $clientID, $FundID, $id);
					$client = $this->db->get_where("tbl_client", array("ClientID"=>$clientID) )->row_array();
					$this->lib->kirimemail('email_redeem', $client["ClientEmail"], $clientID, $id);
					
					$data["pesan"] = "Berhasil Add Redeem";
					$respon = 1;
				}else{
					$data["pesan"] = "Gagal Add Redeem";
					$respon = 0;
				}
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}

	function upload_subscribe(){
		$orderID = $this->input->post('OrderID');
		$KeyLog = $this->input->post('KeyLog');
		
		//echo $orderID." - ".$KeyLog;
		//exit;
		
		if ($KeyLog){
			$this->lib->cekkeylog($KeyLog);
			if ($orderID){
				if($_FILES['TransferProofFile']['name'] != ""){
					$path = "__repository/subscribe/";
					$file = date('YmdHis')."_TRANSFERPROOFFILE_".$orderID;
					$filename =  $this->lib->uploadnong($path, 'TransferProofFile', $file);
					
					$array_update = array(
						"TransferProofFile" =>	$filename,
						"StatusID" => 2,
						"StatusDescription" => "UPLOAD",
						"UpdateDate" => date("Y-m-d H:i:s")
					);
					$update = $this->db->update("tbl_order_subscribe", $array_update, array("OrderID"=>$orderID) );
					if($update){
						$array_log = array(
							"OrderID" => $orderID,
							"StatusID" => 2,
							"LogDatetime" => date("Y-m-d H:i:s"),
						);
						$this->db->insert("tbl_order_subscribe_log", $array_log);
						
						$data["pesan"] = "Berhasil Upload File Subscribe";
						$respon = 1;
					}
				}else{
					$data["pesan"] = "File Tidak Ada yang Dikirim";
					$respon = 0;
				}
			}else{
				$data["pesan"] = "Data Order Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}
	
	function add_subscribe(){
		$clientID = $this->input->post('ClientID');
		$amount = $this->input->post('amount');
		$bankID = $this->input->post('bankID');
		$FundID = $this->input->post('FundID');
		$KeyLog = $this->input->post('KeyLog');
		
		$data = "";
		$result = "";
				
		if (isset($KeyLog)){
			$this->lib->cekkeylog($KeyLog);
			if (isset($clientID)){
				$array_insert = array(
					"ClientID" => $clientID,
					"FundID" => $FundID,
					"OrderDate" => date("Y-m-d H:i:s"),
					"OrderAmount" => $amount,
					"AccountID" => $bankID,
					"StatusID" => 1,
					"StatusDescription" => "Input Subscribe",
				);
				$insert = $this->db->insert("tbl_order_subscribe", $array_insert);
				$id = $this->db->insert_id();
				if($insert){
					$array_client = array(
						"ClientID" => $clientID,
						"FlagNew" => 1,
					);
					$this->db->insert("tbl_order_subscribe_client", $array_client);
					
					$array_log = array(
						"OrderID" => $id,
						"StatusID" => 1,
						"LogDatetime" => date("Y-m-d H:i:s"),
					);
					$this->db->insert("tbl_order_subscribe_log", $array_log);
					
					$data["pesan"] = "Berhasil Add Subscribe";
					$respon = 1;
				}else{
					$data["pesan"] = "Gagal Add Subscribe";
					$respon = 0;
				}
			}else{
				$data["pesan"] = "Informasi Client Tidak Ditemukan";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "No Key Param";
			$respon = 2;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}
	
	function register_new_account(){
		$ClientEmail = $this->input->post('ClientEmail');
		$ClientName = $this->input->post('ClientName');
		$ClientHP = $this->input->post('ClientHP');
		
		$data = "";
		$result = "";
		
		$cek_db = $this->db->get_where("tbl_client", array("ClientEmail"=>$ClientEmail) )->row_array();
		if(!$cek_db){
			$password_asli = strtoupper($this->lib->randomString(5, 'angkahuruf'));
			$password = $this->encryption->encrypt($password_asli);

			$array_insert = array(
				"ClientName" => $ClientName,
				"ClientEmail" => $ClientEmail,
				"ClientHP" => $ClientHP,
				"ClientPassword" => $password,
				"StatusID" => 1,
				"StatusDescription" => "Input Subscribe",
			);
			$insert = $this->db->insert("tbl_client", $array_insert);
			if($insert){
				$this->lib->kirimemail('email_registrasi', $ClientEmail, $ClientEmail, $password_asli);
				$data["pesan"] = "Berhasil Register Akun";
				$respon = 1;
			}else{
				$data["pesan"] = "Gagal Register Akun";
				$respon = 0;
			}
		}else{
			$data["pesan"] = "Data Akun Sudah Terdaftar";
			$respon = 0;
		}
		
		$result = array('data'=>$data, 'respon'=>$respon);
		echo json_encode($result);
	}
	
	function test_pdf(){
		$this->cetak("redeem_document", 7, 1);
	}
	
	function cetak($mod, $p1="", $p2="", $p3=""){
		switch($mod){
			case "redeem_document":
				$data = array();
				$client = $this->db->get_where("tbl_client", array("ClientID"=>$p1) )->row_array();
				$fund = $this->db->get_where("tbl_fund", array("FundID"=>$p2) )->row_array();
				$bank = $this->db->get_where("tbl_fund_bankaccount", array("FundID"=>$p2) )->row_array();
				
				$data["client"] = $client;
				$data["fund"] = $fund;
				$data["bank"] = $bank;
				
				$filename = "file-".$p1."-transaksiredeem-".$p3;
				$temp = "frontend/redeem_pdf.html";
				$pathdest = "__repository/redeem_pdf/".$filename.".pdf";
				
				$this->hasil_output('pdf',$mod,$data,$filename,$temp,$pathdest,"A4-L");
			break;
		}
	}
	
	function hasil_output($p1,$mod,$data,$filename,$temp="",$pathdest="",$ukuran="A4"){
		switch($p1){
			case "pdf":
				$this->load->library('mlpdf');	
				$pdf = $this->mlpdf->load();
				
				$this->nsmarty->assign('data', $data);
				$this->nsmarty->assign('mod', $mod);
				
				$htmlcontent = $this->nsmarty->fetch($temp);
				
				//echo $htmlcontent;exit;
				
				$spdf = new mPDF('', $ukuran, 0, '', 5, 10, 8, 0, 0, 0, 'P');
				$spdf->ignore_invalid_utf8 = true;
				// bukan sulap bukan sihir sim salabim jadi apa prok prok prok
				$spdf->allow_charset_conversion = true;     // which is already true by default
				$spdf->charset_in = 'iso-8859-1';  // set content encoding to iso
				$spdf->SetDisplayMode('fullpage');		
				//$spdf->SetHTMLHeader($htmlheader);
				//$spdf->keep_table_proportions = true;
				$spdf->useSubstitutions=false;
				$spdf->simpleTables=true;
				
				$spdf->SetProtection(array('print'));				
				$spdf->WriteHTML($htmlcontent); // write the HTML into the PDF
				//$spdf->Output('repositories/Dokumen_LS/LS_PDF/'.$filename.'.pdf', 'F'); // save to file because we can
				$spdf->Output($pathdest, 'F');
				//$spdf->Output($file_name.'.pdf', 'I'); // view file	
			break;
		}
	}
	

	function genpass(){
		// $pass = "12345";
		// echo $this->encryption->encrypt($pass);
		$data = $this->db->get_where("tbl_client", array("ClientEmail"=>"levi@g") )->row_array();
		echo $this->encryption->decrypt($data["ClientPassword"]);
		// echo $_SERVER["DOCUMENT_ROOT"];
	}
	

}
