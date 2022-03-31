/**
 * 2016-2017 Leone MusicReader B.V.
 *
 * NOTICE OF LICENSE
 *
 * Source file is copyrighted by Leone MusicReader B.V.
 * Only licensed users may install, use and alter it.
 * Original and altered files may not be (re)distributed without permission.
 *
 * @author    Leone MusicReader B.V.
 *
 * @copyright 2018 Leone MusicReader B.V.
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
                        text=text.trim().replace(/\n/g,"<br/>");
                        console.log("Generic Printer - Set Text Object "+v+" to "+text);
                        this.template=this.template.replace("[["+v+"]]",text);
                        this.template=this.template.replace("[["+v+"]]",text);
                        this.template=this.template.replace("[["+v+"]]",text);
                        this.template=this.template.replace("[["+v+"]]",text);
                        this.template=this.template.replace("[["+v+"]]",text);
                    },
                    print:function(printerName,count,unknownVar,lastPrint) {
                        console.log("last print:"+lastPrint);
                        if(!count || count==0 || count==""){
                            count=1;
                        }
                        if(typeof lastPrint=="undefined"){
                            console.log("print label to:"+printerName);
                            printHTML(this.template,this.width,this.height,this.rotate,count);
                        }else{
                            if(!lastPrint){
                                if(typeof dymo.templates == "undefined"){
                                    dymo.templates=[];
                                    dymo.counts=[];
                                }
                                dymo.templates[dymo.templates.length]=this.template;
                                dymo.counts[dymo.counts.length]=count;
                            }
                            else{
                                if(typeof dymo.templates == "undefined"){
                                    dymo.templates=[];
                                    dymo.counts=[];
                                }
                                dymo.templates[dymo.templates.length]=this.template;
                                dymo.counts[dymo.counts.length]=count;
                                printMultipleHTML(dymo.templates,this.width,this.height,this.rotate,dymo.counts);
                                dymo.templates=[];
                                dymo.counts=[];
                            }
                        }
                    },
                };
                if(url.indexOf("ShippingAddress")<0 || (typeof third_party_module_dlpp != "undefined" && third_party_module_dlpp)) {
                    obj.template = dlppb_generic_label_content;
                    obj.width = dlppb_generic_label_width;
                    obj.height = dlppb_generic_label_height;
                    obj.rotate = dlppb_generic_label_rotate;
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

    var fixed_width=650;

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
            ['code',['codeview']]
        ]
    });
}

function printHTML(code, width,height,rotate,count) {

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
                    printMultipleImages(images,height,width,rotate);
                else
                    printMultipleImages(images,width,height,rotate);
            }

        });
    }

    processPage(0,code_pages);

}

function printMultipleHTML(codes_1, width,height,rotate,counts) {

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
                    printMultipleImages(images,height,width,rotate);
                else{
                    printMultipleImages(images,width,height,rotate);
                }
            }
        });
    }
    processHTML(codes,0);
}

function generateImageFromHTML(code, width,height,rotate,callback){

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
    div.id="html_renderer_div";
    document.body.appendChild(div);

    //Set BarCodes - http://lindell.me/JsBarcode/
    if(typeof JsBarcode != "undefined") {
        var barcodes = $("#html_renderer_div .barcode");
        for (var i = 0; i < barcodes.length; i++) {
            barcodes[i].src = undefined;
            barcodes[i].tagName = "svg";
            $(barcodes[i]).attr("jsbarcode-format", "auto");
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

        /*var canvas2=document.createElement("canvas");
        canvas2.width=width;
        canvas2.height=height;*/

        html2canvas(div, {width: width, height: height, canvas:canvas, embedImages:true}).then(function (canvas) {

            if(rotate){
                canvas.context.restore();
            }

            document.body.removeChild(div);

            //callback(canvas2.toDataURL());

            var myRectangle = canvas.context.getSerializedSvg(true);

            //console.log(myRectangle);

            var data_url="data:image/svg+xml;utf8,"+encodeURIComponent(myRectangle);
            //console.log(data_url);

            //document.body.innerHTML+=myRectangle;

            callback(data_url);

            /*if (rotate) {
                // store current data to an image
                var myImageData = new Image();
                myImageData.src = canvas.toDataURL();

                myImageData.onload = function () {
                    // reset the canvas with new dimensions
                    canvas.width = height;
                    canvas.height = width;
                    width = canvas.width;
                    height = canvas.height;

                    context = canvas.getContext('2d');

                    context.save();
                    // translate and rotate
                    context.translate(width, 0);
                    context.rotate(Math.PI / 2);
                    // draw the previows image, now rotated
                    context.drawImage(myImageData, 0, 0, height, width);
                    context.restore();

                    // clear the temporary image
                    myImageData = null;

                    callback(canvas.toDataURL());

                }
            } else {
                callback(canvas.toDataURL());
            }*/
        });
    },50);
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

    $("#labelPrintPreview").remove();

    var iframe = document.createElement('iframe');
    iframe.id="labelPrintPreview";

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
    iframe.style.position="absolute";
    iframe.style.left="0px";
    iframe.style.top="0px";
    if(isiOSBrowser()){
        iframe.style.top="-"+height+"px";
    }
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
        if(e.data=="Printing Finished"){
            if(isAndroidBrowser()){
                win.close();
            }
            document.body.removeChild(iframe);
            if (callback)
                callback();
            //alert('parent received message!:  ',e.data);
        }

    },false);

    var extra_css="";
    if(isChrome || isFirefox){
        extra_css="@page { size: auto; margin: 0; }";
    }

    var jscode = "setTimeout(function(){window.print();parent.postMessage('Printing Finished','*');},1000);";
    if(isiOSBrowser()) {
        jscode = "setTimeout(function(){window.print();},1000);";
    }else if(isAndroidBrowser()){
        win.onblur = function () {
            console.log("onblur iframe");
            win.onfocus = function () {
                console.log("onfocus iframe");
                //On Second Load -> Remove frame and call callback
                win.close();
                document.body.removeChild(iframe);
                if (callback)
                    callback();
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
                if (callback)
                    callback();
            };
        };
        jscode = "setTimeout(function(){window.print();},1000);";
    } else if(isFirefox){
        jscode = "window.onafterprint=function(){parent.postMessage('Printing Finished');};setTimeout(function(){window.print();},1000);";
    }

    /*var jscode="window.onafterprint=function(){location.href='about:blank';};window.print();";
    if(isEdge || isMSIE){
        jscode="window.print();";
    }*/

    var img_style="width:100%;max-height:100%;"

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
    if(code.length<64000) {
        console.log("html_code:" + code);
        document.getElementById("label_content").value = code;
    }else{
        alert("The template is too large to save. Please reduce filesize of images.")
        return false;
    }
}

