<?php
class ControllerExtensionCiproductAccessory extends Controller {
	public function index() {
		if($this->config->get('module_ciaccessory_setting_status')) {
			$data['button_cart'] = $this->language->get('button_cart');
			$data['text_loading'] = $this->language->get('text_loading');

		    $this->load->language('extension/ciproduct_accessory');
		    $this->load->model('extension/ciproduct_accessory');

		    $data['column_image'] = $this->language->get('column_image');
		    $data['column_product'] = $this->language->get('column_product');
		    $data['column_price'] = $this->language->get('column_price');
		    $data['column_quantity'] = $this->language->get('column_quantity');
		    $data['column_action'] = $this->language->get('column_action');

		    $module_ciaccessory_setting_title = $this->config->get('module_ciaccessory_setting_title');
		    $data['text_product_accessories'] = (!empty($module_ciaccessory_setting_title[$this->config->get('config_language_id')]['title'])) ? html_entity_decode($module_ciaccessory_setting_title[$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8') : $this->language->get('text_product_accessories');

		    if(isset($module_ciaccessory_setting_title[$this->config->get('config_language_id')])) {
		    	$data['top_description'] = html_entity_decode($module_ciaccessory_setting_title[$this->config->get('config_language_id')]['top_description'], ENT_QUOTES, 'UTF-8');
		    	$data['bottom_description'] = html_entity_decode($module_ciaccessory_setting_title[$this->config->get('config_language_id')]['bottom_description'], ENT_QUOTES, 'UTF-8');
			} else {
				$data['top_description'] = '';
				$data['bottom_description'] = '';
			}

			$data['custom_css'] = $this->config->get('module_ciaccessory_setting_custom_css');

		    $data['module_ciaccessory_setting_display_layout'] = $this->config->get('module_ciaccessory_setting_display_layout');
		    $data['module_ciaccessory_setting_display_title'] = $this->config->get('module_ciaccessory_setting_display_title');
		    $data['module_ciaccessory_setting_display_image'] = $this->config->get('module_ciaccessory_setting_display_image');
		    $data['module_ciaccessory_setting_display_model'] = $this->config->get('module_ciaccessory_setting_display_model');
		    $data['module_ciaccessory_setting_image_type'] = $this->config->get('module_ciaccessory_setting_image_type');
		    $data['module_ciaccessory_setting_image_width'] = $this->config->get('module_ciaccessory_setting_image_width');
		    $data['module_ciaccessory_setting_image_height'] = $this->config->get('module_ciaccessory_setting_image_height');
		    $data['module_ciaccessory_setting_display_name'] = $this->config->get('module_ciaccessory_setting_display_name');
		    $data['module_ciaccessory_setting_display_price'] = $this->config->get('module_ciaccessory_setting_display_price');
		    $data['module_ciaccessory_setting_text_alignment'] = $this->config->get('module_ciaccessory_setting_text_alignment');
		    $data['module_ciaccessory_setting_display_qty'] = $this->config->get('module_ciaccessory_setting_display_qty');
		    $data['module_ciaccessory_setting_display_button'] = $this->config->get('module_ciaccessory_setting_display_button');

		    $data['module_ciaccessory_setting_backgroundcolor'] = $this->config->get('module_ciaccessory_setting_backgroundcolor');
		    $data['module_ciaccessory_setting_textcolor'] = $this->config->get('module_ciaccessory_setting_textcolor');
		    $data['module_ciaccessory_setting_bordercolor'] = $this->config->get('module_ciaccessory_setting_bordercolor');

		    $data['module_ciaccessory_setting_button_textcolor'] = $this->config->get('module_ciaccessory_setting_button_textcolor');
		    $data['module_ciaccessory_setting_button_backgroundcolor'] = $this->config->get('module_ciaccessory_setting_button_backgroundcolor');

		    $data['module_ciaccessory_setting_button_hover_textcolor'] = $this->config->get('module_ciaccessory_setting_button_hover_textcolor');
		    $data['module_ciaccessory_setting_button_hover_backgroundcolor'] = $this->config->get('module_ciaccessory_setting_button_hover_backgroundcolor');

			$results = $this->model_extension_ciproduct_accessory->getProductAccessories($this->request->get['product_id']);
			$data['product_accessories'] = [];
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $data['module_ciaccessory_setting_image_width'], $data['module_ciaccessory_setting_image_height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $data['module_ciaccessory_setting_image_width'], $data['module_ciaccessory_setting_image_height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}

				$data['product_accessories'][] = [
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'model'        => $result['model'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get($this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
					'total_options'      => $this->model_extension_ciproduct_accessory->getTotalProductOptions($result['product_id']),
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				];
			}

			return $this->load->view('extension/ciaccessory/product_accessory', $data);
		}
	}

	public function options() {
	    $module_ciaccessory_setting_title = $this->config->get('module_ciaccessory_setting_title');
	    $data['text_product_accessories'] = (!empty($module_ciaccessory_setting_title[$this->config->get('config_language_id')]['title'])) ? $module_ciaccessory_setting_title[$this->config->get('config_language_id')]['title'] : $this->language->get('text_product_accessories');

		if (isset($this->request->post['product_id'])) {
			$data['product_id'] = (int)$this->request->post['product_id'];
		} else {
			$data['product_id'] = 0;
		}

		$this->load->model('catalog/product');

		$this->load->language('product/product');
		$data['text_points'] = $this->language->get('text_points');
		$data['text_tax'] = $this->language->get('text_tax');
		$data['text_option'] = $this->language->get('text_option');
		$data['button_upload'] = $this->language->get('button_upload');
		$data['text_select'] = $this->language->get('text_select');
		$data['text_upload'] = $this->language->get('text_upload');
		$data['text_loading'] = $this->language->get('text_loading');
		$data['button_cart'] = $this->language->get('button_cart');

		$this->load->model('tool/image');

		$product_info = $this->model_catalog_product->getProduct($data['product_id']);

		if ($product_info) {
			$data['heading_title'] = $product_info['name'];

			$data['text_payment_recurring'] = $this->language->get('text_payment_recurring');
			$data['recurrings'] = $this->model_catalog_product->getProfiles($data['product_id']);
			$data['points'] = $product_info['points'];
			$data['rating'] = (int)$product_info['rating'];
			$data['description'] = html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8');

			if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
				$data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['price'] = false;
			}

			if ((float)$product_info['special']) {
				$data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
			} else {
				$data['special'] = false;
			}

			if ($this->config->get('config_tax')) {
				$data['tax'] = $this->currency->format((float)$product_info['special'] ? $product_info['special'] : $product_info['price'], $this->session->data['currency']);
			} else {
				$data['tax'] = false;
			}

			if ($product_info['image']) {
				$data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('module_ciaccessory_setting_image_thumb_width'), $this->config->get('module_ciaccessory_setting_image_thumb_height'));
			} else {
				$data['thumb'] = '';
			}

			$data['options'] = array();

			foreach ($this->model_catalog_product->getProductOptions($data['product_id']) as $option) {
				$product_option_value_data = array();

				foreach ($option['product_option_value'] as $option_value) {
					if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
						if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
							$price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
						} else {
							$price = false;
						}

						$product_option_value_data[] = array(
							'product_option_value_id' => $option_value['product_option_value_id'],
							'option_value_id'         => $option_value['option_value_id'],
							'name'                    => $option_value['name'],
							'image'                   => $this->model_tool_image->resize($option_value['image'], 50, 50),
							'price'                   => $price,
							'price_prefix'            => $option_value['price_prefix']
						);
					}
				}

				$data['options'][] = array(
					'product_option_id'    => $option['product_option_id'],
					'product_option_value' => $product_option_value_data,
					'option_id'            => $option['option_id'],
					'name'                 => $option['name'],
					'type'                 => $option['type'],
					'value'                => $option['value'],
					'required'             => $option['required']
				);
			}

			if ($product_info['minimum']) {
				$data['minimum'] = $product_info['minimum'];
			} else {
				$data['minimum'] = 1;
			}

			if (isset($this->request->post['quantity'])) {
				$data['post_quantity'] = (int)$this->request->post['quantity'];
			} else {
				$data['post_quantity'] = 1;
			}


			$this->response->setOutput($this->load->view('extension/ciaccessory/product_option', $data));
		}
	}

	// Trigger for admin/controller/common/header/before
	public function createHeaderScript(&$route, &$data) {
		if(!$this->config->get('module_ciaccessory_setting_status')) {
			return;
		}

		// Extension Style
		$this->document->addStyle('catalog/view/theme/default/stylesheet/ciproduct_accessory.css');
	}

	public function createJournal3AccessoryTab(&$route, &$data, &$output) {
		if(!$this->config->get('module_ciaccessory_setting_status')) {
			return;
		}

		if($data['module_id'] == $this->config->get('module_ciaccessory_setting_module_id')) {
			if($this->config->get('module_ciaccessory_setting_widget') == 'tab_inside') {
				$output = $this->load->controller('extension/ciproduct_accessory');
			}
		}
	}

	public function createAccessoryTab(&$route, &$data, &$output) {
		if (!$this->config->get('module_ciaccessory_setting_status')) {
			return;
		}

	    $data['product_accessory'] = $this->load->controller('extension/ciproduct_accessory');

	    $data['accessory_setting_widget'] = $this->config->get('module_ciaccessory_setting_widget');

	    $accessory_setting_title = $this->config->get('module_ciaccessory_setting_title');
	    $data['tab_ciaccesssory'] = (!empty($accessory_setting_title[$this->config->get('config_language_id')]['tab_title'])) ? $accessory_setting_title[$this->config->get('config_language_id')]['tab_title'] : $this->language->get('tab_ciaccesssory');

	    if ($data['accessory_setting_widget'] == 'tab_inside') {
	    	if($data['product_accessory']) {
		    	$find = '<a href="#tab-description" data-toggle="tab">'.$data['tab_description'].'</a></li>';
		    	$replace = '<li><a href="#tab-ciaccesssory" data-toggle="tab">'. $data['tab_ciaccesssory'] .'</a></li>';
	    		$output = str_replace($find, $find . $replace, $output);

				$html = '<div class="tab-pane tab-content" id="tab-ciaccesssory">'. $data['product_accessory'] .'</div>';

				$output = str_replace('<div class="tab-pane active" id="tab-description">', $html .'<div class="tab-pane active" id="tab-description">', $output);
			}
	    } elseif ($data['accessory_setting_widget'] == 'tab_outside') {
	    	if($data['product_accessory']) {
	    		if($this->config->get('module_ciaccessory_setting_find_before')) {
	    			$find = html_entity_decode($this->config->get('module_ciaccessory_setting_find_before'), ENT_QUOTES, 'UTF-8');
	    		} else {
	    			$find = '<ul class="nav nav-tabs">';
	    		}

	    		$output = $this->str_replace_first($find, $data['product_accessory'] . $find, $output);
	    	}
	    }
	}

	public function str_replace_first($search, $replace, $subject) {
   	 	$search = '/'.preg_quote($search, '/').'/';
		return preg_replace($search, $replace, $subject, 1);
	}
}