<script type="text/javascript">
    /**
     * 2017 Leone MusicReader B.V.
     *
     * NOTICE OF LICENSE
     *
     * Source file is copyrighted by Leone MusicReader B.V.
     * Only licensed users may install, use and alter it.
     * Original and altered files may not be (re)distributed without permission.
     */
    {literal}
    var fields={
    {/literal}
            "product_name":"",
            "product_name_xx":"{l s='Replace xx with language abbreviation (en/de/nl/...)' mod='directlabelprintproduct'}",
            "description":"",
            "description_xx":"{l s='Replace xx with language abbreviation (en/de/nl/...)' mod='directlabelprintproduct'}",
            "description_short":"",
            "description_short_xx":"{l s='Replace xx with language abbreviation (en/de/nl/...)' mod='directlabelprintproduct'}",
            "id_product":"",
            "id_combination":"",
            "all_attributes":"",
            "all_attributes_multiple_lines":"",
            "all_attributes_values_only":"",
            "all_features":"",
            "all_features_values_only":"",
            "all_features_multiple_lines":"",
            "isbn":"",
            "ean13":"",
            "upc":"",
            "manufacturer_name":"",
            "reference":"",
            "location":"",
            "width":"",
            "height":"",
            "depth":"",
            "weight":"",
            "on_sale":"",
            "supplier_reference":"",
            "supplier_name":"",
            "warehouse_location":"",
            "minimal_quantity":"",
            "price":"",
            "price_incl_tax":"",
            "discount_price_incl_tax":"",
            "discount_incl_tax":"",
            "discount_percentage":"",
            "wholesale_price":"",
            "unity":"",
            "unit_price_ratio":"",
            "unit_price_incl_tax":"",
            "unit_price_excl_tax":"",
            "condition":"",
            "date_add":"",
            "date_upd":"",
            "pack_stock_type":"",
            "product_website_url":"",
            "ordered_quantity":"",
            "order_reference":"",
            "order_id":"",
            "order_line_nr":"",
            "order_line_count":"",
            "order_line_total_weight":"",
            "current_date":"",
            "label_per_product_number":"",
            "label_per_product_count":""
    {literal}
    };
    {/literal}

    {literal}
    var fields_order={
        {/literal}
        "ordered_quantity":"",
        "order_reference":"",
        "order_id":"",
        "order_line_nr":"",
        "order_line_count":"",
        "order_line_total_weight":"",
        "ShippingAddress":"",
        "shipping_method":"",
        "order_reference":"",
        "order_id":"",
        "invoice_number":"",
        "tracking_number":"",
        "first_order":"",
        "telephone_invoice":"",
        "telephone_delivery":"",
        "mobile_invoice":"",
        "mobile_delivery":"",
        "vat_number":"",
        "customer_email":"",
        "address_shipping_company":"",
        "address_shipping_name":"",
        "address_shipping_address1":"",
        "address_shipping_address2":"",
        "address_shipping_postcode":"",
        "address_shipping_city":"",
        "address_shipping_state":"",
        "address_shipping_other":"",
        "address_shipping_country":"",
        "address_billing_company":"",
        "address_billing_name":"",
        "address_billing_address1":"",
        "address_billing_address2":"",
        "address_billing_postcode":"",
        "address_billing_city":"",
        "address_billing_state":"",
        "address_billing_other":"",
        "address_billing_country":""
        {literal}
    };
    {/literal}

    if(typeof dlpa_controller_url!="undefined"){
        fields=Object.assign(fields,fields_order);
    }

    if ("{$dymosoftwaretype|escape:'html':'UTF-8'}"!="true"){
        fields["cover_image_url"]="{l s='Cover Image of product' mod='directlabelprintproduct'}";
        fields["image_x_url"]="{l s='Replace x with number' mod='directlabelprintproduct'}";
    }

    var printertyperror="{$printertypeerror|escape:'html':'UTF-8'}";
    var printertypedymo={$printertype|escape:'html':'UTF-8'};
    var selectedDymoIndex_dlpp={$selectedDymoIndex|escape:'html':'UTF-8'};//SDI

    var dymoPrinterIndex_dlpp={$dymoPrinterIndex|escape:'html':'UTF-8'};

    var choosenTemplate="{$choosenTemplate|escape:'html':'UTF-8'}";
    var choosenTemplateName="{$choosenTemplateName|escape:'html':'UTF-8'}";
    var choosenVariantIndex="{$choosenVariantIndex|escape:'html':'UTF-8'}";

    function adjustDisplay(){
        var menuButtons=$("#generalSettingsButton,#manageTemplatesButton, #advancedSettingsButton,#helpButton,#recommendedModulesButton");

        menuButtons.on("click",function(ev){
            menuButtons.removeClass("selected_button");
            $( ev.delegateTarget).addClass("selected_button");
            $(".generalSettingsArea,.advancedSettingsArea, .manageTemplatesArea, .helpArea, .recommendedModulesArea").hide();
            $("."+ev.delegateTarget.id.replace("Button","Area")).show();
            $(".informationMessage").hide();
        });

        var openArea="{$openSettingsArea|escape:'html':'UTF-8'}";
        if (openArea.length>0){
            $("#"+openArea+"Button").trigger("click");
            $(".informationMessage").show();
        }

        if (printertyperror.length>4){
            //CHECK IF DYMO LABEL
            var printers = dymo.label.framework.getPrinters();
            if (printers.length >0) {
                document.getElementById('printertype_on').checked=true;
            }
            $("#dymoSettings,#barcodePanel").remove();
            $("#dymoSoftwareType").hide();
        }
        else if (printertypedymo){
            $(".otherPrinterArea").remove();
            var printers = dymo.label.framework.getPrinters();
            if (printers.length >0) {
                menuButtons.show();

                //Add Row List
                var columns=$("#row_list ul");
                var field_keys=Object.keys(fields);
                var items_per_column=Math.round(field_keys.length/columns.length);
                for (var i=0;i<columns.length;i++){
                    for (var j=0;(i*items_per_column+j)<field_keys.length && j<items_per_column;j++){
                        var fieldkey=field_keys[(i*items_per_column)+j];
                        if (fields[fieldkey].length>0){
                            $(columns[i]).append("<li title=\""+fields[fieldkey]+"\">"+fieldkey+" [<i class=\"icon-info\"></i>]</li>");
                        }else{
                            $(columns[i]).append("<li>"+fieldkey+"</li>");
                        }

                    }
                }


                //Dymo Printers Lists //SDI
                var printers = dymo.label.framework.getPrinters();

                var j=0;
                var optionsCode="";
                for (var i = 0; i < printers.length; ++i) {
                    var printer = printers[i];
                    if (printer.printerType == "LabelWriterPrinter") {
                        $("#dymo_select select").append($('<option>', {
                            value: j,
                            text: printer.name,
                            selected: (j==selectedDymoIndex_dlpp)
                        }));
                        j++;
                    }
                }
            }else{
                $("#noDymoFoundError").show();
            }

            if ("{$dymosoftwaretype|escape:'html':'UTF-8'}"=="true"){
                $(".dymoLabelInstructions").remove();
            }else{
                $(".dymoConnectInstructions").remove();
            }

            $("#downloadDymoTemplate").on("click",function() {
                var url=dlpp_controller_url + "&action=getTemplateOfId&template_id="+choosenTemplate+"&cache="+Math.round(Math.random()*1000);
                window.open(url, "_blank");
            });
        }
        else{
            $(".dymoPrinterArea").remove();
            $("#dymoSoftwareType").hide();
            menuButtons.show();

            if (choosenTemplate.length!=0)
                generateConfigurationScreen();

            var field_keys=Object.keys(fields);
            for (var i=0;i<field_keys.length;i++){
                var key=field_keys[i];
                $("#summernote_fields_insert").append("<option value=\""+key+"\" title=\""+fields[key]+"\">[["+key+"]]</option>");
                if (document.getElementById("summernote_barcode_insert"))
                    $("#summernote_barcode_insert").append("<option value=\""+key+"\" title=\""+fields[key]+"\">[["+key+"]]</option>");
                if (document.getElementById("summernote_qrcode_insert"))
                    $("#summernote_qrcode_insert").append("<option value=\""+key+"\" title=\""+fields[key]+"\">[["+key+"]]</option>");
            }

            $("#summernote_fields_insert").on("change",insertTextField.bind(this,fields));
            $("#summernote_barcode_insert").on("change",insertBarcodeField.bind(this,fields));
            $("#summernote_qrcode_insert").on("change",insertQRField.bind(this,fields));

        }


        if (choosenTemplate.length==0){
            $(".editTemplate").remove();
        }else{
            if (!choosenTemplate.startsWith("DEFAULT")) {
                $("#removeCurrentTemplate").show();
                $("#removeCurrentTemplate").on("click", function () {
                    $("#removeTemplateForm").submit();
                });
            }
            $("#chooseVariantDiv,#chooseTemplateDiv").css("width","49%");
            $("#chooseVariantDiv").css("position","relative");
            $("#chooseVariantDiv").css("float","right");
            $("#chooseVariant,#chooseVariantDiv").css("display","block");
        }

        if(choosenVariantIndex!="1"){
            $("#removeCurrentVariant").show();
            $("#removeCurrentVariant").on("click",function() {
                $("#removeVariantForm").submit();
            });
        }

        //LOAD TEMPLATE LIST
        function loadAvailableTemplateList() {
            var dropdown=$($(".chooseTemplateArea .dropdown")[0]);
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    var list = JSON.parse(xhttp.responseText);
                    dropdown.empty();
                    var option = document.createElement("option");
                    option.innerText = "Choose Template...";
                    option.disabled=true;
                    option.selected=choosenTemplate.length==0;
                    option.hidden=true;
                    dropdown.append(option);
                    var keys = Object.keys(list);
                    var products_group=document.createElement("optgroup");
                    products_group.label="{l s='Products' mod='directlabelprintproduct'}";
                    var categories_group=document.createElement("optgroup");
                    categories_group.label="{l s='Categories' mod='directlabelprintproduct'}";
                    var alreadySelected=(choosenTemplate.length==0);
                    keys.forEach(function (key) {
                        var option = document.createElement("option");
                        option.value = key;
                        option.selected=(key==choosenTemplate);
                        alreadySelected=alreadySelected||(key==choosenTemplate);
                        option.innerText = list[key];
                        if (key.startsWith("product-")){
                            products_group.appendChild(option);
                        }else if (key.startsWith("category-")){
                            categories_group.appendChild(option);
                        }else{
                            dropdown.append(option);
                        }
                    });
                    if (!alreadySelected){
                        var option = document.createElement("option");
                        option.value = choosenTemplate;
                        option.innerText = choosenTemplateName+"*";
                        option.selected=true;
                        if (choosenTemplate.startsWith("product-")){
                            products_group.appendChild(option);
                        }else if (choosenTemplate.startsWith("category-")) {
                            categories_group.appendChild(option);
                        }
                        $("#downloadDymoTemplate").hide(); //Can't download new
                    }
                    if (products_group.children.length>0) {
                        dropdown.append(products_group);
                    }
                    if (categories_group.children.length>0) {
                        dropdown.append(categories_group);
                    }
                    var option = document.createElement("option");
                    option.value = "NEW";
                    option.innerText = "New Template...";
                    option.style="background-color: white";
                    dropdown.append(option);
                }
            };
            xhttp.open("GET", dlpp_controller_url + "&action=ListTemplates&cache="+Math.round(Math.random()*1000), true);
            xhttp.send();
        }
        loadAvailableTemplateList();


        $(".chooseTemplateArea .dropdown").change(function() {
            var value=$( this ).val();
            if (value=="NEW"){
                $(".chooseTemplateArea input[type=text]").show();
                $(".chooseTemplateArea input[type=text]").focus();
                $(".chooseTemplateArea .dropdown").hide();
            }else{
                $("#choosenTemplateInput").val(value);
                $("#choosenTemplateNameInput").val($('.chooseTemplateArea .dropdown').find(":selected").text());
                $("#choosenTemplateForm").submit();
            }
        });

        if(choosenTemplate.length!=0) {
            $('.chooseVariantArea .dropdown')[0].selectedIndex = parseInt(choosenVariantIndex) - 1;
            $(".chooseVariantArea .dropdown").change(function () {
                $("#choosenVariantIndex").val($('.chooseVariantArea .dropdown')[0].selectedIndex + 1);
                $("#choosenTemplateForm").submit();
            });
        }


        $(".chooseTemplateArea input[type=text]").keydown(function() {
            if (typeof templateSearchTimer!= "undefined"){
                clearTimeout(templateSearchTimer);
            }
            templateSearchTimer=setTimeout(function() {
                var query=$(".chooseTemplateArea input[type=text]").val();
                var searchresultselect=$(".chooseTemplateArea .search");
                searchresultselect.show();
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {
                    if (this.readyState == 4 && this.status == 200) {
                        var list = JSON.parse(xhttp.responseText);
                        searchresultselect.empty();
                        var keys = Object.keys(list);
                        var products_group=document.createElement("optgroup");
                        products_group.label="{l s='Products' mod='directlabelprintproduct'}";
                        var categories_group=document.createElement("optgroup");
                        categories_group.label="{l s='Categories' mod='directlabelprintproduct'}";
                        keys.forEach(function (key) {
                            var option = document.createElement("option");
                            option.value = key;
                            option.innerText = list[key];
                            if (key.startsWith("product-")){
                                products_group.appendChild(option);
                            }else if (key.startsWith("category-")){
                                categories_group.appendChild(option);
                            }else{
                                searchresultselect.append(option);
                            }
                        });
                        if (products_group.children.length>0) {
                            searchresultselect.append(products_group);
                        }
                        if (categories_group.children.length>0) {
                            searchresultselect.append(categories_group);
                        }
                    }
                };
                xhttp.open("GET", dlpp_controller_url + "&action=searchProductOrCategory&query="+query, true);
                xhttp.send();
            },500);
        });

        $(".chooseTemplateArea .search").change(function() {
            var value = $(this).val();
            $("#choosenTemplateInput").val(value);
            $("#choosenTemplateNameInput").val($('.chooseTemplateArea .search').find(":selected").text());
            $("#choosenTemplateForm").submit();
        });

        $(".module_directlabelprintproduct").hide();

    }

    if (window.attachEvent) {
        window.attachEvent('onload', adjustDisplay);
    } else {
        if (window.onload) {
            var curronload = window.onload;
            var newonload = function(curronload,evt) {
                curronload(evt);
                adjustDisplay(evt);
            }.bind(this,curronload);
            window.onload = newonload;
        } else {
            window.onload = adjustDisplay;
        }
    }

    function processCSVFile(){
        var input=$("#csv_file input[type='file']");
        var file=input[0].files[0];

        var r = new FileReader();
        r.onload = function(e) {
            console.log("content size:"+e.target.result.length);

            var delimiter=",";
            if (e.target.result.indexOf(";")>-1){
                delimiter=";";
            }
            if (e.target.result.indexOf("\t")>-1){
                delimiter="\t";
            }
            console.log("delimiter:"+delimiter);
            var items=[];
            var lines=e.target.result.replace("\"","").split("\n");
            console.log("line count:"+lines.length);
            for (var i=0;i<lines.length;i++) {
                var line = lines[i];
                var parts = line.split(delimiter);
                console.log("part count:"+parts.length);
                if (parts.length>1) {
                    var reference=parts[0].trim();
                    var quantity=parseInt(parts[1]);
                    console.log("line:"+reference+"-"+quantity);
                    if (reference.length>0 && quantity>0) {
                        items[items.length]={
                            reference: reference,
                            quantity: quantity
                        };
                    }
                }
            }

            if (items.length==0) {
                alert("{l s='Can\'t find items in file. Please check file.' mod='directlabelprintproduct'}");
            }
            console.log("to print:"+JSON.stringify(items));
            function processNext(i) {
                if (i<items.length)
                    printLabelFromBarcode(items[i].reference,items[i].quantity,(i==items.length-1),processNext.bind(this,i+1));
            }
            processNext(0);
        }
        r.readAsText(file);
    }

    var barcode_sample_url = "{$barcode_sample_url|escape:'html':'UTF-8'}";
    var qrcode_sample_url="{$qrcode_sample_url|escape:'html':'UTF-8'}";
    var image_sample_url="{$image_sample_url|escape:'html':'UTF-8'}";
