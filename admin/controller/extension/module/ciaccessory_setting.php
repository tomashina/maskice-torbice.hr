<?php
class ControllerExtensionModuleCiaccessorySetting extends Controller {
	private $error = array();

	private $code = 'ci_product_accessory';
	private $description = 'Product Accessory - Codinginspect';
	private $status = 1;
	private $sort_order = 0;
	private $events = [
		'admin'	=> [
			[
				'trigger'	=> 'admin/view/catalog/product_form/after',
				'action'	=> 'extension/module/ciaccessory_setting/createAccessoryTab',
			],
			[
				'trigger'	=> 'admin/model/catalog/product/addProduct/after',
				'action'	=> 'extension/module/ciaccessory_setting/addAccessory',
			],
			[
				'trigger'	=> 'admin/model/catalog/product/editProduct/after',
				'action'	=> 'extension/module/ciaccessory_setting/editAccessory',
			],
			[
				'trigger'	=> 'admin/model/catalog/product/deleteProduct/after',
				'action'	=> 'extension/module/ciaccessory_setting/deleteAccessory',
			],
			[
				'trigger'	=> 'admin/view/common/header/after',
				'action'	=> 'extension/module/ciaccessory_setting/addHeaderScript',
			],
		],
		'catalog'	=> [
			[
				'trigger'	=> 'catalog/controller/common/header/before',
				'action'	=> 'extension/ciproduct_accessory/createHeaderScript',
			],
			[
				'trigger'	=> 'catalog/view/product/product/after',
				'action'	=> 'extension/ciproduct_accessory/createAccessoryTab',
			],
			[
				'trigger'	=> 'catalog/controller/journal3/product_advantage/after',
				'action'	=> 'extension/ciproduct_accessory/createJournal3AccessoryTab',
			],
			[
				'trigger'	=> 'catalog/controller/journal3/blocks_example/after',
				'action'	=> 'extension/ciproduct_accessory/createJournal3AccessoryTab',
			],
		],
	];

	public function __construct($registery) {
		parent::__construct($registery);

		$this->load->model('extension/ciproduct_accessory');

		if(VERSION <= '2.3.0.2') {
			$this->module_token = 'token';
			$this->ci_token = isset($this->session->data['token']) ? $this->session->data['token'] : '';

			$this->extension_path = 'extension/extension';
		} else {
			$this->module_token = 'user_token';
			$this->ci_token = isset($this->session->data['user_token']) ? $this->session->data['user_token'] : '';

			$this->extension_path = 'marketplace/extension';
		}

		/* Compatibility for oc 2.3x starts */
		if(VERSION <= '2.3.0.2') {
			foreach($this->events['catalog'] as $key => $value) {
				if(strpos($value['trigger'], 'common/menu') !== false) {
					$this->events['catalog'][$key]['trigger'] = str_replace('common/menu', 'common/header', $this->events['catalog'][$key]['trigger']);
				}

				$explode = explode('/', $value['trigger']);
				if(strpos($value['trigger'], 'catalog/view') !== false && end($explode) == 'after') {
					$this->events['catalog'][$key]['trigger'] = 'catalog/view/*/template/'. substr($value['trigger'], strlen('catalog/view/'));
				}
			}
		}
		/* Compatibility for oc 2.3x ends */
	}

	public function install() {
		$filter_data = [
			'events'		=> $this->events,
			'code'			=> $this->code,
			'description'	=> $this->description,
			'status'		=> $this->status,
			'sort_order'	=> $this->sort_order,
		];

		// Remove Events
		$this->model_extension_ciproduct_accessory->removeEvents($filter_data);

		// Create Events
		$this->model_extension_ciproduct_accessory->createEvents($filter_data);

		// Create Tables
		$this->model_extension_ciproduct_accessory->createTables();
	}

	public function uninstall() {
		$filter_data = [
			'events'		=> $this->events,
			'code'			=> $this->code,
			'description'	=> $this->description,
			'status'		=> $this->status,
			'sort_order'	=> $this->sort_order,
		];

		$this->model_extension_ciproduct_accessory->removeEvents($filter_data);
	}

