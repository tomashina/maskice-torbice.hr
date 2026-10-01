<?php
class ModelExtensionTotalCustomization extends Model {
	public function getTotal($total) {
		$this->load->language('extension/total/customization');

		$sub_total = $this->cart->getSubTotal();
                $customization_cost = 0;
                foreach ($this->cart->getProducts() as $product) {
                       $this->load->model('setting/setting');
                       $this->load->model('tool/image');
                        $this->load->model('kbproduct_customizer/kbproduct_customizer');
                        $kbproduct_customizer = $this->model_setting_setting->getSetting('kbproduct_customizer', $this->config->get('config_store_id'));
                        if(isset($kbproduct_customizer['kbproduct_customizer']['enable']) && $kbproduct_customizer['kbproduct_customizer']['enable'] ==1){
                            $kbcustomized_cart = $this->model_kbproduct_customizer_kbproduct_customizer->getCart($product['cart_id'],$product['quantity']);
                            if(isset($kbcustomized_cart['kbproduct_cost'])){
                                $customization_cost += $kbcustomized_cart['kbproduct_cost'];
                            }
                        }
                }
                        
		$total['totals'][] = array(
			'code'       => 'customization',
			'title'      => $this->language->get('text_customization'),
			'value'      => $customization_cost,
			'sort_order' => $this->config->get('customization_sort_order')
		);

		$total['total'] += $customization_cost;
	}
}
