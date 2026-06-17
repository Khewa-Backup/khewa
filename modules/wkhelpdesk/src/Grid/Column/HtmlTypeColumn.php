<?php
namespace WkHelpDesk\Grid\Column;

use PrestaShop\PrestaShop\Core\Grid\Column\AbstractColumn;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HtmlTypeColumn extends AbstractColumn
{
    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'mymodule_button';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired([
                'ModuleClass',
                'custom_text'
            ])
            ->setAllowedTypes('ModuleClass', 'object')
            ->setAllowedTypes('custom_text', 'string');
    }
}
