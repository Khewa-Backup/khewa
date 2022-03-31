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

    var fields=["product_name",
            "product_name_xx",
            "description",
            "description_xx",
            "description_short",
            "description_short_xx",
            "id_product",
            "id_combination",
            "all_attributes",
            "all_attributes_multiple_lines",
            "all_attributes_values_only",
            "isbn",
            "ean13",
            "upc",
            "manufacturer_name",
            "reference",
            "location",
            "width",
            "height",
            "depth",
            "weight",
            "on_sale",
            "supplier_reference",
            "supplier_name",
            "minimal_quantity",
            "price",
            "price_incl_tax",
            "discount_price_incl_tax",
            "discount_incl_tax",
            "discount_percentage",
            "wholesale_price",
            "unity",
            "unit_price_ratio",
            "unit_price_incl_tax",
            "unit_price_excl_tax",
            "condition",
            "date_add",
            "date_upd",
            "pack_stock_type",
            "product_website_url",
            "ordered_quantity",
            "order_reference",
            "order_id",
            "order_line_nr",
            "order_line_count",
            "order_line_total_weight",
            "current_date"];

    var printertyperror="{$printertypeerror|escape:'html':'UTF-8'}";
    var printertypedymo={$printertype|escape:'html':'UTF-8'};
    var selectedDymoIndex_dlpp={$selectedDymoIndex|escape:'html':'UTF-8'};//SDI

    var dymoPrinterIndex_dlpp={$dymoPrinterIndex|escape:'html':'UTF-8'};

    function adjustDisplay(){
        if(printertyperror.length>4){
            //CHECK IF DYMO LABEL
            var printers = dymo.label.framework.getPrinters();
            if (printers.length >0) {
                document.getElementById('printertype_on').checked=true;
            }
        }
        else if(printertypedymo){
            var printers = dymo.label.framework.getPrinters();
            if (printers.length >0) {
                $("#dymoPanel").show();
                $("#dymoSettings").show();
                $("#barcodePanel").show();
                $("#otherSettings").show();

                //Add Row List
                var columns=$("#row_list ul");
                var items_per_column=Math.round(fields.length/columns.length);
                for(var i=0;i<columns.length;i++){
                    for(var j=0;(i*items_per_column+j)<fields.length && j<items_per_column;j++){
                        $(columns[i]).append("<li>"+fields[(i*items_per_column)+j]+"</li>");
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
        }
        else{
            $("#barcodePanel").show();
            $("#otherPrinterPanel").show();
            $("#otherSettings").show();
            generateConfigurationScreen();

            for(var i=0;i<fields.length;i++){
                $("#summernote_fields_insert").append("<option value=\""+fields[i]+"\">[["+fields[i]+"]]</option>");
                if(document.getElementById("summernote_barcode_insert"))
                    $("#summernote_barcode_insert").append("<option value=\""+fields[i]+"\">[["+fields[i]+"]]</option>");
                if(document.getElementById("summernote_qrcode_insert"))
                    $("#summernote_qrcode_insert").append("<option value=\""+fields[i]+"\">[["+fields[i]+"]]</option>");
            }

        }
    }

    if(window.attachEvent) {
        window.attachEvent('onload', adjustDisplay);
    } else {
        if(window.onload) {
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
            if(e.target.result.indexOf(";")>-1){
                delimiter=";";
            }
            if(e.target.result.indexOf("\t")>-1){
                delimiter="\t";
            }
            console.log("delimiter:"+delimiter);
            var items=[];
            var lines=e.target.result.replace("\"","").split("\n");
            console.log("line count:"+lines.length);
            for(var i=0;i<lines.length;i++) {
                var line = lines[i];
                var parts = line.split(delimiter);
                console.log("part count:"+parts.length);
                if(parts.length>1){
                    var reference=parts[0].trim();
                    var quantity=parseInt(parts[1]);
                    console.log("line:"+reference+"-"+quantity);
                    if(reference.length>0 && quantity>0){
                        items[items.length]={
                            reference: reference,
                            quantity: quantity
                        };
                    }
                }
            }

            if(items.length==0){
                alert("Can't find items in file. Please check file.");
            }
            console.log("to print:"+JSON.stringify(items));
            function processNext(i){
                if(i<items.length)
                    printLabelFromBarcode(items[i].reference,items[i].quantity,(i==items.length-1),processNext.bind(this,i+1));
            }
            processNext(0);
        }
        r.readAsText(file);
    }

    var barcode_sample_url = "{$barcode_sample_url|escape:'html':'UTF-8'}";
    var qrcode_sample_url="{$qrcode_sample_url|escape:'html':'UTF-8'}";
</script>

<style>
    #barcodePanel,#dymoPanel,#otherPrinterPanel,#noDymoFoundError, #dymoSettings, #batchPanel, #otherSettings{
        display:none;
    }
</style>

<ps-alert-error id="noDymoFoundError">The module can't find a DYMO Printer.<br/>
    Please make sure:<br/>
    <ul>
        <li>You have a DYMO Printer. If not, then please select "Other" below.</li>
        <li>Latest DYMO Label Software (!not Dymo Connect!) installed, to be sure we recommend to reinstall from <a href="http://www.dymo.com/en-US/dymo-user-guides" target="_blank">this website</a>.</li>
        <li>DYMO Web Service installed and running.<br/>
            <a href="http://developers.dymo.com/2016/08/08/dymo-label-web-service-faq/" target="_blank">Here you can find instruction on how to check.</a></li>
        <li>Still not working? Then do <a href="https://127.0.0.1:41951/DYMO/DLS/Printing/Check" target="_blank">this test</a> to verify your browser settings. Test fails? Approve security message / install certificate or try another browser.</li>
        <li><a href="https://addons.prestashop.com/contact-form.php?id_product=26296" target="_blank">Contact us if you still have problems getting it working.</a></li>
    </ul>
</ps-alert-error>

<ps-panel id="batchPanel" header="CSV Batch Print" img="{$iconurl|escape:'html':'UTF-8'}">
    <form class="form-horizontal" method="POST" enctype="multipart/form-data" >
        <ps-input-upload id="csv_file" name="csvfile" label="CSV File" size="20" required-input="true" hint="CSV File to process" fixed-width="300"></ps-input-upload>
        <input type="hidden" name="upload" value="upload"/>

        <button class="btn btn-default" type="button" style="margin-left:25%" OnClick="processCSVFile();">
            Print Items
        </button>
    </form>
    <ps-panel-divider></ps-panel-divider>
    Select a CSV file with:
    <ul>
        <li>Reference / Barcode / product_id (first column)</li>
        <li>Number of labels (second column)</li>
    </ul><br/>
    It will then print these labels in the specified numbers.
</ps-panel>

<ps-panel id="barcodePanel" header="Scan & Print Label" img="{$iconurl|escape:'html':'UTF-8'}">
    <form class="form-horizontal" id="scanandprint" onsubmit="printLabelFromBarcode($($('#scanandprint input')[0]).val());return false;">

        Enter a product identification (EAN / UPC / Reference / ...) to print label of that product<br/>&nbsp;<br/>

        <ps-input-text id="barcode" name="barcode" label="EAN / UPC / Reference" size="20" hint="Enter Barcode" fixed-width="300"></ps-input-text>

        <button class="btn btn-default" type="submit" style="margin-left:25%">
            Print Label
        </button>
        <button class="btn btn-default" type="button" OnClick="document.getElementById('batchPanel').style.display='block'" style="margin-left:40%">
            CSV Batch Print
        </button><br/>
        &nbsp;<br/>
        <ps-alert-warn>Tip! This module integrates with: <a href="https://addons.prestashop.com/oo/stock-supplier-management/18006-scan-spray-product-with-ean13.html" target="_blank">Scan Spray product with EAN13 Module</a>.</ps-alert-warn>

    </form>
</ps-panel>

<ps-panel header="Printer Type" img="{$iconurl|escape:'html':'UTF-8'}">
    {if isset($printertypeerror) }
        <ps-alert-error>{$printertypeerror|escape:'html':'UTF-8'}</ps-alert-error>
    {/if}

    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" >
        <ps-switch name="printertype" label="Printer Type" yes="DYMO" no="Other" active="{$printertype|escape:'html':'UTF-8'}" help="DYMO LabelWriter or Other (Label) Printer."></ps-switch>
        <input type="hidden" name="printertype_submit" value="printertype_submit"/>

        <ps-panel-footer>
            <ps-panel-footer-submit title="save" icon="process-icon-save" direction="right" name="submitPanel"></ps-panel-footer-submit>
        </ps-panel-footer>

    </form>
</ps-panel>

<ps-panel id="dymoPanel" header="Upload Label Template" img="{$iconurl|escape:'html':'UTF-8'}">
    {if isset($error) }
        <ps-alert-error>{$error|escape:'html':'UTF-8'}</ps-alert-error>
    {/if}
    {if isset($success) }
        <ps-alert-success>{$success|escape:'html':'UTF-8'}</ps-alert-success>
    {/if}

    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" >
        <ps-input-upload id="upload_file" name="filelabel" label="Label Template" size="20" required-input="true" hint="Upload Template File" fixed-width="300"></ps-input-upload>
        <input type="hidden" name="upload" value="upload"/>

        <button class="btn btn-default" type="submit" style="margin-left:25%">
            <i class="icon-upload-alt" ></i>
            Upload this template
        </button>
    </form>

    <ps-panel-divider></ps-panel-divider>

                    Upload a .label file from the DYMO Label Software as template.<br/>
                    This template will determine size and layout of printed labels.<br/>
                    There is an default template inside module (Library Barcode - 3/4" x 2 1/2").<br/>
                    Please only change if you need another size or need another layout.<br/>

                    &nbsp;<br/>
                    <p>
                        <b>IMPORTANT INSTRUCTIONS</b><br/>
                        This Dymo .label file needs to contain text/barcode objects with a correct reference name.<br/>
                        This reference name will determine which data will be put inside the object.
                        You can edit this reference name inside the Dymo software.<br/>
                    </p>
                    <p>
                        Normally the module erases the object text in the template and replaces completely with product data.<br/>
                        So for example "Title:" with reference name <i>product_name</i> will become "my product name".<br/>
                        However, if you add <b>(*)</b> to the sample data it will add product data on that location.<br/>
                        So for example "Title: (*)" with reference name <i>product_name</i> will become "Title: my product name".<br/>
                    </p>
                    <p><a href="http://somup.com/cbnFD285w" target="_blank"><b>Click here for video tutorial.</b></a></p>
                    <p><a href="{$templateurl|escape:'html':'UTF-8'}" download target="_blank"><b>Click here for sample template
                                (Library Barcode - 3/4" x 2 1/2").</b></a></p>
                    <p>
                        The following reference names are supported:
                    </p>
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

    <ps-alert-warn>Check out our other modules: <a href="https://addons.prestashop.com/oo/preparation-shipping/15699-direct-label-print-address-edition.html" target="_blank">Direct Label Print Address Edition</a> and <a href="https://addons.prestashop.com/oo/preparation-shipping/29114-direct-pdf-print-invoice-and-delivery-slips.html" target="_blank">Direct PDF Print</a>.</ps-alert-warn>


</ps-panel>

<ps-panel id="dymoSettings" header="Dymo Settings" img="{$iconurl|escape:'html':'UTF-8'}">
    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" >
        <!--//SDI-->
        If you have multiple Dymo printer you can select printer here:

        <ps-select id="dymo_select" label="Printer Select" name="selectedDymoIndex" chosen='false'>
            <option value="1000">Please select printer</option>
        </ps-select>

        If you have a duo/twin Dymo or two Dymo printers you can use this setting to choose roll.<br/>&nbsp;<br/>

        <ps-switch name="dymoPrinterIndex" label="Which Dymo Roll" yes="second" no="first" active="{$dymoPrinterIndexActive|escape:'html':'UTF-8'}"></ps-switch>

        <input type="hidden" name="dymoSettings" value="dymoSettings"/>&nbsp;<br/>

        <ps-panel-footer>
            <ps-panel-footer-submit title="save" icon="process-icon-save" direction="right" name="submitPanel"></ps-panel-footer-submit>
        </ps-panel-footer>

    </form>
</ps-panel>

<ps-panel id="otherPrinterPanel" header="Change Label Template" img="{$iconurl|escape:'html':'UTF-8'}">
    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" OnSubmit="copyLabelContent()">

        <ps-input-text id="width_input" name="width_input" label="Label Width" size="20" hint="Enter width of label used. "  help="Please enter whole numbers only (no dots, commas or text). The unit doesn't matter, the printer decides the actual size." fixed-width="50" value="{$width_input|escape:'html':'UTF-8'}"></ps-input-text>
        <ps-input-text id="height_input" name="height_input" label="Label Height" size="20" hint="Enter height of label used." help="Please enter whole numbers only (no dots, commas or text). The unit doesn't matter, the printer decides the actual size." fixed-width="50" value="{$height_input|escape:'html':'UTF-8'}"></ps-input-text>

        <ps-switch id="rotate_image" name="rotate_image" label="Rotate Label"  hint="This allows to print in other orientation." yes="Yes" no="No" active="{$rotate_image|escape:'html':'UTF-8'}"></ps-switch>

        <ps-alert-warn>Please be sure this matches: (1) paper size in printer settings (2) actual label size in the printer.</ps-alert-warn>

        &nbsp;<br/>
        <ps-form-group label="Label Template"  hint="Design your label and decide what to show.">
            <select id="summernote_fields_insert" style="display:inline-block;width:150px" onChange="insertTextField()">
                <option>**Add Field**</option>
            </select>
            <select id="summernote_barcode_insert" style="display:inline-block;width:150px" onChange="insertBarcodeField()">
                <option>**Add Barcode**</option>
                <option value="">Custom Text</option>
            </select>
            <select id="summernote_qrcode_insert" style="display:inline-block;width:150px" onChange="insertQRField()">
                <option>**Add QR-code**</option>
                <option value="">Custom Text</option>
            </select>
            <button type="button" style="display:inline-block;width:150px;margin-left:25px;" onclick="printTemplate()">Print Template</button>

            <!-- PLEASE READ - field "label_content" below is HTML-based template - can't be escape or it won't work -->
            <div id="summernote">{$label_content}</div>

        </ps-form-group>

        <input type="hidden" id="label_content" name="label_content" value="{$label_content|escape:'html':'UTF-8'}"/>
        <input type="hidden" name="generic_label_submit" value="generic_label_submit"/>

        <ps-panel-footer>
            <ps-panel-footer-submit title="save" icon="process-icon-save" direction="right" name="submitPanel"></ps-panel-footer-submit>
        </ps-panel-footer>

    </form>

</ps-panel>

<ps-panel id="otherSettings" header="Other Settings" img="{$iconurl|escape:'html':'UTF-8'}">
    <form class="form-horizontal" action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" >


        <ps-switch name="autoGenerateReference" label="Autogenerate Product Reference" yes="Yes" no="No" active="{$autoGenerateReference|escape:'html':'UTF-8'}"  hint="Automatic generate product reference when not available."></ps-switch>

        <ps-input-text id="autoFirstReferenceCounter" name="autoFirstReferenceCounter" label="Reference Count Value" size="13" hint="This is the next reference number." fixed-width="50" value="{$autoFirstReferenceCounter|escape:'html':'UTF-8'}"></ps-input-text>

        <ps-switch name="autoGenerateEAN" label="Autogenerate Product EAN" yes="Yes" no="No" active="{$autoGenerateEAN|escape:'html':'UTF-8'}"  hint="Automatic generate product EAN when not available."></ps-switch>

        <ps-input-text id="autoGenerateEAN_StartValue" name="autoGenerateEAN_StartValue" label="EAN Start Value" size="13" hint="Enter start of EAN range" fixed-width="50" value="{$autoGenerateEAN_StartValue|escape:'html':'UTF-8'}"></ps-input-text>

        <ps-input-text id="autoGenerateEAN_EndValue" name="autoGenerateEAN_EndValue" label="EAN End Value" size="13" hint="Enter end of EAN range" fixed-width="50" value="{$autoGenerateEAN_EndValue|escape:'html':'UTF-8'}"></ps-input-text>


        <ps-switch name="autoGenerateUPC" label="Autogenerate Product UPC" yes="Yes" no="No" active="{$autoGenerateUPC|escape:'html':'UTF-8'}"  hint="Automatic generate product UPC when not available."></ps-switch>

        <ps-input-text id="autoGenerateUPC_StartValue" name="autoGenerateUPC_StartValue" label="UPC Start Value" size="13" hint="Enter start of UPC range" fixed-width="50" value="{$autoGenerateUPC_StartValue|escape:'html':'UTF-8'}"></ps-input-text>

        <ps-input-text id="autoGenerateUPC_EndValue" name="autoGenerateUPC_EndValue" label="UPC End Value" size="13" hint="Enter end of UPC range" fixed-width="50" value="{$autoGenerateUPC_EndValue|escape:'html':'UTF-8'}"></ps-input-text>


        <input type="hidden" name="otherSettings" value="otherSettings"/>&nbsp;<br/>

        <ps-panel-footer>
            <ps-panel-footer-submit title="save" icon="process-icon-save" direction="right" name="submitPanel"></ps-panel-footer-submit>
        </ps-panel-footer>

    </form>
</ps-panel>

<ps-panel id="helpPanel" header="Help & Rating" img="{$iconurl|escape:'html':'UTF-8'}">

    <table border="0" style="width:100%;height:100px;font-size:15px">
        <tr><td style="width:50%">
                <a href="https://addons.prestashop.com/contact-form.php?id_product=26296" target="_black">
                    <img src="{$imgfolder|escape:'html':'UTF-8'}email.png" width="70"/>
                    <img src="{$imgfolder|escape:'html':'UTF-8'}help.png" width="70"/>
                    <img src="{$imgfolder|escape:'html':'UTF-8'}chat.png" width="70"/><br/>
                    Need help? Have problem / complaint?<br/> Any special needs? <br/> <b>Please contact us, we are here to help.</b>
                </a>
            </td><td>
                <a href="https://addons.prestashop.com/ratings.php" target="_black">
                    <img src="{$imgfolder|escape:'html':'UTF-8'}star.png" width="70"/>
                    <img src="{$imgfolder|escape:'html':'UTF-8'}thumbsup.png" width="70"/><br/>
                    Do you have everything running? And are you happy?<br/> <b>Please rate / review this module.</b>
                </a>
            </td>
        </tr>
    </table>

</ps-panel>

<script>

    riot.compile(function() {
        // here tags are compiled and riot.mount works synchronously
        var tags = riot.mount('*')
    })

</script>

