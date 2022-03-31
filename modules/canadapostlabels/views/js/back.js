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

$(function() {
    if (typeof configUrl !== 'undefined') {
        ajaxCheckStatus('api_connection');
        ajaxCheckStatus('boxes');
    }
    initFormTabs();
});

/*
* Check the status of module services and display error on failure
* */
function ajaxCheckStatus(service) {
    $.post(configUrl, {check_status: 1, service: service}, function (data) {
        if ("status" in data && !data.status) {
            $('#canadapost').prepend($(data.errorHtml));
            $('html, body').animate({ scrollTop: 0 }, 200);
        }
    }, 'json');
}

/*
* Setup sidebar tabs for forms
* */
function initFormTabs() {
    $('#canadapost .forms.tab-content').addClass('col-xs-12 col-sm-9 col-md-10 col-lg-10');
    $('#canadapost .sidebar').show();

    var tabs = $('#canadapost .sidebar .formTabs .list-group-item:not(.disabled)');

    // Go to the config latest tab if it exists
    var lastTab = localStorage.getItem('cplLastTab');
    if (typeof configUrl !== 'undefined' && lastTab && $('#'+lastTab).hasClass('disabled') === false) {
        var activeTab = $('#'+lastTab);
        activeTab.tab('show');
        activeTab.addClass('active');
        $('#canadapost .forms '+activeTab.attr('href')).addClass('active');
    } else {
        $('#canadapost .forms > div:first-child').addClass('active');
        $('#canadapost .sidebar .formTabs .list-group-item:first-child').addClass('active');
    }

    // Store active tab
    if (typeof configUrl !== 'undefined') {
        tabs.on('shown.bs.tab', function (e) {
            localStorage.setItem('cplLastTab', $(e.target).attr('id'));
        });
    }

    tabs.on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');

        // Toggle active states
        $("#canadapost .sidebar .list-group .list-group-item").removeClass("active");
        $(this).addClass("active");
        $("#canadapost .forms > .tab-pane").removeClass("active");
        $(".forms "+$(this).attr('href')).addClass("active");
    });
}

function appendUrlVars(url, vars) {
    if (url.indexOf("?") !== -1) {
        url = url+"&"+vars;
    } else {
        url = url+"?"+vars;
    }
    return url;
}

