;
var ThemifyPageBuilder, ThemifyLiveStyling, ThemifyBuilderCommon, tinyMCEPreInit, ThemifyBuilderModuleJs;
(function ($, _, window, document, undefined) {

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
        slidePanelOpen: true,
        init: function () {
            this.tfb_hidden_editor_object = tinyMCEPreInit.mceInit['tfb_lb_hidden_editor'];
            this.alertLoader = $('<div/>', {id: 'themify_builder_alert', class: 'themify-builder-alert'});
            this.builder_content_selector = '#themify_builder_content-' + themifyBuilder.post_ID;

            // status
            this.editing = false;

            this.bindEvents();

            ThemifyBuilderCommon.Lightbox.setup();
            ThemifyBuilderCommon.LiteLightbox.modal.on('attach', function(){
                this.$el.addClass('themify_builder_lite_lightbox_modal');
            });

            this.setupTooltips();
            this.mediaUploader();
            this.openGallery();

            /**
             * New instance created on lightbox open, destroyed on lightbox close
             * @type ThemifyLiveStyling
             */
            this.liveStylingInstance = new ThemifyLiveStyling();
        },
        bindEvents: function () {
            var self = ThemifyPageBuilder,
                    $body = $('body'),
                    resizeId,
                    eventToUse = 'true' == themifyBuilder.isTouch ? 'touchend' : 'mouseenter mouseleave';

            /* rows */
            $body.on('click', '.themify_builder_toggle_row, .themify_builder_row_js_wrapper .themify_builder_row_top .toggle_row', this.toggleRow)
                    .on('click', '.themify_builder_option_row,.themify_builder_style_row', this.optionRow)
                    // used for both column and sub-column options
                    .on('click', '.themify_builder_option_column', this.optionColumn)

                    .on('click', '.themify_builder_content .themify_builder_delete_row', this.deleteRowBuilder)
                    .on('click', '.themify_builder_content .themify_builder_duplicate_row', this.duplicateRowBuilder)
                    .on('click', '#tfb_module_settings .themify_builder_delete_row, #tfb_row_settings .themify_builder_delete_row', this.deleteRow)
                    .on('click', '#tfb_module_settings .themify_builder_duplicate_row, #tfb_row_settings .themify_builder_duplicate_row', this.duplicateRow)

                    /* Copy, paste, import, export component (row, sub-row, module) */
                    .on('click', '.themify_builder_content .themify_builder_copy_component', this.copyComponentBuilder)
                    .on('click', '.themify_builder_content .themify_builder_paste_component', this.pasteComponentBuilder)
                    .on('click', '.themify_builder_content .themify_builder_import_component', this.importComponentBuilder)
                    .on('click', '.themify_builder_content .themify_builder_export_component', this.exportComponentBuilder)

                    /* On component import form save */
                    .on('click', '#builder_submit_import_component_form', this.importRowModBuilderFormSave)

                    .on(eventToUse, '.themify_builder_row .row_menu', this.MenuHover)
                    .on(eventToUse, '.themify_builder_module_front', this.ModHover)
                    .on('click', '#tfb_row_settings .add_new a', this.rowOptAddRow);
            $('.themify_builder_row_panel').on(eventToUse, '.module_menu, .module_menu .themify_builder_dropdown', this.MenuHover);

            /* module */
            $body.on('click', '.themify_builder .js--themify_builder_module_styling', this.moduleStylingOption)
                    .on('click', '.themify_builder .themify_module_options', this.optionsModule)
                    .on('dblclick', '.themify_builder .active_module', this.dblOptionModule)
                    .on('click', '.themify_builder .themify_module_duplicate', this.duplicateModule)
                    .on('click', '.themify_builder .themify_module_delete', this.deleteModule)
                    .on('click', '.add_module', this.addModule)

                    /* panel */
                    .on('click', '.themify-builder-front-save', this.panelSave)
                    .on('click', '.themify-builder-front-close', this.panelClose)

                    /* Layout Action */
                    .on('click', '.layout_preview', this.templateSelected);

            // add support click mobile device
            if (this.is_touch_device()) {
                $body.on('touchstart', '.themify_builder .themify_module_options', this.optionsModule)
                        .on('touchstart', '.themify_builder .themify_module_duplicate', this.duplicateModule)
                        .on('touchstart', '.themify_builder .themify_module_delete', this.deleteModule);
            }

            $body
                    .on('click', '#tfb_module_settings .add_new a', this.moduleOptAddRow)
                    .on('click', '#builder_submit_layout_form', this.saveAsLayout)

                    .on('mouseenter mouseleave', '.themify_builder_sub_row_top', this.hideColumnStylingIcon);

            $('body').on('builderscriptsloaded.themify', function () {
                if (typeof switchEditors !== 'undefined' && typeof tinyMCE !== 'undefined') {
                    //make sure the hidden WordPress Editor is in Visual mode
                    switchEditors.go('tfb_lb_hidden_editor', 'tmce');
                }
            });

            // module events
            $(window).resize(function () {
                clearTimeout(resizeId);
                resizeId = setTimeout(function () {
                    self.moduleEvents();
                }, 500);
            });

            // add loader to body
            self.alertLoader.appendTo('body').hide();

            // layout icon selected
            $body.on('click', '.tfl-icon', function (e) {
                $(this).addClass('selected').siblings().removeClass('selected');
                e.preventDefault();
            });

            // Front builder
            $('#wp-admin-bar-themify_builder .ab-item:first').on('click', function (e) {
                e.preventDefault();
            });
            $('body').on('click.aftertbloader', 'a.js-turn-on-builder', this.toggleFrontEdit);
            $('.themify_builder_dup_link a').on('click', this.duplicatePage);
            $('.slide_builder_module_panel').on('click', this.slidePanel);

            if (this.is_touch_device()) {
                $body.addClass('touch');
                $body.on('touchstart', '.themify_builder_module_front .module', function (e) {
                    $(self.builder_content_selector + ' .themify_builder_module_front').removeClass('tap');
                    $(this).parent().addClass('tap');
                }).on('touchend', '.themify_builder_module_front_overlay', function (e) {
                    $(this).parent().removeClass('tap');
                });
            }

            // Import links
            $('.themify_builder_import_page > a').on('click', this.builderImportPage);
            $('.themify_builder_import_post > a').on('click', this.builderImportPost);
            $('.themify_builder_import_file > a').on('click', this.builderImportFile);
            $('.themify_builder_load_layout > a').on('click', this.builderLoadLayout);
            $('.themify_builder_save_layout > a').on('click', this.builderSaveLayout);
            $body.on('click', '#builder_submit_import_form', this.builderImportSubmit)

                    // Grid Menu List
                    .on('click', '.themify_builder_grid_list li a', this._gridMenuClicked)
                    .on(eventToUse, '.themify_builder_row .grid_menu', this._gridHover)
                    .on('change', '.themify_builder_row .gutter_select', this._gutterChange)
                    .on('click', '.themify_builder_sub_row .sub_row_delete', this._subRowDelete)
                    .on('click', '.themify_builder_sub_row .sub_row_duplicate', this._subRowDuplicate)
                    .on('change', '.themify_builder_equal_column_height_checkbox', this._equalColumnHeightChanged)

            // Undo / Redo buttons
            .on('click', '.js-themify-builder-undo-btn', this.actionUndo)
            .on('click', '.js-themify-builder-redo-btn', this.actionRedo)

            // Builder Revisions
            .on('click', '.themify_builder_load_revision > a, .js-themify-builder-load-revision', this.loadRevisionLightbox)
            .on('click', '.themify_builder_save_revision > a, .js-themify-builder-save-revision', this.saveRevisionLightbox)
            .on('click', '.js-builder-restore-revision-btn', this.restoreRevision)
            .on('click', '.js-builder-delete-revision-btn', this.deleteRevision)
            .on(eventToUse, '.themify-builder-front-save, .themify-builder-front-save-title, .themify-builder-revision-dropdown-panel', this.toggleRevDropdown)

            // Apply All checkbox
            .on('click', '.style_apply_all', this.applyAll_events);

            // Listen to any changes of undo/redo
            ThemifyBuilderCommon.undoManager.instance.setCallback(this.undoManagerCallback);
            this.updateUndoBtns();
            ThemifyBuilderCommon.undoManager.events.on('change', function(event, container, startValue, newValue){
                ThemifyBuilderCommon.undoManager.set(container, startValue, newValue);
            });

            // Module actions
            self.moduleActions();

            // Ajax Start action
            $(document).on('ajaxSend', this._ajaxStart).on('ajaxComplete', this._ajaxComplete);
        },
        checkUnload: function () {
            /* unload event */
            if ($('body').hasClass('themify_builder_active')) {
                window.onbeforeunload = function () {
                    return ThemifyBuilderCommon.undoManager.instance.hasUndo() && ThemifyPageBuilder.editing ?themifyBuilder.confirm_on_unload:null;
                };
            }
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

                    $('body').trigger(
                            'themify_builder_color_picker_change',
                            [minicolorsObj.parent().nextAll('.builderColorSelectInput').attr('name'), minicolorsObj.minicolors('rgbaString')]
                            );
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
                    change: _.debounce(function (hex, opacity) {
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

                            $('body').trigger(
                                    'themify_builder_color_picker_change',
                                    [$cssRuleInput.attr('name'), $minicolors.minicolors('rgbaString')]
                                    );
                        }
                    }, 200)
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
        draggedNotTapped: false,
        moduleEvents: function () {
            var self = ThemifyPageBuilder;

            $('.row_menu .themify_builder_dropdown, .module_menu .themify_builder_dropdown').hide();
            $('.themify_module_holder').each(function () {
                if ($(this).find('.themify_builder_module_front').length > 0) {
                    $(this).find('.empty_holder_text').hide();
                } else {
                    $(this).find('.empty_holder_text').show();
                }
            });

            $(".themify_builder_module_panel .themify_builder_module").draggable({
                appendTo: "body",
                helper: "clone",
                revert: 'invalid',
                zIndex: 20000,
                connectToSortable: ".themify_module_holder"
            });

            $('.themify_module_holder').each(function(){
                var startModuleFragment = $(this).closest('.themify_builder_content')[0].innerHTML, newModuleFragment;
                $(this).sortable({
                    placeholder: 'themify_builder_ui_state_highlight',
                    items: '.themify_builder_module_front, .themify_builder_sub_row',
                    connectWith: '.themify_module_holder',
                    cursor: 'move',
                    revert: 100,
                    handle: '.themify_builder_module_front_overlay, .themify_builder_sub_row_top',
                    cursorAt: {top: 20, left: 110},
                    helper: function () {
                        return $('<div class="themify_builder_sortable_helper"/>');
                    },
                    sort: function (event, ui) {
                        $('.themify_module_holder .themify_builder_ui_state_highlight').height(35);
                        $('.themify_module_holder .themify_builder_sortable_helper').height(40).width(220);

                        if (!$('#themify_builder_module_panel').hasClass('slide_builder_module_state_down')) {
                            $('#themify_builder_module_panel').addClass('slide_builder_module_state_down');
                            $('#themify_builder_module_panel').find('.slide_builder_module_wrapper').slideUp();
                        }
                    },
                    receive: function (event, ui) {
                        self.PlaceHoldDragger();
                        $(this).parent().find('.empty_holder_text').hide();
                    },
                    start: function (event, ui) {
                        ThemifyPageBuilder.draggedNotTapped = true;
                    },
                    stop: function (event, ui) {
                        ThemifyPageBuilder.draggedNotTapped = false;
                        if (!ui.item.hasClass('active_module') && !ui.item.hasClass('themify_builder_sub_row')) {
                            var module_name = ui.item.data('module-name');
                            $(this).parent().find(".empty_holder_text").hide();
                            ui.item.addClass('active_module').find('.add_module').hide();
                            $.ajax({
                                type: "POST",
                                url: themifyBuilder.ajaxurl,
                                data:
                                        {
                                            action: 'tfb_add_element',
                                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                                            tfb_template_name: 'module_front',
                                            tfb_module_name: module_name
                                        },
                                success: function (data) {
                                    var $newElems = $(data);
                                    ui.item.replaceWith($newElems);
                                    self.moduleEvents();
                                    $newElems.find('.themify_builder_module_front_overlay').show();
                                    $newElems.find('.themify_module_options').trigger('click', [true]);
                                    $newElems.find('.module').hide();
                                }
                            });
                        } else {
                            var builder_container = ui.item.closest('.themify_builder_content')[0];
                            newModuleFragment = ui.item.closest('.themify_builder_content')[0].innerHTML;

                            // Make sub_row only can nested one level
                            if (ui.item.hasClass('themify_builder_sub_row') && ui.item.parents('.themify_builder_sub_row').length) {
                                var $clone_for_move = ui.item.find('.active_module').clone();
                                $clone_for_move.insertAfter(ui.item);
                                ui.item.remove();
                            }

                            self.newRowAvailable();
                            self.moduleEvents();

                            if ( startModuleFragment !== newModuleFragment ) {
                                ThemifyBuilderCommon.undoManager.events.trigger('change', [builder_container, startModuleFragment, newModuleFragment]);
                            }
                        }
                        self.editing = true;

                        if (self.slidePanelOpen && $('#themify_builder_module_panel').hasClass('slide_builder_module_state_down')) {
                            $('#themify_builder_module_panel').removeClass('slide_builder_module_state_down');
                            $('#themify_builder_module_panel').find('.slide_builder_module_wrapper').slideDown();
                        }
                    }
                });
            });
            $('.themify_builder_content').not('.not_editable_builder').each(function(){
                var startValue = $(this).closest('.themify_builder_content')[0].innerHTML, newValue;
                $(this).sortable({
                    items: '.themify_builder_row',
                    handle: '.themify_builder_row_top',
                    axis: 'y',
                    placeholder: 'themify_builder_ui_state_highlight',
                    sort: function (event, ui) {
                        $('.themify_builder_ui_state_highlight').height(35);
                    },
                    stop: function (event, ui) {
                        self.editing = true;
                        var builder_container = ui.item.closest('.themify_builder_content')[0];
                        newValue = ui.item.closest('.themify_builder_content')[0].innerHTML;
                        if ( startValue !== newValue ) {
                            ThemifyBuilderCommon.undoManager.events.trigger('change', [builder_container, startValue, newValue]);
                        }
                    }
                });
            });

            var grid_menu_func = wp.template('builder_grid_menu'),
                    tmpl_grid_menu = grid_menu_func({});
            $('.themify_builder_row_content').each(function () {
                $(this).children().each(function () {
                    var $holder = $(this).find('.themify_module_holder').first();
                    $holder.children('.themify_builder_module_front').each(function () {
                        if ($(this).find('.grid_menu').length == 0) {
                            $(this).append($(tmpl_grid_menu));
                        }
                    });
                });
            });

            self._RefreshHolderHeight();
        },
        setupTooltips: function () {
            var setupBottomTooltips = function () {
                $('body').on('mouseover', '[rel^="themify-tooltip-"]', function (e) {
                    // append custom tooltip
                    var $title = $(this).data('title')?$(this).data('title'):$(this).prop('title');
                    $(this).append('<span class="themify_tooltip">' + $title + '</span>');
                });

                $('body').on('mouseout', '[rel^="themify-tooltip-"]', function (e) {
                    // remove custom tooltip
                    $(this).children('.themify_tooltip').remove();
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
            var row_length = $(this).closest('.themify_builder_row_js_wrapper').find('.themify_builder_row:visible').length;
            if (row_length > 1) {
                $(this).closest('.themify_builder_row').remove();
            }
            else {
                $(this).closest('.themify_builder_row').hide();
            }
            self.editing = true;
        },
        deleteRowBuilder: function (e) {
            e.preventDefault();

            if (!confirm(themifyBuilder.rowDeleteConfirm)) {
                return;
            }

            var $builder_container = $(this).closest('.themify_builder_content'),
                row_length = $builder_container.find('.themify_builder_row:visible').length,
                self = ThemifyPageBuilder, startValue = $builder_container[0].innerHTML;

            if (row_length > 1) {
                $(this).closest('.themify_builder_row').remove();
            }
            else {
                $(this).closest('.themify_builder_row').hide();
            }
            self.editing = true;

            var newValue = $builder_container[0].innerHTML;
            if ( startValue !== newValue ) {
                ThemifyBuilderCommon.undoManager.events.trigger('change', [$builder_container[0], startValue, newValue]);
            }
        },
        duplicateRow: function (e) {
            e.preventDefault();

            var self = ThemifyPageBuilder,
                    oriElems = $(this).closest('.themify_builder_row'),
                    newElems = $(this).closest('.themify_builder_row').clone(),
                    row_count = $('#tfb_module_settings .themify_builder_row_js_wrapper').find('.themify_builder_row:visible').length + 1,
                    number = row_count + Math.floor(Math.random() * 9);

            // fix wpeditor empty textarea
            newElems.find('.tfb_lb_wp_editor.tfb_lb_option_child').each(function () {
                var this_option_id = $(this).attr('id'), element_val;
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
                $(this).val(insertTo);
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
            self.editing = true;
        },
        duplicateRowBuilder: function (e) {
            e.preventDefault();
            var self = ThemifyPageBuilder,
                    oriElems = $(this).closest('.themify_builder_row'),
                    $builder_container = $(this).closest('.themify_builder_content'),
                    builder_id = $builder_container.data('postid'),
                    sendData = ThemifyPageBuilder._getSettings(oriElems, 0),
                    startValue = $builder_container[0].innerHTML;

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                beforeSend: function (xhr) {
                    ThemifyBuilderCommon.showLoader('show');
                },
                data: {
                    action: 'builder_render_duplicate_row',
                    nonce: themifyBuilder.tfb_load_nonce,
                    row: sendData,
                    id: builder_id,
                    builder_grid_activate: 1
                },
                success: function (data) {
                    var $newElems = $(data.html);
                    $newElems.find('.themify_builder_dropdown').hide();
                    oriElems[0].parentNode.insertBefore( $newElems[2], oriElems[0].nextSibling );
                    self.moduleEvents();
                    self.loadContentJs();
                    self.editing = true;
                    ThemifyBuilderCommon.showLoader('hide');

                    var newValue = $builder_container[0].innerHTML;
                    if ( startValue !== newValue ) {
                        ThemifyBuilderCommon.undoManager.events.trigger('change', [$builder_container[0], startValue, newValue]);
                    }
                }
            });
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
                    $(this).find('.themify_builder_dropdown_front').stop(false, true).css('z-index', '').hide();
                    $(this).find('.themify_builder_dropdown_front ul').stop(false, true).hide();
                    $(this).find('.themify_builder_dropdown').stop(false, true).hide();
                    $row.css('z-index', '');
                    ThemifyPageBuilder.menuTouched = [];
                } else {
                    var $builderCont = $('.themify_builder_content');
                    $builderCont.find('.themify_builder_dropdown').stop(false, true).hide();
                    $builderCont.find('.themify_builder_dropdown_front').stop(false, true).hide();
                    $builderCont.find('.themify_builder_dropdown_front ul').stop(false, true).hide();
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
        highlightModuleFront: function ($module) {
            $('.themify_builder_content .module_menu_front').removeClass('current_selected_module');
            $module.addClass('current_selected_module');
        },
        ModHover: function (e) {
            if ('touchend' == e.type) {
                if (!ThemifyPageBuilder.draggedNotTapped) {
                    ThemifyPageBuilder.draggedNotTapped = false;
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
                        $(this).find('.themify_builder_dropdown_front').stop(false, true).css('z-index', '').hide();
                        $(this).find('.themify_builder_dropdown_front ul').stop(false, true).hide();
                        $(this).find('.themify_builder_dropdown').stop(false, true).hide();
                        ThemifyPageBuilder.menuTouched = [];
                    } else {
                        var $builderCont = $('.themify_builder_content');
                        $builderCont.find('.themify_builder_dropdown_front').stop(false, true).css('z-index', '').hide();
                        $builderCont.find('.themify_builder_dropdown_front ul').stop(false, true).hide();
                        $builderCont.find('.themify_builder_dropdown').stop(false, true).hide();
                        $(this).find('.themify_builder_dropdown_front').stop(false, true).css('z-index', '999').show();
                        $(this).find('.themify_builder_dropdown_front ul').stop(false, true).show();
                        ThemifyPageBuilder.menuTouched = [];
                        ThemifyPageBuilder.menuTouched[index] = true;
                    }
                }
            } else if (e.type == 'mouseenter') {
                $(this).find('.themify_builder_module_front_overlay').stop(false, true).show();
                $(this).find('.themify_builder_dropdown_front').stop(false, true).show();
            } else if (e.type == 'mouseleave') {
                $(this).find('.themify_builder_module_front_overlay').stop(false, true).hide();
                $(this).find('.themify_builder_dropdown_front').stop(false, true).hide();
            }
        },
        isShortcutModuleStyling: false,
        moduleStylingOption: function( event ) {
            event.preventDefault();
            ThemifyPageBuilder.isShortcutModuleStyling = true;
            $(this).closest('ul').find('.themify_module_options').trigger('click');
        },
        optionsModule: function (event, isNewModule) {
            event.preventDefault();
            isNewModule = isNewModule || false; // assume that if isNewModule:true = Add module, otherwise Edit Module

            var self = ThemifyPageBuilder;

            var $this = $(this);

            var module_name = $this.data('module-name');
            var $moduleWrapper = $this.closest('.themify_builder_module_front');
            var $currentStyledModule = $moduleWrapper.children('.module');

            var moduleSettings = ThemifyBuilderCommon.getModuleSettings($moduleWrapper);
            var is_settings_exist = typeof moduleSettings !== 'string' ? true : false;

            $('.module_menu .themify_builder_dropdown').hide();

            self.highlightModuleFront($this.closest('.module_menu_front'));

            var callback = function ( response ) {
                if ( isNewModule ) {
                    response.setAttribute('data-form-state', 'new');
                } else {
                    response.setAttribute('data-form-state', 'edit');
                }

                var inputs = response.querySelectorAll('.tfb_lb_option'), iterate;
                for (iterate = 0; iterate < inputs.length; ++iterate) {
                    var $this_option = $(inputs[iterate]),
                            this_option_id = $this_option.attr('id'),
                            $found_element = moduleSettings[this_option_id];

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

                            multiple_cat.val(cat_val);
                            parent.find("option[value='" + cat_val + "']").attr('selected', 'selected');

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
                            $this_option.find("input[value='" + $found_element + "']").prop('checked', true);
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
                } //iterate

                // Trigger event
                $('body').trigger('editing_module_option', [moduleSettings]);

                // add new wp editor
                self.addNewWPEditor();

                // set touch element
                self.touchElement();

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

                ThemifyBuilderCommon.Lightbox.rememberRow();
                self.liveStylingInstance.init($currentStyledModule, moduleSettings);

                // shortcut tabs
                if ( ThemifyPageBuilder.isShortcutModuleStyling ) {
                    $('a[href="#themify_builder_options_styling"]').trigger('click');
                    ThemifyPageBuilder.isShortcutModuleStyling = false;
                }
            };

            ThemifyBuilderCommon.highlightRow($(this).closest('.themify_builder_row'));
            
            // Start capture undo action
            var $startValueRaw = $(this).closest('.themify_builder_content').clone(), startValue;
            if ( isNewModule ) {
                $startValueRaw.find('.current_selected_module').closest('.active_module').remove();
                startValue = $startValueRaw[0].innerHTML;
            } else {
                startValue = $(this).closest('.themify_builder_content')[0].innerHTML;
            }
            ThemifyBuilderCommon.undoManager.setStartValue( startValue );
            
            ThemifyBuilderCommon.Lightbox.open( { loadMethod: 'inline', templateID: 'builder_form_module_' + module_name }, callback);
        },
        _objectAssocLength: function (obj) {
            var size = 0, key;
            for (key in obj) {
                if (obj.hasOwnProperty(key))
                    size++;
            }
            return size;
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
            var holder = $(this).closest('.themify_builder_module_front'),
                self = ThemifyPageBuilder,
                temp_appended_data = JSON.parse(holder.find('.front_mod_settings').find('script[type="text/json"]').text()),
                module_slug = holder.find('.front_mod_settings').data('mod-name'),
                $builder_container = $(this).closest('.themify_builder_content'),
                builder_id = $builder_container.data('postid'),
                startValue = $builder_container[0].innerHTML;

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data:
                        {
                            action: 'tfb_load_module_partial',
                            tfb_post_id: builder_id,
                            tfb_module_slug: module_slug,
                            tfb_module_data: JSON.stringify(temp_appended_data),
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            builder_grid_activate: 1
                        },
                beforeSend: function (xhr) {
                    ThemifyBuilderCommon.showLoader('show');
                },
                success: function (data) {
                    var $newElems = $(data.html);
                    holder[0].parentNode.insertBefore( $newElems[0], holder[0].nextSibling );

                    self.newRowAvailable();
                    self.moduleEvents();
                    self.loadContentJs();
                    ThemifyBuilderCommon.showLoader('hide');

                    var newValue = $builder_container[0].innerHTML;
                    if ( startValue !== newValue ) {
                        ThemifyBuilderCommon.undoManager.events.trigger('change', [$builder_container[0], startValue, newValue]);
                    }
                }
            });

            self.editing = true;
        },
        deleteModule: function (e) {
            e.preventDefault();

            var self = ThemifyPageBuilder,
                    _this = $(this);

            if (confirm(themifyBuilder.moduleDeleteConfirm)) {

                var builder_container = _this.closest('.themify_builder_content')[0],
                    startValue = builder_container.innerHTML;

                self.switchPlaceholdModule(_this);
                _this.parents('.themify_builder_module_front').remove();
                self.newRowAvailable();
                self.moduleEvents();
                self.editing = true;

                var newValue = builder_container.innerHTML;
                if ( startValue !== newValue ) {
                    ThemifyBuilderCommon.undoManager.events.trigger('change', [builder_container, startValue, newValue]);
                }
            }
        },
        addModule: function (e) {
            e.preventDefault();

            var self = ThemifyPageBuilder;
            var module_name = $(this).data('module-name');

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                data:
                        {
                            action: 'tfb_add_element',
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            tfb_template_name: 'module_front',
                            tfb_module_name: module_name
                        },
                success: function (data) {
                    var dest = $('.themify_builder_row:visible').first().find('.themify_module_holder').first(),
                            $newElems = $(data);
                    $newElems.appendTo(dest);

                    self.moduleEvents();
                    $newElems.find('.themify_builder_module_front_overlay').show();
                    $newElems.find('.themify_module_options').trigger('click', [true]);
                    $newElems.find('.module').hide();
                    self.editing = true;
                }
            });
        },
        /**
         * @deprecated Backwards compatibility with Builder plugins.
         *
         * TODO: remove.
         */
        showLoader: ThemifyBuilderCommon.showLoader,
        /**
         * @deprecated Backwards compatibility with Builder plugins.
         *
         * TODO: remove.
         */
        _openLightbox: ThemifyBuilderCommon.Lightbox.open,
        /**
         * @deprecated Backwards compatibility with Builder plugins.
         *
         * TODO: remove.
         */
        cancelKeyListener: ThemifyBuilderCommon.Lightbox.cancelKeyListener,
        /**
         * @deprecated Backwards compatibility with Builder plugins.
         *
         * TODO: remove.
         */
        cancelLightbox: ThemifyBuilderCommon.Lightbox.cancel,
        /**
         * @deprecated Backwards compatibility with Builder plugins.
         *
         * TODO: remove.
         */
        closeLightbox: ThemifyBuilderCommon.Lightbox.close,
        initNewEditor: function (editor_id) {
            var self = ThemifyPageBuilder;
            if (typeof tinyMCEPreInit.mceInit[editor_id] !== "undefined") {
                self.initMCEv4(editor_id, tinyMCEPreInit.mceInit[editor_id]);
                return;
            }
            var tfb_new_editor_object = self.tfb_hidden_editor_object;

            tfb_new_editor_object['elements'] = editor_id;
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
            if (check.find('.themify_builder_module_front').length == 1) {
                check.find('.empty_holder_text').show();
            }
        },
        PlaceHoldDragger: function () {
            $('.themify_module_holder').each(function () {
                if ($(this).find('.themify_builder_module_front').length == 0) {
                    $(this).find('.empty_holder_text').show();
                }
            });
        },
        saveData: function (loader, callback, saveto, previewOnly) {
            saveto = saveto || 'main';
            var self = ThemifyPageBuilder,
                ids = $('.themify_builder_content').not('.not_editable_builder').map(function () {
                    var temp_id = $(this).data('postid') || null;
                    var temp_data = self.retrieveData(this) || null;
                    return {id: temp_id, data: temp_data};
                }).get(),
                previewOnly = typeof previewOnly !== 'undefined' ? previewOnly : false;

            if (saveto == 'main') {
                self.editing = false;
            }

            return $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                data:
                        {
                            action: 'tfb_save_data',
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            ids: JSON.stringify(ids),
                            tfb_saveto: saveto,
                            // Only trust themifyBuilder.post_ID on single views
                            tfb_post_id: themifyBuilder.post_ID
                        },
                cache: false,
                beforeSend: function (xhr) {
                    if (loader) {
                        if (previewOnly) {
                            ThemifyBuilderCommon.showLoader('lightbox-preview');
                        } else {
                            ThemifyBuilderCommon.showLoader('show');
                        }
                    }
                },
                success: function (data) {
                    if (loader && !previewOnly) {
                        ThemifyBuilderCommon.showLoader('hide');
                    }
                    // load callback
                    if ($.isFunction(callback)) {
                        callback.call(this, data);
                    }
                },
                error: function() {
                    if (loader && !previewOnly) {
                        ThemifyBuilderCommon.showLoader('error');
                    }   
                }
            });
        },
        moduleSave: function (e) {
            var self = ThemifyPageBuilder,
                    $currentSelectedModule = $('.current_selected_module'),
                    $active_module_settings = $currentSelectedModule.find('.front_mod_settings'),
                    temp_appended_data = {},
                    previewOnly = false,
                    form_state = document.getElementById('tfb_module_settings').getAttribute('data-form-state');

            if (ThemifyBuilderCommon.Lightbox.previewButtonClicked($(this))) {
                previewOnly = true;
            }

            $('#tfb_module_settings .tfb_lb_option').each(function (iterate) {
                var option_value,
                        this_option_id = $(this).attr('id');

                if ($(this).hasClass('tfb_lb_wp_editor')) {
                    if (typeof tinyMCE !== 'undefined') {
                        option_value = tinyMCE.get(this_option_id).hidden === false ? tinyMCE.get(this_option_id).getContent() : switchEditors.wpautop(tinymce.DOM.get(this_option_id).value);
                    } else {
                        option_value = $(this).val();
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

            $active_module_settings.find('script[type="text/json"]').text(JSON.stringify(temp_appended_data));

            // clear empty module
            self.deleteEmptyModule();

            // Save data
            self.saveData(true, function () {
                var hilite = $('.current_selected_module').parents('.themify_builder_module_front'),
                        class_hilite = self.getHighlightClass(hilite),
                        hilite_obj = self.getHighlightObject(hilite),
                        mod_name = hilite.data('module-name');

                if (!previewOnly) {
                    ThemifyBuilderCommon.Lightbox.close()
                }

                hilite.wrap('<div class="temp_placeholder ' + class_hilite + '" />').find('.themify_builder_module_front_overlay').show();
                self.updateContent(class_hilite, hilite_obj, mod_name, temp_appended_data, previewOnly, form_state);
            }, 'cache', previewOnly);

            self.editing = true;
            e.preventDefault();
        },
        hideModulesControl: function () {
            $('.themify_builder_module_front_overlay').hide();
            $('.themify_builder_dropdown_front').hide();
        },
        hideColumnsBorder: function () {
            $('.themify_builder_col').css('border', 'none');
        },
        showColumnsBorder: function () {
            $('.themify_builder_col').css('border', '');
        },
        retrieveData: function (obj) {
            var option_data = {},
                    $builder_selector = $(obj);

            // rows
            $builder_selector.children('.themify_builder_row').each(function (r) {
                option_data[r] = ThemifyPageBuilder._getSettings($(this), r);
            });
            return option_data;
        },
        _getSubRowSettings: function ($subRow, subRowOrder) {
            var self = ThemifyPageBuilder,
                    sub_cols = {};
            $subRow.find('.themify_builder_col').first().parent().children('.themify_builder_col').each(function (sub_col) {
                var sub_grid_class = self.filterClass($(this).attr('class')),
                        sub_modules = {};

                $(this).find('.active_module').each(function (sub_m) {
                    var sub_mod_name = $(this).find('.front_mod_settings').data('mod-name'),
                            sub_mod_elems = $(this).find('.front_mod_settings'),
                            sub_mod_settings = JSON.parse(sub_mod_elems.find('script[type="text/json"]').text());
                    sub_modules[sub_m] = {mod_name: sub_mod_name, mod_settings: sub_mod_settings};
                });

                sub_cols[ sub_col ] = {
                    'column_order': sub_col,
                    grid_class: sub_grid_class,
                    modules: sub_modules
                };

                // get sub-column styling
                if ($(this).children('.column-data-styling').length > 0) {
                    var $data_styling = $(this).children('.column-data-styling').data('styling');
                    if ('object' === typeof $data_styling)
                        sub_cols[ sub_col ].styling = $data_styling;
                }
            });

            return {
                row_order: subRowOrder,
                gutter: $subRow.data('gutter'),
                equal_column_height: $subRow.data('equal-column-height'),
                cols: sub_cols
            }
        },
        _getSettings: function ($base, index) {
            var self = ThemifyPageBuilder,
                    option_data = {},
                    cols = {};

            // cols
            $base.find('.themify_builder_row_content').first().children('.themify_builder_col').each(function (c) {
                var grid_class = self.filterClass($(this).attr('class')),
                        modules = [];
                // mods
                $(this).find('.themify_module_holder').first().children().each(function (m) {
                    if ($(this).hasClass('themify_builder_module_front')) {
                        var mod_name = $(this).find('.front_mod_settings').data('mod-name'),
                                mod_elems = $(this).find('.front_mod_settings'),
                                mod_settings = JSON.parse(mod_elems.find('script[type="text/json"]').text());
                        modules[ m ] = {mod_name: mod_name, mod_settings: mod_settings};
                    }

                    // Sub Rows
                    if ($(this).hasClass('themify_builder_sub_row')) {
                        modules[m] = self._getSubRowSettings($(this), m);
                    }
                });

                cols[c] = {
                    'column_order': c,
                    'grid_class': grid_class,
                    'modules': modules
                };

                // get column styling
                if ($(this).children('.column-data-styling').length > 0) {
                    var $data_styling = $(this).children('.column-data-styling').data('styling');
                    if ('object' === typeof $data_styling)
                        cols[ c ].styling = $data_styling;
                }
            });

            option_data = {
                row_order: index,
                gutter: $base.data('gutter'),
                equal_column_height: $base.data('equal-column-height'),
                cols: cols
            };

            // get row styling
            if ($base.find('.row-data-styling').length > 0) {
                var $data_styling = $base.find('.row-data-styling').data('styling');
                if ('object' === typeof $data_styling)
                    option_data.styling = $data_styling;
            }

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

            if ($(str).text().length > limit) {
                new_str = $(str).text().substr(0, limit);
            }
            else {
                new_str = $(str).text();
            }

            return new_str;
        },
        mediaUploader: function () {

            // Uploading files
            var $body = $('body'); // Set this

            // Field Uploader
            $body.on('click', '.themify-builder-media-uploader', function (event) {
                var $el = $(this);

                var file_frame = wp.media.frames.file_frame = wp.media({
                    title: $el.data('uploader-title'),
                    library: {
                        type: $el.data('library-type') ? $el.data('library-type') : 'image'
                    },
                    button: {
                        text: $el.data('uploader-button-text')
                    },
                    multiple: false  // Set to true to allow multiple files to be selected
                });

                // When an image is selected, run a callback.
                file_frame.on('select', function () {
                    // We set multiple to false so only get one image from the uploader
                    var attachment = file_frame.state().get('selection').first().toJSON();

                    // Do something with attachment.id and/or attachment.url here
                    $el.closest('.themify_builder_input').find('.themify-builder-uploader-input').val(attachment.url).trigger('change')
                            .parent().find('.img-placeholder').empty()
                            .html($('<img/>', {src: attachment.url, width: 50, height: 50}))
                            .parent().show();

                    // Attached id to input
                    $el.closest('.themify_builder_input').find('.themify-builder-uploader-input-attach-id').val(attachment.id);
                });

                // Finally, open the modal
                file_frame.open();
                event.preventDefault();
            });

            // delete button
            $body.on('click', '.themify-builder-delete-thumb', function (e) {
                $(this).prev().empty().parent().hide();
                $(this).closest('.themify_builder_input').find('.themify-builder-uploader-input').val('').trigger('change');
                e.preventDefault();
            });

            // Media Buttons
            $body.on('click', '.insert-media', function (e) {
                window.wpActiveEditor = $(this).data('editor');
            });
        },
        builderPlupload: function (action_text) {
            var class_new = action_text == 'new_elemn' ? '.plupload-clone' : '',
                    $builderPluploadUpload = $(".themify-builder-plupload-upload-uic" + class_new);

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
                    //pconfig["multipart_params"]["_ajax_nonce"] = $this.find(".ajaxnonceplu").attr("id").replace("ajaxnonceplu", "");
                    pconfig["multipart_params"]["_ajax_nonce"] = themifyBuilder.tfb_load_nonce;
                    pconfig["multipart_params"]['topost'] = themifyBuilder.post_ID;
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
        updateContent: function (class_hilite, hilite_obj, module_slug, temp_appended_data, previewOnly, form_state) {
            var self = ThemifyPageBuilder,
                    $builder_selector = $('.current_selected_module').closest('.themify_builder_content'),
                    builder_id = $builder_selector.data('postid'),
                    previewOnly = typeof previewOnly !== 'undefined' ? previewOnly : false,
                    form_state = form_state || 'edit',
                    startValue = ThemifyBuilderCommon.undoManager.getStartValue() || $builder_selector[0].innerHTML;

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data:
                        {
                            action: 'tfb_load_module_partial',
                            tfb_post_id: builder_id,
                            tfb_module_slug: module_slug,
                            tfb_module_data: JSON.stringify(temp_appended_data),
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            builder_grid_activate: 1
                        },
                beforeSend: function (xhr) {
                    //
                    if (!previewOnly) {
                        ThemifyBuilderCommon.showLoader('show');
                    }
                },
                success: function (data) {
                    var $newElems = $(data.html),
                        parent = $builder_selector.find('.temp_placeholder.' + class_hilite);

                    // goto mod element
                    if (parent.length > 0) {
                        $('html,body').animate({scrollTop: parent.offset().top - 150}, 500);
                        parent.empty();
                    }

                    $newElems.find('.module_menu_front').addClass('current_selected_module');
                    parent.get(0).innerHTML = $newElems.get(0).outerHTML;
                    parent.find('.active_module').unwrap();
                    $newElems.find('.themify_builder_module_front_overlay')
                            .show().delay(1000).fadeOut(1000);

                    if (previewOnly) {
                        var $currentStyledModule = $newElems.children('.module');

                        self.liveStylingInstance.init(
                                $currentStyledModule,
                                ThemifyBuilderCommon.getModuleSettings($newElems)
                                );

                    } else {
                        self.newRowAvailable();
                    }

                    self.moduleEvents();
                    self.loadContentJs();

                    // Load google font style
                    if ('undefined' !== typeof WebFont && data.gfonts.length > 0) {
                        WebFont.load({
                            google: {
                                families: data.gfonts
                            }
                        });
                    }

                    if (previewOnly) {
                        ThemifyBuilderCommon.showLoader('lightbox-preview-hide');
                    } else {
                        // log the action
                        var newValue = $builder_selector[0].innerHTML;
                        ThemifyBuilderCommon.undoManager.events.trigger('change', [$builder_selector[0], startValue, newValue ]);
                    }

                    // Hook
                    $('body').trigger('builder_load_module_partial', $newElems);
                }
            });
        },
        toggleFrontEdit: function (e) {
            var self = ThemifyPageBuilder,
                    is_edit = 0;

            // remove lightbox if any
            if ($('#themify_builder_lightbox_parent').is(':visible')) {
                $('.builder_cancel_lightbox').trigger('click');
            }

            var location_url = window.location.pathname + window.location.search;
            // remove hash
            if (window.history && window.history.replaceState) {
                window.history.replaceState('', '', location_url);
            } else {
                window.location.href = window.location.href.replace(/#.*$/, '#');
            }

            var bids = $('.themify_builder_content').not('.not_editable_builder').map(function () {
                return $(this).data('postid') || null;
            }).get();

            // add body class
            if (!$('body').hasClass('themify_builder_active')) {
                is_edit = 1;
                $('.toggle_tf_builder a:first').text(themifyBuilder.toggleOff);
                $('.themify_builder_front_panel').slideDown();
            } else {
                $('.themify_builder_front_panel').slideUp();
                $('.toggle_tf_builder a:first').text(themifyBuilder.toggleOn);
                is_edit = 0;
            }

            if (is_edit == 0 && self.editing) {
                // confirm
                var reply = confirm(themifyBuilder.confirm_on_turn_off);
                if (reply) {
                    self.saveData(true, function () {
                        self.toggleFrontEditAjax(is_edit, bids);
                    });
                } else {
                    self.toggleFrontEditAjax(is_edit, bids);
                }
            } else {
                self.toggleFrontEditAjax(is_edit, bids);
                self.editing = false;
            }

            if ('undefined' !== typeof e) {
                e.preventDefault();
            }
        },
        toggleFrontEditAjax: function (is_edit, bids) {
            var self = ThemifyPageBuilder;
            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data:
                        {
                            action: 'tfb_toggle_frontend',
                            tfb_post_id: themifyBuilder.post_ID,
                            tfb_post_ids: bids,
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            builder_grid_activate: is_edit
                        },
                beforeSend: function (xhr) {
                    ThemifyBuilderCommon.showLoader('show');
                },
                success: function (data) {

                    if ( ! is_edit ) {
                        // Clear undo history
                        ThemifyBuilderCommon.undoManager.instance.clear();
                    }

                    if (data.length > 0) {
                        $('.themify_builder_content').not('.not_editable_builder').empty();
                        $.each(data, function (i, v) {
                            var $target = $('#themify_builder_content-' + data[i].builder_id).empty();
                            $target.get(0).innerHTML = $(data[i].markup).unwrap().get(0).innerHTML;
                        });
                    }
                    if (is_edit) {
                        $('body').addClass('themify_builder_active');
                        self.newRowAvailable();
                        self.moduleEvents();
                        self._selectedGridMenu();
                        self.checkUnload();
                        setTimeout(self._RefreshHolderHeight, 1000);
                    }
                    else {
                        $('body').removeClass('themify_builder_active');
                        window.onbeforeunload = null;
                        ThemifyBuilderModuleJs.init();
                    }
                    self.loadContentJs();
                    ThemifyBuilderCommon.showLoader('spinhide');

                    $('body').trigger('builder_toggle_frontend', is_edit);
                }
            });
        },
        newRowAvailable: function () {
            var self = ThemifyPageBuilder;

            $('.themify_builder_content').not('.not_editable_builder').each(function () {
                var $container = $(this),
                        $parent = $container.find('.themify_builder_row:visible').first().parent().children('.themify_builder_row:visible').not('.module-layout-part .themify_builder_row'), // exclude builder rows inside layout parts
                        template_func = wp.template('builder_row'),
                        $template = $(template_func({}));

                $parent.each(function () {
                    if ($(this).find('.themify_builder_module_front').length != 0) {
                        return;
                    }

                    var removeThis = true;

                    var column_data_styling = $(this).find('.column-data-styling');
                    var data_styling = null;

                    column_data_styling.each(function () {
                        if (!removeThis) {
                            return;
                        }

                        data_styling = $(this).data('styling');

                        if ((typeof data_styling === 'array' && data_styling.length > 0) || !$.isEmptyObject(data_styling)) {
                            removeThis = false;
                        }
                    });

                    data_styling = $(this).find('.row-data-styling').data('styling');

                    if (removeThis && (typeof data_styling === 'string' || $.isEmptyObject(data_styling))) {
                        $(this).remove();
                    }
                });

                if ($parent.find('.themify_builder_module_front').length > 0 || $container.find('.themify_builder_row:visible').length == 0) {
                    $template.css('visibility', 'visible').appendTo($container);
                }
            });
        },
        loadContentJs: function () {
            ThemifyBuilderModuleJs.loadOnAjax(); // load module js ajax
            // hook
            $('body').trigger('builder_load_on_ajax');
        },
        duplicatePage: function (e) {
            var self = ThemifyPageBuilder;

            if ($('body').hasClass('themify_builder_active')) {
                var reply = confirm(themifyBuilder.confirm_on_duplicate_page);
                if (reply) {
                    self.saveData(true, function () {
                        self.duplicatePageAjax();
                    });
                } else {
                    self.duplicatePageAjax();
                }
            } else {
                self.duplicatePageAjax();
            }
            e.preventDefault();
        },
        duplicatePageAjax: function () {
            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data:
                        {
                            action: 'tfb_duplicate_page',
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            tfb_post_id: themifyBuilder.post_ID,
                            tfb_is_admin: 0
                        },
                beforeSend: function (xhr) {
                    ThemifyBuilderCommon.showLoader('show');
                },
                success: function (data) {
                    ThemifyBuilderCommon.showLoader('hide');
                    var new_url = data.new_url.replace(/\&amp;/g, '&');
                    window.onbeforeunload = null;
                    window.location.href = new_url;
                }
            });
        },
        getHighlightClass: function (obj) {
            var mod = obj.index() - 1,
                    col = obj.closest('.themify_builder_col').index(),
                    row = obj.closest('.themify_builder_row').index();

            return 'r' + row + 'c' + col + 'm' + mod;
        },
        getHighlightObject: function (obj) {
            var mod = obj.index() - 1,
                    col = obj.closest('.themify_builder_col').index(),
                    row = obj.closest('.themify_builder_row').index();

            return {row: row, col: col, mod: mod};

        },
        deleteEmptyModule: function () {
            var self = ThemifyPageBuilder;
            $(self.builder_content_selector).find('.themify_builder_module_front').each(function () {
                if ($.trim($(this).find('.front_mod_settings').find('script[type="text/json"]').text()).length <= 2) {
                    $(this).remove();
                }
            });
        },
        is_touch_device: function () {
            return 'true' == themifyBuilder.isTouch;
        },
        touchElement: function () {
            $('input, textarea').each(function () {
                $(this).addClass('touchInput');
            });
        },
        slidePanel: function (e) {
            e.preventDefault();
            if ($(this).parent().hasClass('slide_builder_module_state_down')) {
                ThemifyPageBuilder.slidePanelOpen = true;
            } else {
                ThemifyPageBuilder.slidePanelOpen = false;
            }
            $(this).parent().toggleClass('slide_builder_module_state_down');
            $(this).next().slideToggle();
        },
        hideSlidingPanel: function () {
            if (ThemifyPageBuilder.slidePanelOpen) {
                $('.slide_builder_module_panel').trigger('click');
            }
        },
        showSlidingPanel: function () {
            if (!ThemifyPageBuilder.slidePanelOpen) {
                $('.slide_builder_module_panel').trigger('click');
            }
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

                    shortcode_val.val(shortcode.string()).trigger('change');

                    wp.media.gallery.shortcode = clone;
                    return shortcode;
                };

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
                } else {
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
            var $body = $('body');
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
        panelSave: function (e) {
            e.preventDefault();
            if ( ! $(this).hasClass('disabled') ) {
                ThemifyPageBuilder.saveData(true).fail(function(){
                    alert( themifyBuilder.errorSaveBuilder );
                });
            }
        },
        panelClose: function (e) {
            e.preventDefault();
            ThemifyPageBuilder.toggleFrontEdit();
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
                            importTo: themifyBuilder.post_ID
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
        optionRow: function (e) {
            e.preventDefault();

            var self = ThemifyPageBuilder;

            var $this = $(this);

            var $currentSelectedRow = $this.closest('.themify_builder_row');
            var options = ThemifyBuilderCommon.getRowStylingSettings($currentSelectedRow);

            var callback = function () {
                if ('object' === typeof options) {
                    if(options.background_slider){
                        self.getShortcodePreview($('#background_slider'),options.background_slider);
                    }
                    $.each(options, function (id, val) {
                        $('#tfb_row_settings').find('#' + id).val(val);
                    });

                    $('#tfb_row_settings').find('.tfb_lb_option[type=radio]').each(function () {
                        var id = $(this).prop('name');
                        if ('undefined' !== typeof options[id]) {
                            if ($(this).val() === options[id]) {
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
                        $found_element = options ? options[this_option_id] : false;

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

                // set touch element
                self.touchElement();

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
                    if (options && options[id]) {
                        options[id] = typeof options[id] == 'string' ? [options[id]] : options[id]; // cast the option value as array
                        // First unchecked all to fixed checkbox has default value.
                        $(this).find('.tf-checkbox').prop('checked', false);
                        // Set the values
                        $.each(options[id], function (i, v) {
                            $('.tf-checkbox[value="' + v + '"]').prop('checked', true);
                        });
                    }
                });

                $('body').trigger('editing_row_option', [options]);

                // builder drag n drop init
                self.rowOptionBuilder();

                // "Apply all" // apply all init
                self.applyAll_init();

                ThemifyBuilderCommon.Lightbox.rememberRow();
                self.liveStylingInstance.init($currentSelectedRow, options);                 
                if($this.closest('.themify_builder_style_row').length>0){
                    $('a[href="#themify_builder_row_fields_styling"]').trigger('click');
                }
            };

            ThemifyBuilderCommon.highlightRow($this.closest('.themify_builder_row'));

            ThemifyBuilderCommon.undoManager.setStartValue( $currentSelectedRow.closest('.themify_builder_content')[0].innerHTML );

            ThemifyBuilderCommon.Lightbox.open({ loadMethod: 'inline', templateID: 'builder_form_row' }, callback);
        },
        optionColumn: function (e) {
            e.preventDefault();

            var self = ThemifyPageBuilder;

            var $this = $(this);

            var $currentSelectedCol = $this.closest('.themify_builder_col');
            var options = ThemifyBuilderCommon.getColumnStylingSettings($currentSelectedCol);

            var callback = function () {
                if ('object' === typeof options) {
                    
                    if(options.background_slider){
                        self.getShortcodePreview($('#background_slider'),options.background_slider);
                    }
                    $.each(options, function (id, val) {
                        $('#tfb_column_settings').find('#' + id).val(val);
                    });

                    $('#tfb_column_settings').find('.tfb_lb_option[type=radio]').each(function () {
                        var id = $(this).prop('name');
                        if ('undefined' !== typeof options[id]) {
                            if ($(this).val() === options[id]) {
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

                // set touch element
                self.touchElement();

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
                    if (options[id]) {
                        options[id] = typeof options[id] == 'string' ? [options[id]] : options[id]; // cast the option value as array
                        // First unchecked all to fixed checkbox has default value.
                        $(this).find('.tf-checkbox').prop('checked', false);
                        // Set the values
                        $.each(options[id], function (i, v) {
                            $('.tf-checkbox[value="' + v + '"]').prop('checked', true);
                        });
                    }
                });

                $('body').trigger('editing_column_option', [options]);

                // "Apply all" // apply all init
                self.applyAll_init();

                ThemifyBuilderCommon.Lightbox.rememberRow();
                self.liveStylingInstance.init($currentSelectedCol, options);
            };

            ThemifyBuilderCommon.highlightColumn($this.closest('.themify_builder_col'));
            ThemifyBuilderCommon.highlightRow($this.closest('.themify_builder_row'));

            ThemifyBuilderCommon.undoManager.setStartValue( $currentSelectedCol.closest('.themify_builder_content')[0].innerHTML );

            ThemifyBuilderCommon.Lightbox.open({ loadMethod: 'inline', templateID: 'builder_form_column' }, callback);
        },
        rowSaving: function (e) {
            e.preventDefault();
            var self = ThemifyPageBuilder,
                $currentSelectedRow = $('.current_selected_row'),
                builder_id = $currentSelectedRow.closest('.themify_builder_content').data('postid'),
                $active_row_settings = $('.current_selected_row .row-data-styling'),
                temp_appended_data = $('#tfb_row_settings .tfb_lb_option').serializeObject();

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

            $active_row_settings.data('styling', temp_appended_data);

            var sendData = ThemifyPageBuilder._getSettings($currentSelectedRow, 0);

            self.liveUpdateRow(builder_id, sendData, null,
                    ThemifyBuilderCommon.Lightbox.previewButtonClicked($(this)));

            self.editing = true;
        },
        columnSaving: function (e) {
            e.preventDefault();
            var self = ThemifyPageBuilder,
                    $currentSelectedColumn = $('.current_selected_column'),
                    builder_id = $currentSelectedColumn.closest('.themify_builder_content').data('postid'),
                    $active_column_settings = $('.current_selected_column').children('.column-data-styling'),
                    colLocationObj = {};

            var $parentCol = $currentSelectedColumn.parent().closest('.themify_builder_col');

            // Detect if a column OR a sub-column was selected
            if ($parentCol.length) {
                // a sub-column was selected
                colLocationObj['sub-col_index'] = $currentSelectedColumn.index();
                colLocationObj['col_index'] = $parentCol.index();
            } else {
                colLocationObj['col_index'] = $currentSelectedColumn.index();
            }

            var temp_appended_data = $('#tfb_column_settings .tfb_lb_option').serializeObject();

            $active_column_settings.data('styling', temp_appended_data);

            var sendData = ThemifyPageBuilder._getSettings($('.current_selected_row'), 0);

            self.liveUpdateRow(builder_id, sendData, colLocationObj,
                    ThemifyBuilderCommon.Lightbox.previewButtonClicked($(this)));

            self.editing = true;
        },
        // TODO: remove previewOnly as preview btn was removed.
        liveUpdateRow: function (builder_id, sendData, colLocationObj, previewOnly) {
            var self = ThemifyPageBuilder,
                    $currentSelectedRow = $('.current_selected_row'),
                    rowIndex = $currentSelectedRow.index(),
                    class_hilite = 'r' + rowIndex,
                    hilite_obj = {row: rowIndex},
                    $builder_selector = $currentSelectedRow.closest('.themify_builder_content'),
                    previewOnly = typeof previewOnly !== 'undefined' ? previewOnly : false,
                    startValue = ThemifyBuilderCommon.undoManager.getStartValue() || $builder_selector[0].innerHTML; // startValue is used to capture previous builder output before its updated. used for undo/redo features.

            $currentSelectedRow.wrap('<div class="temp_row_placeholder ' + class_hilite + '" />');

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                dataType: 'json',
                data:
                        {
                            action: 'tfb_load_row_partial',
                            post_id: builder_id,
                            nonce: themifyBuilder.tfb_load_nonce,
                            row: sendData,
                            builder_grid_activate: 1
                        },
                beforeSend: function (xhr) {
                    if (previewOnly) {
                        ThemifyBuilderCommon.showLoader('lightbox-preview');
                    } else {
                        ThemifyBuilderCommon.showLoader('show');
                        ThemifyBuilderCommon.Lightbox.close();

                    }
                },
                success: function (data) {
                    var $newElems = $(data.html),
                            parent = $builder_selector.find('.temp_row_placeholder.' + class_hilite);

                    // goto mod element
                    if (parent.length > 0) {
                        $('html,body').animate({scrollTop: parent.offset().top - 150}, 500);
                        parent.empty();
                    }

                    $newElems.addClass('current_selected_row');

                    var $currentCol = null;

                    // for col/sub-col styling, select current column
                    if (typeof colLocationObj !== 'undefined' && colLocationObj !== null) {
                        $currentCol = ThemifyBuilderCommon.findColumnInNewRow($newElems, colLocationObj);

                        $currentCol.addClass('current_selected_column');
                    }

                    $newElems.css('visibility', 'visible');
                    parent.get(0).innerHTML = $newElems.get(2).outerHTML;
                    parent.find('.themify_builder_row').first().unwrap();

                    if (previewOnly) {
                        var $currentStyledComponent = $newElems;
                        var stylingSettings = ThemifyBuilderCommon.getRowStylingSettings($newElems);

                        if ($currentCol !== null) {
                            $currentStyledComponent = $currentCol;
                            stylingSettings = ThemifyBuilderCommon.getColumnStylingSettings($currentCol);
                        }

                        self.liveStylingInstance.init(
                                $currentStyledComponent,
                                stylingSettings
                                );

                    } else {
                        self.newRowAvailable();
                    }

                    self.moduleEvents();
                    self.loadContentJs();

                    // Load google font style
                    if ('undefined' !== typeof WebFont && data.gfonts.length > 0) {
                        WebFont.load({
                            google: {
                                families: data.gfonts
                            }
                        });
                    }

                    if (previewOnly) {
                        ThemifyBuilderCommon.showLoader('lightbox-preview-hide');
                    } else {
                        ThemifyBuilderCommon.showLoader('hide');

                        // Logs undo action
                        var newValue = $builder_selector[0].innerHTML;
                        if ( startValue !== newValue ) {
                            ThemifyBuilderCommon.undoManager.events.trigger('change', [$builder_selector[0], startValue, newValue]);
                        }
                    }

                    // Hook
                    $('body').trigger('builder_load_row_partial', $newElems);
                }
            });
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
                    pconfig["multipart_params"]['topost'] = themifyBuilder.post_ID;

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
                    postid: themifyBuilder.post_ID
                }
            },
            callback = function () {
                // plupload init
                ThemifyPageBuilder.builderPlupload('normal');
            };
            ThemifyBuilderCommon.Lightbox.open(options, callback);
        },
        copyComponentBuilder: function (event) {
            event.preventDefault();

            var $thisElem = $(this);
            var self = ThemifyPageBuilder;
            var component = ThemifyBuilderCommon.detectBuilderComponent($thisElem);

            switch (component) {
                case 'row':
                    var $selectedRow = $thisElem.closest('.themify_builder_row');

                    var rowOrder = $selectedRow.index();
                    var rowData = self._getSettings($selectedRow, rowOrder);
                    var rowDataInJson = JSON.stringify(rowData);

                    ThemifyBuilderCommon.Clipboard.set('row', rowDataInJson);

                    $selectedRow.find('.themify_builder_dropdown').hide();
                    break;

                case 'sub-row':
                    var $selectedSubRow = $thisElem.closest('.themify_builder_sub_row');

                    var subRowOrder = $selectedSubRow.index();
                    var subRowData = self._getSubRowSettings($selectedSubRow, subRowOrder);
                    var subRowDataInJSON = JSON.stringify(subRowData);

                    ThemifyBuilderCommon.Clipboard.set('sub-row', subRowDataInJSON);
                    break;

                case 'module':
                    var $selectedModule = $thisElem.closest('.themify_builder_module_front');

                    var moduleName = $selectedModule.find('.front_mod_settings').data('mod-name');
                    var moduleData = JSON.parse($selectedModule.find('.front_mod_settings')
                            .find('script[type="text/json"]').text());

                    var moduleDataInJson = JSON.stringify({
                        mod_name: moduleName,
                        mod_settings: moduleData
                    });

                    ThemifyBuilderCommon.Clipboard.set('module', moduleDataInJson);
                    break;
            }
        },
        pasteComponentBuilder: function (event) {
            event.preventDefault();

            var $thisElem = $(this);
            var self = ThemifyPageBuilder;
            var component = ThemifyBuilderCommon.detectBuilderComponent($thisElem);

            var dataInJSON = ThemifyBuilderCommon.Clipboard.get(component);

            if (dataInJSON === false) {
                ThemifyBuilderCommon.alertWrongPaste();
                return;
            }

            if (!ThemifyBuilderCommon.confirmDataPaste()) {
                return;
            }

            switch (component) {
                case 'row':
                    var $selectedRow = $thisElem.closest('.themify_builder_row');

                    ThemifyBuilderCommon.highlightRow($selectedRow);

                    var rowDataPlainObject = JSON.parse(dataInJSON);

                    var builderId = $selectedRow.closest('.themify_builder_content').data('postid');

                    self.liveUpdateRow(builderId, rowDataPlainObject);
                    break;

                case 'sub-row':
                    var $selectedRow = $thisElem.closest('.themify_builder_row');
                    var $selectedSubRow = $thisElem.closest('.themify_builder_sub_row');

                    ThemifyBuilderCommon.highlightRow($selectedRow);

                    var subRowDataPlainObject = JSON.parse(dataInJSON);

                    var rowOrder = $selectedRow.index();
                    // Get sub-row's parent row data and inject the sub-row's data into proper space.
                    var rowData = self._getSettings($selectedRow, rowOrder);

                    var selectedSubRowOrder = $selectedSubRow.index().toString();
                    var colOrder = $selectedSubRow.closest('.themify_builder_col').index().toString();

                    // Inject the pasted sub-row's data into proper module.
                    rowData['cols'][colOrder]['modules'][selectedSubRowOrder] = subRowDataPlainObject;

                    var builderId = $selectedRow.closest('.themify_builder_content').data('postid');

                    self.liveUpdateRow(builderId, rowData);
                    break;

                case 'module':
                    var $selectedModule = $thisElem.closest('.module_menu_front');

                    self.highlightModuleFront($selectedModule);

                    var modDataPlainObject = JSON.parse(dataInJSON);

                    var modSettings = modDataPlainObject['mod_settings'];
                    var modName = modDataPlainObject['mod_name'];

                    var hilite = $('.current_selected_module').parents('.themify_builder_module_front'),
                            class_hilite = self.getHighlightClass(hilite),
                            hilite_obj = self.getHighlightObject(hilite);

                    $('#themify_builder_lightbox_parent').hide();
                    hilite.wrap('<div class="temp_placeholder ' + class_hilite + '" />').find('.themify_builder_module_front_overlay').show();

                    self.updateContent(class_hilite, hilite_obj, modName, modSettings);

                    ThemifyBuilderCommon.showLoader('hide');
                    break;
            }

            self.editing = true;
        },
        importComponentBuilder: function (event) {
            event.preventDefault();

            var $thisElem = $(this);
            var self = ThemifyPageBuilder;
            var component = ThemifyBuilderCommon.detectBuilderComponent($thisElem);

            var options = {
                data: {
                    action: 'tfb_imp_component_data_lightbox_options'
                }
            };

            switch (component) {
                case 'row':
                    var $selectedRow = $thisElem.closest('.themify_builder_row');
                    options.data.component = 'row';

                    ThemifyBuilderCommon.highlightRow($selectedRow);
                    ThemifyBuilderCommon.Lightbox.open(options, null);
                    break;

                case 'sub-row':
                    var $selectedSubRow = $thisElem.closest('.themify_builder_sub_row');
                    var $selectedRow = $thisElem.closest('.themify_builder_row');
                    options.data.component = 'sub-row';

                    ThemifyBuilderCommon.highlightRow($selectedRow);
                    ThemifyBuilderCommon.highlightSubRow($selectedSubRow);
                    ThemifyBuilderCommon.Lightbox.open(options, null);
                    break;

                case 'module':
                    var $selectedModule = $thisElem.closest('.module_menu_front');
                    options.data.component = 'module';

                    self.highlightModuleFront($selectedModule);
                    ThemifyBuilderCommon.Lightbox.open(options, null);
                    break;
            }
        },
        exportComponentBuilder: function (event) {
            event.preventDefault();

            var $thisElem = $(this);
            var self = ThemifyPageBuilder;
            var component = ThemifyBuilderCommon.detectBuilderComponent($thisElem);

            var options = {
                data: {
                    action: 'tfb_exp_component_data_lightbox_options'
                }
            };

            switch (component) {
                case 'row':
                    var $selectedRow = $thisElem.closest('.themify_builder_row');
                    options.data.component = 'row';

                    var rowCallback = function () {
                        var rowOrder = $selectedRow.index();

                        var rowData = self._getSettings($selectedRow, rowOrder);
                        rowData['component_name'] = 'row';

                        var rowDataInJson = JSON.stringify(rowData);

                        var $rowDataTextField = $('#tfb_exp_row_data_field');
                        $rowDataTextField.val(rowDataInJson);

                        self._autoSelectInputField($rowDataTextField);
                        $rowDataTextField.on('click', function () {
                            self._autoSelectInputField($rowDataTextField)
                        });
                    };

                    ThemifyBuilderCommon.Lightbox.open(options, rowCallback);
                    break;

                case 'sub-row':
                    var $selectedSubRow = $thisElem.closest('.themify_builder_sub_row');
                    options.data.component = 'sub-row';

                    var subRowCallback = function () {
                        var subRowOrder = $selectedSubRow.index();

                        var subRowData = self._getSubRowSettings($selectedSubRow, subRowOrder);
                        subRowData['component_name'] = 'sub-row';

                        var subRowDataInJSON = JSON.stringify(subRowData);

                        var $subRowDataTextField = $('#tfb_exp_sub_row_data_field');
                        $subRowDataTextField.val(subRowDataInJSON);

                        self._autoSelectInputField($subRowDataTextField);
                        $subRowDataTextField.on('click', function () {
                            self._autoSelectInputField($subRowDataTextField)
                        });
                    };

                    ThemifyBuilderCommon.Lightbox.open(options, subRowCallback);
                    break;

                case 'module':
                    var $selectedModule = $thisElem.closest('.themify_builder_module_front');
                    options.data.component = 'module';

                    var moduleCallback = function () {
                        var moduleName = $selectedModule.find('.front_mod_settings').data('mod-name');
                        var moduleData = JSON.parse($selectedModule.find('.front_mod_settings')
                                .find('script[type="text/json"]').text());

                        var moduleDataInJson = JSON.stringify({
                            mod_name: moduleName,
                            mod_settings: moduleData,
                            component_name: 'module'
                        });

                        var $moduleDataTextField = $('#tfb_exp_module_data_field');
                        $moduleDataTextField.val(moduleDataInJson);

                        self._autoSelectInputField($moduleDataTextField);
                        $moduleDataTextField.on('click', function () {
                            self._autoSelectInputField($moduleDataTextField)
                        });
                    };

                    ThemifyBuilderCommon.Lightbox.open(options, moduleCallback);
                    break;
            }
        },
        importRowModBuilderFormSave: function (event) {
            event.preventDefault();

            var $form = $("#tfb_imp_component_form");
            var self = ThemifyPageBuilder;
            var component = $form.find("input[name='component']").val();

            switch (component) {
                case 'row':
                    var $rowDataField = $form.find('#tfb_imp_row_data_field');
                    var rowDataPlainObject = JSON.parse($rowDataField.val());

                    if (!rowDataPlainObject.hasOwnProperty('component_name')
                            || rowDataPlainObject['component_name'] !== 'row') {
                        ThemifyBuilderCommon.alertWrongPaste();
                        return;
                    }

                    var builderId = $('.current_selected_row').closest('.themify_builder_content').data('postid');

                    self.liveUpdateRow(builderId, rowDataPlainObject);
                    break;

                case 'sub-row':
                    var $subRowDataField = $form.find('#tfb_imp_sub_row_data_field');
                    var subRowDataPlainObject = JSON.parse($subRowDataField.val());

                    if (!subRowDataPlainObject.hasOwnProperty('component_name')
                            || subRowDataPlainObject['component_name'] !== 'sub-row') {
                        ThemifyBuilderCommon.alertWrongPaste();
                        return;
                    }

                    var $selectedRow = $('.current_selected_row');
                    var $selectedSubRow = $('.current_selected_sub_row');

                    var rowOrder = $selectedRow.index();
                    // Get sub-row's parent row data and inject the sub-row's data into proper space.
                    var rowData = self._getSettings($selectedRow, rowOrder);

                    var selectedSubRowOrder = $selectedSubRow.index().toString();
                    var colOrder = $selectedSubRow.closest('.themify_builder_col').index().toString();

                    // Inject the pasted sub-row's data into proper module.
                    rowData['cols'][colOrder]['modules'][selectedSubRowOrder] = subRowDataPlainObject;

                    var builderId = $selectedRow.closest('.themify_builder_content').data('postid');

                    self.liveUpdateRow(builderId, rowData);
                    break;

                case 'module':
                    var $modDataField = $form.find('#tfb_imp_module_data_field');
                    var modDataPlainObject = JSON.parse($modDataField.val());

                    if (!modDataPlainObject.hasOwnProperty('component_name')
                            || modDataPlainObject['component_name'] !== 'module') {
                        ThemifyBuilderCommon.alertWrongPaste();
                        return;
                    }

                    var modSettings = modDataPlainObject['mod_settings'];
                    var modName = modDataPlainObject['mod_name'];

                    var hilite = $('.current_selected_module').parents('.themify_builder_module_front'),
                            class_hilite = self.getHighlightClass(hilite),
                            hilite_obj = self.getHighlightObject(hilite);

                    ThemifyBuilderCommon.Lightbox.close();
                    hilite.wrap('<div class="temp_placeholder ' + class_hilite + '" />').find('.themify_builder_module_front_overlay').show();

                    self.updateContent(class_hilite, hilite_obj, modName, modSettings);

                    ThemifyBuilderCommon.showLoader('hide');
                    break;
            }

            self.editing = true;
        },
        _autoSelectInputField: function ($inputField) {
            $inputField.trigger('focus').trigger('select');
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
                        current_builder_id: themifyBuilder.post_ID,
                        builtin_layout: $this.data('builtin')
                    },
                    success: function (data) {
                        ThemifyBuilderCommon.Lightbox.close();
                        if (data.status == 'success') {
                            window.location.hash = '#builder_active';
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
        },
        _gridMenuClicked: function (event) {
            event.preventDefault();
            var $this = $(this),
                set = $(this).data('grid'),
                handle = $(this).data('handle'), $builder_container = $this.closest('.themify_builder_content')[0], 
                $base, is_sub_row = false, startValue = $builder_container.innerHTML;

            $(this).closest('.themify_builder_grid_list').find('.selected').removeClass('selected');
            $(this).closest('li').addClass('selected');
            var $equial_height = $(this).closest('.themify_builder_grid_list_wrapper').find('.themify_builder_equal_column_height');
            if (set[0] === '-full') {
                $equial_height.hide();
            }
            else {
                $equial_height.show();
            }
            switch (handle) {
                case 'module':
                    var sub_row_func = wp.template('builder_sub_row'),
                            tmpl_sub_row = sub_row_func({placeholder: themifyBuilder.dropPlaceHolder, newclass: 'col-full'}),
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

            ThemifyPageBuilder.moduleEvents();

            // Log the action
            var newValue = $builder_container.innerHTML;
            if ( startValue !== newValue ) {
                ThemifyBuilderCommon.undoManager.events.trigger('change', [$builder_container, startValue, newValue]);
            }
        },
        _addNewColumn: function (params, $context) {
            var tmpl_func = wp.template('builder_column'),
                    template = tmpl_func(params);
            $context.append($(template));
        },
        _gridHover: function (event) {
            if (event.type == 'touchend') {
                var $column_menu = $(this).find('.themify_builder_grid_list_wrapper');
                if ($column_menu.is(':hidden')) {
                    $column_menu.show();
                } else {
                    $column_menu.hide();
                }
            }
            else if (event.type == 'mouseenter') {
                $(this).find('.themify_builder_grid_list_wrapper').stop(false, true).show();
            } else if (event.type == 'mouseleave' && (event.toElement || event.relatedTarget)) {
                $(this).find('.themify_builder_grid_list_wrapper').stop(false, true).hide();
            }
        },
        _gutterChange: function (event) {
            var handle = $(this).data('handle');
            if (handle == 'module')
                return;

            switch (handle) {
                case 'sub_row':
                    $(this).closest('.themify_builder_sub_row').data('gutter', this.value).removeClass(themifyBuilder.gutterClass).addClass(this.value);
                    break;

                default:
                    $(this).closest('.themify_builder_row').data('gutter', this.value).removeClass(themifyBuilder.gutterClass).addClass(this.value);
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

                var $selected = $(this).find('.grid-layout-' + grid_base.join('-'));
                $selected.closest('li').addClass('selected');
                var $grid = $selected.data('grid');
                if ($grid && $grid[0] === '-full') {
                    $selected.closest('.themify_builder_grid_list_wrapper').find('.themify_builder_equal_column_height').hide();
                }

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
        _RefreshHolderHeight: function(){
            ThemifyPageBuilder.makeEqual($('.themify_builder_row:visible'), '.themify_builder_row_content:visible');
            ThemifyPageBuilder.makeEqual($('.themify_builder_sub_row:visible'), '.themify_builder_sub_row_content');
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
                ThemifyPageBuilder.moduleEvents();
                ThemifyPageBuilder.editing = true;
            }
        },
        _subRowDuplicate: function (event) {
            event.preventDefault();
            $(this).closest('.themify_builder_sub_row').clone().insertAfter($(this).closest('.themify_builder_sub_row'));
            ThemifyPageBuilder.moduleEvents();
            ThemifyPageBuilder.editing = true;
        },
        hideColumnStylingIcon: function (event) {
            var $colStylingIcon = $(this).closest('.themify_builder_col').children('.themify_builder_column_styling_icon').first();

            if (event.type == 'mouseenter') {
                $colStylingIcon.hide();
            } else {
                $colStylingIcon.css('display', '');
            }
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
            ThemifyPageBuilder.loadContentJs();
            $('.themify_builder_module_front_overlay').hide();
            ThemifyBuilderCommon.undoManager.startValue = null; // clear temp
        },
        _ajaxStart: function() {
            document.querySelector('.themify-builder-front-save').classList.add('disabled');
        },
        _ajaxComplete: function() {
            document.querySelector('.themify-builder-front-save').classList.remove('disabled');
        },
        loadRevisionLightbox: function( event ) {
            event.preventDefault();
            event.stopPropagation();
            var options = {
                data: {
                    action: 'tfb_load_revision_lists',
                    postid: themifyBuilder.post_ID,
                    tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                }
            };
            ThemifyBuilderCommon.Lightbox.open(options, function(){
                $('.themify_builder_options_tab li:first-child').addClass('current');
            });
        },
        saveRevisionLightbox: function( event ) {
            event.preventDefault();
            event.stopPropagation();
            ThemifyPageBuilder._saveRevision();
        },
        _saveRevision: function( callback ) {
            ThemifyBuilderCommon.LiteLightbox.prompt( themifyBuilder.enterRevComment, function( result ){
                if ( result !== null ) {
                    console.log(result, 'comment msg');
                    $.ajax({
                        type: "POST",
                        url: themifyBuilder.ajaxurl,
                        data: {
                            action: 'tfb_save_revision',
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            postid: themifyBuilder.post_ID,
                            rev_comment: result
                        },
                        beforeSend: function (xhr) {
                            ThemifyBuilderCommon.showLoader('show');
                        },
                        success: function (data) {
                            if ( data.success ) {
                                // load callback
                                if ($.isFunction(callback)) {
                                    callback.call(this, data);
                                }
                            } else {
                                alert( data.data );
                            }
                            ThemifyBuilderCommon.showLoader('hide');
                        }
                    });
                }
            } );
        },
        restoreRevision: function( event ) {
            event.preventDefault();
            var revID = $(this).data('rev-id'),
                restoreIt = function() {
                    $.ajax({
                        type: "POST",
                        url: themifyBuilder.ajaxurl,
                        data: {
                            action: 'tfb_restore_revision_page',
                            tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                            revid: revID
                        },
                        beforeSend: function (xhr) {
                            ThemifyBuilderCommon.showLoader('show');
                        },
                        success: function (data) {
                            if ( data.success ) {
                                ThemifyBuilderCommon.showLoader('hide');
                                ThemifyBuilderCommon.Lightbox.close();
                                window.location.reload();
                            } else {
                                ThemifyBuilderCommon.showLoader('error');
                                alert( data.data );
                            }
                        }
                    });
                };

            ThemifyBuilderCommon.LiteLightbox.confirm( themifyBuilder.confirmRestoreRev, function( response ){
                console.log( response, 'response');
                if ( 'yes' == response ) {
                    ThemifyPageBuilder._saveRevision( restoreIt );
                } else {
                    restoreIt();
                }
            });
            
        },
        deleteRevision: function( event ) {
            event.preventDefault();
            var $this = $(this),
                revID = $(this).data('rev-id');
            if ( ! confirm( themifyBuilder.confirmDeleteRev ) ) return;

            $.ajax({
                type: "POST",
                url: themifyBuilder.ajaxurl,
                data: {
                    action: 'tfb_delete_revision',
                    tfb_load_nonce: themifyBuilder.tfb_load_nonce,
                    revid: revID
                },
                beforeSend: function (xhr) {
                    ThemifyBuilderCommon.showLoader('show');
                    $this.parent().hide();
                },
                success: function (data) {
                    if ( ! data.success ) {
                        ThemifyBuilderCommon.showLoader('error');
                        $this.parent().show();
                        alert( data.data );
                    } else {
                        ThemifyBuilderCommon.showLoader('hide');
                    }
                }
            });
        },
        toggleRevDropdown: function( event ) {
            if (event.type == 'mouseenter' && $(this).hasClass('themify-builder-revision-dropdown-panel') ) {
                $(this).find('ul').addClass('hover');
            }
            if (event.type == 'mouseenter' && $(this).hasClass('themify-builder-front-save-title') ) {
                $(this).next().find('ul').removeClass('hover');
            }
            if (event.type == 'mouseleave' && $(this).hasClass('themify-builder-front-save') ) {
                $(this).find('ul').removeClass('hover');
            }
        }
    };

    ThemifyLiveStyling = (function ($, jss) {

        function ThemifyLiveStyling() {
            this.$context = $('#themify_builder_lightbox_parent');
            this.elmtSelector = '#builder_live_styled_elmt';
            this.isInit = false;

            this.bindLightboxForm();

            $(document).trigger('tfb.live_styling.after_create', this);
        }

        ThemifyLiveStyling.prototype.init = function ($liveStyledElmt, currentStyleObj) {
            this.remove(); // remove previous live styling, if any

            this.$liveStyledElmt = $liveStyledElmt;

            if (typeof currentStyleObj === 'object') {
                this.currentStyleObj = currentStyleObj;
            } else {
                this.currentStyleObj = {};
            }

            this.setLiveStyledElmtID();
            this.isInit = true;

            $(document).trigger('tfb.live_styling.after_init', this);
        };

        ThemifyLiveStyling.prototype.setLiveStyledElmtID = function () {
            this.$liveStyledElmt.attr('id', this.elmtSelector.substring(1));
        };

        ThemifyLiveStyling.prototype.unsetLiveStyledElmtID = function () {
            this.$liveStyledElmt.attr('id', '');
        };

        /**
         * Apply CSS rules to the live styled element.
         *
         * @param {Object} newStyleObj Object containing CSS rules for the live styled element.
         * @param {Array} selectors List of selectors to apply the newStyleObj to (e.g., ['', 'h1', 'h2']).
         */
        ThemifyLiveStyling.prototype.setLiveStyle = function (newStyleObj, selectors) {
            var self = this;

            selectors.forEach(function (selector) {
                var fullSelector = self.elmtSelector;

                if (selector.length > 0) {
                    fullSelector += ' ' + selector;
                }

                jss.set(fullSelector, newStyleObj);

                //logging(fullSelector);
            });

            function logging(fullSelector) {
                console.log(fullSelector + ':', jss.get(fullSelector));
            }
        };

        ThemifyLiveStyling.prototype.fontFamily = function (fontFamilySelector, CSSrule, selectors) {
            var self = this;

            this.$context.on('change', fontFamilySelector, function () {
                var fontFamily = $(this).val();

                if (!self.isWebSafeFont(fontFamily)) {
                    var googleFontFamily = fontFamily.split(' ').join('+');

                    ThemifyBuilderCommon.loadGoogleFonts([
                        googleFontFamily + ':400,700:latin,latin-ext'
                    ]);

                    // Put quotes around font family name.
                    fontFamily = "'" + fontFamily + "'";
                }

                var newStyle = {};

                newStyle[CSSrule] = fontFamily;

                self.setLiveStyle(newStyle, selectors);

            });
        };

        ThemifyLiveStyling.prototype.textInputWithUnit = function (inputSelector, CSSrule, selectors) {
            var self = this;

            this.$context.on('keyup', inputSelector, function () {
                var $input = $(inputSelector);
                var $inputUnit = $(inputSelector + '_unit');

                var newStyle = {};

                newStyle[CSSrule] = $input.val() + $inputUnit.val();

                self.setLiveStyle(newStyle, selectors);
            });

            this.$context.on('change', inputSelector + '_unit', function () {
                var $input = $(inputSelector);
                var $inputUnit = $(inputSelector + '_unit');

                var newStyle = {};

                newStyle[CSSrule] = $input.val() + $inputUnit.val();

                self.setLiveStyle(newStyle, selectors)
            });
        };

        ThemifyLiveStyling.prototype.textInput = function (inputSelector, CSSrule, selectors, unit) {
            var self = this;

            this.$context.on('keyup', inputSelector, function () {
                var $input = $(inputSelector);
                var newStyle = {};

                var val = $input.val();

                if (typeof unit !== 'undefined') {
                    val += unit;
                }

                newStyle[CSSrule] = val;

                self.setLiveStyle(newStyle, selectors);
            });
        };

        ThemifyLiveStyling.prototype.radioWithWrapper = function (radioWrapperSelector, CSSrule, selectors) {
            var self = this;
            var radioElmtsSelector = radioWrapperSelector + ' input[type="radio"]';

            this.$context.on('change', radioElmtsSelector, function () {
                var newStyle = {};

                var val = ThemifyBuilderCommon.getCheckedRadioInGroup($(this), self.$context).val();

                if (typeof val === 'undefined') {
                    val = '';
                }

                newStyle[CSSrule] = val;

                self.setLiveStyle(newStyle, selectors);
            });
        };

        ThemifyLiveStyling.prototype.selectbox = function (selectboxSelector, CSSrule, selectors) {
            var self = this;

            this.$context.on('change', selectboxSelector, function () {
                var $selectbox = $(selectboxSelector);
                var newStyle = {};

                newStyle[CSSrule] = $selectbox.val();

                self.setLiveStyle(newStyle, selectors);
            });
        };

        ThemifyLiveStyling.prototype.bindRadioBoxes = function () {
            this.radioWithWrapper('#text_align', 'text-align', ['']);
        };

        ThemifyLiveStyling.prototype.bindColors = function () {
            var self = this;

            var simpleColors = {
                'background_color': 'background-color',
                'border_top_color': 'border-top-color',
                'border_right_color': 'border-right-color',
                'border_bottom_color': 'border-bottom-color',
                'border_left_color': 'border-left-color'
            };

            var fontColor = {
                'font_color': 'color'
            };

            var linkColor = {
                'link_color': 'color'
            };

            // TODO: move this in event handler.
            var newStyle = {};
            var colorCSSRule = '';

            $('body').on('themify_builder_color_picker_change', function (e, colorType, rgbaString) {
                if (colorType in simpleColors) {
                    colorCSSRule = simpleColors[colorType];

                    newStyle[colorCSSRule] = rgbaString;

                    self.setLiveStyle(newStyle, ['']);
                } else if (colorType in fontColor) {
                    colorCSSRule = fontColor[colorType];

                    newStyle[colorCSSRule] = rgbaString;

                    self.setLiveStyle(newStyle, [''].concat(self.getSpecialTextSelectors()));
                } else if (colorType in linkColor) {
                    colorCSSRule = linkColor[colorType];

                    newStyle[colorCSSRule] = rgbaString;

                    self.setLiveStyle(newStyle, ['a'])
                } else if (colorType == 'cover_color') {
                    self.addOrRemoveComponentOverlay(rgbaString);
                }
            });
        };

        ThemifyLiveStyling.prototype.bindColorInputBoxes = function () {
            this.$context.on('change', 'input.colordisplay', function () {
                var $colorDisplayInput = $(this);
                var hexString = '';

                if ($colorDisplayInput.val().length) {
                    hexString = '#' + $colorDisplayInput.val();
                }

                var $cssRuleInput = $colorDisplayInput.parent().find('.builderColorSelectInput');

                $('body').trigger(
                        'themify_builder_color_picker_change',
                        [$cssRuleInput.attr('name'), hexString]
                        );
            });
        };

        ThemifyLiveStyling.prototype.getSpecialTextSelectors = function () {
            return ['h1', 'h2', 'h3:not(.module-title)', 'h4', 'h5', 'h6'];
        };

        ThemifyLiveStyling.prototype.addOrRemoveComponentOverlay = function (rgbaString) {
            var $overlayElmt = ThemifyLiveStyling.getComponentBgOverlay(this.$liveStyledElmt);

            if (!rgbaString.length) {
                $overlayElmt.remove();

                return;
            }

            if ($overlayElmt.length) {
                $overlayElmt.data('color', rgbaString);

                $overlayElmt.css('background-color', rgbaString);
                return;
            }

            $overlayElmt = $('<div/>', {class: 'builder_row_cover'});
            $overlayElmt.css('background-color', rgbaString);
            $overlayElmt.data('color', rgbaString);

            var $elmtToInsertBefore = ThemifyLiveStyling.getComponentBgSlider(this.$liveStyledElmt);

            if (!$elmtToInsertBefore.length) {
                var selector = '';
                var componentType = ThemifyBuilderCommon.getComponentType(this.$liveStyledElmt);

                if (componentType === 'row') {
                    selector = '.row_inner_wrapper';
                } else if (componentType === 'col' || componentType === 'sub-col') {
                    selector = '.themify_builder_column_styling_icon'
                }

                $elmtToInsertBefore = this.$liveStyledElmt.children(selector);
            }

            $overlayElmt.insertBefore($elmtToInsertBefore);
        };


        ThemifyLiveStyling.prototype.bindSelectboxes = function () {
            var self = this;

            var selectboxes = {
                '#border_top_style': 'border-top-style',
                '#border_right_style': 'border-right-style',
                '#border_bottom_style': 'border-bottom-style',
                '#border_left_style': 'border-left-style'
            };

            Object.keys(selectboxes).forEach(function (selectboxSelector) {
                self.selectbox(selectboxSelector, selectboxes[selectboxSelector], ['']);
            });

            // Link
            self.selectbox('#text_decoration', 'text-decoration', ['a']);
        };

        ThemifyLiveStyling.prototype.bindInputsWithUnit = function () {
            var self = this;

            var inputsWithUnit = {
                '#padding_top': 'padding-top',
                '#padding_right': 'padding-right',
                '#padding_bottom': 'padding-bottom',
                '#padding_left': 'padding-left',
                '#margin_top': 'margin-top',
                '#margin_right': 'margin-right',
                '#margin_bottom': 'margin-bottom',
                '#margin_left': 'margin-left',
                '#font_size': 'font-size',
                '#line_height': 'line-height'
            };

            Object.keys(inputsWithUnit).forEach(function (inputSelector) {
                self.textInputWithUnit(inputSelector, inputsWithUnit[inputSelector], ['']);
            });
        };

        ThemifyLiveStyling.prototype.bindTextInputs = function () {
            var self = this;

            var textInputs = {
                '#border_top_width': 'border-top-width',
                '#border_right_width': 'border-right-width',
                '#border_bottom_width': 'border-bottom-width',
                '#border_left_width': 'border-left-width'
            };

            Object.keys(textInputs).forEach(function (inputSelector) {
                self.textInput(inputSelector, textInputs[inputSelector], [''], 'px');
            });
        };

        ThemifyLiveStyling.prototype.bindRowWidthHeight = function () {
            var self = this;

            this.$context.on('change', 'input[name="row_width"]', function () {
                var rowWidthVal = self.getStylingVal('row_width');

                var val = ThemifyBuilderCommon.getCheckedRadioInGroup($(this), self.$context).val();

                if (val.length > 0) {
                    if (rowWidthVal.length > 0) {
                        self.$liveStyledElmt.removeClass(rowWidthVal);
                    }

                    rowWidthVal = val;

                    self.setStylingVal('row_width', rowWidthVal);
                    self.$liveStyledElmt.addClass(rowWidthVal);
                } else {
                    self.$liveStyledElmt.removeClass(rowWidthVal);
                }
            });

            this.$context.on('change', 'input[name="row_height"]', function () {
                var rowHeightVal = self.getStylingVal('row_height');

                var val = ThemifyBuilderCommon.getCheckedRadioInGroup($(this), self.$context).val();

                if (val.length > 0) {
                    if (rowHeightVal.length > 0) {
                        self.$liveStyledElmt.removeClass(rowHeightVal);
                    }

                    rowHeightVal = val;

                    self.setStylingVal('row_height', rowHeightVal);
                    self.$liveStyledElmt.addClass(rowHeightVal);

                } else {
                    self.$liveStyledElmt.removeClass(rowHeightVal);
                }
            });
        };

        ThemifyLiveStyling.prototype.removeAnimations = function (animationEffect, $elmt) {
            $elmt.removeClass(animationEffect);
            $elmt.removeClass('wow');
            $elmt.removeClass('animated');
            $elmt.css('animation-name', '');
        };

        ThemifyLiveStyling.prototype.addAnimation = function (animationEffect, $elmt) {
            $elmt.addClass(animationEffect);
            $elmt.addClass('animated');
        };

        ThemifyLiveStyling.prototype.bindAnimation = function () {
            var self = this;

            this.$context.on('change', '#animation_effect', function () {
                var animationEffect = self.getStylingVal('animation_effect');

                if ($(this).val().length) {
                    if (animationEffect.length) {
                        self.removeAnimations(animationEffect, self.$liveStyledElmt);
                    }

                    animationEffect = $(this).val();

                    self.setStylingVal('animation_effect', animationEffect);
                    self.addAnimation(animationEffect, self.$liveStyledElmt);
                } else {
                    self.removeAnimations(animationEffect, self.$liveStyledElmt);
                }
            });
        };

        ThemifyLiveStyling.prototype.bindAdditionalCSSClass = function () {
            var self = this;

            this.$context.on('keyup', '#custom_css_row, #custom_css_column, #add_css_text', function () {
                var id = this.id,
                    className = self.getStylingVal(id);

                self.$liveStyledElmt.removeClass(className);

                className = $(this).val();

                self.setStylingVal(id, className);
                self.$liveStyledElmt.addClass(className);
            });
        };

        ThemifyLiveStyling.prototype.bindRowAnchor = function () {
            var self = this;

            this.$context.on('keyup', '#row_anchor', function () {
                var rowAnchor = self.getStylingVal('row_anchor');

                self.$liveStyledElmt.removeClass(self.getRowAnchorClass(rowAnchor));

                rowAnchor = $(this).val();

                self.setStylingVal('row_anchor', rowAnchor);
                self.$liveStyledElmt.addClass(self.getRowAnchorClass(rowAnchor));
            });
        };

        ThemifyLiveStyling.prototype.getRowAnchorClass = function (rowAnchor) {
            if (!rowAnchor.length) {
                return '';
            }

            return 'tb_section-' + rowAnchor;
        };

        ThemifyLiveStyling.prototype.getStylingVal = function (stylingKey) {
            if (this.currentStyleObj.hasOwnProperty(stylingKey)) {
                return this.currentStyleObj[stylingKey];
            }

            return '';
        };

        ThemifyLiveStyling.prototype.setStylingVal = function (stylingKey, val) {
            this.currentStyleObj[stylingKey] = val;
        };

        ThemifyLiveStyling.prototype.bindBackgroundMode = function () {
            var self = this;


            this.$context.on('change', '#background_repeat', function () {
                var previousVal = self.getStylingVal('background_repeat');

                var val = $(this).val();

                if (val.length > 0) {
                    if (previousVal.length > 0) {
                        self.$liveStyledElmt.removeClass(previousVal);
                    }

                    self.setStylingVal('background_repeat', val);
                    self.$liveStyledElmt.addClass(val);
                } else {
                    self.$liveStyledElmt.removeClass(previousVal);
                }
            });

        };

        ThemifyLiveStyling.prototype.bindBackgroundImage = function () {
            var self = this;

            this.$context.on('change', '#background_image', function () {
                var bgImageURL = $(this).val();
                var val = 'url(' + bgImageURL + ')';

                if (!bgImageURL.length) {
                    val = 'none';
                }

                var newStyleObj = {
                    'background-image': val
                };

                self.setLiveStyle(newStyleObj, ['']);
            });
        };

        ThemifyLiveStyling.prototype.bindBackgroundVideo = function () {
            var self = this;

            this.$context.on('change', '#background_video', function () {
                var bgVideoURL = $(this).val();

                if (bgVideoURL.length) {
                    self.$liveStyledElmt.data('fullwidthvideo', bgVideoURL);

                    ThemifyBuilderModuleJs.fullwidthVideo(self.$liveStyledElmt);
                } else {
                    self.$liveStyledElmt.data('fullwidthvideo', '');

                    ThemifyLiveStyling.removeBgVideo(self.$liveStyledElmt);
                }
            });
        };

        ThemifyLiveStyling.prototype.isWebSafeFont = function (fontFamily) {
            /**
             *  Array containing the web safe fonts from the backend themify_get_web_safe_font_list().
             *
             * @type {Array}
             */
            var webSafeFonts = themifyBuilder.webSafeFonts;

            return webSafeFonts.indexOf(fontFamily) !== -1;
        };



        ThemifyLiveStyling.prototype.bindFontFamily = function () {
            var self = this;

            self.fontFamily('#font_family', 'font-family', [''].concat(self.getSpecialTextSelectors()));
        };

        ThemifyLiveStyling.prototype.bindBackgroundSlider = function () {
            function getBackgroundSlider(options) {
                return $.post(
                        themifyBuilder.ajaxurl,
                        {
                            nonce: themifyBuilder.tfb_load_nonce,
                            action: 'tfb_slider_live_styling',
                            tfb_background_slider_data: options
                        }
                );
            }

            var getOptions, insertBackgroundSliderToHTML, initBackgroundSlider;

            getOptions = function () {
                return {
                    shortcode: encodeURIComponent($('#background_slider').val()),
                    mode: $('#background_slider_mode').val(),
                    size: $('#background_slider_size').val(),
                    order: ThemifyBuilderCommon.getComponentOrder(this.$liveStyledElmt),
                    type: ThemifyBuilderCommon.getComponentType(this.$liveStyledElmt)
                };
            }.bind(this);

            insertBackgroundSliderToHTML = function ($backgroundSlider) {
                var liveStyledElmtType = ThemifyBuilderCommon.getComponentType(this.$liveStyledElmt);
                var bgCover = ThemifyLiveStyling.getComponentBgOverlay(this.$liveStyledElmt);

                if (bgCover.length) {
                    bgCover.after($backgroundSlider);
                } else {
                    if (liveStyledElmtType == 'row') {
                        this.$liveStyledElmt.children('.themify_builder_row_top').after($backgroundSlider);
                    } else {
                        this.$liveStyledElmt.prepend($backgroundSlider);
                    }
                }
            }.bind(this);

            initBackgroundSlider = function ($bgSlider) {
                ThemifyBuilderModuleJs.backgroundSlider($bgSlider);
            };

            var self = this;

            this.$context.on('change', '#background_slider, #background_slider_mode,#background_slider_size', function () {
                ThemifyLiveStyling.removeBgSlider(self.$liveStyledElmt);

                if (!$('#background_slider').val().length) {
                    return;
                }

                getBackgroundSlider(getOptions()).done(function (backgroundSlider) {
                    if (backgroundSlider.length < 10) {
                        return;
                    }

                    var $bgSlider = $(backgroundSlider);

                    insertBackgroundSliderToHTML($bgSlider);
                    initBackgroundSlider($($bgSlider.get(0)));
                });
            });
        };

        ThemifyLiveStyling.prototype.bindBackgroundTypeRadio = function () {
            var self = this;

            this.$context.on('change', 'input[name="background_type"]', function () {
                if (!self.isInit) {
                    return;
                }

                var bgType = ThemifyBuilderCommon.getCheckedRadioInGroup($(this), self.$context).val();

                if (bgType === 'image') {
                    ThemifyLiveStyling.removeBgSlider(self.$liveStyledElmt);
                    ThemifyLiveStyling.removeBgVideo(self.$liveStyledElmt);

                    self.$context.find('#background_image').trigger('change');

                } else if (bgType === 'video') {
                    ThemifyLiveStyling.removeBgSlider(self.$liveStyledElmt);

                    self.$context.find('#background_video').trigger('change');

                } else if (bgType === 'slider') {
                    ThemifyLiveStyling.removeBgVideo(self.$liveStyledElmt);

                    // remove bg image
                    self.setLiveStyle({
                        'background-image': 'none'
                    }, ['']);

                    self.$context.find('#background_slider').trigger('change');

                }
            });

        };

        /**
         * Binds module layout + styling options to produce live styling on change.
         */
        ThemifyLiveStyling.prototype.bindModuleLayout = function() {
            var self = this;

            var layoutStylingOptions = {
                'layout_accordion': '> ul.ui.module-accordion',
                'layout_callout': '',
                'layout_feature': '',
                'style_image': '',
                'layout_menu': 'ul.ui.nav:first',
                'layout_post': '> .builder-posts-wrap',
                'layout_tab': ''
            };

            // TODO: Optimize for speed by having one .on('click') handler
            Object.keys(layoutStylingOptions).forEach(function(layoutSelectorKey) {
                self.$context.on('click', '#' + layoutSelectorKey + ' > a', function() {
                    var selectedLayout = $(this).attr('id');

                    var $elmtToApplyTo = self.$liveStyledElmt;

                    if (layoutStylingOptions[layoutSelectorKey] !== '') {
                        $elmtToApplyTo = self.$liveStyledElmt.find(layoutStylingOptions[layoutSelectorKey]);
                    }

                    var prevLayout = self.getStylingVal(layoutSelectorKey);

                    if (layoutSelectorKey === 'layout_feature') {
                        selectedLayout = 'layout-' + selectedLayout;
                        prevLayout = 'layout-' + prevLayout;
                    }

                    $elmtToApplyTo
                        .removeClass(prevLayout)
                        .addClass(selectedLayout);

                    if (layoutSelectorKey === 'layout_feature') {
                        selectedLayout = selectedLayout.substr(7);
                    }

                    self.setStylingVal(layoutSelectorKey, selectedLayout);
                });
            });

        };
        
        /**
         * Binds module radio buttons to produce live styling on change.
         */
        ThemifyLiveStyling.prototype.bindModuleRadio = function() {
            var self = this;

            var RadioStylingOptions = {
                'buttons_style': '> .module-buttons',
                'buttons_size': '> .module-buttons',
                'icon_style': '> .module-icon',
                'icon_size': '> .module-icon'
            };

            Object.keys(RadioStylingOptions).forEach(function(radioSelectorKey) {
                self.$context.on('change', '#' + radioSelectorKey+' input[type="radio"]', function() {
                    var selectedRadio = $(this).val(),
                        $elmtToApplyTo = RadioStylingOptions[radioSelectorKey] !== ''?
                                         self.$liveStyledElmt.find(RadioStylingOptions[radioSelectorKey]):self.$liveStyledElmt,
                        prevLayout = self.getStylingVal(radioSelectorKey);
                        
                    $elmtToApplyTo.removeClass(prevLayout).addClass(selectedRadio);
                    self.setStylingVal(radioSelectorKey, selectedRadio);
                });
            });

        };
        
        ThemifyLiveStyling.prototype.bindModuleColor = function() {
            var self = this;

            /**
             * A key-value pair.
             * Key represents the ID of the element which should be listened to.
             * Value represents the selector to the element which the live styling should be applied to.
             */
            var colorStylingOptions = {
                'color_accordion': '> ul.ui.module-accordion',
                'color_box': '> .module-box-content.ui',
                'color_button': '> .ui.builder_button',
                'color_callout': '',
                'color_menu': 'ul.ui.nav:first',
                'mod_color_pricing_table': ['', '> .module-pricing-table-header', '.module-pricing-table-button'],
                'color_tab': '',
                'icon_color_bg':'> .module-icon i',
                'button_color_bg':'> .module-buttons a'
            };

            var colorStylingSelector = Object.keys(colorStylingOptions).reduce(function(selectors, selector, index) {
                var result = selectors;

                if (index !== 0) {
                    result += ',';
                }

                result += '#' + selector + ' > a';

                return result;
            }, '');
            
            self.$context.on('click', colorStylingSelector, function() {
               var $this = $(this),
                    colorSelectorKey = $this.parent().attr('id'),
                    selectedColor =  $(this).attr('id'),
                    $builder = $this.closest('.themify_builder_row_js_wrapper');

                var elmtToApplyToSelector = colorStylingOptions[colorSelectorKey];

                if (!Array.isArray(elmtToApplyToSelector)) {
                    elmtToApplyToSelector = [elmtToApplyToSelector];
                }

                var $elmtsToApplyTo = $([]);

                elmtToApplyToSelector.forEach(function(selector) {
                    if (selector === '') {
                        $elmtsToApplyTo = $elmtsToApplyTo.add(self.$liveStyledElmt);
                    } else {
                        $elmtsToApplyTo = $elmtsToApplyTo.add(
                            self.$liveStyledElmt.find(selector)
                        );
                    }

                });

                if($builder.length>0){
                    var $index = $this.closest('.themify_builder_row').index(),
                        realKey = colorSelectorKey;
                        colorSelectorKey+='_'+$index;
                        $elmtsToApplyTo = $($elmtsToApplyTo[$index]);
                }
               
                var prevColor = self.getStylingVal(colorSelectorKey);
                if(!prevColor && $builder.length>0){
                    var $rows = self.getStylingVal($builder.attr('id'));
                    if($rows[$index] && $rows[$index][realKey]){
                        prevColor = $rows[$index][realKey];
                    }
                }

                $elmtsToApplyTo
                    .removeClass(prevColor)
                    .addClass(selectedColor);

                self.setStylingVal(colorSelectorKey, selectedColor);
            });
        };

        ThemifyLiveStyling.prototype.bindModuleApppearance = function() {
            var self = this;

            var getSelectedAppearances = function(appearanceSelector) {
                var selectedAppearances = self.$context.find('#' + appearanceSelector + ' > input:checked')
                    .map(function(i, checkbox) {
                        return $(checkbox).val();
                    })
                    .toArray();

                return selectedAppearances.join(' ');
            };

            var appearanceStylingOptions = {
                'accordion_appearance_accordion': '> ul.ui.module-accordion',
                'appearance_box': '> .module-box-content.ui',
                'appearance_button': '> .ui.builder_button',
                'appearance_callout': '',
                'appearance_image': '',
                'according_style_menu': 'ul.ui.nav:first',
                'mod_appearance_pricing_table': ['', '> .module-pricing-table-header', '.module-pricing-table-button'],
                'tab_appearance_tab': ''
            };

            var appearanceStylingSelector = Object.keys(appearanceStylingOptions).reduce(
                function(selectors, selector, index) {
                    var result = selectors;

                    if (index !== 0) {
                        result += ',';
                    }

                    result += '#' + selector + ' > input';

                    return result;
                },
            '');

            self.$context.on('change', appearanceStylingSelector, function() {
                var $this = $(this);
                var appearanceSelectorKey = $this.parent().attr('id');

                var elmtToApplyToSelector = appearanceStylingOptions[appearanceSelectorKey];

                if (!Array.isArray(elmtToApplyToSelector)) {
                    elmtToApplyToSelector = [elmtToApplyToSelector];
                }

                var $elmtsToApplyTo = $([]);

                elmtToApplyToSelector.forEach(function(selector) {
                    if (selector === '') {
                        $elmtsToApplyTo = $elmtsToApplyTo.add(self.$liveStyledElmt);
                    } else {
                        $elmtsToApplyTo = $elmtsToApplyTo.add(
                            self.$liveStyledElmt.find(selector)
                        );
                    }

                });

                var prevAppearances = self.getStylingVal(appearanceSelectorKey)
                    .split('|')
                    .join(' ');

                var selectedAppearances = getSelectedAppearances(appearanceSelectorKey);

                if (appearanceSelectorKey === 'mod_appearance_pricing_table') {
                    prevAppearances += ' ' + prevAppearances.split(' ').join('|');
                    selectedAppearances += ' ' + selectedAppearances.split(' ').join('|');
                }

                $elmtsToApplyTo
                    .removeClass(prevAppearances)
                    .addClass(selectedAppearances);

                self.setStylingVal(
                    appearanceSelectorKey,
                    getSelectedAppearances(appearanceSelectorKey).split(' ').join('|')
                );
            });
        };

        ThemifyLiveStyling.prototype.bindLightboxForm = function () {
            // "Styling" tab live styling
            this.bindInputsWithUnit();
            this.bindRadioBoxes();
            this.bindColors();
            this.bindColorInputBoxes();
            this.bindSelectboxes();
            this.bindTextInputs();
            this.bindRowWidthHeight();
            this.bindBackgroundMode();
            this.bindAnimation();
            this.bindAdditionalCSSClass();
            this.bindRowAnchor();
            this.bindBackgroundImage();
            this.bindFontFamily();
            this.bindBackgroundVideo();
            this.bindBackgroundSlider();
            this.bindBackgroundTypeRadio();

            // "Module options tab" live styling
            this.bindModuleLayout();
            this.bindModuleColor();
            this.bindModuleApppearance();
            this.bindModuleRadio();
        };

        /**
         * Resets or removes all styling (both live and from server).
         */
        ThemifyLiveStyling.prototype.resetStyling = function () {

            var selectorsWithTriggerRequired = [
                '#background_repeat',
                '#row_anchor',
                '#custom_css_row',
                '#custom_css_column',
                '#add_css_text',
                '#animation_effect',
                'input[name=row_height]',
                'input[name=row_width]'
            ];

            $(selectorsWithTriggerRequired.join(','), this.$context).trigger('change');

            var $styleTag = ThemifyLiveStyling.getComponentStyleTag(this.$liveStyledElmt);
            $styleTag.remove();

            // Removes row overlay.
            this.addOrRemoveComponentOverlay('');

            this._removeAllLiveStyles();

            // TODO: removing bg slider needs more testing.
        };

        /**
         * Returns component's background cover element wrapped in jQuery.
         *
         * @param {jQuery} $component
         * @returns {jQuery}
         */
        ThemifyLiveStyling.getComponentBgOverlay = function ($component) {
            return $component.children('.builder_row_cover');
        };

        /**
         * Returns component's background slider element wrapped in jQuery.
         *
         * @param {jQuery} $component
         * @returns {jQuery}
         */
        ThemifyLiveStyling.getComponentBgSlider = function ($component) {
            return $component.children('.row-slider, .col-slider, .sub-col-slider');
        };

        /**
         * Returns component's background video element wrapped in jQuery.
         *
         * @param {jQuery} $component
         * @returns {jQuery}
         */
        ThemifyLiveStyling.getComponentBgVideo = function ($component) {
            return $component.children('.big-video-wrap');
        };

        /**
         * Returns component's <style> tag.
         *
         * @param {jQuery} $component
         * @returns {jQuery|null}
         */
        ThemifyLiveStyling.getComponentStyleTag = function ($component) {
            var type = ThemifyBuilderCommon.getComponentType($component);

            var $styleTag = null;

            if (type === 'row') {
                $styleTag = $component.find('.row_inner').children('style');
            } else if (type === 'col') {
                $styleTag = $component.find('.tb-column-inner').children('style');
            } else if (type === 'sub-col') {
                $styleTag = $component.children('style');
            } else if (type === 'module-inner') {
                $styleTag = $component.siblings('style');
            }

            return $styleTag;
        };

        /**
         * Removes background slider if there is any in $component.
         *
         * @param {jQuery} $component
         */
        ThemifyLiveStyling.removeBgSlider = function ($component) {
            ThemifyLiveStyling.getComponentBgSlider($component)
                    .add($component.children('.backstretch'))
                    .remove();

            $component.css({
                'position': '',
                'background': '',
                'z-index': ''
            });
        };

        /**
         * Removes background video if there is any in $component.
         *
         * @param {jQuery} $component
         */
        ThemifyLiveStyling.removeBgVideo = function ($component) {
            ThemifyLiveStyling.getComponentBgVideo($component).remove();
        };

        /**
         * Removes live styling from the live styled element.
         *
         * @private
         */
        ThemifyLiveStyling.prototype._removeAllLiveStyles = function () {
            var self = this;

            var selectors = this.getSpecialTextSelectors().concat('a');

            selectors.forEach(function (selector) {
                // Remove styles for special selectors.
                jss.remove(self.elmtSelector + ' ' + selector);
            });

            // Remove styles for the selector.
            jss.remove(this.elmtSelector);
        };

        ThemifyLiveStyling.prototype.remove = function () {
            if (!this.isInit) {
                return;
            }

            this._removeAllLiveStyles();

            this.unsetLiveStyledElmtID();

            this.$liveStyledElmt = null;
            this.currentStyleObj = null;
            this.isInit = false;
        };

        return ThemifyLiveStyling;
    })(jQuery, jss);

    // Initialize Builder
    $('body').on('builderscriptsloaded.themify', function (e) {
        ThemifyPageBuilder.init();
        ThemifyPageBuilder.toggleFrontEdit(e);
        $('.toggle_tf_builder a:first').on('click', ThemifyPageBuilder.toggleFrontEdit);


    });

}(jQuery, _, window, document));
