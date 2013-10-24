<?php
class ControllerDevDev extends Controller {
	protected $preload_language = array();

	protected $preload_model = array();

	private $error = array();

	public $template = './template/dev.tpl';

	public function index(){
		if(! $this->validate()){
			$this->redirect(HTTP_PATH);
			exit;
		}
		//$this->load->model('catalog/product');

		//$this->response->setOutput($this->render());
	}
	public function cii(){
		if(! $this->validate()){
			$this->redirect(HTTP_PATH);
			exit;
		}
		if($this->request->server['REQUEST_METHOD'] == 'POST'){
			if(empty($this->request->post['cii'])){
				$this->error[] = 'Cii is required !';
			}else{
				$cii = $this->request->post['cii'];

				if(isset($this->request->post['model'])){
					$fileName  = DIR_APPLICATION . 'model/' . $cii . '.php';

					$className = 'Model' . strtr(ucwords(preg_replace('/[^a-zA-Z0-9]/', ' ', $cii)), array(' ' => ''));

					if(is_file($fileName)){
						$this->error[] = $className . ' file is existed !';
					}else{
						$template = new Template();
						$template->data['className'] = $className;
						$template->data['methods'] = array();
						if($this->request->post['model_methods']){
							$methods = explode("\n", trim(strtr($this->request->post['model_methods'], array("\r"=>''))));

							if($methods){
								foreach ($methods as $method){
									if(trim($method)=='') continue;

									$mary = explode('/', $method);

									if(! $mary) continue;

									$methodName = $mary[0];

									if(isset($mary[1]) && trim($mary[1])){
										$methodAccess = $mary[1];
									}else{
										$methodAccess = 'public';
									}

									$template->data['methods'][] = array(
	  					 				'access' => $methodAccess,
	  					 				'method' => $methodName,
									);
								}
							}
						}

						$content = $template->fetch('dev/template/model.tpl');

						if($content){
							is_dir(dirname($fileName)) || mkdir(dirname($fileName), 777, true);

							file_put_contents($fileName, $content);

							$this->error[] = $className . ' file is created !';
						}else{
							$this->error[] = $className . ' file is empty !';
						}
					}
				}
				if(isset($this->request->post['controller'])){
					$fileName  = DIR_APPLICATION . 'controller/' . $cii . '.php';

					$className = 'Controller' . strtr(ucwords(preg_replace('/[^a-zA-Z0-9]/', ' ', $cii)), array(' ' => ''));

					if(is_file($fileName)){
						$this->error[] = $className . ' file is existed !';
					}else{
						$template = new Template();
						$template->data['cii'] = $cii;
						$template->data['className'] = $className;
						$template->data['methods'] = array();
						if($this->request->post['controller_methods']){
							$methods = explode("\n", trim(strtr($this->request->post['controller_methods'], array("\r"=>''))));

							if($methods){
								foreach ($methods as $method){
									if(trim($method)=='') continue;

									$mary = explode('/', $method);

									if(! $mary) continue;

									$methodName = $mary[0];

									if(isset($mary[1]) && trim($mary[1])){
										$methodAccess = $mary[1];
									}else{
										$methodAccess = 'public';
									}

									$template->data['methods'][] = array(
	  					 				'access' => $methodAccess,
	  					 				'method' => $methodName,
									);
								}

							}
						}

						$content = $template->fetch('dev/template/controller.tpl');

						if($content){
							is_dir(dirname($fileName)) || mkdir(dirname($fileName), 777, true);

							file_put_contents($fileName, $content);

							$this->error[] = $className . ' file is created !';
						}else{
							$this->error[] = $className . '\'s content is empty !';
						}
					}
				}
				if(isset($this->request->post['language'])){
					$fileName  = DIR_LANGUAGE . $this->config->get('config_admin_language') . '/' . $cii . '.php';

					if(is_file($fileName)){
						$this->error[] = $this->config->get('config_admin_language') . '/' . $cii . ' file is existed !';
					}else{
						$template = new Template();
						$template->data['texts'] = array();
						if($this->request->post['language_texts']){
							$texts = explode("\n", trim(strtr($this->request->post['language_texts'], array("\r"=>''))));

							$header_title = '';

							if($texts){
								foreach ($texts as $text){
									if(trim($text)=='') continue;

									$mary = explode(';;;', $text);

									if((! $mary) || trim($mary[0])=='') continue;

									if($mary[0] == 'heading_title'){
										$header_title = $mary[1];
										continue;
									}

									$template->data['texts'][$mary[0]] = addcslashes(isset($mary[1]) ? $mary[1] : '', '\'');
								}
							}
						}

						ksort($template->data['texts']);

						$template->data['texts'] = array_merge(array('header_title' => $header_title), $template->data['texts']);

						$content = $template->fetch('dev/template/language.tpl');

						if($content){
							is_dir(dirname($fileName)) || mkdir(dirname($fileName), 777, true);

							file_put_contents($fileName, $content);

							$this->error[] = strtr($fileName, array(DIR_LANGUAGE => '')) . ' file is created !';
						}else{
							$this->error[] = strtr($fileName, array(DIR_LANGUAGE => '')) . '\'s content is empty !';
						}
					}
				}
				if(isset($this->request->post['view'])){
					$fileName  = DIR_TEMPLATE . $cii . '_list.tpl';

					if(is_file($fileName)){
						$this->error[] = $cii . ' file is existed !';
					}else{
						$template = new Template();

						//$template->data['view_content'] = html_entity_decode($this->request->post['view_content'], ENT_QUOTES, 'UTF-8');

						$content = $template->fetch('dev/template/view_list.tpl');

						if($content){
							is_dir(dirname($fileName)) || mkdir(dirname($fileName), 777, true);

							file_put_contents($fileName, $content);

							$this->error[] = strtr($fileName, array(DIR_TEMPLATE => '')) . ' file is created !';
						}else{
							$this->error[] = strtr($fileName, array(DIR_TEMPLATE => '')) . '\'s content is empty !';
						}
					}
					// form
					$fileName  = DIR_TEMPLATE . $cii . '_form.tpl';

					if(is_file($fileName)){
						$this->error[] = $cii . ' file is existed !';
					}else{
						$template = new Template();

						//$template->data['view_content'] = html_entity_decode($this->request->post['view_content'], ENT_QUOTES, 'UTF-8');

						$content = $template->fetch('dev/template/view_form.tpl');

						if($content){
							is_dir(dirname($fileName)) || mkdir(dirname($fileName), 777, true);

							file_put_contents($fileName, $content);

							$this->error[] = strtr($fileName, array(DIR_TEMPLATE => '')) . ' file is created !';
						}else{
							$this->error[] = strtr($fileName, array(DIR_TEMPLATE => '')) . '\'s content is empty !';
						}
					}
				}
			}
		}

		$pary = array('cii', 'model', 'model_methods', 'controller', 'controller_methods', 'language', 'language_texts', 'view', 'view_content');

		foreach($pary as $val){
			if(isset($this->request->post[$val])){
				$this->data[$val] = $this->request->post[$val];
			}else{
				$this->data[$val] = '';
			}
		}

		$this->data['error'] = implode('<br/>', $this->error);

		$this->data['action'] = $this->url->link('dev/dev/cii', 'token='.$this->session->data['token'], 'SSL');

		$this->template = 'dev/cii.tpl';

		$this->response->setOutput($this->render());
	}

	public function export(){
		if(! $this->validate()){
			$this->redirect(HTTP_PATH);
			exit;
		}

		$this->load->ext('Excel');

		$header = array('姓名','郵箱','等級','狀態','訂閱','註冊時間');
		$data = array(
		array('千點距','cmd.dos@hotmail.com','普 卡','啟用','是','2013-10-08'),
		array('千點距','mail.song.de.qiang@gmail.com','銀 卡','啟用','否','2013-10-08'),
		);

		$excel = new ExcelE;

		$excel->setData($header, $data);

		$excel->download();

		//$this->response->setOutput($this->render());
	}

	private function validate(){
		return (strpos(strtolower($_SERVER['HTTP_HOST']),'local')!==false) && $this->user->isLogged();
	}
}
?>