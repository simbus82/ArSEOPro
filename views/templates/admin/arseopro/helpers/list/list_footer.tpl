{*
* 2018 Areama
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@areama.net so we can send you a copy immediately.
*
*
*  @author Areama <contact@areama.net>
*  @copyright  2018 Areama
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of Areama
*}
	</table>
</div>
<div class="row">
	<div class="col-lg-6">
		{if $bulk_actions && $has_bulk_actions}
		<div class="btn-group bulk-actions dropup">
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
				{l s='Bulk actions' mod='arseopro'} <span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
                                <li>
					<a href="#" onclick="javascript:checkDelBoxes($(this).closest('form').get(0), '{$list_id|escape:'htmlall':'UTF-8'}Box[]', true);return false;">
						<i class="icon-check-sign"></i>&nbsp;{l s='Select all' mod='arseopro'}
					</a>
				</li>
				<li>
					<a href="#" onclick="javascript:checkDelBoxes($(this).closest('form').get(0), '{$list_id|escape:'htmlall':'UTF-8'}Box[]', false);return false;">
						<i class="icon-check-empty"></i>&nbsp;{l s='Unselect all' mod='arseopro'}
					</a>
				</li>
				<li class="divider"></li>
				{foreach $bulk_actions as $key => $params}
					<li{if $params.text == 'divider'} class="divider"{/if}>
						{if $params.text != 'divider'}
						<a href="#" {if isset($params.id)}id="{$params.id|escape:'htmlall':'UTF-8'}"{/if} {if isset($params.js_action)}onclick="{$params.js_action|escape:'htmlall':'UTF-8'}"{else}onclick="{if isset($params.confirm)}if (confirm('{$params.confirm|escape:'htmlall':'UTF-8'}')){/if}sendBulkAction($(this).closest('form').get(0), 'submitBulk{$key|escape:'htmlall':'UTF-8'}{$table|escape:'htmlall':'UTF-8'}');"{/if}>
							{if isset($params.icon)}<i class="{$params.icon|escape:'htmlall':'UTF-8'}"></i>{/if}&nbsp;{$params.text|escape:'htmlall':'UTF-8'}
						</a>
						{/if}
					</li>
				{/foreach}
			</ul>
		</div>
		{/if}
	</div>
	{if !$simple_header && $list_total > $pagination[0]}
	<div class="col-lg-6">
		{* Choose number of results per page *}
		<div class="pagination">
			{l s='Display' mod='arseopro'}
			<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
				{$selected_pagination|escape:'htmlall':'UTF-8'}
				<i class="icon-caret-down"></i>
			</button>
			<ul class="dropdown-menu">
			{foreach $pagination AS $value}
				<li>
					<a href="javascript:void(0);" class="pagination-items-page" data-items="{$value|intval}" data-list-id="{$list_id|escape:'htmlall':'UTF-8'}">{$value|escape:'htmlall':'UTF-8'}</a>
				</li>
			{/foreach}
			</ul>
			/ {$list_total|escape:'htmlall':'UTF-8'} {l s='result(s)' mod='arseopro'}
			<input type="hidden" id="{$list_id|escape:'htmlall':'UTF-8'}-pagination-items-page" name="{$list_id|escape:'htmlall':'UTF-8'}_pagination" value="{$selected_pagination|intval}" />
		</div>
		<script type="text/javascript">
			$('.pagination-items-page').on('click',function(e){
				e.preventDefault();
				$('#'+$(this).data("list-id")+'-pagination-items-page').val($(this).data("items")).closest("form").submit();
			});
		</script>
		<ul class="pagination pull-right">
			<li {if $page <= 1}class="disabled"{/if}>
				<a href="javascript:void(0);" class="pagination-link" data-page="1" data-list-id="{$list_id|escape:'htmlall':'UTF-8'}">
					<i class="icon-double-angle-left"></i>
				</a>
			</li>
			<li {if $page <= 1}class="disabled"{/if}>
				<a href="javascript:void(0);" class="pagination-link" data-page="{$page|intval - 1}" data-list-id="{$list_id|escape:'htmlall':'UTF-8'}">
					<i class="icon-angle-left"></i>
				</a>
			</li>
			{assign p 0}
			{while $p++ < $total_pages}
				{if $p < $page-2}
					<li class="disabled">
						<a href="javascript:void(0);">&hellip;</a>
					</li>
					{assign p $page-3}
				{elseif $p > $page+2}
					<li class="disabled">
						<a href="javascript:void(0);">&hellip;</a>
					</li>
					{assign p $total_pages}
				{else}
					<li {if $p == $page}class="active"{/if}>
						<a href="javascript:void(0);" class="pagination-link" data-page="{$p|intval}" data-list-id="{$list_id|escape:'htmlall':'UTF-8'}">{$p|escape:'htmlall':'UTF-8'}</a>
					</li>
				{/if}
			{/while}
			<li {if $page >= $total_pages}class="disabled"{/if}>
				<a href="javascript:void(0);" class="pagination-link" data-page="{$page|intval + 1}" data-list-id="{$list_id|escape:'htmlall':'UTF-8'}">
					<i class="icon-angle-right"></i>
				</a>
			</li>
			<li {if $page >= $total_pages}class="disabled"{/if}>
				<a href="javascript:void(0);" class="pagination-link" data-page="{$total_pages|intval}" data-list-id="{$list_id|escape:'htmlall':'UTF-8'}">
					<i class="icon-double-angle-right"></i>
				</a>
			</li>
		</ul>
		<script type="text/javascript">
			$('.pagination-link').on('click',function(e){
				e.preventDefault();

				if (!$(this).parent().hasClass('disabled'))
					$('#submitFilter'+$(this).data("list-id")).val($(this).data("page")).closest("form").submit();
			});
		</script>
	</div>
	{/if}
