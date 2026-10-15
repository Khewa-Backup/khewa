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

const pickr1 = Pickr.create({
    el: '.ps_colorpicker1',
    default: color1,
    defaultRepresentation: 'HEX',
    closeWithKey: 'Escape',
    adjustableNumbers: true,
    components: {

        // Main components
        preview: true,
        opacity: false,
        hue: true,

        // Input / output Options
        interaction: {
            hex: false,
            rgba: false,
            hsla: false,
            hsva: false,
            cmyk: false,
            input: true,
            clear: false,
            save: true
        }
    }
});

const pickr2 = Pickr.create({
    el: '.ps_colorpicker2',
    default: color2,
    defaultRepresentation: 'HEX',
    closeWithKey: 'Escape',
    adjustableNumbers: true,
    components: {

        // Main components
        preview: true,
        opacity: false,
        hue: true,

        // Input / output Options
        interaction: {
            hex: false,
            rgba: false,
            hsla: false,
            hsva: false,
            cmyk: false,
            input: true,
            clear: false,
            save: true
        }
    }
});

pickr1.on('change', (...args) => {
    let pickrColor = pickr1.getColor();
    let hexaColor = pickrColor.toHEX().toString();
    liveEditColor('primary', hexaColor);
    $('#primary_color button').css('color', hexaColor);
    $('#primary_color .data_input').val(hexaColor);
})

pickr2.on('change', (...args) => {
    let pickrColor = pickr2.getColor();
    let hexaColor = pickrColor.toHEX().toString();
    liveEditColor('secondary', hexaColor);
    $('#secondary_color button').css('color', hexaColor);
    $('#secondary_color .data_input').val(hexaColor);
})
