<?php

class ModelKbproductCustomizerKbproductCustomizer extends Model {

    public function getAllFonts($data = array(), $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_fonts where store_id = '" . (int) $store_id . "'";

        if (isset($data['filter_font']) && !is_null($data['filter_font'])) {
            $sql .= " AND font_title LIKE '%" . trim($data['filter_font']) . "%'";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND status = '" . (int) $data['filter_status'] . "'";
        }

        $sort_data = array(
            'id_fonts',
            'font_title',
            'status',
        );
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY id_fonts";
        }
        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int) $data['start'] . "," . (int) $data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getFonts($id, $store_id = 0) {
        $sql = "SELECT id_font FROM `" . DB_PREFIX . "velsof_product_customizer_fonts_product` WHERE id_product='" . (int) $id . "'";
        $query = $this->db->query($sql)->rows;
        $ids = array();
        foreach ($query as $key => $value) {
            $ids[] = $value['id_font'];
        }
        $sql = "SELECT Distinct * FROM " . DB_PREFIX . "velsof_product_customizer_fonts WHERE status = 1";
        $query = $this->db->query($sql);
        $data = array();
        foreach ($query->rows as $key => $value) {
            if (!in_array($value['id_fonts'], $ids)) {
                $data[] = array(
                    'id_fonts' => $value['id_fonts'],
                    'font_title' => $value['font_title'],
                    'font_url' => $value['font_url'],
                );
            }
        }
        return $data;
    }

    public function getColors($id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_colors as cc
                    LEFT JOIN `" . DB_PREFIX . "velsof_product_customizer_colors_product` as cp 
                    ON cc.id_colors = cp.id_color
                    WHERE cp.id_color is null
                    AND cc.status = 1";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAllColors($store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_colors where store_id = '" . (int) $store_id . "'";
        $sql .= " AND status = '1'";
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getDisabledColors($product_id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_colors_product where id_product='" . (int) $product_id . "'";
        $query = $this->db->query($sql)->rows;
        $data = array();
        foreach ($query as $key => $value) {
            $data[] = $value['id_color'];
        }
        return $data;
    }

    public function getGroups($id, $store_id = 0) {
        $sql = "SELECT id_group FROM `" . DB_PREFIX . "velsof_product_customizer_groups_product` WHERE id_product='" . (int) $id . "'";
        $query = $this->db->query($sql)->rows;
        $ids = array();
        foreach ($query as $key => $value) {
            $ids[] = $value['id_group'];
        }
        $sql = "SELECT Distinct * FROM " . DB_PREFIX . "velsof_product_customizer_image_groups WHERE status = 1";
        $query = $this->db->query($sql);
        $data = array();
        foreach ($query->rows as $key => $value) {
            if (!in_array($value['id_groups'], $ids)) {
                $check_image = $this->db->query("SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_images WHERE status = 1 AND id_group='" . (int) $value['id_groups'] . "'");
                if ($check_image->num_rows) {
                    $data[] = array(
                        'id_groups' => $value['id_groups'],
                        'name' => json_decode($value['name'], true),
                        'preview' => $value['preview'],
                        'date_add' => $value['date_add'],
                    );
                }
            }
        }
        return $data;
    }

    //Changes made in the below function by SHivam Bansal for solving the images issue.
    public function getImages($groups, $id, $store_id = 0) {
        $sql = "SELECT id_image FROM `" . DB_PREFIX . "velsof_product_customizer_image_product` WHERE id_product='" . (int) $id . "'";
        $query = $this->db->query($sql)->rows;
        $ids = array();
        foreach ($query as $key => $value) {
            $ids[] = $value['id_image'];
        }
        $data = array();
        if(!empty($groups)) {
            foreach($groups as $group) {
                $sql = "SELECT Distinct * FROM " . DB_PREFIX . "velsof_product_customizer_images WHERE status = 1 AND id_group = " . $group['id_groups'];
                $query = $this->db->query($sql);
                foreach ($query->rows as $key => $value) {
                    if (!in_array($value['id_image'], $ids)) {
                        $data[] = array(
                            'id_image' => $value['id_image'],
                            'id_group' => $value['id_group'],
                            'price' => $value['price'],
                            'image' => $value['image'],
                            'status' => $value['status'],
                            'date_add' => $value['date_add'],
                        );
                    }
                }
            }
        }
        return $data;
    }

    public function getProductSetting($product_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_product_setting where id_product='" . (int) $product_id . "'";
        $query = $this->db->query($sql)->row;
        return $query;
    }

    public function addDesign($data) {
        $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_design_output SET design_src='" . $this->db->escape(json_encode($data['design_src'])) . "', design_data='" . $this->db->escape(json_encode($data['design_data'])) . "', object_data='" . $this->db->escape(json_encode($data['object_data'])) . "', layer_count='" . (int) $data['canvas_layer_count'] . "'";
        $query = $this->db->query($sql);
        $design_id = $this->db->getLastId();

        $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_cart_design SET price='" . (float) $data['customization_cost'] . "', cart_id='" . (int) $data['cart_id'] . "', product_id='" . (int) $data['id_product'] . "', product_attribute_id='" . (int) $data['id_product_attribute'] . "', design_id='" . (int) $design_id . "'";
        $query = $this->db->query($sql);
        return $design_id;
    }

    public function getCartId($product_id) {
        $query = $this->db->query("SELECT cart_id FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int) $this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int) $this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int) $product_id . "'");
        return $query->row['cart_id'];
    }

    public function checkForNewCart($product_id) {
        $cart_id = $this->getCartId($product_id);
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_cart_design WHERE cart_id = '" . (int) $cart_id . "'");
        return $query->num_rows;
    }

    public function getCart($cart_id, $quantity) {
        $query = $this->db->query("SELECT do.*, cd.price FROM " . DB_PREFIX . "velsof_product_customizer_design_output as do, " . DB_PREFIX . "velsof_product_customizer_cart_design as cd  WHERE do.id_design = cd.design_id AND cd.cart_id= '" . (int) $cart_id . "'");
        if (isset($query->row['object_data'])) {
            $object_data = json_decode(json_decode($query->row['object_data']), true);
            $design_data = json_decode($query->row['design_data'], true);

            $data['design_id'] = $query->row['id_design'];
            $data['cost'] = $query->row['price'];
            foreach ($design_data as $key => $design) {
                $data['products'][$key]['thumb'] = $this->model_tool_image->resize($design['img_src'], 100, 100);
                $data['products'][$key]['popup'] = $this->model_tool_image->resize($design['img_src'], 600, 600);
                if (isset($design['name'])) {
                    $data['products'][$key]['name'] = $design['name'];
                } else {
                    $data['products'][$key]['name'] = '';
                }
//                var_dump($design);die;
                if (isset($object_data[$key])) {
                    foreach ($object_data[$key] as $key2 => $value) {
                        $data['products'][$key]['object'][] = $value['object_src'];
//                        $data['products'][$key]['name'][] = $value['name'];
                    }
                }
            }

            $data['kbproduct_cost'] = $quantity * $data['cost'];
            $data['cost'] = $quantity * $data['cost'];
            $data['cost'] = $this->currency->format($data['cost'], $this->session->data['currency']);

            return $data;
        } else {
            return array();
        }
    }

    public function getOrder($design_id, $quantity) {
        $query = $this->db->query("SELECT do.*, cd.price FROM " . DB_PREFIX . "velsof_product_customizer_design_output as do, " . DB_PREFIX . "velsof_product_customizer_cart_design as cd  WHERE do.id_design = cd.design_id AND do.id_design= '" . (int) $design_id . "'");
        if (isset($query->row['object_data'])) {
            $object_data = json_decode(json_decode($query->row['object_data']), true);
            $design_data = json_decode($query->row['design_data'], true);

//                var_dump($design);die; 
            $data['design_id'] = $query->row['id_design'];
            $data['cost'] = $query->row['price'];
            foreach ($design_data as $key => $design) {
                $data['products'][$key]['thumb'] = $this->model_tool_image->resize($design['img_src'], 100, 100);
                $data['products'][$key]['popup'] = $this->model_tool_image->resize($design['img_src'], 600, 600);
                $data['products'][$key]['popup_detail_thumb'] = $this->model_tool_image->resize($design['img_src'], 300, 300);
                if (isset($object_data[$key])) {
                    foreach ($object_data[$key] as $key2 => $value) {
                        $data['products'][$key]['object'][] = $value['object_src'];
                    }
                }
            }

            $data['kbproduct_cost'] = $quantity * $data['cost'];
            $data['cost'] = $quantity * $data['cost'];
            $data['cost'] = $this->currency->format($data['cost'], $this->session->data['currency']);

            return $data;
        } else {
            return array();
        }
    }

    public function add($product_id, $quantity = 1, $option = array(), $recurring_id = 0, $kbdesign_id) {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int) $this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int) $this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int) $product_id . "' AND recurring_id = '" . (int) $recurring_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "'");

        $this->db->query("INSERT " . DB_PREFIX . "cart SET kbdesign_id='" . (int) $kbdesign_id . "', api_id = '" . (isset($this->session->data['api_id']) ? (int) $this->session->data['api_id'] : 0) . "', customer_id = '" . (int) $this->customer->getId() . "', session_id = '" . $this->db->escape($this->session->getId()) . "', product_id = '" . (int) $product_id . "', recurring_id = '" . (int) $recurring_id . "', `option` = '" . $this->db->escape(json_encode($option)) . "', quantity = '" . (int) $quantity . "', date_added = NOW()");
        $cart_id = $this->db->getLastId();
        $this->db->query("UPDATE " . DB_PREFIX . "velsof_product_customizer_cart_design SET cart_id = " . (int) $cart_id . " WHERE design_id = '" . (int) $kbdesign_id . "'");

        $total_cart = 0;
        $sql = "SELECT product_id, quantity FROM " . DB_PREFIX . "cart";
        $query = $this->db->query($sql);
        foreach ($query->rows as $row) {
            $ids[] = $row['product_id'];
            $id = $row['product_id'];
            $sqlCart = "SELECT price FROM " . DB_PREFIX . "product WHERE product_id=" . (int) $id;
            $query2 = $this->db->query($sqlCart);
            $total_cart += $query2->row['price'] * $row['quantity'];
        }
    }

    public function downloadPdf() {
//        $pdf - new 
    }

}

?>
