/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to a trade license awarded by
 * Garamo Online L.T.D.
 *
 * Any use, reproduction, modification or distribution
 * of this source file without the written consent of
 * Garamo Online L.T.D It Is prohibited.
 *
 * @author    ReactionCode <info@reactioncode.com>
 * @copyright 2015-2021 Garamo Online L.T.D
 * @license   Commercial license
 */

(function() {
    'use strict';

    var trackingInfoNode = document.querySelector('.js-rcgtm-info-panel');
    var orderDetailNode = document.querySelector('.js-rcgtm-order-detail');
    var detailSentNode = document.querySelector('.js-rcgtm-detail-sent');
    var detailByNode = document.querySelector('.js-rcgtm-detail-by');
    var stStatusMessageNode = document.querySelector('.js-rcgtm-st-status');
    var notSendMessageNode = document.querySelector('.js-rcgtm-not-send');
    var sendButtonNode = document.querySelector('.js-rcgtm-send');
    var removeButtonNode = document.querySelector('.js-rcgtm-remove');

    var trackingData = JSON.parse(trackingInfoNode.dataset.trackingData);

    // Initialize all user events when DOM ready
    document.addEventListener('DOMContentLoaded', initRcGtmContentOrder, false);

    function initRcGtmContentOrder() {
        sendButtonNode.addEventListener('click', rcAjaxAction, false);

        removeButtonNode.addEventListener('click', rcAjaxAction, false);

        updateReport(trackingData.trackingReport);
    }

    function updateReport(report) {
        stStatusMessageNode.hidden = true;
        notSendMessageNode.hidden = true;

        removeButtonNode.hidden = true;
        sendButtonNode.hidden = true;

        orderDetailNode.hidden = true;
        detailSentNode.innerText = '';
        detailByNode.innerText = '';

        if (Object.keys(report).length) {
            orderDetailNode.hidden = false;
            detailSentNode.innerText = report.sent_at;
            detailByNode.innerText = trackingData.trackingStatuses[report.sent_from];
            removeButtonNode.hidden = false;

            if (report.sent_from === 'st') {
                stStatusMessageNode.hidden = false;
            }
            badgeIcon('ok');
        } else {
            notSendMessageNode.hidden = false;
            sendButtonNode.hidden = false;
            badgeIcon('ko');
        }
    }

    function badgeIcon(action) {
        var iconNode = document.querySelector('.js-rcgtm-icon-status i');
        var spinnerNode = document.querySelector('.js-rcgtm-icon-status span');

        iconNode.hidden = true;
        spinnerNode.hidden = true;

        if (action === 'refresh') {
            spinnerNode.hidden = false;
            iconNode.innerText = '';
        } else if (action === 'ok') {
            iconNode.hidden = false;
            iconNode.innerText = 'check_circle_outline';
        } else if (action === 'ko') {
            iconNode.hidden = false;
            iconNode.innerText = 'report_problem';
        }
    }

    function rcAjaxAction(event) {
        var req = new XMLHttpRequest();
        var url = trackingData.moduleUrl + 'rc_pgtagmanager-ajax.php';
        var data = {
            'action': event.currentTarget.dataset.action,
            'id_order': trackingData.orderId,
            'id_shop': trackingData.orderIdShop
        };
        var formData;

        formData = new FormData();
        formData.append('data', JSON.stringify(data));
        formData.append('token', trackingData.token);

        badgeIcon('refresh');

        req.open('POST', url, true);
        req.responseType = 'json';
        req.onload = () => {
            if (req.status === 200) {
                var report = {};

                if (Object.hasOwnProperty.call(req.response, 'sent_at') &&
                    Object.hasOwnProperty.call(req.response, 'sent_from')
                ) {
                    report = {
                        sent_at: req.response.sent_at,
                        sent_from: req.response.sent_from
                    };
                }
                updateReport(report);
            }
        };
        req.send(formData);
    }
})();
