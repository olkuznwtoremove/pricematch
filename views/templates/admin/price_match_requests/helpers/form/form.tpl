{*
*  @author    Olga Kuznetsova <olkuznw@gmail.com>
*  @copyright odev.me 2017
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  @version   1.0.0
*
* Languages: EN
* PS version: 1.6
**}

{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if 'href' == $input.type}
		<div class="form-control-static">
			<a target="_blank" href="{$fields_value[$input.name]|escape:'html':'UTF-8'}">{$fields_value[$input.name]|escape:'html':'UTF-8'}</a>
		</div>
	{elseif 'comment' == $input.type}
		<div class="col-lg-4">
			<textarea disabled="disabled">{$fields_value[$input.name]|escape:'html':'UTF-8'}</textarea>
		</div>
	{elseif 'dropdown' == $input.type}
		<div class="col-lg-2 ">
			<select name="{$input.name|escape:'html':'utf-8'}"
												class="{if isset($input.class)}{$input.class|escape:'html':'utf-8'}{/if} fixed-width-xl"
												id="{if isset($input.id)}{$input.id|escape:'html':'utf-8'}{else}{$input.name|escape:'html':'utf-8'}{/if}"
												{if isset($input.multiple)}multiple="multiple" {/if}
												{if isset($input.size)}size="{$input.size|escape:'html':'utf-8'}"{/if}
												{if isset($input.onchange)}onchange="{$input.onchange|escape:'html':'utf-8'}"{/if}>
			{foreach from=$input.options item="option" key="key"}
				<option value="{$key|escape:'html':'UTF-8'}" {if $key == $fields_value[$input.name]|escape:'html':'UTF-8'}selected="selected"{/if}>
					{$option|escape:'htmlall':'UTF-8'}
				</option>
			{/foreach}
			</select>
		</div>
	{/if}
	{$smarty.block.parent}
{/block}
