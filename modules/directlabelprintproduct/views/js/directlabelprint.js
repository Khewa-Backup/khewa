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
 * @copyright 2016-2020 Leone MusicReader B.V.
 *
 * @license   custom see above
 */
function printProductLabel(url,info,count,isLast) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var label_xml=this.responseText;
            printProductLabelXML(label_xml,info,count,isLast);
        }
    }.bind(xhttp);
    xhttp.open("GET", url+"?cache="+Math.round(Math.random()*1000), true);
    xhttp.send();
}

function printProductLabelXML(label_xml,info,count,isLast) {
    console.log("PL:"+isLast);
    if(!printer_type_set){
        if(typeof isLast=="undefined" || isLast)
            alert("Module requires setup before use, please go to Module Settings.");
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

    if(!count && typeof printHTML !="undefined")
        count=1;

    if(!count || count==0 || count=="") {
        var value = prompt("Please enter number of labels.", "1");
        if(value==null)
            return;
        count = parseInt(value);
    }

    var printers = dymo.label.framework.getPrinters();
    if (printers.length == 0) {
        alert("Can't find DYMO label printers." + '\n' + "Please make sure:" + '\n' + "(1) You have DYMO printers installed. (other brands don't work)" + '\n' + "(2) Have latest DYMO software installed (8.5.3 or newer) with Dymo Label Service (DLS) running." + '\n' + "(3) Approved security messages in your browser.");
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
        alert("Incorrect printer set in settings. Please set available Dymo printer.");
        return;
    }

    var label = dymo.label.framework.openLabelXml(label_xml);

    for (v in info) {
        try {
            var text=label.getObjectText(v);
            info[v]=""+info[v]; //make sure it's string
            info[v]=info[v].replace(/\|\|/ig,"\n"); //for multi-line
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
            label.setObjectText(v, text);
            console.log("added "+v+"-"+text);
        }
        catch (err) {
            console.log("not found "+v+"-"+err);
        }
    }

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
