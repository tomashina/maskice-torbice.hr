<?php
class ModelExtensionShippingGlsPs extends Model {
	private $extension_path = "extension/shipping/";
	private $extension_name = "gls_ps";
	private $extension_type = "shipping";
    private $extension = "extension_"; 

	function getQuote($address) {
        /* LOAD MODEL & LANGUAGE -----------------------------------------------------------------------------------------------------------------------------------*/

        $this->load->language((VERSION < 2.3 ? $this->extension_type . '/' : $this->extension_path) . $this->extension_name);
    
        /* END LOAD MODEL & LANGUAGE -------------------------------------------------------------------------------------------------------------------------------*/

        $extension_name = (VERSION >= 3 ? $this->extension_type . '_':''). $this->extension_name;	


        $status = true;
		$cost = null;
        $calculate_price = $this->cart->getSubTotal();
        $calculate_weight = $this->cart->getWeight();

        if($this->config->get($extension_name.'_cost')) {
		  $rates = $this->config->get($extension_name.'_cost');

		  if (!empty($rates) && is_array($rates)) {
			usort($rates, array($this, 'sort'));

			foreach ($rates as $rate) {
				if ((float)$rate['p']['from'] <= $calculate_price && ($rate['p']['to'] === '' || (float)$rate['p']['to'] >= $calculate_price)) {
				  if ((float)$rate['w']['from'] <= $calculate_weight && ($rate['w']['to'] === '' || (float)$rate['w']['to'] >= $calculate_weight)) {
					  $cost = $rate['price'];
					  break;
				  }
                }
		    }
 		  }
        }
    
        if (is_null($cost)) {
          $status = false;
        }
        
        /* Customer group */
        if (isset($_POST['customer_group_id']) && $_POST['customer_group_id']) {
          $customer_group_id = $_POST['customer_group_id'];
        } elseif (isset($_GET['customer_group_id']) && $_GET['customer_group_id']) {
          $customer_group_id = $_GET['customer_group_id'];
        } elseif ($this->customer->isLogged()) {
          if(VERSION > 2.0) {
             $customer_group_id = $this->customer->getGroupId();
          } else {
             $customer_group_id = $this->customer->getCustomerGroupId();
          }
        } elseif (isset($this->session->data['customer']) && isset($this->session->data['customer']['customer_group_id']) && $this->session->data['customer']['customer_group_id']) {
          $customer_group_id = $this->session->data['customer']['customer_group_id'];
        } else {
          $customer_group_id = 1;
        }         
        
        if(!in_array(strtolower($address['iso_code_2']),$this->config->get($extension_name.'_country'))) {
           $status = false;
        }  

        
        if(!in_array($customer_group_id,$this->config->get($extension_name.'_customer_group'))) {
          $status = false;       
        }    

		$method_data = array();

		if ($status) {
        
            $out = null;
            if(!empty($this->session->data['gls_ps'])) {
                $gls_ps_data = explode(";",$this->session->data['gls_ps']);
                $point = $gls_ps_data[0];
                $button = $this->language->get('text_gls_ps_change');              
            }  else {
                $point = '';
                $button = $this->language->get('text_gls_ps_select'); 
            }   
            
            $out .= '<div id="select-gls-ps" '.(isset($gls_ps_data[1]) ? 'data-branch-id="'.$gls_ps_data[1].'"':'').'><strong>'.$point.' </strong><span>'.$button.'</span></div>';
            
			$quote_data = array();

			$quote_data[$this->extension_name] = array(
				'code'         => $this->extension_name.'.'.$this->extension_name,
				'title'        => $this->language->get('text_description'),
                'description'  => $out,
				'cost'         => $cost,
				'tax_class_id' => $this->config->get($extension_name . '_tax_class_id'),
				'text'         => $this->currency->format($this->tax->calculate($cost, $this->config->get($extension_name . '_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency'])
			);                    

			$method_data = array(
				'code'       => $this->extension_name,
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get($extension_name . '_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
    
	public function sort($a, $b) {
		if ((float)$a['p']['from'] == (float)$b['p']['from']) {
			if ((float)$a['w']['from'] < (float)$b['w']['from']) {
				return -1;
			} elseif ((float)$a['w']['from'] > (float)$b['w']['from']) {
				return 1;
			} elseif ($a['w']['to'] === '') {
				return 1;
			} elseif ($b['w']['to'] === '') {
				return -1;
			} elseif ((float)$a['w']['to'] < (float)$b['w']['to']) {
				return -1;
			} elseif ((float)$a['w']['to'] > (float)$b['w']['to']) {
				return 1;
			} else {
				return 0;
			}
		}

		if ((float)$a['p']['from'] < (float)$b['p']['from']) {
			return -1;
		} elseif ((float)$a['p']['from'] > (float)$b['p']['from']) {
			return 1;
		} elseif ($a['p']['to'] === '') {
			return 1;
		} elseif ($b['p']['to'] === '') {
			return -1;
		} elseif ((float)$a['p']['to'] < (float)$b['p']['to']) {
			return -1;
		} elseif ((float)$a['p']['to'] > (float)$b['p']['to']) {
			return 1;
		} else {
			return 0;
		}
	}    
    
    private function array_intersect_faster(array $array1, array $array2): array { 
        $is_found = false;
        foreach ($array1 as $key) {
            if (in_array($key, $array2)) {
                $is_found = true;
                break;
            }
        }
        return $is_found;
    }      
}
?>