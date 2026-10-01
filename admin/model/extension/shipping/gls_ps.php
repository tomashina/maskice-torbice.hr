<?php
class ModelExtensionShippingGlsPs extends Model {
	private $extension_path = "extension/shipping/";
	private $extension_name = "gls_ps";
	private $extension_type = "shipping";
    private $extension = "extension_"; 

	public function glsCountry() {
    
        /* LOAD MODEL & LANGUAGE -----------------------------------------------------------------------------------------------------------------------------------*/

        $this->load->language((VERSION < 2.3 ? $this->extension_type . '/' : $this->extension_path) . $this->extension_name);
    
        /* END LOAD MODEL & LANGUAGE -------------------------------------------------------------------------------------------------------------------------------*/    
    
        $data['country'] = array(
            array('country'=>'cz','name'=>$this->language->get('text_country_cz')),
            array('country'=>'sk','name'=>$this->language->get('text_country_sk')),
            array('country'=>'si','name'=>$this->language->get('text_country_si')),
            array('country'=>'hu','name'=>$this->language->get('text_country_hu')),
            array('country'=>'hr','name'=>$this->language->get('text_country_hr')),
            array('country'=>'ro','name'=>$this->language->get('text_country_ro'))
        );   
        
        return $data['country'];
    }
    
	public function glsGroup() {
    
        /* LOAD MODEL & LANGUAGE -----------------------------------------------------------------------------------------------------------------------------------*/

        $this->load->language((VERSION < 2.3 ? $this->extension_type . '/' : $this->extension_path) . $this->extension_name);
    
        /* END LOAD MODEL & LANGUAGE -------------------------------------------------------------------------------------------------------------------------------*/    
        
        $data['group'] = array();
        
        for ($x = 1; $x <= 18; $x++) {
             $data['group'][] = array('group_id'=>$x,'name'=>$this->language->get('text_group_'.$x));
        }  
        
        return $data['group'];
    }  
    
	public function createTables() {
		// new table for additional data of orders
        $query = $this->db->query("DESC ".DB_PREFIX."order gls_ps");
        if (!$query->num_rows) { 
            $this->db->query("ALTER TABLE " . DB_PREFIX . "order ADD gls_ps TEXT");
        } 
   }        
}