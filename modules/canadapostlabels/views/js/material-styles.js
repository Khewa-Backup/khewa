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

$(function () {
  // Add material classes for styling on Symfony pages
  $.each($('#canadapost'), function(containerId, container) {

    //  Add switch styling
    $(container).find('.prestashop-switch').addClass('ps-switch');

    // Flip the order of switch radio buttons, they are reversed in new pages
    $.each($(container).find(".prestashop-switch input[id$='_off']"), function(id, element) {
      let switchElement = $(element).parent('.prestashop-switch');
      let elementId = $(element).attr('id');
      let inputLabel = $('label[for=' + elementId + ']');
      inputLabel.detach().prependTo(switchElement);
      $(element).detach().prependTo(switchElement);

    });

    // Description text styling
    $(container).find('.help-block').addClass('form-text');

    // Suffix/Prefix styling
    $(container).find('.input-group-addon').addClass('input-group-text');

    // Select styling
    $(container).find('select').addClass('custom-select');

    // Form Fields
    $(container).find('input[type=text]').addClass('form-control');

    // Grid columns
    $(container).find('.form-group').addClass('row');
    $(container).find('.form-group .control-label').removeClass('col-lg-3 control-label').addClass('form-control-label');
    $(container).find('.form-group .col-lg-9').removeClass('col-lg-9').addClass('col-sm');

    // Convert tooltips to popovers
    $.each($(container).find('.label-tooltip'), function(id, element) {
      let parentLabel = $(element).parent('label, .title_box');
      let elementText = $(element).text();
      let elementHtml = $('<span />').html($(element).html());
      elementHtml.prependTo(parentLabel);
      $(element).text("").
          attr('data-content', $(element).attr('title')).
          attr('title', elementText).
          attr('data-toggle', 'popover').
          removeClass('label-tooltip').
          addClass('help-box').popover();
    });
  });
});

