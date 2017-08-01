{*
*  @author    Olga Kuznetsova <olkuznw@gmail.com>
*  @copyright odev.me 2017
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  @version   1.0.0
*
* Languages: EN
* PS version: 1.6
**}

<!-- Pricematch module -->
<div id="pricematchpopup" class="pricematchpopup">
	<div class="pricematch-loader"></div>
	<div class="pricematch-error col-xs-12"></div>
	<div class="rte col-xs-12">
		{if !empty($description)}
			{$description|escape:'html':'UTF-8'|htmlspecialchars_decode:3}
		{/if}
	</div>
	<form id="pricematchForm" method="post" action="{$pricematchUrl|escape:'htmlall':'UTF-8'}">
		<input type="hidden" name="id_shop" value="{$id_shop|intval}" />
		<div class="form-group clearfix">
			<div class="col-sm-6 col-xs-12">
				<label for="pricematchCustomerName">{l s='Your name' mod='pricematch'}<sup>*</sup></label>
				<input name="customer_name" type="text" class="form-control required" id="pricematchCustomerName" placeholder="{l s='Your name' mod='pricematch'}" value="{$customer_name|escape:'htmlall':'UTF-8'}"/>
				<input type="hidden" name="id_customer" value="{$id_customer|intval}" />
			</div>
			<div class="col-sm-6 col-xs-12">
				<label for="pricematchEmail">{l s='Email' mod='pricematch'}<sup>*</sup></label>			
				<input name="customer_email" type="email" class="form-control required" id="pricematchEmail" placeholder="{l s='Email' mod='pricematch'}" value="{$customer_email|escape:'htmlall':'UTF-8'}"/>
			</div>		
		</div>
		<div class="form-group clearfix">
			<div class="col-sm-6 col-xs-12">
				<label for="pricematchCustomerPhone">{l s='Phone number' mod='pricematch'}</label>
				<input name="customer_phone" type="tel" class="form-control" id="pricematchCustomerPhone" />
			</div>
		</div>
		<div class="form-group clearfix">
			<div class="col-xs-12">
				<label for="pricemacthProduct">{l s='Product' mod='pricematch'}<sup>*</sup></label>
				<input type="text" class="form-control" value="{$productName|escape:'htmlall':'UTF-8'}"/>
				<input type="hidden" name="id_product" value="{$id_product|intval}"/>
			</div>
		</div>
		<div class="form-group clearfix">
			<div class="col-xs-12">
				<label>{l s='Competitor\'s product link' mod='pricematch'}<sup>*</sup></label>
				<input name="competitor_url" type="url" class="form-control required toReset" />
			</div>			
		</div>
		<div class="form-group clearfix">
			<div class="col-xs-12">
				<label>{l s='Competitor\'s price' mod='pricematch'}<sup>*</sup></label>
				<input name="competitor_price" step="0.01" type="number" class="form-control required toReset" />
			</div>
		</div>
		<div class="form-group clearfix">
			<div class="col-xs-12">
				<label>{l s='Comment' mod='pricematch'}</label>
				<textarea name="comment" class="form-control toReset"></textarea>
			</div>
		</div>
		<div class="form-group clearfix">
			<div class="col-xs-6 text-right">
				<button id="pricematchClose" type="button" class="btn btn-default">{l s='Reset' mod='pricematch'}</button>
			</div>
			<div class="col-xs-6 text-left">
				<button id="pricematchSave" type="submit" class="btn btn-default">{l s='Submit' mod='pricematch'}</button>
			</div>
		</div>
	</form>
</div>
<!-- /Pricematch module -->