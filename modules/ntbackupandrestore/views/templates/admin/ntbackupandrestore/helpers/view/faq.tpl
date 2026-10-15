{*
* 2013-2024 2N Technologies
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@2n-tech.com so we can send you a copy immediately.
*
* @author    2N Technologies <contact@2n-tech.com>
* @copyright 2013-2024 2N Technologies
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="panel-heading">
    <i class="fas fa-question-circle"></i>
    &nbsp;{l s='FAQ' mod='ntbackupandrestore'}
</div>
<div class="panel-group" id="accordion-faq">
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-2">
                {l s='How frequently the automation by 2N is done?' mod='ntbackupandrestore'}
            </a>
        </div>
        <div id="faq-2" class="collapse">
            <div class="panel-body">
                {l s='The automation by 2N is done daily' mod='ntbackupandrestore'}
            </div>
        </div>
    </div>
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-3">
                {l s='The automation by 2N does not seems to works anymore, what can I do?' mod='ntbackupandrestore'}
            </a>
        </div>
        <div id="faq-3" class="collapse">
            <div class="panel-body">
                {l s='Please try to save it again' mod='ntbackupandrestore'}
            </div>
        </div>
    </div>
    {if !$is_presta_edition}
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-4">
                    {l s='The backup ended with an error that indicate there is not enough space for the backup, but I know there is. Why am I having this error?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-4" class="collapse">
                <div class="panel-body">
                    {l s='This error can be caused by a limit of your server. Some server (32 bits) will not allow the backup to be of more than 2 GB. In this case, you can use the advanced option to limit backup size. Your backup will be cut in smaller parts that will be accepted by your server. For example, you can choose 1500 as the size limit for your backup file size. Pay attention that all parts are mandatory if you want to restore your shop.' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-5">
                    {l s='Where can I find the backup on my server?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-5" class="collapse">
                <div class="panel-body">
                    {l s='You can find your backup in the directory modules/ntbackupandrestore/backup/ by default, or in the directory you configured in advanced configuration.' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-6">
                    {l s='Where can I find the database dump in my backup?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-6" class="collapse">
                <div class="panel-body">
                    {l s='When you create a complete backup, your database is saved with all other files in the backup file. If you want to check, untar your backup file on your computer, you can find the dump.sql in the directory modules/ntbackupandrestore/backup/' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-7">
                    {l s='Does the complete backup really contain all of my files and data?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-7" class="collapse">
                <div class="panel-body">
                    {l s='The complete backup will contain an exact copy of your website, which means all your files and data, except files and data of low interest (caches, logs ...) which are not saved because their size is large and they are of little use during a restore. You can nevertheless tell the module to save them in the advanced options.' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
        <div class="panel check_backup">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-8">
                    {l s='How can I check that my backup is valid?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-8" class="collapse">
                <div class="panel-body">
                    {l s='The best way to make sure your backup is valid, is to try to restore it somewhere else. For example, on a new domain and new database or on your computer with Wamp, Mamp, Easyphp or other local server softwares.' mod='ntbackupandrestore'}<br/>
                    {l s='While not foolproof, you can also try to open it with a standard compression software like 7zip, winzip, winrar... If it open correctly, it is very probable that the file is correct.' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-9">
                    {l s='How can I restore my backup?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-9" class="collapse">
                <div class="panel-body">
                    {l s='To restore your backup you need to:' mod='ntbackupandrestore'}
                    <ul>
                        <li>{l s='Choose where you shop will be restored. Either at a new place (new domain and new database) for a copy/move or at the root of your current shop for its replacement.' mod='ntbackupandrestore'}</li>
                        <li>{l s='Put your backup and restore script at the choosen location' mod='ntbackupandrestore'}</li>
                        <li>{l s='Access the restore script, using your web browser' mod='ntbackupandrestore'}</li>
                        <li>{l s='Fill in the access credentials of the database where data will be restored.' mod='ntbackupandrestore'}</li>
                        <li>{l s='Optional: if needed, configure the advanced options' mod='ntbackupandrestore'}</li>
                        <li>{l s='Launch the restoration. Pay attention that files from your backup will overwrite potential existing files and database will be replaced by database from your backup.' mod='ntbackupandrestore'}</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-10">
                    {l s='I do not have enough space on my server for the backup, can I send it directly to one of my remote accounts (%1$s, %2$s...)?' sprintf=[$ntbr_dropbox_name|escape:'html':'UTF-8', $ntbr_googledrive_name|escape:'html':'UTF-8'] mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-10" class="collapse">
                <div class="panel-body">
                    {l s='Before it can be sent to a remote account, the backup need to be created on your server. You must at least have room for one backup. You can choose in the advanced configuration the option allowing the automatic deletion of the local backup if it is sent to a remote account. If you do not have enough space for one backup, you can enable direct sending (without local backup) to the remote server (except FTP and Box) but in this case, the backup will not be compressed.' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-11">
                    {l s='To restore my shop, do I need to install an empty Prestashop, first?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-11" class="collapse">
                <div class="panel-body">
                    {l s='You do not need to install an empty Prestashop to restore. You only need your backup file and restoration script.' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-12">
                    {l s='My backup does not start, what can I do?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-12" class="collapse">
                <div class="panel-body">
                    {l s='Check that the module directories and files have the correct rights. It should be 755 for directories and 644 for the files' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-13">
                    {l s='How to update the module?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-13" class="collapse">
                <div class="panel-body">
                    {l s='You need to download the last version of the module from whre you purchased it.' mod='ntbackupandrestore'}<br/>
                    {l s='Then in the backoffice of your shop, in Modules > Modules Manager, click on the Install module button and choose the module zip file.' mod='ntbackupandrestore'}<br/>
                    {l s='Prestashop will detect your current version and update automatically your module.' mod='ntbackupandrestore'}<br/>
                    {l s='There is no need to uninstall the module first.' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
    <div class="panel">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-14">
                {l s='If I encrypt my backup, will it take much more space?' mod='ntbackupandrestore'}
            </a>
        </div>
        <div id="faq-14" class="collapse">
            <div class="panel-body">
                {l s='No, encryption will increase your backup size by approximately 3.3% only' mod='ntbackupandrestore'}
            </div>
        </div>
    </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-15">
                    {l s='How do I encrypt my backup?' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-15" class="collapse">
                <div class="panel-body">
                    {l s='In the advanced configuration of the module, enable the option "Encrypt the backup". Remember to write down the secret key that will be displayed under the option because it will be essential to restore your encrypted backup.' mod='ntbackupandrestore'}
                </div>
            </div>
        </div>
        <div class="panel">
            <div class="panel-heading">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-faq" href="#faq-16">
                    {l s='When I restore my backup with the restore script, I get the error message: "Error while restoring files, tar file truncated"' mod='ntbackupandrestore'}
                </a>
            </div>
            <div id="faq-16" class="collapse">
                <div class="panel-body">
                    {l s='This error appears when the restore script fails to extract all files from the backup.' mod='ntbackupandrestore'}<br/>
                    {l s='There are usually three causes:' mod='ntbackupandrestore'}<br/>
                    <br/>
                    1) {l s='The upload of the backup file stopped before completion, so it is incomplete.' mod='ntbackupandrestore'}<br/>
                    {l s='Check its size with the original one.' mod='ntbackupandrestore'}<br/>
                    {l s='If they are different, the transfer of the backup file to where you are going to restore has stopped before completion.' mod='ntbackupandrestore'}<br/>
                    {l s='In this case, resend the file.' mod='ntbackupandrestore'}<br/>
                    <br/>
                    2) {l s='Your backup file is more than 2 GB but the server where you are backing up or restoring it does not support files larger than 2 GB.' mod='ntbackupandrestore'}<br/>
                    {l s='In the module\'s advanced configuration, change the "Maximum size (in MB) for your backup files" option to 2000 and perform a backup again.' mod='ntbackupandrestore'}<br/>
                    {l s='Your backup will then be split into several smaller files. Copy all of them to where you want to restore and run the restore again.' mod='ntbackupandrestore'}<br/>
                    <br/>
                    3) {l s='More rare but it can happen, the backup file has a problem.' mod='ntbackupandrestore'}<br/>
                    {l s='To be sure, open it with a standard compression software like 7zip, winrar, winzip...' mod='ntbackupandrestore'}<br/>
                    {l s='If the software manages to open the file without error, the file is correct. Otherwise there was a problem during the backup.' mod='ntbackupandrestore'}<br/>
                    <br/>
                    {l s='If despite this you still have a problem, do not hesitate to contact us.' mod='ntbackupandrestore'}<br/>
                </div>
            </div>
        </div>
    {/if}
</div>