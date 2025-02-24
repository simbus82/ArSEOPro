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
<div class="modal fade" id="arseo-redirect-modal-import" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">{l s='Import redirects' mod='arseopro'}</div>
            </div>
            <form action="{$ajaxUrl.redirect|escape:'htmlall':'UTF-8'}&action=importCsv" class="form-horizontal form" id="arseo-redirect-import-form" method="POST" enctype="multipart/form-data">
                <div class="modal-body" style="font-size: 15px">
                    <div class="form-group">
                        <label class="control-label required col-sm-2">{l s='CSV file to import' mod='arseopro'}</label>
                        <div class="col-sm-10" style="padding-top: 4px;">
                            <input type="file" class="" value="/" name="file" />
                            <div class="errors"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" type="button" data-dismiss="modal">{l s='Close' mod='arseopro'}</button>
                    <button class="btn btn-success" type="submit">{l s='Import' mod='arseopro'}</button>
                </div>
            </form>
        </div>
    </div>
</div>