</script>

<style>
    /*#barcodePanel,#dymoPanel,#otherPrinterPanel,#noDymoFoundError, #dymoSettings, #batchPanel, #otherSettings{
        display:none;
    }*/

    #batchPanel{
        display:none;
    }

    #otherSettings p{
        padding-bottom:20px;
    }

</style>

<div id="ConfigTabBar">
    <button class="btn btn-default selected_button" id="generalSettingsButton"><i class="icon-gear" ></i><br/>{l s='General Settings' mod='directlabelprintproduct'}</button>
    <button class="btn btn-default" id="manageTemplatesButton"><i class="icon-edit" ></i><br/>{l s='Manage Templates' mod='directlabelprintproduct'}</button>
    <button class="btn btn-default" id="advancedSettingsButton"><i class="icon-gear" ></i><br/>{l s='Advanced Settings' mod='directlabelprintproduct'}</button>
    <button class="btn btn-default" id="helpButton"><i class="icon-question" ></i><br/>{l s='Help & Rating' mod='directlabelprintproduct'}</button>
    <button class="btn btn-default" id="recommendedModulesButton"><i class="icon-puzzle-piece" ></i><br/>{l s='Recommended Modules' mod='directlabelprintproduct'}</button>
</div>

<ps-alert-error id="noDymoFoundError">{l s='The module can\'t find a DYMO Printer.' mod='directlabelprintproduct'}<br/>
    {l s='Please make sure:' mod='directlabelprintproduct'}<br/>
    <ul>
        <li>{l s='You have a DYMO Printer. If not, then please select "Other" below.' mod='directlabelprintproduct'}</li>
        <li>{l s='The latest DYMO Label or Dymo Connect Software is installed, to be sure we recommend to reinstall from' mod='directlabelprintproduct'} <a href="https://www.dymo.com/on/demandware.store/Sites-dymo-Site/en_US/Support-user-guides" target="_blank">{l s='this website' mod='directlabelprintproduct'}</a>.</li>
        <li>{l s='Still not working? Then click on the Dymo icon in system tray and choose "Diagnose". That should tell what is wrong.' mod='directlabelprintproduct'}</li>
        <li><a href="https://addons.prestashop.com/contact-form.php?id_product=26296" target="_blank">{l s='Contact us if you still have problems getting it working.' mod='directlabelprintproduct'}</a></li>
    </ul>
