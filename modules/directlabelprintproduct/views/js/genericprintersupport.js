/**
 * 2016-2021 Leone MusicReader B.V.
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

dymo =
{
    label:{
        framework:{
            getPrinters:function() {
                console.log("Generic Printer - Get Printers");
                return [{
                    printerType: "LabelWriterPrinter",
                    name: "Generic Label Printer",
                }];
            },
            openLabelXml: function(xml) {
                return dymo.label.framework.openLabelFile(xml);
            },
            openLabelFile: function(url) {
                console.log("Generic Printer - Open Label File");
                var obj= {
                    getObjectText:function(v) {
                        console.log("Generic Printer - Get Text Object "+v);
                        //Return Text
                        if(this.template.indexOf("[["+v+"]]")>-1)
                            return "found";
                        else
                            return undefined;
                    },
                    setObjectText:function(v, text) {
                        if(v.indexOf("_html")>-1){
                            text=decodeURIComponent(text);
                            text=text.replace(/\+/g," ");
                        }else {
                            text = text.trim().replace(/\r/g, "<br/>");
                        }
                        console.log("Generic Printer - Set Text Object "+v+" to "+text);
                        this.template=this.template.replace("[["+v+"]]",text);
                        this.template=this.template.replace("[["+v+"]]",text);
                        this.template=this.template.replace("[["+v+"]]",text);
                        this.template=this.template.replace("[["+v+"]]",text);
                        this.template=this.template.replace("[["+v+"]]",text);
                    },
                    getObjectNames: function(){
                        return [];
                    },
                    print:function(printerName,count,unknownVar,lastPrint,callback) {
                        console.log("last print:"+lastPrint);
                        if(!count || count==0 || count==""){
                            count=1;
                        }
                        if(typeof lastPrint=="undefined"){
                            console.log("print label to:"+printerName);
                            printHTML(this.template,this.width,this.height,this.rotate,count,callback);
                        }else{
                            if(!lastPrint){
                                if(typeof dymo.templates == "undefined"){
                                    dymo.templates=[];
                                    dymo.counts=[];
                                }
                                dymo.templates[dymo.templates.length]=this.template;
                                dymo.counts[dymo.counts.length]=count;
                                if(callback)
                                    callback();
                            }
                            else{
                                if(typeof dymo.templates == "undefined"){
                                    dymo.templates=[];
                                    dymo.counts=[];
                                }
                                dymo.templates[dymo.templates.length]=this.template;
                                dymo.counts[dymo.counts.length]=count;
                                printMultipleHTML(dymo.templates,this.width,this.height,this.rotate,dymo.counts,callback);
                                dymo.templates=[];
                                dymo.counts=[];
                            }
                        }
                    },
                };
                if(url.indexOf("ShippingAddress")<0 || (typeof third_party_module_dlpp != "undefined" && third_party_module_dlpp)) {
                    var config_obj=JSON.parse(url);

                    obj.template = decodeURIComponent(config_obj["label_content"].replace(/\+/gi," "));
                    obj.width = config_obj["width"];
                    obj.height = config_obj["height"];
                    obj.rotate = config_obj["rotate_image"]=="true";
                }else{
                    obj.template = dlpa_generic_label_content;
                    obj.width = dlpa_generic_label_width;
                    obj.height = dlpa_generic_label_height;
                    obj.rotate = dlpa_generic_label_rotate;
                }
                return obj;
            }
        }
    }
};


function generateConfigurationScreen(){
    $("#width_input input")[0].onkeydown=generateConfigurationScreen;
    $("#width_input input")[0].oninput=generateConfigurationScreen;
    $("#width_input input")[0].onpaste=generateConfigurationScreen;

    $("#height_input input")[0].onkeydown=generateConfigurationScreen;
    $("#height_input input")[0].oninput=generateConfigurationScreen;
    $("#height_input input")[0].onpaste=generateConfigurationScreen;

    var width =  $("#width_input input")[0].value;
    var height = $("#height_input input")[0].value;

    var fixed_width=675;

    height=height/width*fixed_width;
    width=fixed_width;

    $('#summernote').summernote('destroy');

    $.summernote.options.fontSizes= ['8', '9', '10', '11', '12', '14', '18', '24', '34', '40', '50', '60'];

    $('#summernote').summernote( {
        height: height,
        width: width,
        minHeight: height,
        maxHeight: height,
        minWidth: width,
        maxWidth: width,
        popatmouse: false,
        toolbar: [
            // [groupName, [list of button]]
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['fontsize', ['fontname', 'fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert',['picture', 'table']],
            ['undo',['undo', 'redo']],
            ['code',['codeview']]
        ]
    });
    $('#editer_header_buttons').width(width-1);

    $("#editer_header_buttons select").attr("class", "white");
    $("#editer_header_buttons select").change(function(){
        $("#editer_header_buttons select").attr("class", "white");
    });
    addWaterMarkingToBarcodes();
}


function addWaterMarkingToBarcodes(){

    var lines=[];

    var barcodes=$(".barcode");
    for(var i=0;i<barcodes.length;i++){
        var name = barcodes[i].name;
        lines[lines.length]=".barcode[name='"+name+"']{ background-image:url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' version='1.1' height='25px' width='125px'><text x='50%' y ='50%' dominant-baseline='middle' text-anchor='middle' fill='rgb(0,0,0)' font-size='12' font-family='Arial, Helvetica, sans-serif'>"+name+"</text></svg>\");}";
    }

    var qrcodes=$(".qrcode");
    for(var i=0;i<qrcodes.length;i++){
        var name = qrcodes[i].name;
        lines[lines.length]=".qrcode[name='"+name+"']{ background-image:url(\"data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' version='1.1' height='25px' width='125px'><text x='50%' y ='50%' dominant-baseline='middle' text-anchor='middle' fill='rgb(0,0,0)' font-size='12' font-family='Arial, Helvetica, sans-serif'>"+name+"</text></svg>\");}";
    }

    var styleString=lines.join("\n");
    var styleElement=document.createElement("style");
    styleElement.innerText=styleString;
    document.getElementsByTagName('head')[0].appendChild(styleElement);


}



function printHTML(code, width,height,rotate,count,callback) {

    if(!count){
        count=1;
    }

    var images=[];

    //Special Addition to support multiple pages
    var code_pages=code.split("[****]");

    function processPage(i,code_pages){
        generateImageFromHTML(code_pages[i],width,height,rotate,function(image){
            for(var j=0;j<count;j++)
                images[images.length]=image;
            i++;
            if(i<code_pages.length) {
                processPage(i,code_pages);
            }else{
                if(rotate)
                    printMultipleImages(images,height,width,rotate,callback);
                else
                    printMultipleImages(images,width,height,rotate,callback);
            }

        });
    }

    processPage(0,code_pages);

}

function printMultipleHTML(codes_1, width,height,rotate,counts,callback) {

    var waitDiv=document.createElement("div");
    waitDiv.className="labelPrintWait";
    document.body.appendChild(waitDiv);

    //Special Addition to support multiple pages
    var codes=[];
    for(var k=0;k<codes_1.length;k++){
        var code_split=codes_1[k].split("[****]");
        code_split.forEach(function(element) {
            codes[codes.length]=element;
        });
    }

    var images=[];
    function processHTML(codes,i){
        waitDiv.innerText="Rendering Label "+(i+1)+" / "+codes.length;
        console.log(waitDiv.innerText);
        generateImageFromHTML(codes[i],width,height,rotate,function(image){

            if(!counts[i]){
                counts[i]=1;
            }

            for(var j=0;j<counts[i];j++)
                images[images.length]=image;

            i++;
            if(i<codes.length){
                processHTML(codes,i);
            }else{
                document.body.removeChild(waitDiv);
                if(rotate)
                    printMultipleImages(images,height,width,rotate,callback);
                else{
                    printMultipleImages(images,width,height,rotate,callback);
                }
            }
        });
    }
    processHTML(codes,0);
}

function generateImageFromHTML(code, width,height,rotate,callback){

    window.scrollTo(0, 0);

    var enlargement=4;

    console.log("start print HTML");

    var fixed_width=650;

    height=height/width*fixed_width;
    width=fixed_width;

    console.log("HTML code:"+code);

    var div = document.createElement('div');
    div.height=height;
    div.width=width;
    div.innerHTML=code;
    div.style.position="absolute";
    div.style.left="-"+(2*width)+"px";
    div.style.top="-"+(2*height)+"px";
    div.style.height=height+"px";
    div.style.width=width+"px";
    div.style.transform="scale("+enlargement+","+enlargement+")";
    div.style.zIndex=10000;
    div.style.background="white";
    div.style.color="black";
    div.style.padding="20px";
    div.id="html_renderer_div";
    document.body.appendChild(div);

    //Remove borders from table
    $("#html_renderer_div table").removeClass("table-bordered");
    $("#html_renderer_div table").removeClass("table");
    $("#html_renderer_div table").css("width","100%");
    $("#html_renderer_div table td").css("border","none");
    $("#html_renderer_div table tbody").css("border","none");

    //Set BarCodes - http://lindell.me/JsBarcode/
    if(typeof JsBarcode != "undefined") {
        var barcodes = $("#html_renderer_div .barcode");
        for (var i = 0; i < barcodes.length; i++) {
            barcodes[i].src = undefined;
            barcodes[i].tagName = "svg";
            if(barcodes[i].getAttribute("fieldname")=="ean13" && barcodes[i].name.length==13){
                $(barcodes[i]).attr("jsbarcode-format", "EAN13");
            }else if(barcodes[i].getAttribute("fieldname")=="upc" && ( barcodes[i].name.length==13 || barcodes[i].name.length==12)){
                $(barcodes[i]).attr("jsbarcode-format", "UPC");
            }else{
                $(barcodes[i]).attr("jsbarcode-format", "auto");
            }
            $(barcodes[i]).attr("jsbarcode-displayValue", "false");
            $(barcodes[i]).attr("jsbarcode-value", barcodes[i].name);
            $(barcodes[i]).attr("jsbarcode-margin", "0");
            //$(barcodes[i]).attr("jsbarcode-textmargin", "0");
            //$(barcodes[i]).attr("jsbarcode-fontoptions", "bold");
        }
        JsBarcode("#html_renderer_div .barcode").init();
        $("#html_renderer_div .barcode").css("display","inline");
    }

    //Set QRCodes - https://davidshimjs.github.io/qrcodejs/
    if(typeof QRCode != "undefined") {
        var qrcodes = $("#html_renderer_div .qrcode");
        for (var i = 0; i < qrcodes.length; i++) {

            var new_div = document.createElement("div");
            new_div.id = "qrcode" + i;

            $(qrcodes[i]).replaceWith(new_div);

            new QRCode(new_div.id, qrcodes[i].name);

            $("#" + new_div.id + " img")[0].style.width = qrcodes[i].style.width;
            $("#" + new_div.id + " img")[0].style.height = qrcodes[i].style.height;
            $(new_div)[0].style.display="inline-block";
        }

    }

    function renderLabelContentsNow(){
        setTimeout(function() {
            width=width*enlargement;
            height=height*enlargement;

            width=width*window.devicePixelRatio;
            height=height*window.devicePixelRatio;

            var canvas={
                context:  new C2S(width,height),
                getContext: function(){
                    return canvas.context;
                },
                style:{},
            };

            if(rotate){
                canvas={
                    context:  new C2S(height,width),
                    getContext: function(){
                        return canvas.context;
                    },
                    style:{},
                };
                canvas.context.save();
                // translate and rotate
                canvas.context.translate(height, 0);
                canvas.context.rotate(Math.PI / 2);
            }

            html2canvas(div, {width: width, height: height, canvas:canvas, embedImages:true}).then(function (canvas) {

                if(rotate){
                    canvas.context.restore();
                }

                document.body.removeChild(div);


                var myRectangle = canvas.context.getSerializedSvg(true);

                var data_url="data:image/svg+xml;utf8,"+encodeURIComponent(myRectangle);

                callback(data_url);

            });
        },50);
    }

    var imageObjects=$(".product_image");
    if(imageObjects.length==0){
        renderLabelContentsNow();
        return;
    }
    var doneCount=0;
    function finish(){
        doneCount++;
        if(doneCount==imageObjects.length){
            renderLabelContentsNow();
        }
    }
    for(var i=0;i<imageObjects.length;i++){
        var imgOBJ=imageObjects[i];
        imgOBJ.style.maxWidth=imgOBJ.style.width;
        imgOBJ.style.maxHeight=imgOBJ.style.height;
        imgOBJ.style.width="auto";
        imgOBJ.style.height="auto";

        if(imgOBJ.src.length>10) {
            var oReq = new XMLHttpRequest();
            oReq.open("GET", imgOBJ.alt, true);
            oReq.responseType = "blob";
            oReq.onload = function (oEvent) {
                var blob = oReq.response;
                const reader = new FileReader();
                reader.addEventListener("load", function () {
                    // convert image file to base64 string
                    imgOBJ.src = reader.result;
                    finish();
                }, false);
                reader.readAsDataURL(blob);
            };
            oReq.send();
        }else{
            finish();
        }
    }


}



/*function printImage(width,height,image_data_url,callback){
    printMultipleImages([image_data_url],width,height,callback);
}*/

