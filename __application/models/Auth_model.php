<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

class Auth_model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		$this->load->database('localhost');
		$this->config->load('auth', FALSE, TRUE);
		
		$this->table_user = $this->config->item('table_user');
		$this->table_user_config = $this->config->item('table_user_config');
		$this->forgot_token_expiration = $this->config->item('forgot_token_expiration');
		$this->android_token_expiration = $this->config->item('android_token_expiration');
		$this->ios_token_expiration = $this->config->item('ios_token_expiration');
		$this->web_token_expiration = $this->config->item('web_token_expiration');
		
		$this->min_password_length = $this->config->item('min_password_length');
		$this->max_password_length = $this->config->item('max_password_length');
		$this->remember_users = $this->config->item('remember_users');
		$this->max_login_attempts = $this->config->item('max_login_attempts');
		$this->lockout_time = $this->config->item('lockout_time');
		$this->domain_frontend = $this->config->item('domain_frontend');
		
		$this->release_locked_account();
	}
	
	/* 
	 * Method for auto release locked account
	 * 
	 */
	private function release_locked_account()
	{
		$this->db->update($this->table_user, 
			['login_try' => 0, 'account_locked_until' => null], 
			['login_try >' => 0, 'account_locked_until <' => date('Y-m-d H:i:s')]
		);
	}
	
	/* 
	 * Method for checking password validation
	 * 
	 * params password
	 * 
	 * return @error 		array(status = FALSE, message = 'Minimum length of Password !')
	 * return @error 		array(status = FALSE, message = 'Maximum length of Password !')
	 * return @success 	array(status = TRUE, data = encrypted_password)
	 * 
	 */
	private function is_valid_password($password)
	{
		if (!isset($password) || empty($password))
			return [FALSE, $this->f->lang('err_param_required', 'password')];
		
		if (strlen($password) < $this->min_password_length)
			return [FALSE, $this->f->lang('err_min_password_length', $this->min_password_length)];
		
		if (strlen($password) > $this->max_password_length)
			return [FALSE, $this->f->lang('err_max_password_length', $this->max_password_length)];
		
		$password = md5($password);
		return [TRUE, $password];
	}
	
	/* 
	 * Method for check validity token from outside
	 * 
	 * params row
	 * 
	 * return @error		[FALSE, message]
	 * return @success	[TRUE, ""]
	 * 
	 */
	function is_valid_token($request)
	{
		list($success, $return) = $this->f->is_valid_token($request);
		if (!$success)
			return [FALSE, $return];
		
		return [TRUE, ""];
	}
	
	/* 
	 * Method for force release locked account
	 * 
	 * params agent, token, id
	 * 
	 */
	function force_release_locked_account($request)
	{
		list($success, $return) = $this->f->is_valid_token($request);
		if (!$success)
			return [FALSE, $return];
		
		if (!isset($request->params->email) || empty($request->params->email))
			return [FALSE, $this->f->lang('err_param_required', 'email')];
		
		$row = $this->db->get_where($this->table_user, ['email' => $request->params->email])->row();
		if (!$row)
			return [FALSE, ['message' => $this->f->lang('err_email_not_found')]];
		
		$this->db->update($this->table_user, 
			['login_try' => 0, 'account_locked_until' => null], 
			['email' => $request->params->email]
		);
		
		return [TRUE, $this->f->lang('success_unlocked')];
	}
	
	/* 
	 * Method for get detail token
	 * 
	 * params row
	 * 
	 * return @success	row
	 * 
	 */
	function get_token($token)
	{
		return $this->db->get_where($this->table_user, $token)->row();
	}
	
 	/*
	 * Method for login, with checking of login attempt and generate session token.
	 *
	 * params agent, username, password
	 *
	 * return @error 		array(status = FALSE, message = 'Email not found !')
	 * return @error 		array(status = FALSE, message = 'Maximum login attempt reached, your account is temporary locked !')
	 * return @error 		array(status = FALSE, message = 'Incorrect Email or Password !')
	 * return @success 	array(status = TRUE, data = $row)
	 * 
 	 */
	function login($request)
	{
		if (!isset($request->params->email) || empty($request->params->email))
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'email')]];
		
		if (!isset($request->params->time_epoch) || empty($request->params->time_epoch))
			$currentTime = time();
		else
			$currentTime = $request->params->time_epoch; // strtotime($request->params->time);
		
		$row = $this->db->get_where($this->table_user, ['email' => $request->params->email])->row();
		if (!$row)
			return [FALSE, ['message' => $this->f->lang('err_email_not_found')]];
		
		if ((integer)$row->login_try >= $this->max_login_attempts){
			$this->load->helper('datetime');
			return [FALSE, ['message' => $this->f->lang('err_login_attempt_reached', nicetime_lang($row->account_locked_until, $request->lang))]];
		}
		
		if (md5($request->params->password) != $row->password){
			
			$login_try = $row->login_try + 1;
			if ($login_try == $this->max_login_attempts)
				$update_field['account_locked_until'] = date('Y-m-d H:i:s', time() + $this->lockout_time);
			
			$update_field['login_try'] = $login_try;
			$this->db->update($this->table_user, 
				$update_field, 
				['email' => $row->email]
			);
			
			return [FALSE, ['message' => $this->f->lang('err_login_failed')]];
		}
		
		$token = $this->f->gen_token();
		if ($request->agent == 'android') {
			$token_exp = date('Y-m-d\TH:i:s\Z', $currentTime + $this->android_token_expiration);
			$fld = ['android_token' => $token, 'android_token_expired' => $token_exp];
		}
		if ($request->agent == 'ios') {
			$token_exp = date('Y-m-d\TH:i:s\Z', $currentTime + $this->ios_token_expiration);
			$fld = ['ios_token' => $token, 'ios_token_expired' => $token_exp];
		}
		if ($request->agent == 'web') {
			$token_exp = date('Y-m-d H:i:s', $currentTime + $this->web_token_expiration);
			$fld = ['web_token' => $token, 'web_token_expired' => $token_exp];
		}
		
		$this->db->update($this->table_user, 
			array_merge(['login_last' => date('Y-m-d H:i:s'), 'login_try' => 0], $fld), 
			['email' => $row->email] 
		);
		
		// Get Login Account
		$fields = ['ClientID','email'];
		$row = (object)array_intersect_key((array)$row, array_flip($fields));
		
		$result = (object)[];
		$result->user = $row;
		$result->user->token = $token;
		$result->user->token_exp = $token_exp;
		$result->user->token_exp_epoch = strtotime($token_exp);
		
		return [TRUE, ['result' => $result, 'message' => $this->f->lang('success_login')]];
	}
	
	function logout($request)
	{
		if ($request->agent == 'android') {
			$fld = ['android_token' => null, 'android_token_expired' => null];
			$where = ['android_token' => urldecode($request->token)];
		}
		if ($request->agent == 'ios') {
			$fld = ['ios_token' => null, 'ios_token_expired' => null];
			$where = ['ios_token' => urldecode($request->token)];
		}
		if ($request->agent == 'web') {
			$fld = ['web_token' => null, 'web_token_expired' => null];
			$where = ['web_token' => urldecode($request->token)];
		}
		
		if (!$return = $this->db->update($this->table_user, $fld, $where)) 
			return [FALSE, ['message' => $this->db->error()['message']]];
		else
			return [TRUE, ['message' => '']];
		
		// $return = $this->db->update($this->table_user, $fld, $where);
		
		// return [TRUE, ['message' => '']];
	}
	/*
	 * Method for unlock session, with checking of login attempt
	 *
	 * params agent, token, password
	 *
	 * return @error 		array(status = FALSE, message = 'Maximum login attempt reached, your account is temporary locked !')
	 * return @error 		array(status = FALSE, message = 'Incorrect Password !')
	 * return @success 	array(status = TRUE, data = '')
	 * 
 	 */
	function unlock($request)
	{
		list($success, $return) = $this->f->is_valid_token($request);
		if (!$success)
			return [FALSE, $return];
		
		$row = $return['result'];
		
		if ((integer)$row->login_try >= $this->max_login_attempts){
			$this->load->helper('datetime');
			return [FALSE, ['message' => $this->f->lang('err_login_attempt_reached', nicetime_lang($row->account_locked_until, $request->lang))]];
		}
		
		if (md5($request->params->password) != $row->password){

			$login_try = $row->login_try + 1;
			if ($login_try == $this->max_login_attempts)
				$update_field['account_locked_until'] = date('Y-m-d H:i:s', time() + $this->lockout_time);
			
			$update_field['login_try'] = $login_try;
			$this->db->update($this->table_user, 
				$update_field, 
				['email' => $row->email]
			);
			
			return [FALSE, ['message' => $this->f->lang('err_unlocked_failed')]];
		}
		
		return [TRUE, ['message' => NULL]];
	}
	
	/*
	 * Method for simple forgotten password & email confirmation with generated random password
	 *
	 * params email
	 * 
	 * return @error 		array(status = FALSE, message = 'Email not found !')
	 * return @success 	array(status = TRUE, message = 'Link address for reset your password has been sent to your email !')
	 * 
 	 */
	function forgot_password_simple($request)
	{
		$row = $this->db->get_where($this->table_user, ['email' => $request->params->email])->row();
		if (!$row)
			return [FALSE, ['message' => $this->f->lang('err_email_not_found')]];
		
		$row->client = $this->get_client_detail($row);

		// generate random password
		$new_password = $this->f->gen_pwd($this->min_password_length);
		$new_password_enc = md5($new_password);
		$this->db->update($this->table_user, 
			['password' => $new_password_enc], 
			['email' => $row->email]
		);
		
		$header = (object)[];
		$header->from = $this->config->item('system_email');
		$header->to = $row->email;
		$header->subject = $this->f->lang('email_subject_forgot_password_simple');
		$message = $this->f->lang('email_body_forgot_password_simple', 
									[
										'{name}' => $row->client->full_name, 
										'{new_password}' => $new_password
									]);

		list($success, $message) = $this->f->send_mail($header, $message);
		// if (!$success)
			// return [FALSE, $message];
		
		return [TRUE, ['message' => $this->f->lang('info_sent_email_password')]];
	}
	
	/*
	 * Method for forgotten password & email confirmation
	 *
	 * params email
	 * 
	 * return @error 		array(status = FALSE, message = 'Email not found !')
	 * return @success 	array(status = TRUE, message = 'Link address for reset your password has been sent to your email !')
	 * 
 	 */
	function forgot_password($request)
	{
		$row = $this->db->get_where($this->table_user, ['email' => $request->params->email])->row();
		if (!$row)
			return [FALSE, ['message' => $this->f->lang('err_email_not_found')]];
		
		$row->client = $this->get_client_detail($row);

		$token = $this->f->gen_token();
		$token_exp = date('Y-m-d H:i:s', time() + $this->forgot_token_expiration);
		$this->db->update($this->table_user, 
			['forgot_token' => $token, 'forgot_token_expired' => $token_exp], 
			['email' => $row->email]
		);
		
		$header = (object)[];
		$header->from = $this->config->item('system_email');
		$header->to = $row->email;
		$header->subject = $this->f->lang('email_subject_forgot_password');
		$message = $this->f->lang('email_body_forgot_password', 
									[
										'{name}' => $row->client->full_name, 
										'{domain_frontend}' => $this->domain_frontend, 
										'{token}' => $token
									]);

		list($success, $message) = $this->f->send_mail($header, $message);
		// if (!$success)
			// return [FALSE, $message];
		
		return [TRUE, ['message' => $this->f->lang('info_sent_email_reset_password_link')]];
	}
	
	/*
	 * Method for reset password, with checking of forgot token & email confirmation
	 *
	 * params forgot_token, password
	 * 
	 * return @error 		array(status = FALSE, message = 'Invalid token !')
	 * return @error 		array(status = FALSE, message = 'Token expired !')
	 * return @success 	array(status = TRUE, message = 'Your password has been reset !')
	 * 
 	 */
	function reset_password($request)
	{
		$row = $this->db->get_where($this->table_user, ['forgot_token' => $request->params->token])->row();
		if (!$row)
			return [FALSE, ['message' => $this->f->lang('err_token_invalid')]];
		
		$row->client = $this->get_client_detail($row);

		if ($row->forgot_token_expired < date('Y-m-d H:i:s'))
			return [FALSE, ['message' => $this->f->lang('err_token_expired')]];
		
		list($success, $message) = $this->is_valid_password($request->params->password);
		if (!$success)
			return [FALSE, ['message' => $message]];
		
		$new_password_enc = $message;
		$this->db->update($this->table_user, 
			['forgot_token' => null, 'forgot_token_expired' => null, 'password' => $new_password_enc], 
			['email' => $row->email]
		);
		
		$header = (object)[];
		$header->from = $this->config->item('system_email');
		$header->to = $row->email;
		$header->subject = $this->f->lang('email_subject_reset_password');
		$message = $this->f->lang('email_body_reset_password', 
									[
										'{name}' => $row->client->full_name, 
										'{new_password}' => $request->params->password
									]);

		list($success, $message) = $this->f->send_mail($header, $message);
		// if (!$success)
			// return [FALSE, $message];

		return [TRUE, ['message' => $this->f->lang('success_reset')]];
	}
	
	/*
	 * Method for reset password admin, with checking of forgot token & email confirmation
	 *
	 * params email, auto, password
	 * 
	 * return @success 	array(status = TRUE, message = 'Password has been reset successfully !')
	 * 
 	 */
	function rst_password($request)
	{
		list($success, $return) = $this->f->is_valid_token($request);
		if (!$success)
			return [FALSE, $return];
		
		$row = $this->db->get_where($this->table_user, ['email' => $request->params->email])->row();
		if (!$row)
			return [FALSE, ['message' => $this->f->lang('err_email_not_found')]];
		
		$row->client = $this->get_client_detail($row);

		if (isset($request->params->auto) && $request->params->auto) {
			// generate random password
			$new_password = $this->f->gen_pwd($this->min_password_length);
			$new_password_enc = md5($new_password);
		} else {
			list($success, $message) = $this->is_valid_password($request->params->password);
			if (!$success)
				return [FALSE, ['message' => $message]];
			
			$new_password_enc = $message;
		}
		$this->db->update($this->table_user, 
			['password' => $new_password_enc], 
			['email' => $request->params->email]
		);
		
		$header = (object)[];
		$header->from = $this->config->item('system_email');
		$header->to = $row->email;
		$header->subject = $this->f->lang('email_subject_rst_password');
		$message = $this->f->lang('email_body_rst_password', 
									[
										'{name}' => $row->client->full_name, 
										'{new_password}' => $request->params->password
									]);

		list($success, $message) = $this->f->send_mail($header, $message);
		// if (!$success)
			// return [FALSE, $message];

		return [TRUE, ['message' => $this->f->lang('info_sent_email_rst_password')]];
	}
	
	/*
	 * Method for change password, with checking of token & old password, & with email confirmation
	 *
	 * params agent, token, password, new password
	 * 
	 * return @error 		array(status = FALSE, message = 'Maximum login attempt reached, your account is temporary locked !')
	 * return @error 		array(status = FALSE, message = 'Incorrect Old Password !')
	 * return @success 	array(status = TRUE, message = 'Your password has been changed !')
	 * 
 	 */
	function chg_password($request)
	{
		list($success, $return) = $this->f->is_valid_token($request);
		if (!$success)
			return [FALSE, $return];
		
		$row = $return['result'];
		$row->client = $this->get_client_detail($row);
		
		if ((integer)$row->login_try >= $this->max_login_attempts){
			$this->load->helper('datetime');
			return [FALSE, ['message' => $this->f->lang('err_login_attempt_reached', nicetime_lang($row->account_locked_until, $request->lang))]];
		}
		
		if (md5($request->params->old_password) != $row->password){

			$login_try = $row->login_try + 1;
			if ($login_try == $this->max_login_attempts)
				$update_field['account_locked_until'] = date('Y-m-d H:i:s', time() + $this->lockout_time);
			
			$update_field['login_try'] = $login_try;
			$this->db->update($this->table_user, 
				$update_field, 
				['email' => $row->email]
			);
			
			return [FALSE, ['message' => $this->f->lang('err_old_password')]];
		}
		
		$new_password = isset($request->params->new_password) ? $request->params->new_password : null;
		list($success, $message) = $this->is_valid_password($new_password);
		if (!$success)
			return [FALSE, ['message' => $message]];
		
		$new_password_enc = $message;
		$this->db->update($this->table_user, 
			['login_try' => 0, 'password' => $new_password_enc], 
			['email' => $row->email]
		);
		
		$header = (object)[];
		$header->from = $this->config->item('system_email');
		$header->to = $row->email;
		$header->subject = $this->f->lang('email_subject_chg_password');
		$message = $this->f->lang('email_body_chg_password', 
									[
										'{name}' => $row->client->full_name, 
										'{new_password}' => $new_password
									]);

		list($success, $message) = $this->f->send_mail($header, $message);
		// if (!$success)
			// return [FALSE, $message];

		return [TRUE, ['message' => $this->f->lang('success_chg_password')]];
	}
	
	/*
	 * Method for register new account or existing account to access mobile apps
	 *
	 * params agent, email, phone, name_first, name_last, password
	 * 
	 * return @error 		array(status = FALSE, message = 'Your email have registered but not activate yet, please check your email to activate !')
	 * return @error 		array(status = FALSE, message = 'Your email have registered, please login with your email & password !')
	 * return @success 	array(status = TRUE, message = 'Your registration done, please check your email to validate your account !')
	 * 
 	 */
	function register($request)
	{
		if (!$request->params->email)
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'email')]];
		
		// #1: 
		// ==>
		// Check first on table_user 
		// if email exists, they may be real client or may be prospect client
		// <==
		$row = $this->db->get_where($this->table_user, ['email' => $request->params->email])->row();
		if ($row){
			if ($row->is_need_activate)
				return [FALSE, ['message' => $this->f->lang('err_email_has_register_not_active')]];
			
			return [FALSE, ['message' => $this->f->lang('err_email_has_register')]];
		}
		
		if (!$request->params->phone)
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'phone')]];
		
		if (!$request->params->name_first)
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'name_first')]];
		
		if (!$request->params->name_last)
			return [FALSE, ['message' => $this->f->lang('err_param_required', 'name_last')]];
		
		$new_password = $this->f->gen_pwd($this->min_password_length);
		list($success, $message) = $this->is_valid_password($new_password);
		if (!$success)
			return [FALSE, ['message' => $message]];
		
		$new_password_enc = $message;
		$token = $this->f->gen_token();
		$this->db->insert($this->table_user, 
			[
				'email' => $request->params->email, 
				'password' => $new_password_enc,
				'forgot_token' => $token,
				'is_need_activate' => 1,
			]
		);
		
		$header = (object)[];
		$header->from = $this->config->item('system_email');
		$header->to = $request->params->email;
		$header->subject = $this->f->lang('email_subject_register');
		$message = $this->f->lang('email_body_register', 
									[
										'{name}' => 'New Client', 
										'{email}' => $request->params->email,
										'{new_password}' => $new_password,
										'{token}' => $token,
										'{domain_frontend}' => $this->domain_frontend
									]);

		list($success, $message) = $this->f->send_mail($header, $message);
		// if (!$success)
			// return [FALSE, $message];

		return [TRUE, ['message' => $this->f->lang('success_register')]];
	}
	
	/*
	 * Method for activation account just registered.
	 * 
	 * params agent, token
	 * 
	 * return @error 		array(status = FALSE, message = 'Token not found, or your account has already activate !')
	 * return @success 	array(status = TRUE, message = 'Thank you. Now your account has been activate !')
	 * 
 	 */
	function activation($request)
	{
		$row = $this->db->get_where($this->table_user, ['forgot_token' => $request->token])->row();
		if (!$row)
			return [FALSE, ['message' => $this->f->lang('err_activate_account')]];
		
		$this->db->update($this->table_user, 
			['is_need_activate' => 0, 'forgot_token' => null],
			['email' => $row->email]
		);
		
		return [TRUE, ['message' => $this->f->lang('success_activation')]];
	}
}
