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
            'date_add',
            'status'
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

    public function getAllConfiguredFonts($data = array(), $product_id, $store_id = 0) {
        $sql = "SELECT cf.* FROM " . DB_PREFIX . "velsof_product_customizer_fonts as cf LEFT JOIN "
                . DB_PREFIX . "velsof_product_customizer_fonts_product as fp ON cf.id_fonts = fp.id_font"
                . " WHERE fp.id_product='" . (int) $product_id . "' AND cf.store_id = '" . (int) $store_id . "' AND fp.store_id = '" . (int) $store_id . "'"
                . " AND cf.status='1'";
        if (isset($data['filter_font']) && !is_null($data['filter_font'])) {
            $sql .= " AND cf.font_title LIKE '%" . trim($data['filter_font']) . "%'";
        }

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            if ($data['filter_status'] == '1') {
                $sql .= " AND fp.id_font IS null";
            } else if ($data['filter_status'] == '0') {
                $sql .= " AND fp.id_font IS NOT null";
            }
        }

        $sort_data = array(
            'id_fonts',
            'font_title',
            'date_add',
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
        var_dump($sql);
        die;
        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function getAllFontsArray($font_id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_fonts where store_id = '" . (int) $store_id . "' AND id_fonts!='" . (int) $font_id . "'";
        $query = $this->db->query($sql)->rows;
        $data = array();
        foreach ($query as $key => $value) {
            $data['title'][] = $value['font_title'];
            $data['url'][] = $value['font_url'];
        }
        return $data;
    }

    public function getFont($id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_fonts where store_id = '" . (int) $store_id . "' AND id_fonts='" . (int) $id . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function setFont($data, $store_id = 0) {
        if ($data['id_fonts'] == '0') {
            $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_fonts SET font_title='" . $data['title'] . "', font_url='" . $this->db->escape($data['font_url']) . "',status='" . (int) $data['enable'] . "', store_id = '" . (int) $store_id . "'";
            $query = $this->db->query($sql);
        } else {
            $sql = "UPDATE " . DB_PREFIX . "velsof_product_customizer_fonts SET font_title='" . $data['title'] . "', font_url='" . $this->db->escape($data['font_url']) . "',status='" . (int) $data['enable'] . "' where store_id = '" . (int) $store_id . "' AND id_fonts='" . (int) $data['id_fonts'] . "'";
            $query = $this->db->query($sql);
        }
        return $query->row;
    }

    public function deleteFont($id, $store_id = 0) {

        $sql = "DELETE FROM " . DB_PREFIX . "velsof_product_customizer_fonts where store_id = '" . (int) $store_id . "' AND id_fonts='" . (int) $id . "'";
        $query = $this->db->query($sql);
        return $query;
    }

    public function getAllColors($data = array(), $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_colors where store_id = '" . (int) $store_id . "'";


        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND status = '" . (int) $data['filter_status'] . "'";
        }

        $sort_data = array(
            'id_colors',
            'date_add',
            'status',
        );
        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY code";
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

    public function getAllColorsArray($color_id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_colors where store_id = '" . (int) $store_id . "' AND id_colors != '" . (int) $color_id . "'";
        $query = $this->db->query($sql)->rows;
        $data = array();
        foreach ($query as $key => $value) {
            $data[] = $value['code'];
        }
        return $data;
    }

    public function getColor($id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_colors where store_id = '" . (int) $store_id . "' AND id_colors='" . (int) $id . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function setColor($data, $store_id = 0) {
        if ($data['id_colors'] == '0') {
            $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_colors SET code='" . $data['color'] . "',status='" . (int) $data['enable'] . "', store_id = '" . (int) $store_id . "'";
            $query = $this->db->query($sql);
        } else {
            $sql = "UPDATE " . DB_PREFIX . "velsof_product_customizer_colors SET code='" . $data['color'] . "',status='" . (int) $data['enable'] . "' where store_id = '" . (int) $store_id . "' AND id_colors='" . (int) $data['id_colors'] . "'";
            $query = $this->db->query($sql);
        }
        return $query->row;
    }

    public function deleteColor($id, $store_id = 0) {
        $sql = "DELETE FROM " . DB_PREFIX . "velsof_product_customizer_colors where store_id = '" . (int) $store_id . "' AND id_colors='" . (int) $id . "'";
        $query = $this->db->query($sql);
        return $query;
    }

    public function getAllGroups($data = array(), $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_image_groups where store_id = '" . (int) $store_id . "'";

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND status = '" . (int) $data['filter_status'] . "'";
        }

        if (isset($data['filter_image_group']) && !is_null($data['filter_image_group'])) {
            $sql .= " AND name LIKE '%" . trim($data['filter_image_group']) . "%'";
        }

        $sort_data = array(
            'id_groups',
            'name',
            'date_add',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY id_groups";
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

    public function getImagesByGroup($group_id, $data = array(), $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_images where store_id = '" . (int) $store_id . "' AND id_group='" . (int) $group_id . "'";

        if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
            $sql .= " AND status = '" . (int) $data['filter_status'] . "'";
        }

        $sort_data = array(
            'id_image',
            'date_add',
            'status'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY id_image";
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

    public function getImage($id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_images where store_id = '" . (int) $store_id . "' AND id_image='" . (int) $id . "'";
        $query = $this->db->query($sql);
        return $query->row;
    }

    public function setImage($data, $store_id = 0) {
        if (isset($data['image_id']) && $data['image_id'] != '0') {
            $sql = "UPDATE " . DB_PREFIX . "velsof_product_customizer_images SET price ='" . (float) $data['price'] . "', image ='" . $data['image'] . "', status ='" . (int) $data['enable'] . "' where store_id = '" . (int) $store_id . "' AND id_image='" . (int) $data['image_id'] . "' AND id_group='" . (int) $data['group_id'] . "'";
            $query = $this->db->query($sql);
            return $query;
        } else {
            $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_images SET price ='" . (float) $data['price'] . "', image ='" . $data['image'] . "', status ='" . (int) $data['enable'] . "', store_id = '" . (int) $store_id . "', id_image='" . (int) $data['image_id'] . "', id_group='" . (int) $data['group_id'] . "'";
            $query = $this->db->query($sql);
            return $query;
        }
    }

    public function deleteImage($id, $id_group, $store_id = 0) {
        $sql = "DELETE FROM " . DB_PREFIX . "velsof_product_customizer_images where store_id = '" . (int) $store_id . "' AND id_group='" . (int) $id_group . "' AND id_image='" . (int) $id . "'";
        $query = $this->db->query($sql);
        return $query;
    }

    public function getGroup($id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_image_groups where store_id = '" . (int) $store_id . "' AND id_groups='" . (int) $id . "'";
        $query = $this->db->query($sql)->rows;
        $data = array();

        foreach ($query as $key => $value) {
            $data['name'] = json_decode($value['name'], true);
            $data['image'] = $value['preview'];
            $data['status'] = $value['status'];
        }
        return $data;
    }

    public function setGroup($data, $store_id = 0) {
        if (isset($data['group_id']) && $data['group_id'] != '0') {
            $sql = "UPDATE " . DB_PREFIX . "velsof_product_customizer_image_groups SET name ='" . $this->db->escape(json_encode($data['name'])) . "', preview ='" . $data['image'] . "', status ='" . (int) $data['enable'] . "' where store_id = '" . (int) $store_id . "' AND id_groups='" . (int) $data['group_id'] . "'";
            $query = $this->db->query($sql);
            return $query;
        } else {
            $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_image_groups SET name ='" . $this->db->escape(json_encode($data['name'])) . "', preview ='" . $data['image'] . "', status ='" . (int) $data['enable'] . "', store_id = '" . (int) $store_id . "'";
            $query = $this->db->query($sql);
            return $query;
        }
    }

    public function deleteGroup($id, $store_id = 0) {
        $sql = "DELETE FROM " . DB_PREFIX . "velsof_product_customizer_image_groups where store_id = '" . (int) $store_id . "' AND id_groups='" . (int) $id . "'";
        $query = $this->db->query($sql);
        return $query;
    }

    public function getProductSetting($product_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_product_setting where id_product='" . (int) $product_id . "'";
        $query = $this->db->query($sql)->row;
        return $query;
    }

    public function editConfigureFont($product_id, $font_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_fonts_product where id_product='" . (int) $product_id . "' AND id_font='" . (int) $font_id . "'";
        $query = $this->db->query($sql)->row;
        if (empty($query)) {
            $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_fonts_product SET"
                    . " id_product='" . (int) $product_id . "',"
                    . " id_font='" . (int) $font_id . "',"
                    . " status ='0'";
            $query = $this->db->query($sql);
        } else {
            $sql = "DELETE FROM " . DB_PREFIX . "velsof_product_customizer_fonts_product"
                    . " WHERE id_product='" . (int) $product_id . "'"
                    . " AND id_font='" . (int) $font_id . "'";
            $query = $this->db->query($sql);
        }
        return $query;
    }

    public function editConfigureColor($product_id, $color_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_colors_product where id_product='" . (int) $product_id . "' AND id_color='" . (int) $color_id . "'";
        $query = $this->db->query($sql)->row;
        if (empty($query)) {
            $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_colors_product SET"
                    . " id_product='" . (int) $product_id . "',"
                    . " id_color='" . (int) $color_id . "',"
                    . " status ='0'";
            $query = $this->db->query($sql);
        } else {
            $sql = "DELETE FROM " . DB_PREFIX . "velsof_product_customizer_colors_product"
                    . " WHERE id_product='" . (int) $product_id . "'"
                    . " AND id_color='" . (int) $color_id . "'";
            $query = $this->db->query($sql);
        }
        return $query;
    }

    public function editConfigureGroup($product_id, $group_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_groups_product where id_product='" . (int) $product_id . "' AND id_group='" . (int) $group_id . "'";
        $query = $this->db->query($sql)->row;
        if (empty($query)) {
            $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_groups_product SET"
                    . " id_product='" . (int) $product_id . "',"
                    . " id_group='" . (int) $group_id . "',"
                    . " status ='0'";
            $query = $this->db->query($sql);
        } else {
            $sql = "DELETE FROM " . DB_PREFIX . "velsof_product_customizer_groups_product"
                    . " WHERE id_product='" . (int) $product_id . "'"
                    . " AND id_group='" . (int) $group_id . "'";
            $query = $this->db->query($sql);
        }
        return $query;
    }

    public function editConfigureImage($product_id, $image_id) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_image_product where id_product='" . (int) $product_id . "' AND id_image='" . (int) $image_id . "'";
        $query = $this->db->query($sql)->row;
        if (empty($query)) {
            $sql = "INSERT INTO " . DB_PREFIX . "velsof_product_customizer_image_product SET"
                    . " id_product='" . (int) $product_id . "',"
                    . " id_image='" . (int) $image_id . "',"
                    . " status ='0'";
            $query = $this->db->query($sql);
        } else {
            $sql = "DELETE FROM " . DB_PREFIX . "velsof_product_customizer_image_product"
                    . " WHERE id_product='" . (int) $product_id . "'"
                    . " AND id_image='" . (int) $image_id . "'";
            $query = $this->db->query($sql);
        }
        return $query;
    }

    public function getDisabledFonts($product_id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_fonts_product where id_product='" . (int) $product_id . "'";
        $query = $this->db->query($sql)->rows;
        $data = array();
        foreach ($query as $key => $value) {
            $data[] = $value['id_font'];
        }
        return $data;
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

    public function getDisabledGroups($product_id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_groups_product where id_product='" . (int) $product_id . "'";
        $query = $this->db->query($sql)->rows;
        $data = array();
        foreach ($query as $key => $value) {
            $data[] = $value['id_group'];
        }
        return $data;
    }

    public function getDisabledImages($product_id, $store_id = 0) {
        $sql = "SELECT * FROM " . DB_PREFIX . "velsof_product_customizer_image_product where id_product='" . (int) $product_id . "'";
        $query = $this->db->query($sql)->rows;
        $data = array();
        foreach ($query as $key => $value) {
            $data[] = $value['id_image'];
        }
        return $data;
    }

    public function getOrder($design_id, $quantity) {
        $query = $this->db->query("SELECT do.*, cd.price FROM " . DB_PREFIX . "velsof_product_customizer_design_output as do, " . DB_PREFIX . "velsof_product_customizer_cart_design as cd  WHERE do.id_design = cd.design_id AND do.id_design= '" . (int) $design_id . "'");
        if (isset($query->row['object_data'])) {
            $object_data = json_decode(json_decode($query->row['object_data']), true);
            $design_data = json_decode($query->row['design_data'], true);

            $data['design_id'] = $query->row['id_design'];
            $data['cost'] = $query->row['price'];
            foreach ($design_data as $key => $design) {

                $data['products'][$key]['thumb'] = $this->model_tool_image->resize($design['img_src'], 100, 100);
                $data['products'][$key]['popup'] = $this->model_tool_image->resize($design['img_src'], 600, 600);
                $data['products'][$key]['popup_detail_thumb'] = $this->model_tool_image->resize($design['img_src'], 300, 300);
                $data['products'][$key]['originX'] = $design['originX'];
                $data['products'][$key]['originY'] = $design['originY'];
                $data['products'][$key]['width'] = $design['width'];
                $data['products'][$key]['height'] = $design['height'];
                $data['products'][$key]['backgroundColor'] = $design['backgroundColor'];
                if (isset($object_data[$key])) {
                    foreach ($object_data[$key] as $key2 => $value) {
                        $data['products'][$key]['object'][$key2]['id'] = str_replace(' ', '-', $value['id']);
                        $data['products'][$key]['object'][$key2]['thumb'] = $this->model_tool_image->resize($value['object_src'], 300, 300);
                        $data['products'][$key]['object'][$key2]['top'] = $value['object']['top'];
                        $data['products'][$key]['object'][$key2]['left'] = $value['object']['left'];
                        $data['products'][$key]['object'][$key2]['angle'] = $value['object']['angle'];
                        $data['products'][$key]['object'][$key2]['opacity'] = $value['object']['opacity'];
                        if (!isset($value['object']['src']) || strpos($value['object']['src'], 'data:image/png;base64,') !== false) {
                            $data['products'][$key]['object'][$key2]['original_img'] = '';
                        } else {
                            $data['products'][$key]['object'][$key2]['original_img'] = $value['object']['src'];
                        }
                        $data['products'][$key]['object'][$key2]['filters'] = '';
                        if (isset($value['object']['filters'])) {
                            foreach ($value['object']['filters'] as $filter) {
                                if ($filter != null) {
                                    $data['products'][$key]['object'][$key2]['filters'] .= $filter['type'] . ',';
                                }
                            }
                        }
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

}

?>
