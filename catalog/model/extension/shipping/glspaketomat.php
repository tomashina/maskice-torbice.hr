<?php
class ModelExtensionShippingGlspaketomat extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/glspaketomat');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_glspaketomat_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_glspaketomat_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		
			if (get_class($this)!='ModelExtensionShippingFree') {
              if (($this->config->get('shipping_free_status') == 1) && (float)$this->cart->getTotal() >= $this->config->get('shipping_free_total')) {
                 $status = false;
              }
			}

		if ($status) {
			$quote_data = array();

			    if($this->session->data['currency']=='HRK'){
                $text =  $this->currency->format($this->tax->calculate($this->config->get('shipping_glspaketomat_cost'), $this->config->get('shipping_glspaketomat_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']).' <small>('.$this->currency->format($this->tax->calculate($this->config->get('shipping_glspaketomat_cost'), $this->config->get('shipping_glspaketomat_tax_class_id'), $this->config->get('config_tax')), 'EUR'). ')</small> ';
            }
            else{
                $text = $this->currency->format($this->tax->calculate($this->config->get('shipping_glspaketomat_cost'), $this->config->get('shipping_glspaketomat_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']);
            }

			$quote_data['glspaketomat'] = array(
				'code'         => 'glspaketomat.glspaketomat',
				'title'        => $this->language->get('text_description'),
				'cost'         => $this->config->get('shipping_glspaketomat_cost'),
				'tax_class_id' => $this->config->get('shipping_glspaketomat_tax_class_id'),
				'text'         => $text
			);

			$method_data = array(
				'code'       => 'glspaketomat',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_glspaketomat_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}