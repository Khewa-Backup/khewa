<?php
/**
 * MailChimp
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Commercial License
 * you can't distribute, modify or sell this code
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file
 * If you need help please contact leo@prestachamps.com
 *
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   commercial
 */

namespace PrestaChamps\MailchimpPro\Console\Commands;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaChamps\MailchimpPro\Helpers\PsContext;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CLI Order sync
 */
class OrdersSyncCommand extends Command
{
    protected function configure()
    {
        $this->setName('mailchimp:sync:orders');
        $this->setDescription('Sync your orders to Mailchimp');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ids = [1];
        $progressBar = new ProgressBar($output, count($ids));

        $progressBar->start();

        $context = PsContext::getContext();
        /** @var \Mailchimppro $module */
        $module = \Module::getInstanceByName('mailchimppro');
        $apiClient = $module->getApiClient();
        foreach ($ids as $orderId) {
            try {
                $command = new \PrestaChamps\MailchimpPro\Commands\OrderSyncCommand(
                    $context,
                    $apiClient,
                    [$orderId]
                );
            } catch (\Exception $exception) {
                $output->writeln($exception->getMessage());
                exit;
            }
            $progressBar->advance();
        }
        $progressBar->finish();

        $output->writeln(PHP_EOL . 'Sync done!');

        return 0;
    }

    protected function getOrderIds()
    {
        $query = new \DbQuery();
        $query->from('orders');
        $query->select('id_order');
        $query->where('id_shop = 1');

        return array_column(\Db::getInstance()->executeS($query), 'id_order');
    }
}
