<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2011 Franz Holzinger (franz@ttproducts.de)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Part of the agency (Agency Registration) extension.
 *
 * setup configuration functions
 *
 * @author	Franz Holzinger <franz@ttproducts.de>
 *
 * @package TYPO3
 * @subpackage agency
 *
 *
 */


class tx_agency_conf {
	protected $conf = array();
	protected $config = array();

	public function init ($conf) {
		$this->conf = $conf;
		$this->config = array();
	}

	public function setConf (array $dataArray, $k = '') {
		if ($k) {
			$this->conf[$k] = $dataArray;
		} else {
			$this->conf = $dataArray;
		}
	}

	public function getConf () {
		return $this->conf;
	}

	public function setConfig (array $dataArray, $k = '') {
		if ($k) {
			$this->config[$k] = $dataArray;
		} else {
			$this->config = $dataArray;
		}
	}

	public function getConfig () {
		return $this->config;
	}
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/' . AGENCY_EXT . '/lib/class.tx_agency_conf.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/' . AGENCY_EXT . '/lib/class.tx_agency_conf.php']);
}
