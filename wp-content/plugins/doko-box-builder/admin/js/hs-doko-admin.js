(function ($) {
	'use strict';


	$(document).ready(function () {
		function dk_get_products(selector_name, url, placeholder, is_full_width) {
            var packageOptionMode = selector_name.data('packageMode');
            return selector_name.select2(
                {
                    width: is_full_width ? '100%' : null,
                    multiple: true,
                    placeholder: placeholder,
                    ajax: {
                        url: ajaxurl,
                        dataType: 'json',
                        delay: 30,
                        data: function (params) {
                            return {
                                q: params.term,
                                action: url,
                                operation_type: packageOptionMode,
                            };
                        },
                        processResults: function (data) {
                            var options = [];
                            if (data) {
                                data.forEach(
                                    (content) => {
                                        options.push(
                                            {
                                                id: content[0],
                                                text: content[1]
                                            }
                                        );
                                    }
                                );
                            }
                            return {
                                results: options
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1
                }
            );
        }

		window.dk_get_products = dk_get_products;


		var hs_dk_bundle_screen = [
			'#doko-bundle-screens',
			'#doko-rules'
		];

		hs_dk_bundle_screen = wp.hooks.applyFilters('doko_mb_btn_level', hs_dk_bundle_screen);

        for (var screen in hs_dk_bundle_screen) {
            $('.type_box').insertAfter(hs_dk_bundle_screen[screen] + ' .hndle');
        }


		var HSGenerateRandom = function (length) {
			"use strict";
			var result = "";
			var characters =
				"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
			var charactersLength = characters.length;
			for (var i = 0; i < length; i++) {
				result += characters.charAt(Math.floor(Math.random() * charactersLength));
			}
			return result;
		};

		window.HSGenerateRandom = HSGenerateRandom;

		var hs_dk_generate_fields = function (hash_code = 	window.HSGenerateRandom(5), box_id) {
            var html = "<p data-hash='" + hash_code + "' class='hs-dk-content'>";

            html += "<select class='hs-dk-rules' name='doko[rules][" + box_id + "][dynamic][" + hash_code + "][cl-rules]' >";
            for (var option_name in window.doko.rules) {
                html += "<option value='" + option_name + "'>" + window.doko.rules[option_name] + "</option>";
            }
            html += "</select>&nbsp;&nbsp;&nbsp; <select class='hs-dk-options' name='doko[rules][" + box_id + "][dynamic][" + hash_code + "][cl-options]'>";

            for (var option_name in window.doko.product_operators) {
                html += "<option value='" + option_name + "'>" + window.doko.product_operators[option_name] + "</option>";
            }
            html += "</select>&nbsp;&nbsp;&nbsp;"

            html += "<span class='hs-dk-values' id=" + hash_code + "><input type='text' /> <button class='button button-primary woo-usn-cl-remove-block' >Remove</button> </span></p>";
            return html;
        }

		window.hs_dk_generate_fields = hs_dk_generate_fields;


	

		// Meta-Boxes - Open/close
		$(document).on('click', '.wc-metabox h3', function (event) {
			// If the user clicks on some form input inside the h3, like a select list (for variations), the box should not be toggled
			if ($(event.target).filter(':input, option, .sort').length) {
				return;
			}
			$(this).next('.wc-metabox-content').stop().slideToggle();
		})



		$('.wc-metabox.closed').each(function () {
			$(this).find('.wc-metabox-content').hide();
		});

		$(document).on('click', 'a.remove_variation.delete', function () {
			$(this).closest('div.woocommerce_variation').remove();
		});


		$('a.expand_all').on('click', function (e) {
            e.preventDefault();
            $('div.doko-metabox > .doko-metabox-content').show();
            return false;
        })

        $('a.close_all').on('click', function (e) {
            e.preventDefault();
            $('div.doko-metabox > .doko-metabox-content').hide();
            return false;
        });

        $('a.close_all').trigger('click');

		function dk_init_sortable_event() {
            $('div.woocommerce_variations').sortable({
                items: '.woocommerce_variation',
                cursor: 'move',
                axis: 'y',
                handle: '.sort',
                scrollSensitivity: 40,
                forcePlaceholderSize: true,
                helper: 'original',
                opacity: 0.65,
                stop: function () {
                    var wrapper = $('#variable_product_options').find('.woocommerce_variations'),
                        current_page = parseInt(wrapper.attr('data-page'), 10),
                        offset = parseInt((current_page - 1) * 15, 10);

                    $('.woocommerce_variations .woocommerce_variation').each(function (index, el) {
                        $('.variation_menu_order', el)
                            .val(parseInt($(el)
                                .index('.woocommerce_variations .woocommerce_variation'), 10) + 1 + offset)
                            .trigger('change');
                    });
                }
            });
            $('#accordion div.group').map(function (i) { return this.id; }).get()
        }

        window.dk_init_sortable_event = dk_init_sortable_event;


        function dk_change_option_disposition(name, product_name_selector, cat_name_selector, tags_name_selector) {
            $(name).on('change', function (e) {
                var data = $(this).val();
                if ("products" == data) {
                    $(product_name_selector).show();
                    $(cat_name_selector).hide();
                    $(tags_name_selector).hide()
                } else if ("categories" == data) {
                    $(cat_name_selector).show();
                    $(product_name_selector).hide();
                    $(tags_name_selector).hide();
                } else if ("tags" == data) {
                    $(cat_name_selector).hide();
                    $(product_name_selector).hide();
                    $(tags_name_selector).show();
                }
            });
            $(name + ':checked').trigger('change');
        }

        window.dk_change_option_disposition = dk_change_option_disposition;


        function dk_change_title_disposition(name, title_selector) {
            $(name).on('change', function (e) {
                var data = $(this).val();
                if ("yes" == data) {
                    $(title_selector).show();
                } else {
                    $(title_selector).hide();
                }
            });

            $(name + ':checked').trigger('change');
        }


       



        function dk_build_sortable_bundle(idField, args) {

            dk_get_products($('select#doko-box-pdts-select-id-' + idField), 'hs_dk_query_wc', doko_object.pick_product_message, true);
            dk_get_products($('select#doko-ctgs-select-id-' + idField), 'hs_dk_query_wc', doko_object.pick_category_message, true);
            dk_change_option_disposition('input[name="doko[' + idField + '][options]"]', 'tr.doko-tr-section-prod-' + idField, 'tr.doko-tr-section-cat-' + idField, 'tr.doko-multiple-tags.doko-tr-section-tag-' + idField)
            dk_change_title_disposition('input[name="doko[' + idField + '][display-bundle-title]"]', 'tr.doko-tr-section-bundle-title-' + idField);
            $('select[name="doko[' + idField + '][tags][]"]').select2()


            if ($('#' + args.box_description_editor_id).length > 0) {
                wp.attachEditor(document.getElementById(args.box_description_editor_id), {});
                $('input[name="doko[' + idField + '][screen-name]"]').on('keyup', function (e) {
                    $('span.doko-section-' + idField).html($(this).val())
                });
            }

        }


		window.dk_build_sortable_bundle = dk_build_sortable_bundle;





		// wp.domReady( () => {
		// 	wp.blocks.unregisterBlockVariation( 'core/group', 'group-row' );
		// });

		$("tr.doko-multiple-tags select").select2({ "width": "100%" })
		$("tr.doko-box-tags-mode select").select2({ "width": "100%" })

	});
})(jQuery);