/*function printImage(width,height,rotate,image_data_url,callback){
    var isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
    var isEdge = window.navigator.userAgent.toLowerCase().indexOf("edge") > -1;
    var isChrome = /chrome/.test(navigator.userAgent.toLowerCase());
    var isSafari = navigator.vendor && navigator.vendor.indexOf('Apple') > -1 &&
        navigator.userAgent && !navigator.userAgent.match('CriOS');
    var isMSIE = window.navigator.userAgent.indexOf("MSIE")>0;

    var iframe = document.createElement('iframe');

    var min_width=650;

    if(width<min_width){
        height=height/width*min_width;
        width=min_width;
    }

    iframe.scroll="auto";
    iframe.height=height;
    iframe.width=width;
    iframe.style.position="absolute";
    iframe.style.left="0px";
    iframe.style.top="0px";
    iframe.style.zIndex=10000;
    iframe.marginwidth="0";
    iframe.marginheight="0";
    iframe.hspace="0";
    iframe.vspace="0";
    iframe.style.background="white";
    document.body.appendChild(iframe);
    iframe.focus();

    iframe.onload = function(){
        if(isFirefox){
            iframe.onload = function(){
                //On Second Load -> Remove frame and call callback
                document.body.removeChild(iframe);
                if(callback)
                    callback();
            };
        }else if(isEdge || isMSIE){
            iframe.onblur = function(){
                console.log("onblur iframe");
                iframe.onfocus = function(){
                    console.log("onfocus iframe");
                    //On Second Load -> Remove frame and call callback
                    document.body.removeChild(iframe);
                    if(callback)
                        callback();
                };
            };

        }else { //Chrome and others
            document.body.removeChild(iframe);
            if (callback)
                callback();
        }
    };

    var extra_css="";
    if(isChrome || isFirefox){
        extra_css="@page { size: auto; margin: 0; }";
    }
    var max="97%";
    var jscode="window.onafterprint=function(){location.href='about:blank';};window.print();";
    if(isEdge || isMSIE){
        jscode="window.print();";
    }
    var htmlcode="<style>html, body { height: "+max+"; width:"+max+" } "+extra_css+" </style><img src=\"" + image_data_url + "\" style=\"width:100%;max-height:100%;\" onload=\""+jscode+"\"/>";

    iframedoc = iframe.contentDocument || iframe.contentWindow.document;
    if(isFirefox) {
        //iframe.src = canvas.toDataURL(); //TODO moznomarginboxes
        iframe.srcdoc = htmlcode;
    }else {
        iframedoc.body.innerHTML = htmlcode;
    }
}*/

