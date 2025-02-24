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
<div class="modal fade" id="arseo-redirect-export" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">{l s='Export redirect list to CSV file' mod='arseopro'}</div>
            </div>
            
            <div class="modal-body" style="font-size: 15px">
                <p>
                    {l s='You can use this tool to automatically create list of redirects from page-not-found PrestaShop table.' mod='arseopro'} 
                </p>
                <p>
                    {l s='After list will be generated, you can download it and import using "Import from CSV file" feature.' mod='arseopro'} 
                </p>
                
                <div id="arseo-export-complete" class="hidden">
                    <p class="alert alert-success">
                        {l s='List correcly generated. You can download file here:' mod='arseopro'} <a href="{$path|escape:'htmlall':'UTF-8'}csv/export.csv">export.csv</a>
                    </p>
                </div>
                <div id="arseo-export-progress-container" class="hidden">
                    <div>
                        {l s='Items processed:' mod='arseopro'} <span id="arseo-export-processed">0</span> {l s='of' mod='arseopro'} <span id="arseo-export-total">...</span>
                    </div>
                    <div class="progress" id="arseo-export-progress">
                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
                            0%
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" type="button" data-dismiss="modal">{l s='Close' mod='arseopro'}</button>
                <button class="btn btn-success" type="button" onclick="arSEO.redirect._export(0, 0)">{l s='Generate' mod='arseopro'}</button>
            </div>
        </div>
    </div>
</div>