<?php
/**
 * 2013-2024 2N Technologies
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@2n-tech.com so we can send you a copy immediately.
 *
 * @author    2N Technologies <contact@2n-tech.com>
 * @copyright 2013-2024 2N Technologies
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/*
 * Your OneDrive client ID.
 */
// define('ONEDRIVE_CLIENT_ID', '689df7e1-1aa5-4025-ab3a-323c6c898762'); //old version
// define('ONEDRIVE_CLIENT_ID', 'fdabf9df-24aa-4857-8c9e-1f12f269e98a'); // new version
define('ONEDRIVE_CLIENT_ID', 'fdabf9df-24aa-4857-8c9e-1f12f269e98a');

/*
 * Your OneDrive client secret.
 */
// define('ONEDRIVE_CLIENT_SECRET', '6Mi3E4D0pM4SBs5r5RgM89M'); //old version
// define('ONEDRIVE_CLIENT_SECRET', '_Md~g82g9MD--uYSmF918T1z1906~2nqRJ'); // new version
define('ONEDRIVE_CLIENT_SECRET', 'F1j.1nv.4vU-Lf5X1S9B3s73R.RH~d~tVU');

/*
* Your OneDrive callback URI.
*/
define('ONEDRIVE_CALLBACK_URI', 'https://oauth.2n-tech.com/get_oauth_code.php');

/*
* The URL to get the token link to a code
*/
define('GET_TOKEN_URI', 'https://oauth.2n-tech.com/get_oauth_token.php');

/*
* Display log
*/
define('CREATE_ONEDRIVE_LOG', true);
