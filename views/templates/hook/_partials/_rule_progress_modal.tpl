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
<div class="modal fade" id="arseo-progress-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" style="font-size: 18px;" id="myModalLabel">
                    {l s='Applying rule' mod='arseopro'} :: <span id="arseo-progress-rule-name"></span>
                </div>
            </div>
            <div class="modal-body">
                <div class="progress">
                    <div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0">
                        0%
                    </div>
                </div>
                {l s='Processed:' mod='arseopro'} <span id="arseo-count"></span>/<span id="arseo-total"></span>
            </div>
            <div class="modal-footer">
                <button class="btn btn-danger pull-left hidden btn-terminate" onclick="arSEO.lastProcess.requestTermination()" type="button" ><i class="icon-stop"></i> {l s='Terminate' mod='arseopro'}</button>
                <button class="btn btn-primary pull-left hidden btn-continue" onclick="arSEO.lastProcess.continue()" type="button" ><i class="icon-play"></i> {l s='Continue' mod='arseopro'}</button>
                <button class="btn btn-success pull-left hidden btn-start-over" onclick="arSEO.lastProcess.start()" type="button" ><i class="icon-refresh"></i> {l s='Start over' mod='arseopro'}</button>
                <button class="btn btn-default btn-close hidden" type="button" data-dismiss="modal">{l s='Close' mod='arseopro'}</button>
            </div>
        </div>
    </div>
</div>