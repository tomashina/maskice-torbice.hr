<?php
class ModelExtensionCiproductAccessory extends Model {

	public function __construct($registery) {
		parent::__construct($registery);

		if(VERSION <= '2.3.0.2') {
			$this->load->model('extension/event');
			$this->event_model = 'model_extension_event';
		} else {
			$this->load->model('setting/event');
			$this->event_model = 'model_setting_event';
		}

		$this->load->model('user/user_group');
	}

	public function getProductAccessories($product_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_accessory WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

		return $query->rows;
	}

	public function createTables() {
		$query = $this->db->query("SHOW TABLES LIKE '". DB_PREFIX ."product_accessory'");
		if(!$query->num_rows) {
			$this->db->query("CREATE TABLE IF NOT EXISTS `". DB_PREFIX ."product_accessory` (`product_id` int(11) NOT NULL, `accessory_id` int(11) NOT NULL, `status` tinyint(4) NOT NULL, `sort_order` int(11) NOT NULL, PRIMARY KEY (`product_id`,`accessory_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
		}
	}

	public function cratePermissions($file_routes) {
		$user_group_id = $this->user->getGroupId();

		foreach ($file_routes as $route) {
			$this->model_user_user_group->removePermission($user_group_id, 'access', $route);
			$this->model_user_user_group->removePermission($user_group_id, 'modify', $route);

			$this->model_user_user_group->addPermission($user_group_id, 'access', $route);
			$this->model_user_user_group->addPermission($user_group_id, 'modify', $route);
		}
	}

	public function createEvents($data) {
		foreach ($data['events'] as $folder => $folder_info) {
			foreach ($folder_info as $event) {
				$this->{$this->event_model}->addEvent($data['code'] .'_'. $folder, $event['trigger'], $event['action'], $data['status'], $data['sort_order']);
			}
		}

	}

	public function enableEvents($code) {
		$query = $this->db->query("UPDATE `" . DB_PREFIX . "event` SET status = 1 WHERE `code` = '" . $this->db->escape($code) . "'");
	}

	public function syncEvents($data) {
		/* Create Missing Events Into Database */
		$found_missing_events = [];
		foreach ($data['events'] as $folder => $folder_info) {
			foreach ($folder_info as $event) {
				$filter_data = [
					'code'		=> $data['code'] .'_'. $folder,
					'trigger'	=> $event['trigger'],
					'action'	=> $event['action'],
				];

				$existing_event = $this->getEventByCode($filter_data);
				if(!$existing_event) {
					$found_missing_events[$folder][] = $event;
				}
			}
		}

		if($found_missing_events) {
			$add_data = [
				'events'		=> $found_missing_events,
				'code'			=> $data['code'],
				'description'	=> $data['description'],
				'status'		=> $data['status'],
				'sort_order'	=> $data['sort_order'],
			];
			$this->createEvents($add_data);
		}
		/* Create Missing Events Ends */

		/* Remove Extra Events from Database Starts */
		$file_string = [];
		$codes = [];
		foreach ($data['events'] as $folder => $folder_info) {
			foreach ($folder_info as $event) {
				$file_string[] = $event['trigger'] .':'. $event['action'];
			}

			$codes[] = $data['code'] .'_'. $folder;
		}

		$filter_data = [
			'codes'		=> $codes,
		];

		$db_events = $this->getEventsByCode($filter_data);

		foreach($db_events as $db_event) {
			if(!in_array($db_event['trigger'] .':'. $db_event['action'], $file_string)) {
				$this->{$this->event_model}->deleteEvent($db_event['event_id']);
			}
		}
		/* Remove Extra Events from Database Ends */

		/* Remove Duplicate Events from Database Starts */
		$filter_data = [
			'codes'		=> $codes,
		];
		$duplicates = $this->getDuplicateEvents($filter_data);
		foreach ($duplicates as $duplicate) {
			$this->{$this->event_model}->deleteEvent($duplicate['event_id']);
		}
		/* Remove Duplicate Events from Database Ends */
	}

	public function removeEvents($data) {
		foreach ($data['events'] as $folder => $folder_info) {
			$this->deleteEvent($data['code'] .'_'. $folder);
		}
	}

	public function deleteEvent($code) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($code) . "'");
	}

	public function getEventByCode($data) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($data['code']) . "'";

		if(!empty($data['trigger'])) {
			$sql .= " AND `trigger` = '" . $this->db->escape($data['trigger']) . "'";
		}

		if(!empty($data['action'])) {
			$sql .= " AND `action` = '" . $this->db->escape($data['action']) . "'";
		}

		$sql .= " ORDER BY event_id ASC";

		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getEventsByCode($data) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "event` WHERE event_id > 0";

		if(!empty($data['codes'])) {
			$implode = array();
			$sql .= " AND (";
			foreach ($data['codes'] as $code) {
				$implode[] = "`code` = '" . $this->db->escape($code) . "'";
			}

			if ($implode) {
				$sql .= " " . implode(" OR ", $implode) . "";
			}

			$sql .= ")";
		} else {
			$sql .= " AND `code` = '" . $this->db->escape($data['code']) . "'";
		}

		if(!empty($data['trigger'])) {
			$sql .= " AND `trigger` = '" . $this->db->escape($data['trigger']) . "'";
		}

		if(!empty($data['action'])) {
			$sql .= " AND `action` = '" . $this->db->escape($data['action']) . "'";
		}

		$sql .= " ORDER BY event_id ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getEventsByShortCode($code) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "event` WHERE `code` LIKE '" . $this->db->escape($code) . "%'";

		$sql .= " ORDER BY event_id ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getDuplicateEvents($data) {
		$sql = "SELECT event_id, `trigger`, COUNT(`trigger`), `action`, COUNT(`action`) FROM `" . DB_PREFIX . "event` WHERE event_id > 0";

      	if(!empty($data['codes'])) {
			$implode = array();
			$sql .= " AND (";
			foreach ($data['codes'] as $code) {
				$implode[] = "`code` = '" . $this->db->escape($code) . "'";
			}

			if ($implode) {
				$sql .= " " . implode(" OR ", $implode) . "";
			}

			$sql .= ")";
		} else {
			$sql .= " AND `code` = '" . $this->db->escape($data['code']) . "'";
		}

		$sql .= " GROUP BY `trigger`,`action` HAVING COUNT(`trigger`) > 1 AND COUNT(`action`) > 1";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalEvents($data = []) {

		$sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "event` WHERE `code` = '" . $this->db->escape($data['code']) . "'";

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			$sql .= " AND status = '" . (int)$data['filter_status'] . "'";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}
}