</ps-alert-error>

{if isset($error) }
    <ps-alert-error class="informationMessage">{$error|escape:'html':'UTF-8'}</ps-alert-error>
{/if}
{if isset($success) }
    <ps-alert-success class="informationMessage">{$success|escape:'html':'UTF-8'}</ps-alert-success>
{/if}

<ps-panel id="batchPanel" header="CSV {l s='Batch Print' mod='directlabelprintproduct'}" img="{$iconurl|escape:'html':'UTF-8'}">
    <form class="form-horizontal" method="POST" enctype="multipart/form-data" >
        <ps-input-upload id="csv_file" name="csvfile" label="CSV {l s='File' mod='directlabelprintproduct'}" size="20" required-input="true" hint="{l s='CSV File to process' mod='directlabelprintproduct'}" fixed-width="300"></ps-input-upload>
        <input type="hidden" name="upload" value="upload"/>

        <button class="btn btn-default" type="button" style="margin-left:25%" OnClick="processCSVFile();">
            {l s='Print Items' mod='directlabelprintproduct'}
        </button>
    </form>
    <ps-panel-divider></ps-panel-divider>
    {l s='Select a CSV file with:' mod='directlabelprintproduct'}
    <ul>
        <li>{l s='Reference / Barcode / product_id (first column)' mod='directlabelprintproduct'}</li>
        <li>{l s='Number of labels (second column)' mod='directlabelprintproduct'}</li>
    </ul><br/>
    {l s='It will then print these labels in the specified numbers.' mod='directlabelprintproduct'}
