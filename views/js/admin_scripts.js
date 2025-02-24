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

var arSEORedirectLoaded = false;
var arSEOUrlLoaded = false;
var arSEOMetaLoaded = false;
var arSEODuplicatesLoaded = false;
var arSEOSitemapProductLoaded = false;
var arSEOSitemapSuppliersLoaded = false;
var arSEOSitemapManufacturersLoaded = false;
var arSEOSitemapCmsLoaded = false;
var arSEOSitemapMetaLoaded = false;
var arSEORobotsLoaded = false;
var arSEOHelpLoaded = false;

$(".arseoproTabs a").click(function(e){
    e.preventDefault();
    $(".arseoproTabs .active").removeClass('active');
    $(this).addClass('active');
    $('#arseopro-config .arseopro-config-panel').addClass('hidden');
    $('#' + $(this).data('target')).removeClass('hidden');
    $('#arseoproActiveTab').remove();
    $('#arseoproActiveTab').val($(this).data('tab'));
    if ($(this).data('target') == 'arseopro-redirect') {
        if (!arSEORedirectLoaded){
            arSEO.redirect.reload();
            arSEORedirectLoaded = true;
        }
    }
    if ($(this).data('target') == 'arseopro-url') {
        if (!arSEOUrlLoaded){
            arSEO.url.reload();
            arSEOUrlLoaded = true;
        }
    }
    if ($(this).data('target') == 'arseopro-meta') {
        if (!arSEOMetaLoaded){
            arSEO.meta.reload();
            arSEOMetaLoaded = true;
        }
    }
    if ($(this).data('target') == 'arseopro-robots') {
        if (!arSEORobotsLoaded){
            arSEO.robots.reload(false);
            arSEORobotsLoaded = true;
        }
    }
    if ($(this).data('target') == 'arseopro-help') {
        if (!arSEOHelpLoaded){
            arSEO.help.reload(false);
            arSEOHelpLoaded = true;
        }
    }
});

$('#arseopro-sitemap-products-tab').click(function(){
    if (!arSEOSitemapProductLoaded){
        arSEO.sitemap.product.reload();
        arSEOSitemapProductLoaded = true;
    }
});
$('#arseopro-sitemap-suppliers-tab').click(function(){
    if (!arSEOSitemapSuppliersLoaded){
        arSEO.sitemap.supplier.reload();
        arSEOSitemapSuppliersLoaded = true;
    }
});
$('#arseopro-sitemap-manufacturers-tab').click(function(){
    if (!arSEOSitemapManufacturersLoaded){
        arSEO.sitemap.manufacturer.reload();
        arSEOSitemapManufacturersLoaded = true;
    }
});
$('#arseopro-sitemap-cms-tab').click(function(){
    if (!arSEOSitemapCmsLoaded){
        arSEO.sitemap.cms.reload();
        arSEOSitemapCmsLoaded = true;
    }
});
$('#arseopro-sitemap-meta-tab').click(function(){
    if (!arSEOSitemapMetaLoaded){
        arSEO.sitemap.meta.reload();
        arSEOSitemapMetaLoaded = true;
    }
});

$('#arseopro-duplication-tab').click(function(){
    arSEO.url.duplication.reload();
});

$('#arseo-url-rule-form_id_category').change(function(){
    if ($(this).val() == 1){
        $('#arseo-categories-container').removeClass('hidden');
    }else{
        $('#arseo-categories-container').addClass('hidden');
    }
});

$('#arseo-meta-rule-form_id_category').change(function(){
    if ($(this).val() == 1){
        $('#arseo-meta-categories-container').removeClass('hidden');
    }else{
        $('#arseo-meta-categories-container').addClass('hidden');
    }
});

$('#arseo-meta-rule-form_fb_image').change(function(){
    if ($(this).val() == '3'){
        $('#arseo-fb-custom-image').removeClass('hidden');
    }else{
        $('#arseo-fb-custom-image').addClass('hidden');
    }
});

$('#arseo-meta-rule-form_tw_image').change(function(){
    if ($(this).val() == '3'){
        $('#arseo-tw-custom-image').removeClass('hidden');
    }else{
        $('#arseo-tw-custom-image').addClass('hidden');
    }
});

$('.arseoproTabs .active').trigger('click');

$('body').on('click', '.arseo-help-link', function(){
    var tab = $(this).attr('data-tab');
    var accordion = $(this).attr('data-accordion');
    $('[data-target="arseopro-help"]').trigger('click');
    if (tab){
        $(tab).tab('show');
    }
    if (accordion){
        $(accordion).parents('.panel-group').find('.panel-collapse:not(' + accordion + ')').collapse('hide');
        $(accordion).collapse('show');
    }
    return false;
    var element = $(this).attr('href');
    element = $(element);
    
    var y = element.offset().top - 135;

    var body = $("html, body");
    body.stop().animate({scrollTop:y}, 200, 'swing');
    return false;
});

