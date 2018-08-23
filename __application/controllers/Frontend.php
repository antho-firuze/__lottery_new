<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Frontend extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('frontend_model');
		
		/* Config: Fenom/Smarty/Twig/Blade/Volt (Template Engine for PHP) Settings */
		$this->root_url 	= '__application/views';
		$this->root_dir		= APPPATH.'views';
		$this->init_dir		= 'frontend';
		$this->theme 			= 'materialpro';
		define('DIR_CACHE', FCPATH.'__tmp');
		define('DIR_TEMPLATE', $this->root_dir.DIRECTORY_SEPARATOR.$this->init_dir.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR);
		define('THEME_URL', BASE_URL.$this->root_url.SEPARATOR.$this->init_dir.SEPARATOR.$this->theme.SEPARATOR);
		define('THEME_PATH', DIR_TEMPLATE);
		$this->load->library(['xfenom','xsmarty']);
		/* End Template Engine for PHP */
		
		/* Get input from request browser */
		$this->params = (object) $this->input->get();
		
		// ============================================================== 
		// Config variables
		// ============================================================== 
		$this->title = 'Ayo Avram App';
		$this->languages = [
			'us' => ['id'	=> 'us', 'name' => 'English', 	'idiom' => 'english', 	'icon' => 'flag-icon-us'],
			'id' => ['id'	=> 'id', 'name' => 'Indonesia', 'idiom' => 'indonesia', 'icon' => 'flag-icon-id'],
		];
		$this->page = ['home','profile','contact_us'];
		$this->menu = [
			[
				'id'		=> 'sidebarnav',
				'name'	=> 'home',
				'icon'	=> 'mdi mdi-gauge',
				'class'	=> 'waves-effect waves-dark',
				'child' => []
			],[
				'name'	=> 'profile',
				'icon'	=> 'mdi mdi-laptop-windows',
				'class'	=> 'waves-effect waves-dark',
				'child' => []
			],[
				'name'	=> 'contact_us',
				'icon'	=> 'mdi mdi-laptop-windows',
				'class'	=> 'waves-effect waves-dark',
				'child' => []
			],
		];
		
		// ============================================================== 
		// Checking parameters => lang
		// ============================================================== 
		if (!isset($this->params->lang) || !in_array($this->params->lang, array_keys($this->languages))) {
			$this->params->lang = 'id';
			$this->idiom = 'indonesia';
		} else {
			$this->idiom = $this->languages[$this->params->lang]['idiom'];
		}
		$this->languages[$this->params->lang]['default'] = true;
		// ============================================================== 
		// Checking parameters => page
		// ============================================================== 
		if (!isset($this->params->page)) {
			$this->params->page = 'home';
		} else if (!in_array($this->params->page, $this->page)) {
				$this->throw_error();
		} 
			
		// ============================================================== 
		// Setting companion data
		// ============================================================== 
		$this->sub_companion_data = [
			'title' 		=> $this->title,
			'language'	=> $this->getLanguage(),
		];
		$this->companion_data = [
			'title' 		=> $this->title,
			'language'	=> $this->getLanguage(),
			'languages' => $this->getLanguageOptions($this->languages),
			'menu' 			=> $this->getMenuRecursively($this->menu),
			'content' 	=> $this->xfenom->view('pages/'.$this->params->page.'.fenom.html', $this->sub_companion_data, true),
			'footer' 		=> '© 2018 Ayo Avram Application by simpi-pro.com',
		];
		
	}
	
	// public function index() {echo 'Frontend OK !';}
	public function index() { $this->xfenom->view('index.fenom.html', $this->companion_data); }
	// public function index() {$this->xfenom->view('activation.fenom.html');}
	// public function index() {$this->xsmarty->view('index.smarty.html');}
	
	private function single_view($page) { 
		$this->xfenom->view('pages/'.$page.'.fenom.html', $this->companion_data); 
	}
	
	private function throw_error($errNo = 404, $message = '', $url = '') {
		$this->params->page = 'error';
		$this->lang->load('frontend',$this->idiom);
		$this->xfenom->view('pages/errors/error.fenom.html', [
			'title' 	=> $this->title,
			'language'	=> $this->getLanguage(),
			'error_code' 	=> $errNo,
			'error_name' 	=> $this->f->lang('err_'.$errNo),
			'error_desc' 	=> empty($message) ? $this->f->lang('errDesc_'.$errNo) : $message,
			'message'	=> $message,
			'url' 		=> !empty($url) ? $url : $this->setURL($this->params->lang, 'home'),
			'footer' 	=> '© 2018 Ayo Avram Application by simpi-pro.com'
		]);
	}
	
	private function getLanguageOptions($data = []) {
		$r = '';
		foreach ($data as $k => $v) {
			$id 		= isset($v['id']) || !empty($v['id']) ? 'id="'.$v['id'].'"' : '';
			$icon 	= isset($v['icon']) || !empty($v['icon']) ? '<i class="flag-icon '.$v['icon'].'"></i>' : '';
			$url		= BASE_URL.'frontend?lang='.$v['id'].'&page='.$this->params->page;
			
			if (isset($v['default'])) {
				$d = '<a class="nav-link dropdown-toggle text-muted waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> '.$icon.'</a>';
			} else {
				$r .= '<a class="dropdown-item" href="'.$url.'">'.$icon.' '.$v['name'].'</a>';
			}
		}
		return $d.'<div class="dropdown-menu dropdown-menu-right">'.$r.'</div>';
	}
	
	private function getMenuRecursively($data = []) {
		$r = ''; 
		$lang = $this->getLanguage('menu');
		foreach ($data as $k => $v) {
			$id 		= isset($v['id']) || !empty($v['id']) ? 'id="'.$v['id'].'"' : '';
			$icon 	= isset($v['icon']) || !empty($v['icon']) ? '<i class="'.$v['icon'].'"></i>' : '';
			$has_child = (isset($v['child']) && !empty($v['child'])) ? 'has-arrow ' : '';
			$class 	= isset($v['class']) || !empty($v['class']) ? 'class="'.$has_child.$v['class'].'"' : '';
			$url 		= 'href="'.$this->setURL($this->params->lang, $v['name']).'"';
			$name		= isset($lang[$v['name']]['name']) ? $lang[$v['name']]['name'] : 'noname';
			
			if ($k == 0)
				$r .= '<ul '.$id.'>';
			
			$r .= '<li> <a id="menu" name="'.$v['name'].'" '.$class.' '.$url.' aria-expanded="false">'.$icon.'<span class="hide-menu">'.$name.' </span></a>';
			if (isset($v['child']) && !empty($v['child'])) {
				$r .= $this->getMenuRecursively($v['child']);
			}
			$r .= '</li>';
		}
		// $this->f->debug('<textarea >'.$r.'</ul>'.'</textarea >');
		return $r.'</ul>';
	}

	private function getLanguage($type = '') {
		// Load the base file, so any others found can override it
		$basepath = APPPATH.'language/'.$this->idiom.'/frontend_lang.php';
		if (($found = file_exists($basepath)) === TRUE)
		{
			include($basepath);
			if (empty($type))
				return array_merge($lang, $page[$this->params->page]);
			else if ($type == 'page')
				return $page[$this->params->page];
			else if ($type == 'menu')
				return $menu;
		}
		return '';
	}

	private function setURL($lang, $page, $token = null) {
		return BASE_URL.'frontend'
			.'?lang='.$lang
			.'&page='.$page;
	}
	
	function getContent()
	{
		$lang = $this->getLanguage('page');
		$content = $this->xfenom->view('pages/'.$this->params->page.'.fenom.html', $this->sub_companion_data, true);
		$this->f->response(TRUE, ['title' => $lang['title'], 'content' => $content, 'language' => $lang]);
	}
	function x_auth()
	{
		$mode = isset($this->params->mode) ? $this->params->mode : null;
		if (!in_array($mode, ['activation']))
			$this->xfenom->view('activation.error.fenom.html', ['message' => 'Invalid authentication mode !']);
		
		if ($mode == 'activation'){
			$this->load->library(['auth']);
			list($success, $message) = $this->auth->activation();
			if (!$success){
				$this->xfenom->view('activation.error.fenom.html', ['message' => $message]);
			}
			
			$this->xfenom->view('activation.success.fenom.html', ['message' => $message]);
		}
	}
}
