<div class="tiny-product">

	<div class="tiny-product__main">
	  
		{* Лейблы товара *}
		<div class="tiny-product__labels">
			{if $product->attachment_files_one}
				<span class="tiny-product__labels-item tiny-product__labels-item--hit">
					<img src="/files/documents/{$product->attachment_files_one}" alt="">
				</span>
			{/if}
			{if $smarty.get.module =='TagsView'}
			  {if $product->variant->compare_price > 0}
			    {if $product->variant->compare_price > 0 && $product->variant->price > 0}
					  <span class="tiny-product__labels-item tiny-product__labels-item--sale">- {(($product->variant->compare_price - $product->variant->price) * 100 / $product->variant->compare_price)|string_format:"%d"} %</span>
			    {else}
					<span class="tiny-product__labels-item tiny-product__labels-item--sale">до - {(($product->min_compare_price - $product->min_price) * 100 / $product->min_compare_price)|string_format:"%d"} %</span>
					{/if}
				{/if}
			{else}
			  {if $product->variant->compare_price}
			    {if $product->equally_compare_price > 0 && $product->equally_price > 0}
				    <span class="tiny-product__labels-item tiny-product__labels-item--sale">
				  	- {(($product->equally_compare_price - $product->equally_price) * 100 / $product->equally_compare_price)|string_format:"%d"} %
				    </span>
			    {else}
				    <span class="tiny-product__labels-item tiny-product__labels-item--sale">до - {(($product->min_compare_price - $product->min_price) * 100 / $product->min_compare_price)|string_format:"%d"} %</span>
		  	  {/if}
	   	  {/if}
			{/if}

		</div>
		{* /Лейблы товара *}
		
		{* Изображение товара *}
		<div class="tiny-product__image">
			{if $product->images}
				<img src="{$product->image->filename|escape|resize:190:190}" alt="{$product->name|escape}"/>
			{else}
				<img src="/design/{$settings->theme}/images/defaultPhoto.png" alt="{$product->name|escape}"/>
			{/if}
		</div>
		{* /Изображение товара *}
		
		{* Название товара *}
		<div class="tiny-product__name">
			{$product->name|escape}
		</div>
		{* /Название товара *}
		
		{* Цены товара *}
		<div class="tiny-product__price">
			{* Старая цена *}
			{if $product->variant->compare_price}
				<span class="tiny-product__price-item tiny-product__price-item--old">
					{$product->variant->compare_price|convert} {$currency->sign}
				</span>
			{/if}
			<span class="tiny-product__price-item {if $product->max_sale<0}tiny-product__price-item--maxsale{/if}">
				{$product->variant->price|convert} {$currency->sign}
			</span>
		</div>
		{* Цены товара *}
		
		<a href="/{$product->cat_url}/u_{$product->url}" class="tiny-product__link"></a>
	
	</div>
	
	<div class="tiny-product__bar">
	
		{* Форма товара *}
		<form class="tiny-product__form tocart">
			{* вот таким забавным способом передается кол-во товара *}
			<span class="number__value" style="display: none">1</span>
			<div class="tiny-product__form-item">
				<input style="display: none" type="radio" name="variant" value="{$product->variant->id}" checked/>
				<input class="tiny-product__form-button" type="submit" value="В корзину" onclick="yaCounter36200345.reachGoal('CART'); return true;"  data-result-text="В корзине"/>
			</div>
			<div class="tiny-product__form-item">
				{*<a  data-popup-button="callback" class="tiny-product__form-button tiny-product__form-button--rounded">Быстрый заказ</a>*}
				<a href="#onclick" data-popup-button="callback" class="tiny-product__form-button tiny-product__form-button--rounded">Быстрый заказ</a>
			</div>
		</form>
		{* /Форма товара *}
		
		{* Краткое описание товара *}
		{if $product->annotation}
			<div class="tiny-product__annotation">
				{$product->annotation|strip_tags}
			</div>
		{/if}
		{* /Краткое описание товара *}
	</div>
	
</div>