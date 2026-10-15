<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

namespace CanadaPostPs;

class Icon
{
    /** @var string */
    public $name;

    /**
     * Array of icon names organized as "material-icon" => "font-awesome icon" names,
     * used to convert between the two for older PS versions.
     *
     * @var string[]
     */
    public static $materialToFontAwesomeIcons = array(
        'description' => 'file-text-o',
        'cancel' => 'ban',
        'arrow_back' => 'arrow-left',
        'access_time' => 'time',
        'error' => 'exclamation-circle',
        'check' => 'check',
        'favorite' => 'heart',
        'print' => 'print',
        'list' => 'list',
        'view_quilt' => 'cubes',
        'local_shipping' => 'truck',
        'monetization_on' => 'dollar',
        'save' => 'save',
    );

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function geName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMaterialName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFontAwesomeName()
    {
        return self::$materialToFontAwesomeIcons[$this->name];
    }

    /**
     * Get <i> icon html appropriate for this PS version.
     * Older PS versions used font-awesome, while newer ones use material icons
     *
     * @param $htmlClasses
     *
     * @return string
     * @throws \SmartyException
     */
    public function getHtml($htmlClasses)
    {
        $Context = \Context::getContext();

        $class = 'icon-' . $this->name;
        $text  = '';

        // If icon has an associated font-awesome icon, convert it;
        // otherwise, render it as-is in legacy font-awesome format
        if (isset(self::$materialToFontAwesomeIcons[$this->name])) {
            $class = 'icon-' . $this->getFontAwesomeName();

            if (version_compare(_PS_VERSION_, '1.7.7') >= 0) {
                $class = 'material-icons';
                $text  = $this->name;
            }
        }

        if (null !== $htmlClasses) {
            $class .= ' ' . $htmlClasses;
        }

        $Context->smarty->assign(array(
            'htmlClass' => $class,
            'text' => $text
        ));

        return $Context->smarty->fetch(_PS_MODULE_DIR_. 'canadapostlabels/views/templates/hook/icon.tpl');
    }

    /**
     * Get <i> icon html appropriate for this PS version.
     * Older PS versions used font-awesome, while newer ones use material icons
     *
     * @param string $iconName
     * @param string $htmlClasses
     *
     * @return string
     * @throws \Exception
     */
    public static function getIconHtml($iconName, $htmlClasses = null)
    {
        $Icon = new Icon($iconName);

        return $Icon->getHtml($htmlClasses);
    }
}
