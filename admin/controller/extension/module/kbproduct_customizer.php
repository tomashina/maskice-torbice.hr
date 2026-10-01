<?php

class ControllerExtensionModuleKbproductCustomizer extends Controller {

    private $session_token_key = 'token';
    private $session_token = '';
    private $module_path = '';

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
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_setting_setting->editSetting('kbproduct_customizer', $this->request->post, $store_id);

            if (VERSION < 3.0) {
                $this->model_setting_setting->editSetting('customization', array('customization_status' => $this->request->post['kbproduct_customizer']['enable']));
            } else {
                $this->model_setting_setting->editSetting('total_customization', array('total_customization_status' => $this->request->post['kbproduct_customizer']['enable']));
            }
            $enable_status['module_kbproduct_customizer_status'] = $this->request->post['kbproduct_customizer']['enable'];
            $this->model_setting_setting->editSetting('module_kbproduct_customizer', $enable_status, $store_id);

            $this->session->data['success'] = $this->language->get('success_configure');
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);

        $data['text_edit'] = $this->language->get('heading_title');
        $data['heading_title'] = $this->language->get('heading_title');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_enable'] = $this->language->get('text_enable');
        $data['text_display_option_download'] = $this->language->get('text_display_option_download');
        $data['text_display_option_download_tooltip'] = $this->language->get('text_display_option_download_tooltip');
        $data['text_show_designer_preview'] = $this->language->get('text_show_designer_preview');
        $data['text_max_sides'] = $this->language->get('text_max_sides');
        $data['text_max_sides_tooltip'] = $this->language->get('text_max_sides_tooltip');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_disable_status'] = $this->language->get('text_disable_status');
        $data['text_enable_status'] = $this->language->get('text_enable_status');

        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['error_max_side_limit'] = $this->language->get('error_max_side_limit');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['error_empty_field'] = $this->language->get('error_empty_field');
        $data['error_positive_number'] = $this->language->get('error_positive_number');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $settings = $this->model_setting_setting->getSetting('kbproduct_customizer', $store_id);

        if (isset($this->request->post['kbproduct_customizer'])) {
            $data['kbproduct_customizer'] = $this->request->post['kbproduct_customizer'];
        } elseif (isset($settings['kbproduct_customizer']) && !empty($settings['kbproduct_customizer'])) {
            $data['kbproduct_customizer'] = $settings['kbproduct_customizer'];
        } else {
            $data['kbproduct_customizer']['enable'] = 0;
            $data['kbproduct_customizer']['display_download'] = 0;
            $data['kbproduct_customizer']['max_sides'] = 2;
        }

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 1;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);
        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configuration.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configuration', $data));
        }
    }

    //when user will install module
    public function install() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->language('extension/module/kbproduct_customizer');
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_fonts` (
            `id_fonts` int(11) AUTO_INCREMENT,
            `font_title` varchar(255) NOT NULL DEFAULT '0',
            `font_url` text NOT NULL,
            `store_id` int(11) DEFAULT 0,
            `status` smallint(5),
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id_fonts)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $count = $this->db->query("SELECT * FROM `" . DB_PREFIX . "velsof_product_customizer_fonts`");
        if (!$count->num_rows) {
            $sql = "INSERT INTO `" . DB_PREFIX . "velsof_product_customizer_fonts` (`id_fonts`, `font_title`, `font_url`, `status`) VALUES
                    (1, 'Emblema One', '@import url(https://fonts.googleapis.com/css?family=Emblema+One);', 1),
                    (2, 'Mr Bedfort', '@import url(https://fonts.googleapis.com/css?family=Mr+Bedfort);', 1),
                    (3, 'Miss Fajardose', '@import url(https://fonts.googleapis.com/css?family=Miss+Fajardose);', 1),
                    (4, 'Roboto', '@import url(https://fonts.googleapis.com/css?family=Roboto);', 1),
                    (5, 'Open Sans', '@import url(https://fonts.googleapis.com/css?family=Open+Sans);', 1),
                    (6, 'Pacifico', '@import url(https://fonts.googleapis.com/css?family=Pacifico);', 1),
                    (7, 'Bad Script', '@import url(https://fonts.googleapis.com/css?family=Bad+Script);', 1),
                    (8, 'Parisienne', '@import url(https://fonts.googleapis.com/css?family=Parisienne);', 1),
                    (9, 'Cabin Sketch', '@import url(https://fonts.googleapis.com/css?family=Cabin+Sketch);', 1),
                    (10, 'Homemade Apple', '@import url(https://fonts.googleapis.com/css?family=Homemade+Apple);', 1),
                    (11, 'Skranji', '@import url(https://fonts.googleapis.com/css?family=Skranji);', 1),
                    (12, 'Dancing Script', '@import url(https://fonts.googleapis.com/css?family=Dancing+Script);', 1);";
            $this->db->query($sql);
        }

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_colors` (
            `id_colors` int(11) AUTO_INCREMENT,
            `code` varchar(255) NOT NULL,
            `store_id` int(11) DEFAULT 0,
            `status` smallint(5) DEFAULT 0,
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id_colors)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $count = $this->db->query("SELECT * FROM `" . DB_PREFIX . "velsof_product_customizer_colors`");
        if (!$count->num_rows) {
            $sql = "INSERT INTO `" . DB_PREFIX . "velsof_product_customizer_colors` (`id_colors`, `code`, `status`) VALUES
                    (1, '#ff0000', 1),
                    (2, '#000000', 1),
                    (3, '#0000ff', 1),
                    (4, '#7fffd4', 1),
                    (5, '#fa8072', 1),
                    (6, '#008000', 1),
                    (7, '#ff7f50', 1),
                    (8, '#ffff66', 1),
                    (9, '#66cccc', 1),
                    (10, '#ff1493', 1),
                    (11, '#800000', 1),
                    (12, '#800080', 1),
                    (13, '#00004b', 1),
                    (14, '#db006e', 1),
                    (15, '#b3b3b3', 1),
                    (16, '#00db6e', 1),
                    (17, '#ff4848', 1),
                    (18, '#4b0026', 1),
                    (19, '#ffb6c1', 1),
                    (20, '#fff68f', 1),
                    (21, '#ffffff', 1)";
            $this->db->query($sql);
        }

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_colors_product` (
            `id_color_product` int(11) AUTO_INCREMENT,
            `id_color` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `id_product` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `store_id` int(11) DEFAULT 0,
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            `date_upd` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id_color_product)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_design_output` (
            `id_design` int(11) AUTO_INCREMENT,
            `design_src` text,
            `design_data` mediumtext,
            `object_data` mediumtext,
            `layer_count` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            `date_upd` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id_design)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_cart_design` (
            `id` int(11) AUTO_INCREMENT,
            `cart_id` int(11),
            `product_id` int(11),
            `product_attribute_id` int(11),
            `design_id` int(11),
            `price` float,
            `date_added` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_fonts_product` (
            `id_product_font` int(11) AUTO_INCREMENT,
            `id_font` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `id_product` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `store_id` int(11) DEFAULT 0,
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            `date_upd` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id_product_font)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_image_groups` (
            `id_groups` int(11) AUTO_INCREMENT,
            `name` varchar(255),
            `preview` varchar(255),
            `store_id` int(11) DEFAULT 0,
            `status` smallint(5),
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id_groups)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        foreach ($languages as $key => $language) {
            $animals[$language['language_id']] = $this->language->get('text_animals');
            $food_drink[$language['language_id']] = $this->language->get('text_food_drink');
            $love[$language['language_id']] = $this->language->get('text_love');
            $zodiac[$language['language_id']] = $this->language->get('text_zodiac');
            $nature[$language['language_id']] = $this->language->get('text_nature');
            $wallpapers[$language['language_id']] = $this->language->get('text_wallpapers');
            $flags[$language['language_id']] = $this->language->get('text_flags');
            $shapes[$language['language_id']] = $this->language->get('text_shapes');
        }

        $count = $this->db->query("SELECT * FROM `" . DB_PREFIX . "velsof_product_customizer_image_groups`");
        if (!$count->num_rows) {
            $sql = "INSERT INTO `" . DB_PREFIX . "velsof_product_customizer_image_groups` (`id_groups`, `name`, `preview`,`status`) VALUES
                    (1, '" . $this->db->escape(json_encode($animals, true)) . "', 'kbproduct_customizer/imagegroup/icons/animals/1.png', 1),
                    (2, '" . $this->db->escape(json_encode($food_drink, true)) . "', 'kbproduct_customizer/imagegroup/icons/food/1.png', 1),
                    (3, '" . $this->db->escape(json_encode($love, true)) . "', 'kbproduct_customizer/imagegroup/icons/love/1.png', 1),
                    (4, '" . $this->db->escape(json_encode($zodiac, true)) . "', 'kbproduct_customizer/imagegroup/icons/zodiac/1.png', 1),
                    (5, '" . $this->db->escape(json_encode($nature, true)) . "', 'kbproduct_customizer/imagegroup/icons/nature/1.png', 1),
                    (6, '" . $this->db->escape(json_encode($wallpapers, true)) . "', 'kbproduct_customizer/imagegroup/icons/wallpapers/1.jpg', 1),
                    (7, '" . $this->db->escape(json_encode($flags, true)) . "', 'kbproduct_customizer/imagegroup/icons/flags/1.png', 1),
                    (8, '" . $this->db->escape(json_encode($shapes, true)) . "', 'kbproduct_customizer/imagegroup/icons/shapes/1.png', 1)";
            $this->db->query($sql);
        }

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_groups_product` (
            `id_group_product` int(11) AUTO_INCREMENT,
            `id_group` varchar(255),
            `id_product` varchar(255),
            `store_id` int(11) DEFAULT 0,
            `status` smallint(5),
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            `date_upd` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id_group_product)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_images` (
            `id_image` int(11) AUTO_INCREMENT,
            `id_group` varchar(255),
            `price` decimal(15,2) NOT NULL,
            `image` text,
            `store_id` int(11) DEFAULT 0,
            `status` smallint(5),
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            `date_upd` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id_image)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $count = $this->db->query("SELECT * FROM `" . DB_PREFIX . "velsof_product_customizer_images`");
        if (!$count->num_rows) {
            $sql = "INSERT INTO `" . DB_PREFIX . "velsof_product_customizer_images` (`id_image`, `id_group`, `price`, `image`, `status`) VALUES
                    (1, 1, '5.00', 'kbproduct_customizer/imagegroup/icons/animals/2.png', 1),
                    (2, 2, '5.00', 'kbproduct_customizer/imagegroup/icons/food/2.png', 1),
                    (3, 3, '5.00', 'kbproduct_customizer/imagegroup/icons/love/2.png', 1),
                    (4, 5, '5.00', 'kbproduct_customizer/imagegroup/icons/nature/2.png', 1),
                    (5, 6, '5.00', 'kbproduct_customizer/imagegroup/icons/wallpapers/2.jpg', 1),
                    (6, 8, '5.00', 'kbproduct_customizer/imagegroup/icons/shapes/2.png', 1),
                    (7, 1, '5.00', 'kbproduct_customizer/imagegroup/icons/animals/3.png', 1),
                    (8, 2, '5.00', 'kbproduct_customizer/imagegroup/icons/food/3.png', 1),
                    (9, 3, '5.00', 'kbproduct_customizer/imagegroup/icons/love/3.png', 1),
                    (10, 5, '5.00', 'kbproduct_customizer/imagegroup/icons/nature/3.png', 1),
                    (11, 6, '5.00', 'kbproduct_customizer/imagegroup/icons/wallpapers/3.jpg', 1),
                    (12, 8, '5.00', 'kbproduct_customizer/imagegroup/icons/shapes/3.png', 1),
                    (13, 1, '5.00', 'kbproduct_customizer/imagegroup/icons/animals/4.png', 1),
                    (14, 2, '5.00', 'kbproduct_customizer/imagegroup/icons/food/4.png', 1),
                    (15, 3, '5.00', 'kbproduct_customizer/imagegroup/icons/love/4.png', 1),
                    (16, 5, '5.00', 'kbproduct_customizer/imagegroup/icons/nature/4.png', 1),
                    (17, 6, '5.00', 'kbproduct_customizer/imagegroup/icons/wallpapers/4.jpg', 1),
                    (18, 8, '5.00', 'kbproduct_customizer/imagegroup/icons/shapes/4.png', 1),
                    (19, 1, '5.00', 'kbproduct_customizer/imagegroup/icons/animals/5.png', 1),
                    (20, 2, '5.00', 'kbproduct_customizer/imagegroup/icons/food/5.png', 1),
                    (21, 3, '5.00', 'kbproduct_customizer/imagegroup/icons/love/5.png', 1),
                    (22, 5, '5.00', 'kbproduct_customizer/imagegroup/icons/nature/5.png', 1),
                    (23, 6, '5.00', 'kbproduct_customizer/imagegroup/icons/wallpapers/5.jpg', 1),
                    (24, 8, '5.00', 'kbproduct_customizer/imagegroup/icons/shapes/5.png', 1),
                    (25, 1, '5.00', 'kbproduct_customizer/imagegroup/icons/animals/6.png', 1),
                    (26, 2, '5.00', 'kbproduct_customizer/imagegroup/icons/food/6.png', 1),
                    (27, 3, '5.00', 'kbproduct_customizer/imagegroup/icons/love/6.png', 1),
                    (28, 5, '5.00', 'kbproduct_customizer/imagegroup/icons/nature/6.png', 1),
                    (29, 6, '5.00', 'kbproduct_customizer/imagegroup/icons/wallpapers/6.jpg', 1),
                    (30, 8, '5.00', 'kbproduct_customizer/imagegroup/icons/shapes/6.png', 1),
                    (31, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/2.png', 1),
                    (32, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/3.png', 1),
                    (33, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/4.png', 1),
                    (34, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/5.png', 1),
                    (35, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/6.png', 1),
                    (36, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/7.png', 1),
                    (37, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/8.png', 1),
                    (38, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/9.png', 1),
                    (39, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/10.png', 1),
                    (40, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/11.png', 1),
                    (41, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/12.png', 1),
                    (42, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/13.png', 1),
                    (43, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/14.png', 1),
                    (44, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/15.png', 1),
                    (45, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/16.png', 1),
                    (46, 7, '5.00', 'kbproduct_customizer/imagegroup/icons/flags/17.png', 1),
                    (47, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/2.png', 1),
                    (48, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/3.png', 1),
                    (49, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/4.png', 1),
                    (50, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/5.png', 1),
                    (51, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/6.png', 1),
                    (52, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/7.png', 1),
                    (53, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/8.png', 1),
                    (54, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/9.png', 1),
                    (55, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/10.png', 1),
                    (56, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/11.png', 1),
                    (57, 4, '5.00', 'kbproduct_customizer/imagegroup/icons/zodiac/12.png', 1);";
            $this->db->query($sql);
        }

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_image_product` (
            `id_image_product` int(11) AUTO_INCREMENT,
            `id_image` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `id_product` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `store_id` int(11) DEFAULT 0,
            `status` smallint(5),
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            `date_upd` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id_image_product)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "velsof_product_customizer_product_setting` (
            `id` int(11) AUTO_INCREMENT,
            `id_product` int(11) UNSIGNED NOT NULL,
            `enable` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `is_required` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `upload_image_size` int(15) NOT NULL DEFAULT '0',
            `upload_image_price` decimal(15,2) NOT NULL,
            `show_download_png` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `show_download_pdf_cart` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `allow_img_upload` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `resize_text` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `rotator_text` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `enable_qrcode` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `image_transparency` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `enable_image_filters` int(11) UNSIGNED NOT NULL DEFAULT '0',
            `image_filters` text,
            `text_fixed_price` decimal(15,2) NOT NULL,
            `enable_cost_character` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `design_fixed_price` decimal(15,2) NOT NULL,
            `qrcode_price` decimal(15,2) NOT NULL,
            `display_text_block` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `max_text_length` int(15) NOT NULL DEFAULT '0',
            `min_text_length` int(15) NOT NULL DEFAULT '0',
            `allow_text_transparency` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `allow_text_curve` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `slice_data` text,
            `store_id` int(11) DEFAULT 0,
            `active` tinyint(1) UNSIGNED NOT NULL DEFAULT '0',
            `date_add` datetime DEFAULT CURRENT_TIMESTAMP,
            `date_upd` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ";
        $this->db->query($sql);

        $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "cart' AND COLUMN_NAME = 'kbdesign_id' ";
        $query = $this->db->query($sql);

        if ($query->num_rows != '1') {
            $sql = "ALTER TABLE `" . DB_PREFIX . "cart` ADD `kbdesign_id` INT NOT NULL DEFAULT '0' ";
            $this->db->query($sql);
        }
        $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "order_product' AND COLUMN_NAME = 'kbdesign_id' ";
        $query = $this->db->query($sql);

        if ($query->num_rows != '1') {
            $sql = "ALTER TABLE `" . DB_PREFIX . "order_product` ADD `kbdesign_id` INT NOT NULL DEFAULT '0' ";
            $this->db->query($sql);
        }
        
        $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '" . DB_DATABASE . "' AND TABLE_NAME = '" . DB_PREFIX . "velsof_product_customizer_product_setting' AND COLUMN_NAME = 'transparency_status' ";
        $query = $this->db->query($sql);
        if ($query->num_rows != '1') {
            $sql = "ALTER TABLE `" . DB_PREFIX . "velsof_product_customizer_product_setting` ADD `transparency_status` ENUM('0','1') NOT NULL DEFAULT '0' COMMENT '\'0\' = Transparent, \'1\' = Non-Transparent' ";
            $this->db->query($sql);
        }

        if (VERSION < 3.0) {
            $this->load->model('extension/extension');
        } else {
            $this->load->model('setting/extension');
        }

        $this->load->model('user/user_group');

        if (VERSION < 3.0) {
            $this->model_extension_extension->install('total', 'customization');
        } else {
            $this->model_setting_extension->install('total', 'customization');
        }

        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/total/customization');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/total/customization');
    }

    //when user will uninstall module
    public function uninstall() {
        $this->load->model('setting/setting');
        if (VERSION < 3.0) {
            $this->load->model('extension/extension');
        } else {
            $this->load->model('setting/extension');
        }
        $this->model_setting_setting->deleteSetting('kbproduct_customizer');
        if (VERSION < 3.0) {
            $this->model_extension_extension->uninstall('total', 'customization');
        } else {
            $this->model_setting_extension->uninstall('total', 'customization');
        }
    }

    public function store_swticher($data = array()) {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/store');
        $data['stores'] = $this->model_setting_store->getStores();
        if (!empty($data['stores'])) {
            if (VERSION < 2.2) {
                return $this->load->view($this->module_path . '/kbproduct_customizer/store_switcher.tpl', $data);
            } else {
                return $this->load->view($this->module_path . '/kbproduct_customizer/store_switcher', $data);
            }
        } else {
            return "";
        }
    }

    public function tabs($data = array()) {
        $this->load->language($this->module_path . '/kbproduct_customizer');

        $store_id = $data['store_id'];

        $data['tab_configure'] = $this->language->get('tab_configure');
        $data['tab_fonts'] = $this->language->get('tab_fonts');
        $data['tab_colors'] = $this->language->get('tab_colors');
        $data['tab_images'] = $this->language->get('tab_images');

        $data['tab_configure_url'] = $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['tab_fonts_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['tab_colors_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['tab_images_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);

        if (VERSION < 2.2) {
            return $this->load->view($this->module_path . '/kbproduct_customizer/tabs.tpl', $data);
        } else {
            return $this->load->view($this->module_path . '/kbproduct_customizer/tabs', $data);
        }
    }

    // Function for fonts tab
    public function fonts() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_setting_setting->editSetting('kbproduct_customizer', $this->request->post, $store_id);
            $this->session->data['success'] = $this->language->get('success');
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['editFont_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/editfont', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['deleteFont_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/deletefont', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);

        $data['text_edit'] = $this->language->get('tab_fonts');
        $data['heading_title'] = $this->language->get('heading_title');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('tab_fonts'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_id'] = $this->language->get('text_id');
        $data['text_font'] = $this->language->get('text_font');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_stauts_active'] = $this->language->get('text_stauts_active');
        $data['text_stauts_inactive'] = $this->language->get('text_stauts_inactive');

        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['text_date_add'] = $this->language->get('text_date_add');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');
        $data['text_are_you_sure'] = $this->language->get('text_are_you_sure');

        $data['error_field_empty'] = $this->language->get('error_field_empty');
        $data['error_url'] = $this->language->get('error_url');
        $data['required'] = $this->language->get('required');
        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['invalid_url'] = $this->language->get('invalid_url');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['filter_font'])) {
            $filter_font = $this->request->get['filter_font'];
        } else {
            $filter_font = null;
        }

        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        if (isset($this->request->post['reset'])) {
            $filter_font = null;
            $filter_status = null;
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_fonts';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (!isset($this->request->get['page'])) {
            $page = 1;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        }

        $filter_data = array(
            'filter_font' => $filter_font,
            'filter_status' => $filter_status,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $fonts_data = $this->model_kbproduct_customizer_kbproduct_customizer->getAllFonts($filter_data, $store_id);
        $data['fonts_data'] = array();

        foreach ($fonts_data as $key => $value) {
            $data['fonts_data'][] = array(
                'id' => $value['id_fonts'],
                'font' => $value['font_title'],
                'date_add' => date($this->language->get('date_format_short'), strtotime($value['date_add'])),
                'status' => $value['status'],
                'status_text' => $value['status'] == 1 ? $this->language->get('text_stauts_active') : $this->language->get('text_stauts_inactive'),
                'font_url' => $value['font_url'],
            );
        }

        $filter_data = array(
            'filter_font' => $filter_font,
            'filter_status' => $filter_status,
        );
        $total_fonts = count($this->model_kbproduct_customizer_kbproduct_customizer->getAllFonts($filter_data, $store_id));

        $url = '';

        if (isset($this->request->get['filter_font'])) {
            $url .= '&filter_font=' . $this->request->get['filter_font'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_id'] = $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&sort=id_fonts&store_id=' . $store_id . $url, true);
        $data['sort_font'] = $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&sort=font_title&store_id=' . $store_id . $url, true);
        $data['sort_status'] = $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&sort=status&store_id=' . $store_id . $url, true);
        $data['sort_date_add'] = $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&sort=date_add&store_id=' . $store_id . $url, true);

        $url = '';
        if (isset($this->request->get['filter_font'])) {
            $url .= '&filter_font=' . $this->request->get['filter_font'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $total_fonts;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}&store_id=' . $store_id, true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_fonts) ? (($page - 1) * $pagination->limit) + 1 : 0, ((($page - 1) * $pagination->limit) > ($total_fonts - $pagination->limit)) ? $total_fonts : ((($page - 1) * $pagination->limit) + $pagination->limit), $total_fonts, ceil($total_fonts / $pagination->limit));

        $data['filter_font'] = $filter_font;
        $data['filter_status'] = $filter_status;
        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 2;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);

        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/fonts.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/fonts', $data));
        }
    }

    // Function to edit/add the fonts
    public function editfont() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }
        $font_id = 0;
        if (isset($this->request->get['font_id'])) {
            $font_id = $this->request->get['font_id'];
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_kbproduct_customizer_kbproduct_customizer->setFont($this->request->post, $store_id);
            if ($this->request->post['id_fonts'] == 0) {
                $this->session->data['success'] = $this->language->get('success_fonts_add');
            } else {
                $this->session->data['success'] = $this->language->get('success_fonts_edit');
            }
            $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, 'SSL'));
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/editfont', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['cancel'] = $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);

        if ($font_id == 0) {
            $data['text_edit'] = $this->language->get('text_add_font');
        } else {
            $data['text_edit'] = $this->language->get('text_edit_font');
        }

        $data['heading_title'] = $this->language->get('heading_title');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('tab_fonts'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_id'] = $this->language->get('text_id');
        $data['text_font'] = $this->language->get('text_font');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['fonts_hint2'] = $this->language->get('fonts_hint2');
        $data['fonts_hint1'] = $this->language->get('fonts_hint1');
        $data['text_embed_font'] = $this->language->get('text_embed_font');
        $data['text_embed_font_tooltip'] = $this->language->get('text_embed_font_tooltip');
        $data['text_font_title_tooltip'] = $this->language->get('text_font_title_tooltip');
        $data['text_font_title'] = $this->language->get('text_font_title');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');


        $data['error_empty_field'] = $this->language->get('error_empty_field');
        $data['error_url'] = $this->language->get('error_url');
        $data['required'] = $this->language->get('required');
        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['invalid_url'] = $this->language->get('invalid_url');
        $data['duplicate_font_name'] = $this->language->get('duplicate_font_name');
        $data['duplicate_font_url'] = $this->language->get('duplicate_font_url');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $fonts_data = $this->model_kbproduct_customizer_kbproduct_customizer->getFont($font_id, $store_id);
        $all_fonts = $this->model_kbproduct_customizer_kbproduct_customizer->getAllFontsArray($font_id, $store_id);
        $data['all_fonts_name'] = json_encode($all_fonts['title']);
        $data['all_fonts_url'] = addslashes(json_encode($all_fonts['url']));

        if (isset($this->request->post['enable'])) {
            $data['enable'] = $this->request->post['enable'];
        } else if (isset($fonts_data['status']) && $fonts_data['status'] != '') {
            $data['enable'] = $fonts_data['status'];
        } else {
            $data['enable'] = '1';
        }

        if (isset($this->request->post['id_fonts'])) {
            $data['id_fonts'] = $this->request->post['id_fonts'];
        } else if (isset($fonts_data['id_fonts']) && $fonts_data['id_fonts'] != '') {
            $data['id_fonts'] = $fonts_data['id_fonts'];
        } else {
            $data['id_fonts'] = '0';
        }

        if (isset($this->request->post['title'])) {
            $data['title'] = $this->request->post['title'];
        } else if (isset($fonts_data['font_title']) && $fonts_data['font_title'] != '') {
            $data['title'] = $fonts_data['font_title'];
        } else {
            $data['title'] = '';
        }

        if (isset($this->request->post['font_url'])) {
            $data['font_url'] = $this->request->post['font_url'];
        } else if (isset($fonts_data['font_url']) && $fonts_data['font_url'] != '') {
            $data['font_url'] = $fonts_data['font_url'];
        } else {
            $data['font_url'] = '';
        }

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 2;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);

        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/editfont', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/editfont.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/editfont', $data));
        }
    }

    // Function for Colors tab
    public function colors() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_kbproduct_customizer_kbproduct_customizer->setColor($this->request->post, $store_id);
            $this->session->data['success'] = $this->language->get('success');
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['editColor_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/editcolor', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['deleteColor_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/deletecolor', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['text_edit'] = $this->language->get('tab_colors');
        $data['heading_title'] = $this->language->get('heading_title');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('tab_colors'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_id'] = $this->language->get('text_id');
        $data['text_code'] = $this->language->get('text_code');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['text_are_you_sure'] = $this->language->get('text_are_you_sure');
        $data['text_date_add'] = $this->language->get('text_date_add');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');

        $data['error_field_empty'] = $this->language->get('error_field_empty');
        $data['error_url'] = $this->language->get('error_url');
        $data['required'] = $this->language->get('required');
        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['invalid_url'] = $this->language->get('invalid_url');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();


        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        if (isset($this->request->post['reset'])) {
            $filter_status = null;
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_colors';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (!isset($this->request->get['page'])) {
            $page = 1;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        }

        $filter_data = array(
            'filter_status' => $filter_status,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $data['colors_data'] = array();
        $colors_data = $this->model_kbproduct_customizer_kbproduct_customizer->getAllColors($filter_data, $store_id);
        foreach ($colors_data as $key => $value) {
            $data['colors_data'][] = array(
                'id' => $value['id_colors'],
                'color' => $value['code'],
                'status' => $value['status'],
                'date_add' => date($this->language->get('date_format_short'), strtotime($value['date_add'])),
                'status_text' => $value['status'] == 1 ? $this->language->get('text_stauts_active') : $this->language->get('text_stauts_inactive')
            );
        }

        $filter_data = array(
            'filter_status' => $filter_status,
        );
        $total_colors = count($this->model_kbproduct_customizer_kbproduct_customizer->getAllColors($filter_data, $store_id));

        $url = '';

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_id'] = $this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&sort=id_colors&store_id=' . $store_id . $url, true);
        $data['sort_status'] = $this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&sort=status&store_id=' . $store_id . $url, true);
        $data['sort_date_add'] = $this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&sort=date_add&store_id=' . $store_id . $url, true);

        $url = '';

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $total_colors;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}&store_id=' . $store_id, true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_colors) ? (($page - 1) * $pagination->limit) + 1 : 0, ((($page - 1) * $pagination->limit) > ($total_colors - $pagination->limit)) ? $total_colors : ((($page - 1) * $pagination->limit) + $pagination->limit), $total_colors, ceil($total_colors / $pagination->limit));

        $data['filter_status'] = $filter_status;
        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 3;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);

        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/colors.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/colors', $data));
        }
    }

    // Function to edit/add the colors
    public function editcolor() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }
        $color_id = 0;
        if (isset($this->request->get['color_id'])) {
            $color_id = $this->request->get['color_id'];
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_kbproduct_customizer_kbproduct_customizer->setColor($this->request->post, $store_id);
            if ($this->request->post['id_colors'] == 0) {
                $this->session->data['success'] = $this->language->get('success_colors_add');
            } else {
                $this->session->data['success'] = $this->language->get('success_colors_edit');
            }
            $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, 'SSL'));
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/editcolor', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['cancel'] = $this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);

        if ($color_id == 0) {
            $data['text_edit'] = $this->language->get('text_add_color');
        } else {
            $data['text_edit'] = $this->language->get('text_edit_color');
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('tab_colors'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_id'] = $this->language->get('text_id');
        $data['text_font'] = $this->language->get('text_font');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_color_code'] = $this->language->get('text_color_code');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['duplicate_color'] = $this->language->get('duplicate_color');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['error_empty_field'] = $this->language->get('error_empty_field');
        $data['invalid_color'] = $this->language->get('invalid_color');
        $data['maxchar_color'] = $this->language->get('maxchar_color');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $color_data = $this->model_kbproduct_customizer_kbproduct_customizer->getColor($color_id, $store_id);
        $all_colors = $this->model_kbproduct_customizer_kbproduct_customizer->getAllColorsArray($color_id, $store_id);
        $data['all_colors'] = json_encode($all_colors);

        if (isset($this->request->post['enable'])) {
            $data['enable'] = $this->request->post['enable'];
        } else if (isset($color_data['status']) && $color_data['status'] != '') {
            $data['enable'] = $color_data['status'];
        } else {
            $data['enable'] = '1';
        }

        $data['id_colors'] = $color_id;
        if (isset($this->request->post['color'])) {
            $data['color'] = $this->request->post['color'];
        } else if (isset($color_data['code']) && $color_data['code'] != '') {
            $data['color'] = $color_data['code'];
        } else {
            $data['color'] = '#ffffff';
        }

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 3;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);
        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/editcolor', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/editcolor.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/editcolor', $data));
        }
    }

    // Function for images tab
    public function images() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }

        $group_id = 0;
        if (isset($this->request->get['id_group'])) {
            $group_id = $this->request->get['id_group'];
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_kbproduct_customizer_kbproduct_customizer->setImage($this->request->post, $store_id);
            $this->session->data['success'] = $this->language->get('success');
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['editImage_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/editimage', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['viewImage_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['deleteImage_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/deleteimage', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);

        $data['cancel'] = $this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);

        $data['heading_title'] = $this->language->get('heading_title');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('tab_images'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_id'] = $this->language->get('text_id');
        $data['text_image_group'] = $this->language->get('text_image_group');
        $data['text_preview'] = $this->language->get('text_preview');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['button_view_images'] = $this->language->get('button_view_images');
        $data['text_are_you_sure'] = $this->language->get('text_are_you_sure');
        $data['text_date_add'] = $this->language->get('text_date_add');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');

        $data['error_field_empty'] = $this->language->get('error_field_empty');
        $data['error_url'] = $this->language->get('error_url');
        $data['required'] = $this->language->get('required');
        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['invalid_url'] = $this->language->get('invalid_url');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_images';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (!isset($this->request->get['page'])) {
            $page = 1;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        }

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $data['groups_data'] = array();
        $data['group_id'] = $group_id;

        $groups_data = $this->model_kbproduct_customizer_kbproduct_customizer->getImagesByGroup($group_id, $filter_data, $store_id);
        $group = $this->model_kbproduct_customizer_kbproduct_customizer->getGroup($group_id, $store_id);
        $data['text_edit'] = html_entity_decode($group['name'][$this->config->get('config_language_id')]);

        foreach ($groups_data as $key => $value) {
            $data['groups_data'][] = array(
                'id' => $value['id_image'],
                'preview' => HTTPS_CATALOG . 'image/' . $value['image'],
                'status' => $value['status'],
                'date_add' => date($this->language->get('date_format_short'), strtotime($value['date_add'])),
                'status_text' => $value['status'] = 1 ? $this->language->get('text_stauts_active') : $this->language->get('text_stauts_inactive')
            );
        }
        $total_images = count($groups_data);

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_id'] = $this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . '&sort=id_image&store_id=' . $store_id . '&id_group=' . $group_id . $url, true);
        $data['sort_status'] = $this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . '&sort=status&store_id=' . $store_id . '&id_group=' . $group_id . $url, true);


        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $total_images;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}&store_id=' . $store_id, true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_images) ? (($page - 1) * $pagination->limit) + 1 : 0, ((($page - 1) * $pagination->limit) > ($total_images - $pagination->limit)) ? $total_images : ((($page - 1) * $pagination->limit) + $pagination->limit), $total_images, ceil($total_images / $pagination->limit));

        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 4;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);
        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/images.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/images', $data));
        }
    }

    // Function for Image groups tab
    public function groups() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }

        $group_id = 0;
        if (isset($this->request->get['id_group'])) {
            $group_id = $this->request->get['id_group'];
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_kbproduct_customizer_kbproduct_customizer->setImage($this->request->post, $store_id);
            $this->session->data['success'] = $this->language->get('success');
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['editGroup_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/editgroup', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['viewImage_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['deleteGroup_url'] = $this->url->link($this->module_path . '/kbproduct_customizer/deleteGroup', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);

        $data['heading_title'] = $this->language->get('heading_title');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('tab_groups'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_id'] = $this->language->get('text_id');
        $data['text_image_group'] = $this->language->get('text_image_group');
        $data['text_preview'] = $this->language->get('text_preview');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['button_view_images'] = $this->language->get('button_view_images');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');
        $data['text_are_you_sure'] = $this->language->get('text_are_you_sure');
        $data['text_date_add'] = $this->language->get('text_date_add');

        $data['error_field_empty'] = $this->language->get('error_field_empty');
        $data['error_url'] = $this->language->get('error_url');
        $data['required'] = $this->language->get('required');
        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['invalid_url'] = $this->language->get('invalid_url');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_images';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (!isset($this->request->get['page'])) {
            $page = 1;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        }

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $data['groups_data'] = array();
        $data['group_id'] = $group_id;
        $groups_data = $this->model_kbproduct_customizer_kbproduct_customizer->getAllGroups($filter_data, $store_id);
        $data['text_edit'] = $this->language->get('tab_images');

        foreach ($groups_data as $key => $value) {
            $data['groups_data'][] = array(
                'id' => $value['id_groups'],
                'name' => json_decode($value['name'], true)[$this->config->get('config_language_id')],
                'preview' => HTTPS_CATALOG . 'image/' . $value['preview'],
                'date_add' => date($this->language->get('date_format_short'), strtotime($value['date_add'])),
                'status_text' => $value['status'] == 1 ? $this->language->get('text_stauts_active') : $this->language->get('text_stauts_inactive'),
                'status' => $value['status']
            );
        }
        $total_images = count($groups_data);

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_id'] = $this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&sort=id_groups&store_id=' . $store_id . $url, true);
        $data['sort_status'] = $this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&sort=status&store_id=' . $store_id . $url, true);
        $data['sort_date_add'] = $this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&sort=date_add&store_id=' . $store_id . $url, true);

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $total_images;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}&store_id=' . $store_id, true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_images) ? (($page - 1) * $pagination->limit) + 1 : 0, ((($page - 1) * $pagination->limit) > ($total_images - $pagination->limit)) ? $total_images : ((($page - 1) * $pagination->limit) + $pagination->limit), $total_images, ceil($total_images / $pagination->limit));

        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 4;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);
        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/groups.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/groups', $data));
        }
    }

    // Function to edit/add image
    public function editimage() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }
        $image_id = 0;
        if (isset($this->request->get['image_id'])) {
            $image_id = $this->request->get['image_id'];
        }
        $data['image_id'] = $image_id;

        $group_id = 0;
        if (isset($this->request->get['group_id'])) {
            $group_id = $this->request->get['group_id'];
        }
        $data['group_id'] = $group_id;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            $this->model_kbproduct_customizer_kbproduct_customizer->setImage($this->request->post, $store_id);
            if ($this->request->post['image_id'] == 0) {
                $this->session->data['success'] = $this->language->get('success_images_add');
            } else {
                $this->session->data['success'] = $this->language->get('success_images_edit');
            }
            $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/images&id_group=' . $group_id, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, 'SSL'));
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/editimage&group_id=' . $group_id, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['cancel'] = $this->url->link($this->module_path . '/kbproduct_customizer/images&id_group=' . $group_id, $this->session_token_key . '=' . $this->session_token . '&type=module&store_id=' . $store_id, true);

        if ($image_id == 0) {
            $data['text_edit'] = $this->language->get('text_add_image');
        } else {
            $data['text_edit'] = $this->language->get('text_edit_image');
        }
        $data['heading_title'] = $this->language->get('heading_title');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('tab_images'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/images', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );


        $data['text_id'] = $this->language->get('text_id');
        $data['text_font'] = $this->language->get('text_font');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_image_group_name'] = $this->language->get('text_image_group_name');
        $data['text_upload_image'] = $this->language->get('text_upload_image');
        $data['text_image_group_name'] = $this->language->get('text_image_group_name');
        $data['text_price'] = $this->language->get('text_price');
        $data['text_img_hint'] = $this->language->get('text_img_hint');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');

        $data['error_empty_field'] = $this->language->get('error_empty_field');
        $data['valid_amount'] = $this->language->get('valid_amount');
        $data['positive_amount'] = $this->language->get('positive_amount');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $image_data = $this->model_kbproduct_customizer_kbproduct_customizer->getImage($image_id, $store_id);

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } else if (isset($image_data['name']) && $image_data['name'] != '') {
            $data['name'] = $image_data['name'];
        } else {
            foreach ($data['languages'] as $key => $value) {
                $data['name'][$value['language_id']] = '';
            }
        }

        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } else if (isset($image_data['image']) && $image_data['image'] != '') {
            $data['image'] = $image_data['image'];
        } else {
            $data['image'] = '';
        }

        if (isset($this->request->post['price'])) {
            $data['price'] = $this->request->post['price'];
        } else if (isset($image_data['price']) && $image_data['price'] != '') {
            $data['price'] = $image_data['price'];
        } else {
            $data['price'] = 0;
        }

        if (isset($this->request->post['enable'])) {
            $data['enable'] = $this->request->post['enable'];
        } else if (isset($image_data['status']) && $image_data['status'] != '') {
            $data['enable'] = $image_data['status'];
        } else {
            $data['enable'] = 0;
        }

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 4;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);
        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/editimage', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/editimage.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/editimage', $data));
        }
    }

    // Function to edit/add group
    public function editgroup() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }

        $group_id = 0;
        if (isset($this->request->get['group_id'])) {
            $group_id = $this->request->get['group_id'];
        }
        $data['group_id'] = $group_id;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_kbproduct_customizer_kbproduct_customizer->setGroup($this->request->post, $store_id);
            if ($this->request->post['group_id'] == 0) {
                $this->session->data['success'] = $this->language->get('success_groups_add');
            } else {
                $this->session->data['success'] = $this->language->get('success_groups_edit');
            }
            $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, 'SSL'));
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/editgroup', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['cancel'] = $this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&type=module&store_id=' . $store_id, true);

        if ($group_id == '0') {
            $data['text_edit'] = $this->language->get('text_add_group');
        } else {
            $data['text_edit'] = $this->language->get('text_edit_group');
        }
        $data['heading_title'] = $this->language->get('heading_title');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link($this->extension_path, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('tab_groups'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );


        $data['text_id'] = $this->language->get('text_id');
        $data['text_font'] = $this->language->get('text_font');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_image_group_name'] = $this->language->get('text_image_group_name');
        $data['text_upload_image'] = $this->language->get('text_upload_image');
        $data['text_image_group_name'] = $this->language->get('text_image_group_name');
        $data['text_price'] = $this->language->get('text_price');
        $data['text_img_hint'] = $this->language->get('text_img_hint');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');

        $data['error_empty_field'] = $this->language->get('error_empty_field');
        $data['error_empty_lang'] = $this->language->get('error_name');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        $image_data = $this->model_kbproduct_customizer_kbproduct_customizer->getGroup($group_id, $store_id);

        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } else if (isset($image_data['name']) && $image_data['name'] != '') {
            $data['name'] = $image_data['name'];
        } else {
            foreach ($data['languages'] as $key => $value) {
                $data['name'][$value['language_id']] = '';
            }
        }

        if (isset($this->request->post['image'])) {
            $data['image'] = $this->request->post['image'];
        } else if (isset($image_data['image']) && $image_data['image'] != '') {
            $data['image'] = $image_data['image'];
        } else {
            $data['image'] = '';
        }

        if (isset($this->request->post['price'])) {
            $data['price'] = $this->request->post['price'];
        } else if (isset($image_data['price']) && $image_data['price'] != '') {
            $data['price'] = $image_data['price'];
        } else {
            $data['price'] = 0;
        }

        if (isset($this->request->post['enable'])) {
            $data['enable'] = $this->request->post['enable'];
        } else if (isset($image_data['status']) && $image_data['status'] != '') {
            $data['enable'] = $image_data['status'];
        } else {
            $data['enable'] = 0;
        }

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 4;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);
        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/editimage', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/editgroup.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/editgroup', $data));
        }
    }

    // Functiom to delete the font
    public function deleteFont() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $id = $this->request->get['id'];
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $this->model_kbproduct_customizer_kbproduct_customizer->deleteFont($id, $store_id);
        $this->session->data['success'] = $this->language->get('success_delete_font');
        $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, 'SSL'));
    }

    // Functiom to delete the color
    public function deleteColor() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $id = $this->request->get['id'];
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $this->model_kbproduct_customizer_kbproduct_customizer->deleteColor($id, $store_id);
        $this->session->data['success'] = $this->language->get('success_delete_color');
        $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/colors', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, 'SSL'));
    }

    // Functiom to delete the image group
    public function deleteGroup() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $id = $this->request->get['id'];
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $this->model_kbproduct_customizer_kbproduct_customizer->deleteGroup($id, $store_id);
        $this->session->data['success'] = $this->language->get('success_delete_group');
        $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/groups', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, 'SSL'));
    }

    // Functiom to delete the image
    public function deleteImage() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $id = $this->request->get['id'];
        $id_group = $this->request->get['group_id'];
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        } else {
            $store_id = 0;
        }
        $this->model_kbproduct_customizer_kbproduct_customizer->deleteImage($id, $id_group, $store_id);
        $this->session->data['success'] = $this->language->get('success_delete_image');
        $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/images&id_group=' . $id_group, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, 'SSL'));
    }

    public function product($kbdata) {
        $product_id = $kbdata['product_id'];
        $data['product_id'] = $kbdata['product_id'];
        $data['errors'] = $kbdata['errors'];
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');

        $data['tab_configuration'] = $this->language->get('tab_configuration');
        $data['tab_price'] = $this->language->get('tab_price');
        $data['tab_text'] = $this->language->get('tab_text');
        $data['tab_sides'] = $this->language->get('tab_sides');

        $data['text_kb'] = $this->language->get('text_kb');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_enable'] = $this->language->get('text_enable');
        $data['text_enable_tooltip'] = $this->language->get('text_enable_tooltip');
        $data['text_required_pro_customization'] = $this->language->get('text_required_pro_customization');
        $data['text_required_pro_customization_tooltip'] = $this->language->get('text_required_pro_customization_tooltip');
        $data['text_allow_image_upload'] = $this->language->get('text_allow_image_upload');
        $data['text_allow_image_upload_tooltip'] = $this->language->get('text_allow_image_upload_tooltip');
        $data['text_upload_image_size'] = $this->language->get('text_upload_image_size');
        $data['text_upload_image_size_tooltip'] = $this->language->get('text_upload_image_size_tooltip');
        $data['text_display_qr_code_tooltip'] = $this->language->get('text_display_qr_code_tooltip');
        $data['text_display_qr_code'] = $this->language->get('text_display_qr_code');
        $data['text_display_rotator_tooltip'] = $this->language->get('text_display_rotator_tooltip');
        $data['text_display_rotator'] = $this->language->get('text_display_rotator');
        $data['text_resize_text_tooltip'] = $this->language->get('text_resize_text_tooltip');
        $data['text_resize_text'] = $this->language->get('text_resize_text');
        $data['text_display_download_option_tooltip'] = $this->language->get('text_display_download_option_tooltip');
        $data['text_display_download_option'] = $this->language->get('text_display_download_option');
        $data['text_upload_image_price_tooltip'] = $this->language->get('text_upload_image_price_tooltip');
        $data['text_upload_image_price'] = $this->language->get('text_upload_image_price');
        $data['text_image_filters_tooltip'] = $this->language->get('text_image_filters_tooltip');
        $data['text_image_filters'] = $this->language->get('text_image_filters');
        $data['text_display_image_filters_tooltip'] = $this->language->get('text_display_image_filters_tooltip');
        $data['text_display_image_filters'] = $this->language->get('text_display_image_filters');
        $data['text_display_image_transparency_tooltip'] = $this->language->get('text_display_image_transparency_tooltip');
        $data['text_display_image_transparency'] = $this->language->get('text_display_image_transparency');
        $data['text_grayscale'] = $this->language->get('text_grayscale');
        $data['text_sepia'] = $this->language->get('text_sepia');
        $data['text_invert'] = $this->language->get('text_invert');
        $data['text_emboss'] = $this->language->get('text_emboss');
        $data['text_sharpen'] = $this->language->get('text_sharpen');
        $data['text_blur'] = $this->language->get('text_blur');

        $data['text_text_fixed_price'] = $this->language->get('text_text_fixed_price');
        $data['text_qrcode_price'] = $this->language->get('text_qrcode_price');
        $data['text_design_fixed_price'] = $this->language->get('text_design_fixed_price');
        $data['text_enable_cost_per_char'] = $this->language->get('text_enable_cost_per_char');
        $data['text_enable_cost_per_char_hint'] = $this->language->get('text_enable_cost_per_char_hint');

        $data['text_display_text_block'] = $this->language->get('text_display_text_block');
        $data['text_max_text_lenght'] = $this->language->get('text_max_text_lenght');
        $data['text_min_text_lenght'] = $this->language->get('text_min_text_lenght');
        $data['text_allow_text_transparency'] = $this->language->get('text_allow_text_transparency');
        $data['text_allow_text_curve'] = $this->language->get('text_allow_text_curve');

        $data['text_side_image'] = $this->language->get('text_side_image');
        $data['text_enable_side'] = $this->language->get('text_enable_side');
        $data['text_side_name'] = $this->language->get('text_side_name');
        $data['text_back'] = $this->language->get('text_back');
        $data['text_side2'] = $this->language->get('text_side2');
        $data['text_configure_groups'] = $this->language->get('text_configure_groups');
        $data['text_configure_colors'] = $this->language->get('text_configure_colors');
        $data['text_configure_fonts'] = $this->language->get('text_configure_fonts');
        $data['text_px'] = $this->language->get('text_px');
        // errors
        $data['error_image'] = $this->language->get('error_image');
        $data['error_empty'] = $this->language->get('error_empty');
        $data['error_name'] = $this->language->get('error_name');
        $data['error_length_invalid'] = $this->language->get('error_length_invalid');
        $data['error_length'] = $this->language->get('error_length');
        $data['error_price'] = $this->language->get('error_price');
        $data['error_image_size'] = $this->language->get('error_image_size');
        $data['error_side_select'] = $this->language->get('error_side_select');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();
        $language_id = $this->config->get('config_language_id');
        $data['language_id'] = $language_id;

        $data['configureFonts_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/configureFonts', $this->session_token_key . '=' . $this->session_token, true));
        $data['configureColors_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/configureColors', $this->session_token_key . '=' . $this->session_token, true));
        $data['configureGroups_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/configureGroups', $this->session_token_key . '=' . $this->session_token, true));
        $data['configureImages_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/configureImages', $this->session_token_key . '=' . $this->session_token, true));
        $product_data = $this->model_kbproduct_customizer_kbproduct_customizer->getProductSetting($product_id);


        if (isset($this->request->post['kbproduct']['config']['enable'])) {
            $data['kbproduct']['config']['enable'] = $this->request->post['kbproduct']['config']['enable'];
        } else if (isset($product_data['enable']) && $product_data['enable'] != '') {
            $data['kbproduct']['config']['enable'] = $product_data['enable'];
        } else {
            $data['kbproduct']['config']['enable'] = 0;
        }

        if (isset($this->request->post['kbproduct']['config']['required'])) {
            $data['kbproduct']['config']['required'] = $this->request->post['kbproduct']['config']['required'];
        } else if (isset($product_data['is_required']) && $product_data['is_required'] != '') {
            $data['kbproduct']['config']['required'] = $product_data['is_required'];
        } else {
            $data['kbproduct']['config']['required'] = 0;
        }

        if (isset($this->request->post['kbproduct']['config']['allow_image_upload'])) {
            $data['kbproduct']['config']['allow_image_upload'] = $this->request->post['kbproduct']['config']['allow_image_upload'];
        } else if (isset($product_data['allow_img_upload']) && $product_data['allow_img_upload'] != '') {
            $data['kbproduct']['config']['allow_image_upload'] = $product_data['allow_img_upload'];
        } else {
            $data['kbproduct']['config']['allow_image_upload'] = 1;
        }

        if (isset($this->request->post['kbproduct']['config']['image_size'])) {
            $data['kbproduct']['config']['image_size'] = $this->request->post['kbproduct']['config']['image_size'];
        } else if (isset($product_data['upload_image_size']) && $product_data['upload_image_size'] != '') {
            $data['kbproduct']['config']['image_size'] = $product_data['upload_image_size'];
        } else {
            $data['kbproduct']['config']['image_size'] = 35;
        }

        if (isset($this->request->post['kbproduct']['config']['image_price'])) {
            $data['kbproduct']['config']['image_price'] = $this->request->post['kbproduct']['config']['image_price'];
        } else if (isset($product_data['upload_image_price']) && $product_data['upload_image_price'] != '') {
            $data['kbproduct']['config']['image_price'] = $product_data['upload_image_price'];
        } else {
            $data['kbproduct']['config']['image_price'] = 0;
        }

        if (isset($this->request->post['kbproduct']['config']['display_download'])) {
            $data['kbproduct']['config']['display_download'] = $this->request->post['kbproduct']['config']['display_download'];
        } else if (isset($product_data['show_download_png']) && $product_data['show_download_png'] != '') {
            $data['kbproduct']['config']['display_download'] = $product_data['show_download_png'];
        } else {
            $data['kbproduct']['config']['display_download'] = 1;
        }

        if (isset($this->request->post['kbproduct']['config']['resize_text'])) {
            $data['kbproduct']['config']['resize_text'] = $this->request->post['kbproduct']['config']['resize_text'];
        } else if (isset($product_data['resize_text']) && $product_data['resize_text'] != '') {
            $data['kbproduct']['config']['resize_text'] = $product_data['resize_text'];
        } else {
            $data['kbproduct']['config']['resize_text'] = 1;
        }

        if (isset($this->request->post['kbproduct']['config']['display_rotator'])) {
            $data['kbproduct']['config']['display_rotator'] = $this->request->post['kbproduct']['config']['display_rotator'];
        } else if (isset($product_data['rotator_text']) && $product_data['rotator_text'] != '') {
            $data['kbproduct']['config']['display_rotator'] = $product_data['rotator_text'];
        } else {
            $data['kbproduct']['config']['display_rotator'] = 1;
        }

        if (isset($this->request->post['kbproduct']['config']['qr_code'])) {
            $data['kbproduct']['config']['qr_code'] = $this->request->post['kbproduct']['config']['qr_code'];
        } else if (isset($product_data['enable_qrcode']) && $product_data['enable_qrcode'] != '') {
            $data['kbproduct']['config']['qr_code'] = $product_data['enable_qrcode'];
        } else {
            $data['kbproduct']['config']['qr_code'] = 1;
        }

        if (isset($this->request->post['kbproduct']['config']['image_transparency'])) {
            $data['kbproduct']['config']['image_transparency'] = $this->request->post['kbproduct']['config']['image_transparency'];
        } else if (isset($product_data['image_transparency']) && $product_data['image_transparency'] != '') {
            $data['kbproduct']['config']['image_transparency'] = $product_data['image_transparency'];
        } else {
            $data['kbproduct']['config']['image_transparency'] = 1;
        }

        if (isset($this->request->post['kbproduct']['config']['enable_image_filters'])) {
            $data['kbproduct']['config']['enable_image_filters'] = $this->request->post['kbproduct']['config']['enable_image_filters'];
        } else if (isset($product_data['enable_image_filters']) && $product_data['enable_image_filters'] != '') {
            $data['kbproduct']['config']['enable_image_filters'] = $product_data['enable_image_filters'];
        } else {
            $data['kbproduct']['config']['enable_image_filters'] = 1;
        }
        if (isset($this->request->post['kbproduct']['config']['enable_image_filters'])) {
            if (isset($this->request->post['kbproduct']['config']['image_filters'])) {
                $data['kbproduct']['config']['image_filters'] = $this->request->post['kbproduct']['config']['image_filters'];
            } else {
                $data['kbproduct']['config']['image_filters'] = array();
            }
        } else if (isset($product_data['image_filters']) && !empty($product_data['image_filters'])) {
            $data['kbproduct']['config']['image_filters'] = json_decode($product_data['image_filters'], true);
        } else {
            $data['kbproduct']['config']['image_filters'] = ['0', '1', '2', '3', '4', '5'];
        }

        if (isset($this->request->post['kbproduct']['price']['text_price'])) {
            $data['kbproduct']['price']['text_price'] = $this->request->post['kbproduct']['price']['text_price'];
        } else if (isset($product_data['text_fixed_price']) && $product_data['text_fixed_price'] != '') {
            $data['kbproduct']['price']['text_price'] = $product_data['text_fixed_price'];
        } else {
            $data['kbproduct']['price']['text_price'] = 0;
        }

        if (isset($this->request->post['kbproduct']['price']['cost_per_char'])) {
            $data['kbproduct']['price']['cost_per_char'] = $this->request->post['kbproduct']['price']['cost_per_char'];
        } else if (isset($product_data['enable_cost_character']) && $product_data['enable_cost_character'] != '') {
            $data['kbproduct']['price']['cost_per_char'] = $product_data['enable_cost_character'];
        } else {
            $data['kbproduct']['price']['cost_per_char'] = 0;
        }

        if (isset($this->request->post['kbproduct']['price']['design_price'])) {
            $data['kbproduct']['price']['design_price'] = $this->request->post['kbproduct']['price']['design_price'];
        } else if (isset($product_data['design_fixed_price']) && $product_data['design_fixed_price'] != '') {
            $data['kbproduct']['price']['design_price'] = $product_data['design_fixed_price'];
        } else {
            $data['kbproduct']['price']['design_price'] = 0;
        }

        if (isset($this->request->post['kbproduct']['price']['qrcode_price'])) {
            $data['kbproduct']['price']['qrcode_price'] = $this->request->post['kbproduct']['price']['qrcode_price'];
        } else if (isset($product_data['qrcode_price']) && $product_data['qrcode_price'] != '') {
            $data['kbproduct']['price']['qrcode_price'] = $product_data['qrcode_price'];
        } else {
            $data['kbproduct']['price']['qrcode_price'] = 0;
        }

        if (isset($this->request->post['kbproduct']['text']['display_text_block'])) {
            $data['kbproduct']['text']['display_text_block'] = $this->request->post['kbproduct']['text']['display_text_block'];
        } else if (isset($product_data['display_text_block']) && $product_data['display_text_block'] != '') {
            $data['kbproduct']['text']['display_text_block'] = $product_data['display_text_block'];
        } else {
            $data['kbproduct']['text']['display_text_block'] = 1;
        }

        if (isset($this->request->post['kbproduct']['text']['max_len'])) {
            $data['kbproduct']['text']['max_len'] = $this->request->post['kbproduct']['text']['max_len'];
        } else if (isset($product_data['max_text_length']) && $product_data['max_text_length'] != '') {
            $data['kbproduct']['text']['max_len'] = $product_data['max_text_length'];
        } else {
            $data['kbproduct']['text']['max_len'] = 20;
        }

        if (isset($this->request->post['kbproduct']['text']['min_len'])) {
            $data['kbproduct']['text']['min_len'] = $this->request->post['kbproduct']['text']['min_len'];
        } else if (isset($product_data['min_text_length']) && $product_data['min_text_length'] != '') {
            $data['kbproduct']['text']['min_len'] = $product_data['min_text_length'];
        } else {
            $data['kbproduct']['text']['min_len'] = 1;
        }

        if (isset($this->request->post['kbproduct']['text']['allow_transparency'])) {
            $data['kbproduct']['text']['allow_transparency'] = $this->request->post['kbproduct']['text']['allow_transparency'];
        } else if (isset($product_data['allow_text_transparency']) && $product_data['allow_text_transparency'] != '') {
            $data['kbproduct']['text']['allow_transparency'] = $product_data['allow_text_transparency'];
        } else {
            $data['kbproduct']['text']['allow_transparency'] = 1;
        }

        if (isset($this->request->post['kbproduct']['text']['allow_curve'])) {
            $data['kbproduct']['text']['allow_curve'] = $this->request->post['kbproduct']['text']['allow_curve'];
        } else if (isset($product_data['allow_text_curve']) && $product_data['allow_text_curve'] != '') {
            $data['kbproduct']['text']['allow_curve'] = $product_data['allow_text_curve'];
        } else {
            $data['kbproduct']['text']['allow_curve'] = 1;
        }
        
        //BOC added for the Image Transparency Fixes by Shivam Bansal on 7-7-2021
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $data['text_transparency_status'] = $this->language->get('text_transparency_status');
        $data['help_transparency_status'] = $this->language->get('help_transparency_status');
        $data['text_transparent'] = $this->language->get('text_transparent');
        $data['text_non_transparent'] = $this->language->get('text_non_transparent');
        if (isset($this->request->post['kbproduct']['transparency_status'])) {
            $data['kbproduct']['transparency_status'] = $this->request->post['kbproduct']['transparency_status'];
        } else if (isset($product_data['transparency_status']) && $product_data['transparency_status'] != '') {
            $data['kbproduct']['transparency_status'] = $product_data['transparency_status'];
        } else {
            $data['kbproduct']['transparency_status'] = '0';
        }
        //EOC added for the Image Transparency Fixes by Shivam Bansal on 7-7-2021

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';

        if (isset($product_data['slice_data'])) {
            $sides_data = json_decode($product_data['slice_data'], true);
        } else {
            $sides_data = array();
        }

        $settings = $this->model_setting_setting->getSetting('kbproduct_customizer', $this->config->get('config_store_id'));

        $count = 1;
        foreach ($sides_data as $key => $side) {
            foreach ($side['side_name'] as $key2 => $value) {
                if ($value == '') {
                    $sides_data[$key]['side_name'][$key2] = $this->language->get('text_side') . ' ' . $count;
                }
            }
            $count++;
        }

        $total_side = count($sides_data);
        $total_side_allow = $settings['kbproduct_customizer']['max_sides'];
        $blank_sides = $total_side_allow > $total_side ? $total_side_allow - $total_side : 0;

        if ($blank_sides > 0) {
            for ($i = $total_side + 1; $i <= $total_side_allow; $i++) {
                $sides_data['side_' . $i]['enable_side'] = 0;
                $sides_data['side_' . $i]['side_image'] = '';
                foreach ($data['languages'] as $key => $value) {
                    $sides_data['side_' . $i]['side_name'][$value['language_id']] = $this->language->get('text_side') . ' ' . $i;
                }
            }
        }
        if (isset($this->request->post['kbproduct']['sides'])) {
            $data['kbproduct']['sides'] = $this->request->post['kbproduct']['sides'];
        } else if (isset($sides_data) && !empty($sides_data)) {
            $count = 0;
            foreach ($sides_data as $key => $value) {
                $data['kbproduct']['sides'][$key] = $value;
                $count++;
                if ($total_side_allow <= $count) {
                    break;
                }
            }
        } else {
            $data['kbproduct']['sides']['side_1']['enable_side'] = 0;
            $data['kbproduct']['sides']['side_1']['side_image'] = '';
            foreach ($data['languages'] as $key => $value) {
                $data['kbproduct']['sides']['side_1']['side_name'][$value['language_id']] = $this->language->get('text_back');
            }
        }

        return $this->load->view($this->module_path . '/kbproduct_customizer/product', $data);
    }

    public function configureImages() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }

        $product_id = 0;
        if (isset($this->request->get['product_id'])) {
            $product_id = $this->request->get['product_id'];
        }
        $data['product_id'] = $product_id;
        $group_id = 0;
        if (isset($this->request->get['id_group'])) {
            $group_id = $this->request->get['id_group'];
        }
        $data['group_id'] = $group_id;

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_kbproduct_customizer_kbproduct_customizer->setImage($this->request->post, $store_id);
            $this->session->data['success'] = $this->language->get('success');
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/setConfigureImages', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['cancel'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureGroups&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, true);

        $data['heading_title'] = $this->language->get('text_products');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_products'),
            'href' => $this->url->link('catalog/product&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_groups'),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/configureGroups&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $group = $this->model_kbproduct_customizer_kbproduct_customizer->getGroup($group_id, $store_id);

        $data['breadcrumbs'][] = array(
            'text' => html_entity_decode($group['name'][$this->config->get('config_language_id')]),
            'href' => $this->url->link($this->module_path . '/kbproduct_customizer/configureImages&product_id=' . $product_id . '&id_group=' . $group_id, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );


        $data['text_id'] = $this->language->get('text_id');
        $data['text_image_group'] = $this->language->get('text_image_group');
        $data['text_preview'] = $this->language->get('text_preview');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['button_view_images'] = $this->language->get('button_view_images');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');

        $data['error_field_empty'] = $this->language->get('error_field_empty');
        $data['error_url'] = $this->language->get('error_url');
        $data['required'] = $this->language->get('required');
        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['invalid_url'] = $this->language->get('invalid_url');
        $data['text_enable_disable_images'] = $this->language->get('text_enable_disable_images');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_images';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (!isset($this->request->get['page'])) {
            $page = 1;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        }

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $data['groups_data'] = array();
        $data['group_id'] = $group_id;

        $groups_data = $this->model_kbproduct_customizer_kbproduct_customizer->getImagesByGroup($group_id, $filter_data, $store_id);
        $disabled_images = $this->model_kbproduct_customizer_kbproduct_customizer->getDisabledImages($product_id, $store_id);
        $data['text_edit'] = $this->language->get('text_images');
        $group = $this->model_kbproduct_customizer_kbproduct_customizer->getGroup($group_id, $store_id);
        $data['text_edit'] = html_entity_decode($group['name'][$this->config->get('config_language_id')]);

        $count_max = 0;
        foreach ($groups_data as $key => $value) {
            if ($value['status'] == '1') {
                $count_max++;
                if (in_array($value['id_image'], $disabled_images)) {
                    $data['groups_data'][] = array(
                        'id' => $value['id_image'],
                        'preview' => HTTPS_CATALOG . 'image/' . $value['image'],
                        'status' => 0,
                    );
                } else {
                    $data['groups_data'][] = array(
                        'id' => $value['id_image'],
                        'preview' => HTTPS_CATALOG . 'image/' . $value['image'],
                        'status' => $value['status'],
                    );
                }
            }
        }
        $total_images = ($count_max);

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_id'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureImages', $this->session_token_key . '=' . $this->session_token . '&sort=id_image&store_id=' . $store_id . $url, true);
        $data['sort_status'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureImages', $this->session_token_key . '=' . $this->session_token . '&sort=status&store_id=' . $store_id . $url, true);


        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $total_images;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbproduct_customizer/configureImages', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}&store_id=' . $store_id, true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_images) ? (($page - 1) * $pagination->limit) + 1 : 0, ((($page - 1) * $pagination->limit) > ($total_images - $pagination->limit)) ? $total_images : ((($page - 1) * $pagination->limit) + $pagination->limit), $total_images, ceil($total_images / $pagination->limit));

        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;

        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/configureImages', $this->session_token_key . '=' . $this->session_token, true));

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configureImages.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configureImages', $data));
        }
    }

    public function configureGroups() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }
        $product_id = 0;
        if (isset($this->request->get['product_id'])) {
            $product_id = $this->request->get['product_id'];
        }
        $data['product_id'] = $product_id;
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_kbproduct_customizer_kbproduct_customizer->setImage($this->request->post, $store_id);
            $this->session->data['success'] = $this->language->get('success');
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/setConfigureGroups', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['cancel'] = $this->url->link('catalog/product/edit&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, true);
        $data['configureImages_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/configureImages', $this->session_token_key . '=' . $this->session_token, true));

        $data['heading_title'] = $this->language->get('text_products');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_products'),
            'href' => $this->url->link('catalog/product&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_groups'),
            'href' => $this->url->link($this->module_path . '/configureGroups&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_id'] = $this->language->get('text_id');
        $data['text_image_group'] = $this->language->get('text_image_group');
        $data['text_preview'] = $this->language->get('text_preview');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['button_view_images'] = $this->language->get('button_view_images');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_enable_disable_groups'] = $this->language->get('text_enable_disable_groups');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');

        $data['error_field_empty'] = $this->language->get('error_field_empty');
        $data['error_url'] = $this->language->get('error_url');
        $data['required'] = $this->language->get('required');
        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['invalid_url'] = $this->language->get('invalid_url');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_images';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (!isset($this->request->get['page'])) {
            $page = 1;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        }

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $data['groups_data'] = array();
        $groups_data = $this->model_kbproduct_customizer_kbproduct_customizer->getAllGroups($filter_data, $store_id);
        $disabled_groups = $this->model_kbproduct_customizer_kbproduct_customizer->getDisabledGroups($product_id, $store_id);
        $data['text_edit'] = $this->language->get('tab_images');
        $count_max = 0;
        foreach ($groups_data as $key => $value) {
            if ($value['status'] == '1') {
                $count_max++;
                if (in_array($value['id_groups'], $disabled_groups)) {
                    $data['groups_data'][] = array(
                        'id' => $value['id_groups'],
                        'name' => json_decode($value['name'], true)[$this->config->get('config_language_id')],
                        'preview' => HTTPS_CATALOG . 'image/' . $value['preview'],
                        'status' => 0,
                    );
                } else {
                    $data['groups_data'][] = array(
                        'id' => $value['id_groups'],
                        'name' => json_decode($value['name'], true)[$this->config->get('config_language_id')],
                        'preview' => HTTPS_CATALOG . 'image/' . $value['preview'],
                        'status' => $value['status'],
                    );
                }
            }
        }
        $total_images = ($count_max);

        $url = '';

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_id'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureGroups', $this->session_token_key . '=' . $this->session_token . '&sort=id_groups&store_id=' . $store_id . $url, true);
        $data['sort_status'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureGroups', $this->session_token_key . '=' . $this->session_token . '&sort=status&store_id=' . $store_id . $url, true);


        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $total_images;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbproduct_customizer/configureGroups', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}&store_id=' . $store_id, true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_images) ? (($page - 1) * $pagination->limit) + 1 : 0, ((($page - 1) * $pagination->limit) > ($total_images - $pagination->limit)) ? $total_images : ((($page - 1) * $pagination->limit) + $pagination->limit), $total_images, ceil($total_images / $pagination->limit));

        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/configureGroups', $this->session_token_key . '=' . $this->session_token, true));

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configureGroups.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configureGroups', $data));
        }
    }

    public function configureColors() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $product_id = 0;
        if (isset($this->request->get['product_id'])) {
            $product_id = $this->request->get['product_id'];
        }
        $data['product_id'] = $product_id;
        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            $this->model_kbproduct_customizer_kbproduct_customizer->setColor($this->request->post, $store_id);
            $this->session->data['success'] = $this->language->get('success');
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/setConfigureColors', $this->session_token_key . '=' . $this->session_token, true);
        $data['cancel'] = $this->url->link('catalog/product/edit&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, true);

        $data['text_edit'] = $this->language->get('tab_colors');
        $data['heading_title'] = $this->language->get('text_products');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_products'),
            'href' => $this->url->link('catalog/product&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_colors'),
            'href' => $this->url->link($this->module_path . '/configureColors&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_id'] = $this->language->get('text_id');
        $data['text_code'] = $this->language->get('text_code');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_enable_disable_colors'] = $this->language->get('text_enable_disable_colors');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_edit'] = $this->language->get('button_edit');
        $data['button_delete'] = $this->language->get('button_delete');
        $data['button_add'] = $this->language->get('button_add');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');

        $data['error_field_empty'] = $this->language->get('error_field_empty');
        $data['error_url'] = $this->language->get('error_url');
        $data['required'] = $this->language->get('required');
        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['invalid_url'] = $this->language->get('invalid_url');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();


        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        if (isset($this->request->post['reset'])) {
            $filter_status = null;
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_colors';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (!isset($this->request->get['page'])) {
            $page = 1;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        }

        $filter_data = array(
            'filter_status' => $filter_status,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $data['colors_data'] = array();
        $colors_data = $this->model_kbproduct_customizer_kbproduct_customizer->getAllColors($filter_data, $store_id);
        $disabled_colors = $this->model_kbproduct_customizer_kbproduct_customizer->getDisabledColors($product_id, $store_id);
        $data['colors_data'] = array();
        $count_max = 0;
        foreach ($colors_data as $key => $value) {
            if ($value['status'] == '1') {
                $count_max++;
                if (in_array($value['id_colors'], $disabled_colors)) {
                    $data['colors_data'][] = array(
                        'id' => $value['id_colors'],
                        'color' => $value['code'],
                        'status' => 0,
                    );
                } else {
                    $data['colors_data'][] = array(
                        'id' => $value['id_colors'],
                        'color' => $value['code'],
                        'status' => $value['status'],
                    );
                }
            }
        }

        $filter_data = array(
            'filter_status' => $filter_status,
        );
        $total_colors = $count_max;

        $url = '';

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_id'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureColors', $this->session_token_key . '=' . $this->session_token . '&sort=id_colors&store_id=' . $store_id . $url, true);
//        $data['sort_color'] = $this->url->link($this->module_path . '/kbproduct_customizer/fonts', $this->session_token_key . '=' . $this->session_token . '&sort=color&store_id=' . $store_id . $url, true);
        $data['sort_status'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureColors', $this->session_token_key . '=' . $this->session_token . '&sort=status&store_id=' . $store_id . $url, true);


        $url = '';

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $total_colors;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbproduct_customizer/configureColors', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}&store_id=' . $store_id, true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_colors) ? (($page - 1) * $pagination->limit) + 1 : 0, ((($page - 1) * $pagination->limit) > ($total_colors - $pagination->limit)) ? $total_colors : ((($page - 1) * $pagination->limit) + $pagination->limit), $total_colors, ceil($total_colors / $pagination->limit));

        $data['filter_status'] = $filter_status;
        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 3;
        $data['tabs'] = $this->load->controller($this->module_path . '/kbproduct_customizer/tabs', $tabs_data);
        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/configureColors', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true));
        $data['store_switcher'] = $this->load->controller($this->module_path . '/kbproduct_customizer/store_swticher', $data);

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configureColors.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configureColors', $data));
        }
    }

    public function configureFonts() {
        $this->load->language($this->module_path . '/kbproduct_customizer');
        $this->load->model('setting/setting');
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        $this->document->setTitle($this->language->get('heading_title_main'));

        $product_id = 0;
        if (isset($this->request->get['product_id'])) {
            $product_id = $this->request->get['product_id'];
        }
        $data['product_id'] = $product_id;
        $store_id = 0;
        if (isset($this->request->get['store_id'])) {
            $store_id = $this->request->get['store_id'];
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        $data['action'] = $this->url->link($this->module_path . '/kbproduct_customizer/setConfigureFonts', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true);
        $data['cancel'] = $this->url->link('catalog/product/edit&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, true);

        $data['text_edit'] = $this->language->get('tab_fonts');
        $data['heading_title'] = $this->language->get('text_products');
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_products'),
            'href' => $this->url->link('catalog/product&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_fonts'),
            'href' => $this->url->link($this->module_path . '/configureFonts&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token . '&store_id=' . $store_id, true)
        );

        $data['text_id'] = $this->language->get('text_id');
        $data['text_font'] = $this->language->get('text_font');
        $data['text_active'] = $this->language->get('text_active');
        $data['text_action'] = $this->language->get('text_action');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_default_store'] = $this->language->get('text_default_store');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_filter'] = $this->language->get('button_filter');
        $data['button_reset'] = $this->language->get('button_reset');


        $data['error_field_empty'] = $this->language->get('error_field_empty');
        $data['error_url'] = $this->language->get('error_url');
        $data['required'] = $this->language->get('required');
        $data['error_number_field'] = $this->language->get('error_number_field');
        $data['invalid_url'] = $this->language->get('invalid_url');
        $data['text_enable_disable_fonts'] = $this->language->get('text_enable_disable_fonts');

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        if (isset($this->request->get['filter_font'])) {
            $filter_font = $this->request->get['filter_font'];
        } else {
            $filter_font = null;
        }

        if (isset($this->request->get['filter_status'])) {
            $filter_status = $this->request->get['filter_status'];
        } else {
            $filter_status = null;
        }

        if (isset($this->request->post['reset'])) {
            $filter_font = null;
            $filter_status = null;
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'id_fonts';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'ASC';
        }
        if (!isset($this->request->get['page'])) {
            $page = 1;
        }
        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        }

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $fonts_data = $this->model_kbproduct_customizer_kbproduct_customizer->getAllFonts($filter_data, $store_id);
        $disabled_fonts = $this->model_kbproduct_customizer_kbproduct_customizer->getDisabledFonts($product_id, $store_id);
        $data['fonts_data'] = array();

        $count_max = 0;
        foreach ($fonts_data as $key => $value) {
            if ($value['status'] == '1') {
                $count_max++;
                if (isset($filter_status) && $filter_status == '1') {
                    if (in_array($value['id_fonts'], $disabled_fonts)) {
                        $count_max--;
                        continue;
                    }
                } else if (isset($filter_status) && $filter_status == '0') {
                    if (!in_array($value['id_fonts'], $disabled_fonts)) {
                        $count_max--;
                        continue;
                    }
                }
                if (in_array($value['id_fonts'], $disabled_fonts)) {
                    $data['fonts_data'][] = array(
                        'id' => $value['id_fonts'],
                        'font' => $value['font_title'],
                        'font_url' => $value['font_url'],
                        'status' => 0,
                    );
                } else {
                    $data['fonts_data'][] = array(
                        'id' => $value['id_fonts'],
                        'font' => $value['font_title'],
                        'font_url' => $value['font_url'],
                        'status' => $value['status'],
                    );
                }
            }
        }

        $filter_data = array(
            'filter_font' => $filter_font,
            'filter_status' => $filter_status,
        );
        $total_fonts = $count_max;

        $url = '';
        if (isset($this->request->get['filter_font'])) {
            $url .= '&filter_font=' . $this->request->get['filter_font'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_id'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureFonts', $this->session_token_key . '=' . $this->session_token . '&sort=id_fonts&store_id=' . $store_id . $url, true);
        $data['sort_font'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureFonts', $this->session_token_key . '=' . $this->session_token . '&sort=font_title&store_id=' . $store_id . $url, true);
        $data['sort_status'] = $this->url->link($this->module_path . '/kbproduct_customizer/configureFonts', $this->session_token_key . '=' . $this->session_token . '&sort=status&store_id=' . $store_id . $url, true);


        $url = '';
        if (isset($this->request->get['filter_font'])) {
            $url .= '&filter_font=' . $this->request->get['filter_font'];
        }

        if (isset($this->request->get['filter_status'])) {
            $url .= '&filter_status=' . $this->request->get['filter_status'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $total_fonts;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link($this->module_path . '/kbproduct_customizer/configureFonts', $this->session_token_key . '=' . $this->session_token . $url . '&page={page}&store_id=' . $store_id, true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total_fonts) ? (($page - 1) * $pagination->limit) + 1 : 0, ((($page - 1) * $pagination->limit) > ($total_fonts - $pagination->limit)) ? $total_fonts : ((($page - 1) * $pagination->limit) + $pagination->limit), $total_fonts, ceil($total_fonts / $pagination->limit));

        $data['filter_font'] = $filter_font;
        $data['filter_status'] = $filter_status;
        $data['sort'] = $sort;
        $data['order'] = strtolower($order);

        $data['image_dir_url'] = HTTPS_CATALOG . 'image/';
        $data['language_id'] = $this->config->get('config_language_id');
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $data['store_id'] = $store_id;
        $tabs_data['store_id'] = $store_id;
        $tabs_data['active'] = 2;
        $data['current_url'] = html_entity_decode($this->url->link($this->module_path . '/kbproduct_customizer/configureFonts', $this->session_token_key . '=' . $this->session_token, true));

        if (VERSION < 2.2) {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configureFonts.tpl', $data));
        } else {
            $this->response->setOutput($this->load->view($this->module_path . '/kbproduct_customizer/configureFonts', $data));
        }
    }

    public function setConfigureFonts() {
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        if (isset($this->request->get['product_id']) && isset($this->request->get['font_id'])) {

            $product_id = $this->request->get['product_id'];
            $font_id = $this->request->get['font_id'];
            $this->model_kbproduct_customizer_kbproduct_customizer->editConfigureFont($product_id, $font_id);
            $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/configureFonts&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, 'SSL'));
        }
    }

    public function setConfigureColors() {
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        if (isset($this->request->get['product_id']) && isset($this->request->get['color_id'])) {

            $product_id = $this->request->get['product_id'];
            $color_id = $this->request->get['color_id'];
            $this->model_kbproduct_customizer_kbproduct_customizer->editConfigureColor($product_id, $color_id);
            $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/configureColors&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, 'SSL'));
        }
    }

    public function setConfigureGroups() {
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        if (isset($this->request->get['product_id']) && isset($this->request->get['group_id'])) {

            $product_id = $this->request->get['product_id'];
            $group_id = $this->request->get['group_id'];
            $this->model_kbproduct_customizer_kbproduct_customizer->editConfigureGroup($product_id, $group_id);
            $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/configureGroups&product_id=' . $product_id, $this->session_token_key . '=' . $this->session_token, 'SSL'));
        }
    }

    public function setConfigureImages() {
        $this->load->model('kbproduct_customizer/kbproduct_customizer');
        if (isset($this->request->get['product_id']) && isset($this->request->get['image_id'])) {

            $group_id = $this->request->get['group_id'];
            $product_id = $this->request->get['product_id'];
            $image_id = $this->request->get['image_id'];
            $this->model_kbproduct_customizer_kbproduct_customizer->editConfigureImage($product_id, $image_id);
            $this->response->redirect($this->url->link($this->module_path . '/kbproduct_customizer/configureImages&product_id=' . $product_id . '&id_group=' . $group_id, $this->session_token_key . '=' . $this->session_token, 'SSL'));
        }
    }

}