arSEOSwitch();
switchSitemapImages();
arSEOSwitchRedirect();

function arSEOSwitchRedirect() {
    if ($('#ARSP_REDIRECT').val() == '0') {
        $('#arseopro-product .field_redirect_code').addClass('hidden');
    } else {
        $('#arseopro-product .field_redirect_code').removeClass('hidden');
    }
    
    if ($('#ARSP_REDIRECT_NOT_ACTIVE').val() == '0') {
        $('#arseopro-product .field_redirect_not_active_code').addClass('hidden');
    } else {
        $('#arseopro-product .field_redirect_not_active_code').removeClass('hidden');
    }
    
    if ($('#ARSC_REDIRECT').val() == '0') {
        $('#arseopro-category .field_redirect_code').addClass('hidden');
    } else {
        $('#arseopro-category .field_redirect_code').removeClass('hidden');
    }
    
    if ($('#ARSM_REDIRECT').val() == '0') {
        $('#arseopro-manufacturer .field_redirect_code').addClass('hidden');
    } else {
        $('#arseopro-manufacturer .field_redirect_code').removeClass('hidden');
    }
    
    if ($('#ARSS_REDIRECT').val() == '0') {
        $('#arseopro-supplier .field_redirect_code').addClass('hidden');
    } else {
        $('#arseopro-supplier .field_redirect_code').removeClass('hidden');
    }
    
    if ($('#ARSCMS_REDIRECT').val() == '0') {
        $('#arseopro-cms .field_redirect_code').addClass('hidden');
    } else {
        $('#arseopro-cms .field_redirect_code').removeClass('hidden');
    }
    
    if ($('#ARSCMSC_REDIRECT').val() == '0') {
        $('#arseopro-cms-category .field_redirect_code').addClass('hidden');
    } else {
        $('#arseopro-cms-category .field_redirect_code').removeClass('hidden');
    }
}

$('#ARSP_REDIRECT, #ARSP_REDIRECT_NOT_ACTIVE, #ARSC_REDIRECT, #ARSM_REDIRECT, #ARSS_REDIRECT, #ARSCMS_REDIRECT, #ARSCMSC_REDIRECT').change(function(){
    arSEOSwitchRedirect();
});

$('.prestashop-switch').click(function(){
    arSEOSwitch();
});

$('#arseopro-product .field_default_cat .prestashop-switch input').change(function(){
    if ($('#ARSP_DEFAULT_CAT_on').is(':checked')){
        $('#ARSP_PARENT_CAT_off').trigger('click');
    }
});

$('#arseopro-product .field_parent_cat .prestashop-switch input').change(function(){
    if ($('#ARSP_PARENT_CAT_on').is(':checked')){
        $('#ARSP_DEFAULT_CAT_off').trigger('click');
    }
});

// redirect list

$('#form-redirect-list .pagination-link').off('click');
$('#form-redirect-list .pagination-items-page').off('click');
$('#arseopro-config').on('click', '.pagination-link', function(){
    $('#form-redirect-list input[name="page"]').val($(this).data('page'));
    arSEO.redirect.reload();
    return false;
});
$(document).on('submit', '#form-redirect-list', function(a){
    arSEO.redirect.reload();
    return false;
});
$(document).on('click', '#form-redirect-list [name="submitResetredirect-list"]', function(a){
    arSEO.redirect.reload('submitReset');
    return false;
});
$('#arseopro-config').on('click', '#form-redirect-list .pagination-items-page', function(){
    $('#form-redirect-list input[name="selected_pagination"]').val($(this).data('items'));
    arSEO.redirect.reload();
    return false;
}); 

// !redirect list

// url list

$('#form-url-list .pagination-link').off('click');
$('#form-url-list .pagination-items-page').off('click');
$('#arseopro-config').on('click', '#arseopro-url .pagination-link', function(){
    $('#arseopro-url input[name="page"]').val($(this).data('page'));
    arSEO.url.reload();
    return false;
});
$(document).on('submit', '#form-url-list', function(a){
    arSEO.url.reload();
    return false;
});
$(document).on('click', '#form-url-list [name="submitReseturl-list"]', function(a){
    arSEO.url.reload('submitReset');
    return false;
});
$('#arseopro-config').on('click', '#arseopro-url .pagination-items-page', function(){
    $('#arseopro-url input[name="selected_pagination"]').val($(this).data('items'));
    arSEO.url.reload();
    return false;
});

// !url list

// meta list