</div>
{block name="footer"}
{foreach from=$toolbar_btn item=btn key=k}
	{if $k == 'back'}
		{assign 'back_button' $btn}
		{break}
	{/if}
{/foreach}
{if isset($back_button)}
<div class="panel-footer">
	<a id="desc-{$table|escape:'htmlall':'UTF-8'}-{if isset($back_button.imgclass)}{$back_button.imgclass|escape:'htmlall':'UTF-8'}{else}{$k|escape:'htmlall':'UTF-8'}{/if}" class="btn btn-default{if isset($back_button.target) && $back_button.target} _blank{/if}"{if isset($back_button.href)} href="{$back_button.href|escape:'html':'UTF-8'}"{/if}{if isset($back_button.js) && $back_button.js} onclick="{$back_button.js|escape:'htmlall':'UTF-8'}"{/if}>
		<i class="process-icon-back {if isset($back_button.class)}{$back_button.class|escape:'htmlall':'UTF-8'}{/if}" ></i> <span {if isset($back_button.force_desc) && $back_button.force_desc == true } class="locked" {/if}>{$back_button.desc|escape:'htmlall':'UTF-8'}</span>
	</a>
</div>
{/if}
{/block}
{if !$simple_header}
		<input type="hidden" name="token" value="{$token|escape:'html':'UTF-8'}" />
	</div>
{else}
	</div>
{/if}

{hook h='displayAdminListAfter'}
{if isset($name_controller)}
	{capture name=hookName assign=hookName}display{$name_controller|ucfirst|escape:'htmlall':'UTF-8'}ListAfter{/capture}
	{hook h=$hookName}
{elseif isset($smarty.get.controller)}
	{capture name=hookName assign=hookName}display{$smarty.get.controller|ucfirst|htmlentities|escape:'htmlall':'UTF-8'}ListAfter{/capture}
	{hook h=$hookName}
{/if}

{block name="endForm"}
</form>
{/block}

{block name="after"}{/block}