<?php

define('_TOKEN', VERSION < 3 ? 'token' : 'user_token');

class ControllerExtensionShippingGlsPs extends Controller {
  private $extension_path = "extension/shipping/";
  private $extension_name = "gls_ps";
  private $extension_type = "shipping";
  private $extension = "extension_";
  private $error; 
    
  public function index() {
  
    /* LOAD MODEL & LANGUAGE -----------------------------------------------------------------------------------------------------------------------------------*/

    $this->load->model('setting/setting');
    $this->load->language((VERSION < 2.3 ? $this->extension_type . '/' : $this->extension_path) . $this->extension_name);
    
    /* END LOAD MODEL & LANGUAGE -------------------------------------------------------------------------------------------------------------------------------*/

    $extension_name = ((VERSION >= 3 ? $this->extension_type . '_':''). $this->extension_name);	
    
    if(version_compare(VERSION, '2.0.0.0', '<')) {
      $extension_url = 'extension/'.$this->extension_type;   
    } else if(version_compare(VERSION, '2.0.0.0', '>=') && version_compare(VERSION, '2.3', '<')) {
      $extension_url = 'extension/'.$this->extension_type;
    } else if(version_compare(VERSION, '2.3.0.0', '>=')) {
      $extension_url = (version_compare(VERSION, '3.0.0.0', '>=') ? 'marketplace' : 'extension').'/extension';
    } 
        
    if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {         	
      $this->session->data['success'] = $this->language->get('text_success');
	  $this->model_setting_setting->editSetting($extension_name, $this->request->post);
			    
	  $this->session->data['success'] = $this->language->get('text_success');
        
      if (VERSION < '2.0') {
        $this->redirect($this->url->link($extension_url, _TOKEN . '=' . $this->session->data[_TOKEN], 'SSL'));
      } else {
        $this->response->redirect($this->url->link($extension_url, _TOKEN . '=' . $this->session->data[_TOKEN] . (VERSION >= '2.3' ? '&type='.$this->extension_type : ''), version_compare(VERSION, '2.3.0.0', '>=') ? true : 'SSL'));
      }           
    }  
    
    $this->document->addStyle('view/stylesheet/'.$this->extension_name.'.css');   

    /* LANGUAGE ------------------------------------------------------------------------------------------------------------------------------------------------*/
        
    $this->document->setTitle($this->language->get('heading_title'));

    $language = array(
        'heading_title',
        'entry_status',
        'entry_tax_class',
        'entry_cost',
		'entry_sort_order', 
        'entry_name',
        'entry_country',
        'entry_group',
        'help_name',
        'help_country',
        'help_group',        
        'entry_customer_group',
        'text_none',
        'text_all_zones',
        'text_enabled',   
        'text_disabled',                    
        'button_save',
        'button_cancel',
        'column_price',
        'column_weight',
        'column_from',
        'column_to',
        'button_add_price',
        'button_remove'
    );
    
    foreach($language as $language_field) {
      if(!is_array($language_field)) {
        $data[$language_field] = $this->language->get($language_field);
      } else {
        if(isset($language_field[1])) {
          $data[$language_field[1].'_'.$language_field[0]] = $this->language->get($language_field[1].'_'.$language_field[0]);
        }        
        if(!empty($language_field[2])) {
          $data['entry_'.$language_field[0]] = $this->language->get('entry_'.$language_field[0]);
        }
        if(!empty($language_field[3])) {
          $data['help_'.$language_field[0]] = $this->language->get('help_'.$language_field[0]);
        }
      }
    }      

  /* END LANGUAGE --------------------------------------------------------------------------------------------------------------------------------------------*/        
        
  /* BREADCRUMBS ---------------------------------------------------------------------------------------------------------------------------------------------*/   

    $data['breadcrumbs'] = array();
        
    $data['breadcrumbs'][] = array(
		'text' => $this->language->get('text_home'),
		'href' => $this->url->link('common/dashboard', _TOKEN . '=' . $this->session->data[_TOKEN], version_compare(VERSION, '2.3.0.0', '>=') ? true : 'SSL'),
        'separator' => false
	);
    
    $data['breadcrumbs'][] = array(
		'text' => (VERSION >= '2.3' ? $this->language->get('text_extension') : $this->language->get('text_'.$this->extension_type)),
		'href' => $this->url->link($extension_url, _TOKEN . '=' . $this->session->data[_TOKEN] . (VERSION >= '2.3' ? '&type='.$this->extension_type : ''), version_compare(VERSION, '2.3.0.0', '>=') ? true : 'SSL'),
        'separator' => ' :: '
	);
    
	$data['breadcrumbs'][] = array(
		'text' => $this->language->get('heading_title'),
		'href' => $this->url->link($this->extension_path . $this->extension_name, _TOKEN . '=' . $this->session->data[_TOKEN], version_compare(VERSION, '2.3.0.0', '>=') ? true : 'SSL'),
        'separator' => ' :: '
	);
                                       
    $data['action'] = $this->url->link((version_compare(VERSION, '2.3.0.0', '>=') ? $this->extension_path : $this->extension_type.'/').$this->extension_name, _TOKEN . '=' . $this->session->data[_TOKEN], version_compare(VERSION, '2.3.0.0', '>=') ? true : 'SSL');
    $data['cancel'] = $this->url->link($extension_url, _TOKEN . '=' . $this->session->data[_TOKEN] . (VERSION >= '2.3' ? '&type='.$this->extension_type : ''), version_compare(VERSION, '2.3.0.0', '>=') ? true : 'SSL');    
  
    /* END BREADCRUMBS ------------------------------------------------------------------------------------------------------------------------------------------*/    
 
    /* LIST VARIABLE  -------------------------------------------------------------------------------------------------------------------------------------------*/        
    
    $this->load->model('localisation/tax_class');
    $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
    
    if(intval(str_replace('.','',VERSION)) >=  2101) {
      $this->load->model('customer/customer_group');
      $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
    } else {
      $this->load->model('sale/customer_group');
      $data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
    }     
    
    $this->load->model('localisation/language');
    $languages  = $this->model_localisation_language->getLanguages();
    
    foreach ($languages as $language) {
      if(version_compare(VERSION, '2.0.0.0', '>=') && version_compare(VERSION, '2.2.0.0', '<')) {
        $image = "view/image/flags/".$language['image'];
      } else if(version_compare(VERSION, '2.2.0.0', '>=')) {
        $image = "language/".$language['code']."/".$language['code'].".png";
      }            
      
      $data['language_icon'][$language['language_id']] = $image;
    }    
    
    $this->load->model((VERSION < 2.3 ? $this->extension_type . '/' : $this->extension_path) . $this->extension_name);
	$model_name = "model_" . (VERSION < 2.3 ? '' : $this->extension) . $this->extension_type . "_" . $this->extension_name;
    
    $data['groups'] = $this->{$model_name}->glsGroup();
    $data['countries'] = $this->{$model_name}->glsCountry();
    
    /* END LIST VARIABLE  -----------------------------------------------------------------------------------------------------------------------------------------*/ 
        
    /* DATA VARIABLE  -------------------------------------------------------------------------------------------------------------------------------------------*/        
    
    $data[_TOKEN] = $this->session->data[_TOKEN];

        
    if (isset($this->error['warning'])) {
      $data['error_warning'] = $this->error['warning'];
    } else {
      $data['error_warning'] = '';
    }
    
    $config_info = array(
      'status',
      array('cost',true),
      'group',
       array('customer_group',true),
      'country',
      'tax_class_id',
      'sort_order'        
    );
    
   $config_info_language = array();    
    
    foreach($config_info as $config_field) {
      if(is_array($config_field) && !empty($config_field[2])) { 
        if (isset($this->error[$config_field[0]])) {
			    $data['error_'.$config_field[0]] = $this->error[$config_field[0]];
		    } else {
			    $data['error_'.$config_field[0]] = '';
		    }
      }
    
      if(is_array($config_field) && !empty($config_field[1])) {
        if (isset($this->request->post[$extension_name . '_'.$config_field[0]])) {
	        $data[$extension_name . '_'.$config_field[0]] = $this->request->post[$extension_name . '_'.$config_field[0]];
	      } else if($this->config->get($extension_name . '_'.$config_field[0])){
		      $data[$extension_name . '_'.$config_field[0]] = $this->config->get($extension_name . '_'.$config_field[0]);
	      } else {
          $data[$extension_name . '_'.$config_field[0]] = ($config_field[1] == true) ? array() : $config_field[0];
        }    
      } else {
      
        if(!in_array($config_field,$config_info_language)) {
          if (isset($this->request->post[$extension_name . '_'.$config_field])) {
	   		    $data[$extension_name . '_'.$config_field] = $this->request->post[$extension_name . '_'.$config_field];
	   	    } else {
		  	    $data[$extension_name . '_'.$config_field] = $this->config->get($extension_name . '_'.$config_field);
	   	    }
        } else {
          foreach ($languages as $language) {
			      if (isset($this->request->post[$extension_name.'_'.$config_field . $language['language_id']])) {
				      $data[$extension_name.'_'.$config_field . $language['language_id']] = $this->request->post[$extension_name.'_'.$config_field . $language['language_id']];
			      } else {
				      $data[$extension_name.'_'.$config_field . $language['language_id']] = $this->config->get($extension_name.'_'.$config_field . $language['language_id']);
			      }
		      }
            }
        }    
    }     
    
    $data['languages'] = array_reverse($languages);  

    $data['extension_name'] = $extension_name;
        
    /* RENDER VIEW ------------------------------------------------------------------------------------------------------------------------------------------------*/   

    $tpl = version_compare(VERSION, '3.0.0.0', '<') ? '.tpl':'';

    if (VERSION < '2.0') {
      $this->data = $data;
      $this->template = $this->extension_type.'/'.$this->extension_name .'_15'. $tpl;
      $this->children = array(
       'common/header',
       'common/footer'
      );
      $this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
    } else {        
      $data['header'] = $this->load->controller('common/header');
		  $data['column_left'] = $this->load->controller('common/column_left');
		  $data['footer'] = $this->load->controller('common/footer');
         
      $this->response->setOutput($this->load->view((version_compare(VERSION, '2.3.0.0', '<') ? $this->extension_type. '/' . $this->extension_name : $this->extension_path . $this->extension_name). $tpl, $data));
    }
    
    /* END RENDER VIEW --------------------------------------------------------------------------------------------------------------------------------------------*/    
  }
  
