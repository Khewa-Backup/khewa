{*
 * @author    Mailchimp
 * @copyright Mailchimp
 * @license   Proprietary
 *}

{*
 * Pixel event-push partial. Emits a marker span + a reader script that pushes
 * the per-request JSON payload onto MC_PIXEL_EVENTS_QUEUE. Payload travels via
 * the marker's data-payload attribute (no `nofilter` needed).
 *
 * Assigned vars: $markerClass, $payload (JSON string, or '' on encode fail).
 *}
{nocache}
    <span class="{$markerClass|escape:'htmlall':'UTF-8'}" data-payload="{$payload|escape:'htmlall':'UTF-8'}" hidden></span>
    {if $payload}
        <script>
            (function () {
                var marker = document.currentScript.previousElementSibling;
                var payload = JSON.parse(marker.getAttribute('data-payload'));
                (window.MC_PIXEL_EVENTS_QUEUE = window.MC_PIXEL_EVENTS_QUEUE || []).push(payload);
            })();
        </script>
    {/if}
{/nocache}