function printMultipleImages(image_data_urls,width,height,rotate,callback){
    var isFirefox = navigator.userAgent.toLowerCase().indexOf('firefox') > -1;
    var isEdge = window.navigator.userAgent.toLowerCase().indexOf("edge") > -1;
    var isChrome = /chrome/.test(navigator.userAgent.toLowerCase());
    var isSafari = navigator.vendor && navigator.vendor.indexOf('Apple') > -1 &&
        navigator.userAgent && !navigator.userAgent.match('CriOS');
    var isMSIE = window.navigator.userAgent.indexOf("MSIE")>0;

    //$("#labelPrintPreview").remove();

    var iframe = document.createElement('iframe');
    iframe.id="labelPrintPreview"+Math.round(Math.random()*1000000);

    var min_width=650;

    console.log("print images1:"+width+"x"+height);

    if(width<min_width){
        height=(height*min_width)/width;
        width=min_width;
    }

    console.log("print images1:"+width+"x"+height);

    iframe.scroll="auto";
    iframe.height=height;
    iframe.width=width;
    iframe.style.position="fixed";
    iframe.style.left="50%";
    iframe.style.top="50%";
    iframe.style.transform="translate(-50%,-50%)";
    /*if(isiOSBrowser()){
        iframe.style.top="-"+height+"px";
    }*/
    iframe.style.zIndex=10000;
    iframe.marginwidth="0";
    iframe.marginheight="0";
    iframe.hspace="0";
    iframe.vspace="0";
    iframe.style.background="white";
    document.body.appendChild(iframe);
    iframe.focus();

    /*iframe.onload = function(){
        if(isFirefox){
            iframe.onload = function(){
                //On Second Load -> Remove frame and call callback
                document.body.removeChild(iframe);
                if(callback)
                    callback();
            };
        }else if(isEdge || isMSIE){
            iframe.onblur = function(){
                console.log("onblur iframe");
                iframe.onfocus = function(){
                    console.log("onfocus iframe");
                    //On Second Load -> Remove frame and call callback
                    document.body.removeChild(iframe);
                    if(callback)
                        callback();
                };
            };

        }else { //Chrome and others
            document.body.removeChild(iframe);
            if (callback)
                callback();
        }
    };*/

    var win=undefined;
    if(isAndroidBrowser()) {
        win = window.open("");
    }

    // Create IE + others compatible event handler
    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";
    // Listen to message from child window
    eventer(messageEvent,function(e) {
        var start_message="Printing Finished ";
        if(e.data.indexOf(start_message)==0){
            if(isAndroidBrowser()){
                win.close();
            }
            var frame_id=e.data.substring(start_message.length);
            var iframe=$("#"+frame_id)[0];
            if(typeof iframe!="undefined" &&  iframe)
                document.body.removeChild(iframe);
            //document.body.removeChild(iframe);
            if (callback) {
                callback();
                callback=undefined;
            }
            //alert('parent received message!:  ',e.data);
        }

    },false);

    var extra_css="";
    if(isChrome || isFirefox){
        extra_css="@page { size: auto; margin: 0; }";
    }

    var jscode = "setTimeout(function(){window.print();window.print=undefined;parent.postMessage('Printing Finished "+iframe.id+"','*');},1000);";
    if(isiOSBrowser()) {
        jscode = "setTimeout(function(){window.print();window.print=undefined;},1000);";
    }else if(isAndroidBrowser()){
        win.onblur = function () {
            console.log("onblur iframe");
            win.onfocus = function () {
                console.log("onfocus iframe");
                //On Second Load -> Remove frame and call callback
                win.close();
                document.body.removeChild(iframe);
                if (callback) {
                    callback();
                    callback=undefined;
                }
            };
        };
    }
    else if (isEdge) {
        window.onblur = function () {
            console.log("onblur iframe");
            window.onfocus = function () {
                console.log("onfocus iframe");
                //On Second Load -> Remove frame and call callback
                document.body.removeChild(iframe);
                if (callback) {
                    callback();
                    callback=undefined;
                }
            };
        };
        jscode = "setTimeout(function(){window.print();window.print=undefined;},1000);";
    } else if(isFirefox){
        jscode = "window.onafterprint=function(){parent.postMessage('Printing Finished "+iframe.id+"');};setTimeout(function(){window.print();window.print=undefined;},1000);";
    }

    /*var jscode="window.onafterprint=function(){location.href='about:blank';};window.print();";
    if(isEdge || isMSIE){
        jscode="window.print();";
    }*/

    var img_style="width:100%;max-height:100%;";

    var htmlcode="<style>html, body { height: 98%; width:98%; margin:0; padding:0  } "+extra_css+" </style>";
    for(var i=0;i<image_data_urls.length;i++){
        var onloadcode="";
        if(i==image_data_urls.length-1){
            onloadcode=" onload=\""+jscode+"\"";
        }
        htmlcode+="<img src=\"" + image_data_urls[i] + "\" style=\""+img_style+"\" "+onloadcode+"/>";
        if(i<image_data_urls.length-1){
            htmlcode+="<br/>";
        }
   }

    if(isAndroidBrowser()){
        win.document.body.innerHTML=htmlcode;
    }else{
        iframedoc = iframe.contentDocument || iframe.contentWindow.document;
        if(isFirefox) {
            //iframe.src = canvas.toDataURL(); //TODO moznomarginboxes
            iframe.srcdoc = htmlcode;
        }else {
            iframedoc.body.innerHTML = htmlcode;
        }
    }
}

