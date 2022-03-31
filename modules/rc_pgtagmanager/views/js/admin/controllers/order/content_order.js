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

    // Initialize all user events when DOM ready
    document.addEventListener('DOMContentLoaded', initRcGtmContentOrder, false);

    function initRcGtmContentOrder() {
        var orderDetailNode;
        var detailSentNode;
        var detailByNode;
        var stStatusMessageNode;
        var notSendMessageNode;
        var sendButtonNode;
        var removeButtonNode;

        orderDetailNode = document.querySelector('.js-rcgtm-order-detail');
        detailSentNode = document.querySelector('.js-rcgtm-detail-sent');
        detailByNode = document.querySelector('.js-rcgtm-detail-by');
        stStatusMessageNode = document.querySelector('.js-rcgtm-st-status');
        notSendMessageNode = document.querySelector('.js-rcgtm-not-send');
        sendButtonNode = document.querySelector('.js-rcgtm-send');
        removeButtonNode = document.querySelector('.js-rcgtm-remove');

        // bind action on node to handle it on event method
        sendButtonNode.action = 'forceTransaction';
        // bind event listener to button node
        sendButtonNode.addEventListener('click', rcAjaxAction, false);

        removeButtonNode.action = 'deleteFromControlTable';
        removeButtonNode.addEventListener('click', rcAjaxAction, false);

        // handle display depending actual status
        if (rcTrackingReport) {
            orderDetailNode.classList.remove('hidden');
            detailSentNode.innerText = rcTrackingReport.sent_at;
            detailByNode.innerText = rcTrackingStatuses[rcTrackingReport.sent_from];
            removeButtonNode.classList.remove('hidden');

            if (rcTrackingReport.sent_from === 'st') {
                stStatusMessageNode.classList.remove('hidden');
            }
        } else {
            notSendMessageNode.classList.remove('hidden');
            sendButtonNode.classList.remove('hidden');
        }
    }

    function rcAjaxAction(event) {
        var req = new XMLHttpRequest();
        var url = rcGtmModuleUrl + 'rc_pgtagmanager-ajax.php';
        var action = event.target.action;
        var data = {
            'action': action,
            'id_order': rcOrderId,
            'id_shop': rcOrderIdShop
        };
        var formData;

        formData = new FormData();
        formData.append('data', JSON.stringify(data));
        formData.append('token', rcGtmToken);

        animateIcon('refresh');

        req.open('POST', url, true);
        req.onreadystatechange = function () {
            var response;
            var type;

            if (req.readyState === 4 && req.status === 200) {
                type = req.getResponseHeader('Content-Type');
                if (type === 'application/json') {
                    response = JSON.parse(req.responseText);
                    if (typeof response === 'object') {
                        if (action === 'deleteFromControlTable') {
                            afterDeleteInControlTable(response);
                        } else if (action === 'forceTransaction') {
                            afterForceTransaction(response);
                        }
                    }
                }
            }
        };
        req.send(formData);
    }

    function animateIcon(action) {
        var iconNode;

        iconNode = document.querySelector('.js-rcgtm-icon i');

        if (action === 'refresh') {
            iconNode.className = 'icon-refresh icon-spin';
        } else if (action === 'ok') {
            iconNode.className = 'icon-check-circle';
        } else if (action === 'ko') {
            iconNode.className = 'icon-times-circle';
        }
    }

    function afterDeleteInControlTable(response) {
        var orderDetailNode;
        var detailSentNode;
        var detailByNode;
        var stStatusMessageNode;
        var notSendMessageNode;
        var sendButtonNode;
        var removeButtonNode;

        if (response && response.result) {
            // get all nodes
            orderDetailNode = document.querySelector('.js-rcgtm-order-detail');
            detailSentNode = document.querySelector('.js-rcgtm-detail-sent');
            detailByNode = document.querySelector('.js-rcgtm-detail-by');
            stStatusMessageNode = document.querySelector('.js-rcgtm-st-status');
            notSendMessageNode = document.querySelector('.js-rcgtm-not-send');
            sendButtonNode = document.querySelector('.js-rcgtm-send');
            removeButtonNode = document.querySelector('.js-rcgtm-remove');

            // wipe old data from detail
            detailSentNode.innerText = '';
            detailByNode.innerText = '';

            // hidden all other nodes
            orderDetailNode.classList.add('hidden');
            stStatusMessageNode.classList.add('hidden');
            removeButtonNode.classList.add('hidden');

            // display required nodes
            notSendMessageNode.classList.remove('hidden');
            sendButtonNode.classList.remove('hidden');
            animateIcon('ko');
        }
    }

    function afterForceTransaction(response) {
        var orderDetailNode;
        var detailSentNode;
        var detailByNode;
        var stStatusMessageNode;
        var notSendMessageNode;
        var sendButtonNode;
        var removeButtonNode;

        if (response) {
            // get all nodes
            orderDetailNode = document.querySelector('.js-rcgtm-order-detail');
            detailSentNode = document.querySelector('.js-rcgtm-detail-sent');
            detailByNode = document.querySelector('.js-rcgtm-detail-by');
            stStatusMessageNode = document.querySelector('.js-rcgtm-st-status');
            notSendMessageNode = document.querySelector('.js-rcgtm-not-send');
            sendButtonNode = document.querySelector('.js-rcgtm-send');
            removeButtonNode = document.querySelector('.js-rcgtm-remove');

            // wipe old data from detail
            detailSentNode.innerText = response.sent_at;
            detailByNode.innerText = rcTrackingStatuses[response.sent_from];

            // hidden all other nodes
            notSendMessageNode.classList.add('hidden');
            sendButtonNode.classList.add('hidden');
            stStatusMessageNode.classList.add('hidden');

            // display required nodes
            orderDetailNode.classList.remove('hidden');
            removeButtonNode.classList.remove('hidden');
            animateIcon('ok');
        }
    }
})();
