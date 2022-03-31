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
</script>

<fieldset>
    <legend>Upload Label Template</legend>
    {if isset($error) }
        <div class="error">{$error|escape:'html':'UTF-8'}</div>
    {/if}
    {if isset($success) }
        <div class="conf">{$success|escape:'html':'UTF-8'}</div>
    {/if}
    <table>
        <form action="{$formactionurl|escape:'html':'UTF-8'}" method="POST" enctype="multipart/form-data" >
            <tr>
                <td><label for="upload_file">Label File:</label></td>
                <td><input name="filelabel"  id="upload_file" type="file" /></td>
            </tr>
            <tr>
                <td>&nbsp</td>
                <td><input  class="button" type="submit" value="Upload" name="upload" /></td>
            </tr>
            <tr>
                <td colspan="2">
                    &nbsp;<br/>&nbsp;<br/>
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
                    <div style="float:left">
                        <ul style="float:left">
                            <li>product_name</li>
                            <li>id_product</li>
                            <li>ean13</li>
                            <li>upc</li>
                            <li>manufacturer_name</li>
                            <li>reference</li>
                            <li>location</li>
                            <li>width</li>
                            <li>height</li>
                            <li>depth</li>
                            <li>weight</li>
                        </ul>
                        <ul style="float:right">
                            <li>supplier_reference</li>
                            <li>supplier_name</li>
                            <li>on_sale</li>
                            <li>minimal_quantity</li>
                            <li>price</li>
                            <li>wholesale_price</li>
                            <li>unity</li>
                            <li>unit_price_ratio</li>
                            <li>condition</li>
                            <li>date_add</li>
                            <li>date_upd</li>
                            <li>pack_stock_type</li>
                        </ul>
                    </div><br/>

                </td>
            </tr>

        </form>
        </table>
</fieldset><br /><br />