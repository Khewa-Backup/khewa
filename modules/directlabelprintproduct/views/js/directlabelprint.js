/**
 * 2016-2020 Leone MusicReader B.V.
 *
 * NOTICE OF LICENSE
 *
 * Source file is copyrighted by Leone MusicReader B.V.
 * Only licensed users may install, use and alter it.
 * Original and altered files may not be (re)distributed without permission.
 *
 * @author    Leone MusicReader B.V.
 *
 * @copyright 2016-2021 Leone MusicReader B.V.
 *
 * @license   custom see above
 */
function printProductLabel(url,info,count,isLast,callback) {
    if(typeof isLast == "undefined"){
        isLast=true;
    }
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var label_xml=this.responseText;
            var label_per_product_count=info["label_per_product_count"];
            for(var n=1;n<=label_per_product_count;n++){
                info["label_per_product_number"]=n;
                printProductLabelXML(label_xml,info,count,isLast&&(n==label_per_product_count));
            }
            if(callback){
                callback();
            }
        }
    }.bind(xhttp);
    xhttp.open("GET", url, true);
    xhttp.send();
}

function getProductLabelTemplateURL(id, callback){
    var getTemplateURL=dlpp_controller_url+"&action=getTemplate&id_product="+id+"&cache="+Math.round(Math.random()*1000);
    if(typeof callback =="undefined"){
        return getTemplateURL;
    }else{

        if(typeof batchVariant!="undefined"){
            callback(getTemplateURL+"&variant="+batchVariant);
            return;
        }

        //Check if multiple templates are defined
        var check_multiple_url=dlpp_controller_url+"&action=hasTemplateVariants&id_product="+id+"&cache="+Math.round(Math.random()*1000);
        var xhttp = new XMLHttpRequest();
        xhttp.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                if(this.responseText=="1"){
                    askVariantChoice(function(choice){
                        callback(getTemplateURL+"&variant="+choice);
                    });
                }else{
                    callback(getTemplateURL);
                }
            }
        }.bind(xhttp);
        xhttp.open("GET", check_multiple_url, true);
        xhttp.send();

    }
}

function askVariantChoice(callback){
    window.scrollTo(0, 0);

    var popupDiv=document.createElement("div");
        popupDiv.id="variantPopup";

    var popupHeader=document.createElement("div");
        popupHeader.id="variantPopupHeader";
        popupHeader.innerText=chooseTemplateVariant_text;

    var defaultButton=document.createElement("button");
        defaultButton.innerText=defaultTemplateVariant_text;
        defaultButton.className="btn btn-primary";
        defaultButton.onclick=function(){
            document.body.removeChild(popupDiv);
            callback(1);
        };
    var variant1Button=document.createElement("button");
        variant1Button.innerText=variant1_text;
        variant1Button.className="btn btn-primary";
        variant1Button.onclick=function(){
            document.body.removeChild(popupDiv);
            callback(2);
        };
    var variant2Button=document.createElement("button");
        variant2Button.innerText=variant2_text;
        variant2Button.className="btn btn-primary";
        variant2Button.onclick=function(){
            document.body.removeChild(popupDiv);
            callback(3);
        };

    var cancelButton=document.createElement("button");
        cancelButton.innerText=cancel_text;
        cancelButton.className="btn btn-outline-secondary";
        cancelButton.onclick=function(){
            document.body.removeChild(popupDiv);
        };

    popupDiv.appendChild(popupHeader);
    popupDiv.appendChild(defaultButton);
    popupDiv.appendChild(variant1Button);
    popupDiv.appendChild(variant2Button);
    popupDiv.appendChild(cancelButton);

    document.body.appendChild(popupDiv);
}

