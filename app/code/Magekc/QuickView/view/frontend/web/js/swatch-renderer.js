define([
    'jquery',
    'underscore',
        'jquery/ui',
    'jquery/jquery.parsequery',

], function ($, _) {
    'use strict';

    $.widget('vendor.customSwatchRendererTooltip', {
        options: {
            delay: 200,                             //how much ms before tooltip to show
            tooltipClass: 'swatch-option-tooltip'  //configurable, but remember about css
        },

        /**
         * @private
         */
        _init: function () {
            var $widget = this,
                $this = this.element,
                $element = $('.' + $widget.options.tooltipClass),
                timer,
                type = parseInt($this.attr('option-type'), 10),
                label = $this.attr('option-label'),
                thumb = $this.attr('option-tooltip-thumb'),
                value = $this.attr('option-tooltip-value'),
                $image,
                $title,
                $corner;

            if (!$element.length) {
                $element = $('<div class="' +
                    $widget.options.tooltipClass +
                    '"><div class="image"></div><div class="title"></div><div class="corner"></div></div>'
                );
                $('body').append($element);
            }

            $image = $element.find('.image');
            $title = $element.find('.title');
            $corner = $element.find('.corner');

            $this.hover(function () {
                if (!$this.hasClass('disabled')) {
                    timer = setTimeout(
                        function () {
                            var leftOpt = null,
                                leftCorner = 0,
                                left,
                                $window;

                            if (type === 2) {
                                // Image
                                $image.css({
                                    'background': 'url("' + thumb + '") no-repeat center', //Background case
                                    'background-size': 'initial'
                                });
                                $image.show();
                            } else if (type === 1) {
                                // Color
                                $image.css({
                                    background: value
                                });
                                $image.show();
                            } else if (type === 0 || type === 3) {
                                // Default
                                $image.hide();
                            }

                            $title.text(label);

                            leftOpt = $this.offset().left;
                            left = leftOpt + $this.width() / 2 - $element.width() / 2;
                            $window = $(window);

                            // the numbers (5 and 5) is magick constants for offset from left or right page
                            if (left < 0) {
                                left = 5;
                            } else if (left + $element.width() > $window.width()) {
                                left = $window.width() - $element.width() - 5;
                            }

                            // the numbers (6,  3 and 18) is magick constants for offset tooltip
                            leftCorner = 0;

                            if ($element.width() < $this.width()) {
                                leftCorner = $element.width() / 2 - 3;
                            } else {
                                leftCorner = (leftOpt > left ? leftOpt - left : left - leftOpt) + $this.width() / 2 - 6;
                            }

                            $corner.css({
                                left: leftCorner
                            });
                            $element.css({
                                left: left,
                                top: $this.offset().top - $element.height() - $corner.height() - 18
                            }).show();
                        },
                        $widget.options.delay
                    );
                }
            }, function () {
                $element.hide();
                clearTimeout(timer);
            });

            $(document).on('tap', function () {
                $element.hide();
                clearTimeout(timer);
            });

            $this.on('tap', function (event) {
                event.stopPropagation();
            });
        }
    });


    $.widget('vendor.customSwatchRenderer', {
        options: {
            classes: {
                attributeClass: 'swatch-attribute',
                attributeLabelClass: 'swatch-attribute-label',
                attributeSelectedOptionLabelClass: 'swatch-attribute-selected-option',
                attributeOptionsWrapper: 'swatch-attribute-options',
                attributeInput: 'swatch-input',
                optionClass: 'swatch-option',
                selectClass: 'swatch-select',
                moreButton: 'swatch-more',
            },
            // option's json config
            customjsonConfig: {},

            // swatch's json config
            customjsonSwatchConfig: {},

            // number of controls to show (false or zero = show all)
            numberToShow: false,

            // show only swatch controls
            onlySwatches: false,

            // enable label for control
            enableControlLabel: true,

            // text for more button
            moreButtonText: 'More',
        },

        _init: function () {
            if (this.options.customjsonConfig !== '' && this.options.customjsonSwatchConfig !== '') {
                this._sortAttributes();
                this._RenderControls();
            } else {
                console.log('SwatchRenderer: No input data received');
            }
        },

        _sortAttributes: function () {
            this.options.customjsonConfig.attributes = _.sortBy(this.options.customjsonConfig.attributes, function (attribute) {
                return attribute.position;
            });
        },

        _create: function () {
            var options = this.options,
                gallery = $('[data-gallery-role=gallery-placeholder]', '.column.main'),
                isProductViewExist = $('body.catalog-product-view').length > 0,
                $main = isProductViewExist ?
                    this.element.parents('.column.main') :
                    this.element.parents('.product-item-info');
        },

        _RenderControls: function () {
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                chooseText = this.options.customjsonConfig.chooseText;

            $widget.optionsMap = {};

            $.each(this.options.customjsonConfig.attributes, function () {
                var item = this,
                    productId = $widget.options.customjsonConfig.productId,
                    options = $widget._RenderSwatchOptions(item),
                    select = $widget._RenderSwatchSelect(item, chooseText),
                    input = $widget._RenderFormInput(item, productId),
                    label = '';

                // Show only swatch controls
                if ($widget.options.onlySwatches && !$widget.options.customjsonSwatchConfig.hasOwnProperty(item.id)) {
                    return;
                }

                if ($widget.options.enableControlLabel) {
                    label +=
                        '<span class="' + classes.attributeLabelClass + '">' + item.label + '</span>' +
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>';
                }

                // Create new control
                container.append(
                    '<div class="' + classes.attributeClass + ' ' + item.code +
                    '" attribute-code="' + item.code +
                    '" attribute-id="' + item.id + '">' +
                    label +
                    '<div class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                    options + select +
                    '</div>' + input +
                    '</div>'
                );

                $widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        $widget.optionsMap[item.id][this.id] = {
                            price: parseInt(
                                $widget.options.customjsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                10
                            ),
                            products: this.products
                        };
                    }
                });
            });

            // Connect Tooltip
            container
                .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
                .customSwatchRendererTooltip();

            // Hide all elements below more button
            $('.' + classes.moreButton).nextAll().hide();

            // Handle events like click or change
            $widget._EventListener();

            // Rewind options
            $widget._Rewind(container);
        },

        _RenderSwatchOptions: function (config) {
            var optionConfig = this.options.customjsonSwatchConfig[config.id],
                optionClass = this.options.classes.optionClass,
                moreLimit = parseInt(this.options.numberToShow, 10),
                moreClass = this.options.classes.moreButton,
                moreText = this.options.moreButtonText,
                countAttributes = 0,
                html = '';

            if (!this.options.customjsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            $.each(config.options, function () {
                var id,
                    type,
                    value,
                    thumb,
                    label,
                    attr;

                if (!optionConfig.hasOwnProperty(this.id)) {
                    return '';
                }

                // Add more button
                if (moreLimit === countAttributes++) {
                    html += '<a href="#" class="' + moreClass + '">' + moreText + '</a>';
                }

                id = this.id;
                type = parseInt(optionConfig[id].type, 10);
                value = optionConfig[id].hasOwnProperty('value') ? optionConfig[id].value : '';
                thumb = optionConfig[id].hasOwnProperty('thumb') ? optionConfig[id].thumb : '';
                label = this.label ? this.label : '';
                attr =
                    ' option-type="' + type + '"' +
                    ' option-id="' + id + '"' +
                    ' option-label="' + label + '"' +
                    ' option-tooltip-thumb="' + thumb + '"' +
                    ' option-tooltip-value="' + value + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                if (type === 0) {
                    // Text
                    html += '<div class="' + optionClass + ' text" ' + attr + '>' + (value ? value : label) +
                        '</div>';
                } else if (type === 1) {
                    // Color
                    html += '<div class="' + optionClass + ' color" ' + attr +
                        '" style="background: ' + value +
                        ' no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 2) {
                    // Image
                    html += '<div class="' + optionClass + ' image" ' + attr +
                        '" style="background: url(' + value + ') no-repeat center; background-size: initial;">' + '' +
                        '</div>';
                } else if (type === 3) {
                    // Clear
                    html += '<div class="' + optionClass + '" ' + attr + '></div>';
                } else {
                    // Defaualt
                    html += '<div class="' + optionClass + '" ' + attr + '>' + label + '</div>';
                }
            });

            return html;
        },

        _RenderSwatchSelect: function (config, chooseText) {
            var html;

            if (this.options.customjsonSwatchConfig.hasOwnProperty(config.id)) {
                return '';
            }

            html =
                '<select class="' + this.options.classes.selectClass + ' ' + config.code + '">' +
                '<option value="0" option-id="0">' + chooseText + '</option>';

            $.each(config.options, function () {
                var label = this.label,
                    attr = ' value="' + this.id + '" option-id="' + this.id + '"';

                if (!this.hasOwnProperty('products') || this.products.length <= 0) {
                    attr += ' option-empty="true"';
                }

                html += '<option ' + attr + '>' + label + '</option>';
            });

            html += '</select>';

            return html;
        },

        _RenderFormInput: function (config, productId) {
            return '<input class="' + this.options.classes.attributeInput + ' super-attribute-select" ' +
                'name="super_attribute[' + config.id + ']" ' +
                'type="text" ' +
                'value="" ' +
                'data-selector="super_attribute[' + config.id + ']" ' +
                'data-validate="{required:true}" ' +
                'aria-required="true" ' +
                'aria-invalid="true" ' +
                'style="visibility: hidden; position:absolute; left:-1000px">';
        },

        _EventListener: function () {

            var $widget = this;

            $widget.element.on('click', '.' + this.options.classes.optionClass, function () {
                return $widget._OnClick($(this), $widget);
            });

            $widget.element.on('change', '.' + this.options.classes.selectClass, function () {
                return $widget._OnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + this.options.classes.moreButton, function (e) {
                e.preventDefault();

                return $widget._OnMoreClick($(this));
            });
        },

        _OnClick: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
            } else {
                $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                $label.text($this.attr('option-label'));
                $input.val($this.attr('option-id'));
                $this.addClass('selected');
            }

            $widget._Rebuild();

            if ($widget.element.closest('li').find('.item-price').length) {
                $widget._UpdatePrice();
            }
            $input.trigger('change');
        },

        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($this.val() > 0) {
                $parent.attr('option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('option-selected');
                $input.val('');
            }

            $widget._Rebuild();
            $widget._UpdatePrice();
            $input.trigger('change');
        },

        _OnMoreClick: function ($this) {
            $this.nextAll().show();
            $this.blur().remove();
        },

        _Rewind: function (controls) {
            controls.find('div[option-id], option[option-id]').removeClass('disabled').removeAttr('disabled');
            controls.find('div[option-empty], option[option-empty]').attr('disabled', true).addClass('disabled');
        },

        _Rebuild: function () {

            var $widget = this,
                controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[attribute-id]'),
                selected = controls.filter('[option-selected]');

            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.length <= 0) {
                return;
            }

            // Disable not available options
            controls.each(function () {
                var $this = $(this),
                    id = $this.attr('attribute-id'),
                    products = $widget._CalcProducts(id);

                if (selected.length === 1 && selected.first().attr('attribute-id') === id) {
                    return;
                }

                $this.find('[option-id]').each(function () {
                    var $element = $(this),
                        option = $element.attr('option-id');

                    if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                        $element.hasClass('selected') ||
                        $element.is(':selected')) {
                        return;
                    }

                    if (_.intersection(products, $widget.optionsMap[id][option].products).length <= 0) {
                        $element.attr('disabled', true).addClass('disabled');
                    }
                });
            });
        },

        _CalcProducts: function ($skipAttributeId) {
            var $widget = this,
                products = [];

            // Generate intersection of products
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var id = $(this).attr('attribute-id'),
                    option = $(this).attr('option-selected');

                if ($skipAttributeId !== undefined && $skipAttributeId === id) {
                    return;
                }

                if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option)) {
                    return;
                }

                if (products.length === 0) {
                    products = $widget.optionsMap[id][option].products;
                } else {
                    products = _.intersection(products, $widget.optionsMap[id][option].products);
                }
            });

            return products;
        },

        _UpdatePrice: function () {
            var $widget = this,
                $product = $widget.element.closest('li'),
                options = _.object(_.keys($widget.optionsMap), {}),
                result;

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');

                options[attributeId] = $(this).attr('option-selected');
            });

            result = $widget.options.customjsonConfig.optionPrices[_.findKey($widget.options.customjsonConfig.index, options)];

            if (result) {
                $product.find('.item-price').attr('data-price-amount', result.finalPrice.amount);
                $product.find('.related-checkbox').attr('data-price-amount', result.finalPrice.amount).change();
            }
        }
    });

    return $.vendor.customSwatchRenderer;
});