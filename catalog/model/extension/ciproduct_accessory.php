<?php
class ModelExtensionCiproductAccessory extends Model {
	public function getProductAccessories($product_id) {
		$this->load->model('catalog/product');
		
		$p_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_accessory pac LEFT JOIN " . DB_PREFIX . "product p ON (pac.accessory_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (pac.product_id = p2s.product_id) WHERE pac.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND pac.status = '1' ORDER BY pac.sort_order ASC");

		foreach ($query->rows as $result) {
			$p_data[$result['accessory_id']] = $this->model_catalog_product->getProduct($result['accessory_id']);
		}

		return $p_data;
	}

	public function getTotalProductOptions($product_id) {

		$query = $this->db->query("SELECT COUNT(*) as total_options FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

		return $query->row['total_options'];
	}
}
