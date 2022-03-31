<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
class GiftCardCreateEmailByLang
{
    public $folderBase;
    const ENGLISH = 'en';

    public function __construct()
    {
        $this->folderBase = self::ENGLISH;
    }

    /**
     * Check if we can create a new folder in the folder and if we can read/write the copied files.
     *
     * @param string $folder
     *
     * @return bool
     */
    public function checkIfWeCanCreateFolders($folder)
    {
        if (!is_writable($folder)) {
            return false;
        }

        if (!is_executable($folder)) {
            return false;
        }

        if (!is_readable($folder . $this->folderBase)) {
            return false;
        }

        if (!is_executable($folder . $this->folderBase)) {
            return false;
        }

        return true;
    }

    /**
     * For each languages, check if the language's folder exists and create it if it doesn't
     *
     * @param string $folder
     * @param array $aLanguages
     *
     * @return bool
     */
    public function intializeEmailTemplatesByLanguage($folder, $aLanguages)
    {
        foreach ($aLanguages as $key => $aLanguage) {
            $isoCode = $aLanguage['iso_code'];

            if (true === $this->checkIfEmailLanguageFolderExists($folder, $isoCode)) {
                continue;
            }

            $this->createEmailLanguageFolder($folder, $isoCode);
        }

        return true;
    }

    /**
     * Check if the folder exists
     *
     * @param string $folder
     *
     * @return bool
     */
    private function checkIfEmailLanguageFolderExists($folder, $isoCode)
    {
        if (file_exists($folder . $isoCode)) {
            return true;
        }

        return false;
    }

    /**
     * Copy recursively the default folder (folderBase) for each language
     *
     * @param string $folder
     * @param string $isoCode
     */
    private function createEmailLanguageFolder($folder, $isoCode)
    {
        $sourceFolder = $folder . $this->folderBase;
        $destinationFolder = $folder . $isoCode;

        $dir = opendir($sourceFolder);
        mkdir($destinationFolder);
        while ($file = readdir($dir)) {
            if (($file != '.') && ($file != '..')) {
                copy($sourceFolder . '/' . $file, $destinationFolder . '/' . $file);
            }
        }
        closedir($dir);
    }
}
