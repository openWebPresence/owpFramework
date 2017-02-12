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

/**
* Open Web Presence EZSql Extension Class
*
* @author     Brian Tafoya
* @version    1.0
*/
class owp_EzSql_mysqli extends ezSQL_mysqli {

		function firephpdebug($firephp, $title = "ezSql Debug") {
			
			$table   = array();
			$table[] = array('Item', 'Detail');
			if ( ! $this->debug_called ) {
				$table[] = array('EZSQL_VERSION', EZSQL_VERSION);
			}
			if ( $this->last_error ) {
				$table[] = array('Last Error', $this->last_error);
			}
			$table[] = array('Query [' . $this->num_queries . ']', $this->last_query);
			if ( $this->col_info ) {
				$table[] = array('Col Info', $this->col_info);
			}
			$table[] = array('Last Result', ($this->last_result?$this->get_results(null,ARRAY_A):"No Results"));
			$firephp->table($title, $table);

			$this->debug_called = true;
		}
}