$('#form-meta-list .pagination-link').off('click');
$('#form-meta-list .pagination-items-page').off('click');
$('#arseopro-config').on('click', '#arseopro-meta .pagination-link', function(){
    $('#arseopro-meta input[name="page"]').val($(this).data('page'));
    arSEO.meta.reload();
    return false;
});
$(document).on('submit', '#form-meta-list', function(a){
    arSEO.meta.reload();
    return false;
});
$(document).on('click', '#form-meta-list [name="submitResetmeta-list"]', function(a){
    arSEO.meta.reload('submitReset');
    return false;
});
$('#arseopro-config').on('click', '#arseopro-meta .pagination-items-page', function(){
    $('#arseopro-meta input[name="selected_pagination"]').val($(this).data('items'));
    arSEO.meta.reload();
    return false;
});

// !meta list

// sitemap product list

$('#form-sitemap-products .pagination-link').off('click');
$('#form-sitemap-products .pagination-items-page').off('click');
$('#arseopro-config').on('click', '#form-sitemap-products-container .pagination-link', function(){
    $('#form-sitemap-products-container input[name="page"]').val($(this).data('page'));
    arSEO.sitemap.product.reload();
    return false;
});
$(document).on('submit', '#form-sitemap-products', function(a){
    arSEO.sitemap.product.reload();
    return false;
});
$(document).on('click', '#form-sitemap-products [name="submitResetsitemap-products"]', function(a){
    arSEO.sitemap.product.reload('submitReset');
    return false;
});
$('#arseopro-config').on('click', '#form-sitemap-products-container .pagination-items-page', function(){
    $('#form-sitemap-products-container input[name="selected_pagination"]').val($(this).data('items'));
    arSEO.sitemap.product.reload();
    return false;
});

// !sitemap product list

// sitemap supplier list

$('#form-sitemap-suppliers .pagination-link').off('click');
$('#form-sitemap-suppliers .pagination-items-page').off('click');
$('#arseopro-config').on('click', '#form-sitemap-suppliers-container .pagination-link', function(){
    $('#form-sitemap-suppliers-container input[name="page"]').val($(this).data('page'));
    arSEO.sitemap.supplier.reload();
    return false;
});
$(document).on('submit', '#form-sitemap-suppliers', function(a){
    arSEO.sitemap.supplier.reload();
    return false;
});
$(document).on('click', '#form-sitemap-suppliers [name="submitResetsitemap-suppliers"]', function(a){
    arSEO.sitemap.supplier.reload('submitReset');
    return false;
});
$('#arseopro-config').on('click', '#form-sitemap-suppliers-container .pagination-items-page', function(){
    $('#form-sitemap-suppliers-container input[name="selected_pagination"]').val($(this).data('items'));
    arSEO.sitemap.supplier.reload();
    return false;
});

// !sitemap supplier list

// sitemap manufacturer list

$('#form-sitemap-manufacturers .pagination-link').off('click');
$('#form-sitemap-manufacturers .pagination-items-page').off('click');
$('#arseopro-config').on('click', '#form-sitemap-manufacturers-container .pagination-link', function(){
    $('#form-sitemap-manufacturers-container input[name="page"]').val($(this).data('page'));
    arSEO.sitemap.manufacturer.reload();
    return false;
});
$(document).on('submit', '#form-sitemap-manufacturers', function(a){
    arSEO.sitemap.manufacturer.reload();
    return false;
});
$(document).on('click', '#form-sitemap-manufacturers [name="submitResetsitemap-manufacturers"]', function(a){
    arSEO.sitemap.manufacturer.reload('submitReset');
    return false;
});
$('#arseopro-config').on('click', '#form-sitemap-manufacturers-container .pagination-items-page', function(){
    $('#form-sitemap-manufacturers-container input[name="selected_pagination"]').val($(this).data('items'));
    arSEO.sitemap.manufacturer.reload();
    return false;
});

// !sitemap manufacturer list

// sitemap cms list

$('#form-sitemap-cms .pagination-link').off('click');
$('#form-sitemap-cms .pagination-items-page').off('click');
$('#arseopro-config').on('click', '#form-sitemap-cms-container .pagination-link', function(){
    $('#form-sitemap-cms-container input[name="page"]').val($(this).data('page'));
    arSEO.sitemap.cms.reload();
    return false;
});
$(document).on('submit', '#form-sitemap-cms', function(a){
    arSEO.sitemap.cms.reload();
    return false;
});
$(document).on('click', '#form-sitemap-cms [name="submitResetsitemap-cms"]', function(a){
    arSEO.sitemap.cms.reload('submitReset');
    return false;
});
$('#arseopro-config').on('click', '#form-sitemap-cms-container .pagination-items-page', function(){
    $('#form-sitemap-cms-container input[name="selected_pagination"]').val($(this).data('items'));
    arSEO.sitemap.cms.reload();
    return false;
});

// !sitemap cms list

// sitemap meta list