</ps-panel>

<ps-panel id="barcodePanel" header="{l s='Scan & Print Label' mod='directlabelprintproduct'}" img="{$iconurl|escape:'html':'UTF-8'}" class="generalSettingsArea">
    <form class="form-horizontal" id="scanandprint" onsubmit="printLabelFromBarcode($($('#scanandprint input')[0]).val());return false;">

        {l s='Enter a product identification to print label of that product' mod='directlabelprintproduct'}(EAN / UPC / {l s='Reference' mod='directlabelprintproduct'} / ...)<br/>&nbsp;<br/>

        <ps-input-text id="barcode" name="barcode" label="EAN / UPC / {l s='Reference' mod='directlabelprintproduct'}" size="20" hint="{l s='Enter Barcode' mod='directlabelprintproduct'}" fixed-width="300"></ps-input-text>

        <button class="btn btn-default" type="submit" style="margin-left:25%">
            {l s='Print Label' mod='directlabelprintproduct'}
        </button>
        <button class="btn btn-default" type="button" OnClick="document.getElementById('batchPanel').style.display='block'" style="margin-left:40%">
            CSV {l s='Batch Print' mod='directlabelprintproduct'}
        </button><br/>
        &nbsp;<br/>
        <ps-alert-warn>{l s='Tip! This module integrates with:' mod='directlabelprintproduct'} <a href="https://addons.prestashop.com/oo/stock-supplier-management/18006-scan-spray-product-with-ean13.html" target="_blank">Scan Spray product with EAN13 Module</a>.</ps-alert-warn>

    </form>
</ps-panel>

<ps-panel header="{l s='Printer Type' mod='directlabelprintproduct'}" img="{$iconurl|escape:'html':'UTF-8'}" class="generalSettingsArea">
    {if isset($printertypeerror) }
        <ps-alert-error>{$printertypeerror|escape:'html':'UTF-8'}</ps-alert-error>
    {/if}

    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" >
        <ps-switch name="printertype" label="{l s='Printer Type' mod='directlabelprintproduct'}" yes="DYMO" no="{l s='Other' mod='directlabelprintproduct'}" active="{$printertype|escape:'html':'UTF-8'}" help="{l s='DYMO LabelWriter or Other (Label) Printer.' mod='directlabelprintproduct'}"></ps-switch>
        <ps-switch id="dymoSoftwareType" name="dymosoftwaretype" label="Dymo Software" yes="Connect" no="Label" active="{$dymosoftwaretype|escape:'html':'UTF-8'}" help="{l s='Are you using Dymo Connect or Dymo Label Software?' mod='directlabelprintproduct'}"></ps-switch>

        <input type="hidden" name="printertype_submit" value="printertype_submit"/>

        <ps-panel-footer>
            <ps-panel-footer-submit title="{l s='Save' mod='directlabelprintproduct'}" icon="process-icon-save" direction="right" name="submitPanel"></ps-panel-footer-submit>
        </ps-panel-footer>

    </form>
