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

var keywordTimeout;
var arSEO = {
    errorMessage: 'Operation failed',
    successMessage: 'Operation complete',
    clearConfirmation: 'Clear all items?',
    removeConfirmation: 'Remove selected items?',
    deleteItemConfirmation: 'Are you sure you want to delete item?',
    activateConfirmation: 'Activate selected items?',
    deactivateConfirmation: 'Deactivate selected items?',
    noItemsSelected: 'No items selected',
    terminateLongProcess: false,
    ajaxUrl: '',
    moduleVersion: '',
    utils: {
        editOldRoutes: function(){
            $('#arseo-oldroutes-modal').modal({
                backdrop: 'static',
                show: true
            });
        },
        saveOldRoutes: function(){
            $('#arseo-old-routes-field').removeClass('hidden');
            $.ajax({
                type: 'POST',
                url: arSEO.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'saveOldRoutes',
                    ajax : true,
                    id_shop: $('#arseo-oldroutes-form_id_shop').val(),
                    content: $('#arseo-old-routes').val()
                },
                success: function(data){
                    if (data.success){
                        showSuccessMessage('Routes saved');
                    }else{
                        showErrorMessage(data.error);
                    }
                    arSEO.unblockUI('#arseo-oldroutes-modal .modal-dialog');
                }
            }).fail(function(){
                arSEO.unblockUI('#arseo-oldroutes-modal .modal-dialog');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        loadOldRoutes: function(){
            if ($('#arseo-oldroutes-form_id_shop').val() == 0) {
                $('#arseo-old-routes').val('');
                $('#arseo-old-routes-field').addClass('hidden');
                $('#arseo-oldroutes-modal .btn-success').attr('disabled', true);
                return false;
            }
            arSEO.blockUI('#arseo-oldroutes-modal .modal-dialog');
            $('#arseo-oldroutes-modal .btn-success').removeProp('disabled');
            $('#arseo-old-routes-field').removeClass('hidden');
            $.ajax({
                type: 'POST',
                url: arSEO.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'loadOldRoutes',
                    ajax : true,
                    id_shop: $('#arseo-oldroutes-form_id_shop').val()
                },
                success: function(data){
                    if (data.success){
                        $('#arseo-old-routes').val(data.content);
                    }else{
                        showErrorMessage(arSEO.errorMessage);
                    }
                    arSEO.unblockUI('#arseo-oldroutes-modal .modal-dialog');
                }
            }).fail(function(){
                arSEO.unblockUI('#arseo-oldroutes-modal .modal-dialog');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        reInstallOverrides: function(){
            arSEO.blockUI('#arseopro-utils');
            $.ajax({
                type: 'POST',
                url: arSEO.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'reinstallOverrides',
                    ajax : true,
                },
                success: function(data){
                    if (data.success){
                        arSEO.utils.getOverridesVersion();
                    }else{
                        showErrorMessage(arSEO.errorMessage);
                    }
                    arSEO.unblockUI('#arseopro-utils');
                }
            }).fail(function(){
                arSEO.unblockUI('#arseopro-utils');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        getOverridesVersion: function() {
            arSEO.blockUI('#arseopro-utils');
            $.ajax({
                type: 'POST',
                url: arSEO.ajaxUrl,
                dataType: 'json',
                data: {
                    action : 'getOverridesVersion',
                    ajax : true,
                },
                success: function(data){
                    if (data.success){
                        for (var v in data.versions) {
                            if (data.versions[v] == data.moduleVersion) {
                                $('#arseo-override-' + v).addClass('arseo-success').removeClass('arseo-fail');
                            } else {
                                $('#arseo-override-' + v).addClass('arseo-fail').removeClass('arseo-success');
                            }
                            $('#arseo-override-' + v + ' span').text(data.versions[v]);
                        }
                        showSuccessMessage('Overrides updated');
                    }else{
                        showErrorMessage(arSEO.errorMessage);
                    }
                    arSEO.unblockUI('#arseopro-utils');
                }
            }).fail(function(){
                arSEO.unblockUI('#arseopro-utils');
                showErrorMessage(arSEO.errorMessage);
            });
        }
    },
    lastProcess: {
        name: null,
        id: null,
        offset: 0,
        count: 0,
        processed: 0,
        finished: true,
        all: 0,
        requestTermination: function(){
            arSEO.terminateLongProcess = true;
        },
        terminate: function(){
            arSEO.terminateLongProcess = false;
            $('#arseo-progress-modal .btn-terminate').addClass('hidden');
            $('#arseo-progress-modal .btn-continue').removeClass('hidden');
            $('#arseo-progress-modal .btn-start-over').removeClass('hidden');
            $('#arseo-progress-modal .btn-close').removeClass('hidden');
        },
        continue: function(){
            arSEO.terminateLongProcess = false;
            $('#arseo-progress-modal .btn-terminate').removeClass('hidden');
            $('#arseo-progress-modal .btn-continue').addClass('hidden');
            $('#arseo-progress-modal .btn-start-over').addClass('hidden');
            $('#arseo-progress-modal .btn-close').addClass('hidden');
            if (arSEO.lastProcess.name == 'meta.applyRule'){
                arSEO.meta.applyRule(arSEO.lastProcess.id, arSEO.lastProcess.count, arSEO.lastProcess.offset, arSEO.lastProcess.all);
            }
            if (arSEO.lastProcess.name == 'url.applyRule'){
                arSEO.url.applyRule(arSEO.lastProcess.id, arSEO.lastProcess.count, arSEO.lastProcess.offset, arSEO.lastProcess.all);
            }
        },
        start: function(){
            arSEO.terminateLongProcess = false;
            $('#arseo-progress-modal .progress-bar').css({width: '0%'});
            $('#arseo-progress-modal .progress-bar').text('0%');
            $('#arseo-progress-modal .btn-terminate').removeClass('hidden');
            $('#arseo-progress-modal .btn-continue').addClass('hidden');
            $('#arseo-progress-modal .btn-start-over').addClass('hidden');
            $('#arseo-progress-modal .btn-close').addClass('hidden');
            if (arSEO.lastProcess.name == 'meta.applyRule'){
                arSEO.meta.applyRule(arSEO.lastProcess.id, 0, 0, arSEO.lastProcess.all);
            }
            if (arSEO.lastProcess.name == 'url.applyRule'){
                arSEO.url.applyRule(arSEO.lastProcess.id, 0, 0, arSEO.lastProcess.all);
            }
        },
    },
    init: function(){
        $('#arseopro-config').on('click', '.keywords-select li a', function(){
            var target = $(this).parent().parent().data('target');
            var keyword = '{' + $(this).data('keyword') + '}';
            var $target = $('#'+target);
            
            var currentValue = $target.val();
            
            if (currentValue.indexOf(keyword) == -1){
                currentValue = currentValue + keyword;
                currentValue = currentValue.replace(/}{/g, '} {');
                $target.val(currentValue);
            }else{
                currentValue = currentValue.replace(new RegExp(keyword, 'g'), '');
            }
            currentValue = currentValue.replace(/\s+/g, ' ');
            currentValue = currentValue.replace(/^\s+/g, '');
            currentValue = currentValue.replace(/\s+$/g, '');
            $target.val(currentValue);
            $target.focus();
            return false;
        });
        $('#arseopro-config').on('focus', '.has-keywords', function(){
            clearTimeout(keywordTimeout);
            $('.keywords-select-row').removeClass('active');
            var target = $(this).attr('id') + '-keyword-selector';
            $('#' + target).addClass('active');
        });
        $('#arseopro-config').on('blur', '.has-keywords', function(){
            var $this = $(this);
            keywordTimeout = setTimeout(function(){
                var target = $this.attr('id') + '-keyword-selector';
                $('#' + target).removeClass('active');
            }, 200);
        });
        arSEO.url.reloadRules();
    },
    help: {
        reload: function(){
            arSEO.blockUI('#arseopro-help');
            $.ajax({
                type: 'POST',
                url: arSEO.robots.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.robots.controller,
                    action : 'help',
                    ajax : true,
                },
                success: function(data){
                    if (data.success){
                        $('#arseo-help-content').html(data.content);
                        $('#arseo-help-content .fancybox').fancybox();
                    }else{
                        showErrorMessage(data.error);
                    }
                    arSEO.unblockUI('#arseopro-help');
                }
            }).fail(function(){
                arSEO.unblockUI('#arseopro-help');
                showErrorMessage(arSEO.errorMessage);
            });
        },
    },
    robots: {
        ajaxUrl: null,
        controller: 'AdminArSeoRobots',
        reload: function(showConfirmation){
            if (showConfirmation){
                if (!confirm('All modifications will be lost. Continue?')){
                    return false;
                }
            }
            arSEO.blockUI('#arseopro-robots');
            $.ajax({
                type: 'POST',
                url: arSEO.robots.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.robots.controller,
                    action : 'reload',
                    ajax : true,
                },
                success: function(data){
                    if (data.success){
                        $('#arseopro-robots-form textarea').val(data.content);
                        if (showConfirmation){
                            showSuccessMessage('Content reloaded');
                        }
                    }else{
                        showErrorMessage(data.error);
                    }
                    arSEO.unblockUI('#arseopro-robots');
                }
            }).fail(function(){
                arSEO.unblockUI('#arseopro-robots');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        save: function(){
            arSEO.blockUI('#arseopro-robots');
            $.ajax({
                type: 'POST',
                url: arSEO.robots.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.robots.controller,
                    action : 'save',
                    ajax : true,
                    robots: $('#arseopro-robots-form textarea').val()
                },
                success: function(data){
                    if (data.success){
                        $('#arseopro-robots-form textarea').val(data.content);
                        showSuccessMessage('robots.txt saved');
                    }else{
                        showErrorMessage(data.error);
                    }
                    arSEO.unblockUI('#arseopro-robots');
                }
            }).fail(function(){
                arSEO.unblockUI('#arseopro-robots');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        defaults: function(){
            if (!confirm('All modifications will be lost. Continue?')){
                return false;
            }
            arSEO.blockUI('#arseopro-robots');
            $.ajax({
                type: 'POST',
                url: arSEO.robots.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.robots.controller,
                    action : 'defaults',
                    ajax : true,
                },
                success: function(data){
                    if (data.success){
                        $('#arseopro-robots-form textarea').val(data.content);
                        showSuccessMessage('Defaults restored');
                    }else{
                        showErrorMessage(data.error);
                    }
                    arSEO.unblockUI('#arseopro-robots');
                }
            }).fail(function(){
                arSEO.unblockUI('#arseopro-robots');
                showErrorMessage(arSEO.errorMessage);
            });
        }
    },
    sitemap: {
        ajaxUrl: null,
        controller: 'AdminArSeoSitemap',
        generateUrl: null,
        totalCount: 0,
        totalTime: 0,
        peakMemory: 0,
        productsCount: 0,
        imagesCount: 0,
        resetCounters: function(){
            arSEO.sitemap.totalTime = 0;
            arSEO.sitemap.peakMemory = 0;
            arSEO.sitemap.productsCount = 0;
            arSEO.sitemap.imagesCount = 0;
            arSEO.sitemap.totalCount = 0;
            $('#arseopro-sitemap-time').text('');
            $('#arseopro-sitemap-memory').text('');
            $('#arseopro-sitemap-products-count').text('0');
            $('#arseopro-sitemap-images-count').text('0');
            $('#arseopro-sitemap-categories-count').text('0');
            $('#arseopro-sitemap-meta-count').text('0');
            $('#arseopro-sitemap-cms-count').text('0');
            $('#arseopro-sitemap-manufacturers-count').text('0');
            $('#arseopro-sitemap-suppliers-count').text('0');
            $('#arseo-sitemap-progress>div').css({
                width: '0%'
            });
            $('#arseo-sitemap-progress>div').text('0%');
        },
        generate: function(id_shop, step, page, totalCount, token){
            if (step == 0){
                arSEO.sitemap.resetCounters();
                arSEO.blockUI('#arseopro-sitemap');
            }
            $.ajax({
                type: 'GET',
                url: arSEO.sitemap.generateUrl,
                dataType: 'json',
                data: {
                    redirect: 0,
                    id_shop: id_shop,
                    step: step,
                    page: page,
                    totalCount: totalCount,
                    token: token
                },
                success: function(data){
                    if (!data.success){
                        showErrorMessage(data.error);
                         arSEO.unblockUI('#arseopro-sitemap');
                        return false;
                    }
                    if (data.continue){
                        arSEO.sitemap.generate(data.id_shop, data.step, data.page, data.totalCount, data.token);
                    }else{
                        arSEO.unblockUI('#arseopro-sitemap');
                        if (data.lastgen){
                            $('#arseopro-sitemap-lastgen-' + data.id_shop).text(data.lastgen);
                        }
                    }
                    arSEO.sitemap.totalTime += data.time;
                    $('#arseopro-sitemap-time').text(arSEO.sitemap.totalTime.toFixed(2) + ' s');
                    if (data.memory > arSEO.sitemap.peakMemory){
                        arSEO.sitemap.peakMemory = data.memory;
                    }
                    $('#arseopro-sitemap-memory').text(arSEO.sitemap.peakMemory + ' MB');
                    arSEO.sitemap.productsCount += data.count.product;
                    arSEO.sitemap.imagesCount += data.count.image;
                    $('#arseopro-sitemap-products-count').text(arSEO.sitemap.productsCount);
                    $('#arseopro-sitemap-images-count').text(arSEO.sitemap.imagesCount);
                    if (data.count.category){
                        $('#arseopro-sitemap-categories-count').text(data.count.category);
                    }
                    if (data.count.meta){
                        $('#arseopro-sitemap-meta-count').text(data.count.meta);
                    }
                    if (data.count.cms){
                        $('#arseopro-sitemap-cms-count').text(data.count.cms);
                    }
                    if (data.count.manufacturer){
                        $('#arseopro-sitemap-manufacturers-count').text(data.count.manufacturer);
                    }
                    if (data.count.supplier){
                        $('#arseopro-sitemap-suppliers-count').text(data.count.supplier);
                    }
                    if (data.count.smartblog){
                        $('#arseopro-sitemap-smartblog-pages-count').text(data.count.smartblog);
                    }
                    if (data.count.smartblog_category){
                        $('#arseopro-sitemap-smartblog-categories-count').text(data.count.smartblog_category);
                    }
                    if (data.count.prestablog){
                        $('#arseopro-sitemap-prestablog-pages-count').text(data.count.prestablog);
                    }
                    if (data.count.prestablog_category){
                        $('#arseopro-sitemap-prestablog-categories-count').text(data.count.prestablog_category);
                    }
                    if (data.count.prestablog_author){
                        $('#arseopro-sitemap-prestablog-authors-count').text(data.count.prestablog_author);
                    }
                    if (data.count.simpleblog){
                        $('#arseopro-sitemap-simpleblog-pages-count').text(data.count.simpleblog);
                    }
                    if (data.count.simpleblog_category){
                        $('#arseopro-sitemap-simpleblog-categories-count').text(data.count.simpleblog_category);
                    }
                    if (data.count.faqs){
                        $('#arseopro-sitemap-faq-pages-count').text(data.count.faqs);
                    }
                    if (data.count.faqs_category){
                        $('#arseopro-sitemap-faq-categories-count').text(data.count.faqs_category);
                    }
                    if (data.totalCount){
                        arSEO.sitemap.totalCount += data.realCount.total;
                        var percent = (arSEO.sitemap.totalCount/ data.totalCount * 100);
                        if (data.continue === 0) {
                            percent = 100;
                        }
                        $('#arseo-sitemap-progress>div').css({
                            width: parseInt(percent) + '%'
                        });
                        $('#arseo-sitemap-progress>div').text(parseInt(percent) + '%');
                    }
                }
            }).fail(function(){
                arSEO.unblockUI('#arseopro-sitemap');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        toggleItem: function(el){
            var url = $(el).attr('href');
            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                success: function(data){
                    if (data.success){
                        showSuccessMessage(data.text);
                    }
                    if (data.status){
                        $(el).find('.icon-check').removeClass('hidden');
                        $(el).find('.icon-remove').addClass('hidden');
                        $(el).removeClass('action-disabled').addClass('action-enabled').attr('title', 'Enabled');;
                    }else{
                        $(el).find('.icon-check').addClass('hidden');
                        $(el).find('.icon-remove').removeClass('hidden');
                        $(el).addClass('action-disabled').removeClass('action-enabled').attr('title', 'Disabled');
                    }
                }
            }).fail(function(){
                showErrorMessage(arSEO.errorMessage);
            });
        },
        category: {
            ajaxUrl: null,
            controller: 'AdminArSeoSitemapCategory',
            toggle: function(el){
                $.ajax({
                    type: 'POST',
                    url: arSEO.sitemap.category.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.sitemap.category.controller,
                        action : 'switch',
                        ajax : true,
                        id: $(el).attr('value')
                    },
                    success: function(data){
                        showSuccessMessage(data.text);
                    }
                }).fail(function(){
                    showErrorMessage(arSEO.errorMessage);
                });
            },
            checkAll: function(){
                arSEO.blockUI('#arseo-sitemap-category-tree');
                var ids = [];
                $('#arseo-sitemap-category-tree [type="checkbox"]').each(function(){
                    if ($(this).is(':checked')){
                        ids.push($(this).attr('value'));
                    }
                });
                $.ajax({
                    type: 'POST',
                    url: arSEO.sitemap.category.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.sitemap.category.controller,
                        action : 'checkAll',
                        ajax : true,
                        ids: ids
                    },
                    success: function(data){
                        showSuccessMessage(arSEO.successMessage);
                        arSEO.unblockUI('#arseo-sitemap-category-tree');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#arseo-sitemap-category-tree');
                    showErrorMessage(arSEO.errorMessage);
                });
            },
            uncheckAll: function(){
                arSEO.blockUI('#arseo-sitemap-category-tree');
                $.ajax({
                    type: 'POST',
                    url: arSEO.sitemap.category.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.sitemap.category.controller,
                        action : 'uncheckAll',
                        ajax : true,
                    },
                    success: function(data){
                        showSuccessMessage(arSEO.successMessage);
                        arSEO.unblockUI('#arseo-sitemap-category-tree');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#arseo-sitemap-category-tree');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        meta: {
            ajaxUrl: null,
            controller: 'AdminArSeoSitemapMeta',
            bulk: {
                activate: function(){
                    arSEO.sitemap.meta.bulk._processAction('activate', arSEO.activateConfirmation);
                },
                deactivate: function(){
                    arSEO.sitemap.meta.bulk._processAction('deactivate', arSEO.deactivateConfirmation);
                },
                _processAction: function(action, confirmation){
                    var ids = arSEO.sitemap.meta.bulk._getSelectedIds();
                    if (ids.length){
                        if (confirm(confirmation)) {
                            arSEO.blockUI('#form-sitemap-meta-container');
                            $.ajax({
                                type: 'POST',
                                url: arSEO.sitemap.meta.ajaxUrl,
                                dataType: 'json',
                                data: {
                                    controller : arSEO.sitemap.meta.controller,
                                    action : action,
                                    ajax : true,
                                    ids: ids
                                },
                                success: function(data){
                                    arSEO.unblockUI('#form-sitemap-meta-container');
                                    arSEO.sitemap.meta.reload();
                                    showSuccessMessage(arSEO.successMessage);
                                }
                            }).fail(function(){
                                arSEO.unblockUI('#form-sitemap-meta-container');
                                showErrorMessage(arSEO.errorMessage);
                            });
                        }
                    }else{
                        showErrorMessage(arSEO.noItemsSelected);
                    }
                },
                _getSelectedIds: function(){
                    var ids = [];
                    $('#form-sitemap-meta .row-selector input').each(function(){
                        if ($(this).is(':checked')){
                            ids.push($(this).val());
                        }
                    });
                    return ids;
                }
            },
            reload: function(submit){
                var params = arSEO._getFormData('#form-sitemap-meta', true);
                if (typeof submit != 'undefined' && submit !== null){
                    params.push({
                        name: 'submit',
                        value: submit
                    });
                }
                arSEO.blockUI('#form-sitemap-meta-container');
                $.ajax({
                    type: 'POST',
                    url: arSEO.sitemap.meta.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.sitemap.meta.controller,
                        action : 'reload',
                        ajax : true,
                        data: params,
                        'sitemap-metaBox': arSEO.sitemap.meta.bulk._getSelectedIds()
                    },
                    success: function(data){
                        $('#form-sitemap-meta-container').html(data.content);
                        $('#form-sitemap-meta .pagination-link').off('click');
                        $('#form-sitemap-meta .pagination-items-page').off('click');
                        arSEO.unblockUI('#form-sitemap-meta-container');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#form-sitemap-meta-container');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        cms: {
            ajaxUrl: null,
            controller: 'AdminArSeoSitemapCms',
            bulk: {
                activate: function(){
                    arSEO.sitemap.cms.bulk._processAction('activate', arSEO.activateConfirmation);
                },
                deactivate: function(){
                    arSEO.sitemap.cms.bulk._processAction('deactivate', arSEO.deactivateConfirmation);
                },
                _processAction: function(action, confirmation){
                    var ids = arSEO.sitemap.cms.bulk._getSelectedIds();
                    if (ids.length){
                        if (confirm(confirmation)) {
                            arSEO.blockUI('#form-sitemap-cms-container');
                            $.ajax({
                                type: 'POST',
                                url: arSEO.sitemap.cms.ajaxUrl,
                                dataType: 'json',
                                data: {
                                    controller : arSEO.sitemap.cms.controller,
                                    action : action,
                                    ajax : true,
                                    ids: ids
                                },
                                success: function(data){
                                    arSEO.unblockUI('#form-sitemap-cms-container');
                                    arSEO.sitemap.cms.reload();
                                    showSuccessMessage(arSEO.successMessage);
                                }
                            }).fail(function(){
                                arSEO.unblockUI('#form-sitemap-cms-container');
                                showErrorMessage(arSEO.errorMessage);
                            });
                        }
                    }else{
                        showErrorMessage(arSEO.noItemsSelected);
                    }
                },
                _getSelectedIds: function(){
                    var ids = [];
                    $('#form-sitemap-cms .row-selector input').each(function(){
                        if ($(this).is(':checked')){
                            ids.push($(this).val());
                        }
                    });
                    return ids;
                }
            },
            reload: function(submit){
                var params = arSEO._getFormData('#form-sitemap-cms', true);
                if (typeof submit != 'undefined' && submit !== null){
                    params.push({
                        name: 'submit',
                        value: submit
                    });
                }
                arSEO.blockUI('#form-sitemap-cms-container');
                $.ajax({
                    type: 'POST',
                    url: arSEO.sitemap.cms.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.sitemap.cms.controller,
                        action : 'reload',
                        ajax : true,
                        data: params,
                        'sitemap-cmsBox': arSEO.sitemap.cms.bulk._getSelectedIds()
                    },
                    success: function(data){
                        $('#form-sitemap-cms-container').html(data.content);
                        $('#form-sitemap-cms .pagination-link').off('click');
                        $('#form-sitemap-cms .pagination-items-page').off('click');
                        arSEO.unblockUI('#form-sitemap-cms-container');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#form-sitemap-cms-container');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        manufacturer: {
            ajaxUrl: null,
            controller: 'AdminArSeoSitemapManufacturer',
            bulk: {
                activate: function(){
                    arSEO.sitemap.manufacturer.bulk._processAction('activate', arSEO.activateConfirmation);
                },
                deactivate: function(){
                    arSEO.sitemap.manufacturer.bulk._processAction('deactivate', arSEO.deactivateConfirmation);
                },
                _processAction: function(action, confirmation){
                    var ids = arSEO.sitemap.manufacturer.bulk._getSelectedIds();
                    if (ids.length){
                        if (confirm(confirmation)) {
                            arSEO.blockUI('#form-sitemap-manufacturers-container');
                            $.ajax({
                                type: 'POST',
                                url: arSEO.sitemap.manufacturer.ajaxUrl,
                                dataType: 'json',
                                data: {
                                    controller : arSEO.sitemap.manufacturer.controller,
                                    action : action,
                                    ajax : true,
                                    ids: ids
                                },
                                success: function(data){
                                    arSEO.unblockUI('#form-sitemap-manufacturers-container');
                                    arSEO.sitemap.manufacturer.reload();
                                    showSuccessMessage(arSEO.successMessage);
                                }
                            }).fail(function(){
                                arSEO.unblockUI('#form-sitemap-manufacturers-container');
                                showErrorMessage(arSEO.errorMessage);
                            });
                        }
                    }else{
                        showErrorMessage(arSEO.noItemsSelected);
                    }
                },
                _getSelectedIds: function(){
                    var ids = [];
                    $('#form-sitemap-manufacturers .row-selector input').each(function(){
                        if ($(this).is(':checked')){
                            ids.push($(this).val());
                        }
                    });
                    return ids;
                }
            },
            reload: function(submit){
                var params = arSEO._getFormData('#form-sitemap-manufacturers', true);
                if (typeof submit != 'undefined' && submit !== null){
                    params.push({
                        name: 'submit',
                        value: submit
                    });
                }
                arSEO.blockUI('#form-sitemap-manufacturers-container');
                $.ajax({
                    type: 'POST',
                    url: arSEO.sitemap.manufacturer.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.sitemap.manufacturer.controller,
                        action : 'reload',
                        ajax : true,
                        data: params,
                        'sitemap-manufacturersBox': arSEO.sitemap.manufacturer.bulk._getSelectedIds()
                    },
                    success: function(data){
                        $('#form-sitemap-manufacturers-container').html(data.content);
                        $('#form-sitemap-manufacturers .pagination-link').off('click');
                        $('#form-sitemap-manufacturers .pagination-items-page').off('click');
                        arSEO.unblockUI('#form-sitemap-manufacturers-container');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#form-sitemap-manufacturers-container');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        supplier: {
            ajaxUrl: null,
            controller: 'AdminArSeoSitemapSupplier',
            bulk: {
                activate: function(){
                    arSEO.sitemap.supplier.bulk._processAction('activate', arSEO.activateConfirmation);
                },
                deactivate: function(){
                    arSEO.sitemap.supplier.bulk._processAction('deactivate', arSEO.deactivateConfirmation);
                },
                _processAction: function(action, confirmation){
                    var ids = arSEO.sitemap.supplier.bulk._getSelectedIds();
                    if (ids.length){
                        if (confirm(confirmation)) {
                            arSEO.blockUI('#form-sitemap-suppliers-container');
                            $.ajax({
                                type: 'POST',
                                url: arSEO.sitemap.supplier.ajaxUrl,
                                dataType: 'json',
                                data: {
                                    controller : arSEO.sitemap.supplier.controller,
                                    action : action,
                                    ajax : true,
                                    ids: ids
                                },
                                success: function(data){
                                    arSEO.unblockUI('#form-sitemap-suppliers-container');
                                    arSEO.sitemap.supplier.reload();
                                    showSuccessMessage(arSEO.successMessage);
                                }
                            }).fail(function(){
                                arSEO.unblockUI('#form-sitemap-suppliers-container');
                                showErrorMessage(arSEO.errorMessage);
                            });
                        }
                    }else{
                        showErrorMessage(arSEO.noItemsSelected);
                    }
                },
                _getSelectedIds: function(){
                    var ids = [];
                    $('#form-sitemap-suppliers .row-selector input').each(function(){
                        if ($(this).is(':checked')){
                            ids.push($(this).val());
                        }
                    });
                    return ids;
                }
            },
            reload: function(submit){
                var params = arSEO._getFormData('#form-sitemap-suppliers', true);
                if (typeof submit != 'undefined' && submit !== null){
                    params.push({
                        name: 'submit',
                        value: submit
                    });
                }
                arSEO.blockUI('#form-sitemap-suppliers-container');
                $.ajax({
                    type: 'POST',
                    url: arSEO.sitemap.supplier.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.sitemap.supplier.controller,
                        action : 'reload',
                        ajax : true,
                        data: params,
                        'sitemap-suppliersBox': arSEO.sitemap.supplier.bulk._getSelectedIds()
                    },
                    success: function(data){
                        $('#form-sitemap-suppliers-container').html(data.content);
                        $('#form-sitemap-suppliers .pagination-link').off('click');
                        $('#form-sitemap-suppliers .pagination-items-page').off('click');
                        arSEO.unblockUI('#form-sitemap-suppliers-container');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#form-sitemap-suppliers-container');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        product: {
            ajaxUrl: null,
            controller: 'AdminArSeoSitemapProduct',
            bulk: {
                activate: function(){
                    arSEO.sitemap.product.bulk._processAction('activate', arSEO.activateConfirmation);
                },
                deactivate: function(){
                    arSEO.sitemap.product.bulk._processAction('deactivate', arSEO.deactivateConfirmation);
                },
                _processAction: function(action, confirmation){
                    var ids = arSEO.sitemap.product.bulk._getSelectedIds();
                    if (ids.length){
                        if (confirm(confirmation)) {
                            arSEO.blockUI('#form-sitemap-products-container');
                            $.ajax({
                                type: 'POST',
                                url: arSEO.sitemap.product.ajaxUrl,
                                dataType: 'json',
                                data: {
                                    controller : arSEO.sitemap.product.controller,
                                    action : action,
                                    ajax : true,
                                    ids: ids
                                },
                                success: function(data){
                                    arSEO.unblockUI('#form-sitemap-products-container');
                                    arSEO.sitemap.product.reload();
                                    showSuccessMessage(arSEO.successMessage);
                                }
                            }).fail(function(){
                                arSEO.unblockUI('#form-sitemap-products-container');
                                showErrorMessage(arSEO.errorMessage);
                            });
                        }
                    }else{
                        showErrorMessage(arSEO.noItemsSelected);
                    }
                },
                _getSelectedIds: function(){
                    var ids = [];
                    $('#form-sitemap-products .row-selector input').each(function(){
                        if ($(this).is(':checked')){
                            ids.push($(this).val());
                        }
                    });
                    return ids;
                }
            },
            reload: function(submit){
                var params = arSEO._getFormData('#form-sitemap-products', true);
                if (typeof submit != 'undefined' && submit !== null){
                    params.push({
                        name: 'submit',
                        value: submit
                    });
                }
                arSEO.blockUI('#form-sitemap-products-container');
                $.ajax({
                    type: 'POST',
                    url: arSEO.sitemap.product.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.sitemap.product.controller,
                        action : 'reload',
                        ajax : true,
                        data: params,
                        'sitemap-productsBox': arSEO.sitemap.product.bulk._getSelectedIds()
                    },
                    success: function(data){
                        $('#form-sitemap-products-container').html(data.content);
                        $('#form-sitemap-products .pagination-link').off('click');
                        $('#form-sitemap-products .pagination-items-page').off('click');
                        arSEO.unblockUI('#form-sitemap-products-container');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#form-sitemap-products-container');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        }
    },
    meta: {
        ajaxUrl: null,
        controller: 'AdminArSeoMeta',
        formId: '#arseo-meta-rule-form',
        modalId: '#arseo-meta-modal',
        createTitle: 'New meta tags rule',
        editTitle: 'Edit meta tags rule',
        saveSuccess: 'Rule saved',
        saveError: 'Error. Rule not saved',
        bulk: {
            remove: function(){
                arSEO.meta.bulk._processAction('removeBulk', arSEO.removeConfirmation);
            },
            activate: function(){
                arSEO.meta.bulk._processAction('activate', arSEO.activateConfirmation);
            },
            deactivate: function(){
                arSEO.meta.bulk._processAction('deactivate', arSEO.deactivateConfirmation);
            },
            _processAction: function(action, confirmation){
                var ids = arSEO.meta.bulk._getSelectedIds();
                if (ids.length){
                    if (confirm(confirmation)) {
                        arSEO.blockUI('#form-url-list-container');
                        $.ajax({
                            type: 'POST',
                            url: arSEO.meta.ajaxUrl,
                            dataType: 'json',
                            data: {
                                controller : arSEO.meta.controller,
                                action : action,
                                ajax : true,
                                ids: ids
                            },
                            success: function(data){
                                arSEO.unblockUI('#form-url-list-container');
                                arSEO.meta.reload();
                                showSuccessMessage(arSEO.successMessage);
                            }
                        }).fail(function(){
                            arSEO.unblockUI('#form-url-list-container');
                            showErrorMessage(arSEO.errorMessage);
                        });
                    }
                }else{
                    showErrorMessage(arSEO.noItemsSelected);
                }
            },
            _getSelectedIds: function(){
                var ids = [];
                $('#form-meta-list .row-selector input').each(function(){
                    if ($(this).is(':checked')){
                        ids.push($(this).val());
                    }
                });
                return ids;
            }
        },
        changeTwitterType: function(){
            if ($('#arseo-meta-rule-form_tw_type').val() == 'product'){
                $('.tw-ch-1, .tw-ch-2').removeClass('hidden');
            }else{
                $('.tw-ch-1, .tw-ch-2').addClass('hidden');
            }
        },
        updateKeywords: function(){
            arSEO.clearErrors(arSEO.meta.formId);
            $('#arseopro-meta-meta .keywords-container').html('');
            $('#arseopro-meta-fb .keywords-container').html('');
            $('#arseopro-meta-tw .keywords-container').html('');
            if ($('#arseo-meta-rule-form_rule_type').val() == 'category'){
                $('#arseo-meta-rule-form_fb_image [value="2"]').attr('disabled', '');
                $('#arseo-meta-rule-form_tw_image [value="2"]').attr('disabled', '');
                $('#arseo-meta-rule-form_fb_image [value="1"]').removeAttr('disabled', '');
                $('#arseo-meta-rule-form_tw_image [value="1"]').removeAttr('disabled', '');
                $('.form_group_categories').removeClass('hidden');
                $('.form_group_metapages').addClass('hidden');
            }else if ($('#arseo-meta-rule-form_rule_type').val() == 'product'){
                $('#arseo-meta-rule-form_fb_image [value="2"]').removeAttr('disabled');
                $('#arseo-meta-rule-form_tw_image [value="2"]').removeAttr('disabled');
                $('#arseo-meta-rule-form_fb_image [value="1"]').removeAttr('disabled', '');
                $('#arseo-meta-rule-form_tw_image [value="1"]').removeAttr('disabled', '');
                $('.form_group_categories').removeClass('hidden');
                $('.form_group_metapages').addClass('hidden');
            }else if ($('#arseo-meta-rule-form_rule_type').val() == 'metapage'){
                $('#arseo-meta-rule-form_fb_image [value="1"]').attr('disabled', '');
                $('#arseo-meta-rule-form_tw_image [value="1"]').attr('disabled', '');
                $('#arseo-meta-rule-form_fb_image [value="2"]').attr('disabled', '');
                $('#arseo-meta-rule-form_tw_image [value="2"]').attr('disabled', '');
                $('.form_group_categories').addClass('hidden');
                $('.form_group_metapages').removeClass('hidden');
            }else if ($('#arseo-meta-rule-form_rule_type').val() == 'brand'){
                $('#arseo-meta-rule-form_fb_image [value="1"]').removeAttr('disabled');
                $('#arseo-meta-rule-form_tw_image [value="1"]').removeAttr('disabled');
                $('#arseo-meta-rule-form_fb_image [value="2"]').attr('disabled', '');
                $('#arseo-meta-rule-form_tw_image [value="2"]').attr('disabled', '');
                $('.form_group_categories').addClass('hidden');
                $('.form_group_metapages').addClass('hidden');
            }
            arSEO.meta.checkOgTags();
            $.ajax({
                type: 'POST',
                url: arSEO.meta.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.meta.controller,
                    action : 'getKeywords',
                    ajax : true,
                    type: $('#arseo-meta-rule-form_rule_type').val()
                },
                success: function(data){
                    $.each(data.meta, function(i){
                        $('#arseopro-meta-meta .keywords-container').append(data.meta[i]);
                    });
                    $.each(data.fb, function(i){
                        $('#arseopro-meta-fb .keywords-container').append(data.fb[i]);
                    });
                    $.each(data.tw, function(i){
                        $('#arseopro-meta-tw .keywords-container').append(data.tw[i]);
                    });
                }
            }).fail(function(){
                arSEO.unblockUI(arSEO.meta.modalId + ' .modal-dialog');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        applyRule: function(id, count, offset, all){
            $('#arseo-progress-modal').modal({
                backdrop: 'static',
                show: true
            });
            if (arSEO.lastProcess.finished){
                $('#arseo-progress-modal .progress-bar').css({width: 0});
            }
            $.ajax({
                type: 'POST',
                url: arSEO.meta.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.meta.controller,
                    action : 'applyRule',
                    ajax : true,
                    count: count,
                    offset: offset,
                    all: all,
                    id: id
                },
                success: function(data){
                    if (!data.success){
                        showErrorMessage(data.error);
                        arSEO.lastProcess.finished = true;
                        setTimeout(function(){
                            $('#arseo-progress-modal').modal('hide');
                        }, 500);
                        return false;
                    }
                    arSEO.lastProcess.name = 'meta.applyRule';
                    arSEO.lastProcess.count = data.processed;
                    arSEO.lastProcess.offset = data.offset;
                    arSEO.lastProcess.id = data.id;
                    arSEO.lastProcess.all = all;
                    $('#arseo-progress-rule-name').html(data.rule.name);
                    if (data.continue){
                        arSEO.lastProcess.finished = false;
                        if (!arSEO.terminateLongProcess){
                            arSEO.meta.applyRule(id, data.processed, data.offset, all);
                            $('#arseo-progress-modal .btn-terminate').removeClass('hidden');
                            $('#arseo-progress-modal .btn-continue').addClass('hidden');
                            $('#arseo-progress-modal .btn-start-over').addClass('hidden');
                            $('#arseo-progress-modal .btn-close').addClass('hidden');
                        }else{
                            arSEO.lastProcess.terminate();
                        }
                    }else{
                        arSEO.lastProcess.finished = true;
                        if (all && data.nextRule){
                            $('#arseo-progress-modal .progress-bar').css({width: 0});
                            arSEO.meta.applyRule(data.nextRule, 0, 0, all);
                        }else{
                            arSEO.meta.reload();
                            $('#arseo-progress-modal .btn-terminate').addClass('hidden');
                            $('#arseo-progress-modal .btn-close').removeClass('hidden');
                        }
                    }
                    $('#arseo-progress-modal .progress-bar').css({width: data.percent + '%'});
                    $('#arseo-progress-modal .progress-bar').text(data.percent + '%');
                    $('#arseo-total').text(data.total);
                    $('#arseo-count').text(data.processed);
                }
            }).fail(function(){
                showErrorMessage(arSEO.meta.saveError);
            });
        },
        resetForm: function(type){
            arSEO.resetForm(arSEO.meta.formId);
            $('#arseo-meta-rule-form_rule_type').val(type);
            $('#arseo-meta-rule-form_id_category').trigger('change');
            $('#arseo-meta-rule-form_tw_image').trigger('change');
            $('#arseo-meta-rule-form_fb_image').trigger('change');
            $('#arseopro_fb_upload_image_list').html('');
            $('#arseopro_tw_upload_image_list').html('');
            $('#arseo-meta-rule-form_fb_custom_image').val('');
            $('#arseo-meta-rule-form_tw_custom_image').val('');
            $('#arseo-meta-rule-form_rule_type').trigger('change');
            uncheckAllAssociatedCategories($('#arseo-meta-categories'));
            arSEO.meta.changeTwitterType();
        },
        newRule: function(type){
            arSEO.meta.resetForm(type);
            $(arSEO.meta.modalId + ' .modal-title').text(arSEO.meta.createTitle);
            $(arSEO.meta.modalId).modal('show');
        },
        save: function(stay){
            arSEO.clearErrors(arSEO.meta.formId);
            var params = arSEO._getFormData(arSEO.meta.formId, true);
            arSEO.blockUI(arSEO.meta.modalId + ' .modal-dialog');
            $.ajax({
                type: 'POST',
                url: arSEO.meta.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.meta.controller,
                    action : 'save',
                    ajax : true,
                    data: params,
                    id: $('#arseo-meta-rule-form_id').val()
                },
                success: function(data){
                    arSEO.unblockUI(arSEO.meta.modalId + ' .modal-dialog');
                    if (!arSEO.processErrors(arSEO.meta.formId, data)){
                        showSuccessMessage(arSEO.meta.saveSuccess);
                        if (!stay){
                            $(arSEO.meta.modalId).modal('hide');
                        }else{
                            arSEO.resetForm(arSEO.meta.formId);
                            uncheckAllAssociatedCategories($('#arseo-meta-categories'));
                            $('#arseo-meta-rule-form_id_category').trigger('change');
                        }
                        arSEO.meta.reload();
                    }
                }
            }).fail(function(){
                arSEO.unblockUI(arSEO.meta.modalId + ' .modal-dialog');
                showErrorMessage(arSEO.meta.saveError);
            });
        },
        clear: function(){
            if (confirm(arSEO.clearConfirmation)){
                arSEO.blockUI('#form-url-list');
                $.ajax({
                    type: 'GET',
                    url: arSEO.meta.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.meta.controller,
                        action : 'clear',
                        ajax : true,
                    },
                    success: function(data)
                    {
                        arSEO.meta.reload();
                        arSEO.unblockUI('#form-url-list');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#form-url-list');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        remove: function(id){
            if (confirm(arSEO.deleteItemConfirmation)){
                arSEO.blockUI(arSEO.meta.modalId + ' .modal-dialog');
                $.ajax({
                    type: 'GET',
                    url: arSEO.meta.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.meta.controller,
                        action : 'delete',
                        ajax : true,
                        id: id
                    },
                    success: function(data)
                    {
                        arSEO.unblockUI(arSEO.meta.modalId + ' .modal-dialog');
                        arSEO.meta.reload();
                    }
                }).fail(function(){
                    arSEO.unblockUI(arSEO.meta.modalId + ' .modal-dialog');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        edit: function(id){
            arSEO.meta.resetForm();
            $(arSEO.meta.modalId + ' .modal-title').text(arSEO.meta.editTitle);
            arSEO.blockUI(arSEO.meta.modalId + ' .modal-dialog');
            $.ajax({
                type: 'GET',
                url: arSEO.meta.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.meta.controller,
                    action : 'edit',
                    ajax : true,
                    id: id
                },
                success: function(data)
                {
                    $(arSEO.meta.modalId).modal('show');
                    arSEO.populateForm(arSEO.meta.formId, data);
                    $('#arseo-meta-rule-form_id_category').trigger('change');
                    $('#arseo-meta-categories').tree('expandAll');
                    $('#collapse-all-arseo-meta-categories').show();
                    $('#expand-all-arseo-meta-categories').hide(); 
                    arSEO.unblockUI(arSEO.meta.modalId + ' .modal-dialog');
                    if (data.categories){
                        if (data.rule_type == 'metapage'){
                            arSEO.blockUI('#arseo-meta-pages-container');
                        }else{
                            arSEO.blockUI('#arseo-meta-categories-container');
                        }
                    }
                    if (data.fb_custom_image_url){
                        $('#arseopro_fb_upload_image_list').html('<img src="' + data.fb_custom_image_url + '" width="120"/>');
                    }
                    if (data.tw_custom_image_url){
                        $('#arseopro_tw_upload_image_list').html('<img src="' + data.tw_custom_image_url + '" width="120"/>');
                    }
                    $('#arseo-meta-rule-form_fb_image').trigger('change');
                    $('#arseo-meta-rule-form_tw_image').trigger('change');
                    $('#arseo-meta-rule-form_rule_type').trigger('change');
                    setTimeout(function(){
                        if (data.categories){
                            if (data.rule_type == 'metapage'){
                                $.each(data.categories, function(index){
                                    $('#arseo-meta-pages-container [value="' + data.categories[index] + '"]').prop('checked', true);
                                });
                                arSEO.unblockUI('#arseo-meta-pages-container');
                            }else{
                                $.each(data.categories, function(index){
                                    $('#arseo-meta-categories [value="' + data.categories[index] + '"]').prop('checked', true);
                                });
                                arSEO.unblockUI('#arseo-meta-categories-container');
                            }
                        }
                    }, 1000);
                    
                }
            }).fail(function(){
                arSEO.unblockUI(arSEO.meta.modalId + ' .modal-dialog');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        reload: function(submit){
            arSEO.clearErrors(arSEO.meta.formId);
            var params = arSEO._getFormData('#form-meta-list', true);
            if (typeof submit != 'undefined' && submit !== null){
                params.push({
                    name: 'submit',
                    value: submit
                });
            }
            arSEO.blockUI('#form-meta-list-container');
            $.ajax({
                type: 'POST',
                url: arSEO.meta.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.meta.controller,
                    action : 'reload',
                    ajax : true,
                    data: params,
                    'meta-listBox': arSEO.meta.bulk._getSelectedIds()
                },
                success: function(data){
                    $('#form-meta-list-container').html(data.content);
                    $('#form-meta-list .pagination-link').off('click');
                    $('#form-meta-list .pagination-items-page').off('click');
                    arSEO.unblockUI('#form-url-list-container');
                }
            }).fail(function(){
                arSEO.unblockUI('#form-meta-list-container');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        checkOgTags: function(){
            if ($('#arseo-meta-rule-form_rule_type').val() != 'product'){
                $('#arseopro-meta-fb-alert').html('');
                return false;
            }
            arSEO.blockUI('#arseo-meta-rule-form');
            $.ajax({
                type: 'POST',
                url: arSEO.meta.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.meta.controller,
                    action : 'checkOgTags',
                    ajax : true
                },
                success: function(data){
                    $('#arseopro-meta-fb-alert').html(data.content);
                    arSEO.unblockUI('#arseo-meta-rule-form');
                }
            }).fail(function(){
                arSEO.unblockUI('#arseo-meta-rule-form');
                showErrorMessage(arSEO.errorMessage);
            });
        }
    },
    url: {
        ajaxUrl: null,
        controller: 'AdminArSeoUrls',
        formId: '#arseo-url-rule-form',
        modalId: '#arseo-url-modal',
        createTitle: 'New URL rewrite rule',
        editTitle: 'Edit URL rewrite rule',
        saveSuccess: 'Rule saved',
        saveError: 'Error. Rule not saved',
        resetOldRoutes: function(){
            if (!confirm('Are you sure you want reset all old routes?')) {
                return false;
            }
            arSEO.blockUI('#arseopro-url');
            $.ajax({
                type: 'POST',
                url: arSEO.url.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.url.controller,
                    action : 'resetOldRoutes',
                    ajax : true
                },
                success: function(data){
                    arSEO.unblockUI('#arseopro-url');
                    arSEO.url.reloadRules();
                    showSuccessMessage(arSEO.successMessage);
                }
            }).fail(function(){
                arSEO.unblockUI('#arseopro-url');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        resetRoutes: function(){
            if (!confirm('Are you sure you want reset all routes?')) {
                return false;
            }
            arSEO.blockUI('#arseopro-url');
            $.ajax({
                type: 'POST',
                url: arSEO.url.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.url.controller,
                    action : 'resetRoutes',
                    ajax : true
                },
                success: function(data){
                    arSEO.unblockUI('#arseopro-url');
                    arSEO.url.reloadRules();
                    showSuccessMessage(arSEO.successMessage);
                }
            }).fail(function(){
                arSEO.unblockUI('#arseopro-url');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        bulk: {
            remove: function(){
                arSEO.url.bulk._processAction('removeBulk', arSEO.removeConfirmation);
            },
            activate: function(){
                arSEO.url.bulk._processAction('activate', arSEO.activateConfirmation);
            },
            deactivate: function(){
                arSEO.url.bulk._processAction('deactivate', arSEO.deactivateConfirmation);
            },
            _processAction: function(action, confirmation){
                var ids = arSEO.url.bulk._getSelectedIds();
                if (ids.length){
                    if (confirm(confirmation)) {
                        arSEO.blockUI('#form-url-list-container');
                        $.ajax({
                            type: 'POST',
                            url: arSEO.url.ajaxUrl,
                            dataType: 'json',
                            data: {
                                controller : arSEO.url.controller,
                                action : action,
                                ajax : true,
                                ids: ids
                            },
                            success: function(data){
                                arSEO.unblockUI('#form-url-list-container');
                                arSEO.url.reload();
                                showSuccessMessage(arSEO.successMessage);
                            }
                        }).fail(function(){
                            arSEO.unblockUI('#form-url-list-container');
                            showErrorMessage(arSEO.errorMessage);
                        });
                    }
                }else{
                    showErrorMessage(arSEO.noItemsSelected);
                }
            },
            _getSelectedIds: function(){
                var ids = [];
                $('#form-url-list .row-selector input').each(function(){
                    if ($(this).is(':checked')){
                        ids.push($(this).val());
                    }
                });
                return ids;
            }
        },
        applyRule: function(id, count, offset, all){
            $('#arseo-progress-modal').modal({
                backdrop: 'static',
                show: true
            });
            if (arSEO.lastProcess.finished){
                $('#arseo-progress-modal .progress-bar').css({width: 0});
            }
            $.ajax({
                type: 'POST',
                url: arSEO.url.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.url.controller,
                    action : 'applyRule',
                    ajax : true,
                    count: count,
                    offset: offset,
                    id: id,
                    all: all
                },
                success: function(data){
                    if (!data.success){
                        showErrorMessage(data.error);
                        arSEO.lastProcess.finished = true;
                        setTimeout(function(){
                            $('#arseo-progress-modal').modal('hide');
                        }, 500);
                        return false;
                    }
                    arSEO.lastProcess.name = 'url.applyRule';
                    arSEO.lastProcess.count = data.processed;
                    arSEO.lastProcess.offset = data.offset;
                    arSEO.lastProcess.id = data.id;
                    $('#arseo-progress-rule-name').html(data.rule.name);
                    if (data.continue){
                        arSEO.lastProcess.finished = false;
                        if (!arSEO.terminateLongProcess){
                            arSEO.url.applyRule(id, data.processed, data.offset, all);
                            $('#arseo-progress-modal .btn-terminate').removeClass('hidden');
                            $('#arseo-progress-modal .btn-continue').addClass('hidden');
                            $('#arseo-progress-modal .btn-start-over').addClass('hidden');
                            $('#arseo-progress-modal .btn-close').addClass('hidden');
                        }else{
                            arSEO.lastProcess.terminate();
                        }
                    }else{
                        arSEO.lastProcess.finished = true;
                        if (all && data.nextRule){
                            $('#arseo-progress-modal .progress-bar').css({width: 0});
                            arSEO.url.applyRule(data.nextRule, 0, 0, all);
                        }else{
                            arSEO.url.reload();
                            $('#arseo-progress-modal .btn-terminate').addClass('hidden');
                            $('#arseo-progress-modal .btn-close').removeClass('hidden');
                        }
                    }
                    $('#arseo-progress-modal .progress-bar').css({width: data.percent + '%'});
                    $('#arseo-progress-modal .progress-bar').text(data.percent + '%');
                    $('#arseo-total').text(data.total);
                    $('#arseo-count').text(data.processed);
                }
            }).fail(function(){
                showErrorMessage(arSEO.url.saveError);
            });
        },
        duplication: {
            updateLinkRewrite: function(val, $this){
                var params = arSEO._getFormData('#arseo-duplication-form', true);
                $.ajax({
                    type: 'POST',
                    url: arSEO.url.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.url.controller,
                        action : 'duplicateLinkRewrite',
                        ajax : true,
                        data: params,
                        id_lang: $($this).data('lang')
                    },
                    success: function(data){
                        $.each(data.link_rewrite, function(i){
                            $('#link-rewrite-lang-' + i + ' .actual-rewrite').text(data.link_rewrite[i]);
                        });
                    }
                }).fail(function(){
                    showErrorMessage(arSEO.url.saveError);
                });
            },
            save: function(){
                arSEO.clearErrors('#arseo-duplication-form');
                var params = arSEO._getFormData('#arseo-duplication-form', true);
                arSEO.blockUI('#arseo-duplication-modal .modal-dialog');
                $.ajax({
                    type: 'POST',
                    url: arSEO.url.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.url.controller,
                        action : 'duplicateSave',
                        ajax : true,
                        data: params,
                        type: $('#arseo-duplication-form_type').val(),
                        id: $('#arseo-duplication-form_id').val()
                    },
                    success: function(data){
                        arSEO.unblockUI('#arseo-duplication-modal .modal-dialog');
                        if (!arSEO.processErrors('#arseo-duplication-form', data)){
                            showSuccessMessage(arSEO.url.saveSuccess);
                            $('#arseo-duplication-modal').modal('hide');
                            arSEO.url.duplication.reload();
                        }
                    }
                }).fail(function(){
                    arSEO.unblockUI('#arseo-duplication-modal .modal-dialog');
                    showErrorMessage(arSEO.url.saveError);
                });
            },
            edit: function(id){
                arSEO.resetForm('#arseo-duplication-form');
                uncheckAllAssociatedCategories($('#arseo-categories'));
                $('#arseo-duplication-modal .modal-title').text(arSEO.url.editTitle);
                arSEO.blockUI('#arseo-duplication-modal .modal-dialog');
                $.ajax({
                    type: 'GET',
                    url: arSEO.url.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.url.controller,
                        action : 'duplicateEdit',
                        ajax : true,
                        id: id
                    },
                    success: function(data)
                    {
                        if (data.field == 'link_rewrite'){
                            $('.link-rewrite-group').removeClass('hidden');
                            $('.name-group').addClass('hidden');
                        }else{
                            $('.link-rewrite-group').addClass('hidden');
                            $('.name-group').removeClass('hidden');
                        }
                        $('#arseo-duplication-modal').modal('show');
                        arSEO.populateForm('#arseo-duplication-form', data);
                        arSEO.unblockUI('#arseo-duplication-modal .modal-dialog');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#arseo-duplication-modal .modal-dialog');
                    showErrorMessage(arSEO.errorMessage);
                });
            },
            reload: function(submit){
                arSEO.clearErrors(arSEO.url.formId);
                var params = arSEO._getFormData('#form-url-duplication-list', true);
                if (typeof submit != 'undefined' && submit !== null){
                    params.push({
                        name: 'submit',
                        value: submit
                    });
                }
                arSEO.blockUI('#form-url-duplication-list-container');
                $.ajax({
                    type: 'POST',
                    url: arSEO.url.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.url.controller,
                        action : 'duplicatesReload',
                        ajax : true,
                        data: params,
                        'url-duplication-listBox': arSEO.url.bulk._getSelectedIds()
                    },
                    success: function(data){
                        $('#form-url-duplication-list-container').html(data.content);
                        $('#form-url-duplication-list .pagination-link').off('click');
                        $('#form-url-duplication-list .pagination-items-page').off('click');
                        arSEO.unblockUI('#form-url-duplication-list-container');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#form-url-duplication-list-container');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        newRule: function(){
            arSEO.resetForm(arSEO.url.formId);
            uncheckAllAssociatedCategories($('#arseo-categories'));
            $('#arseo-url-rule-form_id_category').trigger('change');
            $(arSEO.url.modalId + ' .modal-title').text(arSEO.url.createTitle);
            $(arSEO.url.modalId).modal('show');
        },
        clear: function(){
            if (confirm(arSEO.clearConfirmation)){
                arSEO.blockUI('#form-url-list');
                $.ajax({
                    type: 'GET',
                    url: arSEO.url.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.url.controller,
                        action : 'clear',
                        ajax : true,
                    },
                    success: function(data)
                    {
                        arSEO.url.reload();
                        arSEO.unblockUI('#form-url-list');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#form-url-list');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        remove: function(id){
            if (confirm(arSEO.deleteItemConfirmation)){
                arSEO.blockUI(arSEO.url.modalId + ' .modal-dialog');
                $.ajax({
                    type: 'GET',
                    url: arSEO.url.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.url.controller,
                        action : 'delete',
                        ajax : true,
                        id: id
                    },
                    success: function(data)
                    {
                        arSEO.unblockUI(arSEO.url.modalId + ' .modal-dialog');
                        arSEO.url.reload();
                    }
                }).fail(function(){
                    arSEO.unblockUI(arSEO.url.modalId + ' .modal-dialog');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        edit: function(id){
            arSEO.resetForm(arSEO.url.formId);
            uncheckAllAssociatedCategories($('#arseo-categories'));
            $(arSEO.url.modalId + ' .modal-title').text(arSEO.url.editTitle);
            arSEO.blockUI(arSEO.url.modalId + ' .modal-dialog');
            $.ajax({
                type: 'GET',
                url: arSEO.url.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.url.controller,
                    action : 'edit',
                    ajax : true,
                    id: id
                },
                success: function(data)
                {
                    $(arSEO.url.modalId).modal('show');
                    arSEO.populateForm(arSEO.url.formId, data);
                    arSEO.unblockUI(arSEO.url.modalId + ' .modal-dialog');
                    $('#arseo-url-rule-form_id_category').trigger('change');
                    $('#arseo-categories').tree('expandAll');
                    $('#collapse-all-arseo-categories').show();
                    $('#expand-all-arseo-categories').hide(); 
                    if (data.categories){
                        arSEO.blockUI('#arseo-categories-container');
                    }
                    setTimeout(function(){
                        if (data.categories){
                            $.each(data.categories, function(index){
                                $('#arseo-categories [value="' + data.categories[index] + '"]').prop('checked', true);
                            });
                            arSEO.unblockUI('#arseo-categories-container');
                        }
                    }, 1000);
                    
                }
            }).fail(function(){
                arSEO.unblockUI(arSEO.url.modalId + ' .modal-dialog');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        save: function(stay){
            arSEO.clearErrors(arSEO.url.formId);
            var params = arSEO._getFormData(arSEO.url.formId, true);
            arSEO.blockUI(arSEO.url.modalId + ' .modal-dialog');
            $.ajax({
                type: 'POST',
                url: arSEO.url.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.url.controller,
                    action : 'save',
                    ajax : true,
                    data: params,
                    id: $('#arseo-url-rule-form_id').val()
                },
                success: function(data){
                    arSEO.unblockUI(arSEO.url.modalId + ' .modal-dialog');
                    if (!arSEO.processErrors(arSEO.url.formId, data)){
                        showSuccessMessage(arSEO.url.saveSuccess);
                        if (!stay){
                            $(arSEO.url.modalId).modal('hide');
                        }else{
                            arSEO.resetForm(arSEO.url.formId);
                            uncheckAllAssociatedCategories($('#arseo-categories'));
                            $('#arseo-url-rule-form_id_category').trigger('change');
                        }
                        arSEO.url.reload();
                    }
                }
            }).fail(function(){
                arSEO.unblockUI(arSEO.url.modalId + ' .modal-dialog');
                showErrorMessage(arSEO.url.saveError);
            });
        },
        reload: function(submit){
            arSEO.clearErrors(arSEO.url.formId);
            var params = arSEO._getFormData('#form-url-list', true);
            if (typeof submit != 'undefined' && submit !== null){
                params.push({
                    name: 'submit',
                    value: submit
                });
            }
            arSEO.blockUI('#form-url-list-container');
            $.ajax({
                type: 'POST',
                url: arSEO.url.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.url.controller,
                    action : 'reload',
                    ajax : true,
                    data: params,
                    'url-listBox': arSEO.url.bulk._getSelectedIds()
                },
                success: function(data){
                    $('#form-url-list-container').html(data.content);
                    $('#form-url-list .pagination-link').off('click');
                    $('#form-url-list .pagination-items-page').off('click');
                    arSEO.unblockUI('#form-url-list-container');
                }
            }).fail(function(){
                arSEO.unblockUI('#form-url-list-container');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        reloadRules: function(){
            $.ajax({
                type: 'GET',
                url: arSEO.url.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.url.controller,
                    action : 'reloadRoutes',
                    ajax : true
                },
                success: function(data){
                    $.each(data, function(index){
                        $('#arseo_' + index).html(data[index]);
                    });
                }
            });
        }
    },
    redirect: {
        ajaxUrl: null,
        formId: '#arseo-redirect-form',
        modalId: '#arseo-redirect-modal',
        controller: 'AdminArSeoRedirects',
        createTitle: 'New redirect rule',
        editTitle: 'Edit redirect rule',
        saveSuccess: 'Rule saved',
        saveError: 'Error. Rule not saved',
        bulk: {
            remove: function(){
                arSEO.redirect.bulk._processAction('removeBulk', arSEO.removeConfirmation);
            },
            activate: function(){
                arSEO.redirect.bulk._processAction('activate', arSEO.activateConfirmation);
            },
            deactivate: function(){
                arSEO.redirect.bulk._processAction('deactivate', arSEO.deactivateConfirmation);
            },
            _processAction: function(action, confirmation){
                var ids = arSEO.redirect.bulk._getSelectedIds();
                if (ids.length){
                    if (confirm(confirmation)) {
                        arSEO.blockUI('#form-redirect-list-container');
                        $.ajax({
                            type: 'POST',
                            url: arSEO.redirect.ajaxUrl,
                            dataType: 'json',
                            data: {
                                controller : arSEO.redirect.controller,
                                action : action,
                                ajax : true,
                                ids: ids
                            },
                            success: function(data){
                                arSEO.unblockUI('#form-redirect-list-container');
                                arSEO.redirect.reload();
                                showSuccessMessage(arSEO.successMessage);
                            }
                        }).fail(function(){
                            arSEO.unblockUI('#form-redirect-list-container');
                            showErrorMessage(arSEO.errorMessage);
                        });
                    }
                }else{
                    showErrorMessage(arSEO.noItemsSelected);
                }
            },
            _getSelectedIds: function(){
                var ids = [];
                $('#form-redirect-list .row-selector input').each(function(){
                    if ($(this).is(':checked')){
                        ids.push($(this).val());
                    }
                });
                return ids;
            }
        },
        importModal: function(){
            $('#arseo-redirect-modal-import').modal('show');
        },
        switch: function(){
            arSEO.blockUI('#form-redirect-list-container');
            $.ajax({
                type: 'POST',
                url: arSEO.redirect.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.redirect.controller,
                    action : 'switch',
                    ajax : true
                },
                success: function(data){
                    arSEO.unblockUI('#form-redirect-list-container');
                    if (data.active){
                        $('#arseopro-redirect .form-wrapper .ar-block').removeClass('active');
                    }else{
                        $('#arseopro-redirect .form-wrapper .ar-block').addClass('active');
                    }
                }
            }).fail(function(){
                arSEO.unblockUI('#form-redirect-list-container');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        switchLog: function(){
            arSEO.blockUI('#form-redirect-list-container');
            $.ajax({
                type: 'POST',
                url: arSEO.redirect.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.redirect.controller,
                    action : 'switchLog',
                    ajax : true
                },
                success: function(data){
                    arSEO.unblockUI('#form-redirect-list-container');
                }
            }).fail(function(){
                arSEO.unblockUI('#form-redirect-list-container');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        export: function(){
            $('#arseo-export-progress > div').css({
                width: '0%'
            }).text('0%');
            $('#arseo-export-progress-container').addClass('hidden');
            $('#arseo-export-complete').addClass('hidden');
            $('#arseo-redirect-export').modal('show');
        },
        _export: function(page, count){
            arSEO.blockUI('#arseo-redirect-export .modal-dialog');
            $('#arseo-export-progress-container').removeClass('hidden');
            $.ajax({
                type: 'GET',
                url: arSEO.redirect.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.redirect.controller,
                    action : 'export',
                    ajax : true,
                    page: page,
                    count: count
                },
                success: function(data)
                {
                    if (data.success){
                        if (data.continue){
                            arSEO.redirect._export(data.page, data.processed);
                        }else{
                            showSuccessMessage(arSEO.successMessage);
                            arSEO.unblockUI('#arseo-redirect-export .modal-dialog');
                            $('#arseo-export-generated').removeClass('hidden').find('small').text(data.time);
                            $('#arseo-export-complete').removeClass('hidden');
                            
                        }
                        $('#arseo-export-processed').text(data.processed);
                        $('#arseo-export-total').text(data.totalCount);
                        $('#arseo-export-progress > div').css({
                            width: data.percent + '%'
                        }).text(data.percent + '%');
                    }else{
                        showErrorMessage(data.error);
                        arSEO.unblockUI('#arseo-redirect-export .modal-dialog');
                    }
                }
            }).fail(function(){
                showErrorMessage(arSEO.errorMessage);
                arSEO.unblockUI('#arseo-redirect-export .modal-dialog');
            });
        },
        notFoundList: function(){
            $('#not-found-progress > div').css({
                width: '0%'
            }).text('0%');
            $('#not-found-progress-container').addClass('hidden');
            $('#not-found-complete').addClass('hidden');
            $('#arseo-redirect-modal2').modal('show');
        },
        _generateNotFoundList: function(page, count){
            arSEO.blockUI('#arseo-redirect-modal2 .modal-dialog');
            $('#not-found-progress-container').removeClass('hidden');
            $.ajax({
                type: 'GET',
                url: arSEO.redirect.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.redirect.controller,
                    action : 'pageNotFound',
                    ajax : true,
                    page: page,
                    count: count,
                    to: $('#arseo-redirect-nfl_to').val(),
                    type: $('#arseo-redirect-nfl_type').val(),
                },
                success: function(data)
                {
                    if (data.success){
                        if (data.continue){
                            arSEO.redirect._generateNotFoundList(data.page, data.processed);
                        }else{
                            showSuccessMessage(arSEO.successMessage);
                            arSEO.unblockUI('#arseo-redirect-modal2 .modal-dialog');
                            $('#not-found-generated').removeClass('hidden').find('small').text(data.time);
                            $('#not-found-complete').removeClass('hidden');
                            
                        }
                        $('#arseor-processed').text(data.processed);
                        $('#arseor-total').text(data.totalCount);
                        $('#not-found-progress > div').css({
                            width: data.percent + '%'
                        }).text(data.percent + '%');
                    }else{
                        showErrorMessage(data.error);
                        arSEO.unblockUI('#arseo-redirect-modal2 .modal-dialog');
                    }
                }
            }).fail(function(){
                showErrorMessage(arSEO.errorMessage);
                arSEO.unblockUI('#arseo-redirect-modal2 .modal-dialog');
            });
        },
        newRule: function(){
            arSEO.resetForm(arSEO.redirect.formId);
            $(arSEO.redirect.modalId + ' .modal-title').text(arSEO.redirect.createTitle);
            $(arSEO.redirect.modalId).modal('show');
            
        },
        remove: function(id){
            if (confirm(arSEO.deleteItemConfirmation)){
                arSEO.blockUI(arSEO.redirect.modalId + ' .modal-dialog');
                $.ajax({
                    type: 'GET',
                    url: arSEO.redirect.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.redirect.controller,
                        action : 'delete',
                        ajax : true,
                        id: id
                    },
                    success: function(data)
                    {
                        arSEO.unblockUI(arSEO.redirect.modalId + ' .modal-dialog');
                        arSEO.redirect.reload();
                    }
                }).fail(function(){
                    arSEO.unblockUI(arSEO.redirect.modalId + ' .modal-dialog');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        clear: function(){
            if (confirm(arSEO.clearConfirmation)){
                arSEO.blockUI('#form-redirect-list');
                $.ajax({
                    type: 'GET',
                    url: arSEO.redirect.ajaxUrl,
                    dataType: 'json',
                    data: {
                        controller : arSEO.redirect.controller,
                        action : 'clear',
                        ajax : true,
                    },
                    success: function(data)
                    {
                        arSEO.redirect.reload();
                        arSEO.unblockUI('#form-redirect-list');
                    }
                }).fail(function(){
                    arSEO.unblockUI('#form-redirect-list');
                    showErrorMessage(arSEO.errorMessage);
                });
            }
        },
        edit: function(id){
            arSEO.resetForm(arSEO.redirect.formId);
            $(arSEO.redirect.modalId + ' .modal-title').text(arSEO.redirect.editTitle);
            arSEO.blockUI(arSEO.redirect.modalId + ' .modal-dialog');
            $.ajax({
                type: 'GET',
                url: arSEO.redirect.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.redirect.controller,
                    action : 'edit',
                    ajax : true,
                    id: id
                },
                success: function(data)
                {
                    $(arSEO.redirect.modalId).modal('show');
                    arSEO.populateForm(arSEO.redirect.formId, data);
                    arSEO.unblockUI(arSEO.redirect.modalId + ' .modal-dialog');
                }
            }).fail(function(){
                arSEO.unblockUI(arSEO.redirect.modalId + ' .modal-dialog');
                showErrorMessage(arSEO.errorMessage);
            });
        },
        save: function(stay){
            arSEO.clearErrors(arSEO.redirect.formId);
            var params = arSEO._getFormData(arSEO.redirect.formId);
            arSEO.blockUI(arSEO.redirect.modalId + ' .modal-dialog');
            $.ajax({
                type: 'POST',
                url: arSEO.redirect.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.redirect.controller,
                    action : 'save',
                    ajax : true,
                    data: params,
                    id: $('#arseo-redirect-form_id').val()
                },
                success: function(data){
                    arSEO.unblockUI(arSEO.redirect.modalId + ' .modal-dialog');
                    if (!arSEO.processErrors(arSEO.redirect.formId, data)){
                        showSuccessMessage(arSEO.redirect.saveSuccess);
                        if (!stay){
                            $(arSEO.redirect.modalId).modal('hide');
                        }else{
                            arSEO.resetForm(arSEO.redirect.formId);
                        }
                        arSEO.redirect.reload();
                    }
                }
            }).fail(function(){
                arSEO.unblockUI(arSEO.redirect.modalId + ' .modal-dialog');
                showErrorMessage(arSEO.redirect.saveError);
            });
        },
        reload: function(submit){
            arSEO.clearErrors(arSEO.redirect.formId);
            var params = arSEO._getFormData('#form-redirect-list', true);
            if (typeof submit != 'undefined' && submit !== null){
                params.push({
                    name: 'submit',
                    value: submit
                });
            }
            arSEO.blockUI('#form-redirect-list-container');
            $.ajax({
                type: 'POST',
                url: arSEO.redirect.ajaxUrl,
                dataType: 'json',
                data: {
                    controller : arSEO.redirect.controller,
                    action : 'reload',
                    ajax : true,
                    data: params,
                    'redirect-listBox': arSEO.redirect.bulk._getSelectedIds()
                },
                success: function(data){
                    $('#form-redirect-list-container').html(data.content);
                    $('#form-redirect-list .pagination-link').off('click');
                    $('#form-redirect-list .pagination-items-page').off('click');
                    arSEO.unblockUI('#form-redirect-list-container');
                }
            }).fail(function(){
                arSEO.unblockUI('#form-redirect-list-container');
                showErrorMessage(arSEO.errorMessage);
            });
        }
    },
    _getFormData: function(form, all){
        var params = [];
        var selector = '';
        if (all){
            selector = form + ' input, ' + form + ' select';
        }else{
            selector = form + ' [data-serializable="true"]'  
        }
        $(selector).each(function(){
            var val = $(this).val();
            if ($(this).attr('type') == 'checkbox'){
                val = $(this).is(':checked')? $(this).val() : 0;
            }
            params.push({
                name: $(this).attr('name'),
                value: val
            });
        });
        return params;
    },
    clearErrors: function(form){
        $(form + ' .form-group.has-error').removeClass('has-error');
        $(form + ' .nav-tabs .has-error').removeClass('has-error');
    },
    processErrors: function(form, data){
        arSEO.clearErrors();
        if (data.success == 0){
            $.each(data.errors, function(index){
                $(form + '_'+index).parents('.form-group').addClass('has-error');
                $(form + '_'+index).parents('.form-group').find('.errors').text(data.errors[index]);
                if ($(form + '_'+index).parents('.tab-pane').length){
                    var tabId = $(form + '_'+index).parents('.tab-pane').attr('id');
                    $('.nav-tabs li>a[href="#' + tabId + '"]').parent().addClass('has-error');
                }
            });
            showErrorMessage(arSEO.errorMessage);
            return true;
        }
        return false;
    },
    resetForm: function(form){
        arSEO.clearErrors(form);
        $(form + ' [data-default').each(function(){
            var attr = $(this).attr('data-default');
            if (typeof attr !== typeof undefined && attr !== false) {
                if ($(this).attr('type') == 'checkbox'){
                    if ($(this).data('default') == 1){
                        $(this).prop('checked', 'true');
                    }else{
                        $(this).removeProp('checked');
                    }
                }else{
                    $(this).val($(this).data('default'));
                }
            }
        });
    },
    blockUI: function(selector){
        $(selector).addClass('ar-blocked');
        $(selector).find('.ar-loading').remove();
        $(selector).append('<div class="ar-loading"><div class="ar-loading-inner">Loading...</div></div>');
    },
    unblockUI: function(selector){
        $(selector).find('.ar-loading').remove();
        $(selector).removeClass('ar-blocked');
    },
    populateForm: function(form, data){
        $.each(data, function(i){
            var fieldId = form + '_' + i;
            if (typeof data[i] == 'object'){
                if (data[i] != null){
                    $.each(data[i], function(id_lang){
                        $(fieldId + '_' + id_lang).val(data[i][id_lang]);
                    });
                }
            }else{
                if ($(fieldId).attr('type') == 'checkbox'){
                    if (data[i] == 1){
                        $(fieldId).prop('checked', 'true');
                    }else{
                        $(fieldId).removeProp('checked');
                    }
                }else{
                    $(fieldId).val(data[i]);
                }
            }
        });
    }
};