$('#form-sitemap-meta .pagination-link').off('click');
$('#form-sitemap-meta .pagination-items-page').off('click');
$('#arseopro-config').on('click', '#form-sitemap-meta-container .pagination-link', function(){
    $('#form-sitemap-meta-container input[name="page"]').val($(this).data('page'));
    arSEO.sitemap.meta.reload();
    return false;
});
$(document).on('submit', '#form-sitemap-meta', function(a){
    arSEO.sitemap.meta.reload();
    return false;
});
$(document).on('click', '#form-sitemap-meta [name="submitResetsitemap-meta"]', function(a){
    arSEO.sitemap.meta.reload('submitReset');
    return false;
});
$('#arseopro-config').on('click', '#form-sitemap-meta-container .pagination-items-page', function(){
    $('#form-sitemap-meta-container input[name="selected_pagination"]').val($(this).data('items'));
    arSEO.sitemap.meta.reload();
    return false;
});

// !sitemap meta list

$('#ARSEO_REDIRECTS_SWITCH input').change(function(){
    arSEO.redirect.switch();
});

$('#ARSEO_REDIRECTS_LOG_SWITCH input').change(function(){
    arSEO.redirect.switchLog();
});

window.addEventListener('load', function(){
    $('#arseopro_fb_upload_image').fileupload({
        dataType: 'json',
        async: false,
        autoUpload: false,
        singleFileUploads: true,
        maxFileSize: max_image_size,
        done: function (e, data){
            var images = data.result.arseopro_fb_upload_image;
            $.each(images, function(){
                if (this.error){
                    $('#arseopro_fb_upload_image-errors').append('<div class="form-group"><strong>'+this.name+'</strong> ('+humanizeSize(this.size)+') : '+this.error+'</div>').parent().show();
                }else{
                    $('#arseopro_fb_upload_image_list').html('<img width="120" src="' + this.url + '" />');
                    $('#arseo-meta-rule-form_fb_custom_image').val(this.filename);
                }
            });
            $('#arseopro_fb_upload_image-files-list').html('');
        },
        fail: function (e, data) {
            $('#arseopro_fb_upload_image-errors').html(data.errorThrown.message).parent().show();
        }
    });

    $('#arseopro_tw_upload_image').fileupload({
        dataType: 'json',
        async: false,
        autoUpload: false,
        singleFileUploads: true,
        maxFileSize: max_image_size,
        done: function (e, data){
            var images = data.result.arseopro_tw_upload_image;
            $.each(images, function(){
                if (this.error){
                    $('#arseopro_tw_upload_image-errors').append('<div class="form-group"><strong>'+this.name+'</strong> ('+humanizeSize(this.size)+') : '+this.error+'</div>').parent().show();
                }else{
                    $('#arseopro_tw_upload_image_list').html('<img width="120" src="' + this.url + '" />');
                    $('#arseo-meta-rule-form_tw_custom_image').val(this.filename);
                }
            });
            $('#arseopro_tw_upload_image-files-list').html('');
        },
        fail: function (e, data) {
            $('#arseopro_tw_upload_image-errors').html(data.errorThrown.message).parent().show();
        }
    });
});

function arSEOSwitch(){
    if ($('#ARSF_IOS_MASTER_on').is(':checked')){
        $('.field_ios_icon, .field_ios_icon_preview, .field_ios_remove_icon').addClass('hidden');
    }else{
        $('.field_ios_icon, .field_ios_icon_preview, .field_ios_remove_icon').removeClass('hidden');
    }

    if ($('#ARSF_ANDROID_MASTER_on').is(':checked')){
        $('.field_android_icon, .field_android_icon_preview, .field_android_remove_icon').addClass('hidden');
    }else{
        $('.field_android_icon, .field_android_icon_preview, .field_android_remove_icon').removeClass('hidden');
    }

    if ($('#ARSF_MS_MASTER_on').is(':checked')){
        $('.field_ms_icon, .field_ms_icon_preview, .field_ms_remove_icon').addClass('hidden');
    }else{
        $('.field_ms_icon, .field_ms_icon_preview, .field_ms_remove_icon').removeClass('hidden');
    }
    
    if ($('#ARSP_DISABLE_ANCHOR_on').is(':checked')){
        $('.field_disable_default_attr_anchor').addClass('hidden');
    }else{
        $('.field_disable_default_attr_anchor').removeClass('hidden');
    }
    
    if ($('#ARSP_ENABLE_ATTR_on').is(':checked')){
        $('.field_disable_default_attr').removeClass('hidden');
    }else{
        $('.field_disable_default_attr').addClass('hidden');
    }
}

$('#ARSSP_IMAGES').change(function(){
    switchSitemapImages();
});

function switchSitemapImages(){
    if ($('#ARSSP_IMAGES').val() == '0'){
        $('.field_image_type, .field_image_title, .field_image_caption').addClass('hidden');
    }else{
        $('.field_image_type, .field_image_title, .field_image_caption').removeClass('hidden');
    }
}