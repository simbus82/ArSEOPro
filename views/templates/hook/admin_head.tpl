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
{if $moduleConfig}
<script type="text/javascript">
    if (document.getElementById('maintab-AdminArSeo')){
        document.getElementById('maintab-AdminArSeo').classList.add("active");
    }else if(document.getElementById('subtab-AdminArSeo')){
        document.getElementById('subtab-AdminArSeo').classList.add("active");
        document.getElementById('subtab-AdminArSeo').classList.add("-active");
        document.getElementById('subtab-AdminArSeo').classList.add("ul-open");
        document.getElementById('subtab-AdminArSeo').classList.add("open");
        var arSEOIcon = document.getElementById('subtab-AdminArSeo').querySelector('a i');
        if (arSEOIcon && arSEOIcon.innerHTML == ''){
            arSEOIcon.innerHTML = 'link';
        }
    }
    if (document.getElementById('maintab-AdminParentModules')){
        document.getElementById('maintab-AdminParentModules').classList.remove("active");
    }
    if (document.getElementById('subtab-AdminParentModulesSf')){
        document.getElementById('subtab-AdminParentModulesSf').classList.remove("active");
        document.getElementById('subtab-AdminParentModulesSf').classList.remove("-active");
        document.getElementById('subtab-AdminParentModulesSf').classList.remove("ul-open");
        document.getElementById('subtab-AdminParentModulesSf').classList.remove("open");
    }
</script>
{/if}
<style type="text/css">
    .icon-AdminArSeo:before{
        content:"";
    }
</style>
{if $productForm}
    <script type="text/javascript">
        window.addEventListener('load', function(){
            $('#form').on('submit', function(){
                var d = setInterval(function(){
                    
                    if ($('#submit').is(':disabled')) {
                        
                    } else {
                        clearInterval(d);
                        $.ajax({
                            type: 'POST',
                            url: '{$arSEOAjaxURL nofilter}',
                            dataType: 'json',
                            data: {
                                controller : 'AdminArSeoUrls',
                                action : 'applyProductRules',
                                id: $('#form_id_product').val(),
                                ajax : true,
                            },
                            success: function(data){

                            }
                        }).fail(function(){
                            console.log('AdminArSeoUrls:applyProductRules fail')
                        });
                    }
                }, 500);
            });
        });
    </script>
{/if}