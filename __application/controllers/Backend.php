<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Backend extends CI_Controller 
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('backend_model');
		
		/* Config: Template Engine for PHP => Fenom/Smarty/Twig/Blade/Volt */
		$this->root_url 	= '__application/views';
		$this->root_dir		= APPPATH.'views';
		$this->init_dir		= 'backend';
		$this->theme 			= 'materialpro';
		define('DIR_CACHE', FCPATH.'__tmp');
		define('DIR_TEMPLATE', $this->root_dir.DIRECTORY_SEPARATOR.$this->init_dir.DIRECTORY_SEPARATOR.$this->theme.DIRECTORY_SEPARATOR);
		define('THEME_URL', BASE_URL.$this->root_url.SEPARATOR.$this->init_dir.SEPARATOR.$this->theme.SEPARATOR);
		define('THEME_PATH', DIR_TEMPLATE);
		$this->load->library(['f', 'xfenom','xsmarty']);
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
		$this->state = ['auth','client','admin'];
		$this->page = [
			'auth' 		=> ['login','register'], 
			'client' 	=> ['dashboard','profile','transaction','subscribe','redeem','performance','chg_pwd'], 
			'admin' 	=> ['dashboard','web_app','mobile_app',], 
		];
		$this->page_action = [
			'login' 		=> ['register' => BASE_URL.'backend'.'?lang='.$this->params->lang.'&state=auth&page=register'],
			'register' 	=> ['login' => BASE_URL.'backend'.'?lang='.$this->params->lang.'&state=auth&page=login'],
		];
		/* 
			$this->sample_menus = [
				[
					'name'	=> 'Template',
					'icon'	=> 'mdi mdi-laptop-windows',
					'class'	=> 'has-arrow waves-effect waves-dark',
					'url'		=> '',
					'child' => [
						['name'	=> 'Menu 1', 'url' => BASE_URL.'backend?page=menu1'],
						['name'	=> 'Menu 2', 'url' => BASE_URL.'backend?page=menu2'],
						['name'	=> 'Menu 3', 'url' => BASE_URL.'backend?page=menu3'],
					]
				],
			]; 
		*/
		$this->menu = [
			'client' 	=> 
			[ 
				[
					'id'		=> 'sidebarnav',
					'name'	=> 'dashboard',
					'icon'	=> 'mdi mdi-gauge',
					'class'	=> 'waves-effect waves-dark',
					'child' => []
				],[
					'name'	=> 'transaction',
					'icon'	=> 'mdi mdi-laptop-windows',
					'class'	=> 'waves-effect waves-dark',
					'child' => []
				],[
					'name'	=> 'subscribe',
					'icon'	=> 'mdi mdi-laptop-windows',
					'class'	=> 'waves-effect waves-dark',
					'child' => []
				],[
					'name'	=> 'redeem',
					'icon'	=> 'mdi mdi-laptop-windows',
					'class'	=> 'waves-effect waves-dark',
					'child' => []
				],
			],
			'admin' 	=> 
			[
				[
					'id'		=> 'sidebarnav',
					'name'	=> 'dashboard',
					'icon'	=> 'mdi mdi-gauge',
					'class'	=> 'has-arrow waves-effect waves-dark',
					'child' => []
				],[
					'name'	=> 'web_app',
					'icon'	=> 'mdi mdi-laptop-windows',
					'class'	=> 'has-arrow waves-effect waves-dark',
					'child' => []
				],[
					'name'	=> 'mobile_app',
					'icon'	=> 'mdi mdi-laptop-windows',
					'class'	=> 'has-arrow waves-effect waves-dark',
					'child' => []
				],
			],
		];
		$this->client_menus = [
			[
				'id'		=> 'sidebarnav',
				'name'	=> 'dashboard',
				'icon'	=> 'mdi mdi-gauge',
				'class'	=> 'waves-effect waves-dark',
				'child' => []
			],[
				'name'	=> 'transaction',
				'icon'	=> 'mdi mdi-laptop-windows',
				'class'	=> 'waves-effect waves-dark',
				'child' => []
			],[
				'name'	=> 'subscribe',
				'icon'	=> 'mdi mdi-laptop-windows',
				'class'	=> 'waves-effect waves-dark',
				'child' => []
			],[
				'name'	=> 'redeem',
				'icon'	=> 'mdi mdi-laptop-windows',
				'class'	=> 'waves-effect waves-dark',
				'child' => []
			],[
				'name'	=> 'performance',
				'icon'	=> 'mdi mdi-laptop-windows',
				'class'	=> 'waves-effect waves-dark',
				'child' => []
			],
		];
		$this->admin_menus = [
			[
				'id'		=> 'sidebarnav',
				'name'	=> 'dashboard',
				'icon'	=> 'mdi mdi-gauge',
				'class'	=> 'has-arrow waves-effect waves-dark',
				'child' => []
			],[
				'name'	=> 'web_app',
				'icon'	=> 'mdi mdi-laptop-windows',
				'class'	=> 'has-arrow waves-effect waves-dark',
				'child' => []
			],[
				'name'	=> 'mobile_app',
				'icon'	=> 'mdi mdi-laptop-windows',
				'class'	=> 'has-arrow waves-effect waves-dark',
				'child' => []
			],
		];
		
		
		
		
		
		// ============================================================== 
		// Checking parameters => lang
		// ============================================================== 
		// if (!isset($this->params->lang) || !in_array($this->params->lang, array_column($this->languages, 'id')))
		if (!isset($this->params->lang) || !in_array($this->params->lang, array_keys($this->languages))) {
			$this->params->lang = 'id';
			$this->idiom = 'indonesia';
		} else {
			$this->idiom = $this->languages[$this->params->lang]['idiom'];
		}
		$this->languages[$this->params->lang]['default'] = true;
		// ============================================================== 
		// Checking parameters => state
		// ============================================================== 
		if (!isset($this->params->state) || !in_array($this->params->state, $this->state))
			$this->throw_error();
		// ============================================================== 
		// Checking parameters => page
		// ============================================================== 
		if (isset($this->params->page)) {
			if (!in_array($this->params->page, $this->page[$this->params->state]))
				$this->throw_error();
		} else {
			if (in_array($this->params->state, ['client','admin']))
				$this->params->page = 'dashboard';
			else
				$this->params->page = 'login';
		}
		// ============================================================== 
		// Checking parameters => token
		// ============================================================== 
		if (in_array($this->params->state, ['client','admin'])) {
			if (isset($this->params->token) || !empty($this->params->token)) {
				
				$this->lang->load('auth', $this->idiom);
				$this->params->agent = 'web';
				list($success, $return) = $this->f->is_valid_token($this->params);
				if (!$success)
					$this->throw_error(403, $return['message']);
				
			} else {
				$this->throw_error(403); 
			}
		}
		
		// $this->login_url = $this->setURL($this->params->lang, 'auth', 'login', null);
		// $this->home_url = $this->setURL($this->params->lang, $this->params->state, 'dashboard', $this->params->token);
		// $this->login_url = BASE_URL.'backend'
			// .'?lang='.$this->params->lang
			// .'&state=auth&page=login';
		// $this->home_url = BASE_URL.'backend'
			// .'?lang='.$this->params->lang
			// .'&state='.$this->params->state
			// .'&page=dashboard'
			// .(isset($this->params->token) ? '&token='.urlencode($this->params->token) : '');
			
			
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
			'menu' 			=> ($this->params->state == 'auth'
											? '' 
											: $this->getMenuRecursively($this->menu[$this->params->state])),
											// : $this->getMenuRecursively($this->{$this->params->state.'_menus'})),
			'action' 		=> isset($this->page_action[$this->params->page]) 
											? $this->page_action[$this->params->page] 
											: '',
			'content' 	=> $this->xfenom->view('pages/'.$this->params->state.'/'.$this->params->page.'.fenom.html', $this->sub_companion_data, true),
			'footer' 		=> '© 2018 Ayo Avram Application by simpi-pro.com',
		];
		
		if ($this->params->state == 'auth')
			$this->single_view($this->params->page);
	}
	
	// function index() {echo 'Backend OK !';}
	// function index() { $this->xfenom->view('index.'.$this->params->lang.'.fenom.html', $this->companion_data); }
	function index() { $this->xfenom->view('index.fenom.html', $this->companion_data); }
	
	private function single_view($page) { 
		// $this->xfenom->view('pages/auth/'.$page.'.'.$this->params->lang.'.fenom.html', $this->companion_data); 
		$this->xfenom->view('pages/'.$this->params->state.'/'.$page.'.fenom.html', $this->companion_data); 
	}
	
	private function throw_error($errNo = 404, $message = '', $url = '') {
		$this->params->page = 'error';
		$this->lang->load('backend',$this->idiom);
		$this->xfenom->view('pages/errors/error.fenom.html', [
			'title' 	=> $this->title,
			'language'	=> $this->getLanguage(),
			'error_code' 	=> $errNo,
			'error_name' 	=> $this->f->lang('err_'.$errNo),
			'error_desc' 	=> empty($message) ? $this->f->lang('errDesc_'.$errNo) : $message,
			'message'	=> $message,
			// 'url' 		=> !empty($url) ? $url : $this->login_url,
			'url' 		=> !empty($url) ? $url : $this->setURL($this->params->lang, 'auth', 'login'),
			'footer' 	=> '© 2018 Ayo Avram Application by simpi-pro.com'
		]);
	}
	
	private function getLanguageOptions($data = []) {
		$r = '';
		foreach ($data as $k => $v) {
			$id 		= isset($v['id']) || !empty($v['id']) ? 'id="'.$v['id'].'"' : '';
			$icon 	= isset($v['icon']) || !empty($v['icon']) ? '<i class="flag-icon '.$v['icon'].'"></i>' : '';
			$url		= BASE_URL.'backend?lang='.$v['id'].'&state='.$this->params->state.'&page='.$this->params->page;
			
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
			$url 		= 'href="'.$this->setURL($this->params->lang, $this->params->state, $v['name'], $this->params->token).'"';
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
		$basepath = APPPATH.'language/'.$this->idiom.'/backend_lang.php';
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

	private function setURL($lang, $state, $page, $token = null) {
		return BASE_URL.'backend'
			.'?lang='.$lang
			.'&state='.$state
			.'&page='.$page
			.(isset($token) ? '&token='.urlencode($token) : '');
	}
	
	function getContent()
	{
		$lang = $this->getLanguage('page');
		$content = $this->xfenom->view('pages/'.$this->params->state.'/'.$this->params->page.'.fenom.html', $this->sub_companion_data, true);
		$this->f->response(TRUE, ['title' => $lang['title'], 'content' => $content, 'language' => $lang]);
	}
}
