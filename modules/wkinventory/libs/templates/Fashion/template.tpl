{*
* This file is part of the 'WK Inventory' module feature.
* Developped by Khoufi Wissem (2017).
* You are not allowed to use it on several site
* You are not allowed to sell or redistribute this module
* This header must not be removed
*
*  @author    KHOUFI Wissem - K.W
*  @copyright Khoufi Wissem
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<?php foreach($products as $product){ ?>
    <div class="product">
        <div class="description">
            <?php echo MakePlainText($product['description'], 750); ?>
        </div>
        <div class="name">
            <?php echo MakePlainText($product['name'],25); ?>
        </div>
        <div class="image">
            <img src="<?php echo $this->getCatalogImagePath($product['id_image']); ?>" style="max-height: 240px; min-height: 240px;" />
        </div>
        <?php if( $this->hasSpecialPrice($product['id_product'], false) ){ ?>
            <div class="price">
                <img src="<?php echo $this->template_dir; ?>img/price_bg.png" />
                <div style="margin-top: -32px;"><s><?php echo $this->getSpecialPrice($product['id_product'], false); ?></s></div>
            </div>
            <div class="special"> 
                <img src="<?php echo $this->template_dir; ?>img/new_price_bg.png" />
                <div style="margin-top: -32px;"><?php echo $this->getPrice($product['id_product'], false); ?></div>
            </div>
        <?php }else{ ?>
            <div class="price">
                <img src="<?php echo $this->template_dir; ?>img/price_bg.png" />
                <div style="margin-top: -32px;"><?php echo $this->getPrice($product['id_product'], false); ?></div>
            </div>
        <?php } ?>
        <div class="info">
            <div class="manufacturer"><?php echo $this->l('Manufacturer').' '.$product['manufacturer']; ?></div>
            <div class="code"><?php echo $this->l('Model').' '.$product['model']; ?></div>
        </div>
    </div>
<?php } ?>