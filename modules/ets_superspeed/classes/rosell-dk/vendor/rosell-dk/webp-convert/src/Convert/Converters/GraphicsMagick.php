<?php
/**
 * 2007-2021 ETS-Soft
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 wesite only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 * 
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please contact us for extra customization service at an affordable price
 *
 *  @author ETS-Soft <etssoft.jsc@gmail.com>
 *  @copyright  2007-2021 ETS-Soft
 *  @license    Valid for 1 website (or project) for each purchase of license
 *  International Registered Trademark & Property of ETS-Soft
 */

namespace WebPConvert\Convert\Converters;

use WebPConvert\Convert\Converters\AbstractConverter;
use WebPConvert\Convert\Converters\ConverterTraits\EncodingAutoTrait;
use WebPConvert\Convert\Converters\ConverterTraits\ExecTrait;

/**
 * Convert images to webp by calling gmagick binary (gm).
 *
 * @package    WebPConvert
 * @author     Bjørn Rosell <it@rosell.dk>
 * @since      Class available since Release 2.0.0
 */
class GraphicsMagick extends AbstractConverter
{
    use ExecTrait;
    use EncodingAutoTrait;
    private function getPath()
    {
        if (defined('WEBPCONVERT_GRAPHICSMAGICK_PATH')) {
            return constant('WEBPCONVERT_GRAPHICSMAGICK_PATH');
        }
        if (!empty(getenv('WEBPCONVERT_GRAPHICSMAGICK_PATH'))) {
            return getenv('WEBPCONVERT_GRAPHICSMAGICK_PATH');
        }
        return 'gm';
    }

    public function isInstalled()
    {
        exec($this->getPath() . ' -version', $output, $returnCode);
        return ($returnCode == 0);
    }

    public function getVersion()
    {
        exec($this->getPath() . ' -version', $output, $returnCode);
        if (($returnCode == 0) && isset($output[0])) {
            return preg_replace('#http.*#', '', $output[0]);
        }
        return 'unknown';
    }

    // Check if webp delegate is installed
    public function isWebPDelegateInstalled()
    {
        exec($this->getPath() . ' -version', $output, $returnCode);
        foreach ($output as $line) {
            if (preg_match('#WebP.*yes#i', $line)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check (general) operationality of imagack converter executable
     *
     * @throws SystemRequirementsNotMetException  if system requirements are not met
     */
    public function checkOperationality()
    {
        if($this->checkOperationalityExecTrait())
        {
            if (!$this->isInstalled()) {
                return true;
            }
            if (!$this->isWebPDelegateInstalled()) {
                return false;
            }
            return true;
        }
    }

    /**
     * Build command line options
     *
     * @return string
     */
    private function createCommandLineOptions()
    {
        $commandArguments = [];

        // Unlike imagick binary, it seems gmagick binary uses a fixed
        // quality (75) when quality is omitted
        $commandArguments[] = '-quality ' . escapeshellarg($this->getCalculatedQuality());

        // encoding
        if ($this->options['encoding'] == 'lossless') {
            // Btw:
            // I am not sure if we should set "quality" for lossless.
            // Quality should not apply to lossless, but my tests shows that it does in some way for gmagick
            // setting it low, you get bad visual quality and small filesize. Setting it high, you get the opposite
            // Some claim it is a bad idea to set quality, but I'm not so sure.
            // https://stackoverflow.com/questions/4228027/
            // First, I do not just get bigger images when setting quality, as toc777 does.
            // Secondly, the answer is very old and that bad behaviour is probably fixed by now.
            $commandArguments[] = '-define webp:lossless=true';
        } else {
            $commandArguments[] = '-define webp:lossless=false';
        }

        if ($this->options['alpha-quality'] !== 100) {
            $commandArguments[] = '-define webp:alpha-quality=' . strval($this->options['alpha-quality']);
        }

        if ($this->options['low-memory']) {
            $commandArguments[] = '-define webp:low-memory=true';
        }

        if ($this->options['metadata'] == 'none') {
            $commandArguments[] = '-strip';
        }

        $commandArguments[] = '-define webp:method=' . $this->options['method'];

        $commandArguments[] = escapeshellarg($this->source);
        $commandArguments[] = escapeshellarg('webp:' . $this->destination);

        return implode(' ', $commandArguments);
    }

    protected function doActualConvert()
    {
        $command = $this->getPath() . ' convert ' . $this->createCommandLineOptions();
        $useNice = (($this->options['use-nice']) && self::hasNiceSupport()) ? true : false;
        if ($useNice) {
            $command = 'nice ' . $command;
        }
        exec($command, $output, $returnCode);
        if ($returnCode == 127) {
            return false;
        }
        if ($returnCode != 0) {
            return false;
        }
        return true;
    }
}
