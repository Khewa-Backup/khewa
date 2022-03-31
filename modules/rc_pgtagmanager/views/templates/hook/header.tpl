{*
* NOTICE OF LICENSE
*
* This source file is subject to a trade license awarded by
* Garamo Online L.T.D.
*
* Any use, reproduction, modification or distribution
* of this source file without the written consent of
* Garamo Online L.T.D It Is prohibited.
*
* @author    Reaction Code <info@reactioncode.com>
* @copyright 2015-2021 Garamo Online L.T.D
* @license   Commercial license
*}
<script data-keepinline>
    var tagManagerId = '{$gtm_id}';
    var optimizeId = '{$optimize_features.tracking_id}';
    var gtmTrackingFeatures = {$gtm_tracking_features|json_encode nofilter};
    var checkDoNotTrack = {$check_do_not_track};
    var disableInternalTracking = {$disable_internal_tracking};
    var dataLayer = window.dataLayer || [];
    var dimensions = new Object();
    var initDataLayer = new Object();
    var gaCreate = new Object();
    var doNotTrack = (
        window.doNotTrack == "1" ||
        navigator.doNotTrack == "yes" ||
        navigator.doNotTrack == "1" ||
        navigator.msDoNotTrack == "1"
    );

    if (typeof gtmTrackingFeatures === 'object' && !disableInternalTracking) {
        if (gtmTrackingFeatures.gua.trackingId) {
            initDataLayer.gua = {
                'trackingId': gtmTrackingFeatures.gua.trackingId,
                'cookieDomain': gtmTrackingFeatures.gua.cookieDomain,
                'allowLinker':  gtmTrackingFeatures.gua.allowLinker,
                'siteSpeedSampleRate': gtmTrackingFeatures.gua.siteSpeedSampleRate,
                'anonymizeIp': gtmTrackingFeatures.gua.anonymizeIp,
                'linkAttribution': gtmTrackingFeatures.gua.linkAttribution
            };

            if (gtmTrackingFeatures.gua.remarketingFeature) {
                // assign index dimensions
                dimensions.ecommProdId = gtmTrackingFeatures.gua.dimensions.ecommProdId;
                dimensions.ecommPageType = gtmTrackingFeatures.gua.dimensions.ecommPageType;
                dimensions.ecommTotalValue = gtmTrackingFeatures.gua.dimensions.ecommTotalValue;
                dimensions.ecommCategory = gtmTrackingFeatures.gua.dimensions.ecommCategory;
            }

            if (gtmTrackingFeatures.gua.businessDataFeature) {
                // assign index dimensions
                dimensions.dynxItemId = gtmTrackingFeatures.gua.dimensions.dynxItemId;
                dimensions.dynxItemId2 = gtmTrackingFeatures.gua.dimensions.dynxItemId2;
                dimensions.dynxPageType = gtmTrackingFeatures.gua.dimensions.dynxPageType;
                dimensions.dynxTotalValue = gtmTrackingFeatures.gua.dimensions.dynxTotalValue;
            }

            // assign index dimensions to data layer
            initDataLayer.gua.dimensions = dimensions;

            if (gtmTrackingFeatures.gua.userIdFeature) {
                initDataLayer.gua.userId = gtmTrackingFeatures.common.userId;
            }

            if (gtmTrackingFeatures.gua.crossDomainList) {
                initDataLayer.gua.crossDomainList = gtmTrackingFeatures.gua.crossDomainList;
            }

            // prepare gaCreate with same configuration than GTM
            gaCreate = {
                'trackingId': gtmTrackingFeatures.gua.trackingId,
                'allowLinker': true,
                'cookieDomain': gtmTrackingFeatures.gua.cookieDomain
            };
        }

        if (gtmTrackingFeatures.googleAds.trackingId) {
            initDataLayer.googleAds = {
                'conversionId' : gtmTrackingFeatures.googleAds.trackingId,
                'conversionLabel' : gtmTrackingFeatures.googleAds.conversionLabel
            };
        }

        if (gtmTrackingFeatures.bing.trackingId) {
            initDataLayer.bing = {
                'trackingId': gtmTrackingFeatures.bing.trackingId
            };
        }

        if (gtmTrackingFeatures.facebook.trackingId) {
            initDataLayer.facebook = {
                'trackingId': gtmTrackingFeatures.facebook.trackingId
            };
        }

        if (gtmTrackingFeatures.twitter.trackingId) {
            initDataLayer.twitter = {
                'trackingId': gtmTrackingFeatures.twitter.trackingId
            };
        }

        // init common values
        initDataLayer.common = {
            'currency' : gtmTrackingFeatures.common.currencyCode,
            'langCode' : gtmTrackingFeatures.common.langCode,
            'countryCode' : gtmTrackingFeatures.common.countryCode,
            'referrer' : document.referrer,
            'userAgent' : navigator.userAgent,
            'navigatorLang' : navigator.language,
            'doNotTrack' : (checkDoNotTrack && doNotTrack)
        };

        dataLayer.push(initDataLayer);
    }
</script>
{if isset($optimize_features, $gtm_tracking_features) && $optimize_features.tracking_id && $gtm_tracking_features.gua.trackingId}
    <!-- Add Optimize in recommended way for GTM -->
    {if $optimize_features.class_name}
        <!-- Google Optimize Page Hiding-->
        <style>.{$optimize_features.class_name} {ldelim}opacity: 0 !important{rdelim} </style>
        <script data-keepinline>
            if (!disableInternalTracking) {
                {literal}
                (function(a,s,y,n,c,h,i,d,e){
                    s.className+=' '+y;h.start=1*new Date;
                    h.end=i=function(){s.className=s.className.replace(RegExp(' ?'+y),'')};
                    (a[n]=a[n]||[]).hide=h;setTimeout(function(){i();h.end=null},c);
                    h.timeout=c;
                })
                {/literal}
                (window, document.documentElement, '{$optimize_features.class_name|escape:"html":"UTF-8"}', 'dataLayer',{$optimize_features.time_out|intval}, {ldelim}'{$optimize_features.tracking_id|escape:"html":"UTF-8"}': true{rdelim});
            }
        </script>
    {/if}
    <!-- initialize Optimize by Google Analytics Script -->
    <script data-keepinline>
        if (!disableInternalTracking) {
            {literal}
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
            {/literal}
            ga('create', gaCreate);
            ga('require', optimizeId);
        }
    </script>
{/if}
<!-- Init Tag Manager script -->
<script data-keepinline>
    if (!disableInternalTracking) {
        {literal}
        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({'gtm.start': new Date().getTime(), event: 'gtm.js'});
            var f = d.getElementsByTagName(s)[0];
            var j = d.createElement(s), dl = l !== 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src = 'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', tagManagerId);
        {/literal}
    }
</script>