function insertTextField(){
    var select_object=document.getElementById("summernote_fields_insert");
    var i=select_object.selectedIndex;
    if(i<1){
        return;
    }

    $('#summernote').summernote('insertText', '[['+select_object.value+']]');

    select_object.selectedIndex=0;
}

function insertBarcodeField(){
    var select_object=document.getElementById("summernote_barcode_insert");
    var i=select_object.selectedIndex;

    if(i<1){
        return;
    }
    var node = document.createElement('img');
    node.className="barcode";
    if(typeof select_object.value != "undefined" && select_object.value.length>0)
        node.name='[['+select_object.value+']]';
    else{
        node.name=window.prompt("Please enter text", "");
    }
    node.src=barcode_sample_url;
    node.style.width="381px";
    node.style.height="202px";

    $('#summernote').summernote('insertNode', node);

    select_object.selectedIndex=0;
}

function insertQRField(){
    var select_object=document.getElementById("summernote_qrcode_insert");
    var i=select_object.selectedIndex;

    if(i<1){
        return;
    }
    var node = document.createElement('img');
    node.className="qrcode";
    if(typeof select_object.value != "undefined" && select_object.value.length>0) {
        node.name = '[[' + select_object.value + ']]';
    }else{
        node.name=window.prompt("Please enter text", "");
    }

    node.src=qrcode_sample_url;
    node.style.width="100px";
    node.style.height="100px";

    $('#summernote').summernote('insertNode', node);

    select_object.selectedIndex=0;
}

function printTemplate(){
    var w=$("#width_input input")[0].value;
    var h=$("#height_input input")[0].value;
    var r=$("#rotate_image input")[0].checked;
    printHTML($('#summernote').summernote('code'), w, h, r);
}
