(function () {
    'use strict';

    $.widget('mageQuickView','catalogAddToCart', {
        component: 'Magekc_QuickView/js/quick-view', // an alias that's used in the `data-mage-init` instructions.
        defaults: {
            template: 'Magekc_QuickView/component' // see "Knockout templates" section below
        },
        options: {
            itemClass: '.products-grid .product-item, .products-list .product-item',
            quickviewLabel: 'Quick View',
            handlerClass: 'btn-quickview',
            baseUrl: '/',
            autoAddButtons: true,
            target: '.product-item-info .actions-secondary',
            template: 'Magekc_QuickView/component',
            galleryQvClass: '.b-quickview-gallery'
        },
        create: function () {
            this._buildQuickView(this.options);
        },
        _addButton: function(config) {
            if (config.autoAddButtons) {
                $(config.itemClass).each(function() {
                    var $elem = $(this);
                    if ($elem.find('.' + config.handlerClass).length == 0) {
                        var productId = $elem.find('.price-final_price').data('product-id');
                        var url = $.breeze.url.build('quickview/view/index/id/' + productId);
                        var html = '<a class="' + config.handlerClass + '" rel="nofollow" href="javascript:void(0)" data-mfp-src="' + url + '"><span>';
                        html += config.quickviewLabel;
                        html += '</span></a>';
                        $elem.find(config.target).prepend(html);
                    }
                });
            }
        },
        _ajaxQuickViewHtml: function(_url) {
            $.request.get({
                url: _url,
                type: 'html'
            }).then(function (response) {
                $('.quickview-popup')
                    .html(response.text);
            });
        },
        _buildQuickView: function(config) {
            var self = this;
            this._addButton(config);
            var $qs_button = $('.' + config.handlerClass);
            $qs_button.on('click', function(){
                var _urlTemplate = $(this).data("mfp-src");
                self._ajaxQuickViewHtml(_urlTemplate);
                $('.quickview-popup').modal({
                    type: 'popup', // Choose between popup and slide modals
                    modalClass: 'quick-view-popup', // Additional CSS class
                    focus: '[data-role="closeBtn"]', // Initially focused element
                    autoOpen: false, // Indicates if modal should be opened right after initialization
                    appendTo: 'body', // Where to add modal in document markup
                    trigger: '', // Element's selector that should trigger modal
                    modalLeftMargin: 45, // Offset for nested slide modals
                    closeText: $t('Close'), // Label for close button
                    buttons: false
                });
                $('.quickview-popup').modal('openModal');
                
                $('.quickview-popup').on('modal:opened', (e, data) => {
                    var _qvParentClass = self.options.galleryQvClass;
                    var _galleryItems = _qvParentClass+ ' .thumbnails .item';
                    var _galleryMainItem = _qvParentClass+ ' .stage .main-image-wrapper';
                    if (_galleryItems.length > 0) {
                        $(document).on('click', _qvParentClass+ ' .thumbnails .item', function (event) {
                            event.preventDefault();
                            $(_qvParentClass+ ' .thumbnails .item').removeClass('active');
                            $(this).addClass('active');
                            var _imgClick = $(this).attr('href');
                            var _imgClickSrcSet = _imgClick+' 512w,' +_imgClick+' 549w,' +_imgClick+' 608w,' +_imgClick+' 704w,' +_imgClick+' 960w,' +_imgClick+' 1216w';
                            $(_galleryMainItem).find('img').attr('src',_imgClick);
                            $(_galleryMainItem).find('img').attr('srcset',_imgClickSrcSet);
                        });
                    }
                });
            });

            $(document).on('submit', '.quickview-addtocart-form', function (event) {
                event.preventDefault();
                self.ajaxSubmit($(this));
                
                setTimeout(function() { 
                    $('.quickview-popup').modal('closeModal');
                }, 1000)
                
            });
        },
        
    });
})();