<?php


class Khwprodbulkmanu extends Module
{
    public function __construct()
    {
        $this->name = "khwprodbulkmanu";
        $this->tab = 'administration';
        $this->version = 1.0;
        $this->author = 'Masudur Rahman';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;


        $this->displayName = $this->l('Khewa Admin Bulk Product Manufacturer');
        $this->description = $this->l('Khewa Admin Bulk Product Manufacturer');

        $this->confirmUninstall = $this->l('Uninstall the module?');
        parent::__construct();
    }
    public function install()
    {
        return parent::install()
            && $this->registerHook('displayBackOfficeHeader')
            && $this->registerHook('displayBackOfficeFooter')
            ;
    }
    public function hookDisplayBackOfficeHeader()
    {
        if(Tools::isSubmit('khwaction')){
            if(Tools::getValue('khwaction') == 'assignMan'){
                $products = Tools::getValue('products');
                $man = Tools::getValue('man', 0);
                if($products){
                    foreach($products as $id_prod){
                        $product = new Product((int) $id_prod);
                        $product->id_manufacturer = (int)$man;
                        $product->save();
                    }
                }
                die();
            }elseif(Tools::getValue('khwaction') == 'assignSup'){
                $products = Tools::getValue('products');
                $sups = Tools::getValue('sup', 0);
                
                if($products){
                    foreach($products as $id_prod){
                        $product = new Product((int) $id_prod);
                        $product->deleteFromSupplier();

                        if($sups){
                            foreach($sups as $sup){
                                $product->addSupplierReference($sup['value'], 0);
                            }
                        }

                        // $product->id_supplier = (int)$sup;
                        // $product->save();
                    }
                }
                die();
            }
        }

    }
    public function hookDisplayBackOfficeFooter()
    {
        if(Tools::getValue('controller') == 'AdminProducts'){
            if(preg_match('@/sell/catalog/products/\d+@', $_SERVER['REQUEST_URI'])){
                return;
            }

            $html = '';
            $mans = Manufacturer::getManufacturers();
            $sups = Supplier::getSuppliers();

            ob_start();

            if($mans) {
                ?>
                <script>

                    var khwAssignManuf = function(mform){
                        const form = $('#product_catalog_list');
                        const items = $('input:checked[name="bulk_action_selected_products[]"]', form);
                        let man = mform.find('[name="selected-man"]').val();
                        let selected = [];

                        items.each(function(){
                            selected.push($(this).val());
                        });

                        var url = location.href;
                        url = url.substr(0,url.lastIndexOf('#'));
                        url += '&khwaction=assignMan';

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: {
                                products: selected,
                                man: man
                            }
                        });

                        $('#khwManModal').modal('hide');

                    };
                    
                    var khwOpenBulkManModal = function(){
                        $('#khwManModal').modal('show');
                    };
                    

                    document.addEventListener('DOMContentLoaded', function(e){
                        var p = $('#product_bulk_menu + .dropdown-menu');
                        p.append(`<a class="dropdown-item" href="#" onclick="khwOpenBulkManModal();">
                                  <i class="material-icons">branding_watermark</i>
                                  <?php echo $this->l('Set Manufacturer');?>
                                </a>`);
                        
                        $('#khwManModal form').on('submit', function(e){
                            e.preventDefault();
                            khwAssignManuf($(this));
                        });
                        
                    });
                </script>


                <!-- Modal -->
                <div class="modal fade" id="khwManModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <form>
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?php echo $this->l('Set Manufacturer For Selection');?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <select class="form-control" name="selected-man">
                                        <option value="0"><?php echo $this->l('No Manufacturer');?></option>
                                        <?php foreach($mans as $man){?>
                                            <option value="<?php echo $man['id_manufacturer']?>"><?php echo $man['name']?></option>
                                        <?php }?>
                                    </select>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $this->l('Close');?></button>
                                    <button type="submit" class="btn btn-primary"><?php echo $this->l('Save');?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } if($sups){ ?>

                <script>
                    var khwAssignSup = function(mform){
                        const form = $('#product_catalog_list');
                        const items = $('input:checked[name="bulk_action_selected_products[]"]', form);
                        
                        let fdata = mform.serializeArray();
                        let selected = [];

                        items.each(function(){
                            selected.push($(this).val());
                        });

                        let data = {
                            products: selected,
                            sup: fdata
                        };



                        var url = location.href;
                        url = url.substr(0,url.lastIndexOf('#'));
                        url += '&khwaction=assignSup';

                        $.ajax({
                            url: url,
                            method: 'POST',
                            data: data
                        });

                        $('#khwSupModal').modal('hide');

                    };

                    var khwOpenBulkSupModal = function(){
                        $('#khwSupModal').modal('show');
                    };

                    document.addEventListener('DOMContentLoaded', function(e){
                        var p = $('#product_bulk_menu + .dropdown-menu');
                        
                        p.append(`<a class="dropdown-item" href="#" onclick="khwOpenBulkSupModal();">
                                  <i class="material-icons">branding_watermark</i>
                                  <?php echo $this->l('Set Supplier');?>
                                </a>`);
                        
                        $('#khwSupModal form').on('submit', function(e){
                            e.preventDefault();
                            khwAssignSup($(this));
                        });
                    });

                </script>

                <div class="modal fade" id="khwSupModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <form>
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><?php echo $this->l('Set Supplier For Selection');?></h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    
                                    <?php 
                                    if(!empty($sups))
                                    foreach($sups as $sup){?>
                                        <label><input type="checkbox" name="selected-sup[]" value="<?php echo $sup['id_supplier']?>"> <?php echo $sup['name']?></label><br>
                                    <?php }?>
                                    
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo $this->l('Close');?></button>
                                    <button type="submit" class="btn btn-primary"><?php echo $this->l('Save');?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }
            $html.= ob_get_clean();

            return $html;
        }

    }

}