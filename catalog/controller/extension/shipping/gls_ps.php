<?php 
class ControllerExtensionShippingGlsPs extends Controller {
    private $extension_path = "extension/shipping/";
    private $extension_name = "gls_ps";
    private $extension_type = "shipping";
    private $extension = "extension_";
    private $extension_name_link;
    private $error; 
    
	public function __construct($registry) {
		parent::__construct($registry);

        $this->extension_name_link = ((VERSION >= 3 ? $this->extension_type . '_':''). $this->extension_name);
	}     

	public function index() {
    
    /* LOAD MODEL & LANGUAGE -----------------------------------------------------------------------------------------------------------------------------------*/

    $this->load->language((VERSION < 2.3 ? $this->extension_type . '/' : $this->extension_path) . $this->extension_name);
    
    /* END LOAD MODEL & LANGUAGE -------------------------------------------------------------------------------------------------------------------------------*/
    
        $data['text_'.$this->extension_name.'_change'] = $this->language->get('text_'.$this->extension_name.'_change');
        
        $data['url'] = '';
        
        //krajiny zobrazenia
        $this->load->model('localisation/country');
        
        if(isset($this->session->data['shipping_address']['country_id'])) {
          $country_info = $this->model_localisation_country->getCountry($this->session->data['shipping_address']['country_id']);
          $data['url'] = '&ctrcode='.$country_info['iso_code_2'];
        }
        
        //jazyk z eshopu        
        $language = mb_substr($this->session->data['language'], 0, 2);
        $accept_language = array('sk','cz','en');
        
        if(in_array($language,$accept_language)) {
           $data['url'] .= '&lng='.$language;
        } else {
           $data['url'] .= '&lng=en';
        }        
        
        //nogroup z nastavenia
        foreach($this->config->get($this->extension_name_link.'_group') as $nogroup) {
          $nogroup_data[] = 'nogroup[]='.$nogroup;
        }      
        if(count($nogroup_data)>0) {        
          $data['url'] .= '&'.implode('&',$nogroup_data);
        }
        
        //odberne miesto zo sessionu
        if(!empty($this->session->data['gls_ps'])) {
            $gls_ps_data = explode(";",$this->session->data['gls_ps']);
            $data['url'] .= '&sid='.$gls_ps_data[1];
          } 

        if (VERSION < '2.2') {
            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/'.$this->extension_type.'/'.$this->extension_name.'.tpl')) {
                $template = $this->config->get('config_template') . '/template/'.$this->extension_type.'/'.$this->extension_name.'.tpl';
		    } else {
                $template = 'default/template/'.$this->extension_type.'/'.$this->extension_name.'.tpl';
		    }
            
            if (VERSION < '2.0') {
               $this->data = $data;  
               $this->template = $template;
               $this->response->setOutput($this->render()); 
            } else {
               $this->load->view($template, $data);
            }
                       
        } else {
          $tpl = version_compare(VERSION, '3.0.0.0', '<') ? '.tpl':'';
  		  if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/'.(version_compare(VERSION, '2.3.0.0', '<') ? $this->extension_type. '/' . $this->extension_name : $this->extension_path . $this->extension_name). $tpl)) {
  			$this->response->setOutput($this->load->view($this->config->get('config_template') . '/template/'.(version_compare(VERSION, '2.3.0.0', '<') ? $this->extension_type. '/' . $this->extension_name : $this->extension_path . $this->extension_name). $tpl, $data));
  		  } else {
  			$this->response->setOutput($this->load->view('default/template/'.(version_compare(VERSION, '2.3.0.0', '<') ? $this->extension_type. '/' . $this->extension_name : $this->extension_path . $this->extension_name). $tpl, $data));
  		  }
        }                  		 
    }
	
    public function saveBranchOrder() {
	  	$json = array();
	    if(!empty($this->request->post['gls_ps'])){
            $this->session->data['gls_ps'] = $this->request->post['gls_ps'];            
        } 
        
        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));    

    }    
    
}