</ps-panel>
<div id="chooseVariantDiv">
    <ps-panel id="chooseVariant" header="{l s='Choose Variant' mod='directlabelprintproduct'}" img="{$iconurl|escape:'html':'UTF-8'}" class="manageTemplatesArea editTemplate">
        <div class="chooseVariantArea">
            <select class="dropdown">
                <option>{l s='Default' mod='directlabelprintproduct'}</option>
                <option>{l s='Variant' mod='directlabelprintproduct'} 1 {$statusVariant1|escape:'html':'UTF-8'}</option>
                <option>{l s='Variant' mod='directlabelprintproduct'} 2 {$statusVariant2|escape:'html':'UTF-8'}</option>
            </select>
        </div>
        <button id="removeCurrentVariant"><i class="icon-unlink"></i> {l s='Remove Variant' mod='directlabelprintproduct'}</button>
    </ps-panel>
</div>

<div id="chooseTemplateDiv">
<ps-panel id="chooseTemplate" header="{l s='Choose Template' mod='directlabelprintproduct'}" img="{$iconurl|escape:'html':'UTF-8'}" class="manageTemplatesArea chooseTemplate">
    <div class="chooseTemplateArea">
        <select class="dropdown">
        </select>
        <input type="text" placeholder="{l s='Enter your search for products / categories.' mod='directlabelprintproduct'}"/>
        <select class="search" multiple size="15"></select>
    </div>
    <button id="removeCurrentTemplate"><i class="icon-unlink"></i> {l s='Remove Template' mod='directlabelprintproduct'}</button>
</ps-panel>
</div>


<ps-panel id="dymoPanel" header="{l s='Upload Label Template for' mod='directlabelprintproduct'} '{$choosenTemplateName|escape:'html':'UTF-8'}'" img="{$iconurl|escape:'html':'UTF-8'}" class="manageTemplatesArea editTemplate dymoPrinterArea">



    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" >

        <ps-input-upload id="upload_file" name="filelabel" label="{l s='Label Template' mod='directlabelprintproduct'}" size="20" required-input="true" hint="{l s='Upload Template File' mod='directlabelprintproduct'}" fixed-width="300"></ps-input-upload>
        <input type="hidden" name="upload" value="upload"/>
        <input type="hidden" name="choosenVariantIndex" value="{$choosenVariantIndex|escape:'html':'UTF-8'}"/>
        <input type="hidden" name="settingsArea" value="manageTemplates"/>
        <input type="hidden" name="choosenTemplate" value="{$choosenTemplate|escape:'html':'UTF-8'}"/>
        <input type="hidden" name="choosenTemplateName" value="{$choosenTemplateName|escape:'html':'UTF-8'}"/>

        <ps-input-text id="choosenVariantName" name="choosenVariantName" label="{l s='Variant Description' mod='directlabelprintproduct'}" size="20" hint="{l s='Name to identify the template variant.' mod='directlabelprintproduct'}"  fixed-width="50" value="{$choosenVariantName|escape:'html':'UTF-8'}"></ps-input-text>

        <button class="btn btn-default" type="submit" style="margin-left:25%">
            <i class="icon-upload-alt" ></i>
            {l s='Upload new template' mod='directlabelprintproduct'}
        </button>
    </form>

    <button id="downloadDymoTemplate"><i class="icon-download"></i> {l s='Download Current Template' mod='directlabelprintproduct'}</button>

    <ps-panel-divider></ps-panel-divider>


                    <div class="dymoLabelInstructions">
                        <p>
                        {l s='Upload a file from the DYMO Label Software as template.' mod='directlabelprintproduct'}<br/>
                        {l s='This template will determine size and layout of printed labels.' mod='directlabelprintproduct'}<br/>
                        {l s='There is an default template inside module (Library Barcode - 3/4" x 2 1/2").' mod='directlabelprintproduct'}<br/>
                        {l s='You only need to change template if you need another size or need another layout.' mod='directlabelprintproduct'}
                        </p>

                        <ps-alert-warn>
                            <b>{l s='Are you using Dymo Connect Software?' mod='directlabelprintproduct'}</b><br/>
                            {l s='Then go to "General Settings" and change "Dymo Software" to "Connect".' mod='directlabelprintproduct'} "Connect".
                        </ps-alert-warn>
                        <p>
                           <a href="{$imgfolder|escape:'html':'UTF-8'}DymoLabelSoftwareConfig.png" target="_blank"> <img src="{$imgfolder|escape:'html':'UTF-8'}DymoLabelSoftwareConfig.png" height="220" style="float:right"/></a>
                            <b>{l s='TEMPLATE INSTRUCTIONS' mod='directlabelprintproduct'}</b><br/>
                            {l s='This Dymo file needs to contain text/barcode objects with a correct "reference name" in "properties".' mod='directlabelprintproduct'}<br/>
                            {l s='This "reference name" will determine which data will be put inside the object.' mod='directlabelprintproduct'}<br/>
                            {l s=' You can edit this reference name inside the Dymo Label software.' mod='directlabelprintproduct'}<br/>
                        </p>
                        <p>
                            {l s='Normally the module erases the object text in the template and replaces completely with product data.' mod='directlabelprintproduct'}<br/>
                            {l s='So for example "Title:" with reference name "product_name" will become "my product name".' mod='directlabelprintproduct'}<br/>
                            {l s='However, if you add (*) to the sample data it will add product data on that location.' mod='directlabelprintproduct'}<br/>
                            {l s='So for example "Title: (*)" with reference name "product_name" will become "Title: my product name".' mod='directlabelprintproduct'}<br/>
                        </p>
                            <p><a href="http://somup.com/cbnFD285w" target="_blank"><i class="icon-youtube-play"></i> <b>{l s='Video Tutorial' mod='directlabelprintproduct'}</b></a></p>
                            <p><a href="{$sampletemplateurl_label|escape:'html':'UTF-8'}" download target="_blank"><i class="icon-download"></i> <b>{l s='Sample Template (Library Barcode - 3/4" x 2 1/2")' mod='directlabelprintproduct'}</b></a></p>
                            &nbsp;<br/><p>
                            {l s='The following reference names are supported:' mod='directlabelprintproduct'}
                            </p>
                    </div>

                    <div class="dymoConnectInstructions">

                        <ps-alert-warn>
                            <b>{l s='Are you using Dymo Label Software?' mod='directlabelprintproduct'}</b><br/>
                            {l s='Then go to "General Settings" and change "Dymo Software" to "Label".' mod='directlabelprintproduct'} "Label".
                        </ps-alert-warn>

                        <a href="{$imgfolder|escape:'html':'UTF-8'}DymoConnectSoftwareConfig.png" target="_blank"> <img src="{$imgfolder|escape:'html':'UTF-8'}DymoConnectSoftwareConfig.png" height="220" style="float:right"/></a>

                        <p>
                            {l s='Upload a file from the DYMO Connect Software as template.' mod='directlabelprintproduct'}<br/>
                            {l s='This template will determine size and layout of printed labels.' mod='directlabelprintproduct'}<br/>
                            {l s='There is an default template inside module (Library Barcode - 3/4" x 2 1/2").' mod='directlabelprintproduct'}<br/>
                            {l s='You only need to change template if you need another size or need another layout.' mod='directlabelprintproduct'}
                        </p>

                        <p>
                            <b>{l s='TEMPLATE INSTRUCTIONS' mod='directlabelprintproduct'}</b><br/>
                            {l s='This Dymo file needs to contain text/barcode objects containing [[field_name]].' mod='directlabelprintproduct'}<br/>
                            {l s='Location where you put [[field_name]] content will be replaced with fields\' content.' mod='directlabelprintproduct'}<br/>
                            {l s='See the image on the right on how this works.' mod='directlabelprintproduct'}<br/>
                        </p>

                        <!--<p><a href="" target="_blank"><i class="icon-youtube-play"></i> <b>Video Tutorial</b></a></p>-->
                        <p><a href="{$sampletemplateurl_connect|escape:'html':'UTF-8'}" download target="_blank"><i class="icon-download"></i> <b>{l s='Sample Template (Library Barcode - 3/4" x 2 1/2")' mod='directlabelprintproduct'}</b></a></p>
                        &nbsp;<br/><p>
                            {l s='The following reference names are supported:' mod='directlabelprintproduct'}
                        </p>

                    </div>


                    <div style="display:table-row" id="row_list">
                        <ul style="display:table-cell">
                        </ul>
                        <ul style="display:table-cell;padding-left:30px">
                        </ul>
                        <ul style="display:table-cell;padding-left:30px">
                        </ul>
                        <ul style="display:table-cell;padding-left:30px">
                        </ul>
                        <ul style="display:table-cell;padding-left:30px">
                        </ul>
                    </div><br/>



