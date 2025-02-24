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
<div class="modal fade" id="arseo-redirect-modal2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">{l s='Generate list of redirects' mod='arseopro'}</div>
            </div>
            
            <div class="modal-body" style="font-size: 15px">
                <p>
                    {l s='You can use this tool to automatically create list of redirects from page-not-found PrestaShop table.' mod='arseopro'} 
                </p>
                <p>
                    {l s='After list will be generated, you can download it and import using "Import from CSV file" feature.' mod='arseopro'} 
                </p>
                <div id="not-found-generated" class="{if empty($nflLastTime)}hidden{/if}">
                    <p class="alert alert-info">
                        {l s='Last generated file:' mod='arseopro'} 
                        <a href="{$path|escape:'htmlall':'UTF-8'}csv/not-found-list.csv">not-found-list.csv</a> (<small>{$nflLastTime|escape:'htmlall':'UTF-8'}</small>)
                    </p>
                </div>
                    
                <div class="form-horizontal form" style="font-size: 12px">
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='Redirect to' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="/" id="arseo-redirect-nfl_to" placeholder="{$path|escape:'htmlall':'UTF-8'}new-url.html or /new-url.html" name="to" data-serializable="true" data-default="/">
                            <div class="errors"></div>
                            <p class="help-block">
                                {l s='You can write absolute or relative URL here.' mod='arseopro'}<br/>
                                {l s='You can use tag {lang} which will be replaced to same language from source URL.' mod='arseopro'}<br/>
                                {l s='You can use tag {default_lang} which will be replaced to default shop language.' mod='arseopro'}
                            </p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='Redirect type' mod='arseopro'}</label>
                        <div class="col-sm-10">
                            <select class="form-control" name="type" id="arseo-redirect-nfl_type" data-serializable="true" data-default="1">
                                <option value="301" selected="">301 - {l s='Moved Permanently' mod='arseopro'}</option>
                                <option value="302">302 - {l s='Moved Temporarily' mod='arseopro'}</option>
                                <option value="303">303 - {l s='See Other' mod='arseopro'}</option>
                            </select>
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                <div id="not-found-complete" class="hidden">
                    <p class="alert alert-success">
                        {l s='List correcly generated. You can download file here:' mod='arseopro'} <a href="{$path|escape:'htmlall':'UTF-8'}csv/not-found-list.csv">not-found-list.csv</a>
                    </p>
                </div>
                <div id="not-found-progress-container" class="hidden">
                    <div>
                        {l s='Items processed:' mod='arseopro'} <span id="arseor-processed">0</span> {l s='of' mod='arseopro'} <span id="arseor-total">...</span>
                    </div>
                    <div class="progress" id="not-found-progress">
                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            0%
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-dismiss="modal">{l s='Close' mod='arseopro'}</button>
                <button class="btn btn-success" type="button" onclick="arSEO.redirect._generateNotFoundList(0, 0)">{l s='Generate' mod='arseopro'}</button>
            </div>
        </div>
    </div>
</div>