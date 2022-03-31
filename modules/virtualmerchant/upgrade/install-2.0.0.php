<?php
/**
 * This file is part of module Virtual Merchant
 *
 *  @author    Bellini Services <bellini@bellini-services.com>
 *  @copyright 2007-2017 bellini-services.com
 *  @license   readme
 *
 * Your purchase grants you usage rights subject to the terms outlined by this license.
 *
 * You CAN use this module with a single, non-multi store configuration, production installation and unlimited test installations of PrestaShop.
 * You CAN make any modifications necessary to the module to make it fit your needs. However, the modified module will still remain subject to this license.
 *
 * You CANNOT redistribute the module as part of a content management system (CMS) or similar system.
 * You CANNOT resell or redistribute the module, modified, unmodified, standalone or combined with another product in any way without prior written (email) consent from bellini-services.com.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

if (!defined('_PS_VERSION_'))
	exit;

function upgrade_module_2_0_0($object, $install = false)
{
	//this is used to bypass meaningless validator rules, since install parameter is never used
	if ($install)
		$install = true;

	$vm_version = Configuration::get('VM_VERSION');

	if ((!$vm_version) || (empty($vm_version)) || ($vm_version < $object->version))
		Configuration::updateValue('VM_VERSION', '2.0.0');

	try 
	{
		//add new features

		//remove features that are no longer used

		//remove any files no longer used
		$path = realpath(_PS_MODULE_DIR_.$object->name);

		$delete_files = array(
			$path.'/config.xml',
			$path.'/logo.gif',
			$path.'/payment.php',

			$path.'/backward_compatibility/backward.ini',
			$path.'/backward_compatibility/backward.php',
			$path.'/backward_compatibility/Context.php',
			$path.'/backward_compatibility/Display.php',
			$path.'/backward_compatibility/index.php',

			$path.'/views/templates/admin/index.php',
			$path.'/views/templates/admin/back_office.tpl',

			$path.'/views/templates/front/payment_execution_mobile.tpl',
		);

		foreach ($delete_files as $file)
		{
			if (file_exists($file))
				unlink($file);
		}

		//backward dir
		$backward_dir = realpath($path.'/backward_compatibility');
		if (is_dir($backward_dir))
			rmdir($backward_dir);

		$admintpl_dir = realpath($path.'/views/templates/admin');
		if (is_dir($admintpl_dir))
			rmdir($admintpl_dir);

	}
	catch (Exception $e)
	{
		//die quitely
		$e = $e;
	}

	return true;
}
