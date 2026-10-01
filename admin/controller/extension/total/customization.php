<?php

class ControllerExtensionTotalCustomization extends Controller {

    private $error = array();

    public function __construct($registry) {
        parent::__construct($registry);
        if (VERSION >= 2.0 && VERSION <= 2.2) {
            $this->session_token_key = 'token';
            $this->session_token = $this->session->data['token'];

            /* BreadCrumb Path */
            $this->extension_path = 'extension/module';

            /* Main Module Path */
            $this->module_path = 'module';
        } else if (VERSION < 3.0) {
            $this->session_token_key = 'token';
            $this->session_token = $this->session->data['token'];

            $this->extension_path = 'extension/extension';
            $this->module_path = 'extension/module';
        } else {
            $this->session_token_key = 'user_token';
            $this->session_token = $this->session->data['user_token'];

            $this->extension_path = 'marketplace/extension';
            $this->module_path = 'extension/module';
        }
    }

    public function index() {
        $this->load->language('extension/total/customization');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            
            if(VERSION < 3.0){
                $this->model_setting_setting->editSetting('customization', $this->request->post);
            }else{
                $this->request->post['total_customization_status'] = $this->request->post['customization_status'];
                unset($this->request->post['customization_status']);
                $this->model_setting_setting->editSetting('total_customization', $this->request->post);
            }
            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link($this->extension_path, $this->session_token_key.'=' . $this->session_token . '&type=total', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key.'=' . $this->session_token, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key.'=' . $this->session_token . '&type=total', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/total/customization', $this->session_token_key.'=' . $this->session_token, true)
        );

        $data['action'] = $this->url->link('extension/total/customization', $this->session_token_key.'=' . $this->session_token, true);

        $data['cancel'] = $this->url->link($this->extension_path, $this->session_token_key.'=' . $this->session_token . '&type=total', true);

        if (isset($this->request->post['customization_status'])) {
            $data['customization_status'] = $this->request->post['customization_status'];
        } else {
            $data['customization_status'] = $this->config->get('customization_status');
        }

        if (isset($this->request->post['customization_sort_order'])) {
            $data['customization_sort_order'] = $this->request->post['customization_sort_order'];
        } else {
            $data['customization_sort_order'] = $this->config->get('customization_sort_order');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/total/customization', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/total/Customization')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

}
