{*
* 2020 Anvanto
*
* NOTICE OF LICENSE
*
* This file is not open source! Each license that you purchased is only available for 1 wesite only.
* If you want to use this file on more websites (or projects), you need to purchase additional licenses. 
* You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
*
*  @author Anvanto <anvantoco@gmail.com>
*  @copyright  2020 Anvanto
*  @license    Valid for 1 website (or project) for each purchase of license
*  International Registered Trademark & Property of Anvanto
*}

{extends file='page.tpl'}

	{block name='page_title'}
		{l s='My wishlist' mod='an_wishlist'}
	{/block}
	
	{block name='page_content_container'}
	{*
		<input class="form-control" name="an_wishlist_link" type="text" value="{$wishlistLink|escape:'quotes'}" required="" style="margin-bottom: 10px;">
	*}
	
	{if $config.display_social_buttons=='1'}
        <section id="social" class="clearfix an_wishlist-social">
            <div class="an_wishlist-social-list">
                <!-- Facebook Button -->
                <div class="itemSocialButton itemFacebookButton">
                    <a href="http://www.facebook.com/sharer.php?u={$wishlistLink|escape:'quotes'}" class="facebook-share-button" data-count="horizontal" target="_blank">
                        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                        	 viewBox="0 0 290 290" style="enable-background:new 0 0 290 290;" xml:space="preserve">
                        <path d="M205.807,0h-15.694c-43.62,0-79.107,35.488-79.107,79.108v33.386H84.193c-2.761,0-5,2.239-5,5v40c0,2.761,2.239,5,5,5
                        	h26.811V285c0,2.761,2.239,5,5,5h40c2.761,0,5-2.239,5-5V162.494h39.786c2.761,0,5-2.239,5-5v-40c0-2.761-2.239-5-5-5h-39.786
                        	V79.108c0-16.05,13.058-29.108,29.107-29.108h15.694c2.761,0,5-2.239,5-5V5C210.807,2.239,208.568,0,205.807,0z"/>
                        </svg>
                        {l s='Facebook' mod='an_wishlist'}
                    </a>
                </div>
                <!-- Twitter Button -->
                <div class="itemSocialButton itemTwitterButton">
                    <a href="https://twitter.com/intent/tweet?text=My whishlist {$wishlistLink|escape:'quotes'}" class="twitter-share-button" data-count="horizontal" target="_blank">
                        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                        	 viewBox="0 0 321.666 321.666" style="enable-background:new 0 0 321.666 321.666;" xml:space="preserve">
                        <path d="M320.518,70.438c-1.187-1.076-2.952-1.21-4.287-0.325c-5.903,3.916-13.86,5.904-20.473,6.914
                        	c7.907-6.45,17.13-16.588,17.069-29.652c-0.006-1.314-0.748-2.515-1.921-3.108c-1.173-0.593-2.58-0.478-3.642,0.296
                        	C295.279,53.309,278.1,57.903,271.81,59.37c-4.448-7.33-19.746-28.824-46.187-28.824c-1.479,0-2.988,0.07-4.485,0.207
                        	c-32.859,3.022-48.781,22.237-56.351,37.825c-4.786,9.855-6.888,19.397-7.809,25.699c-5.211-4.542-14.3-11.454-27.829-18.371
                        	C108.481,65.337,72.983,52.739,21.247,52.739c-5.03,0-10.197,0.119-15.358,0.354c-1.174,0.054-2.243,0.693-2.846,1.702
                        	c-0.603,1.009-0.659,2.254-0.148,3.313C13.937,81.04,37.69,94.51,53.153,101.18c-8.484,2.248-17.549,6.634-20.388,13.544
                        	c-1.441,3.508-1.811,9.021,4.608,15.364c9.424,9.312,20.503,14.97,30.265,18.405c-7.648,1.361-13.755,3.697-15.735,7.584
                        	c-0.753,1.48-1.612,4.518,1.1,8.246c13.001,17.878,44.162,24.83,57.98,25.964c-1.753,4.165-5.404,10.928-12.455,17.626
                        	c-15.066,14.309-38.822,21.873-68.7,21.874c-0.003,0-0.006,0-0.009,0c-8.119,0-16.833-0.55-25.903-1.636
                        	c-1.498-0.177-2.944,0.622-3.585,1.99c-0.641,1.367-0.333,2.991,0.764,4.028C40.484,271.42,85.2,291.113,130.41,291.12
                        	c0.009,0,0.015,0,0.023,0c49.772,0,98.504-24.472,130.357-65.463c28.367-36.505,39.233-80.199,30.06-120.383
                        	c6.128-2.623,19.655-10.379,30.406-30.602C322.008,73.258,321.704,71.514,320.518,70.438z"/>
                        </svg>

                         {l s='Twitter' mod='an_wishlist'}
                    </a>
                </div>
                <!-- Linkedin Button -->
                <div class="itemSocialButton itemLinkedinButton">
                    <a href="https://www.linkedin.com/shareArticle?mini=true&url={$wishlistLink|escape:'quotes'}&title=My whishlist&source=LinkedIn" class="linkedin-share-button" data-count="horizontal" target="_blank">
                        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                        	 viewBox="0 0 260.366 260.366" style="enable-background:new 0 0 260.366 260.366;" xml:space="preserve">
                        	<path d="M34.703,0.183C15.582,0.183,0.014,15.748,0,34.884C0,54.02,15.568,69.588,34.703,69.588
                        		c19.128,0,34.688-15.568,34.688-34.704C69.391,15.75,53.83,0.183,34.703,0.183z"/>
                        	<path d="M60.748,83.531H8.654c-2.478,0-4.488,2.009-4.488,4.489v167.675c0,2.479,2.01,4.488,4.488,4.488h52.093
                        		c2.479,0,4.489-2.01,4.489-4.488V88.02C65.237,85.539,63.227,83.531,60.748,83.531z"/>
                        	<path d="M193.924,81.557c-19.064,0-35.817,5.805-46.04,15.271V88.02c0-2.48-2.01-4.489-4.489-4.489H93.424
                        		c-2.479,0-4.489,2.009-4.489,4.489v167.675c0,2.479,2.01,4.488,4.489,4.488h52.044c2.479,0,4.489-2.01,4.489-4.488v-82.957
                        		c0-23.802,4.378-38.555,26.227-38.555c21.526,0.026,23.137,15.846,23.137,39.977v81.535c0,2.479,2.01,4.488,4.49,4.488h52.068
                        		c2.478,0,4.488-2.01,4.488-4.488v-91.977C260.366,125.465,252.814,81.557,193.924,81.557z"/>
                        </svg>

                         {l s='LinkedIn' mod='an_wishlist'}
                    </a>
                </div>


                <!-- Tumblr Button -->
                <div class="itemSocialButton itemTumblrButton">
                    <a href="http://www.tumblr.com/share/link?url={$wishlistLink|escape:'quotes'}" class="tumblr-share-button" data-count="horizontal" target="_blank">
                        <svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                        	 viewBox="0 0 260.366 260.366" style="enable-background:new 0 0 260.366 260.366;" xml:space="preserve">
                        	<path d="M210.857,197.545c-1.616-0.872-3.584-0.787-5.119,0.223c-11.62,7.638-23.4,11.511-35.016,11.511  c-6.242,0-11.605-1.394-16.416-4.275c-3.27-1.936-6.308-5.321-7.397-8.263c-1.057-2.797-1.045-10.327-1.029-20.748l0.005-63.543  h52.795c2.762,0,5-2.239,5-5V62.802c0-2.761-2.238-5-5-5h-52.795V5c0-2.761-2.238-5-5-5h-35.566c-2.528,0-4.658,1.887-4.964,4.397  c-1.486,12.229-4.258,22.383-8.247,30.196c-3.89,7.7-9.153,14.401-15.651,19.925c-5.206,4.44-14.118,8.736-26.49,12.769  c-2.058,0.671-3.45,2.589-3.45,4.754v35.41c0,2.761,2.238,5,5,5h28.953v82.666c0,12.181,1.292,21.347,3.952,28.026  c2.71,6.785,7.521,13.174,14.303,18.993c6.671,5.716,14.79,10.187,24.158,13.298c9.082,2.962,16.315,4.567,28.511,4.567  c10.31,0,20.137-1.069,29.213-3.179c8.921-2.082,19.017-5.761,30.008-10.934c1.753-0.825,2.871-2.587,2.871-4.524v-39.417  C213.484,200.108,212.476,198.418,210.857,197.545z"/>
                        </svg>

                        {l s='Tumblr' mod='an_wishlist'}
                    </a>
                </div>
            </div>
        </section>
    {/if}

	{if count($products)<1 }
	<article class="alert alert-warning" role="alert" data-alert="warning">
	{l s='Wishlist is empty' mod='an_wishlist'}
	</article>		 
	{else}
	<section id="products" class="clearfix an_wishlist-list"> 

		<div class="products row">
		{foreach from=$products item="product"}
		  {include file='catalog/_partials/miniatures/product.tpl' product=$product}
		{/foreach}
		</div>		

	</section>
	{/if}

	{/block}

{block name='page_footer'}
    {block name='my_account_links'}
        {include file='customer/_partials/my-account-links.tpl'}
    {/block}
{/block}