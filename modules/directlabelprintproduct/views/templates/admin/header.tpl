<style>
    .labelPrintWait{
        border: solid 1px black;
        position: fixed;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        height: 50px;
        width: 400px;
        background-color: black;
        z-index: 1000;
        font-size: 20px;
        color:white;
        text-align: center;
        line-height: 50px;
    }
</style>
<script type="text/javascript">
    /**
     * 2016 Leone MusicReader B.V.
     *
     * NOTICE OF LICENSE
     *
     * Source file is copyrighted by Leone MusicReader B.V.
     * Only licensed users may install, use and alter it.
     * Original and altered files may not be (re)distributed without permission.
     */
        function prepareProductLabel(el) {
            var id = el.parentNode.parentNode.parentNode.parentNode.parentNode.getAttribute("data-product-id");
            var combination_id = el.parentNode.parentNode.parentNode.getAttribute("data");
            if (combination_id) {
                //alert("combination:"+combination_id);
                id = document.getElementById("accordion_combinations").getAttribute("data-id-product");
                //alert("product id:"+id);
            }
            else if (!id) {
                id = el.parentNode.parentNode.parentNode.parentNode.getAttribute("data-product-id");
            }

            console.log("print product label:" + id);

            printProductLabelOf(id,combination_id);
        }

        function printProductLabelOf(id,combination_id,count,isLast,callback){
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var info=JSON.parse(xhttp.responseText);
                    getProductLabelTemplateURL(id,function(product_label_template) {
                        printProductLabel(product_label_template,info,count,isLast);

                        if(info["reference_generated"]=="yes")
                        {
                            $("#combination_" + combination_id + "_attribute_reference").val(info["reference"]);
                        }
                        if(info["ean13_generated"]=="yes"){
                            $("#combination_"+combination_id+"_attribute_ean13").val(info["ean13"]);
                        }
                        if(info["upc_generated"]=="yes"){
                            $("#combination_" + combination_id + "_attribute_upc").val(info["upc"]);
                        }

                        if(callback)
                            callback();
                    });
                }else if(this.readyState == 4){
                    alert("{l s='Direct Label Print couldn\'t load product data.' mod='directlabelprintproduct'}");
                }
            };
            xhttp.open("GET", dlpp_controller_url+"&action=getProductInfo&id="+id+"&lang_id={$lang_id|escape:'html':'UTF-8'}&combination_id="+combination_id, true);
            xhttp.send();

        }

    function printLabelFromBarcode(barcode,quantity,isLast,callback){
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                var info=JSON.parse(xhttp.responseText);
                getProductLabelTemplateURL(info["id_product"],function(product_label_template) {
                    printProductLabel(product_label_template,info,quantity, isLast,callback);
                });
            }else if(this.readyState == 4){
                alert("{l s='Direct Label Print couldn\'t load product data.' mod='directlabelprintproduct'}");
            }
        };
        xhttp.open("GET", dlpp_controller_url+"&action=getProductInfo&barcode="+barcode+"&langid={$lang_id|escape:'html':'UTF-8'}", true);
        xhttp.send();

    }

    function printStockChange(){
        var quantities=$(".table .qty-spinner .edit-qty");
        if(quantities.length>0 && quantities[0].tagName.toUpperCase()!="INPUT") //7.3 and higher
            quantities=$(".table .qty-spinner .edit-qty input");
        var selected=[];
        for(var i=0;i<quantities.length;i++) {
            var count=quantities[i].value;
            console.log("printStockChange-print count:"+count);
            if(count && count!="" && count>0) {
                var ids = quantities[i].id.split("-"); //qty-7-34
                selected[selected.length]={
                    id_product:ids[1],
                    id_combination:ids[2],
                    count:count
                };
            }
        }

        var waitDiv=document.createElement("div");
        waitDiv.className="labelPrintWait";
        document.body.appendChild(waitDiv);

        function processNext(i){
            waitDiv.innerText="{l s='Loading Label Info' mod='directlabelprintproduct'} "+(i+1)+" / "+selected.length;
            console.log(waitDiv.innerText);
            var isLast = (selected.length - 1 == i);
            if(isLast){
                document.body.removeChild(waitDiv);
            }
            printProductLabelOf(selected[i].id_product, selected[i].id_combination, selected[i].count, isLast,function() {
                if(!isLast){
                    processNext(i+1);
                }
            });
        }

        processNext(0);
    }

    function printLabelSelectedProducts(pForm,includingCombinations,excludeMainProduct) {

        window.scrollTo(0, 0);

        var waitDiv=document.createElement("div");
        waitDiv.className="labelPrintWait";

        var count = prompt("{l s='Please enter number of labels (per product/combination).' mod='directlabelprintproduct'}", "1");

        var selected=[];
        for (var i = 0; i < pForm.elements.length; i++){
            if (pForm.elements[i].name == "productBox[]" || pForm.elements[i].name == "bulk_action_selected_products[]") {
                if(pForm.elements[i].checked){
                    selected[selected.length]=pForm.elements[i].value;
                }
            }
        }

        function processNext(i){
            var isLast = (selected.length - 1 == i);
            var combination_ids=[];
            if(includingCombinations){
                for(var j=0;j<product_ids_array.length;j++){
                    var product=product_ids_array[j];
                    if(product.id_product==selected[i] && (!excludeMainProduct || product.id_product_attribute!="0")) {
                        combination_ids[combination_ids.length]=product.id_product_attribute;
                    }
                }
            }else{
                combination_ids=[0];
            }
            function processCombination(j){
                waitDiv.innerText="{l s='Loading Label Info' mod='directlabelprintproduct'} "+(i+1)+" / "+selected.length+" - {l s='Item' mod='directlabelprintproduct'} "+j+" / "+combination_ids.length;
                var isLastComb=(j==combination_ids.length-1);
                printProductLabelOf(selected[i], combination_ids[j], count, isLast&&isLastComb,function() {
                    j++;
                    if(j<combination_ids.length) {
                        processCombination(j);
                    }else{
                        if(!isLast){
                            processNext(i+1);
                        }else{
                            document.body.removeChild(waitDiv);
                            batchVariant=undefined;
                        }
                    }

                });
            }
            processCombination(0);
        }

        //Check if multiple templates are defined
        var check_multiple_url=dlpp_controller_url+"&action=hasTemplateVariants&id_product=DEFAULT&cache="+Math.round(Math.random()*1000);
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                if(this.responseText=="1"){
                    askVariantChoice(function(choice) {
                        document.body.appendChild(waitDiv);
                        batchVariant = choice;
                        processNext(0);
                    });
                }else{
                    document.body.appendChild(waitDiv);
                    batchVariant=1;
                    processNext(0);
                }
            }
        }.bind(xhttp);
        xhttp.open("GET", check_multiple_url, true);
        xhttp.send();


    }


    var documentReadyDLPP=function() {
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function() {
            if (this.readyState == 4 && this.status == 200) {
                product_ids_array=JSON.parse(xhttp.responseText);
                documentReadyDLPP2();
            }else if(this.readyState == 4){
                console.log("Retrieval Error:"+this.readyState+"-"+this.status);
                alert("{l s='Direct Label Print couldn\'t load product data.' mod='directlabelprintproduct'}");
            }
        };
        xhttp.open("GET", dlpp_controller_url+"&action=getProductIds", true);
        xhttp.send();
    }

       var documentReadyDLPP2=function() {
           function findProductIDs(value){
               value=value.replace("product-","");
               for(var j=0;j<product_ids_array.length;j++){
                   var product=product_ids_array[j];
                   console.log(product.id_product+"-"+product.id_product_attribute+"=="+value);
                   if(product.id_product+product.id_product_attribute==value){
                       console.log("found1");
                       return product;
                   }
               }
           }

            $(".adminproducts .btn-group .dropdown-menu-right").append("" +
                    "<a class=\"dropdown-item product-edit\" href=\"#\" onclick=\"prepareProductLabel(this);\">"+
                    "<img src=\"{$dlppb_module_folder|escape:'html':'UTF-8'}views/img/icon-print.png\" style=\"height:25px\"/>"+
                    "Label"+"</a>");

           //Add Bulk (1.6)
               var bulk6 = $(".adminproducts .bulk-actions .dropdown-menu");
               bulk6.append("<li>"+
                       "<a href=\"#\" onclick=\"javascript:printLabelSelectedProducts($(this).closest('form').get(0),false,false);return false;\">"+
                       "<img src=\"../modules/directlabelprintproduct/views/img/icon-print.png\" style=\"height:100%\"/>&nbsp;{l s='Print Product Labels' mod='directlabelprintproduct'} - {l s='Products Only' mod='directlabelprintproduct'}"+
                       "</a>"+
                       "</li>");
               bulk6.append("<li>"+
                       "<a href=\"#\" onclick=\"javascript:printLabelSelectedProducts($(this).closest('form').get(0),true,false);return false;\">"+
                       "<img src=\"../modules/directlabelprintproduct/views/img/icon-print.png\" style=\"height:100%\"/>&nbsp;{l s='Print Product Labels' mod='directlabelprintproduct'} - {l s='Products + Combinations' mod='directlabelprintproduct'}"+
                       "</a>"+
                       "</li>");
               bulk6.append("<li>"+
                       "<a href=\"#\" onclick=\"javascript:printLabelSelectedProducts($(this).closest('form').get(0),true,true);return false;\">"+
                       "<img src=\"../modules/directlabelprintproduct/views/img/icon-print.png\" style=\"height:100%\"/>&nbsp;{l s='Print Product Labels' mod='directlabelprintproduct'} - {l s='Combinations Only' mod='directlabelprintproduct'}"+
                       "</a>"+
                       "</li>");
           //Add Bulk (1.7)
               var bulk7 = $(".adminproducts .bulk-catalog .dropdown-menu");
            console.log("1.7 bulk:"+bulk7.length);
               bulk7.append(
                       "<a href=\"#\" onclick=\"javascript:printLabelSelectedProducts($('#product_catalog_list')[0],false,false);return false;\" class=\"dropdown-item\">"+
                       "<img src=\"{$dlppb_module_folder|escape:'html':'UTF-8'}views/img/icon-print.png\" style=\"height:100%\"/>&nbsp;{l s='Print Product Labels' mod='directlabelprintproduct'} - {l s='Products Only' mod='directlabelprintproduct'}"+
                       "</a>");
               bulk7.append(
                       "<a href=\"#\" onclick=\"javascript:printLabelSelectedProducts($('#product_catalog_list')[0],true,false);return false;\" class=\"dropdown-item\">"+
                       "<img src=\"{$dlppb_module_folder|escape:'html':'UTF-8'}views/img/icon-print.png\" style=\"height:100%\"/>&nbsp;{l s='Print Product Labels' mod='directlabelprintproduct'} - {l s='Products + Combinations' mod='directlabelprintproduct'}"+
                       "</a>");
               bulk7.append(
                       "<a href=\"#\" onclick=\"javascript:printLabelSelectedProducts($('#product_catalog_list')[0],true,true);return false;\" class=\"dropdown-item\">"+
                       "<img src=\"{$dlppb_module_folder|escape:'html':'UTF-8'}views/img/icon-print.png\" style=\"height:100%\"/>&nbsp;{l s='Print Product Labels' mod='directlabelprintproduct'} - {l s='Combinations Only' mod='directlabelprintproduct'}"+
                       "</a>");

           if(bulk6.length==0 && bulk7.length==0){
               //Fix for early 1.7 releases
               var bulk7b = $(".adminproducts .dropup .dropdown-menu");
               bulk7b.append("<a href=\"#\" onclick=\"javascript:printLabelSelectedProducts($('#product_catalog_list')[0],false,false);return false;\" class=\"dropdown-item\">"+
               "<img src=\"../modules/directlabelprintproduct/views/img/icon-print.png\" style=\"height:100%\"/>&nbsp;{l s='Print Product Labels' mod='directlabelprintproduct'} - {l s='Products Only' mod='directlabelprintproduct'}"+
               "</a>");
               bulk7b.append(
                       "<a href=\"#\" onclick=\"javascript:printLabelSelectedProducts($('#product_catalog_list')[0],true,false);return false;\" class=\"dropdown-item\">"+
                       "<img src=\"../modules/directlabelprintproduct/views/img/icon-print.png\" style=\"height:100%\"/>&nbsp;{l s='Print Product Labels' mod='directlabelprintproduct'} - {l s='Products + Combinations' mod='directlabelprintproduct'}"+
                       "</a>");
               bulk7b.append(
                       "<a href=\"#\" onclick=\"javascript:printLabelSelectedProducts($('#product_catalog_list')[0],true,true);return false;\" class=\"dropdown-item\">"+
                       "<img src=\"../modules/directlabelprintproduct/views/img/icon-print.png\" style=\"height:100%\"/>&nbsp;{l s='Print Product Labels' mod='directlabelprintproduct'} - {l s='Combinations Only' mod='directlabelprintproduct'}"+
                       "</a>");
           }

           //Products page - Combinations & Quantity
                setTimeout(function(){
                        $(".adminproducts .attribute-actions .btn-group").append("" +
                            "<a class=\"btn\" href=\"#\" onclick=\"prepareProductLabel(this);\">"+
                            "<img src=\"{$dlppb_module_folder|escape:'html':'UTF-8'}views/img/icon-print.png\" style=\"height:25px\"/></a>");

                        var stock_input_fields=$(".table .available_quantity input");
                        for(var i=0;i<stock_input_fields.length;i++){
                            var input_field=stock_input_fields[i];
                            var combination_id=input_field.name.split("_")[1];
                            var urlParams = new URLSearchParams(window.location.search);
                            var product_id=urlParams.get('id_product');
                            var parent=input_field.parentNode;
                            var button="" +
                                    "<a class=\"btn\" href=\"#\" onclick=\"printProductLabelOf("+product_id+","+combination_id+");\">" +
                                    "<img src=\"{$dlppb_module_folder|escape:'html':'UTF-8'}views/img/icon-print.png\" style=\"height:25px\"/></a>";
                            $(parent).append(button);
                        }
                },4000);

           //Stock Page - keep checking every 3 seconds
               setInterval(function() {
                   var updateQtyB=$(".update-qty");
                   if(updateQtyB.length>0) {
                       var all_parent = updateQtyB.parent();
                       if (typeof all_parent[0].labelAdded == "undefined") {
                           all_parent[0].labelAdded = "yes";
                           var className="btn";
                           if(all_parent[0].className=="col-md-4"){ //7.3 and higher
                               className=updateQtyB[0].className;
                               all_parent[0].className="col-md-2";
                               var new_parent=document.createElement("div");
                               new_parent.className="col-md-2";
                               all_parent.parent()[0].appendChild(new_parent);
                               all_parent=$(new_parent);
                           }
                           all_parent.append("" +
                                   "<button type=\"button\" class=\""+className+"\" style=\"padding:5px\" onclick=\"printStockChange()\">" +
                                   "<img src=\"{$dlppb_module_folder|escape:'html':'UTF-8'}views/img/icon-print.png\" style=\"height:25px\"/> {l s='Print New Qty' mod='directlabelprintproduct'}</button>");
                       }
                   }

                   var quantities=$(".table .qty-spinner .edit-qty");
                   if(quantities.length>0 && quantities[0].tagName.toUpperCase()!="INPUT") //7.3 and higher
                        quantities=$(".table .qty-spinner .edit-qty input");
                   var checkboxes=$(".table .custom-checkbox input");
                   if(checkboxes.length==0){
                       checkboxes=$(".table .md-checkbox input");
                   }
                   if(quantities.length>0) {
                       for (var i = 0; i < quantities.length; i++) {
                           var ids = [];
                           if(typeof quantities[i].id=="undefined" || quantities[i].id.trim().length==0) {
                               var product=findProductIDs(checkboxes[i].id);
                               quantities[i].id="qty-"+product.id_product+"-"+product.id_product_attribute;
                           }
                           ids=quantities[i].id.split("-"); //qty-7-34
                           var node = quantities[i].parentNode.parentNode.parentNode;
                           if (typeof node.labelAdded == "undefined") {
                               $(node).append("" +
                                       "<center><a class=\"btn\" href=\"#\" onclick=\"printProductLabelOf(" + ids[1] + "," + ids[2] + ",document.getElementById('" + quantities[i].id + "').value);\">" +
                                       "<img src=\"{$dlppb_module_folder|escape:'html':'UTF-8'}views/img/icon-print.png\" style=\"height:25px\"/></a></center>");
                               node.labelAdded = "yes";
                           }
                       }
                   }
               }, 3000);


        };

    if(window.attachEvent) {
        window.attachEvent('onload', documentReadyDLPP);
    } else {
        if(window.onload) {
            var curronload = window.onload;
            var newonload = function(curronload,evt) {
                curronload(evt);
                documentReadyDLPP(evt);
            }.bind(this,curronload);
            window.onload = newonload;
        } else {
            window.onload = documentReadyDLPP;
        }
    }


        var directLabelPrintActions=new Array();

        /*var dlppb_generic_label_width={$generic_label_width|escape:'html':'UTF-8'};
        var dlppb_generic_label_height={$generic_label_height|escape:'html':'UTF-8'};
        var dlppb_generic_label_rotate={$generic_label_rotate|escape:'html':'UTF-8'};
        var dlppb_generic_label_content='{$generic_label_content|escape:'quotes':'UTF-8'}';*/
        var printer_type_set={$printertypeset|escape:'html':'UTF-8'};
        var dlppb_printer_type_isDymo={$dlppb_printer_type_isDymo|escape:'html':'UTF-8'};
        var dlppb_printer_type_isGeneric={$dlppb_printer_type_isGeneric|escape:'html':'UTF-8'};

        /*var product_label_template="{$product_label_template|escape:'html':'UTF-8'}";*/
        var dlppb_module_folder="{$dlppb_module_folder|escape:'html':'UTF-8'}";

        var dymoPrinterIndex_dlpp={$dymoPrinterIndex|escape:'html':'UTF-8'};

        var selectedDymoIndex_dlpp={$selectedDymoIndex|escape:'html':'UTF-8'};//SDI

        var dlpp_controller_url="{$dlpp_controller_url|escape:'quotes':'UTF-8'}";

        var message_setup_before_use="{l s='Module requires setup before use, please go to Module Settings.' mod='directlabelprintproduct'}";
        var enter_label_count="{l s='Please enter number of labels.' mod='directlabelprintproduct'}";
        var no_dymo_found="{l s='Can\'t find DYMO label printers. Please go to module settings for details.' mod='directlabelprintproduct'}";
        var incorrect_dymo_selected="{l s='Incorrect printer set in settings. Please set available Dymo printer.' mod='directlabelprintproduct'}";

        var chooseTemplateVariant_text="{l s='Choose Template Variant' mod='directlabelprintproduct'}";
        var defaultTemplateVariant_text="{l s='Default' mod='directlabelprintproduct'}";
        var variant1_text="{l s='Variant 1' mod='directlabelprintproduct'}";
        var variant2_text="{l s='Variant 2' mod='directlabelprintproduct'}";
        var cancel_text="{l s='Cancel' mod='directlabelprintproduct'}";
</script>