  public function install() {
	//create db scheme
    $this->load->model((VERSION < 2.3 ? $this->extension_type . '/' : $this->extension_path) . $this->extension_name);
	$model_name = "model_" . (VERSION < 2.3 ? '' : $this->extension) . $this->extension_type . "_" . $this->extension_name;  
    
    $this->{$model_name}->createTables();
    
    $this->registerEmail();
 
  }  
    
  private function validate() {
    $extension_name = ((VERSION >= 3 ? $this->extension_type . '_':''). $this->extension_name);
  
    if (!$this->user->hasPermission('modify', version_compare(VERSION, '2.3.0.0', '<') ? $this->extension_type. '/' . $this->extension_name : $this->extension_path . $this->extension_name)) {
      $this->error['warning'] = $this->language->get('error_permission');
    }
    
    if (!$this->error) {
      return true;
    } else {
      return false;
    }
  } 

  
  private function registerEmail() {
    $this->load->language((VERSION < 2.3 ? $this->extension_type . '/' : $this->extension_path) . $this->extension_name);
  
	$store_name = $this->config->get('config_name');			
	$store_url = HTTP_CATALOG ;	  
    $module = $this->language->get('heading_title');

	$message  = "Modul ".$module." bol nainštalovaný na novom eshope";
  	$message .= "\n\n";
  	$message .= 'URL Eshopu: '.$store_url . "\n\n";
  	$message .= 'Email Eshopu: '.$this->config->get('config_email') . "\n\n";
    $message .= 'Názov Eshopu:'.html_entity_decode($store_name, ENT_QUOTES, 'UTF-8');

  	$mail = new Mail();
  	$mail->protocol = $this->config->get('config_mail_protocol');
  	$mail->parameter = $this->config->get('config_mail_parameter');
  	$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
  	$mail->smtp_username = $this->config->get('config_mail_smtp_username');
  	$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
  	$mail->smtp_port = $this->config->get('config_mail_smtp_port');
  	$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

  	$mail->setTo('install@opencart-solutions.com');
  	$mail->setFrom($this->config->get('config_email'));
  	$mail->setSender(html_entity_decode($store_name, ENT_QUOTES, 'UTF-8'));
  	$mail->setSubject('Nová inštalácia: '.$module.' ('.$store_url.')');
  	$mail->setText($message);
  	$mail->send();  
  
  }     
}