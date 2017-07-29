<!-- Pricematch module -->
<div id="pricematchpopup" class="pricematchpopup">
	<div class="pricematch-loader"></div>
	<div class="pricematch-error col-xs-12"></div>
	<div class="std col-xs-12">
		{if !empty($description)}
			{$description}
		{/if}
	</div>
	<form id="pricematchForm" method="post" action="{$pricematchUrl}">
		<input type="hidden" name="id_shop" value="{$id_shop}" />
		<div class="form-group clearfix">
			<div class="col-sm-6 col-xs-12">
				<label for="pricematchCustomerName">{l s='Your name' mod='pricematch'}<sup>*</sup></label>
				<input name="customer_name" type="text" class="form-control required" id="pricematchCustomerName" placeholder="{l s='Your name' mod='pricematch'}" value="{$customer_name}"/>
				<input type="hidden" name="id_customer" value="{$id_customer}" />
			</div>
			<div class="col-sm-6 col-xs-12">
				<label for="pricematchEmail">{l s='Email' mod='pricematch'}<sup>*</sup></label>			
				<input name="customer_email" type="email" class="form-control required" id="pricematchEmail" placeholder="{l s='Email' mod='pricematch'}" value="{$customer_email}"/>
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
				<input type="text" class="form-control" value="{$productName}"/>
				<input type="hidden" name="id_product" value="{$id_product}"/>
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