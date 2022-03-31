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
 * Thrown when trying to read an option that has no value set.
 *
 * When accessing optional options from within a lazy option or normalizer you should first
 * check whether the optional option is set. You can do this with `isset($options['optional'])`.
 * In contrast to the {@link UndefinedOptionsException}, this is a runtime exception that can
 * occur when evaluating lazy options.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
class NoSuchOptionException extends \OutOfBoundsException implements ExceptionInterface
{
}
