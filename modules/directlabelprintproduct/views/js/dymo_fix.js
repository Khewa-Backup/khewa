/**
 * 2021 Leone MusicReader B.V.
 *
 * NOTICE OF LICENSE
 *
 * Source file is copyrighted by Leone MusicReader B.V.
 * Only licensed users may install, use and alter it.
 * Original and altered files may not be (re)distributed without permission.
 *
 * @author    Leone MusicReader B.V.
 *
 * @copyright 2021 Leone MusicReader B.V.
 *
 * @license   custom see above
 */

if(typeof dymo.label.framework.openLabelXml2=="undefined"){
    dymo.label.framework.openLabelXml2=dymo.label.framework.openLabelXml;

    dymo.label.framework.openLabelXml=function(xml){

        label=dymo.label.framework.openLabelXml2(xml);

        function fixChildren(elem) {
            if (elem.children.length == 0) {
                if (elem.outerHTML.endsWith("/>") && !elem.outerHTML.includes("DYMO")) {
                    const rawTag = elem.outerHTML.substring(0, elem.outerHTML.length - 2).split(" ")[0];
                    const cleanTag = rawTag.substring(1, rawTag.length);
                    const fixedLine = `${elem.outerHTML.replace("/>", ">")}</${cleanTag}>`;
                    return fixedLine;
                }
                return elem.outerHTML;
            }
            const children = Array.from(elem.children);
            const inner = children.map(c => fixChildren(c)).join("");
            return elem.outerHTML.replace(elem.innerHTML, inner);
        }

        //FIX FOR DYMO BUG
        label.getLabelXml2=label.getLabelXml;
        label.getLabelXml=function() {
            const parser = new DOMParser();
            const doc = parser.parseFromString(label.getLabelXml2(), 'application/xml');
            const DCDXml = Array.from(doc.getElementsByTagName("DesktopLabel")) ?? [];
            if(DCDXml.length==0){
                return label.getLabelXml2();
            }
            const elem = DCDXml[0];
            if (!elem) {
                return label.getLabelXml2();
            }
            const fixed = fixChildren(elem);
            return fixed;
        };

        return label;

    };

}