</ps-panel>

<ps-panel id="dymoSettings" header="{l s='Dymo Settings' mod='directlabelprintproduct'}" img="{$iconurl|escape:'html':'UTF-8'}" class="generalSettingsArea dymoPrinterArea">
    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" >
        <!--//SDI-->
        {l s='If you have multiple Dymo printer you can select printer here:' mod='directlabelprintproduct'}

        <ps-select id="dymo_select" label="{l s='Printer Select' mod='directlabelprintproduct'}" name="selectedDymoIndex" chosen='false'>
            <option value="1000">{l s='Please select printer' mod='directlabelprintproduct'}</option>
        </ps-select>

        {l s='If you have a duo/twin Dymo or two Dymo printers you can use this setting to choose roll.' mod='directlabelprintproduct'}<br/>&nbsp;<br/>

        <ps-switch name="dymoPrinterIndex" label="{l s='Which Dymo Roll' mod='directlabelprintproduct'}" yes="{l s='right' mod='directlabelprintproduct'}" no="{l s='left' mod='directlabelprintproduct'}" active="{$dymoPrinterIndexActive|escape:'html':'UTF-8'}"></ps-switch>

        <input type="hidden" name="dymoSettings" value="dymoSettings"/>&nbsp;<br/>

        <ps-panel-footer>
            <ps-panel-footer-submit title="{l s='Save' mod='directlabelprintproduct'}" icon="process-icon-save" direction="right" name="submitPanel"></ps-panel-footer-submit>
        </ps-panel-footer>

    </form>
</ps-panel>

