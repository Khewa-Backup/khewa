{*
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
 *
 *}

<div id="development" class="tab-pane">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-wrench"></i>
            {l s='Custom Development' mod='canadapostlabels'}
        </div>
        <div class="dynamic-development">
        </div>
        <script type="text/javascript">
            $.post('https://zhmedia.ca/canadapost/development.php', function(data) {
                if ("html" in data) {
                    $('.dynamic-development').html(data.html);
                }
            }, 'json');
        </script>
    </div>
</div>