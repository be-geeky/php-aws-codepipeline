/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * https://www.wyomind.com
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'underscore',
    'mage/template',
    'jquery/ui',
    'mage/translate'
], function ($, _, mageTemplate) {
    'use strict';


    $.widget('mage.wyoSearch', {

        isEmpty : function(value) {
            return (value.length === 0) || (value == null) || /^\s+$/.test(value);
        },
        options: {
            autocomplete: 'off',
            minSearchLength: 1,
            responseFieldElements: 'ul li.qs-option',
            selectClass: 'selected',
            templateSelectors: {
                results: '#wyomind-tmpl-results'
            },
            submitBtn: 'button[type="submit"]',
            searchLabel: '[data-role=minisearch-label]'
        },
        _create: function () {
            this.responseList = {
                indexList: null,
                currentIndex: null
            };
            this.autoComplete = $(this.options.destinationSelector);
            this.searchForm = $(this.options.formSelector);
            this.submitBtn = this.searchForm.find(this.options.submitBtn)[0];
            this.searchLabel = $(this.options.searchLabel);

            _.bindAll(this, '_onKeyDown', '_onPropertyChange');

            this.submitBtn.disabled = true;

            this.element.attr('autocomplete', this.options.autocomplete);

            this.element.on('blur', $.proxy(function () {
                if (this.searchLabel.hasClass('active')) {
                    setTimeout($.proxy(function () {
                        this.searchLabel.removeClass('active');
                        this.autoComplete.hide();
                        this._updateAriaHasPopup(false);
                    }, this), 250);
                }
            }, this));

            this.element.trigger('blur');

            this.element.on('focus', $.proxy(function () {
                this.searchLabel.addClass('active');
            }, this));
            this.element.on('keydown', this._onKeyDown);
            this.element.on('input propertychange', this._onPropertyChange);

            this.searchForm.on('submit', $.proxy(function () {
                if ('true' === this.element.attr('aria-haspopup') && null !== this.responseList.currentIndex) {
                    return false;
                }
                this._updateAriaHasPopup(false);
            }, this));
        },
        /**
         * @private
         * @return {Element} The current element in the suggestion list.
         */
        _current: function () {
            return this.responseList.indexList
                    ? $(this.responseList.indexList[this.responseList.currentIndex])
                    : null;
        },
        /**
         * @private
         */
        _updateCurrent: function () {
            var selectClass = this.options.selectClass;
            this.responseList.indexList.removeClass(selectClass);
            var current = this._current();
            if (current) {
                current.addClass(selectClass);
                this.autoComplete.show();
            }
        },
        /**
         * @private
         */
        _previous: function () {
            if (this.responseList.indexList) {
                var currentIndex = this.responseList.currentIndex;
                var listLength = this.responseList.indexList.length;
                if (--currentIndex < 0) {
                    currentIndex = listLength - 1;
                }
                this.responseList.currentIndex = currentIndex;
                this._updateCurrent();
            }
        },
        /**
         * @private
         */
        _next: function () {
            if (this.responseList.indexList) {
                var currentIndex = this.responseList.currentIndex;
                var listLength = this.responseList.indexList.length;
                if (null === currentIndex || ++currentIndex >= listLength) {
                    currentIndex = 0;
                }
                this.responseList.currentIndex = currentIndex;
                this._updateCurrent();
            }
        },
        /**
         * @private
         */
        _first: function () {
            if (this.responseList.indexList) {
                this.responseList.currentIndex = 0;
                this._updateCurrent();
            }
        },
        /**
         * @private
         */
        _last: function () {
            if (this.responseList.indexList) {
                var listLength = this.responseList.indexList.length;
                this.responseList.currentIndex = listLength - 1;
                this._updateCurrent();
            }
        },
        /**
         * @private
         * @param {Boolean} show Set attribute aria-haspopup to "true/false" for element.
         */
        _updateAriaHasPopup: function (show) {
            if (show) {
                this.element.attr('aria-haspopup', 'true');
            } else {
                this.element.attr('aria-haspopup', 'false');
            }
        },
        /**
         * Clears the item selected from the suggestion list and resets the suggestion list.
         * @private
         */
        _resetResponseList: function () {
            this.responseList.indexList = null;
            this.responseList.currentIndex = null;
        },
        /**
         * Executes when keys are pressed in the search input field. Performs specific actions
         * depending on which keys are pressed.
         * @private
         * @param {Event} e - The key down event
         * @return {Boolean} Default return type for any unhandled keys
         */
        _onKeyDown: function (e) {
            var keyCode = e.keyCode || e.which;

            switch (keyCode) {
                case $.ui.keyCode.HOME:
                    this._first();
                    break;
                case $.ui.keyCode.END:
                    this._last();
                    break;
                case $.ui.keyCode.ESCAPE:
                    this.autoComplete.hide();
                    break;
                case $.ui.keyCode.ENTER:
                    var current = this._current();
                    if (current) {
                        var links = current.find('a');
                        if (links.length) {
                            location.href = links[0].href;
                            this.autoComplete.hide();
                        }
                    } else {
                        this.searchForm.trigger('submit');
                    }
                    break;
                case $.ui.keyCode.DOWN:
                    this._next();
                    break;
                case $.ui.keyCode.UP:
                    this._previous();
                    break;
                default:
                    if (this.element.val() != "") {
                        this.autoComplete.show();
                    }
                    return true;
            }
        },
        /**
         * Executes when the value of the search input field changes. Executes a GET request
         * to populate a suggestion list based on entered text. Handles click (select), hover,
         * and mouseout events on the populated suggestion list dropdown.
         * @private
         */
        _onPropertyChange: function () {
            var searchField = this.element,
                    clonePosition = {
                        position: 'absolute',
                        // Removed to fix display issues
                        // left: searchField.offset().left,
                        // top: searchField.offset().top + searchField.outerHeight(),
                        width: searchField.outerWidth()
                    },
                    templateSelectors = this.options.templateSelectors,
                    dropdown = $('<ul role="listbox" class="wyomind"></ul>'),
                    value = this.element.val();

            this.submitBtn.disabled = this.isEmpty(value);

            if (value.length >= parseInt(this.options.minSearchLength, 10)) {
                if (this.xhr != undefined) {
                    this.xhr.abort();
                }
                this.xhr = $.get(this.options.url, {q: value}, $.proxy(function (data) {
                    if (value == $('#search').val()) {
                        if (typeof data.suggests == "undefined") {
                            data.suggests = {"enabled":false, "count":0,"docs":{}};
                        }
                        if (typeof data.product == "undefined") {
                            data.product = {"enabled":false, "count":0,"docs":{}};
                        }
                        if (typeof data.category == "undefined") {
                            data.category = {"enabled":false, "count":0,"docs":{}};
                        }
                        if (typeof data.cms == "undefined") {
                            data.cms = {"enabled":false, "count":0,"docs":{}};
                        }
                        var html = mageTemplate(templateSelectors['results'], {
                            suggests: data.suggests,
                            products: data.product,
                            categories: data.category,
                            cms: data.cms,
                            search_term: value
                        });

                        if (typeof data.error != "undefined") {
                            var errorMsg = "";
                            $.each(data.error, function (i, val) {
                                errorMsg += "<li class='qs-option'>" + val + "</li>";
                            });
                            html = "<li class='qs-section'>Error</li>" + errorMsg;
                        }

                        dropdown.append(html);

                        this._resetResponseList();

                        if (data.suggests.count == 0 && data.product.count == 0 && data.category.count == 0 && data.cms.count == 0) {
                            this.autoComplete.addClass('auto-size');
                        } else if (data.suggests.count != 0 && data.product.count == 0 && data.category.count == 0 && data.cms.count == 0) {
                            this.autoComplete.addClass('auto-size');
                        } else {
                            this.autoComplete.removeClass('auto-size');
                        }

                        this.responseList.indexList = this.autoComplete.html(dropdown)
                                .css(clonePosition)
                                .show()
                                .find(this.options.responseFieldElements + ':visible');

                        if (this.responseList.indexList.length) {
                            this._updateAriaHasPopup(true);
                        } else {
                            this._updateAriaHasPopup(false);
                        }

                        this.responseList.indexList
                                .on('click', function (e) {
                                    //this.searchForm.trigger('submit');
                                }.bind(this))
                                .on('mouseenter mouseleave', function (e) {
                                    this.responseList.indexList.removeClass(this.options.selectClass);
                                    $(e.target).addClass(this.options.selectClass);
                                }.bind(this));
                    }
                }, this));
            } else {
                this._resetResponseList();
                this.autoComplete.hide();
                this._updateAriaHasPopup(false);
            }
        }
    });

    return $.mage.wyoSearch;
});