<ps-panel id="otherPrinterPanel" header="{l s='Change Label Template for' mod='directlabelprintproduct'} '{$choosenTemplateName|escape:'html':'UTF-8'}'" img="{$iconurl|escape:'html':'UTF-8'}" class="manageTemplatesArea editTemplate otherPrinterArea">
    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" OnSubmit="copyLabelContent()">
        <ps-input-text id="choosenVariantName" name="choosenVariantName" label="{l s='Variant Name' mod='directlabelprintproduct'}" size="20" hint="{l s='Name to identify the template variant.' mod='directlabelprintproduct'}"  fixed-width="50" value="{$choosenVariantName|escape:'html':'UTF-8'}"></ps-input-text>
        <input type="hidden" name="choosenVariantIndex" value="{$choosenVariantIndex|escape:'html':'UTF-8'}"/>

        <ps-input-text id="width_input" name="width_input" label="{l s='Label Width' mod='directlabelprintproduct'}" size="20" hint="{l s='Enter width of label used.' mod='directlabelprintproduct'} "  help="{l s='Please enter whole numbers only (no dots, commas or text). The unit doesn\'t matter, the printer decides the actual size.' mod='directlabelprintproduct'}" fixed-width="50" value="{$width_input|escape:'html':'UTF-8'}"></ps-input-text>
        <ps-input-text id="height_input" name="height_input" label="{l s='Label Height' mod='directlabelprintproduct'}" size="20" hint="{l s='Enter height of label used.' mod='directlabelprintproduct'}" help="{l s='Please enter whole numbers only (no dots, commas or text). The unit doesn\'t matter, the printer decides the actual size.' mod='directlabelprintproduct'}" fixed-width="50" value="{$height_input|escape:'html':'UTF-8'}"></ps-input-text>

        <ps-alert-warn><b>{l s='Important! Please make sure "Paper Size" in your printer settings / preferences is set correctly.' mod='directlabelprintproduct'}</b></ps-alert-warn>

        <ps-switch id="rotate_image" name="rotate_image" label="{l s='Rotate Label' mod='directlabelprintproduct'}"  hint="{l s='This allows to change orientation of print. If YES then label is rotated 90 degrees.' mod='directlabelprintproduct'}" yes="{l s='Yes' mod='directlabelprintproduct'}" no="{l s='No' mod='directlabelprintproduct'}" active="{$rotate_image|escape:'html':'UTF-8'}"></ps-switch>

        &nbsp;<br/>

        <ps-form-group label="{l s='Label Template' mod='directlabelprintproduct'}"  hint="{l s='Design your label and decide what to show.' mod='directlabelprintproduct'}">
            <div id="editer_header_buttons">
                <select id="summernote_fields_insert" style="display:inline-block;width:150px">
                    <option value="header">{l s='Add Field' mod='directlabelprintproduct'}</option>
                </select>
                <select id="summernote_barcode_insert" style="display:inline-block;width:150px">
                    <option value="header">{l s='Add Barcode' mod='directlabelprintproduct'}</option>
                    <option value="">{l s='Custom Text' mod='directlabelprintproduct'}</option>
                </select>
                <select id="summernote_qrcode_insert" style="display:inline-block;width:150px">
                    <option value="header">{l s='Add QR-code' mod='directlabelprintproduct'}</option>
                    <option value="">{l s='Custom Text' mod='directlabelprintproduct'}</option>
                </select>
                <a href="javascript:void(0)" id="summernote_image_insert" class="image_add" onclick="insertProductImage();">{l s='Add Image' mod='directlabelprintproduct'}</a>
                <a href="javascript:void(0)" class="editor_print" onclick="printTemplate()"><img src="{$printicon|escape:'html':'UTF-8'}"/></a>
            </div>

            <!-- PLEASE READ - field "label_content" below is HTML-based template - can't be escape or it won't work -->
            <div id="summernote">{$label_content}</div>

        </ps-form-group>


        <input type="hidden" id="label_content" name="label_content" value="{$label_content|escape:'html':'UTF-8'}"/>
        <input type="hidden" name="generic_label_submit" value="generic_label_submit"/>
        <input type="hidden" name="settingsArea" value="manageTemplates"/>
        <input type="hidden" name="choosenTemplate" value="{$choosenTemplate|escape:'html':'UTF-8'}"/>
        <input type="hidden" name="choosenTemplateName" value="{$choosenTemplateName|escape:'html':'UTF-8'}"/>

        <ps-panel-footer>
            <ps-panel-footer-submit title="{l s='Save' mod='directlabelprintproduct'}" icon="process-icon-save" direction="right" name="submitPanel"></ps-panel-footer-submit>
        </ps-panel-footer>

    </form>

</ps-panel>

<ps-panel id="otherSettings" header="{l s='Other Settings' mod='directlabelprintproduct'}" img="{$iconurl|escape:'html':'UTF-8'}" class="advancedSettingsArea">
    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" >

        <p>
        <ps-switch name="autoGenerateReference" label="{l s='Autogenerate Reference' mod='directlabelprintproduct'}" yes="{l s='Yes' mod='directlabelprintproduct'}" no="{l s='No' mod='directlabelprintproduct'}" active="{$autoGenerateReference|escape:'html':'UTF-8'}"  hint="{l s='Automatic generate product reference when not available.' mod='directlabelprintproduct'}"></ps-switch>

        <ps-input-text id="autoFirstReferenceCounter" name="autoFirstReferenceCounter" label="{l s='Reference Count Value' mod='directlabelprintproduct'}" size="13" hint="{l s='This is the next reference number.' mod='directlabelprintproduct'}" fixed-width="50" value="{$autoFirstReferenceCounter|escape:'html':'UTF-8'}"></ps-input-text>
        </p>
        <p>
        <ps-switch name="autoGenerateEAN" label="{l s='Autogenerate' mod='directlabelprintproduct'} EAN" yes="{l s='Yes' mod='directlabelprintproduct'}" no="{l s='No' mod='directlabelprintproduct'}" active="{$autoGenerateEAN|escape:'html':'UTF-8'}"  hint="{l s='Automatic generate when not available.' mod='directlabelprintproduct'}"></ps-switch>

        <ps-input-text id="autoGenerateEAN_StartValue" name="autoGenerateEAN_StartValue" label="{l s='Start Value' mod='directlabelprintproduct'} EAN" size="13" hint="{l s='Enter start of range' mod='directlabelprintproduct'}" fixed-width="50" value="{$autoGenerateEAN_StartValue|escape:'html':'UTF-8'}"></ps-input-text>

        <ps-input-text id="autoGenerateEAN_EndValue" name="autoGenerateEAN_EndValue" label="{l s='End Value' mod='directlabelprintproduct'} EAN" size="13" hint="{l s='Enter end of range' mod='directlabelprintproduct'}" fixed-width="50" value="{$autoGenerateEAN_EndValue|escape:'html':'UTF-8'}"></ps-input-text>
        </p>
        <p>
        <ps-switch name="autoGenerateUPC" label="{l s='Autogenerate' mod='directlabelprintproduct'} UPC" yes="{l s='Yes' mod='directlabelprintproduct'}" no="{l s='No' mod='directlabelprintproduct'}" active="{$autoGenerateUPC|escape:'html':'UTF-8'}"  hint="{l s='Automatic generate when not available.' mod='directlabelprintproduct'}."></ps-switch>

        <ps-input-text id="autoGenerateUPC_StartValue" name="autoGenerateUPC_StartValue" label="{l s='Start Value' mod='directlabelprintproduct'} UPC" size="13" hint="{l s='Enter start of range' mod='directlabelprintproduct'}" fixed-width="50" value="{$autoGenerateUPC_StartValue|escape:'html':'UTF-8'}"></ps-input-text>

        <ps-input-text id="autoGenerateUPC_EndValue" name="autoGenerateUPC_EndValue" label="{l s='End Value' mod='directlabelprintproduct'} UPC" size="13" hint="{l s='Enter end of range' mod='directlabelprintproduct'}" fixed-width="50" value="{$autoGenerateUPC_EndValue|escape:'html':'UTF-8'}"></ps-input-text>
        </p>
        <p>
        <ps-switch name="multipleLabelsPerProduct" label="{l s='Multiple Labels per Product' mod='directlabelprintproduct'}" yes="{l s='Yes' mod='directlabelprintproduct'}" no="{l s='No' mod='directlabelprintproduct'}" active="{$multipleLabelsPerProduct|escape:'html':'UTF-8'}" hint="{l s='Print multiple copies of label per product.' mod='directlabelprintproduct'}"></ps-switch>

        <ps-input-text id="multipleLabelsPerProduct_count" name="multipleLabelsPerProduct_count" label="{l s='Multiple Label Count / Field' mod='directlabelprintproduct'}" size="13" fixed-width="50" value="{$multipleLabelsPerProduct_count|escape:'html':'UTF-8'}"   hint="{l s='Enter number of labels or enter template field that contains count.' mod='directlabelprintproduct'}"></ps-input-text>
        </p>

        <input type="hidden" name="otherSettings" value="otherSettings"/>&nbsp;<br/>
        <input type="hidden" name="settingsArea" value="advancedSettings"/>

        <ps-panel-footer>
            <ps-panel-footer-submit title="{l s='Save' mod='directlabelprintproduct'}" icon="process-icon-save" direction="right" name="submitPanel"></ps-panel-footer-submit>
        </ps-panel-footer>

    </form>
