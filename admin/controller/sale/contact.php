<?php
class ControllerSaleContact extends Controller {
	protected $preload_language = array('sale/contact');

	protected $preload_model = array('setting/store', 'sale/customer_group', 'sale/customer', 'sale/affiliate', 'sale/order');

	private $error = array();

	public function index() {
		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['token'] = $this->session->data['token'];

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/contact', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

    	$this->data['cancel'] = $this->url->link('sale/contact', 'token=' . $this->session->data['token'], 'SSL');

		$this->data['stores'] = $this->model_setting_store->getStores();

		$this->data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups(0);

		$this->template = 'sale/contact.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function send() {
		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$this->user->hasPermission('modify', 'sale/contact')) {
				$json['error']['warning'] = $this->language->get('error_permission');
			}

			if (!$this->request->post['subject']) {
				$json['error']['subject'] = $this->language->get('error_subject');
			}

			if (!$this->request->post['message']) {
				$json['error']['message'] = $this->language->get('error_message');
			}

			if (!$json) {
				$store_info = $this->model_setting_store->getStore($this->request->post['store_id']);

				if ($store_info) {
					$store_name = $store_info['name'];
				} else {
					$store_name = $this->config->get('config_name');
				}

				if (isset($this->request->get['page'])) {
					$page = $this->request->get['page'];
				} else {
					$page = 1;
				}

				$email_total = 0;

				$emails = array();

				switch ($this->request->post['to']) {
					case 'newsletter':
						$customer_data = array(
							'filter_newsletter' => 1,
							'start'             => ($page - 1) * 10,
							'limit'             => 10
						);

						$email_total = $this->model_sale_customer->getTotalCustomers($customer_data);

						$results = $this->model_sale_customer->getCustomers($customer_data);

						foreach ($results as $result) {
							$emails[] = $result['email'];
						}
						break;
					case 'customer_all':
						$customer_data = array(
							'start'  => ($page - 1) * 10,
							'limit'  => 10
						);

						$email_total = $this->model_sale_customer->getTotalCustomers($customer_data);

						$results = $this->model_sale_customer->getCustomers($customer_data);

						foreach ($results as $result) {
							$emails[] = $result['email'];
						}
						break;
					case 'customer_group':
						$customer_data = array(
							'filter_customer_group_id' => $this->request->post['customer_group_id'],
							'start'                    => ($page - 1) * 10,
							'limit'                    => 10
						);

						$email_total = $this->model_sale_customer->getTotalCustomers($customer_data);

						$results = $this->model_sale_customer->getCustomers($customer_data);

						foreach ($results as $result) {
							$emails[$result['customer_id']] = $result['email'];
						}
						break;
					case 'customer':
						if (!empty($this->request->post['customer'])) {
							foreach ($this->request->post['customer'] as $customer_id) {
								$customer_info = $this->model_sale_customer->getCustomer($customer_id);

								if ($customer_info) {
									$emails[] = $customer_info['email'];
								}
							}
						}
						break;
					case 'affiliate_all':
						$affiliate_data = array(
							'start'  => ($page - 1) * 10,
							'limit'  => 10
						);

						$email_total = $this->model_sale_affiliate->getTotalAffiliates($affiliate_data);

						$results = $this->model_sale_affiliate->getAffiliates($affiliate_data);

						foreach ($results as $result) {
							$emails[] = $result['email'];
						}
						break;
					case 'affiliate':
						if (!empty($this->request->post['affiliate'])) {
							foreach ($this->request->post['affiliate'] as $affiliate_id) {
								$affiliate_info = $this->model_sale_affiliate->getAffiliate($affiliate_id);

								if ($affiliate_info) {
									$emails[] = $affiliate_info['email'];
								}
							}
						}
						break;
					case 'product':
						if (isset($this->request->post['product'])) {
							$email_total = $this->model_sale_order->getTotalEmailsByProductsOrdered($this->request->post['product']);

							$results = $this->model_sale_order->getEmailsByProductsOrdered($this->request->post['product'], ($page - 1) * 10, 10);

							foreach ($results as $result) {
								$emails[] = $result['email'];
							}
						}
						break;
				}

				if ($emails) {
					$start = ($page - 1) * 10;
					$end = $start + 10;

					if ($end < $email_total) {
						$json['success'] = sprintf($this->language->get('text_sent'), $start, $email_total);
					} else {
						$json['success'] = $this->language->get('text_success');
					}

					if ($end < $email_total) {
						$json['next'] = str_replace('&amp;', '&', $this->url->link('sale/contact/send', 'token=' . $this->session->data['token'] . '&page=' . ($page + 1), 'SSL'));
					} else {
						$json['next'] = '';
					}

					$message  = '<html dir="ltr" lang="en">' . "\n";
					$message .= '  <head>' . "\n";
					$message .= '    <title>' . $this->request->post['subject'] . '</title>' . "\n";
					$message .= '    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">' . "\n";
					$message .= '  </head>' . "\n";
					$message .= '  <body>' . html_entity_decode($this->request->post['message'], ENT_QUOTES, 'UTF-8') . '</body>' . "\n";
					$message .= '</html>' . "\n";

					foreach ($emails as $email) {
						$mail = new Mail();

						$mail->SetFrom($this->config->get('config_email'), $store_name);
					    $mail->AddAddress($email);
					    $mail->Subject = html_entity_decode($this->request->post['subject'], ENT_QUOTES, 'UTF-8');
					    $mail->MsgHTML($message);
					    $mail->Send();
					}
				}
			}
		}

		$this->response->setOutput(json_encode($json));
	}
}
?>