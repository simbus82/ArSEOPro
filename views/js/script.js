/*
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
*/

window.addEventListener('load', function(){
    arSeoProParseHash();
});

function arSeoProParseHash(){
    var hash = window.location.hash;
    hash = hash.replace('#/', '');
    hash = hash.replace('#', '');
    if (hash.length == 0) {
        return false;
    }
    
    if (arSEORemoveIdFromHash) {
        $.each(arSEOAttributes, function (key, i) {
            if (key == hash) {
                var data = i.split('/');
                $.each(data, function (kkey) {
                    let a = data[kkey].split('-');
                    let b = a[0].split('|');
                    arSeoProSelectAttribute(b[1]);
                });
            }
        });
    } else {
        var data = hash.split('/');
        if (data) {
            $.each(data, function(i){
                var value = data[i].match(/^(\d+)-/);
                if (value){
                    arSeoProSelectAttribute(value[1]);
                }
            });
        }
    }

    prestashop.emit('updateProduct', {
        eventType: 'updatedProductCombination',
        event: this,
        // Following variables are not used anymore, but kept for backward compatibility
        resp: {},
        reason: {
            productUrl: prestashop.urls.pages.product || '',
        },
    });
}

function arSeoProSelectAttribute(value){
    var $el = $('[data-product-attribute][value="' + value + '"]');
    if ($el.length){
        if ($el.is('[type="checkbox"]') || $el.is('[type="radio"]')){
            $el.prop("checked", "checked");
        }
    }else{
        $el = $('[data-product-attribute] [value="' + value + '"]').parent();
        if ($el.length && $el.is('select')) {
            $el.val(value);
        }
    }
}