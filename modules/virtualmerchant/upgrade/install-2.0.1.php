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

function upgrade_module_2_0_1($object, $install = false)
{
	//this is used to bypass meaningless validator rules, since install parameter is never used
	if ($install)
		$install = true;

	$vm_version = Configuration::get('VM_VERSION');

	if ((!$vm_version) || (empty($vm_version)) || ($vm_version < $object->version))
		Configuration::updateValue('VM_VERSION', '2.0.1');

	try 
	{
		//add new features

		//remove features that are no longer used
		$object->unregisterHook('header');

		//remove any files no longer used
	}
	catch (Exception $e)
	{
		//die quitely
		$e = $e;
	}

	return true;
}
