(function ($) {
    'use strict';

    $(document).ready(function () {

 

        $('select[name="doko[enable-screen-redirect]"]').on('change', function () {
            let changEl = $(this).val();
            $('tr.doko_first_screen_no_products').hide();
            if (changEl == "no") {
                $('tr.doko_first_screen_no_products').show();
            }
        });

        
        $('select[name="doko[enable-screen-redirect]"]').trigger('change');



        hs_dk_toggle_qty_field();


        if ($('#doko-box-description').length > 0) {

            window.dk_get_products($('select[name="doko[box-products][]"]'), 'hs_dk_query_wc', doko_object.pick_product_message, true);
            window.dk_get_products($('select[name="doko[box-categories][]"]'), 'hs_dk_query_wc', doko_object.pick_category_message, true);

            wp.attachEditor(document.getElementById('doko-box-description'));

             window.dk_change_option_disposition('input[name="doko[box-selection-mode]"]', 'tr.doko-box-products-mode', 'tr.doko-box-categories-mode', 'tr.doko-box-tags-mode');



            $('button.hs-add-screen').on('click dbclick', function (e) {
                e.preventDefault();
                $.blockUI({ 'message': 'Adding your bundle screen content, please wait ....' })
                $.post(ajaxurl, { 'action': 'doko_get_admin_screen' },
                    function (r) {
                        r = JSON.parse(r);
                        var idField = r.args.elem_id;
                        $('div.woocommerce_variations').append(r.html);
                        hs_dk_toggle_bundle_viewer(idField);
                        dk_init_sortable_event();
                        dk_build_sortable_bundle(idField, r.args);
                        hs_dk_toggle_pagination_per_page(idField)
                        hs_dk_toggle_gift_messages(idField)
                        hs_dk_toggle_btn_qty(idField)

                        $.unblockUI();
                    });
            });

            change_product_type_tip('hello')

            for (var id in doko_object.ids) {
                hs_dk_toggle_bundle_viewer(doko_object.ids[id].id)
                dk_build_sortable_bundle(doko_object.ids[id].id, doko_object.ids[id].args);
                hs_dk_toggle_pagination_per_page(doko_object.ids[id].id)
                hs_dk_toggle_gift_messages(doko_object.ids[id].id)
                hs_dk_toggle_btn_qty(doko_object.ids[id].id)

            }
            dk_init_sortable_event();
        }

        function hs_dk_toggle_qty_field() {
            $('select[name="doko[enable-qty-input]"]').on('change', function (e) {
                e.preventDefault();
                let option_selected = $(this).val();
                if (option_selected == "yes") {
                    $('tr.doko-qty-position-field').show();
                } else {
                    $('tr.doko-qty-position-field').hide();
                }

            });

            $('select[name="doko[enable-qty-input]"]').trigger('change');
        }



        function hs_dk_toggle_pagination_per_page(idField) {
            $('select[name="doko[' + idField + '][enable-pagination]"]').on("change", function (e) {
                e.preventDefault();
                let options = $('tr.doko-tr-nb-products-per-page.doko-' + idField);
                if ($(this).val() == "yes") {
                    options.show();
                } else {
                    options.hide();
                }
            })
            $('select[name="doko[' + idField + '][enable-pagination]"]').trigger('change')
        }


        function hs_dk_toggle_gift_messages(idField) {
            $('select[name="doko[' + idField + '][enable-gift-message]"]').on("change", function (e) {
                e.preventDefault();
                let options = $('tr.doko-tr-title-gift-message.doko-' + idField);
                if ($(this).val() == "yes") {
                    options.show();
                } else {
                    options.hide();
                }
                options = $('tr.doko-tr-desc-gift-message.doko-' + idField);
                if ($(this).val() == "yes") {
                    options.show();
                } else {
                    options.hide();
                }
            })
            $('select[name="doko[' + idField + '][enable-gift-message]"]').trigger('change')
        }


        function hs_dk_toggle_btn_qty(idField) {
            $('select[name="doko[' + idField + '][enable-qty-input]"]').on("change", function (e) {
                e.preventDefault();
                let options = $('tr.doko-idproduct-qty-field-option.doko-' + idField);
                if ($(this).val() == "yes") {
                    options.show();
                } else {
                    options.hide();
                }
            })
            $('select[name="doko[' + idField + '][enable-qty-input]"]').trigger('change')
        }

        function hs_dk_toggle_bundle_viewer(idField) {

            $(document).on('change', '[name="doko[' + idField + '][display-bundle-viewer]"]', function () {
                var show_viewer = $(this).val()
                if (show_viewer == "no") {
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.choose-bundle-screen-disposition').hide();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.display-bundle-title').hide();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-tr-section-bundle-title-' + idField).hide();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-box-selection-mode').hide();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-multiple-tags.doko-tr-section-tag-' + idField).hide();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-tr-section-prod-' + idField).hide();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-tr-section-cat-' + idField).hide();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-enable-product-description').hide();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-enable-qty-input-label').hide();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-tr-enable-pagination').hide();




                } else {
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.choose-bundle-screen-disposition').show();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.display-bundle-title').show();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-enable-product-description').show();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-box-selection-mode').show();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-tr-section-prod-' + idField).show();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-enable-qty-input-label').show();
                    $('table[name="formulus-input-doko-box-selection-' + idField + '"] tr.doko-tr-enable-pagination').show();

                }
            });
            $('[name="doko[' + idField + '][display-bundle-viewer]"]').trigger('change');


        }




        function change_product_type_tip(content) {
            $('#tiptip_holder').removeAttr('style');
            $('#tiptip_arrow').removeAttr('style');
            $('.woocommerce-product-type-tip').tipTip({
                attribute: 'data-tip',
                content: content,
                fadeIn: 50,
                fadeOut: 50,
                delay: 200,
                keepAlive: true,
            });

            $('.woocommerce-help-tip').tipTip({
                attribute: 'data-tip',
                content: content,
                fadeIn: 50,
                fadeOut: 50,
                delay: 200,
                keepAlive: true,
            });
        }






    });





})(jQuery);