function printProductLabelXML(label_xml,info,count,isLast) {
    console.log("PL:"+isLast);
    if(!printer_type_set){
        if(typeof isLast=="undefined" || isLast)
            alert(message_setup_before_use);
        return;
    }

    if(typeof info.order_id=="undefined")
        info.order_id = "";
    if(typeof info.order_reference=="undefined")
        info.order_reference = "";
    if(typeof info.order_line_nr=="undefined")
        info.order_line_nr = "";
    if(typeof info.order_line_count=="undefined")
        info.order_line_count = "";
    if(typeof info.ordered_quantity=="undefined")
        info.ordered_quantity= "";

    //Serial Module Support
    if(typeof info.serial_no=="undefined")
        info.serial_no="";
    if(Array.isArray(info.serial_no)){
        var serials=info.serial_no;
        for(var i=0;i<count;i++){
            if(i<serials.length){
                info.serial_no=serials[i];
            }else{
                info.serial_no="";
            }
            printProductLabelXML(label_xml,info,1,(i==count-1)&&isLast);
        }
        return;
    }
    //End Serial Module Support

    if(!count && typeof printHTML !="undefined")
        count=1;

    if(!count || count==0 || count=="") {
        var value = prompt(enter_label_count, "1");
        if(value==null)
            return;
        count = parseInt(value);
    }

    var printers = dymo.label.framework.getPrinters();
    if (printers.length == 0) {
        alert(no_dymo_found);
        return;
    }

    console.log("PRINTERS1:"+JSON.stringify(printers));

    var printerNames = [];
    var printerInfos = [];
    for (var i = 0; i < printers.length; ++i) {
        var printer = printers[i];
        if (printer.printerType == "LabelWriterPrinter") {
            printerNames[printerNames.length] = printer.name;
            printerInfos[printerInfos.length]=printer;
        }
    }

    console.log("PRINTERS2:"+JSON.stringify(printerNames));

    console.log("Printer COUNT:"+printerNames.length);

    var printerName=printerNames[0];
    var printerInfo=printerInfos[0];
    if(selectedDymoIndex_dlpp<printerNames.length){ //SDI
        printerName=printerNames[selectedDymoIndex_dlpp];
        printerInfo=printerInfos[selectedDymoIndex_dlpp];
    }else if(typeof printMultipleHTML =="undefined"){
        alert(incorrect_dymo_selected);
        return;
    }

    var label = dymo.label.framework.openLabelXml(label_xml);

    function startPrint(){
        if(count>1){
            if(typeof dymo.label.framework.createLabelWriterPrintParamsXml!="undefined") {
                var printParams={printQuality:dymo.label.framework.LabelWriterPrintQuality.Text};
                printParams.copies = count;
                if(typeof printerInfo.isTwinTurbo!= "undefined" && printerInfo.isTwinTurbo){
                    console.log("Twin Detected Product:"+dymoPrinterIndex_dlpp);
                    if(dymoPrinterIndex_dlpp==0)
                        printParams.twinTurboRoll = dymo.label.framework.TwinTurboRoll.Left;
                    else
                        printParams.twinTurboRoll = dymo.label.framework.TwinTurboRoll.Right;
                }else{
                    console.log("NO TWIN detected");
                }

                label.print(printerName, dymo.label.framework.createLabelWriterPrintParamsXml(printParams));
            }else{
                label.print(printerName, count, undefined, isLast);
            }
        }else{
            if(typeof dymo.label.framework.createLabelWriterPrintParamsXml!="undefined") {
                var printParams={printQuality:dymo.label.framework.LabelWriterPrintQuality.Text};
                if(typeof printerInfo.isTwinTurbo!= "undefined" && printerInfo.isTwinTurbo){
                    console.log("Twin Detected Product:"+dymoPrinterIndex_dlpp);
                    if(dymoPrinterIndex_dlpp==0)
                        printParams.twinTurboRoll = dymo.label.framework.TwinTurboRoll.Left;
                    else
                        printParams.twinTurboRoll = dymo.label.framework.TwinTurboRoll.Right;

                    label.print(printerName, dymo.label.framework.createLabelWriterPrintParamsXml(printParams));
                }else{
                    console.log("NO TWIN detected");
                    label.print(printerName, dymo.label.framework.createLabelWriterPrintParamsXml(printParams));
                }
            }else{
                label.print(printerName, undefined, undefined, isLast);
            }

        }
    }

    var keys=Object.keys(info);

    function processInfo(k){
        if(k>=keys.length){
            startPrint();
            return;
        }
        var v=keys[k];
        //DYMO Label Software and other Printers
        try {
            var text=label.getObjectText(v);
            info[v]=""+info[v]; //make sure it's string
            if(typeof printHTML =="undefined") {
                info[v] = info[v].replace(/\|\|/ig, "\n"); //for multi-line
            }else{
                info[v] = info[v].replace(/\|\|/ig, "<br/>"); //for multi-line
            }
            if(info[v].length==0) {
                text=info[v];
            }else if(text.indexOf("(*)")>-1)
                text=text.replace("(*)",info[v]);
            else
                text=""+info[v];
            text=text.trim();
            text=text.replace("&amp;","&");
            if(typeof printHTML =="undefined"){
                text=$('<textarea />').html(text).text();
            }
            if(typeof printHTML =="undefined" && ((v.toLowerCase()=="ean13" && (text.length==13 || text.length==12)) || (v.toLowerCase()=="upc" && text.length==12))){ /* Only for Dymo*/
                if(v.toLowerCase()=="ean13" && text.length==12){
                    text="0"+text; //Add leading zero
                }

                if(!isNaN(text)) { //Check if number
                    text = text.slice(0, -1);
                }
            }

            if(v.indexOf("image_")>-1 && v.indexOf("_url")>0 && typeof printHTML =="undefined"){
                toDataURL(text, function(data){
                    try{
                        data=data.split(",")[1];
                        label.setObjectText(v, data);label.setObjectText(v+"_2", data);
                    }
                    catch (err) {}
                    processInfo(k+1);
                });
                return;
            }else{
                label.setObjectText(v, text);
                console.log("added "+v+"-"+text);
                label.setObjectText(v+"_2", text);
                console.log("added "+v+"-"+text);
            }
        }
        catch (err) {
            //DYMO CONNECT
            var key=v;
            var names = label.getObjectNames();
            var found=false;
            for (var i in names) {
                var name = names[i];
                var currentText = label.getObjectText(name).trim();
                if (typeof currentText != "undefined" && currentText != "found") {
                    var replaceText="\\[\\[" + key + "\\]\\]";
                    var re = new RegExp(replaceText, 'gi');

                    var value=info[key];
                    if(((key.toLowerCase()=="ean13" && (value.length==13 || value.length==12)) || (key.toLowerCase()=="upc" && value.length==12))){ /* Only for Dymo*/
                        if(key.toLowerCase()=="ean13" && value.length==12){
                            value="0"+value; //Add leading zero
                        }

                        if(!isNaN(value)) { //Check if number
                            value = value.slice(0, -1);
                        }
                    }

                    var newText = currentText.replace(re, value).trim();
                    if (newText != currentText) {
                        console.log(currentText + " to " + newText);
                        try {
                            label.setObjectText(name, newText);
                        }catch (err) {
                            console.log("setObjectText error"+err);
                        }
                        try {
                            label.setObjectText(name + "_2", newText);
                        }catch (err) {}
                        //console.log("Set2:" + key + "-" + info[key]);
                        found = true;
                    }
                }
            }
            if(!found)
                console.log("Not Found:"+key);
        }
        processInfo(k+1);
    }
    processInfo(0);
}

function toDataURL(url, callback) {
    var img = new Image();
    img.crossOrigin = 'Anonymous';
    img.onload = function() {
        var canvas = document.createElement('CANVAS');
        var ctx = canvas.getContext('2d');
        var dataURL;
        canvas.height = this.naturalHeight;
        canvas.width = this.naturalWidth;
        ctx.drawImage(this, 0, 0);
        dataURL = canvas.toDataURL("png");
        callback(dataURL);
    };
    img.src = url;
}