</ps-panel>

<ps-panel id="helpPanel" header="{l s='Help & Rating' mod='directlabelprintproduct'}" img="{$iconurl|escape:'html':'UTF-8'}" class="helpArea">

    <table border="0" style="width:100%;height:100px;font-size:15px">
        <tr><td style="width:50%">
                <a href="https://addons.prestashop.com/contact-form.php?id_product=26296" target="_black">
                    <img src="{$imgfolder|escape:'html':'UTF-8'}email.png" width="70"/>
                    <img src="{$imgfolder|escape:'html':'UTF-8'}help.png" width="70"/>
                    <img src="{$imgfolder|escape:'html':'UTF-8'}chat.png" width="70"/><br/>
                    {l s='Need help? Have problem / complaint?' mod='directlabelprintproduct'}<br/> {l s='Any special needs?' mod='directlabelprintproduct'} <br/> <b>{l s='Please contact us, we are here to help.' mod='directlabelprintproduct'}</b>
                </a>
            </td><td>
                <a href="https://addons.prestashop.com/ratings.php" target="_black">
                    <img src="{$imgfolder|escape:'html':'UTF-8'}star.png" width="70"/>
                    <img src="{$imgfolder|escape:'html':'UTF-8'}thumbsup.png" width="70"/><br/>
                    {l s='Do you have everything running? And are you happy?' mod='directlabelprintproduct'}<br/> <b>{l s='Please rate / review this module.' mod='directlabelprintproduct'}</b>
                </a>
            </td>
        </tr>
    </table>

</ps-panel>

<ps-panel id="recommendedModulesPanel" header="{l s='Recommended Modules' mod='directlabelprintproduct'}" img="{$iconurl|escape:'html':'UTF-8'}" class="recommendedModulesArea">
    {include file='./modules.tpl'}
</ps-panel>

<form method="POST" enctype="multipart/form-data" id="choosenTemplateForm">
    <input type="hidden" name="settingsArea" value="manageTemplates"/>
    <input type="hidden" name="choosenTemplate" id="choosenTemplateInput" value="{$choosenTemplate|escape:'html':'UTF-8'}"/>
    <input type="hidden" name="choosenVariantIndex" id="choosenVariantIndex" value="1"/>
    <input type="hidden" name="choosenTemplateName" id="choosenTemplateNameInput" value="{$choosenTemplateName|escape:'html':'UTF-8'}"/>

</form>

<form method="POST" enctype="multipart/form-data" id="removeTemplateForm">
    <input type="hidden" name="settingsArea" value="manageTemplates"/>
    <input type="hidden" name="removeTemplate" value="{$choosenTemplate|escape:'html':'UTF-8'}"/>
    <input type="hidden" name="removeTemplateName" value="{$choosenTemplateName|escape:'html':'UTF-8'}"/>
    <input type="hidden" name="removeTemplateAction" value="yes"/>
</form>

<form method="POST" enctype="multipart/form-data" id="removeVariantForm">
    <input type="hidden" name="settingsArea" value="manageTemplates"/>
    <input type="hidden" name="removeVariantAction" value="yes"/>
    <input type="hidden" name="removeVariantIndex" id="removeVariantIndex" value="{$choosenVariantIndex|escape:'html':'UTF-8'}"/>
    <input type="hidden" name="choosenTemplate" id="choosenTemplateInput" value="{$choosenTemplate|escape:'html':'UTF-8'}"/>
    <input type="hidden" name="choosenVariantIndex" id="choosenVariantIndex" value="1"/>
    <input type="hidden" name="choosenTemplateName" id="choosenTemplateNameInput" value="{$choosenTemplateName|escape:'html':'UTF-8'}"/>

</form>

<script>

    riot.compile(function() {
        // here tags are compiled and riot.mount works synchronously
        var tags = riot.mount('*')
    })

</script>

