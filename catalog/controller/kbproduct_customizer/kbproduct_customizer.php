<?php

//require_once(DIR_SYSTEM . 'libraries/kbproduct_customizer/dompdf/dompdf_config.inc.php');
class ControllerKbproductCustomizerKbproductCustomizer extends Controller {

    private $error = array();
    private $quantity = 1;

    public function index($product_id) {
//        $product_id = 0;
        $this->load->language('kbproduct_customizer/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('catalog/product');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_related_pro'] = $this->language->get('text_related_pro');
        $data['text_all'] = $this->language->get('text_all');
        $data['text_more_photos'] = $this->language->get('text_more_photos');
        $data['button_submit'] = $this->language->get('button_submit');
        $data['text_shop_now'] = $this->language->get('text_shop_now');
        $data['text_color'] = $this->language->get('text_color');
        $data['text_image'] = $this->language->get('text_image');
        $data['text_text'] = $this->language->get('text_text');
        $data['text_upload_file'] = $this->language->get('text_upload_file');
        $data['text_entre_link_or_text'] = $this->language->get('text_entre_link_or_text');
        $data['text_entre_text_below'] = $this->language->get('text_entre_text_below');
        $data['text_blur'] = $this->language->get('text_blur');
        $data['text_sharpen'] = $this->language->get('text_sharpen');
        $data['text_emboss'] = $this->language->get('text_emboss');
        $data['text_add_text'] = $this->language->get('text_add_text');
        $data['text_invert'] = $this->language->get('text_invert');
        $data['text_sepia'] = $this->language->get('text_sepia');
        $data['text_grayscale'] = $this->language->get('text_grayscale');
        $data['text_transparency'] = $this->language->get('text_transparency');
        $data['text_add_qrcode'] = $this->language->get('text_add_qrcode');
        $data['text_upload_file'] = $this->language->get('text_upload_file');
        $data['text_price'] = $this->language->get('text_price');
        $data['text_font_bg_color'] = $this->language->get('text_font_bg_color');
        $data['text_font_color'] = $this->language->get('text_font_color');
        $data['text_spacing'] = $this->language->get('text_spacing');
        $data['text_radius'] = $this->language->get('text_radius');
        $data['text_curved_text'] = $this->language->get('text_curved_text');
        $data['text_line_height'] = $this->language->get('text_line_height');
        $data['text_font_size'] = $this->language->get('text_font_size');
        $data['text_font_family'] = $this->language->get('text_font_family');
        $data['text_edit_text_below'] = $this->language->get('text_edit_text_below');
        $data['text_back'] = $this->language->get('text_back');
        $data['text_upload_image'] = $this->language->get('text_upload_image');
        $data['text_choose_images'] = $this->language->get('text_choose_images');
        $data['text_customization'] = $this->language->get('text_customization');
        $data['text_total'] = $this->language->get('text_total');
        $data['text_empty_canvas'] = $this->language->get('text_empty_canvas');
        $data['text_undo'] = $this->language->get('text_undo');
        $data['text_redo'] = $this->language->get('text_redo');
        $data['text_save_customization'] = $this->language->get('text_save_customization');
        $data['text_delete_selected_item'] = $this->language->get('text_delete_selected_item');
        $data['text_send_to_back'] = $this->language->get('text_send_to_back');
        $data['text_bring_to_front'] = $this->language->get('text_bring_to_front');
        $data['text_download_as_png'] = $this->language->get('text_download_as_png');
        $data['text_warning'] = $this->language->get('text_warning');
        $data['text_final_cost'] = $this->language->get('text_final_cost');
        $data['text_customized_cost'] = $this->language->get('text_customized_cost');
        $data['text_ok'] = $this->language->get('text_ok');
        $data['text_cancel'] = $this->language->get('text_cancel');
        $data['text_canvas_clear_mesg'] = $this->language->get('text_canvas_clear_mesg');
        $data['text_incl'] = $this->language->get('text_incl');
        $data['text_empty_cust_conf'] = $this->language->get('text_empty_cust_conf');
        $data['text_additional_design_cost'] = $this->language->get('text_additional_design_cost');
        $data['text_select_image_grp'] = $this->language->get('text_select_image_grp');
        $data['text_remove'] = $this->language->get('text_remove');
        $data['text_remove_obj_msg'] = $this->language->get('text_remove_obj_msg');
        $data['text_please_wait'] = $this->language->get('text_please_wait');

        $data['error_minchar_field'] = $this->language->get('error_minchar_field');
        $data['error_maxchar_field'] = $this->language->get('error_maxchar_field');
        $data['error_empty_field'] = $this->language->get('error_empty_field');
        $data['error_image_size'] = $this->language->get('error_image_size');

        $data['language_id'] = $this->config->get('config_language_id');
        $data['token'] = 'as';
        $data['image_dir'] = HTTPS_SERVER;
        $data['product_id'] = $product_id;
        $data['product_attribute_id'] = '';
        $store_id = $this->config->get('config_store_id');

        $kbproduct_customizer = $this->model_setting_setting->getSetting('kbproduct_customizer', $this->config->get('config_store_id'));
        if (isset($kbproduct_customizer['kbproduct_customizer']['enable']) && $kbproduct_customizer['kbproduct_customizer']['enable'] == 1) {

            $product_settings = $this->model_kbproduct_customizer_kbproduct_customizer->getProductSetting($product_id);
            $product_data = $this->model_catalog_product->getProduct($product_id);
            $this->load->model('localisation/currency');
            $currency = $this->model_localisation_currency->getCurrencyByCode($this->session->data['currency']);
            $data['currency_symbol_left'] = $currency['symbol_left'];
            $data['currency_symbol_right'] = $currency['symbol_right'];

            $this->quantity = $product_data['minimum'];
            if (isset($product_data['special']) && $product_data['special'] != NULL) {
                $data['product_price'] = $this->currency->format($this->tax->calculate($product_data['special'], $product_data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                $data['product_price_format'] = $this->currency->format($this->tax->calculate($product_data['special'], $product_data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $data['product_price'] = $this->currency->format($this->tax->calculate($product_data['price'], $product_data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'], '', false);
                $data['product_price_format'] = $this->currency->format($this->tax->calculate($product_data['price'], $product_data['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                ;
            }

            $all_sides = json_decode($product_settings['slice_data'], true);
            $count = 0;
            $data['all_sides'] = array();
            $all_sides_name = array();
            $count = 1;
            foreach ($all_sides as $key => $value) {
                if ($value['enable_side'] == '1') {
                    if ($count == 0) {
                        $data['main_image'] = $value['side_image'];
                    }
                    $data['all_sides'][] = $value;
                    $all_sides_name[$count++] = $value['side_name'][$data['language_id']];
                }
            }
            $data['all_sides_name'] = json_encode($all_sides_name);
            $data['filters'] = json_decode($product_settings['image_filters'], true);
            $data['settings'] = $product_settings;
            $data['settings']['upload_image_price'] = $this->currency->format($product_settings['upload_image_price'], $this->session->data['currency'], '', false);
            ;
            $data['settings']['design_fixed_price'] = $this->currency->format($product_settings['design_fixed_price'], $this->session->data['currency'], '', false);
            ;
            $data['settings']['qrcode_price'] = $this->currency->format($product_settings['qrcode_price'], $this->session->data['currency'], '', false);
            ;
            $data['settings']['text_fixed_price'] = $this->currency->format($product_settings['text_fixed_price'], $this->session->data['currency'], '', false);
            ;

            if ($product_settings['allow_img_upload']) {
                $upload_allow = 1;
            } else {
                $upload_allow = 0;
            }
            if (isset($product_settings['enable'])) {
//                $data['colors'] = array();
//                $data['fonts'] = array();
//                $data['groups'] = array();
//                $data['images'] = array();
                //$data['colors'] = $this->model_kbproduct_customizer_kbproduct_customizer->getColors($product_id);
                $colors_data = $this->model_kbproduct_customizer_kbproduct_customizer->getAllColors($store_id);
                $disabled_colors = $this->model_kbproduct_customizer_kbproduct_customizer->getDisabledColors($product_id, $store_id);
                $data['colors_data'] = array();
                $count_max = 0;
                foreach ($colors_data as $key => $value) {
                    if ($value['status'] == '1') {
                        $count_max++;
                        if (in_array($value['id_colors'], $disabled_colors)) {
                            
                        } else {
                            $data['colors'][] = array(
                                'id_colors' => $value['id_colors'],
                                'code' => $value['code'],
                                'status' => $value['status'],
                            );
                        }
                    }
                }
                $data['fonts'] = $this->model_kbproduct_customizer_kbproduct_customizer->getFonts($product_id);
                $data['groups'] = $this->model_kbproduct_customizer_kbproduct_customizer->getGroups($product_id);
                $data['images'] = $this->model_kbproduct_customizer_kbproduct_customizer->getImages($data['groups'], $product_id);//Changed by Shivam Bansal on 7-7-2021 for sen
               


                foreach ($data['images'] as $key => $value) {
                    $data['images'][$key]['price'] = $this->currency->format($value['price'], $this->session->data['currency'], '', false);
                    ;
                }
            }
        }

        return $this->load->view('kbproduct_customizer/kbproduct_customizer', $data);
    }

    public function saveCustomizedProduct() {

        $this->load->model('kbproduct_customizer/kbproduct_customizer');

        $token = $this->request->post['token'];
        $kb_current = $this->request->post['kb_current'];
        $id_product = $this->request->post['id_product'];
        $id_product_attribute = $this->request->post['ipa'];
        $customization_cost = $this->request->post['customization_cost'];
        $canvasSide = json_decode(html_entity_decode($this->request->post['canvasObjectSide']), true);
        $canvasLayer = json_decode(html_entity_decode($this->request->post['canvasLayer']), true);
        $design_data = array();
        $design_src = array();
        $objectData = array();
        $canvas_layer_count = 0;
//        $this->cart->add($id_product,$this->quantity);
//        $cart_id = $this->model_kbproduct_customizer_kbproduct_customizer->getCartId($id_product);

        if (isset($canvasLayer['src'])) {
            $design_src[] = $canvasLayer['src'];
            $canvas_layer_count = 1;
            /*
             * decode base64 image created while
             * saving the customization
             */

            /* We have used base64_decode to convert the base64 url into image. hence, we cannot remove it for the validator */
            $decoded = base64_decode(str_replace('data:image/png;base64,', '', $canvasLayer['src']));
            /*
             * save the product customization image into the system
             */
            file_put_contents(DIR_IMAGE . 'kbproduct_customizer/customImage/' . $kb_current . '_' . $canvas_layer_count . '.png', $decoded);
            $canvasLayer['object']['img_src'] = 'kbproduct_customizer/customImage/' . $kb_current . '_' . $canvas_layer_count . '.png';
            $canvasLayer['object']['name'] = $canvasLayer['name'];
            $design_data[] = $canvasLayer['object'];
        } else {
            $i = 1;
            foreach ($canvasLayer as $canvas_layer) {
                if (isset($canvas_layer['src'])) {
                    $design_src[] = $canvas_layer['src'];
                    $canvas_layer_count = $i;
                    /*
                     * decode base64 image created while
                     * saving the customization
                     */
                    /* We have used base64_decode to convert the base64 url into image. hence, we cannot remove it for the validator */
                    $decoded = base64_decode(str_replace('data:image/png;base64,', '', $canvas_layer['src']));
                    /*
                     * save the product customization image into the system
                     */
                    file_put_contents(DIR_IMAGE . 'kbproduct_customizer/customImage/' . $kb_current . '_' . $canvas_layer_count . '.png', $decoded);
                    /*
                     * add background color of the image into the object array
                     */
                    if (isset($canvas_layer['backgroundColor'])) {
                        $canvas_layer['object']['backgroundColor'] = $canvas_layer['backgroundColor'];
                    }
                    $canvas_layer['object']['img_src'] = 'kbproduct_customizer/customImage/' . $kb_current . '_' . $canvas_layer_count . '.png';
                    $canvas_layer['object']['name'] = $canvas_layer['name'];
                    $design_data[] = $canvas_layer['object'];
                    $i++;
                }
            }
        }
        if (!empty($canvasSide)) {
            $cust_id = $this->customer->getId();
            /*
             * get convas object data applied on the product
             */
            $i = 1;
            foreach ($canvasSide as $key => $canvas_Side) {
                foreach ($canvas_Side as $canvas) {
                    $data = array();
                    $data['id'] = $canvas['id'];
                    $data['cost'] = $canvas['cost'];
//                    $data['name'] = $canvas['name'];
                    $canvas_obj_img = $canvas['src'];
                    $canvas_obj_img_url = '';
                    if ($canvas_obj_img != '') {
                        /*
                         * create a unique salt
                         */
                        $salt = substr(sha1(uniqid(rand(), true) . $cust_id . $kb_current . $i), 0, 8);
                        /*
                         * decode base64 object image applied on the product
                         */
                        /* We have used base64_decode to convert the base64 url into image. hence, we cannot remove it for the validator */
                        $decoded = base64_decode(str_replace('data:image/png;base64,', '', $canvas_obj_img));
                        $canvas_obj_img_path = DIR_IMAGE . 'kbproduct_customizer/customImage/' . $salt . '_' . $i . '.png';
                        $canvas_obj_img_url = 'kbproduct_customizer/customImage/' . $salt . '_' . $i . '.png';
                        /*
                         * save the object image into the system
                         */
                        file_put_contents($canvas_obj_img_path, $decoded);
                    }
                    $data['object_src'] = $canvas_obj_img_url;
                    $data['object'] = $canvas['object'];
                    $objectData[$key][] = $data;
                    $i++;
                }
            }
        }
        $objectData = json_encode($objectData);

        $data = array(
            'design_src' => $design_src,
            'design_data' => $design_data,
            'object_data' => $objectData,
            'canvas_layer_count' => $canvas_layer_count,
            'cart_id' => '',
            'id_product_attribute' => $id_product_attribute,
            'id_product' => $id_product,
            'token' => $token,
            'customization_cost' => $customization_cost,
        );
        $design_id = $this->model_kbproduct_customizer_kbproduct_customizer->addDesign($data);
        echo $design_id;
    }

    public function getCustomizedProduct($cart_id, $product_id) {

        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $product_data = $this->model_kbproduct_customizer_kbproduct_customizer->getCustomizedProduct($cart_id, $product_id);
        var_dump($product_data);
    }

    public function checkProductAdd() {
        $this->load->language('checkout/cart');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $json = array();

        if (isset($this->request->post['product_id'])) {
            $product_id = (int) $this->request->post['product_id'];
        } else {
            $product_id = 0;
        }
        if (isset($this->request->post['quantity'])) {
            $quantity_post = (int) $this->request->post['quantity'];
        } else {
            $quantity_post = 1;
        }
        if (isset($this->request->post['option'])) {
            $option = array_filter($this->request->post['option']);
        } else {
            $option = array();
        }
        if (isset($this->request->post['recurring_id'])) {
            $recurring_id = $this->request->post['recurring_id'];
        } else {
            $recurring_id = 0;
        }
        if (isset($this->request->post['customize_add']) && $this->request->post['customize_add'] != '0') {
            $design_id = $this->request->post['customize_add'];
        } else {
            $design_id = 0;
        }
//            var_dump($design_id);die;
        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {
            if ((int) $quantity_post >= $product_info['minimum']) {
                $quantity = (int) $quantity_post;
            } else {
                $quantity = $product_info['minimum'] ? $product_info['minimum'] : 1;
            }

            $product_options = $this->model_catalog_product->getProductOptions($product_id);

            foreach ($product_options as $product_option) {
                if ($product_option['required'] && empty($option[$product_option['product_option_id']])) {
                    $json['error']['option'][$product_option['product_option_id']] = sprintf($this->language->get('error_required'), $product_option['name']);
                }
            }

            $recurrings = $this->model_catalog_product->getProfiles($product_info['product_id']);

            if ($recurrings) {
                $recurring_ids = array();

                foreach ($recurrings as $recurring) {
                    $recurring_ids[] = $recurring['recurring_id'];
                }

                if (!in_array($recurring_id, $recurring_ids)) {
                    $json['error']['recurring'] = $this->language->get('error_recurring_required');
                }
            }

            if (!$json) {

                if ($design_id != '0') {
                    $this->model_kbproduct_customizer_kbproduct_customizer->add($this->request->post['product_id'], $quantity, $option, $recurring_id, $design_id);

                    // Unset all shipping and payment methods
                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                }
                $json['success'] = sprintf($this->language->get('text_success'), $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']), $product_info['name'], $this->url->link('checkout/cart'));
                $json['redirect'] = $this->url->link('checkout/cart');
            } else {
                $json['redirect'] = str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $this->request->post['product_id']));
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function checkProductRequired() {
        $json = array();
        if (isset($this->request->post['id'])) {
            $product_id = $this->request->post['id'];
            $this->load->model('kbproduct_customizer/kbproduct_customizer');
            $product_settings = $this->model_kbproduct_customizer_kbproduct_customizer->getProductSetting($product_id);
//            var_dump($product_settings);die;
            if (!empty($product_settings)) {
                if (isset($product_settings['enable']) && $product_settings['enable'] == '1') {
                    if (isset($product_settings['is_required']) && $product_settings['is_required'] == '1') {
                        $json['redirect'] = $this->url->link('product/product&product_id=' . $product_id);
//                        $this->response->redirect($this->url->link('product/product&product_id='.$product_id));
                    }
                }
            }
        }
        $this->response->setOutput(json_encode($json));
    }

    public function generatePdf() {

        $html = '';
        $dompdf = new DOMPDF();
        $html = utf8_decode($image_content);
        $dompdf->load_html($html);
        $dompdf->render();
        $dompdf->stream('preview.pdf');
    }

}
