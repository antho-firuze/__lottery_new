<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Jsonrpc extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
		
		/* JSONRPC Config: This is a bunch of settings */
		$this->r_method_allowed = ['GET','DELETE','POST','PUT','OPTIONS','PATCH','UNLOCK','LOCK'];
		$this->idioms = [
			'english' 	=> ['id'	=> 'us', 'name' => 'English', 	'idiom' => 'english', 	'icon' => 'flag-icon-us'],
			'indonesia' => ['id'	=> 'id', 'name' => 'Indonesia', 'idiom' => 'indonesia', 'icon' => 'flag-icon-id'],
		];
		$this->agent = ['android','ios','web'];
		
		
		$this->load->library('f');
		$this->lang->load('jsonrpc','english');
		
		$this->r_method = $_SERVER['REQUEST_METHOD'];
		if (!in_array($this->r_method, $this->r_method_allowed))
			$this->f->response(FALSE, ['message' => $this->f->lang('err_request_method_unsupported')], 403);
		
		if (in_array($this->r_method, ['GET','DELETE'])) {
			$this->params = (object) $this->input->get();
		}
		
		/*
		// This request params must be place in body and with header: Content-Type: application/json
		// Request can be taken with :
		// $this->requests = file_get_contents('php://input');
		// or
		// $this->requests = $this->input->raw_input_stream;
		*/
		if (in_array($this->r_method, ['POST','PUT','OPTIONS','PATCH','UNLOCK','LOCK'])) {
			$this->requests = json_decode(file_get_contents('php://input'));
			
			if (!in_array(gettype($this->requests), ['object', 'array']))
				$this->f->response(FALSE, ['message' => $this->f->lang('err_request_invalid')]);
			
			$this->requery_request();
		}
	}
	
	function index() { echo 'JSON RPC OK !'; }
	// function index_php() {echo phpinfo();}
	/* function test_send_mail() {
		$from = 'simpi.tfs@gmail.com';
		$to = 'antho.firuze@gmail.com'; // $row->UserEmail;
		$subject = 'SIMPIPRO (test mail) !';
		$message = 'Dear Tester, <br><br>';
		$message .= 'This send mail has been delivered successfully ! <br><br><br>';
		$message .= 'This email was sent by: <b>PT. SIMPI PROFESSIONAL INDONESIA</b>,<br>';
		$message .= 'Palakali Raya Street, No.49C, Kukusan Depok, Indonesia';

		list($success, $message) = $this->f->send_mail($from, $to, $subject, $message);
		if (!$success)
			$this->f->response(FALSE, ['message' => $message]);
			
		$this->f->response(TRUE, ['message' => 'Send mail successfully !']);
	} */
	
	/*
	 * Method for checking is request valid?
	 * 
	 * params $request 	(object)
	 * 
	 */
	private function is_valid_request($request)
	{
		if (!$request || is_string($request))
			return [FALSE, ['message' => $this->f->lang('err_request_invalid')]];
		
		if (!is_object($request))
			return [FALSE, ['message' => $this->f->lang('err_request_invalid')]];
		
		list($success, $result) = $this->is_valid_agent($request);
		if (!$success)
			return [FALSE, $result];
		
		list($success, $result) = $this->is_valid_language($request);
		if (!$success)
			return [FALSE, $result];
		
		// list($success, $result) = $this->is_valid_jsonrpc($request);
		// if (!$success)
			// return [FALSE, $result];
		
		return [TRUE, ''];
	}
	
	/* 
	 * Method for checking request agent
	 * 
	 * params $request 	(object)
	 * 
	 * return @error 		array(status = FALSE, message = 'Required parameter: %s')
	 * return @error 		array(status = FALSE, message = 'Unsupported parameter: %s')
	 * return @success 	array(status = TRUE, message = '')
	 * 
	 */
	private function is_valid_agent($request)
	{
		if (!isset($request->agent))
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'agent')]];	

		if (!in_array($request->agent, $this->agent))
			return [FALSE, ['message' => $this->f->lang('err_param_unsupported', "{agent: $request->agent}")]];	
		
		return [TRUE, ''];
	}
	
	/* 
	 * Method for checking request language
	 * 
	 * params $request 	(object)
	 * 
	 * return @error 		array(status = FALSE, message = 'Unsupported language: %s')
	 * return @success 	array(status = TRUE, message = '')
	 * 
	 */
	private function is_valid_language($request)
	{
		if (isset($request->lang) || !empty($request->lang)){
			if (!in_array($request->lang, array_keys($this->idioms)))
				if (in_array($request->lang, array_column($this->idioms, 'id')))
					foreach($this->idioms as $k => $v) { if ($v['id'] == $request->lang) $request->lang = $k; }
				else
					return [FALSE, ['message' => $this->f->lang('err_param_unsupported', "{lang: $request->lang}")]];	
		} else {
			$request->lang = 'english';
		}
		
		return [TRUE, ''];
	}
	
	/* 
	 * Method for checking request jsonrpc
	 * 
	 * params $request 	(object)
	 * 
	 * return @error 		array(status = FALSE, message = 'Required parameter: %s')
	 * return @error 		array(status = FALSE, message = 'Unsupported parameter: %s')
	 * return @success 	array(status = TRUE, message = '')
	 * 
	 */
	private function is_valid_jsonrpc($request)
	{
		if (!isset($request->jsonrpc))
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'jsonrpc')]];	

		if (!in_array($request->jsonrpc, ['2.0']))
			return [FALSE, ['message' => $this->f->lang('err_param_unsupported', "{jsonrpc: $request->jsonrpc}")]];	
		
		return [TRUE, ''];
	}
	
	/* 
	 * Function for requering request
	 * 
	 */
	private function requery_request()
	{
		function pre_checking($r){
			if (isset($r->params) || !empty($r->params))
				if (is_string($r->params))
					$r->params = json_decode($r->params);
			
			return $r;
		}
		
		if (is_object($this->requests)){
			$request = $this->requests;
			
			list($success, $result) = $this->is_valid_request($request);
			if (!$success){
				if (isset($request->id)) 
					$result['id'] = $request->id;
				
				$this->result = $this->f->response(FALSE, $result, FALSE, FALSE);
			} else {
				$request = pre_checking($request);
			
				$this->result = $this->exec_method($request);
			}
		}
		
		if (is_array($this->requests)) {
			if (count((array)$this->requests) < 1)
				$this->f->response(FALSE, ['message' => $this->f->lang('err_request_invalid'), 'id' => null]);
			
			foreach($this->requests as $request){
				list($success, $result) = $this->is_valid_request($request);
				if (!$success){
					if (isset($request->id)) 
						$result['id'] = $request->id;
				
					$this->result[] = $this->f->response(FALSE, $result, FALSE, FALSE);
				}	else {
					$request = pre_checking($request);
			
					$this->result[] = $this->exec_method($request);
				}
			}
		}
		
		if (isset($this->result))
			$this->json_out($this->result);
	}
	
	/*
	 * JSON Output
	 * 
	 */
	private function json_out($result)
	{
		header("HTTP/1.0 200");
		header('Content-Type: application/json');
		if (is_array($result)){
			if (count($result) > 0)
				echo json_encode($result);
		} else {
			if ($result || !empty($result))
				echo json_encode($result);
		}
		exit();
	}
		
	/* 
	 *	Rule for output:
	 *	1. If result error then request->id == null
	 *	2. If request->id <> null
	 *	3. Except No. 1 & 2 => no output (that's notification)
	*/
	private function exec_method($request)
	{
		$idiom = (!isset($request->lang) || empty($request->lang)) ? 'english' : $request->lang;
		$this->lang->load(['auth','jsonrpc'], $idiom);
			
		list($class, $method) = explode('.', $request->method);
		
		$model = ucfirst($class).'_model';
		if(!file_exists(APPPATH."models/$model.php"))
			return $this->f->response(FALSE, ['message' => $this->f->lang('err_method_unknown', $request->method), 'id' => (isset($request->id) ? $request->id : null)], FALSE, FALSE);
		
		$this->load->model($model);
		if (!method_exists($this->{$model}, $method))
			return $this->f->response(FALSE, ['message' => $this->f->lang('err_method_unknown', $request->method), 'id' => (isset($request->id) ? $request->id : null)], FALSE, FALSE);

		// Execute the process
		list($success, $result) = $this->{$model}->{$method}($request);
		if (!$success)
			return $this->f->response(FALSE, $result, FALSE, FALSE);
		
		if (isset($request->id) && !empty($request->id)){
			$result['id'] = $request->id;
			return $this->f->response(TRUE, $result, FALSE, FALSE);
		}
	}
	
}