	public function enableEvents() {
		$this->load->language('extension/module/ciaccessory_setting');

		$json = [];

		if(!$this->config->get('module_ciaccessory_setting_status')) {
			$json['warning'] = $this->language->get('error_permission');
		}

		if (!$this->user->hasPermission('modify', 'extension/module/ciaccessory_setting')) {
			$json['warning'] = $this->language->get('error_permission');
		}

		if(!$json) {
			foreach ($this->events as $folder => $folder_info) {
				$this->model_extension_ciproduct_accessory->enableEvents($this->code .'_'. $folder);
			}

			$this->session->data['success'] = $this->language->get('text_enable_event_success');

			$json['success'] = str_replace('&amp;', '&', $this->url->link('extension/module/ciaccessory_setting', $this->module_token .'=' . $this->ci_token, true));
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function showevents() {
		echo "<pre>";
		if(isset($this->request->get['test']) && $this->request->get['test'] == 'db') {
			// Database Events
			foreach ($this->events as $folder => $folder_info) {
				if($folder_info) {
					$db_events = [];
					$db_events[$folder] = $this->model_extension_ciproduct_accessory->getEventsByCode(['code'	=> $this->code .'_'. $folder]);
					echo "--- (". count($db_events[$folder]).") Database Event for ". $folder;
					echo "\n";
					print_r($db_events);
					echo "\n";
				}
			}
		} else {
			// Private Events
			foreach ($this->events as $folder => $folder_info) {
				if($folder_info) {
					$pr = [];
					foreach ($folder_info as $event) {
						$pr[$folder][] = [
							'event_id'	=> 0,
							'code'		=> $this->code .'_'. $folder,
							'trigger'	=> $event['trigger'],
							'action'	=> $event['action'],
							'status'	=> 1,
							'sort_order'=> 0,
						];
					}

					echo "--- (". count($pr[$folder]).") Privatee Event for ". $folder;
					echo "\n";
					print_r($pr);

					echo "\n";
				}
			}
		}

		echo "</pre>";
	}

	public function index() {
		$this->load->language('extension/module/ciaccessory_setting');

		$this->document->setTitle($this->language->get('text_accessory_setting'));

		/* checking disabled events starts */
		$data['button_enable_event'] = $this->language->get('button_enable_event');
		$data['info_disabled_events'] = $this->language->get('info_disabled_events');

		$disabled_events = 0;
		if($this->config->get('module_ciaccessory_setting_status') && count($this->events)) {
			foreach ($this->events as $folder => $folder_info) {
				$filter_data = [
					'code'			=> $this->code .'_'. $folder,
					'filter_status' => 0,
				];

				$disabled_events += $this->model_extension_ciproduct_accessory->getTotalEvents($filter_data);
			}
		}

		if($disabled_events) {
			$data['action_enable_events'] = str_replace('&amp;', '&', $this->url->link('extension/module/ciaccessory_setting/enableEvents', $this->module_token .'=' . $this->ci_token, true));
		} else {
			$data['action_enable_events'] = '';
		}
		/* checking disabled events ends */

		/* sync new events starts */
		$add_data = [
			'events'		=> $this->events,
			'code'			=> $this->code,
			'description'	=> $this->description,
			'status'		=> $this->status,
			'sort_order'	=> $this->sort_order,
		];

		$this->model_extension_ciproduct_accessory->syncEvents($add_data);
		/* sync new events ends */


		if(isset($this->request->get['store_id'])) {
			$store_id = $this->request->get['store_id'];
		} else {
			$store_id = 0;
		}

		$this->document->addStyle('view/javascript/cicolorpicker/css/colorpicker.css');
		$this->document->addScript('view/javascript/cicolorpicker/js/colorpicker.js');

		$this->load->model('extension/ciproduct_accessory');

		$this->load->model('setting/setting');

		$url = '';

		if(isset($this->request->get['store_id'])) {
			$url .= '&store_id=' . $this->request->get['store_id'];
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

			$this->model_setting_setting->editSetting('module_ciaccessory_setting', $this->request->post, $store_id);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/ciaccessory_setting', $this->module_token .'=' . $this->ci_token . $url, true));
		}

		$data['heading_title'] = $this->language->get('text_accessory_setting');

		$data['text_default'] = $this->language->get('text_default');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_edit_product'] = $this->language->get('text_edit_product');
		$data['text_edit_option'] = $this->language->get('text_edit_option');
		$data['text_edit_description'] = $this->language->get('text_edit_description');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_yes'] = $this->language->get('text_yes');
		$data['text_no'] = $this->language->get('text_no');
		$data['text_circle'] = $this->language->get('text_circle');
		$data['text_square'] = $this->language->get('text_square');
		$data['text_grid'] = $this->language->get('text_grid');
		$data['text_list'] = $this->language->get('text_list');
		$data['text_left'] = $this->language->get('text_left');
		$data['text_center'] = $this->language->get('text_center');
		$data['text_right'] = $this->language->get('text_right');
		$data['text_color'] = $this->language->get('text_color');
		$data['text_tab_inside'] = $this->language->get('text_tab_inside');
		$data['text_tab_outside'] = $this->language->get('text_tab_outside');

		$data['entry_store'] = $this->language->get('entry_store');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_text_alignment'] = $this->language->get('entry_text_alignment');
		$data['entry_display_title'] = $this->language->get('entry_display_title');
		$data['entry_display_image'] = $this->language->get('entry_display_image');
		$data['entry_display_name'] = $this->language->get('entry_display_name');
		$data['entry_display_model'] = $this->language->get('entry_display_model');
		$data['entry_display_price'] = $this->language->get('entry_display_price');
		$data['entry_display_qty'] = $this->language->get('entry_display_qty');
		$data['entry_display_button'] = $this->language->get('entry_display_button');
		$data['entry_image_type'] = $this->language->get('entry_image_type');
		$data['entry_image_size'] = $this->language->get('entry_image_size');
		$data['entry_width'] = $this->language->get('entry_width');
		$data['entry_height'] = $this->language->get('entry_height');
		$data['entry_display_layout'] = $this->language->get('entry_display_layout');
		$data['entry_title'] = $this->language->get('entry_title');
		$data['entry_tab_title'] = $this->language->get('entry_tab_title');
		$data['entry_backgroundcolor'] = $this->language->get('entry_backgroundcolor');
		$data['entry_bordercolor'] = $this->language->get('entry_bordercolor');
		$data['entry_textcolor'] = $this->language->get('entry_textcolor');
		$data['entry_button_textcolor'] = $this->language->get('entry_button_textcolor');
		$data['entry_button_backgroundcolor'] = $this->language->get('entry_button_backgroundcolor');
		$data['entry_button_hover_backgroundcolor'] = $this->language->get('entry_button_hover_backgroundcolor');
		$data['entry_button_text_backgroundcolor'] = $this->language->get('entry_button_text_backgroundcolor');
		$data['entry_button_hover_textcolor'] = $this->language->get('entry_button_hover_textcolor');
		$data['entry_display_widget'] = $this->language->get('entry_display_widget');
		$data['entry_top_description'] = $this->language->get('entry_top_description');
		$data['entry_bottom_description'] = $this->language->get('entry_bottom_description');
		$data['entry_find_before'] = $this->language->get('entry_find_before');
		$data['entry_custom_css'] = $this->language->get('entry_custom_css');

		$data['help_find_before'] = $this->language->get('help_find_before');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['image_size'])) {
			$data['error_image_size'] = $this->error['image_size'];
		} else {
			$data['error_image_size'] = '';
		}

		if (isset($this->error['image_thumb_size'])) {
			$data['error_image_thumb_size'] = $this->error['image_thumb_size'];
		} else {
			$data['error_image_thumb_size'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}

		if (isset($this->error['tab_title'])) {
			$data['error_tab_title'] = $this->error['tab_title'];
		} else {
			$data['error_tab_title'] = array();
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', $this->module_token .'=' . $this->ci_token, true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link($this->extension_path, $this->module_token .'=' . $this->ci_token . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_accessory_setting'),
			'href' => $this->url->link('extension/module/ciaccessory_setting', $this->module_token .'=' . $this->ci_token, true)
		);

		$data['store_id'] = $store_id;
		if(isset($store_id)) {
			$data['action'] = $this->url->link('extension/module/ciaccessory_setting', $this->module_token .'=' . $this->ci_token . '&store_id='. $store_id, true);
		} else{
			$data['action'] = $this->url->link('extension/module/ciaccessory_setting', $this->module_token .'=' . $this->ci_token, true);
		}

		$data['module_token'] = $this->module_token;
		$data['ci_token'] = $this->session->data[$this->module_token];

		$data['cancel'] = $this->url->link($this->extension_path, $this->module_token .'=' . $this->ci_token . '&type=module', true);

		$this->load->model('setting/store');
		$data['stores'] = $this->model_setting_store->getStores();

		if ($this->request->server['REQUEST_METHOD'] != 'POST') {
			$module_info = $this->model_setting_setting->getSetting('module_ciaccessory_setting',  $store_id);
		}

		if (isset($this->request->post['module_ciaccessory_setting_status'])) {
			$data['module_ciaccessory_setting_status'] = $this->request->post['module_ciaccessory_setting_status'];
		} else if (isset($module_info['module_ciaccessory_setting_status'])) {
			$data['module_ciaccessory_setting_status'] = $module_info['module_ciaccessory_setting_status'];
		} else {
			$data['module_ciaccessory_setting_status'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_display_layout'])) {
			$data['module_ciaccessory_setting_display_layout'] = $this->request->post['module_ciaccessory_setting_display_layout'];
		} else if (isset($module_info['module_ciaccessory_setting_display_layout'])) {
			$data['module_ciaccessory_setting_display_layout'] = $module_info['module_ciaccessory_setting_display_layout'];
		} else {
			$data['module_ciaccessory_setting_display_layout'] = 'grid';
		}

		if (isset($this->request->post['module_ciaccessory_setting_widget'])) {
			$data['module_ciaccessory_setting_widget'] = $this->request->post['module_ciaccessory_setting_widget'];
		} else if (isset($module_info['module_ciaccessory_setting_widget'])) {
			$data['module_ciaccessory_setting_widget'] = $module_info['module_ciaccessory_setting_widget'];
		} else {
			$data['module_ciaccessory_setting_widget'] = 'tab_outside';
		}

		if (isset($this->request->post['module_ciaccessory_setting_display_title'])) {
			$data['module_ciaccessory_setting_display_title'] = $this->request->post['module_ciaccessory_setting_display_title'];
		} else if (isset($module_info['module_ciaccessory_setting_display_title'])) {
			$data['module_ciaccessory_setting_display_title'] = $module_info['module_ciaccessory_setting_display_title'];
		} else {
			$data['module_ciaccessory_setting_display_title'] = '1';
		}

		if (isset($this->request->post['module_ciaccessory_setting_title'])) {
			$data['module_ciaccessory_setting_title'] = $this->request->post['module_ciaccessory_setting_title'];
		} else if (isset($module_info['module_ciaccessory_setting_title'])) {
			$data['module_ciaccessory_setting_title'] = $module_info['module_ciaccessory_setting_title'];
		} else {
			$data['module_ciaccessory_setting_title'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_display_image'])) {
			$data['module_ciaccessory_setting_display_image'] = $this->request->post['module_ciaccessory_setting_display_image'];
		} else if (isset($module_info['module_ciaccessory_setting_display_image'])) {
			$data['module_ciaccessory_setting_display_image'] = $module_info['module_ciaccessory_setting_display_image'];
		} else {
			$data['module_ciaccessory_setting_display_image'] = '1';
		}

		if (isset($this->request->post['module_ciaccessory_setting_display_name'])) {
			$data['module_ciaccessory_setting_display_name'] = $this->request->post['module_ciaccessory_setting_display_name'];
		} else if (isset($module_info['module_ciaccessory_setting_display_name'])) {
			$data['module_ciaccessory_setting_display_name'] = $module_info['module_ciaccessory_setting_display_name'];
		} else {
			$data['module_ciaccessory_setting_display_name'] = '1';
		}

		if (isset($this->request->post['module_ciaccessory_setting_display_model'])) {
			$data['module_ciaccessory_setting_display_model'] = $this->request->post['module_ciaccessory_setting_display_model'];
		} else if (isset($module_info['module_ciaccessory_setting_display_model'])) {
			$data['module_ciaccessory_setting_display_model'] = $module_info['module_ciaccessory_setting_display_model'];
		} else {
			$data['module_ciaccessory_setting_display_model'] = '1';
		}

		if (isset($this->request->post['module_ciaccessory_setting_display_price'])) {
			$data['module_ciaccessory_setting_display_price'] = $this->request->post['module_ciaccessory_setting_display_price'];
		} else if (isset($module_info['module_ciaccessory_setting_display_price'])) {
			$data['module_ciaccessory_setting_display_price'] = $module_info['module_ciaccessory_setting_display_price'];
		} else {
			$data['module_ciaccessory_setting_display_price'] = '1';
		}

		if (isset($this->request->post['module_ciaccessory_setting_display_qty'])) {
			$data['module_ciaccessory_setting_display_qty'] = $this->request->post['module_ciaccessory_setting_display_qty'];
		} else if (isset($module_info['module_ciaccessory_setting_display_qty'])) {
			$data['module_ciaccessory_setting_display_qty'] = $module_info['module_ciaccessory_setting_display_qty'];
		} else {
			$data['module_ciaccessory_setting_display_qty'] = '1';
		}

		if (isset($this->request->post['module_ciaccessory_setting_display_button'])) {
			$data['module_ciaccessory_setting_display_button'] = $this->request->post['module_ciaccessory_setting_display_button'];
		} else if (isset($module_info['module_ciaccessory_setting_display_button'])) {
			$data['module_ciaccessory_setting_display_button'] = $module_info['module_ciaccessory_setting_display_button'];
		} else {
			$data['module_ciaccessory_setting_display_button'] = '1';
		}

		if (isset($this->request->post['module_ciaccessory_setting_image_type'])) {
			$data['module_ciaccessory_setting_image_type'] = $this->request->post['module_ciaccessory_setting_image_type'];
		} else if (isset($module_info['module_ciaccessory_setting_image_type'])) {
			$data['module_ciaccessory_setting_image_type'] = $module_info['module_ciaccessory_setting_image_type'];
		} else {
			$data['module_ciaccessory_setting_image_type'] = 'square';
		}

		if (isset($this->request->post['module_ciaccessory_setting_text_alignment'])) {
			$data['module_ciaccessory_setting_text_alignment'] = $this->request->post['module_ciaccessory_setting_text_alignment'];
		} else if (isset($module_info['module_ciaccessory_setting_text_alignment'])) {
			$data['module_ciaccessory_setting_text_alignment'] = $module_info['module_ciaccessory_setting_text_alignment'];
		} else {
			$data['module_ciaccessory_setting_text_alignment'] = 'text-left';
		}

		if (isset($this->request->post['module_ciaccessory_setting_image_width'])) {
			$data['module_ciaccessory_setting_image_width'] = $this->request->post['module_ciaccessory_setting_image_width'];
		} else if (isset($module_info['module_ciaccessory_setting_image_width'])) {
			$data['module_ciaccessory_setting_image_width'] = $module_info['module_ciaccessory_setting_image_width'];
		} else {
			$data['module_ciaccessory_setting_image_width'] = '110';
		}

		if (isset($this->request->post['module_ciaccessory_setting_image_height'])) {
			$data['module_ciaccessory_setting_image_height'] = $this->request->post['module_ciaccessory_setting_image_height'];
		} else if (isset($module_info['module_ciaccessory_setting_image_height'])) {
			$data['module_ciaccessory_setting_image_height'] = $module_info['module_ciaccessory_setting_image_height'];
		} else {
			$data['module_ciaccessory_setting_image_height'] = '110';
		}

		if (isset($this->request->post['module_ciaccessory_setting_backgroundcolor'])) {
			$data['module_ciaccessory_setting_backgroundcolor'] = $this->request->post['module_ciaccessory_setting_backgroundcolor'];
		} else if (isset($module_info['module_ciaccessory_setting_backgroundcolor'])) {
			$data['module_ciaccessory_setting_backgroundcolor'] = $module_info['module_ciaccessory_setting_backgroundcolor'];
		} else {
			$data['module_ciaccessory_setting_backgroundcolor'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_textcolor'])) {
			$data['module_ciaccessory_setting_textcolor'] = $this->request->post['module_ciaccessory_setting_textcolor'];
		} else if (isset($module_info['module_ciaccessory_setting_textcolor'])) {
			$data['module_ciaccessory_setting_textcolor'] = $module_info['module_ciaccessory_setting_textcolor'];
		} else {
			$data['module_ciaccessory_setting_textcolor'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_bordercolor'])) {
			$data['module_ciaccessory_setting_bordercolor'] = $this->request->post['module_ciaccessory_setting_bordercolor'];
		} else if (isset($module_info['module_ciaccessory_setting_bordercolor'])) {
			$data['module_ciaccessory_setting_bordercolor'] = $module_info['module_ciaccessory_setting_bordercolor'];
		} else {
			$data['module_ciaccessory_setting_bordercolor'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_button_backgroundcolor'])) {
			$data['module_ciaccessory_setting_button_backgroundcolor'] = $this->request->post['module_ciaccessory_setting_button_backgroundcolor'];
		} else if (isset($module_info['module_ciaccessory_setting_button_backgroundcolor'])) {
			$data['module_ciaccessory_setting_button_backgroundcolor'] = $module_info['module_ciaccessory_setting_button_backgroundcolor'];
		} else {
			$data['module_ciaccessory_setting_button_backgroundcolor'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_button_textcolor'])) {
			$data['module_ciaccessory_setting_button_textcolor'] = $this->request->post['module_ciaccessory_setting_button_textcolor'];
		} else if (isset($module_info['module_ciaccessory_setting_button_textcolor'])) {
			$data['module_ciaccessory_setting_button_textcolor'] = $module_info['module_ciaccessory_setting_button_textcolor'];
		} else {
			$data['module_ciaccessory_setting_button_textcolor'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_button_hover_backgroundcolor'])) {
			$data['module_ciaccessory_setting_button_hover_backgroundcolor'] = $this->request->post['module_ciaccessory_setting_button_hover_backgroundcolor'];
		} else if (isset($module_info['module_ciaccessory_setting_button_hover_backgroundcolor'])) {
			$data['module_ciaccessory_setting_button_hover_backgroundcolor'] = $module_info['module_ciaccessory_setting_button_hover_backgroundcolor'];
		} else {
			$data['module_ciaccessory_setting_button_hover_backgroundcolor'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_button_hover_textcolor'])) {
			$data['module_ciaccessory_setting_button_hover_textcolor'] = $this->request->post['module_ciaccessory_setting_button_hover_textcolor'];
		} else if (isset($module_info['module_ciaccessory_setting_button_hover_textcolor'])) {
			$data['module_ciaccessory_setting_button_hover_textcolor'] = $module_info['module_ciaccessory_setting_button_hover_textcolor'];
		} else {
			$data['module_ciaccessory_setting_button_hover_textcolor'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_image_thumb_width'])) {
			$data['module_ciaccessory_setting_image_thumb_width'] = $this->request->post['module_ciaccessory_setting_image_thumb_width'];
		} else if (isset($module_info['module_ciaccessory_setting_image_thumb_width'])) {
			$data['module_ciaccessory_setting_image_thumb_width'] = $module_info['module_ciaccessory_setting_image_thumb_width'];
		} else {
			$data['module_ciaccessory_setting_image_thumb_width'] = '200';
		}

		if (isset($this->request->post['module_ciaccessory_setting_image_thumb_height'])) {
			$data['module_ciaccessory_setting_image_thumb_height'] = $this->request->post['module_ciaccessory_setting_image_thumb_height'];
		} else if (isset($module_info['module_ciaccessory_setting_image_thumb_height'])) {
			$data['module_ciaccessory_setting_image_thumb_height'] = $module_info['module_ciaccessory_setting_image_thumb_height'];
		} else {
			$data['module_ciaccessory_setting_image_thumb_height'] = '200';
		}

		if (isset($this->request->post['module_ciaccessory_setting_custom_css'])) {
			$data['module_ciaccessory_setting_custom_css'] = $this->request->post['module_ciaccessory_setting_custom_css'];
		} else if (isset($module_info['module_ciaccessory_setting_custom_css'])) {
			$data['module_ciaccessory_setting_custom_css'] = $module_info['module_ciaccessory_setting_custom_css'];
		} else {
			$data['module_ciaccessory_setting_custom_css'] = '';
		}

		// Journal3 Compatibility
		$data['entry_module_id'] = $this->language->get('entry_module_id');
		$data['config_theme'] = $this->config->get('config_theme');
		if (isset($this->request->post['module_ciaccessory_setting_module_id'])) {
			$data['module_ciaccessory_setting_module_id'] = $this->request->post['module_ciaccessory_setting_module_id'];
		} else if (isset($module_info['module_ciaccessory_setting_module_id'])) {
			$data['module_ciaccessory_setting_module_id'] = $module_info['module_ciaccessory_setting_module_id'];
		} else {
			$data['module_ciaccessory_setting_module_id'] = '';
		}

		if (isset($this->request->post['module_ciaccessory_setting_find_before'])) {
			$data['module_ciaccessory_setting_find_before'] = $this->request->post['module_ciaccessory_setting_find_before'];
		} else if (!empty($module_info['module_ciaccessory_setting_find_before'])) {
			$data['module_ciaccessory_setting_find_before'] = $module_info['module_ciaccessory_setting_find_before'];
		} else {
			$data['module_ciaccessory_setting_find_before'] = '<ul class="nav nav-tabs">';
		}

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$file_variable = 'template_engine';
		$file_type = 'template';
		$this->config->set($file_variable, $file_type);

		$this->response->setOutput($this->load->view('extension/ciaccessory/setting', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/ciaccessory_setting')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}


		foreach ($this->request->post['module_ciaccessory_setting_title'] as $language_id => $value) {
			if($this->request->post['module_ciaccessory_setting_display_title']) {
				if ((utf8_strlen($value['title']) < 2) || (utf8_strlen($value['title']) > 255)) {
					$this->error['title'][$language_id] = $this->language->get('error_title');
				}
			}

			if(isset($this->request->post['module_ciaccessory_setting_widget']) && $this->request->post['module_ciaccessory_setting_widget'] == 'tab_inside') {
				if ((utf8_strlen($value['tab_title']) < 2) || (utf8_strlen($value['tab_title']) > 255)) {
					$this->error['tab_title'][$language_id] = $this->language->get('error_tab_title');
				}
			}
		}

		if($this->request->post['module_ciaccessory_setting_display_image']) {
			if (!$this->request->post['module_ciaccessory_setting_image_width'] || !$this->request->post['module_ciaccessory_setting_image_height']) {
				$this->error['image_size'] = $this->language->get('error_image_size');
			}
		}

		if (!$this->request->post['module_ciaccessory_setting_image_thumb_width'] || !$this->request->post['module_ciaccessory_setting_image_thumb_height']) {
			$this->error['image_thumb_size'] = $this->language->get('error_image_size');
		}

		return !$this->error;
	}

	public function createAccessoryTab(&$route, &$args, &$output) {

		if (!$this->config->get('module_ciaccessory_setting_status')) {
			return;
		}
		
		$this->load->language('extension/module/ciproduct_accessory');

	    $this->load->model('extension/ciproduct_accessory');

		$data['tab_product_accessories'] = $this->language->get('tab_product_accessories');
		$data['entry_accessory'] = $this->language->get('entry_accessory');
		$data['entry_acc_remove'] = $this->language->get('entry_acc_remove');
		$data['help_accessory'] = $this->language->get('help_accessory');
		$data['placeholder_accessory'] = $this->language->get('placeholder_accessory');

		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_remove'] = $this->language->get('button_remove');

		$data['module_token'] = $this->module_token;
		$data['ci_token'] = $this->session->data[$this->module_token];

		if (isset($this->request->post['product_accessory'])) {
			$product_accessories = $this->request->post['product_accessory'];
		} elseif (isset($this->request->get['product_id']) && $this->request->server['REQUEST_METHOD'] != 'POST') {
			$product_accessories = $this->model_extension_ciproduct_accessory->getProductAccessories($this->request->get['product_id']);
		} else {
			$product_accessories = array();
		}

		$data['ci_product_accessories'] = array();
		foreach ($product_accessories as $product_accessory) {
			$accessory_info = $this->model_catalog_product->getProduct($product_accessory['accessory_id']);

			if ($accessory_info) {
				if (is_file(DIR_IMAGE . $accessory_info['image'])) {
					$image = $this->model_tool_image->resize($accessory_info['image'], 100, 100);
				} else {
					$image = $this->model_tool_image->resize('no_image.png', 100, 100);
				}

				$data['ci_product_accessories'][] = array(
					'accessory_id' 		=> $accessory_info['product_id'],
					'image' 	 		=> $image,
					'name'       		=> $accessory_info['name'],
					'model'       		=> $accessory_info['model'],
					'sort_order'       	=> $product_accessory['sort_order'],
					'status'       		=> $product_accessory['status'],
					'href'       		=> $this->url->link('catalog/product/edit', $this->module_token .'=' . $this->ci_token . '&product_id='. $accessory_info['product_id'], true),
				);
			}
		}

		$find = '<li><a href="#tab-design" data-toggle="tab">' . $this->language->get('tab_design') . '</a></li>';
		$output = str_replace($find, $find .'<li><a href="#tab-ciproduct-accessories " data-toggle="tab">' . $data['tab_product_accessories'] . '</a></li>', $output);

		$accessory_tab_html = $this->load->view('extension/ciaccessory/product_tab', $data);


		$find = '<div class="tab-pane" id="tab-design">';
		$output = str_replace($find, $accessory_tab_html . $find, $output);
	}

	public function productAutocomplete() {
		$json = array();

		$this->load->model('tool/image');

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_model'])) {
			$this->load->model('catalog/product');
			$this->load->model('catalog/option');

			if (isset($this->request->get['filter_name'])) {
				$filter_name = $this->request->get['filter_name'];
			} else {
				$filter_name = '';
			}

			if (isset($this->request->get['filter_model'])) {
				$filter_model = $this->request->get['filter_model'];
			} else {
				$filter_model = '';
			}

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = 5;
			}

			$filter_data = array(
				'filter_name'  => $filter_name,
				'filter_model' => $filter_model,
				'start'        => 0,
				'limit'        => $limit
			);

			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				$option_data = array();

				$product_options = $this->model_catalog_product->getProductOptions($result['product_id']);

				foreach ($product_options as $product_option) {
					$option_info = $this->model_catalog_option->getOption($product_option['option_id']);

					if ($option_info) {
						$product_option_value_data = array();

						foreach ($product_option['product_option_value'] as $product_option_value) {
							$option_value_info = $this->model_catalog_option->getOptionValue($product_option_value['option_value_id']);

							if ($option_value_info) {
								$product_option_value_data[] = array(
									'product_option_value_id' => $product_option_value['product_option_value_id'],
									'option_value_id'         => $product_option_value['option_value_id'],
									'name'                    => $option_value_info['name'],
									'price'                   => (float)$product_option_value['price'] ? $this->currency->format($product_option_value['price'], $this->config->get('config_currency')) : false,
									'price_prefix'            => $product_option_value['price_prefix']
								);
							}
						}

						$option_data[] = array(
							'product_option_id'    => $product_option['product_option_id'],
							'product_option_value' => $product_option_value_data,
							'option_id'            => $product_option['option_id'],
							'name'                 => $option_info['name'],
							'type'                 => $option_info['type'],
							'value'                => $product_option['value'],
							'required'             => $product_option['required']
						);
					}
				}

				$json[] = array(
					'image'		 => (is_file(DIR_IMAGE . $result['image'])) ? $image = $this->model_tool_image->resize($result['image'], 100, 100) : $image = $this->model_tool_image->resize('no_image.png', 100, 100),
					'href'      => $this->url->link('catalog/product/edit', $this->module_token .'=' . $this->ci_token .'&product_id='. $result['product_id'], true),
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
					'model'      => $result['model'],
					'option'     => $option_data,
					'price'      => $result['price']
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	// admin/model/catalog/product/addProduct/after
	public function addAccessory(&$route, &$args, &$output) {
		if (!$this->config->get('module_ciaccessory_setting_status')) {
			return;
		}

		if (isset($args[0]['product_accessory'])) {
			foreach ($args[0]['product_accessory'] as $product_accessory) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_accessory SET product_id = '" . (int)$output . "', accessory_id = '" . (int)$product_accessory['accessory_id'] . "', sort_order = '" . (int)$product_accessory['sort_order'] . "', status = '" . (int)$product_accessory['status'] . "'");
			}
		}
	}

	// admin/model/catalog/product/editProduct/after
	public function editAccessory(&$route, &$args) {
		if (!$this->config->get('module_ciaccessory_setting_status')) {
			return;
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "product_accessory WHERE product_id = '" . (int)$args[0] . "'");

		if (isset($args[1]['product_accessory'])) {
			foreach ($args[1]['product_accessory'] as $product_accessory) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_accessory SET product_id = '" . (int)$args[0] . "', accessory_id = '" . (int)$product_accessory['accessory_id'] . "', sort_order = '" . (int)$product_accessory['sort_order'] . "', status = '" . (int)$product_accessory['status'] . "'");
			}
		}
	}

	// admin/model/catalog/product/deleteProduct/after
	public function deleteAccessory(&$route, &$args) {
		if (!$this->config->get('module_ciaccessory_setting_status')) {
			return;
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_accessory WHERE product_id = '" . (int)$args[0] . "'");
	}

	// Trigger for admin/view/common/header/after
	public function addHeaderScript(&$route, &$data, &$output) {
		$find = '<script type="text/javascript" src="view/javascript/bootstrap/js/bootstrap.min.js"></script>';
		$add_string = '<script type="text/javascript" src="view/javascript/jquery/jquery-ui/jquery-ui.js"></script>';

		$output = str_replace($find, $add_string ."\n". $find, $output);
	}
}