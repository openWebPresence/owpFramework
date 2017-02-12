<?php
/**
 * Open Web Presence - openwebpresence.com
 *
 * Copyright (c) 2017, Brian Tafoya
 *
 * @author Brian Tafoya <btafoya@briantafoya.com>
 * @copyright 2001 - 2017, Brian Tafoya.
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This library is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

class owp_mod {
	protected function getDataItem($itemName) {
		if(isset($this->data_item[$itemName])) {
			return $this->data_item[$itemName];
		}
	}
}
