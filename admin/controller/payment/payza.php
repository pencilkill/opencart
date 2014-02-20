<?php
class ControllerPaymentPayza extends Controller {
	protected $_language = array('payment/payza');

	protected $_model = array('setting/setting', 'localisation/order_status', 'localisation/geo_zone');

	private $error = array(); 

	public function index() {
		$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payza', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

  		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

 		if (isset($this->error['merchant'])) {
			$this->data['error_merchant'] = $this->error['merchant'];
		} else {
			$this->data['error_merchant'] = '';
		}

 		if (isset($this->error['security'])) {
			$this->data['error_security'] = $this->error['security'];
		} else {
			$this->data['error_security'] = '';
		}
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/payza', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$this->data['action'] = $this->url->link('payment/payza', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['payza_merchant'])) {
			$this->data['payza_merchant'] = $this->request->post['payza_merchant'];
		} else {
			$this->data['payza_merchant'] = $this->config->get('payza_merchant');
		}

		if (isset($this->request->post['payza_security'])) {
			$this->data['payza_security'] = $this->request->post['payza_security'];
		} else {
			$this->data['payza_security'] = $this->config->get('payza_security');
		}
		
		$this->data['callback'] = HTTP_CATALOG . 'index.php?route=payment/payza/callback';
		
		if (isset($this->request->post['payza_total'])) {
			$this->data['payza_total'] = $this->request->post['payza_total'];
		} else {
			$this->data['payza_total'] = $this->config->get('payza_total'); 
		} 
				
		if (isset($this->request->post['payza_order_status_id'])) {
			$this->data['payza_order_status_id'] = $this->request->post['payza_order_status_id'];
		} else {
			$this->data['payza_order_status_id'] = $this->config->get('payza_order_status_id'); 
		} 
		
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['payza_geo_zone_id'])) {
			$this->data['payza_geo_zone_id'] = $this->request->post['payza_geo_zone_id'];
		} else {
			$this->data['payza_geo_zone_id'] = $this->config->get('payza_geo_zone_id'); 
		} 

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['payza_status'])) {
			$this->data['payza_status'] = $this->request->post['payza_status'];
		} else {
			$this->data['payza_status'] = $this->config->get('payza_status');
		}
		
		if (isset($this->request->post['payza_sort_order'])) {
			$this->data['payza_sort_order'] = $this->request->post['payza_sort_order'];
		} else {
			$this->data['payza_sort_order'] = $this->config->get('payza_sort_order');
		}

		$this->template = 'payment/payza.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);
				
		$this->response->setOutput($this->render());
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/payza')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		
		if (!$this->request->post['payza_merchant']) {
			$this->error['merchant'] = $this->language->get('error_merchant');
		}

		if (!$this->request->post['payza_security']) {
			$this->error['security'] = $this->language->get('error_security');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>