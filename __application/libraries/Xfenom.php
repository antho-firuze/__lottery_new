<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Xfenom extends Fenom
{
	public function __construct()
	{
		$this->compile_dir 	= DIR_CACHE;
		$this->template_dir = DIR_TEMPLATE;
		$this->options = array(
			'strip' 			=> true,
			'auto_trim' 	=> true,
			'auto_reload' => true
		);
		
		$this->fenom = Fenom::factory($this->template_dir, $this->compile_dir, $this->options);
		
		log_message('debug', "Fenom Class Initialized");
	}
	
	function view($template, $data = array(), $return = FALSE)
	{
		if ($return == FALSE) 
		{
			log_message('debug', "Fenom: display output'");
			$this->fenom->display($template, $data);
			exit();
		} else {
			log_message('debug', "Fenom: return output'");
			return $this->fenom->fetch($template, $data);
		}
	}
	
}