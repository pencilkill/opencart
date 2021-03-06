<?php
class ModelSaleVoucherTheme extends Model {
	public function addVoucherTheme($data) {
		$this->db->set('image', html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8'));

		$this->db->insert('voucher_theme');

		$voucher_theme_id = $this->db->getLastId();

		foreach ($data['voucher_theme_description'] as $language_id => $value) {
			$value['voucher_theme_id'] = (int)$voucher_theme_id;
			$value['language_id'] = (int)$language_id;

			$this->db->insert('voucher_theme_description', $value);
		}

		$this->cache->delete('voucher_theme');
	}

	public function editVoucherTheme($voucher_theme_id, $data) {
		$this->db->update('voucher_theme', array('image' => html_entity_decode($data['image'], ENT_QUOTES, 'UTF-8')), array('voucher_theme_id' => (int)$voucher_theme_id));

		$this->db->delete('voucher_theme_description', array('voucher_theme_id' => (int)$voucher_theme_id));

		foreach ($data['voucher_theme_description'] as $language_id => $value) {
			$value['voucher_theme_id'] = (int)$voucher_theme_id;
			$value['language_id'] = (int)$language_id;

			$this->db->insert('voucher_theme_description', $value);
		}

		$this->cache->delete('voucher_theme');
	}

	public function deleteVoucherTheme($voucher_theme_id) {
		$this->db->delete('voucher_theme', array('voucher_theme_id' => (int)$voucher_theme_id));
		$this->db->delete('voucher_theme_description', array('voucher_theme_id' => (int)$voucher_theme_id));

		$this->cache->delete('voucher_theme');
	}

	public function getVoucherTheme($voucher_theme_id) {
		$query = $this->db->from('voucher_theme vt')->join('voucher_theme_description vtd', 'vt.voucher_theme_id = vtd.voucher_theme_id')->where(array('vt.voucher_theme_id' => (int)$voucher_theme_id, 'vtd.language_id' => (int)$this->config->get('config_language_id')));

		return $query->row;
	}

	public function getVoucherThemes($data = array()) {
      	if ($data) {
			$query = $this->db->select('voucher_theme vt')->join('voucher_theme_description vtd', 'vt.voucher_theme_id = vtd.voucher_theme_id')->where('vtd.language_id', (int)$this->config->get('config_language_id'));

			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$query->order_by('vtd.name', 'DESC');
			} else {
				$query->order_by('vtd.name', 'ASC');
			}

			if (isset($data['start']) || isset($data['limit'])) {
				if (!isset($data['start']) || (int)$data['start'] < 0) {
					$data['start'] = 0;
				}

				if (!isset($data['limit']) || (int)$data['limit'] < 1) {
					$data['limit'] = 20;
				}

				$query->limit((int)$data['limit'], (int)$data['start']);
			}

			return $query->get()->rows;
		} else {
			$voucher_theme_data = $this->cache->get('voucher_theme.' . (int)$this->config->get('config_language_id'));

			if (!$voucher_theme_data) {
				$query = $this->db->select('voucher_theme vt')->join('voucher_theme_description vtd', 'vt.voucher_theme_id = vtd.voucher_theme_id')->where('vtd.language_id', (int)$this->config->get('config_language_id'))->order_by('vtd.name', 'ASC');;

				$voucher_theme_data = $query->rows;

				$this->cache->set('voucher_theme.' . (int)$this->config->get('config_language_id'), $voucher_theme_data);
			}

			return $voucher_theme_data;
		}
	}

	public function getVoucherThemeDescriptions($voucher_theme_id) {
		$voucher_theme_data = array();

		$query = $this->db->get_where('voucher_theme_description', array('voucher_theme_id' => (int)$voucher_theme_id));

		foreach ($query->rows as $result) {
			$voucher_theme_data[$result['language_id']] = array('name' => $result['name']);
		}

		return $voucher_theme_data;
	}

	public function getTotalVoucherThemes() {
      	return $this->db->count_all('voucher_theme');
	}
}
?>