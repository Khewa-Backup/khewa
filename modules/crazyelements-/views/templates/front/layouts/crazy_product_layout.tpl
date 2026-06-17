
{extends file="layouts/layout-full-width.tpl"}
{block name='content'}
  <section id="main" class="classy_layout_parent" itemscope itemtype="https://schema.org/Product">
    <meta itemprop="url" content="{$product.url}">
    <input type="hidden" value="{$parsed}">
    {$content nofilter}
    {block name='product_footer'}
      {hook h='displayFooterProduct' product=$product category=$category}
    {/block}
    {block name='product_images_modal'}
      {include file='catalog/_partials/product-images-modal.tpl'}
    {/block}
    {block name='page_footer_container'}
      <footer class="page-footer">
        {block name='page_footer'}
          <!-- Footer content -->
        {/block}
      </footer>
    {/block}
  </section>
{/block}