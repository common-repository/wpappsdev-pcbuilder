import "../css/wpadpcbu-public.css";
import domtoimage from 'dom-to-image';
(function($) {
    'use strict';
    var xhr, timeout;

    let loaderDisplay = function(classId, displayOption = '') {
        if (displayOption == 'hide') {
            $(classId).waitMe('hide');
        } else {
            $(classId).waitMe({ effect: 'ios' });
        }
    }

    let taxFiltersData = function() {
        let filtersData = [];
        $.each($(".wpadpcbu-filter-panel input[type='checkbox']:checked"), function() {
            filtersData.push({
                tax: $(this).data("tax"),
                val: $(this).val()
            });
        });

        return filtersData;
    }

    let getSearchData = function() {
        return $("#input-search").val();
    }

    let getSortByData = function() {
        return $("#input-sort option:selected").val();
    }

    let priceStart = function() {
        return $('input.wpadpcbu-price-start').val();
    }

    let priceEnd = function() {
        return $('input.wpadpcbu-price-end').val();
    }

    let productsFilterAjaxCall = function(data) {
        let loaderDiv = ".wpadpcbu-component-products";
        $.ajax(wpadpcbu_public.ajaxurl, {
            method: 'post',
            data: data,
            beforeSend: function() {
                loaderDisplay(loaderDiv);
            },
            success: function(response) {
                if (response.success) {
                    let data = response.data;
                    $('#wpadpcbu-products-row').html(data.markup);
                    updatePageData(data);
                } else {
                    let data = response.data;
                    if (data.type == 'pcbucomp') {
                        window.location.href = wpadpcbu_public.builder;
                    }
                }
                loaderDisplay(loaderDiv, 'hide');
            }
        });
    }

    let updatePageData = function(queryData) {
        let totalItems = queryData.total_items;
        let totalPages = queryData.total_pages;
        let currentPage = queryData.current_page;
        let parPage = queryData.per_page;

        $('.pagination-button').hide();

        if (totalPages > currentPage) {
            $('#next-btn').show();
        }

        if (currentPage > 1 && currentPage <= totalPages) {
            $('#previous-btn').show();
        }

        if (totalPages == 1) {
            $('.wpadpcbu-pagination').addClass('no-pagination');
        }

        $('span.total-products').text(totalItems);
        $('span.total-pages').text(totalPages);
        $('span.current-page').text(currentPage);

        // Update hidden input field data
        $('input[name="total_items"]').val(totalItems);
        $('input[name="total_pages"]').val(totalPages);
        $('input[name="per_page"]').val(parPage);
        $('input[name="current_page"]').val(currentPage);
    }

    let priceRangeFilter = function(startPrice, endPrice) {
        clearTimeout(timeout);
        let ajaxData = {
            action: 'filter_component_product',
            pcbucomp: wpadpcbu_public.pcbucomp,
            taxFilters: taxFiltersData(),
            searchFilter: getSearchData(),
            sortByFilter: getSortByData(),
            priceStart: startPrice,
            priceEnd: endPrice,
            _nonce: wpadpcbu_public.nonce
        };

        timeout = setTimeout(function() {
            productsFilterAjaxCall(ajaxData);
        }, 1000);
    }

    let copyShareLink = function(linkUrl) {
        // var textToCopy = $('#text-to-copy').text();
        // var tempTextarea = $('<textarea>');
        // $('body').append(tempTextarea);
        // tempTextarea.val(linkUrl).select();
        navigator.clipboard.writeText(linkUrl);
        // tempTextarea.remove();
    }

    // PC builder page js code
    jQuery(document).ready(function($) {
        // Search button.
        $('.wpadpcbu-search-product').on('click', function(e) {
            e.preventDefault();
            let componentId = jQuery(this).data('componentid');
            window.location.assign(wpadpcbu_public.search + '?pcbucomp=' + componentId);
        });

        // Remove button.
        $('.wpadpcbu-remove-product').on('click', function(e) {
            e.preventDefault();
            let componentId = $(this).data('componentid');
            let componentTr = "#componentid-" + componentId;

            var data = {
                action: 'remove_component_product',
                componentId: componentId,
                _nonce: wpadpcbu_public.nonce
            };

            $.ajax(wpadpcbu_public.ajaxurl, {
                method: 'post',
                data: data,
                beforeSend: function() {
                    loaderDisplay(componentTr);
                },
                success: function(response) {
                    if (response.success) {
                        //removePcBuilderComponent(componentId);
                        location.reload();
                    } else {
                        console.debug(response);
                    }
                    loaderDisplay(componentTr, 'hide');
                }
            });
        });

        // Add to cart button.
        $('.wpadpcbu-product-cart').on('click', function(e) {
            e.preventDefault();
            let tableDiv = ".wpadpcbu-component-table table";
            let data = {
                action: 'add_components_product_to_cart',
                _nonce: wpadpcbu_public.nonce
            };

            $.ajax(wpadpcbu_public.ajaxurl, {
                method: 'post',
                data: data,
                beforeSend: function() {
                    loaderDisplay(tableDiv);
                },
                success: function(response) {
                    if (response.success) {
                        $(document.body).trigger('wc_fragment_refresh');
                        if (response.data.redirect) {
                            window.location.assign(response.data.url);
                        }
                    } else {
                        var rData = response.data;
                        if ('missing-component' == rData.type) {
                            var ids = rData.missing;
                            ids.forEach(id => {
                                $('#componentid-' + id + ' .required-span').addClass('missing');
                            });
                        }
                        alert(response.data.message);
                    }
                    loaderDisplay(tableDiv, 'hide');
                }
            });
        });

        // Save button.
        $('.wpadpcbu-save').on('click', function(e) {
            e.preventDefault();
            let tableDiv = ".wpadpcbu-component-table table";
            let data = {
                action: 'pcbuilder_configuration_save',
                _nonce: wpadpcbu_public.nonce
            };

            $.ajax(wpadpcbu_public.ajaxurl, {
                method: 'post',
                data: data,
                beforeSend: function() {
                    loaderDisplay(tableDiv);
                },
                success: function(response) {
                    if (response.success) {
                        window.location.assign(response.data.url);
                    } else {
                        var rData = response.data;
                        if ('missing-component' == rData.type) {
                            var ids = rData.missing;
                            ids.forEach(id => {
                                $('#componentid-' + id + ' .required-span').addClass('missing');
                            });
                        }
                        alert(rData.message);
                    }
                    loaderDisplay(tableDiv, 'hide');
                }
            });
        });

        // Generate share link.
        $('.wpadpcbu-share-build').on('click', function(e) {
            e.preventDefault();
            let tableDiv = this.closest('.wpadpcbu-saved-config');
            let buildId = $(this).data('id');
            let data = {
                action: 'generate_share_link',
                buildId: buildId,
                _nonce: wpadpcbu_public.nonce
            };

            $.ajax(wpadpcbu_public.ajaxurl, {
                method: 'post',
                data: data,
                beforeSend: function() {
                    loaderDisplay(tableDiv);
                },
                success: function(response) {
                    if (response.success) {
                        copyShareLink(response.data);
                        alert('Copied Share Link.')
                    } else {

                    }
                    loaderDisplay(tableDiv, 'hide');
                }
            });
        });

        $('.wpadpcbu-copy-link').on('click', function(e) {
            e.preventDefault();
            let linkUrl = $(this).data('link');
            copyShareLink(linkUrl);
            alert('Copied Share Link.')
        });

        // Remove saved configuration button.
        $('.wpadpcbu-remove-configuration').on('click', function(e) {
            e.preventDefault();
            let pcId = $(this).data('id');

            var data = {
                action: 'remove_configuration',
                pcId: pcId,
                _nonce: wpadpcbu_public.nonce
            };

            $.ajax(wpadpcbu_public.ajaxurl, {
                method: 'post',
                data: data,
                beforeSend: function() {
                    loaderDisplay('.my_account_saved-configurations');
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        console.debug(response);
                    }
                    loaderDisplay('.my_account_saved-configurations', 'hide');
                }
            });
        });

        // Screenshots button.
        $('.wpadpcbu-screenshots').on('click', function(e) {
            e.preventDefault();

            var node = document.getElementById('wpadpcbu-component');
            var time = new Date();
            var fileName = 'Build Configuration ' + time.toDateString() + ' ' + time.toLocaleTimeString();

            domtoimage.toJpeg(node)
                .then(function(dataUrl) {
                    var link = document.createElement('a');
                    link.download = fileName + '.jpeg';
                    link.href = dataUrl;
                    link.click();
                });
        });
    });

    // Search page js code.
    jQuery(document).ready(function($) {
        // Breadcrumb menu link.
        $('.bclink').on('click', function(e) {
            e.preventDefault();
            let page = jQuery(this).data('page');
            if (page == 'builder') {
                window.location.assign(wpadpcbu_public.builder);
            }
            if (page == 'search') {
                location.reload();
            }
        });

        // Filter toggle button.
        $('#filter-hide').on('click', function(e) {
            e.preventDefault();
            $(this).hide();
            $('#wpadpcbu-search-filter').hide();
            $('#filter-show').show();
        });

        $('#filter-show').on('click', function(e) {
            e.preventDefault();
            $(this).hide();
            $('#wpadpcbu-search-filter').show();
            $('#filter-hide').show();
        });

        // Back button.
        $('.back-button').on('click', function(e) {
            e.preventDefault();
            window.location.assign(wpadpcbu_public.builder);
        });

        // Hide Un-configured component toggle button.
        $('.wpadpcbu-hidden-component').on('click', function(e) {
            e.preventDefault();
            $(this).hide();
            $('.wpadpcbu-show-component').attr( "style", "display: flex !important;" );
            $('tr.fixed-height').hide();
        });
        $('.wpadpcbu-show-component').on('click', function(e) {
            e.preventDefault();
            $(this).hide();
            $('.wpadpcbu-hidden-component').show();
            $('tr.fixed-height').show();
        });

        // Filter toggle button
        $('.filter-group .toggler').on('click', function(e) {
            e.preventDefault();
            let group = jQuery(this).data('group');
            $('#fgp-' + group).toggleClass("show");
        });

        $("body").on('click', '.wpadpcbu-actions .choose', function(e) {
            e.preventDefault();
            let componentId = jQuery(this).data('componentid');
            let productId = jQuery(this).data('productid');
            let productDiv = "#component-product-" + productId;

            var data = {
                action: 'add_component_product',
                componentId: componentId,
                productId: productId,
                _nonce: wpadpcbu_public.nonce
            };

            $.ajax(wpadpcbu_public.ajaxurl, {
                method: 'post',
                data: data,
                beforeSend: function() {
                    loaderDisplay(productDiv);
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = wpadpcbu_public.builder;
                    } else {
                        console.debug(response);
                    }
                    loaderDisplay(productDiv, 'hide');
                }
            });
        });

        // Component filters.
        $("input[type='checkbox']").click(function() {
            clearTimeout(timeout);
            let data = {
                action: 'filter_component_product',
                pcbucomp: wpadpcbu_public.pcbucomp,
                taxFilters: taxFiltersData(),
                searchFilter: getSearchData(),
                sortByFilter: getSortByData(),
                priceStart: priceStart(),
                priceEnd: priceEnd(),
                _nonce: wpadpcbu_public.nonce
            };

            timeout = setTimeout(function() {
                productsFilterAjaxCall(data);
            }, 1000);
        });

        // Sort by filter.
        $('#input-sort').on('change', function(e) {
            e.preventDefault();
            clearTimeout(timeout);
            var sortByFilter = $(this).val();
            var data = {
                action: 'filter_component_product',
                pcbucomp: wpadpcbu_public.pcbucomp,
                taxFilters: taxFiltersData(),
                searchFilter: getSearchData(),
                sortByFilter: sortByFilter,
                priceStart: priceStart(),
                priceEnd: priceEnd(),
                _nonce: wpadpcbu_public.nonce
            };

            timeout = setTimeout(function() {
                productsFilterAjaxCall(data);
            }, 1000);
        });

        // Text search filter.
        $("#input-search").keyup(function(e) {
            e.preventDefault();
            clearTimeout(timeout);
            let searchFilter = $(this).val();
            var data = {
                action: 'filter_component_product',
                pcbucomp: wpadpcbu_public.pcbucomp,
                taxFilters: taxFiltersData(),
                searchFilter: searchFilter,
                sortByFilter: getSortByData(),
                priceStart: priceStart(),
                priceEnd: priceEnd(),
                _nonce: wpadpcbu_public.nonce
            };

            timeout = setTimeout(function() {
                productsFilterAjaxCall(data);
            }, 1000);
        });

        $('.pagination-button').on('click', function(e) {
            e.preventDefault();
            //clearTimeout(timeout);
            let pagination = jQuery(this).data('pagination');
            let currentPage = $('input[name="current_page"]').val();
            let paged = currentPage;
            if ('next' == pagination) {
                paged = parseInt(currentPage) + 1;
            }
            if ('prev' == pagination) {
                paged = parseInt(currentPage) - 1;
            }
            var data = {
                action: 'filter_component_product',
                pcbucomp: wpadpcbu_public.pcbucomp,
                paged: paged,
                taxFilters: taxFiltersData(),
                searchFilter: getSearchData(),
                sortByFilter: getSortByData(),
                priceStart: priceStart(),
                priceEnd: priceEnd(),
                _nonce: wpadpcbu_public.nonce
            };
            productsFilterAjaxCall(data);
        });
    });

    jQuery(document).ready(function($) {
        var $range = $(".wpadpcbu-price-range"),
            $inputFrom = $(".wpadpcbu-price-start"),
            $inputTo = $(".wpadpcbu-price-end"),
            instance,
            min = wpadpcbu_public.start_range,
            max = wpadpcbu_public.end_range,
            from = 0,
            to = 0;

        $range.ionRangeSlider({
            skin: "round",
            type: "double",
            min: min,
            max: max,
            from: min,
            to: max,
            onStart: updateInputs,
            onChange: updateInputs
        });
        instance = $range.data("ionRangeSlider");

        function updateInputs(data) {
            from = data.from;
            to = data.to;

            $inputFrom.prop("value", from);
            $inputTo.prop("value", to);

            priceRangeFilter(from, to);
        }

        $inputFrom.on("input", function() {
            var val = $(this).prop("value");

            // validate
            if (val < min) {
                val = min;
            } else if (val > to) {
                val = to;
            }

            instance.update({
                from: val
            });

            if (val != '') {
                priceRangeFilter(val, priceEnd())
            }

        });

        $inputTo.on("input", function() {
            var val = $(this).prop("value");

            // validate
            if (val < from) {
                val = from;
            } else if (val > max) {
                val = max;
            }

            instance.update({
                to: val
            });

            if (val != '') {
                priceRangeFilter(priceStart(), val);
            }
        });
    });

})(jQuery);
// wpadpcbu-search-product
