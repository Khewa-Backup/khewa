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

namespace CanadaPost\Symfony\Component\OptionsResolver\Exception;

/**
 * Thrown when the value of an option does not match its validation rules.
 *
 * You should make sure a valid value is passed to the option.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class InvalidOptionsException extends InvalidArgumentException
{
}
