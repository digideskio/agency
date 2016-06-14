<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2015 Stanislas Rolland <typo3(arobas)sjbr.ca>
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
 * Storage security functions
 *
 * @author	Stanislas Rolland <typo3(arobas)sjbr.ca>
 *
 * @package TYPO3
 * @subpackage agency
 *
 *
 */
class tx_agency_storage_security {
		// Extension key
	protected $extKey = AGENCY_EXT;
		// The storage security level: normal or salted
	protected $storageSecurityLevel = 'normal';

	/**
	* Constructor
	*
	* @return	void
	*/
	public function __construct () {
		$this->setStorageSecurityLevel();
	}

	/**
	* Sets the storage security level
	*
	* @return	void
	*/
	protected function setStorageSecurityLevel () {
		$this->storageSecurityLevel = 'normal';
		if (t3lib_extMgm::isLoaded('saltedpasswords') && tx_saltedpasswords_div::isUsageEnabled('FE')) {
			$this->storageSecurityLevel = 'salted';
		}
	}

	/**
	* Gets the storage security level
	*
	* @return	string	the storage security level
	*/
	protected function getStorageSecurityLevel () {
		return $this->storageSecurityLevel;
	}

	/**
	* Encrypts the password for secure storage
	*
	* @param	string	$password: password to encrypt
	* @return	string	encrypted password
    *           boolean FALSE in case of an error
	*/
	public function encryptPasswordForStorage ($password) {

		$encryptedPassword = $password;
		if ($password != '') {
			switch ($this->getStorageSecurityLevel()) {
				case 'salted':
					$objSalt = tx_saltedpasswords_salts_factory::getSaltingInstance(NULL);
					if (is_object($objSalt)) {
						$encryptedPassword = $objSalt->getHashedPassword($password);
					} else {
						$encryptedPassword = FALSE;
						// Could not get a salting instance from saltedpasswords
						// Should not happen: checked in tx_agency_pi_base::checkRequirements
					}
					break;
				case 'normal':
				default:
						// No encryption!
					break;
			}
		}

		return $encryptedPassword;
	}

	/**
	* Initializes the password for auto-login on confirmation
	*
	* @param	array	$dataArray
	* @return	void
	*/
// 	public function initializeAutoLoginPassword (array &$dataArray) {
// 		$dataArray['tx_agency_password'] = '';
// 		unset($dataArray['auto_login_key']);
// 	}

	/**
	* Determines if auto login should be attempted
	*
	* @param array $feuData: incoming fe_users parameters
	* @param string &$autoLoginKey: returns auto-login key
	* @return boolean TRUE, if auto-login should be attempted
	*/
	public function getAutoLoginIsRequested (array $feuData, &$autoLoginKey) {
		$autoLoginIsRequested = FALSE;
		if (isset($feuData['key']) && $feuData['key'] !== '') {
			$autoLoginKey = $feuData['key'];
			$autoLoginIsRequested = TRUE;
		}

		return $autoLoginIsRequested;
	}

	/**
	* Encrypts the password for auto-login on confirmation
	*
	* @param	string	$password: the password to be encrypted
	* @param	string	$cryptedPassword: returns the encrypted password
	* @param	string	$autoLoginKey: returns the auto-login key
	* @return	boolean  TRUE if the crypted password and auto-login key are filled in
	*/
	public function encryptPasswordForAutoLogin (
		$password,
		&$cryptedPassword,
		&$autoLoginKey
	) {
		$result = FALSE;
		$privateKey = '';
		$cryptedPassword = '';

		if ($password != '') {
				// Create the keypair
			$keyPair = openssl_pkey_new();

				// Get private key
			openssl_pkey_export($keyPair, $privateKey);
				// Get public key
			$keyDetails = openssl_pkey_get_details($keyPair);
			$publicKey = $keyDetails['key'];

			if (@openssl_public_encrypt($password, $cryptedPassword, $publicKey)) {
				$autoLoginKey = $privateKey;
				$result = TRUE;
			}
		}
		return $result;
	}

	/**
	* Decrypts the password for auto-login on confirmation or invitation acceptation
	*
	* @param	string	$password: the password to be decrypted
	* @param	string	$autoLoginKey: the auto-login private key
	* @return	boolean  TRUE if decryption is successfull or no rsaauth is used
	*/
	public function decryptPasswordForAutoLogin (
		&$password,
		$autoLoginKey
	) {
		$result = TRUE;
		if ($autoLoginKey != '') {
			$privateKey = $autoLoginKey;
			if ($privateKey != '') {
				if ($password != '' && t3lib_extMgm::isLoaded('rsaauth')) {
					$backend = tx_rsaauth_backendfactory::getBackend();
					if (is_object($backend) && $backend->isAvailable()) {
						$decryptedPassword = $backend->decrypt($privateKey, $password);
						if ($decryptedPassword) {
							$password = $decryptedPassword;
						} else {
								// Failed to decrypt auto login password
							$message =
								$GLOBALS['TSFE']->sL(
									'LLL:EXT:' . $this->extKey . '/pi/locallang.xml:internal_decrypt_auto_login_failed'
								);
							t3lib_div::sysLog($message, $this->extKey, t3lib_div::SYSLOG_SEVERITY_ERROR);
						}
					} else {
						// Required RSA auth backend not available
						// Should not happen: checked in tx_agemcy_pi_base::checkRequirements
						$result = FALSE;
					}
				}
			}
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/' . AGENCY_EXT . '/model/class.tx_agency_storage_security.php']) {
  include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/' . AGENCY_EXT . '/model/class.tx_agency_storage_security.php']);
}
