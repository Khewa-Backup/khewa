
<style>
.new-products-slider .owl-carousel .owl-nav button.owl-next:focus, 
.new-products-slider .owl-carousel .owl-nav button.owl-prev:focus{
    border: none;
    outline: none;
}
.new-products-slider .owl-carousel .owl-nav button.owl-next, 
.new-products-slider .owl-carousel .owl-nav button.owl-prev{
    position: absolute;
    bottom: 50%;
    left: -10px;
    cursor: pointer;
    font-size: 60px;
}
.new-products-slider .owl-carousel .owl-nav button.owl-next{
    left: auto;
    right: -10px;
}
.new-products-slider .owl-loaded .js-product-miniature-wrapper {
    width: 100%;
    display: inline;
}
section.new-products-slider img {
    max-width: 200px;
}
</style>

<section class="new-products-slider">
{*<h3>{l s='New products' d='Modules.Newproducts.Shop'}</h3>*}
<div class="owl-carousel products products-grid">
  {foreach from=$products item="product"}
    {include file="catalog/_partials/miniatures/product.tpl" product=$product}
  {/foreach}
</div>
{* <a href="{$allNewProductsLink}">{l s='All new products' d='Modules.Newproducts.Shop'}</a> *}
</section>

<script>
document.addEventListener('DOMContentLoaded', function(){
    $(".new-products-slider .products").owlCarousel({
        items: 4,
        loop: true,
        nav: true,
        autoplay: true,
        dots: false,
        margin: 8,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 2
            },
            1000: {
                items: 3
            },
            1200: {
                items: 4
            }
        }
    });
});
</script>