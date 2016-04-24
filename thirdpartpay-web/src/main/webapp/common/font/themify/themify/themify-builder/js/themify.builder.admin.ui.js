;
var ThemifyPageBuilder, ThemifyBuilderCommon;
(function ($, window, document, undefined) {

    'use strict';

    // Serialize Object Function
    if ('undefined' === typeof $.fn.serializeObject) {
        $.fn.serializeObject = function () {
            var o = {};
            var a = this.serializeArray();
            $.each(a, function () {
                if (o[this.name] !== undefined) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };
    }

    // Builder Function
    ThemifyPageBuilder = {
        clearClass: 'col6-1 col5-1 col4-1 col4-2 col4-3 col3-1 col3-2 col2-1 col-full',
        gridClass: ['col-full', 'col4-1', 'col4-2', 'col4-3', 'col3-1', 'col3-2', 'col6-1', 'col5-1'],
        builderContainer: document.querySelector('.themify_builder_editor_wrapper'),
        eventHandler: {
            click: 'true' == themifyBuilder.isTouch ? 'touchend' : 'click',
            hover: 'true' == themifyBuilder.isTouch ? 'touchend' : 'mouseenter mouseleave'
        },
        init: function () {
            this.tfb_hidden_editor_object = tinyMCEPreInit.mceInit['tfb_lb_hidden_editor'];
            this.preLoader = $('<div/>', {id: 'themify_builder_loader'});
            this.alertLoader = $('<div/>', {id: 'themify_builder_alert', class: 'alert'});

            // auto save metabox when save post
            this.isPostSave = false;

            this.bindEvents();
            this.moduleEvents();

            ThemifyBuilderCommon.Lightbox.setup();
            ThemifyBuilderCommon.LiteLightbox.modal.on('attach', function(){
                this.$el.addClass('themify_builder_lite_lightbox_modal');
            });

            this.setupTooltips();
            this.mediaUploader();
            this.openGallery();
        },
        bindEvents: function () {
            var self = ThemifyPageBuilder,
                $body = $('body'),
                resizeId;

            /* rows */
            $body.on(this.eventHandler.click, '.toggle_row', this.toggleRow)
                    .on(this.eventHandler.click, '.themify_builder_option_row', this.optionRow)

                    // used for both column and sub-column options
                    .on(this.eventHandler.click, '.themify_builder_option_column', this.optionColumn)
                    .on(this.eventHandler.click, '.themify_builder_delete_row', this.deleteRow)
                    .on(this.eventHandler.click, '.themify_builder_duplicate_row', this.duplicateRow)
                    .on(this.eventHandler.hover, '.themify_builder_row .row_menu', this.MenuHover)
                    .on(this.eventHandler.click, '#tfb_row_settings .add_new a', this.rowOptAddRow);
            $('.themify_builder_row_panel').on(this.eventHandler.hover, '.module_menu', this.MenuHover);

            /* module */
            $body.on(this.eventHandler.click, '.themify_module_options', this.optionsModule)
                    .on('dblclick', '.active_module', this.dblOptionModule)
                    .on(this.eventHandler.click, '.themify_module_duplicate', this.duplicateModule)
                    .on(this.eventHandler.click, '.themify_module_delete', this.deleteModule)
                    .on(this.eventHandler.click, '.add_module', this.addModule)

                    /* save module option */
                    .on(this.eventHandler.click, '#themify_builder_main_save', this.mainSave)
                    .on(this.eventHandler.click, '#tfb_module_settings .add_new a', this.moduleOptAddRow)
                    .on(this.eventHandler.click, '#themify_builder_duplicate', this.duplicatePage);

            /* hook save to publish button */
            $('input#publish,input#save-post').on(this.eventHandler.click, function (e) {
                if (e.which) {
                    $('input[name*="builder_switch_frontend"]').val(0);
                }
                self.postSave(e);
            });

            // module events
            $(window).resize(function () {
                clearTimeout(resizeId);
                resizeId = setTimeout(function () {
                    self.moduleEvents();
                }, 500);
            });

            // add loader to body
            self.alertLoader.appendTo('body');

            // equal height
            $('#page-buildert').on(this.eventHandler.click, function () {
                var $inputBuilderSwitch = $('input[name*="builder_switch_frontend"]');
                $inputBuilderSwitch.val(0);
                $inputBuilderSwitch.closest('.themify_field_row').hide(); // hide the switch to frontend field check
                self.newRowAvailable();
                self.moduleEvents();
            });
            $('input[name*="builder_switch_frontend"]').closest('.themify_field_row').hide(); // hide the switch to frontend field check

            // layout icon selected
            $body.on(this.eventHandler.click, '.tfl-icon', function (e) {
                $(this).addClass('selected').siblings().removeClass('selected');
                e.preventDefault();
            });

            // equal height
            self.equalHeight();

            // Import links
            $('.themify_builder_import_page').on(this.eventHandler.click, this.builderImportPage);
            $('.themify_builder_import_post').on(this.eventHandler.click, this.builderImportPost);
            $('.themify_builder_import_file').on(this.eventHandler.click, this.builderImportFile);
            $('.themify_builder_load_layout').on(this.eventHandler.click, this.builderLoadLayout);
            $('.themify_builder_save_layout').on(this.eventHandler.click, this.builderSaveLayout);
            $body.on(this.eventHandler.click, '#builder_submit_import_form', this.builderImportSubmit)
            /* Layout Action */
            .on(this.eventHandler.click, '.layout_preview', this.templateSelected)
            .on(this.eventHandler.click, '#builder_submit_layout_form', this.saveAsLayout);

            // switch frontend
            $('#themify_builder_switch_frontend').on(this.eventHandler.click, this.switchFrontEnd);

            // Grid Menu List
            $body
                    .on(this.eventHandler.click, '.themify_builder_grid_list li a', this._gridMenuClicked)
                    .on(this.eventHandler.hover, '.themify_builder_row .grid_menu', this._gridHover)
                    .on('change', '.themify_builder_row .gutter_select', this._gutterChange)
                    .on(this.eventHandler.click, '.themify_builder_sub_row .sub_row_delete', this._subRowDelete)
                    .on(this.eventHandler.click, '.themify_builder_sub_row .sub_row_duplicate', this._subRowDuplicate)
                    .on('change', '.themify_builder_equal_column_height_checkbox', this._equalColumnHeightChanged)

                    // Undo / Redo buttons
                    .on(this.eventHandler.click, '.js-themify-builder-undo-btn', this.actionUndo)
                    .on(this.eventHandler.click, '.js-themify-builder-redo-btn', this.actionRedo)

                    // Apply All checkbox
                    .on(this.eventHandler.click, '.style_apply_all', this.applyAll_events);

            // Listen to any changes of undo/redo
            if ( document.getElementsByClassName('themify_builder_undo_tools').length ) {
                ThemifyBuilderCommon.undoManager.instance.setCallback(this.undoManagerCallback);
                this.updateUndoBtns();
                ThemifyBuilderCommon.undoManager.events.on('change', function(event, startValue, newValue){
                    ThemifyBuilderCommon.undoManager.set(self.builderContainer, startValue, newValue);
                });
            }

            // Module actions
            self.moduleActions();
            self.newRowAvailable();
            self._selectedGridMenu();

            // Force sticky panel align after wordpress menu expand/collapse
            jQuery('#collapse-menu').on('click', function(e) {
                jQuery(window).resize();
            });
        },
        // "Apply all" // apply all init
        applyAll_init: function() {
            $('.style_apply_all').each(function() {
                var $val = $(this).val(),
                    $fields = $(this).closest('.themify_builder_field').prevUntil('h4'),
                    $inputs = $fields.last().find('input'),
                    $selects = $fields.last().find('select'),
                    $fieldFilter = $val == 'border'
                                    ? '[name="border_top_color"], [name="border_top_width"], [name="border_top_style"], [name="border_right_color"], [name="border_right_width"], [name="border_right_style"], [name="border_bottom_color"], [name="border_bottom_width"], [name="border_bottom_style"], [name="border_left_color"], [name="border_left_width"], [name="border_left_style"]'
                                    : '[name="'+$val+'_top"], [name="'+$val+'_right"], [name="'+$val+'_bottom"], [name="'+$val+'_left"]',
                    $preSelect = true,
                
                $callback = function() {
                    var $rel = $(this).data('rel');

                    if ($('.style_apply_all_'+ $rel).is(':checked')) {
                        var $val = $(this).val(),
                            $select = $(this).is('select');
                        
                        $('.style_'+ $rel).not(':first').closest('.themify_builder_field').each(function() {
                            if ($select) {
                                $(this).find('select option').prop('selected', false);
                                $(this).find('select option[value="'+$val+'"]').prop('selected', true).trigger('change');
                            }
                            else {
                                $(this).find('input[type="text"].tfb_lb_option').val($val).trigger('keyup');
                            }
                        });
                    }
                };

                if ($(this).is(':checked')) {
                    $fields.not(':last').slideUp();
                } else {
                    // Pre-select
                    $fields.find($fieldFilter).each(function(){
                        if ($(this).val() != '') {
                            $preSelect = false;
                            return false;
                        }
                    });

                    if ($preSelect) {
                        $(this).prop('checked', true);
                        $fields.not(':last').hide();
                    }
                }

                // Events
                $inputs.data('rel', $val);
                $selects.data('rel', $val);

                $inputs.on('keyup', $callback);
                $selects.on('change', $callback);
            });
        },
        // "Apply all" // apply all events
        applyAll_events: function( $selector ) {
            var $this = $(this),
                $fields = $this.closest('.themify_builder_field').prevUntil('h4');

            if ( $this.prop('checked') ) {
                $fields.not(':last').slideUp(function(){
                    $fields.last().find('input, select').each(function() {
                        var ev = ($(this).prop('tagName') == 'SELECT') ? 'change' : 'keyup';
                        $(this).trigger(ev);
                    });
                });
            } else {
                $fields.slideDown();
            }
        },
        // "Apply all" // apply all color change
        applyAll_verifyBorderColor: function(element, hiddenInputValue, colorDisplayInputValue, minicolorsObjValue) {
            if (jQuery('.style_apply_all_border').is(':checked') && jQuery(element).filter('[name="border_top_color"]').length > 0) {
                jQuery('[name="border_right_color"], [name="border_bottom_color"], [name="border_left_color"]').each(function() {
                    var minicolorsObj = jQuery(this).prevAll('.minicolors').find('.builderColorSelect');
                    minicolorsObj.parent().nextAll('.builderColorSelectInput').val(hiddenInputValue);
                    minicolorsObj.parent().nextAll('.colordisplay').val(colorDisplayInputValue);
                    minicolorsObj.minicolors('value', minicolorsObjValue);
                });
            }
        },
        setColorPicker: function (context) {
            // "Apply all" // instance self
            var self = ThemifyPageBuilder;

            $('.builderColorSelect', context).each(function () {
                var $minicolors = $(this),
                        // Hidden field used to save the value
                        $input = $minicolors.parent().parent().find('.builderColorSelectInput'),
                        // Visible field used to show the color only
                        $colorDisplay = $minicolors.parent().parent().find('.colordisplay'),
                        setColor = '',
                        setOpacity = 1.0,
                        sep = '_';

                if ('' != $input.val()) {
                    // Get saved value from hidden field
                    var colorOpacity = $input.val();
                    if (-1 != colorOpacity.indexOf(sep)) {
                        // If it's a color + opacity, split and assign the elements
                        colorOpacity = colorOpacity.split(sep);
                        setColor = colorOpacity[0];
                        setOpacity = colorOpacity[1] ? colorOpacity[1] : 1;
                    } else {
                        // If it's a simple color, assign solid to opacity
                        setColor = colorOpacity;
                        setOpacity = 1.0;
                    }
                    // If there was a color set, show in the dummy visible field
                    $colorDisplay.val(setColor);
                }

                $minicolors.minicolors({
                    opacity: 1,
                    textfield: false,
                    change: function (hex, opacity) {
                        if ('' != hex) {
                            if (opacity && '0.99' == opacity) {
                                opacity = '1';
                            }
                            var value = hex.replace('#', '') + sep + opacity;

                            var $cssRuleInput = this.parent().parent().find('.builderColorSelectInput');
                            $cssRuleInput.val(value);

                            $colorDisplay.val(hex.replace('#', ''));

                            // "Apply all" // verify is "apply all" is enabled to propagate the border color
                            self.applyAll_verifyBorderColor($cssRuleInput, value, hex.replace('#', ''), hex.replace('#', ''));
                        }
                    }
                });
                // After initialization, set initial swatch, either defaults or saved ones
                $minicolors.minicolors('value', setColor);
                $minicolors.minicolors('opacity', setOpacity);
            });

            $('body').on('blur', '.colordisplay', function () {
                var $input = $(this),
                        tempColor = '',
                        $minicolors = $input.parent().find('.builderColorSelect'),
                        $field = $input.parent().find('.builderColorSelectInput');
                if ('' != $input.val()) {
                    tempColor = $input.val();
                }
                $input.val(tempColor.replace('#', ''));
                $field.val($input.val().replace(/[abcdef0123456789]{3,6}/i, tempColor.replace('#', '')));
                $minicolors.minicolors('value', tempColor);

                // "Apply all" // verify is "apply all" is enabled to propagate the border color
                self.applyAll_verifyBorderColor($field, $field.val(), $input.val(), tempColor);
            }).on('keyup', '.colordisplay', function () {
                var $input = $(this),
                        tempColor = '',
                        $minicolors = $input.parent().find('.builderColorSelect'),
                        $field = $input.parent().find('.builderColorSelectInput');
                if ('' != $input.val()) {
                    tempColor = $input.val();
                }
                $input.val(tempColor.replace('#', ''));
                $field.val($input.val().replace(/[abcdef0123456789]{3,6}/i, tempColor.replace('#', '')));
                $minicolors.minicolors('value', tempColor);

                // "Apply all" // verify is "apply all" is enabled to propagate the border color
                self.applyAll_verifyBorderColor($field, $field.val(), $input.val(), tempColor);
            });
        },
        moduleEvents: function () {
            var self = ThemifyPageBuilder;

            $('.row_menu .themify_builder_dropdown, .module_menu .themify_builder_dropdown').hide();
            $('.themify_module_holder').each(function () {
                if ($(this).find('.themify_builder_module').length > 0) {
                    $(this).find('.empty_holder_text').hide();
                } else {
                    $(this).find('.empty_holder_text').show();
                }
            });

            $(".themify_builder_module_panel .themify_builder_module").draggable({
                appendTo: "body",
                helper: "clone",
                revert: 'invalid',
                connectToSortable: ".themify_module_holder"
            });

            $('.themify_module_holder').each(function(){
                var startValue = self.builderContainer.innerHTML, newValue;
                $(this).sortable({
                    placeholder: 'themify_builder_ui_state_highlight',
                    items: '.themify_builder_module, .themify_builder_sub_row',
                    connectWith: '.themify_module_holder',
                    cursor: 'move',
                    revert: 100,
                    sort: function (event, ui) {
                        var placeholder_h = ui.item.outerHeight();
                        $('.themify_module_holder .themify_builder_ui_state_highlight').height(placeholder_h);
                    },
                    receive: function (event, ui) {
                        self.PlaceHoldDragger();
                        $(this).parent().find('.empty_holder_text').hide();
                    },
                    stop: function (event, ui) {
                        if (!ui.item.hasClass('active_module') && !ui.item.hasClass('themify_builder_sub_row')) {
                            var tmpl_params = {slug: ui.item.data('module-slug'), name: ui.item.data('module-name')},
                            module_item_tmpl = wp.template('builder_module_item'),
                                    module_item = module_item_tmpl(tmpl_params),
                                    $newElems = $(module_item);

                            $(this).parent().find(".empty_holder_text").hide();
                            ui.item.replaceWith($newElems);
                            $newElems.find('.themify_module_options').trigger(self.eventHandler.click, [true]);
                            self.moduleEvents();
                        } else {
                            // Make sub_row only can nested one level
                            if (ui.item.hasClass('themify_builder_sub_row') && ui.item.parents('.themify_builder_sub_row').length) {
                                var $clone_for_move = ui.item.find('.active_module').clone();
                                $clone_for_move.insertAfter(ui.item);
                                ui.item.remove();
                            }

                            self.newRowAvailable();
                            self.moduleEvents();

                            newValue = self.builderContainer.innerHTML;
                            if ( startValue !== newValue ) {
                                ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue]);
                            }
                        }
                        self.equalHeight();
                    }
                });
            });
            
            var startValue = self.builderContainer.innerHTML;
            $("#themify_builder_row_wrapper").sortable({
                items: '.themify_builder_row',
                handle: '.themify_builder_row_top',
                axis: 'y',
                placeholder: 'themify_builder_ui_state_highlight',
                sort: function (event, ui) {
                    var placeholder_h = ui.item.height();
                    $('.themify_builder_row_panel .themify_builder_ui_state_highlight').height(placeholder_h);
                },
                stop: function(event, ui) {
                    var newValue = self.builderContainer.innerHTML;
                    if ( startValue !== newValue ) {
                        ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue]);
                    }
                    self.moduleEvents();
                }
            });

            var grid_menu_tmpl = wp.template('builder_grid_menu'),
                    grid_menu_render = grid_menu_tmpl({});
            $('.themify_builder_row_content').each(function () {
                $(this).children().each(function () {
                    var $holder = $(this).find('.themify_module_holder').first();
                    $holder.children('.themify_builder_module').each(function () {
                        if ($(this).find('.grid_menu').length == 0) {
                            $(this).append($(grid_menu_render));
                        }
                    });
                });
            });
        },
        getDocHeight: function () {
            var D = document;
            return Math.max(
                    Math.max(D.body.scrollHeight, D.documentElement.scrollHeight),
                    Math.max(D.body.offsetHeight, D.documentElement.offsetHeight),
                    Math.max(D.body.clientHeight, D.documentElement.clientHeight)
                    );
        },
        setupTooltips: function () {
            var setupBottomTooltips = function () {
                $('body').on('mouseover', '[rel^="themify-tooltip-"]', function (e) {
                    var $thisElem = $(this);

                    // append custom tooltip
                    $thisElem.append('<span class="themify_tooltip">' + $thisElem.data('title') + '</span>');
                });

                $('body').on('mouseout', '[rel^="themify-tooltip-"]', function (e) {
                    var $thisElem = $(this);

                    // remove custom tooltip
                    $thisElem.children('.themify_tooltip').remove();
                });
            };

            setupBottomTooltips();
        },
        toggleRow: function (e) {
            e.preventDefault();
            $(this).parents('.themify_builder_row').toggleClass('collapsed').find('.themify_builder_row_content').slideToggle();
        },
        deleteRow: function (e) {
            e.preventDefault();

            if (!confirm(themifyBuilder.rowDeleteConfirm)) {
                return;
            }

            var row_length = $(this).closest('.themify_builder_row_js_wrapper').find('.themify_builder_row:visible').length,
                startValue = ThemifyPageBuilder.builderContainer.innerHTML;
            if (row_length > 1) {
                $(this).closest('.themify_builder_row').remove();
            }
            else {
                $(this).closest('.themify_builder_row').hide();
            }

            var newValue = ThemifyPageBuilder.builderContainer.innerHTML;
            if ( startValue !== newValue ) {
                ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue]);
            }
        },
        duplicateRow: function (e) {
            e.preventDefault();
            var self = ThemifyPageBuilder,
                oriElems = $(this).closest('.themify_builder_row'),
                newElems = $(this).closest('.themify_builder_row').clone(),
                row_count = $('#tfb_module_settings .themify_builder_row_js_wrapper').find('.themify_builder_row:visible').length + 1,
                number = row_count + Math.floor(Math.random() * 9),
                startValue = self.builderContainer.innerHTML;

            // fix wpeditor empty textarea
            newElems.find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                var this_option_id = $(this).attr('id'),
                        element_val;

                if (typeof tinyMCE !== 'undefined') {
                    element_val = tinyMCE.get(this_option_id).hidden === false ? tinyMCE.get(this_option_id).getContent() : switchEditors.wpautop(tinymce.DOM.get(this_option_id).value);
                } else {
                    element_val = $('#' + this_option_id).val();
                }
                $(this).val(element_val);
                $(this).addClass('clone');
            });

            // fix textarea field clone
            newElems.find('textarea:not(.tfb_lb_wp_editor)').each(function (i) {
                var insertTo = oriElems.find('textarea').eq(i).val();
                if (insertTo != '') {
                    $(this).val(insertTo);
                }
            });

            // fix radio button clone
            newElems.find('.themify-builder-radio-dnd').each(function (i) {
                var oriname = $(this).attr('name');
                $(this).attr('name', oriname + '_' + row_count);
                $(this).attr('id', oriname + '_' + row_count + '_' + i);
                $(this).next('label').attr('for', oriname + '_' + row_count + '_' + i);
            });

            newElems.find('.themify-builder-plupload-upload-uic').each(function (i) {
                $(this).attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-upload-ui');
                $(this).find('input[type="button"]').attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-browse-button');
                $(this).addClass('plupload-clone');
            });
            newElems.find('select').each(function (i) {
                var orival =  oriElems.find('select').eq(i).find('option:selected').val();
                $(this).find('option[value="'+orival+'"]').prop('selected',true);
            });
            newElems.insertAfter(oriElems).find('.themify_builder_dropdown').hide();

            $('#tfb_module_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child.clone').each(function (i) {
                var element = $(this),
                        parent_child = element.closest('.themify_builder_input');

                $(this).closest('.wp-editor-wrap').remove();

                var oriname = element.attr('name');
                element.attr('id', oriname + '_' + row_count + number + '_' + i);
                element.attr('class').replace('wp-editor-area', '');

                element.appendTo(parent_child).wrap('<div class="wp-editor-wrap"/>');

            });

            self.addNewWPEditor();
            self.builderPlupload('new_elemn');
            self.moduleEvents();
            if(newElems.find('.builderColorSelect').length>0){
                newElems.find('.builderColorSelect').minicolors('destroy').removeAttr('maxlength');
                self.setColorPicker(newElems);
            }
            var newValue = self.builderContainer.innerHTML;
            if ( startValue !== newValue ) {
                ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue]);
            }
        },
        menuTouched: [],
        MenuHover: function (e) {
            if ('touchend' == e.type) {
                var $row = $(this).closest('.themify_builder_row'),
                        $col = $(this).closest('.themify_builder_col'),
                        $mod = $(this).closest('.themify_builder_module'),
                        index = 'row_' + $row.index();
                if ($col.length > 0) {
                    index += '_col_' + $col.index();
                }
                if ($mod.length > 0) {
                    index += '_mod_' + $mod.index();
                }
                if (ThemifyPageBuilder.menuTouched[index]) {
                    $(this).find('.themify_builder_dropdown').stop(false, true).hide();
                    $row.css('z-index', '');
                    ThemifyPageBuilder.menuTouched = [];
                } else {
                    var $builderCont = $('#themify_builder_row_wrapper');
                    $builderCont.find('.themify_builder_dropdown').stop(false, true).hide();
                    $builderCont.find('.themify_builder_row').css('z-index', '');
                    $(this).find('.themify_builder_dropdown').stop(false, true).show();
                    $row.css('z-index', '998');
                    ThemifyPageBuilder.menuTouched = [];
                    ThemifyPageBuilder.menuTouched[index] = true;
                }
            } else if (e.type == 'mouseenter') {
                $(this).find('.themify_builder_dropdown').stop(false, true).show();
            } else if (e.type == 'mouseleave') {
                $(this).find('.themify_builder_dropdown').stop(false, true).hide();
            }
        },
        highlightModuleBack: function ($module) {
            $('#themify_builder_row_wrapper .themify_builder_module').removeClass('current_selected_module');
            $module.addClass('current_selected_module');
        },
        optionsModule: function (e, isNewModule) {
            e.preventDefault();
            isNewModule = isNewModule || false; // assume that if isNewModule:true = Add module, otherwise Edit Module
            var self = ThemifyPageBuilder,
                    $this = $(this),
                    module_name = $this.data('module-name'),
                    set_elems = $this.closest('.themify_builder_module').find('.themify_module_settings').find('script[type="text/json"]'),
                    is_settings_exist = (set_elems.text().trim().length > 2 && set_elems.text().trim() != 'null') ? true : false,
                    el_settings = (is_settings_exist) ? JSON.parse(set_elems.text().trim()) : '';

            $('.module_menu .themify_builder_dropdown').hide();

            self.highlightModuleBack($this.parents('.themify_builder_module'));

            var callback = function (response) {
                var formEl = document.getElementById('tfb_module_settings');
                if ( isNewModule ) {
                    response.setAttribute('data-form-state', 'new');
                } else {
                    response.setAttribute('data-form-state', 'edit');
                }

                var inputs = response.querySelectorAll('.tfb_lb_option'), iterate;
                for (iterate = 0; iterate < inputs.length; ++iterate) {
                    var $this_option = $(inputs[iterate]),
                        this_option_id = $this_option.attr('id'),
                        $found_element = el_settings[this_option_id];

                    if ($found_element) {
                        if ($this_option.hasClass('select_menu_field')) {
                            if (!isNaN($found_element)) {
                                $this_option.find("option[data-termid='" + $found_element + "']").attr('selected', 'selected');
                            } else {
                                $this_option.find("option[value='" + $found_element + "']").attr('selected', 'selected');
                            }
                        } else if ($this_option.is('select')) {
                            $this_option.val($found_element);
                        } else if ($this_option.hasClass('themify-builder-uploader-input')) {
                            var img_field = $found_element,
                                    img_thumb = $('<img/>', {src: img_field, width: 50, height: 50});

                            if (img_field != '') {
                                $this_option.val(img_field);
                                $this_option.parent().find('.img-placeholder').empty().html(img_thumb);
                            }
                            else {
                                $this_option.parent().find('.thumb_preview').hide();
                            }

                        } else if ($this_option.hasClass('themify-option-query-cat')) {
                            var parent = $this_option.parent(),
                                    multiple_cat = parent.find('.query_category_multiple'),
                                    elems = $found_element,
                                    value = elems.split('|'),
                                    cat_val = value[0];

                            parent.find("option[value='" + cat_val + "']").attr('selected', 'selected');
                            multiple_cat.val(cat_val);

                        } else if ($this_option.hasClass('themify_builder_row_js_wrapper')) {
                            var row_append = 0;
                            if ($found_element.length > 0) {
                                row_append = $found_element.length - 1;
                            }

                            // add new row
                            for (var i = 0; i < row_append; i++) {
                                $this_option.parent().find('.add_new a').first().trigger('click');
                            }

                            $this_option.find('.themify_builder_row').each(function (r) {
                                $(this).find('.tfb_lb_option_child').each(function (i) {
                                        var $this_option_child = $(this),
                                            this_option_id_real = $this_option_child.attr('id'),
                                            this_option_id_child = $this_option_child.hasClass('tfb_lb_wp_editor') ? $this_option_child.attr('name') : $this_option_child.data('input-id');
                                            if(!this_option_id_child){
                                                this_option_id_child = this_option_id_real;
                                            }
                                            var $found_element_child = $found_element[r]['' + this_option_id_child + ''];

                                    if ($this_option_child.hasClass('themify-builder-uploader-input')) {
                                        var img_field = $found_element_child,
                                                img_thumb = $('<img/>', {src: img_field, width: 50, height: 50});

                                        if (img_field != '' && img_field != undefined) {
                                            $this_option_child.val(img_field);
                                            $this_option_child.parent().find('.img-placeholder').empty().html(img_thumb).parent().show();
                                        }
                                        else {
                                            $this_option_child.parent().find('.thumb_preview').hide();
                                        }

                                    }
                                    else if ($this_option_child.hasClass('tf-radio-choice')) {
                                        $this_option_child.find("input[value='" + $found_element_child + "']").attr('checked', 'checked');
                                    } else if ($this_option_child.hasClass('themify-layout-icon')) {
                                        $this_option_child.find('#' + $found_element_child).addClass('selected');
                                    }
                                    else if ($this_option_child.hasClass('themify-checkbox')) {
                                        for(var $i in $found_element_child){
                                           
                                             $this_option_child.find("input[value='" + $found_element_child[$i] + "']").prop('checked', true);
                                        }
                                    }
                                    else if ($this_option_child.is('input, textarea, select')) {
                                        $this_option_child.val($found_element_child);
                                    }

                                    if ($this_option_child.hasClass('tfb_lb_wp_editor') && !$this_option_child.hasClass('clone')) {
                                        self.initQuickTags(this_option_id_real);
                                        if (typeof tinyMCE !== 'undefined') {
                                            self.initNewEditor(this_option_id_real);
                                        }
                                    }

                                });
                            });

                        } else if ($this_option.hasClass('tf-radio-input-container')) {
                            $this_option.find("input[value='" + $found_element + "']").attr('checked', 'checked');
                            var selected_group = $this_option.find('input[name="' + this_option_id + '"]:checked').val();

                            // has group element enable
                            if ($this_option.hasClass('tf-option-checkbox-enable')) {
                                $('.tf-group-element').hide();
                                $('.tf-group-element-' + selected_group).show();
                            }

                        } else if ($this_option.is('input[type!="checkbox"][type!="radio"], textarea')) {
                            $this_option.val($found_element);
                            if(!isNewModule && $this_option.is('textarea') && $this_option.hasClass('tf-thumbs-preview')){
                               self.getShortcodePreview($this_option,$found_element);
                            }
                        } else if ($this_option.hasClass('themify-checkbox')) {
                            var cselected = $found_element;
                            cselected = cselected.split('|');

                            $this_option.find('.tf-checkbox').each(function () {
                                if ($.inArray($(this).val(), cselected) > -1) {
                                    $(this).prop('checked', true);
                                }
                                else {
                                    $(this).prop('checked', false);
                                }
                            });

                        } else if ($this_option.hasClass('themify-layout-icon')) {
                            $this_option.find('#' + $found_element).addClass('selected');
                        } else {
                            $this_option.html($found_element);
                        }
                    }
                    else {
                        if ($this_option.hasClass('themify-layout-icon')) {
                            $this_option.children().first().addClass('selected');
                        }
                        else if ($this_option.hasClass('themify-builder-uploader-input')) {
                            $this_option.parent().find('.thumb_preview').hide();
                        }
                        else if ($this_option.hasClass('tf-radio-input-container')) {
                            $this_option.find('input[type="radio"]').first().prop('checked');
                            var selected_group = $this_option.find('input[name="' + this_option_id + '"]:checked').val();

                            // has group element enable
                            if ($this_option.hasClass('tf-option-checkbox-enable')) {
                                $('.tf-group-element').hide();
                                $('.tf-group-element-' + selected_group).show();
                            }
                        }
                        else if ($this_option.hasClass('themify_builder_row_js_wrapper')) {
                            $this_option.find('.themify_builder_row').each(function (r) {
                                $(this).find('.tfb_lb_option_child').each(function (i) {
                                    var $this_option_child = $(this),
                                            this_option_id_real = $this_option_child.attr('id');

                                    if ($this_option_child.hasClass('tfb_lb_wp_editor')) {

                                        var this_option_id_child = $this_option_child.data('input-id');

                                        self.initQuickTags(this_option_id_real);
                                        if (typeof tinyMCE !== 'undefined') {
                                            self.initNewEditor(this_option_id_real);
                                        }
                                    }

                                });
                            });
                        }
                        else if ($this_option.hasClass('themify-checkbox') && is_settings_exist) {
                            $this_option.find('.tf-checkbox').each(function () {
                                $(this).prop('checked', false);
                            });
                        }
                        else if ($this_option.is('input[type!="checkbox"][type!="radio"], textarea') && is_settings_exist) {
                            $this_option.val('');
                        }
                    }

                    if ($this_option.hasClass('tfb_lb_wp_editor')) {
                        self.initQuickTags(this_option_id);
                        if (typeof tinyMCE !== 'undefined') {
                            self.initNewEditor(this_option_id);
                        }
                    }

                } // iterate

                // Trigger event
                $('body').trigger('editing_module_option', [el_settings]);

                // add new wp editor
                self.addNewWPEditor();

                // colorpicker
                self.setColorPicker();

                // plupload init
                self.builderPlupload('normal');

                // option binding setup
                self.moduleOptionsBinding();

                // builder drag n drop init
                self.moduleOptionBuilder();

                // tabular options
                $('.themify_builder_tabs').tabs();

                // "Apply all" // apply all init
                self.applyAll_init();
            };

            ThemifyBuilderCommon.highlightRow($(this).closest('.themify_builder_row'));

            ThemifyBuilderCommon.Lightbox.open( { loadMethod: 'inline', templateID: 'builder_form_module_' + module_name }, callback);
        },
        moduleOptionsBinding: function () {
            var form = $('#tfb_module_settings');
            $('input[data-binding], textarea[data-binding], select[data-binding]', form).change(function () {
                var logic = false,
                        binding = $(this).data('binding'),
                        val = $(this).val();
                if (val == '' && binding['empty'] != undefined) {
                    logic = binding['empty'];
                } else if (val != '' && binding[val] != undefined) {
                    logic = binding[val];
                } else if (val != '' && binding['not_empty'] != undefined) {
                    logic = binding['not_empty'];
                }

                if (logic) {
                    if (logic['show'] != undefined) {
                        $.each(logic['show'], function (i, v) {
                            $('.' + v).show();
                        });
                    }
                    if (logic['hide'] != undefined) {
                        $.each(logic['hide'], function (i, v) {
                            $('.' + v).hide();
                        });
                    }
                }
            }).change();
        },
        dblOptionModule: function (e) {
            e.preventDefault();
            $(this).find('.themify_module_options').trigger('click');
        },
        duplicateModule: function (e) {
            e.preventDefault();
            var holder = $(this).closest('.themify_builder_module'),
                self = ThemifyPageBuilder,
                startValue = self.builderContainer.innerHTML;
            $(this).closest('.themify_builder_dropdown').hide();
            $(this).closest('.themify_builder_module').clone().insertAfter(holder).find('.themify_builder_dropdown').hide();
            self.equalHeight();

            var newValue = self.builderContainer.innerHTML;
            if ( startValue !== newValue ) {
                ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue]);
            }
        },
        deleteModule: function (e) {
            e.preventDefault();

            if (confirm(themifyBuilder.moduleDeleteConfirm)) {
                var self = ThemifyPageBuilder,
                    startValue = self.builderContainer.innerHTML;
                self.switchPlaceholdModule($(this));
                $(this).parents('.themify_builder_module').remove();
                self.newRowAvailable();
                self.equalHeight();
                self.moduleEvents();

                var newValue = self.builderContainer.innerHTML;
                if ( startValue !== newValue ) {
                    ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue]);
                }
            }
        },
        addModule: function (e) {
            e.preventDefault();
            var self = ThemifyPageBuilder,
                    tmpl_params = {slug: $(this).closest('.themify_builder_module').data('module-slug'), name: $(this).closest('.themify_builder_module').data('module-name')},
            module_item_tmpl = wp.template('builder_module_item'),
                    module_item = module_item_tmpl(tmpl_params),
                    dest = $('.themify_builder_row_panel').find('.themify_builder_row:visible').first().find('.themify_module_holder').first(),
                    $newElems = $(module_item),
                    position = $newElems.appendTo(dest);

            $('html,body').animate({scrollTop: position.offset().top - 300}, 500);
            self.moduleEvents();
            self.equalHeight();
            $newElems.find('.themify_module_options').trigger(self.eventHandler.click, [true]);
        },
        cancelKeyListener: function (e) {
            if (e.keyCode == 27) {
                e.preventDefault();
                ThemifyPageBuilder.closeLightbox(e);
            }
        },
        close: function (e) {
            e.preventDefault();
            $(document).off('keyup', ThemifyPageBuilder.cancelKeyListener);
            var self = ThemifyPageBuilder;

            var $tfb_dialog_form = $('form#tfb_module_settings');

            if (typeof tinyMCE !== 'undefined') {
                $tfb_dialog_form.find('.tfb_lb_wp_editor').each(function () {
                    var $id = $(this).prop('id');
                    switchEditors.go($id, 'tmce');
                });
            }

            $('#themify_builder_lightbox_parent').animate({
                top: self.getDocHeight()
            }, 800, function () {
                // Animation complete.
                $('#themify_builder_lightbox_container').empty();
                $('.themify_builder_lightbox_title').text('');
                $('#themify_builder_overlay, #themify_builder_lightbox_parent').hide();
                $('#themify_builder_lightbox_parent').removeClass('tfb-lightbox-open');
                self.deleteEmptyModule();
                $('body').removeClass('noScroll');
            });
        },
        initNewEditor: function (editor_id) {
            var self = ThemifyPageBuilder;
            if (typeof tinyMCEPreInit.mceInit[editor_id] !== "undefined") {
                self.initMCEv4(editor_id, tinyMCEPreInit.mceInit[editor_id]);
                return;
            }
            var tfb_new_editor_object = self.tfb_hidden_editor_object;

            tfb_new_editor_object['elements'] = editor_id;
            tfb_new_editor_object['selector'] = '#' + editor_id;
            tfb_new_editor_object['wp_autoresize_on'] = false;
            tinyMCEPreInit.mceInit[editor_id] = tfb_new_editor_object;

            // v4 compatibility
            self.initMCEv4(editor_id, tinyMCEPreInit.mceInit[editor_id]);
        },
        initMCEv4: function (editor_id, $settings) {
            // v4 compatibility
            if (parseInt(tinyMCE.majorVersion) > 3) {
                // Creates a new editor instance
                var ed = new tinyMCE.Editor(editor_id, $settings, tinyMCE.EditorManager);
                ed.render();
            }
        },
        initQuickTags: function (editor_id) {
            // add quicktags
            if (typeof (QTags) == 'function') {
                quicktags({id: editor_id});
                QTags._buttonsInit();
            }
        },
        switchPlaceholdModule: function (obj) {
            var check = obj.parents('.themify_module_holder');
            if (check.find('.themify_builder_module').length == 1) {
                check.find('.empty_holder_text').show();
            }
        },
        PlaceHoldDragger: function () {
            $('.themify_module_holder').each(function () {
                if ($(this).find('.themify_builder_module').length == 0) {
                    $(this).find('.empty_holder_text').show();
                }
            });
        },
        makeEqual: function ($obj, target) {
            $obj.each(function () {
                var t = 0;
                $(this).find(target).children().each(function () {
                    var $holder = $(this).find('.themify_module_holder').first();
                    $holder.css('min-height', '');
                    if ($holder.height() > t) {
                        t = $holder.height();
                    }
                });
                $(this).find(target).children().each(function () {
                    $(this).find('.themify_module_holder').first().css('min-height', t + 'px');
                });
            });
        },
        equalHeight: function () {
            ThemifyPageBuilder.makeEqual($('.themify_builder_row:visible'), '.themify_builder_row_content:visible');
            ThemifyPageBuilder.makeEqual($('.themify_builder_sub_row:visible'), '.themify_builder_sub_row_content');
        },
        saveData: function (loader, callback, saveto) {
            saveto = saveto || 'main';
            var self = ThemifyPageBuilder,
                    dataSend = self.retrieveData(),
                    ids = [{id: $('input#post_ID').val(), data: dataSend}];

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                data:
                        {
                            action: 'tfb_save_data',
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            ids: JSON.stringify(ids),
                            tfb_saveto: saveto
                        },
                cache: false,
                beforeSend: function (xhr) {
                    if (loader) {
                        $('#themify_builder_alert').addClass('busy').show();
                    }
                },
                success: function (data) {
                    if (loader) {
                        $('#themify_builder_alert').removeClass('busy').addClass('done');

                        setTimeout(function () {
                            $('#themify_builder_alert').fadeOut('slow').removeClass('done');
                        }, 1000);
                    }

                    // load callback
                    if ($.isFunction(callback)) {
                        callback.call(this, data);
                    }
                    $('body').trigger('themify_builder_save_data');

                }
            });
        },
        moduleSave: function (e) {
            var self = ThemifyPageBuilder,
                $active_module_settings = $('.current_selected_module .themify_module_settings'),
                parent_active_mod = $active_module_settings.parent(),
                temp_appended_data = {},
                form_state = document.getElementById('tfb_module_settings').getAttribute('data-form-state') || 'edit';

            if ( 'edit' == form_state ) {
                var startValue = self.builderContainer.innerHTML;
            } else {
                var $clonedStartValue = $(self.builderContainer).clone();
                $clonedStartValue.find('.current_selected_module').remove();
                var startValue = $clonedStartValue[0].innerHTML;
            }

            $('#tfb_module_settings .tfb_lb_option').each(function () {
                var option_value, option_class,
                        this_option_id = $(this).attr('id');

                option_class = this_option_id + ' tfb_module_setting';

                if ($(this).hasClass('tfb_lb_wp_editor') && !$(this).hasClass('builder-field')) {
                    if (typeof tinyMCE !== 'undefined') {
                        option_value = tinyMCE.get(this_option_id).hidden === false ? tinyMCE.get(this_option_id).getContent() : switchEditors.wpautop(tinymce.DOM.get(this_option_id).value);
                    } else {
                        option_value = $(this).val();
                    }

                    if (parent_active_mod.hasClass('module-text') || parent_active_mod.hasClass('module-box')) {
                        var excerpt = self.limitString(option_value, 100);
                        parent_active_mod.find('.module_excerpt').empty().html(excerpt);
                    }
                }
                else if ($(this).hasClass('themify-checkbox')) {
                    var cselected = [];
                    $(this).find('.tf-checkbox:checked').each(function (i) {
                        cselected.push($(this).val());
                    });
                    if (cselected.length > 0) {
                        option_value = cselected.join('|');
                    } else {
                        option_value = '|';
                    }
                }
                else if ($(this).hasClass('themify-layout-icon')) {
                    if ($(this).find('.selected').length > 0) {
                        option_value = $(this).find('.selected').attr('id');
                    }
                    else {
                        option_value = $(this).children().first().attr('id');
                    }
                }
                else if ($(this).hasClass('themify-option-query-cat')) {
                    var parent = $(this).parent(),
                            single_cat = parent.find('.query_category_single'),
                            multiple_cat = parent.find('.query_category_multiple');

                    if (multiple_cat.val() != '') {
                        option_value = multiple_cat.val() + '|multiple';
                    } else {
                        option_value = single_cat.val() + '|single';
                    }
                }
                else if ($(this).hasClass('themify_builder_row_js_wrapper')) {
                    var row_items = [];
                    $(this).find('.themify_builder_row').each(function () {
                        var temp_rows = {};
                        $(this).find('.tfb_lb_option_child').each(function () {
                            var option_value_child,
                                    this_option_id_child = $(this).data('input-id');
                                if(!this_option_id_child){
                                    this_option_id_child = $(this).attr('id');
                                }
                                
                            if ($(this).hasClass('tf-radio-choice')) {
                                option_value_child = ($(this).find(':checked').length > 0) ? $(this).find(':checked').val() : '';
                            } else if ($(this).hasClass('themify-layout-icon')) {
                                if(!this_option_id_child){
                                    this_option_id_child = $(this).attr('id');
                                }
                                if ($(this).find('.selected').length > 0) {
                                    option_value_child = $(this).find('.selected').attr('id');
                                }
                                else {
                                    option_value_child = $(this).children().first().attr('id');
                                }
                            }
                            else if($(this).hasClass('themify-checkbox')){
                                 option_value_child = [];
                                 $(this).find(':checked').each(function(i){
                                     option_value_child[i] = $(this).val();
                                 });
                            }
                            else if ($(this).hasClass('tfb_lb_wp_editor')) {
                                var text_id = $(this).attr('id');
                                this_option_id_child = $(this).attr('name');
                                if (typeof tinyMCE !== 'undefined') {
                                    option_value_child = tinyMCE.get(text_id).hidden === false ? tinyMCE.get(text_id).getContent() : switchEditors.wpautop(tinymce.DOM.get(text_id).value);
                                } else {
                                    option_value_child = $(this).val();
                                }
                            }
                            else {
                                option_value_child = $(this).val();
                            }

                            if (option_value_child) {
                                temp_rows[this_option_id_child] = option_value_child;
                            }
                        });
                        row_items.push(temp_rows);
                    });
                    option_value = row_items;
                }
                else if ($(this).hasClass('tf-radio-input-container')) {
                    option_value = $(this).find('input[name="' + this_option_id + '"]:checked').val();
                }
                else if ($(this).hasClass('module-widget-form-container')) {
                    option_value = $(this).find(':input').serializeObject();
                }
                else if ($(this).is('select, input, textarea')) {
                    option_value = $(this).val();
                }

                if (option_value) {
                    temp_appended_data[this_option_id] = option_value;
                }
            });
            console.log(temp_appended_data, 'temp_appended_data');
            $active_module_settings.find('script[type="text/json"]').text(JSON.stringify(temp_appended_data));

            ThemifyBuilderCommon.Lightbox.close();

            // clear empty module
            self.deleteEmptyModule();
            self.newRowAvailable();
            self.moduleEvents();

            var newValue = self.builderContainer.innerHTML;
            ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue ]);

            e.preventDefault();
        },
        postSave: function (e) {
            if ($('#themify_builder_row_wrapper').is(':visible')) {
                var self = ThemifyPageBuilder;

                if (!self.isPostSave) {
                    self.saveData(false, function () {
                        // Clear undo history
                        ThemifyBuilderCommon.undoManager.instance.clear();

                        self.isPostSave = true;
                        $(e.currentTarget).trigger('click');
                    });
                    e.preventDefault();
                }
            }
        },
        switchFrontEnd: function (e) {
            if ($('#themify_builder_row_wrapper').is(':visible')) {
                var self = ThemifyPageBuilder,
                        targetLink = themifyBuilder.permalink;

                $('#themify_builder_alert').addClass('busy').show();
                self.saveData(false, function () {

                    // Clear undo history
                    ThemifyBuilderCommon.undoManager.instance.clear();

                    var new_url = targetLink.replace(/\&amp;/g, '&') + '#builder_active';
                    window.location.href = new_url;
                });
                e.preventDefault();
            }
        },
        mainSave: function (e) {
            var self = ThemifyPageBuilder;

            self.saveData(true);
            e.preventDefault();
        },
        retrieveData: function () {
            var self = ThemifyPageBuilder,
                    option_data = {},
                    cols = {},
                    modules = {};

            // rows
            $('#themify_builder_row_wrapper .themify_builder_row:visible').each(function (r) {
                var row_order = r,
                        cols = {};

                // cols
                $(this).find('.themify_builder_row_content').children('.themify_builder_col').each(function (c) {
                    var grid_class = self.filterClass($(this).attr('class')),
                            modules = {};
                    // mods
                    $(this).find('.themify_module_holder').first().children().each(function (m) {
                        if ($(this).hasClass('themify_builder_module')) {
                            var mod_name = $(this).data('mod-name'),
                                    mod_elems = $(this).find('.themify_module_settings'),
                                    mod_settings = JSON.parse(mod_elems.find('script[type="text/json"]').text());
                            modules[m] = {'mod_name': mod_name, 'mod_settings': mod_settings};
                        }

                        // Sub Rows
                        if ($(this).hasClass('themify_builder_sub_row')) {
                            var sub_cols = {};
                            $(this).find('.themify_builder_col').each(function (sub_col) {
                                var sub_grid_class = self.filterClass($(this).attr('class')),
                                        sub_modules = {};

                                $(this).find('.active_module').each(function (sub_m) {
                                    var sub_mod_name = $(this).data('mod-name'),
                                            sub_mod_elems = $(this).find('.themify_module_settings'),
                                            sub_mod_settings = JSON.parse(sub_mod_elems.find('script[type="text/json"]').text());
                                    sub_modules[sub_m] = {'mod_name': sub_mod_name, 'mod_settings': sub_mod_settings};
                                });

                                sub_cols[ sub_col ] = {
                                    'column_order': sub_col,
                                    grid_class: sub_grid_class,
                                    modules: sub_modules
                                };

                                // get sub-column styling
                                if ($(this).children('.column-data-styling').length > 0) {
                                    var $data_styling = $.parseJSON( $(this).children('.column-data-styling').attr('data-styling') );
                                    if ('object' === typeof $data_styling)
                                        sub_cols[ sub_col ].styling = $data_styling;
                                }
                            });

                            modules[m] = {
                                row_order: m,
                                gutter: $(this).data('gutter'),
                                equal_column_height: $(this).data('equal-column-height'),
                                cols: sub_cols
                            };
                        }
                    });

                    cols[c] = {
                        'column_order': c,
                        'grid_class': grid_class,
                        'modules': modules
                    };

                    // get column styling
                    if ($(this).children('.column-data-styling').length > 0) {
                        var $data_styling = $.parseJSON( $(this).children('.column-data-styling').attr('data-styling') );
                        if ('object' === typeof $data_styling)
                            cols[ c ].styling = $data_styling;
                    }
                });

                option_data[r] = {
                    row_order: r,
                    gutter: $(this).data('gutter'),
                    equal_column_height: $(this).data('equal-column-height'),
                    cols: cols
                };

                // get row styling
                if ($(this).find('.row-data-styling').length > 0) {
                    var $data_styling = $.parseJSON( $(this).find('.row-data-styling').attr('data-styling') );
                    if ('object' === typeof $data_styling)
                        option_data[r].styling = $data_styling;
                }

            });

            return option_data;
        },
        filterClass: function (str) {
            var grid = ThemifyPageBuilder.gridClass.concat(['first', 'last']),
                    n = str.split(' '),
                    new_arr = [];

            for (var i = 0; i < n.length; i++) {
                if ($.inArray(n[i], grid) > -1) {
                    new_arr.push(n[i]);
                }
            }

            return new_arr.join(' ');
        },
        limitString: function (str, limit) {
            var new_str;
            str = ThemifyPageBuilder.stripHtml(str); // strip html tags

            if (str.toString().length > limit) {
                new_str = str.toString().substr(0, limit);
            }
            else {
                new_str = str.toString();
            }

            return new_str;
        },
        stripHtml: function(html) {
           var tmp = document.createElement("DIV");
           tmp.innerHTML = html;
           return tmp.textContent || tmp.innerText || "";
        },
        mediaUploader: function () {

            // Uploading files
            var $body = $('body');

            // Field Uploader
            $body.on('click', '.themify-builder-media-uploader', function (event) {
                var $el = $(this);

                var file_frame = wp.media.frames.file_frame = wp.media({
                    title: $el.data('uploader-title'),
                    id: 'builder-media-insert',
                    frame: 'post',
                    library: {
                        type: $el.data('library-type') ? $el.data('library-type') : 'image'
                    },
                    button: {
                        text: $el.data('uploader-button-text')
                    },
                    multiple: false  // Set to true to allow multiple files to be selected
                });

                // When an image is selected, run a callback.
                file_frame.on('insert select', function () {
                    // We set multiple to false so only get one image from the uploader
                    var attachment = file_frame.state().get('selection').first().toJSON(),
                                     size = file_frame.$el.find('select.size').val(),
                                     attachmentBySize = attachment.type=='image'?attachment.sizes[size].url:attachment.url;

                    // Do something with attachment.id and/or attachment.url here
                    $el.closest('.themify_builder_input').find('.themify-builder-uploader-input').val(attachmentBySize).trigger('change')
                            .parent().find('.img-placeholder').empty()
                            .html($('<img/>', {src: attachment.type=='image'?attachment.sizes.thumbnail.url:attachment.icon, width: 50, height: 50}))
                            .parent().show();
                });

                // Hide frame menu
                file_frame.on('attach', function () {
                    file_frame.$el.addClass('hide-menu');
                    file_frame.$el.find('.media-button-insert').text($el.data('uploader-button-text'));
                });

                // Finally, open the modal
                file_frame.open();
                event.preventDefault();
            });

            // delete button
            $body.on('click', '.themify-builder-delete-thumb', function (e) {
                $(this).prev().empty().parent().hide();
                $(this).closest('.themify_builder_input').find('.themify-builder-uploader-input').val('');
                e.preventDefault();
            });

            // Media Buttons
            $body.on('click', '.insert-media', function (e) {
                window.wpActiveEditor = $(this).data('editor');
            });
        },
        builderPlupload: function (action_text) {
            var class_new = action_text == 'new_elemn' ? '.plupload-clone' : '',
                    $builderPlupoadUpload = $(".themify-builder-plupload-upload-uic" + class_new);

            if ($builderPlupoadUpload.length > 0) {
                var pconfig = false;
                $builderPlupoadUpload.each(function () {
                    var $this = $(this);
                    var id1 = $this.attr("id");
                    var imgId = id1.replace("themify-builder-plupload-upload-ui", "");

                    pconfig = JSON.parse(JSON.stringify(themify_builder_plupload_init));
                    pconfig["browse_button"] = imgId + pconfig["browse_button"];
                    pconfig["container"] = imgId + pconfig["container"];
                    pconfig["drop_element"] = imgId + pconfig["drop_element"];
                    pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
                    pconfig["multipart_params"]["imgid"] = imgId;
                    //pconfig["multipart_params"]["_ajax_nonce"] = $this.find(".ajaxnonceplu").attr("id").replace("ajaxnonceplu", "");
                    pconfig["multipart_params"]["_ajax_nonce"] = themifyBuilder.tfb_load_nonce;
                    pconfig["multipart_params"]['topost'] = $('input#post_ID').val();
                    if ($this.data('extensions')) {
                        pconfig['filters'][0]['extensions'] = $this.data('extensions');
                    }
                    var uploader = new plupload.Uploader(pconfig);

                    uploader.bind('Init', function (up) {
                    });
                    uploader.init();

                    // a file was added in the queue
                    uploader.bind('FilesAdded', function (up, files) {
                        up.refresh();
                        up.start();
                        $('#themify_builder_alert').addClass('busy').show();
                    });

                    uploader.bind('Error', function (up, error) {
                        var $promptError = $('.prompt-box .show-error');
                        $('.prompt-box .show-login').hide();
                        $promptError.show();

                        if ($promptError.length > 0) {
                            $promptError.html('<p class="prompt-error">' + error.message + '</p>');
                        }
                        $(".overlay, .prompt-box").fadeIn(500);
                    });

                    // a file was uploaded
                    uploader.bind('FileUploaded', function (up, file, response) {
                        var json = JSON.parse(response['response']), status;

                        if ('200' == response['status'] && !json.error) {
                            status = 'done';
                        } else {
                            status = 'error';
                        }

                        $("#themify_builder_alert").removeClass("busy").addClass(status).delay(800).fadeOut(800, function () {
                            $(this).removeClass(status);
                        });

                        if (json.error) {
                            alert(json.error);
                            return;
                        }

                        var response_url = json.large_url ? json.large_url : json.url,
                             response_id = json.id,
                            thumb_url = json.thumb;
                             
                        $this.closest('.themify_builder_input').find('.themify-builder-uploader-input').val(response_url).trigger('change')
                                .parent().find('.img-placeholder').empty()
                                .html($('<img/>', {src: thumb_url, width: 50, height: 50}))
                                .parent().show();
                        // Attach image id to the input
                        $this.closest('.themify_builder_input').find('.themify-builder-uploader-input-attach-id').val(response_id);
                    });

                    $this.removeClass('plupload-clone');
                });
            }
        },
        moduleOptionBuilder: function () {

            // sortable accordion builder
            $(".themify_builder_module_opt_builder_wrap").sortable({
                items: '.themify_builder_row',
                handle: '.themify_builder_row_top',
                axis: 'y',
                placeholder: 'themify_builder_ui_state_highlight',
                start: function (event, ui) {
                    if (typeof tinyMCE !== 'undefined') {
                        $('#tfb_module_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                            var id = $(this).attr('id'),
                                    content = tinymce.get(id).getContent();
                            $(this).data('content', content);
                            tinyMCE.execCommand('mceRemoveEditor', false, id);
                        });
                    }
                },
                stop: function (event, ui) {
                    if (typeof tinyMCE !== 'undefined') {
                        $('#tfb_module_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                            var id = $(this).attr('id');
                            tinyMCE.execCommand('mceAddEditor', false, id);
                            tinymce.get(id).setContent($(this).data('content'));
                        });
                    }
                },
                sort: function (event, ui) {
                    var placeholder_h = ui.item.height();
                    $('.themify_builder_module_opt_builder_wrap .themify_builder_ui_state_highlight').height(placeholder_h);
                }
            });
        },
        rowOptionBuilder: function () {
            $(".themify_builder_row_opt_builder_wrap").sortable({
                items: '.themify_builder_row',
                handle: '.themify_builder_row_top',
                axis: 'y',
                placeholder: 'themify_builder_ui_state_highlight',
                start: function (event, ui) {
                    if (typeof tinyMCE !== 'undefined') {
                        $('#tfb_row_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                            var id = $(this).attr('id'),
                                    content = tinymce.get(id).getContent();
                            $(this).data('content', content);
                            tinyMCE.execCommand('mceRemoveEditor', false, id);
                        });
                    }
                },
                stop: function (event, ui) {
                    if (typeof tinyMCE !== 'undefined') {
                        $('#tfb_row_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                            var id = $(this).attr('id');
                            tinyMCE.execCommand('mceAddEditor', false, id);
                            tinymce.get(id).setContent($(this).data('content'));
                        });
                    }
                },
                sort: function (event, ui) {
                    var placeholder_h = ui.item.height();
                    $('.themify_builder_row_opt_builder_wrap .themify_builder_ui_state_highlight').height(placeholder_h);
                }
            });
        },
        moduleOptAddRow: function (e) {
            var self = ThemifyPageBuilder,
                    parent = $(this).parent().prev(),
                    template = parent.find('.themify_builder_row').first().clone(),
                    row_count = $('.themify_builder_row_js_wrapper').find('.themify_builder_row:visible').length + 1,
                    number = row_count + Math.floor(Math.random() * 9);

            // clear form data
            template.removeClass('collapsed').find('.themify_builder_row_content').show();
            template.find('.themify-builder-radio-dnd').each(function (i) {
                var oriname = $(this).attr('name');
                $(this).attr('name', oriname + '_' + row_count).not(':checked').prop('checked', false);
                $(this).attr('id', oriname + '_' + row_count + '_' + i);
                $(this).next('label').attr('for', oriname + '_' + row_count + '_' + i);
            });

            template.find('.themify-layout-icon a').removeClass('selected');

            template.find('.thumb_preview').each(function () {
                $(this).find('.img-placeholder').html('').parent().hide();
            });
            template.find('input[type=text], textarea').each(function () {
                $(this).val('');
            });
            template.find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                $(this).addClass('clone');
            });
            template.find('.themify-builder-plupload-upload-uic').each(function (i) {
                $(this).attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-upload-ui');
                $(this).find('input[type=button]').attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-browse-button');
                $(this).addClass('plupload-clone');
            });

            // Fix color picker input
            template.find('.builderColorSelectInput').each(function () {
                var thiz = $(this),
                        input = thiz.clone().val(''),
                        parent = thiz.closest('.themify_builder_field');
                thiz.prev().minicolors('destroy').removeAttr('maxlength');
                parent.find('.colordisplay').wrap('<div class="themify_builder_input" />').before('<span class="builderColorSelect"><span></span></span>').after(input);
                self.setColorPicker(parent);
            });

            $(template).appendTo(parent).show();

            $('#tfb_module_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child.clone').each(function (i) {
                var element = $(this),
                        parent_child = element.closest('.themify_builder_input');

                $(this).closest('.wp-editor-wrap').remove();

                var oriname = element.attr('name');
                element.attr('id', oriname + '_' + row_count + number + '_' + i);
                element.attr('class').replace('wp-editor-area', '');

                element.appendTo(parent_child).wrap('<div class="wp-editor-wrap"/>');

            });

            if (e.which) {
                self.addNewWPEditor();
                self.builderPlupload('new_elemn');
            }

            e.preventDefault();
        },
        rowOptAddRow: function (e) {
            var self = ThemifyPageBuilder,
                    parent = $(this).parent().prev(),
                    template = parent.find('.themify_builder_row').first().clone(),
                    row_count = $('.themify_builder_row_js_wrapper').find('.themify_builder_row:visible').length + 1,
                    number = row_count + Math.floor(Math.random() * 9);

            // clear form data
            template.removeClass('collapsed').find('.themify_builder_row_content').show();
            template.find('.themify-builder-radio-dnd').each(function (i) {
                var oriname = $(this).attr('name');
                $(this).attr('name', oriname + '_' + row_count).not(':checked').prop('checked', false);
                $(this).attr('id', oriname + '_' + row_count + '_' + i);
                $(this).next('label').attr('for', oriname + '_' + row_count + '_' + i);
            });

            template.find('.themify-layout-icon a').removeClass('selected');

            template.find('.thumb_preview').each(function () {
                $(this).find('.img-placeholder').html('').parent().hide();
            });
            template.find('input[type="text"], textarea').each(function () {
                $(this).val('');
            });
            template.find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                $(this).addClass('clone');
            });
            template.find('.themify-builder-plupload-upload-uic').each(function (i) {
                $(this).attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-upload-ui');
                $(this).find('input[type="button"]').attr('id', 'pluploader_' + row_count + number + i + 'themify-builder-plupload-browse-button');
                $(this).addClass('plupload-clone');
            });

            // Fix color picker input
            template.find('.builderColorSelectInput').each(function () {
                var thiz = $(this),
                        input = thiz.clone().val(''),
                        parent = thiz.closest('.themify_builder_field');
                thiz.prev().minicolors('destroy').removeAttr('maxlength');
                parent.find('.colordisplay').wrap('<div class="themify_builder_input" />').before('<span class="builderColorSelect"><span></span></span>').after(input);
                self.setColorPicker(parent);
            });

            $(template).appendTo(parent).show();

            $('#tfb_row_settings').find('.tfb_lb_wp_editor.tfb_lb_option_child.clone').each(function (i) {
                var element = $(this),
                    parent_child = element.closest('.themify_builder_input');

                $(this).closest('.wp-editor-wrap').remove();

                var oriname = element.attr('name');
                element.attr('id', oriname + '_' + row_count + number + '_' + i);
                element.attr('class').replace('wp-editor-area', '');

                element.appendTo(parent_child).wrap('<div class="wp-editor-wrap"/>');

            });

            if (e.which) {
                self.addNewWPEditor();
                self.builderPlupload('new_elemn');
            }

            e.preventDefault();
        },
        deleteEmptyModule: function () {
            $('#themify_builder_row_wrapper').find('.themify_builder_module').each(function () {
                if ($.trim($(this).find('.themify_module_settings').find('script[type="text/json"]').text()).length <= 2) {
                    $(this).remove();
                }
            });
        },
        openGallery: function () {

            var clone = wp.media.gallery.shortcode,
                    $self = this,
                    file_frame;
            $('body').on('click', '.tf-gallery-btn', function (event) {
                var shortcode_val = $(this).closest('.themify_builder_input').find('.tf-shortcode-input');

                // Create the media frame.
                file_frame = wp.media.frames.file_frame = wp.media({
                    frame: 'post',
                    state: 'gallery-edit',
                    title: wp.media.view.l10n.editGalleryTitle,
                    editing: true,
                    multiple: true,
                    selection: false
                });

                wp.media.gallery.shortcode = function (attachments) {
                    var props = attachments.props.toJSON(),
                            attrs = _.pick(props, 'orderby', 'order');

                    if (attachments.gallery)
                        _.extend(attrs, attachments.gallery.toJSON());

                    attrs.ids = attachments.pluck('id');

                    // Copy the `uploadedTo` post ID.
                    if (props.uploadedTo)
                        attrs.id = props.uploadedTo;

                    // Check if the gallery is randomly ordered.
                    if (attrs._orderbyRandom)
                        attrs.orderby = 'rand';
                    delete attrs._orderbyRandom;

                    // If the `ids` attribute is set and `orderby` attribute
                    // is the default value, clear it for cleaner output.
                    if (attrs.ids && 'post__in' === attrs.orderby)
                        delete attrs.orderby;

                    // Remove default attributes from the shortcode.
                    _.each(wp.media.gallery.defaults, function (value, key) {
                        if (value === attrs[ key ])
                            delete attrs[ key ];
                    });

                    var shortcode = new wp.shortcode({
                        tag: 'gallery',
                        attrs: attrs,
                        type: 'single'
                    });

                    shortcode_val.val(shortcode.string());

                    wp.media.gallery.shortcode = clone;
                    return shortcode;
                }

                file_frame.on('update', function (selection) {
                    var shortcode = wp.media.gallery.shortcode(selection).string().slice(1, -1);
                    shortcode_val.val('[' + shortcode + ']');
                    $self.setShortcodePreview(selection.models,shortcode_val);
                });

                if ($.trim(shortcode_val.val()).length > 0) {
                    file_frame = wp.media.gallery.edit($.trim(shortcode_val.val())); 
                    file_frame.state('gallery-edit').on('update', function (selection) {
                        var shortcode = wp.media.gallery.shortcode(selection).string().slice(1, -1);
                        shortcode_val.val('[' + shortcode + ']');
                        $self.setShortcodePreview(selection.models,shortcode_val);
                    });
                }
                else
                {
                    file_frame.open();
                    $('.media-menu').find('.media-menu-item').last().trigger('click');
                }
                event.preventDefault();
            });

        },
        setShortcodePreview:function($images,$input){
            var $preview = $input.next('.themify_builder_shortcode_preview'),
                $html = '';
            if($preview.length===0){
                $input.after('<div class="themify_builder_shortcode_preview"></div>');
                $preview = $input.next('.themify_builder_shortcode_preview');
            }
            for(var $i in $images){
                var attachment = $images[$i].attributes,
                    $url = attachment.sizes.thumbnail? attachment.sizes.thumbnail.url: attachment.url;
                $html+='<img src="'+$url+'" width="50" height="50" />';
            }
            $preview.html($html);  
        },
        getShortcodePreview:function($input,$value){

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                data:
                    {
                        action: 'tfb_load_shortcode_preview',
                        tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                        shortcode:$value
                    },
                success: function (data) {
                    if(data){
                        $input.after(data);
                    }
                }
            });
        },
        addNewWPEditor: function () {
            var self = ThemifyPageBuilder;

            $('#tfb_module_settings').find('.tfb_lb_wp_editor.clone').each(function (i) {
                var element = $(this),
                        element_val = element.val(),
                        parent = element.closest('.themify_builder_input');

                $(this).closest('.wp-editor-wrap').remove();

                var oriname = element.attr('name'),
                        this_option_id_temp = element.attr('id'),
                        this_class = element.attr('class').replace('wp-editor-area', '').replace('clone', '');

                $.ajax({
                    type: "POST",
                    url: themifyBuilder.ajaxurl,
                    dataType: 'html',
                    data:
                            {
                                action: 'tfb_add_wp_editor',
                                tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                                txt_id: this_option_id_temp,
                                txt_class: this_class,
                                txt_name: oriname,
                                txt_val: element_val
                            },
                    success: function (data) {
                        var $newElems = $(data),
                                this_option_id_clone = $newElems.find('.tfb_lb_wp_editor').attr('id');

                        $newElems.appendTo(parent);

                        self.initQuickTags(this_option_id_clone);
                        if (typeof tinyMCE !== 'undefined') {
                            self.initNewEditor(this_option_id_clone);
                        }
                    }
                });

            });
        },
        moduleActions: function () {
            var $body = $('body'),
                $self = ThemifyPageBuilder;
            $body.on('change', '.module-widget-select-field', function () {
                var $seclass = $(this).val(),
                        id_base = $(this).find(':selected').data('idbase');

                $.ajax({
                    type: "POST",
                    url: themifyBuilder.ajaxurl,
                    dataType: 'html',
                    data:
                            {
                                action: 'module_widget_get_form',
                                tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                                load_class: $seclass,
                                id_base: id_base
                            },
                    success: function (data) {
                        var $newElems = $(data);

                        $('.module-widget-form-placeholder').html($newElems);
                        $('#themify_builder_lightbox_container').each(function () {
                            var $this = $(this).find('#instance_widget');
                            $this.find('select').wrap('<div class="selectwrapper"></div>');
                        });
                        $('.selectwrapper').click(function () {
                            $(this).toggleClass('clicked');
                        });
                    }
                });
            });

            $body.on('editing_module_option', function (e, settings) {
                var $field = $('#tfb_module_settings .tfb_lb_option.module-widget-select-field');
                if ($field.length == 0)
                    return;

                var $seclass = $field.val(),
                        id_base = $field.find(':selected').data('idbase'),
                        $instance = settings.instance_widget;

                $.ajax({
                    type: "POST",
                    url: themifyBuilder.ajaxurl,
                    dataType: 'html',
                    data:
                            {
                                action: 'module_widget_get_form',
                                tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                                load_class: $seclass,
                                id_base: id_base,
                                widget_instance: $instance
                            },
                    success: function (data) {
                        var $newElems = $(data);
                        $('.module-widget-form-placeholder').html($newElems);
                    }
                });
            });
        },
        newRowAvailable: function () {
            var self = ThemifyPageBuilder;

            $('.themify_builder_row_js_wrapper').each(function () {
                var $container = $(this),
                        $parent = $container.find('.themify_builder_row:visible'),
                        tmpl = wp.template('builder_row'),
                        $template = $(tmpl({}));

                $parent.each(function () {
                    if ($(this).find('.themify_builder_module').length != 0) {
                        return;
                    }

                    var removeThis = true;

                    var column_data_styling = $(this).find('.column-data-styling');
                    var data_styling = null;

                    column_data_styling.each(function () {
                        if (!removeThis) {
                            return;
                        }

                        data_styling = $.parseJSON( $(this).attr('data-styling') );

                        if ((typeof data_styling === 'array' && data_styling.length > 0) || !$.isEmptyObject(data_styling)) {
                            removeThis = false;
                        }
                    });

                    data_styling = $.parseJSON( $(this).find('.row-data-styling').attr('data-styling') );

                    if (removeThis && (typeof data_styling === 'string' || $.isEmptyObject(data_styling))) {
                        $(this).remove();
                    }
                });

                if ($parent.find('.themify_builder_module').length > 0 || $container.find('.themify_builder_row:visible').length == 0) {
                    $template.appendTo($container);
                }
            });
        },
        showLoader: function (stats) {
            if (stats == 'show') {
                $('#themify_builder_alert').addClass('busy').show();
            }
            else if (stats == 'spinhide') {
                $("#themify_builder_alert").delay(800).fadeOut(800, function () {
                    $(this).removeClass('busy');
                });
            }
            else {
                $("#themify_builder_alert").removeClass("busy").addClass('done').delay(800).fadeOut(800, function () {
                    $(this).removeClass('done');
                });
            }
        },
        duplicatePage: function (e) {
            var self = ThemifyPageBuilder,
                    reply = confirm(themifyBuilder.confirm_on_duplicate_page);
            if (reply) {
                self.saveData(true, function () {
                    self.duplicatePageAjax();
                });
            } else {
                self.duplicatePageAjax();
            }

            e.preventDefault();
        },
        duplicatePageAjax: function () {
            var self = ThemifyPageBuilder;
            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data:
                        {
                            action: 'tfb_duplicate_page',
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            tfb_post_id: $('input#post_ID').val(),
                            tfb_is_admin: 1
                        },
                beforeSend: function (xhr) {
                    self.showLoader('show');
                },
                success: function (data) {
                    self.showLoader('hide');
                    var new_url = data.new_url.replace(/\&amp;/g, '&');
                    window.location.href = new_url;
                }
            });
        },
        optionRow: function (e) {
            e.preventDefault();

            var self = ThemifyPageBuilder,
                    $this = $(this),
                    $options = $.parseJSON( $this.closest('.themify_builder_row').find('.row-data-styling').attr('data-styling') );

            var callback = function () {
                if ('object' === typeof $options && $options != null) {
                    if('undefined' !== typeof $options.background_slider){
                        self.getShortcodePreview($('#background_slider'),$options.background_slider);
                    }
                    $.each($options, function (id, val) {
                        $('#tfb_row_settings').find('#' + id).val(val);
                    });

                    $('#tfb_row_settings').find('.tfb_lb_option[type="radio"]').each(function () {
                        var id = $(this).prop('name');
                        if ('undefined' !== typeof $options[id]) {
                            if ($(this).val() === $options[id]) {
                                $(this).prop('checked', true);
                            }
                        }
                    });
                }

                // image field
                $('#tfb_row_settings').find('.themify-builder-uploader-input').each(function () {
                    var img_field = $(this).val(),
                            img_thumb = $('<img/>', {src: img_field, width: 50, height: 50});

                    if (img_field != '') {
                        $(this).parent().find('.img-placeholder').empty().html(img_thumb);
                    }
                    else {
                        $(this).parent().find('.thumb_preview').hide();
                    }
                });

                // builder
                $('#tfb_row_settings').find('.themify_builder_row_js_wrapper').each(function () {
                    var $this_option = $(this),
                        this_option_id = $this_option.attr('id'),
                        $found_element = $options ? $options[this_option_id] : false;

                    if ($found_element) {
                        var row_append = 0;
                        if ($found_element.length > 0) {
                            row_append = $found_element.length - 1;
                        }

                        // add new row
                        for (var i = 0; i < row_append; i++) {
                            $this_option.parent().find('.add_new a').first().trigger('click');
                        }

                        $this_option.find('.themify_builder_row').each(function (r) {
                            $(this).find('.tfb_lb_option_child').each(function (i) {
                                    var $this_option_child = $(this),
                                        this_option_id_real = $this_option_child.attr('id'),
                                        this_option_id_child = $this_option_child.hasClass('tfb_lb_wp_editor') ? $this_option_child.attr('name') : $this_option_child.data('input-id');
                                        if(!this_option_id_child){
                                            this_option_id_child = this_option_id_real;
                                        }
                                        var $found_element_child = $found_element[r]['' + this_option_id_child + ''];

                                if ($this_option_child.hasClass('themify-builder-uploader-input')) {
                                    var img_field = $found_element_child,
                                        img_thumb = $('<img/>', {src: img_field, width: 50, height: 50});

                                    if (img_field != '' && img_field != undefined) {
                                        $this_option_child.val(img_field);
                                        $this_option_child.parent().find('.img-placeholder').empty().html(img_thumb).parent().show();
                                    }
                                    else {
                                        $this_option_child.parent().find('.thumb_preview').hide();
                                    }
                                }
                                else if ($this_option_child.is('input, textarea, select')) {
                                    $this_option_child.val($found_element_child);
                                }
                            });
                        });
                    }
                });

                // colorpicker
                self.setColorPicker();

                // @backward-compatibility
                if (jQuery('#background_video').val() !== '' && $('#background_type input:checked').length == 0) {
                    $('#background_type_video').trigger('click');
                } else if ($('#background_type input:checked').length == 0) {
                    $('#background_type_image').trigger('click');
                }

                $('.tf-option-checkbox-enable input:checked').trigger('click');

                // plupload init
                self.builderPlupload('normal');

                /* checkbox field type */
                $('.themify-checkbox').each(function () {
                    var id = $(this).attr('id');
                    if ($options && $options[id]) {
                        $options[id] = typeof $options[id] == 'string' ? [$options[id]] : $options[id]; // cast the option value as array
                        // First unchecked all to fixed checkbox has default value.
                        $(this).find('.tf-checkbox').prop('checked', false);
                        // Set the values
                        $.each($options[id], function (i, v) {
                            $('.tf-checkbox[value="' + v + '"]').prop('checked', true);
                        });
                    }
                });

                $('body').trigger('editing_row_option', [$options]);

                // builder drag n drop init
                self.rowOptionBuilder();

                // "Apply all" // apply all init
                self.applyAll_init();
            };

            ThemifyBuilderCommon.highlightRow($this.closest('.themify_builder_row'));

            ThemifyBuilderCommon.Lightbox.open({ loadMethod: 'inline', templateID: 'builder_form_row' }, callback);
        },
        optionColumn: function (e) {
            e.preventDefault();

            var self = ThemifyPageBuilder,
                    $this = $(this),
                    $options = $.parseJSON( $this.closest('.themify_builder_col').children('.column-data-styling').attr('data-styling') );

            var callback = function () {
                if ('object' === typeof $options && $options != null) {
                    if('undefined' !== typeof $options.background_slider){
                        self.getShortcodePreview($('#background_slider'),$options.background_slider);
                    }
                    $.each($options, function (id, val) {
                        $('#tfb_column_settings').find('#' + id).val(val);
                    });

                    $('#tfb_column_settings').find('.tfb_lb_option[type="radio"]').each(function () {
                        var id = $(this).prop('name');
                        if ('undefined' !== typeof $options[id]) {
                            if ($(this).val() === $options[id]) {
                                $(this).prop('checked', true);
                            }
                        }
                    });
                }

                // image field
                $('#tfb_column_settings').find('.themify-builder-uploader-input').each(function () {
                    var img_field = $(this).val(),
                            img_thumb = $('<img/>', {src: img_field, width: 50, height: 50});

                    if (img_field != '') {
                        $(this).parent().find('.img-placeholder').empty().html(img_thumb);
                    }
                    else {
                        $(this).parent().find('.thumb_preview').hide();
                    }
                });

                // colorpicker
                self.setColorPicker();

                // @backward-compatibility
                if (jQuery('#background_video').val() !== '' && $('#background_type input:checked').length == 0) {
                    $('#background_type_video').trigger('click');
                } else if ($('#background_type input:checked').length == 0) {
                    $('#background_type_image').trigger('click');
                }

                $('.tf-option-checkbox-enable input:checked').trigger('click');

                // plupload init
                self.builderPlupload('normal');

                /* checkbox field type */
                $('.themify-checkbox').each(function () {
                    var id = $(this).attr('id');
                    if ($options[id]) {
                        $options[id] = typeof $options[id] == 'string' ? [$options[id]] : $options[id]; // cast the option value as array
                        // First unchecked all to fixed checkbox has default value.
                        $(this).find('.tf-checkbox').prop('checked', false);
                        // Set the values
                        $.each($options[id], function (i, v) {
                            $('.tf-checkbox[value="' + v + '"]').prop('checked', true);
                        });
                    }
                });

                $('body').trigger('editing_column_option', [$options]);

                // "Apply all" // apply all init
                self.applyAll_init();
            };

            ThemifyBuilderCommon.highlightColumn($this.closest('.themify_builder_col'));
            ThemifyBuilderCommon.highlightRow($this.closest('.themify_builder_row'));

            ThemifyBuilderCommon.Lightbox.open({ loadMethod: 'inline', templateID: 'builder_form_column' }, callback);
        },
        rowSaving: function (e) {
            e.preventDefault();
            var self = ThemifyPageBuilder,
                $active_row_settings = $('.current_selected_row .row-data-styling'),
                temp_appended_data = $('#tfb_row_settings .tfb_lb_option').serializeObject(),
                startValue = self.builderContainer.innerHTML;

            $('#tfb_row_settings').find('.themify_builder_row_js_wrapper').each(function () {
                var this_option_id = $(this).attr('id'),
                    row_items = [];
                
                $(this).find('.themify_builder_row').each(function () {
                    var temp_rows = {};
                    
                    $(this).find('.tfb_lb_option_child').each(function () {
                        var option_value_child,
                            this_option_id_child = $(this).data('input-id');
                            if(!this_option_id_child){
                                this_option_id_child = $(this).attr('id');
                            }
                            
                        option_value_child = $(this).val();

                        if (option_value_child) {
                            temp_rows[this_option_id_child] = option_value_child;
                        }
                    });

                    row_items.push(temp_rows);
                });

                if (row_items) {
                    temp_appended_data[this_option_id] = row_items;
                }
            });

            console.log(temp_appended_data, 'temp_appended_data');
            $active_row_settings.attr('data-styling', JSON.stringify( temp_appended_data ) );

            // Save data
            self.saveData(true, function () {
                ThemifyBuilderCommon.Lightbox.close();

                // logs action
                var newValue = self.builderContainer.innerHTML;
                if ( startValue !== newValue ) {
                    ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue]);
                }
            }, 'cache');

            self.editing = true;
        },
        columnSaving: function (e) {
            e.preventDefault();
            var self = ThemifyPageBuilder,
                $active_column_settings = $('.current_selected_column').children('.column-data-styling'),
                temp_appended_data = $('#tfb_column_settings .tfb_lb_option').serializeObject(),
                startValue = self.builderContainer.innerHTML;

            $active_column_settings.attr('data-styling', JSON.stringify(temp_appended_data) );

            // Save data
            self.saveData(true, function () {
                ThemifyBuilderCommon.Lightbox.close();

                // logs action
                var newValue = self.builderContainer.innerHTML;
                if ( startValue !== newValue ) {
                    ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue]);
                }
            }, 'cache');

            self.editing = true;
        },
        _gridMenuClicked: function (event) {
            event.preventDefault();
            var set = $(this).data('grid'),
                handle = $(this).data('handle'), $base, is_sub_row = false,
                startValue = ThemifyPageBuilder.builderContainer.innerHTML;

            $(this).closest('.themify_builder_grid_list').find('.selected').removeClass('selected');
            $(this).closest('li').addClass('selected');

            switch (handle) {
                case 'module':
                    var sub_row_tmpl = wp.template('builder_sub_row'),
                            tmpl_sub_row = sub_row_tmpl({placeholder: themifyBuilder.dropPlaceHolder, newclass: 'col-full'}),
                            $mod_clone = $(this).closest('.active_module').clone();
                    $mod_clone.find('.grid_menu').remove();

                    $base = $(tmpl_sub_row).find('.themify_module_holder').append($mod_clone).end()
                            .insertAfter($(this).closest('.active_module')).find('.themify_builder_sub_row_content');

                    $(this).closest('.active_module').remove();
                    break;

                case 'sub_row':
                    is_sub_row = true;
                    $base = $(this).closest('.themify_builder_sub_row').find('.themify_builder_sub_row_content');
                    break;

                default:
                    $base = $(this).closest('.themify_builder_row').find('.themify_builder_row_content');
            }

            // Hide the dropdown
            $(this).closest('.themify_builder_grid_list_wrapper').hide();

            $.each(set, function (i, v) {
                if ($base.children('.themify_builder_col').eq(i).length > 0) {
                    $base.children('.themify_builder_col').eq(i).removeClass(ThemifyPageBuilder.clearClass).addClass('col' + v);
                } else {
                    // Add column
                    ThemifyPageBuilder._addNewColumn({placeholder: themifyBuilder.dropPlaceHolder, newclass: 'col' + v}, $base);
                }
            });

            // remove unused column
            if (set.length < $base.children().length) {
                $base.children('.themify_builder_col').eq(set.length - 1).nextAll().each(function () {
                    // relocate active_module
                    var modules = $(this).find('.themify_module_holder').first().clone();
                    modules.find('.empty_holder_text').remove();
                    modules.children().appendTo($(this).prev().find('.themify_module_holder').first());
                    $(this).remove(); // finally remove it
                });
            }

            $base.children().removeClass('first last');
            $base.children().first().addClass('first');
            $base.children().last().addClass('last');

            // remove sub_row when fullwidth column
            if (is_sub_row && set[0] == '-full') {
                var $move_modules = $base.find('.active_module').clone();
                $move_modules.insertAfter($(this).closest('.themify_builder_sub_row'));
                $(this).closest('.themify_builder_sub_row').remove();
            }

            ThemifyPageBuilder.equalHeight();
            ThemifyPageBuilder.moduleEvents();

            // Log the action
            var newValue = ThemifyPageBuilder.builderContainer.innerHTML;
            if ( startValue !== newValue ) {
                ThemifyBuilderCommon.undoManager.events.trigger('change', [startValue, newValue]);
            }
        },
        _addNewColumn: function (params, $context) {
            var template_func = wp.template('builder_column'),
                    template = template_func(params);
            $context.append($(template));
        },
        _gridHover: function (event) {
            event.stopPropagation();
            if (event.type == 'touchend') {
                var $column_menu = $(this).find('.themify_builder_grid_list_wrapper');
                if ($column_menu.is(':hidden')) {
                    $column_menu.show();
                } else {
                    $column_menu.hide();
                }
            } else if (event.type == 'mouseenter') {
                $(this).find('.themify_builder_grid_list_wrapper').stop(true, true).show();
            } else if (event.type == 'mouseleave' && (event.toElement || event.relatedTarget)) {
                $(this).find('.themify_builder_grid_list_wrapper').stop(true, true).hide();
            }
        },
        _gutterChange: function (event) {
            var handle = $(this).data('handle');

            switch (handle) {
                case 'sub_row':
                    $(this).closest('.themify_builder_sub_row').data('gutter', this.value);
                    break;

                case 'row':
                    $(this).closest('.themify_builder_row').data('gutter', this.value);
                    break;
            }

            // Hide the dropdown
            $(this).closest('.themify_builder_grid_list_wrapper').hide();
        },
        _selectedGridMenu: function () {
            $('.grid_menu').each(function () {
                var handle = $(this).data('handle'),
                        grid_base = [], $base;
                if (handle == 'module')
                    return;
                switch (handle) {
                    case 'sub_row':
                        $base = $(this).closest('.themify_builder_sub_row').find('.themify_builder_sub_row_content');
                        break;

                    default:
                        $base = $(this).closest('.themify_builder_row').find('.themify_builder_row_content');
                }

                $base.children().each(function () {
                    grid_base.push(ThemifyPageBuilder._getColClass($(this).prop('class').split(' ')));
                });

                $(this).find('.grid-layout-' + grid_base.join('-')).closest('li').addClass('selected');

            });
        },
        _equalColumnHeightChanged: function () {
            var handle = $(this).data('handle');
            if (handle == 'module')
                return;

            var $row = null;

            if (handle == 'sub_row') { // sub-rows
                $row = $(this).closest('.themify_builder_sub_row');
            } else { // rows
                $row = $(this).closest('.themify_builder_row');
            }

            // enable equal column height
            if (this.checked) {
                $row.data('equal-column-height', 'equal-column-height');
            } else { // disable equal column height
                $row.data('equal-column-height', '');
            }
        },
        _getColClass: function (classes) {
            var matches = ThemifyPageBuilder.clearClass.split(' '),
                    spanClass = null;

            for (var i = 0; i < classes.length; i++) {
                if ($.inArray(classes[i], matches) > -1) {
                    spanClass = classes[i].replace('col', '');
                }
            }
            return spanClass;
        },
        _subRowDelete: function (event) {
            event.preventDefault();
            if (confirm(themifyBuilder.subRowDeleteConfirm)) {
                $(this).closest('.themify_builder_sub_row').remove();
                ThemifyPageBuilder.newRowAvailable();
                ThemifyPageBuilder.equalHeight();
                ThemifyPageBuilder.moduleEvents();
                ThemifyPageBuilder.editing = true;
            }
        },
        _subRowDuplicate: function (event) {
            event.preventDefault();
            $(this).closest('.themify_builder_sub_row').clone().insertAfter($(this).closest('.themify_builder_sub_row'));
            ThemifyPageBuilder.equalHeight();
            ThemifyPageBuilder.moduleEvents();
            ThemifyPageBuilder.editing = true;
        },

        // Undo/Redo Functionality
        btnUndo: document.querySelector('.js-themify-builder-undo-btn'),
        btnRedo: document.querySelector('.js-themify-builder-redo-btn'),
        actionUndo: function( event ) {
            event.preventDefault();
            if (this.classList.contains('disabled')) return;
            ThemifyBuilderCommon.undoManager.instance.undo();
            ThemifyPageBuilder.updateUndoBtns();
        },
        actionRedo: function( event ) {
            event.preventDefault();
            if (this.classList.contains('disabled')) return;
            ThemifyBuilderCommon.undoManager.instance.redo();
            ThemifyPageBuilder.updateUndoBtns();
        },
        updateUndoBtns: function() {
            if ( ThemifyBuilderCommon.undoManager.instance.hasUndo() ) {
                ThemifyPageBuilder.btnUndo.classList.remove('disabled');
            } else {
                ThemifyPageBuilder.btnUndo.classList.add('disabled');
            }

            if ( ThemifyBuilderCommon.undoManager.instance.hasRedo() ) {
                ThemifyPageBuilder.btnRedo.classList.remove('disabled');
            } else {
                ThemifyPageBuilder.btnRedo.classList.add('disabled');
            }
        },
        undoManagerCallback: function(){
            ThemifyPageBuilder.updateUndoBtns();
            ThemifyPageBuilder.moduleEvents();
            ThemifyPageBuilder.equalHeight();
            $('.themify_builder_grid_list_wrapper').hide();
        },
        builderImportPage: function (e) {
            e.preventDefault();
            ThemifyPageBuilder.builderImport('page');
        },
        builderImportPost: function (e) {
            e.preventDefault();
            ThemifyPageBuilder.builderImport('post');
        },
        builderImportSubmit: function (e) {
            e.preventDefault();

            var postData = $(this).closest('form').serialize();

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data:
                        {
                            action: 'builder_import_submit',
                            nonce: themifyBuilder.tfb_load_nonce,
                            data: postData,
                            importTo: $('input#post_ID').val()
                        },
                success: function (data) {
                    ThemifyBuilderCommon.Lightbox.close();
                    window.location.reload();
                }
            });
        },
        builderImport: function (imType) {
            var options = {
                dataType: 'html',
                data: {
                    action: 'builder_import',
                    type: imType
                }
            };
            ThemifyBuilderCommon.Lightbox.open(options, null);
        },
        builderImportFile: function (e) {
            e.preventDefault();
            var self = ThemifyPageBuilder,
                    options = {
                        dataType: 'html',
                        data: {
                            action: 'builder_import_file'
                        }
                    },
            callback = function () {
                self.builderImportPlupload();
            };

            if (confirm(themifyBuilder.importFileConfirm)) {
                ThemifyBuilderCommon.Lightbox.open(options, callback);
            }
        },
        builderImportPlupload: function () {
            var $builderPluploadUpload = $(".themify-builder-plupload-upload-uic");

            if ($builderPluploadUpload.length > 0) {
                var pconfig = false;
                $builderPluploadUpload.each(function () {
                    var $this = $(this),
                            id1 = $this.attr("id"),
                            imgId = id1.replace("themify-builder-plupload-upload-ui", "");

                    pconfig = JSON.parse(JSON.stringify(themify_builder_plupload_init));

                    pconfig["browse_button"] = imgId + pconfig["browse_button"];
                    pconfig["container"] = imgId + pconfig["container"];
                    pconfig["drop_element"] = imgId + pconfig["drop_element"];
                    pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
                    pconfig["multipart_params"]["imgid"] = imgId;
                    pconfig["multipart_params"]["_ajax_nonce"] = themifyBuilder.tfb_load_nonce;
                    ;
                    pconfig["multipart_params"]['topost'] = $('input#post_ID').val();

                    var uploader = new plupload.Uploader(pconfig);

                    uploader.bind('Init', function (up) {
                    });

                    uploader.init();

                    // a file was added in the queue
                    uploader.bind('FilesAdded', function (up, files) {
                        up.refresh();
                        up.start();
                        ThemifyBuilderCommon.showLoader('show');
                    });

                    uploader.bind('Error', function (up, error) {
                        var $promptError = $('.prompt-box .show-error');
                        $('.prompt-box .show-login').hide();
                        $promptError.show();

                        if ($promptError.length > 0) {
                            $promptError.html('<p class="prompt-error">' + error.message + '</p>');
                        }
                        $(".overlay, .prompt-box").fadeIn(500);
                    });

                    // a file was uploaded
                    uploader.bind('FileUploaded', function (up, file, response) {
                        var json = JSON.parse(response['response']), status;

                        if ('200' == response['status'] && !json.error) {
                            status = 'done';
                        } else {
                            status = 'error';
                        }

                        $("#themify_builder_alert").removeClass("busy").addClass(status).delay(800).fadeOut(800, function () {
                            $(this).removeClass(status);
                        });

                        if (json.error) {
                            alert(json.error);
                            return;
                        }

                        $('#themify_builder_alert').promise().done(function () {
                            ThemifyBuilderCommon.Lightbox.close();
                            window.location.reload();
                        });

                    });

                });
            }
        },
        builderLoadLayout: function (event) {
            event.preventDefault();
            var options = {
                dataType: 'html',
                data: {
                    action: 'tfb_load_layout'
                }
            };

            ThemifyBuilderCommon.Lightbox.open(options, null);
        },
        builderSaveLayout: function (event) {
            event.preventDefault();
            var options = {
                data: {
                    action: 'tfb_custom_layout_form',
                    postid: $('input#post_ID').val()
                }
            },
            callback = function () {
                // plupload init
                ThemifyPageBuilder.builderPlupload('normal');
            };
            ThemifyBuilderCommon.Lightbox.open(options, callback);
        },
        templateSelected: function (event) {
            event.preventDefault();

            var self = ThemifyPageBuilder,
                    $this = $(this);
            var options = {
                buttons: {
                    no: {
                        label: 'Replace Existing Layout'
                    },
                    yes: {
                        label: 'Append Existing Layout'
                    }
                }
            };

            ThemifyBuilderCommon.LiteLightbox.confirm( themifyBuilder.confirm_template_selected, function( response ){
                console.log( response, 'response');
                var action = '';
                if ( 'no' == response ) {
                    action = 'tfb_set_layout';
                } else {
                    action = 'tfb_append_layout';
                }
                $.ajax({
                    type: "POST",
                    url: themifyBuilder.ajaxurl,
                    dataType: 'json',
                    data: {
                        action: action,
                        nonce: themifyBuilder.tfb_load_nonce,
                        layout_slug: $this.data('layout-slug'),
                        current_builder_id: $('input#post_ID').val(),
                        builtin_layout: $this.data('builtin')
                    },
                    success: function (data) {
                        ThemifyBuilderCommon.Lightbox.close();
                        if (data.status == 'success') {
                            window.location.reload()
                        } else {
                            alert(data.msg);
                        }
                    }
                });
            }, options );
        },
        saveAsLayout: function (event) {
            event.preventDefault();

            var submit_data = $('#tfb_save_layout_form').serialize();
            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data: {
                    action: 'tfb_save_custom_layout',
                    nonce: themifyBuilder.tfb_load_nonce,
                    form_data: submit_data
                },
                success: function (data) {
                    if (data.status == 'success') {
                        ThemifyBuilderCommon.Lightbox.close();
                    } else {
                        alert(data.msg);
                    }
                }
            });
        }
    };

    // Initialize Builder
    $(function () {
        var _original_icl_copy_from_original;
        ThemifyPageBuilder.init();

        // WPML compat
        if (typeof window.icl_copy_from_original == 'function') {
            _original_icl_copy_from_original = window.icl_copy_from_original;
            // override "copy content" button action to load Builder modules as well
            window.icl_copy_from_original = function (lang, id) {
                _original_icl_copy_from_original(lang, id);
                jQuery.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {
                        action: 'themify_builder_icl_copy_from_original',
                        source_page_id: id,
                        source_page_lang: lang
                    },
                    success: function (data) {
                        if (data != '-1') {
                            jQuery('#page-builder .themify_builder.themify_builder_admin').empty().append(data).contents().unwrap();

                            // redo module events
                            ThemifyPageBuilder.moduleEvents();
                        }
                    }
                });
            };
        }
		
        // fixed builder module panel while scrolling
        $(window).load(function () {

            var $panel = $('#page-builder .themify_builder_module_panel'),
                    $top = 0,
                    $scrollTimer = null,
                    $panel_top = 0,
                    $wpadminbar = $('#wpadminbar'),
                    $wpadminbarHeight = $wpadminbar.outerHeight(true);
            if ($panel.length > 0) {
                if ($panel.is(':visible')) {
                    themify_sticky_pos();
                }
                else {
                    $('#themify-meta-box-tabs a').one('click', function () {
                        if ($(this).attr('id') == 'page-buildert') {
                            themify_sticky_pos();
                        }
                    });
                }
            }

            function themify_sticky_pos() {
                $panel.width($panel.width());
                $top = $panel.offset().top;
                $panel_top = Math.round($('#page-builder').offset().top);
                $('#themify_builder_module_tmp').height($panel.outerHeight(true));
                $(window).scroll(function () {
                    if ($scrollTimer) {
                        clearTimeout($scrollTimer);
                    }
                    $scrollTimer = setTimeout(handleScroll, 15);

                }).resize(function () {
                    $top = $panel.offset().top;
                    $panel.width($('#page-builder .themify_builder_admin').width()).css('top', $wpadminbar.outerHeight(true));
                    $('#themify_builder_module_tmp').height($panel.outerHeight(true));
                });
            }

            function handleScroll() {
                $scrollTimer = null;
                var $bottom = $panel_top + $('#page-builder').height(),
                    $scroll = $(this).scrollTop();
                if ($scroll > $top && $scroll < $bottom) {
                    $panel.addClass('themify_builder_module_panel_fixed').css('top', $wpadminbarHeight);
                    $('#themify_builder_module_tmp').css('display', 'block');

                } else {
                    $panel.removeClass('themify_builder_module_panel_fixed').css('top', 0);
                    $('#themify_builder_module_tmp').css('display', 'none');
                }
            }
        });
       
    });
}(jQuery, window, document));
