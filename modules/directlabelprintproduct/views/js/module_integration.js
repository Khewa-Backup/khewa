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

/*ec_scan_ean13*/
function printLabelTPM(){
    var ean13=$("#ean13").val();
    var quantity=$("#quantity").val();
    //alert("Found:"+ean13+"-"+quantity);
    if(parseInt(quantity)>0)
        printLabelFromBarcode(ean13,quantity);
    else
        printLabelFromBarcode(ean13);
}
/*end ec_scan_ean13*/

var documentReadyTPM=function() {

    /**/
    var button_areas=$("#table-product tbody .btn_actions");
    var select_boxes=$("#table-product tbody .row-selector input");

    for(var i=0;i<button_areas.length;i++){
        button_areas[i].innerHTML+='<a class="btn btn-default" title="Pint Label" href="javascript:;" onclick="printProductLabelOf('+select_boxes[i].value+',0)"><img src="'+location.protocol+'//'+location.hostname+(location.port ? ':'+location.port: '')+'/modules/directlabelprintproduct/views/img/icon-print.png" height="15"/></a>';
    }

    setTimeout(function(){
        show_combination_parent=show_combinations;
        show_combinations=function(product_id){
            show_combination_parent(product_id);
            function activateNow() {
                setTimeout(function () {
                    if(document.getElementById("table-combinations-list"))
                            activateInCombinationPopup(product_id);
                    else{
                        activateNow();
                    }
                }, 500);
            }
            activateNow();
        }
    },1000);

    /*ec_scan_ean13*/
    setTimeout(function(){
        $("#module_form .panel-footer").append("" +
            "<a href=\"#\" OnClick=\"printLabelTPM()\" class=\"btn btn-default pull-right\">"+
            "<img src=\""+location.protocol+'//'+location.hostname+(location.port ? ':'+location.port: '')+"/modules/directlabelprintproduct/views/img/icon-print.png\" height=\"100%\"/>"+
            "</a>");
        console.log("Print Button Added!!!!!!!")
    },100);
    /*end ec_scan_ean13*/
};

function activateInCombinationPopup(product_id){
    var button_areas=$("#table-combinations-list tbody .fixed-width-sm");
    var select_boxes=$("#table-combinations-list tbody .row-selector input");

    for(var i=0;i<button_areas.length;i++){
        button_areas[i].innerHTML+='<a class="btn btn-default" title="Pint Label" href="javascript:;" onclick="printProductLabelOf('+product_id+','+select_boxes[i].value+')"><img src="'+location.protocol+'//'+location.hostname+(location.port ? ':'+location.port: '')+'/modules/directlabelprintproduct/views/img/icon-print.png" height="15"/></a>';
    }

}

if(window.attachEvent) {
    window.attachEvent('onload', documentReadyTPM);
} else {
    if(window.onload) {
        var curronload = window.onload;
        var newonload = function(curronload,evt) {
            curronload(evt);
            documentReadyTPM(evt);
        }.bind(this,curronload);
        window.onload = newonload;
    } else {
        window.onload = documentReadyTPM;
    }
}

var third_party_module_dlpp=true;//for detection in javascript