function isAndroidBrowser() {
    var ua = navigator.userAgent.toLowerCase();
    //console.log("User Agent:"+ua);
    return ua.indexOf("android") > -1 || ua.indexOf("linux") > -1;
}

function isiOSBrowser() {

    if(typeof isiOSVersion!="undefined"){
        return isiOSVersion;
    }

    var ua = navigator.userAgent.toLowerCase();
    var isIOS=(ua.match(/(ipad|iphone|ipod)/g) ? true : false);
    var isMac=ua.indexOf('mac') > -1;
    if(!isIOS && isMac){
        //https://51degrees.com/blog/missing-ipad-tablet-web-traffic
        function getReportedRenderer() {
            var canvas = document.createElement("canvas");
            if (canvas != null) {
                var context = canvas.getContext("webgl") ||
                    canvas.getContext("experimental-webgl");
                if (context) {
                    var info = context.getExtension(
                        "WEBGL_debug_renderer_info");
                    if (info) {
                        return context.getParameter(
                            info.UNMASKED_RENDERER_WEBGL);
                    }
                }
            }
        }

        renderer = getReportedRenderer();
        if(typeof renderer=="undefined" || typeof renderer.includes=="undefined"){
            isIOS=false;
        }
        else if (renderer.includes("Apple")) {
            isIOS=true;
        }
        else if (renderer.includes("Intel")) {
            isIOS=false;
        }
        isiOSVersion=isIOS;
    }
    //console.log("isIOS:"+isIOS);
    return isIOS;
}


