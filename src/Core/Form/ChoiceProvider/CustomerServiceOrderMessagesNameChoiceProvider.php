<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
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
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShop\PrestaShop\Core\Form\ChoiceProvider;

use PrestaShop\PrestaShop\Core\Form\FormChoiceProviderInterface;

/**
 * Gets choices for predefined order message types.
 */
final class CustomerServiceOrderMessagesNameChoiceProvider implements FormChoiceProviderInterface
{
    /**
     * @var array
     */
    private $orderMessages;

    public function __construct(array $orderMessages)
    {
        $this->orderMessages = $orderMessages;
    }

    /**
     * {@inheritdoc}
     */
    public function getChoices($langId = null): array
    {
        $result = [];

        // Access the Symfony container
        $container = \PrestaShop\PrestaShop\Adapter\SymfonyContainer::getInstance();

        // Get the request stack service
        $requestStack = $container->get('request_stack');

        // Get the current request
        $request = $requestStack->getCurrentRequest();

        // Get route parameters
        $routeParams = $request->attributes->get('_route_params');

//        var_dump($routeParams);

        if(isset($routeParams['orderId']) && $routeParams['orderId']){
            $o = new \Order($routeParams['orderId']);
            $langId = $o->id_lang;
            $this->orderMessages = \OrderMessage::getOrderMessages($langId);
        }

        foreach ($this->orderMessages as $orderMessage) {
            $result[$orderMessage['name']] = $orderMessage['id_order_message'];
        }

        return $result;
    }
}