function copyLabelContent(){
    var code=$('#summernote').summernote('code');
    if(code.length<10000000) {
        console.log("html_code:" + code);
        document.getElementById("label_content").value = code;
    }else{
        alert("The template is too large to save. Please reduce filesize of images.")
        return false;
    }
}

function insertProductImage(){
    var node = document.createElement('img');
    node.className = "product_image";
    node.src=image_sample_url;
    node.alt="[[cover_image_url]]";
    node.style.width="381px";
    node.style.height="202px";
    $('#summernote').summernote('insertNode', node);
}

function insertTextField(fields){
    var select_object=document.getElementById("summernote_fields_insert");
    var i=select_object.selectedIndex;
    if(i<1){
        return;
    }

    var newValue=select_object.value;
    if(typeof fields != "undefined" && fields[newValue].length>0){
        newValue=window.prompt(fields[newValue], select_object.value);
    }

    $('#summernote').summernote('insertText', '[['+newValue+']]');

    select_object.selectedIndex=0;
}

function insertBarcodeField(fields) {
    var select_object = document.getElementById("summernote_barcode_insert");
    var i = select_object.selectedIndex;

    if (i < 1) {
        return;
    }
    var node = document.createElement('img');
    node.className = "barcode";
    node.fieldname = select_object.value;
    if(typeof fields != "undefined" && fields[select_object.value]&&fields[select_object.value].length>0){
        node.name=window.prompt(fields[select_object.value], select_object.value);
    }else if (typeof select_object.value != "undefined" && select_object.value.length > 0){
        node.name = '[[' + select_object.value + ']]';
    }
    else{
        node.name=window.prompt("Please enter text", "");
    }
    node.src=barcode_sample_url;
    node.style.width="381px";
    node.style.height="202px";

    $('#summernote').summernote('insertNode', node);

    select_object.selectedIndex=0;

    addWaterMarkingToBarcodes();
}

function insertQRField(fields){
    var select_object=document.getElementById("summernote_qrcode_insert");
    var i=select_object.selectedIndex;

    if(i<1){
        return;
    }
    var node = document.createElement('img');
    node.className="qrcode";
    if(typeof fields != "undefined" && fields[select_object.value]&&fields[select_object.value].length>0){
        node.name=window.prompt(fields[select_object.value], select_object.value);
    }else if(typeof select_object.value != "undefined" && select_object.value.length>0) {
        node.name = '[[' + select_object.value + ']]';
    }else{
        node.name=window.prompt("Please enter text", "");
    }

    node.src=qrcode_sample_url;
    node.style.width="100px";
    node.style.height="100px";

    $('#summernote').summernote('insertNode', node);

    select_object.selectedIndex=0;

    addWaterMarkingToBarcodes();
}

function printTemplate(){
    var w=$("#width_input input")[0].value;
    var h=$("#height_input input")[0].value;
    var r=$("#rotate_image input")[0].checked;
    printHTML($('#summernote').summernote('code'), w, h, r, 1,function(){});
}