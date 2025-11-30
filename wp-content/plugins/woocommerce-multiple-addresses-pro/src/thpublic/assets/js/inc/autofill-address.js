var thwma_auto_suggest = (function ($, window, document) {

	var store_country = thwma_public_var.store_country;
	var specific_country = thwma_public_var.specific_country;

	function init_autocomplete() {
		var form_fields_value = {
			'billing_address_1': '',
			'billing_address_2': '',
			'billing_city': '',
			'billing_state': '',
			'billing_postcode': '',
			'billing_country': ''
		};
		var component_form = {
			'street_number': ['billing_address_1', 'short_name'],
			'route': ['billing_address_2', 'long_name'],
			'locality': ['billing_city', 'long_name'],
			'postal_town': ['billing_city', 'long_name'],
			'sublocality_level_1': ['billing_city', 'long_name'],
			'administrative_area_level_1': ['billing_state', 'short_name'],
			'administrative_area_level_2': ['billing_state', 'short_name'],
			'administrative_area_level_3': ['billing_state', 'short_name'],
			'country': ['billing_country', 'short_name'],
			'postal_code': ['billing_postcode', 'short_name']
		}
		var country_field = '';
		autocomplete = new google.maps.places.Autocomplete(
			(document.getElementById('billing_address_1')), {

			types: ['geocode'],

		});

		autocomplete.addListener('place_changed', fillInAddress);
		var billing_country = document.getElementById("billing_country");

		if (billing_country != null) {
			billing_country.addEventListener("change", function (event) {
				set_autocomplete_country()
			}, true);
		}

		var billing_address = document.getElementById("billing_address_1");
		if (billing_address != null) {
			billing_address.addEventListener("focus", function (event) {
				set_autocomplete_country()
			}, true);
		}

		function fillInAddress() {
			clear_form_values();
			var place = autocomplete.getPlace();

			for (var field in place.address_components) {
				for (var type in place.address_components[field].types) {
					for (var adrs_fld in component_form) {

						if (adrs_fld == place.address_components[field].types[type]) {
							var prop = component_form[adrs_fld][1];
							if (place.address_components[field].hasOwnProperty(prop)) {

								if (adrs_fld == 'administrative_area_level_1' || adrs_fld == 'administrative_area_level_2' || adrs_fld == 'administrative_area_level_3') {
									if (thwma_public_var.enable_console_log == true) {
										console.log(place.address_components[field][prop]);
									}

									// filter feature.
									if (thwma_public_var.state_code_convert != null) {
										var state_code_convert_arr = thwma_public_var.state_code_convert;
										var state_code_obj = jQuery.parseJSON(state_code_convert_arr);
										$.each(state_code_obj, function (key, value) {
											if (place.address_components[field][prop] == key) {
												place.address_components[field][prop] = value;
											}
										});
									}

									$('#' + component_form[adrs_fld][0]).val([place.address_components[field][prop]]).change();
								} else {
									$('#' + component_form[adrs_fld][0]).val([place.address_components[field][prop]]).change();
								}

								//console.log(component_form[adrs_fld][0] + " = " + place.address_components[field][prop]);
							}
						}
					}
				}
			}

			fill_street_number(place), fill_state_field(place);
			$(document.body).trigger("thmap_after_billing_autofill", [place, component_form]);
		}

		function clear_form_values() {
			for (var field in form_fields_value) {
				$('#' + field).val('').change();
			}
		}

		function set_autocomplete_country() {
			var country = '';
			country = document.getElementById('billing_country').value;

			if (country == '' && specific_country == '') {
				country = store_country;
			} else if (specific_country && country == '') {
				country = specific_country;
			}
			autocomplete.setComponentRestrictions({
				'country': country
			});
		}

		function fill_street_number($place) {
			var street_number = $place.name;
			"" != street_number && $("#billing_address_1").val([street_number]).change();
		}

		// function fill_state_field(place) {
		// 	for (var field in place.address_components) {
		// 		for (var type in place.address_components[field].types) {
		// 			"administrative_area_level_2" == place.address_components[field].types[type] && $("#billing_state").val([place.address_components[field].short_name]).change();
		// 		}
		// 	}
		// 	if ("" == document.getElementById("billing_state").value) {
		// 		for (var field in place.address_components) {
		// 			for (var type in place.address_components[field].types) {
		// 				"administrative_area_level_1" == place.address_components[field].types[type] && $("#billing_state").val([place.address_components[field].short_name]).change();
		// 			}
		// 		}
		// 	}
		// }

		function fill_state_field(place) {
		    // Helper function to check if a value exists in the select options
			function isValueInSelect(selectElement, value) {
			    return $(selectElement).find('option[value="' + value + '"]').length > 0;
			}

		    // Check for administrative_area_level_2
		    for (var field in place.address_components) {
		        for (var type in place.address_components[field].types) {
		            if ("administrative_area_level_2" === place.address_components[field].types[type]) {
		                const newValue = place.address_components[field].short_name;

		                // Update only if the value exists in the select field
		                if (isValueInSelect("#billing_state", newValue)) {
		                    $("#billing_state").val(newValue).change();
		                }
		            }
		        }
		    }

		    // If billing_state is still empty, check for administrative_area_level_1
		    if (!$("#billing_state").val()) {
		        for (var field in place.address_components) {
		            for (var type in place.address_components[field].types) {
		                if ("administrative_area_level_1" === place.address_components[field].types[type]) {
		                    const newValue = place.address_components[field].short_name;

		                    // Update only if the value exists in the select field
		                    if (isValueInSelect("#billing_state", newValue)) {
		                        $("#billing_state").val(newValue).change();
		                    }
		                }
		            }
		        }
		    }
		}
	}

	function init_shipping_autocomplete() {

		var shipping_form_fields = {
			'shipping_address_1': '',
			'shipping_address_2': '',
			'shipping_city': '',
			'shipping_state': '',
			'shipping_postcode': '',
			'shipping_country': ''
		};

		var shipping_component_form = {
			'street_number': ['shipping_address_1', 'short_name'],
			'route': ['shipping_address_2', 'long_name'],
			'locality': ['shipping_city', 'long_name'],
			'postal_town': ['shipping_city', 'long_name'],
			'sublocality_level_1': ['shipping_city', 'long_name'],
			'administrative_area_level_1': ['shipping_state', 'short_name'],
			'administrative_area_level_2': ['shipping_state', 'short_name'],
			'administrative_area_level_3': ['shipping_state', 'short_name'],
			'country': ['shipping_country', 'short_name'],
			'postal_code': ['shipping_postcode', 'short_name']
		}

		var country_field = '';

		shipping_autocomplete = new google.maps.places.Autocomplete(
			(document.getElementById('shipping_address_1')), {

			types: ['geocode'],

		});


		var place = shipping_autocomplete.getPlace();

		shipping_autocomplete.addListener('place_changed', fill_shipping_Address);

		var shipping_country = document.getElementById("shipping_country");

		if (shipping_country != null) {
			shipping_country.addEventListener("change", function (event) {
				set_autocomplete_shipping_country()
			}, true);
		}

		var shipping_address = document.getElementById("shipping_address_1");
		if (shipping_address != null) {
			shipping_address.addEventListener("focus", function (event) {
				set_autocomplete_shipping_country()
			}, true);
		}

		function fill_shipping_Address() {

			clear_shipping_form_values();
			var place = shipping_autocomplete.getPlace();

			for (var field in place.address_components) {
				for (var type in place.address_components[field].types) {
					for (var adrs_fld in shipping_component_form) {

						// if(adrs_fld == place.address_components[field].types[type]){
						// 	var prop = shipping_component_form[adrs_fld][1];
						// 	if(place.address_components[field].hasOwnProperty(prop)){
						// 		//form_fields_value[component_form[adrs_fld][0]] = place.address_components[field][prop];
						// 		$('#'+shipping_component_form[adrs_fld][0]).val([place.address_components[field][prop]]).change();

						// 		//console.log(component_form[adrs_fld][0] + " = " + place.address_components[field][prop]);
						// 	}
						// }
						if (adrs_fld == place.address_components[field].types[type]) {
							var prop = shipping_component_form[adrs_fld][1];
							if (place.address_components[field].hasOwnProperty(prop)) {

								if (adrs_fld == 'administrative_area_level_1' || adrs_fld == 'administrative_area_level_2' || adrs_fld == 'administrative_area_level_3') {
									if (thwma_public_var.enable_console_log == true) {
										console.log(place.address_components[field][prop]);
									}

									// filter feature.
									if (thwma_public_var.state_code_convert != null) {
										var state_code_convert_arr = thwma_public_var.state_code_convert;
										var state_code_obj = jQuery.parseJSON(state_code_convert_arr);
										$.each(state_code_obj, function (key, value) {
											if (place.address_components[field][prop] == key) {
												place.address_components[field][prop] = value;
											}
										});
									}

									$('#' + shipping_component_form[adrs_fld][0]).val([place.address_components[field][prop]]).change();
								} else {
									$('#' + shipping_component_form[adrs_fld][0]).val([place.address_components[field][prop]]).change();
								}

								//console.log(component_form[adrs_fld][0] + " = " + place.address_components[field][prop]);
							}
						}
					}
				}
			}

			fill_shipping_street_number(place);
			fill_shipping_state_field(place);
			$(document.body).trigger("thmap_after_shipping_autofill", [place, shipping_component_form]);
		}

		function clear_shipping_form_values() {
			for (var field in shipping_form_fields) {
				$('#' + field).val('').change();
			}
		}

		function set_autocomplete_shipping_country() {

			var country = '';
			country = document.getElementById('shipping_country').value;

			if (country == '' && specific_country == '') {
				country = store_country;
			} else if (specific_country && country == '') {
				country = specific_country;
			}

			shipping_autocomplete.setComponentRestrictions({
				'country': country
			});
		}

		function fill_shipping_street_number($place) {

			var street_number = $place.name;
			if (street_number != '') {
				{

					$('#shipping_address_1').val([street_number]).change();
				}
			}
		}

		// function fill_shipping_state_field(place) {
		// 	for (var field in place.address_components)
		// 		for (var type in place.address_components[field].types)
		// 			"administrative_area_level_2" == place.address_components[field].types[type] && $("#shipping_state").val([place.address_components[field].short_name]).change();
		// 	if ("" == document.getElementById("shipping_state").value)
		// 		for (var field in place.address_components)
		// 			for (var type in place.address_components[field].types)
		// 				"administrative_area_level_1" == place.address_components[field].types[type] && $("#shipping_state").val([place.address_components[field].short_name]).change();
		// }

		function fill_shipping_state_field(place) {
		    // Helper function to check if a value exists in the select options
			function isValueInSelect(selectElement, value) {
			    return $(selectElement).find('option[value="' + value + '"]').length > 0;
			}

		    // Check for administrative_area_level_2
		    for (var field in place.address_components) {
		        for (var type in place.address_components[field].types) {
		            if ("administrative_area_level_2" === place.address_components[field].types[type]) {
		                const newValue = place.address_components[field].short_name;

		                // Update only if the value exists in the select field
		                if (isValueInSelect("#shipping_state", newValue)) {
		                    $("#shipping_state").val(newValue).change();
		                }
		            }
		        }
		    }

		    // If shipping_state is still empty, check for administrative_area_level_1
		    if (!$("#shipping_state").val()) {
		        for (var field in place.address_components) {
		            for (var type in place.address_components[field].types) {
		                if ("administrative_area_level_1" === place.address_components[field].types[type]) {
		                    const newValue = place.address_components[field].short_name;

		                    // Update only if the value exists in the select field
		                    if (isValueInSelect("#shipping_state", newValue)) {
		                        $("#shipping_state").val(newValue).change();
		                    }
		                }
		            }
		        }
		    }
		}
	}

	// Cart shipping enable auto fill functionality.
	function init_cart_shipping_autocomplete() {
		var cart_shipping_form_fields = {
			'shipping_address_1': '',
			'shipping_address_2': '',
			'shipping_city': '',
			'shipping_state': '',
			'shipping_postcode': '',
			'shipping_country': ''
		};

		var cart_shipping_component_form = {
			'street_number': ['shipping_address_1', 'short_name'],
			'route': ['shipping_address_2', 'long_name'],
			'locality': ['shipping_city', 'long_name'],
			'postal_town': ['shipping_city', 'long_name'],
			'sublocality_level_1': ['shipping_city', 'long_name'],
			'administrative_area_level_1': ['shipping_state', 'short_name'],
			'administrative_area_level_2': ['shipping_state', 'short_name'],
			'administrative_area_level_3': ['shipping_state', 'short_name'],
			'country': ['shipping_country', 'short_name'],
			'postal_code': ['shipping_postcode', 'short_name']
		}

		var country_field = '';
		cart_shipping_autocomplete = new google.maps.places.Autocomplete(
			(document.getElementById('shipping_address_1')), {
			types: ['geocode']

		});

		//var place = cart_shipping_autocomplete.getPlace();
		cart_shipping_autocomplete.addListener('place_changed', fill_cart_shipping_Address);
		var cart_shipping_country = document.getElementById("shipping_country");

		if (cart_shipping_country != null) {
			cart_shipping_country.addEventListener("change", function (event) {
				set_autocomplete_cart_shipping_country()
			}, true);
		}

		var cart_shipping_address = document.getElementById("shipping_address_1");
		if (cart_shipping_address != null) {
			cart_shipping_address.addEventListener("focus", function (event) {
				set_autocomplete_cart_shipping_country()
			}, true);
		}

		function fill_cart_shipping_Address() {
			clear_cart_shipping_form_values();
			var place = cart_shipping_autocomplete.getPlace();

			for (var field in place.address_components) {
				for (var type in place.address_components[field].types) {
					for (var adrs_fld in cart_shipping_component_form) {

						// if(adrs_fld == place.address_components[field].types[type]){
						// 	var prop = cart_shipping_component_form[adrs_fld][1];
						// 	if(place.address_components[field].hasOwnProperty(prop)){
						// 		//form_fields_value[component_form[adrs_fld][0]] = place.address_components[field][prop];
						// 		$('#'+cart_shipping_component_form[adrs_fld][0]).val([place.address_components[field][prop]]).change();

						// 		//console.log(component_form[adrs_fld][0] + " = " + place.address_components[field][prop]);
						// 	}
						// }
						if (adrs_fld == place.address_components[field].types[type]) {
							var prop = cart_shipping_component_form[adrs_fld][1];
							if (place.address_components[field].hasOwnProperty(prop)) {

								if (adrs_fld == 'administrative_area_level_1' || adrs_fld == 'administrative_area_level_2' || adrs_fld == 'administrative_area_level_3') {
									if (thwma_public_var.enable_console_log == true) {
										console.log(place.address_components[field][prop]);
									}

									// filter feature.
									if (thwma_public_var.state_code_convert != null) {
										var state_code_convert_arr = thwma_public_var.state_code_convert;
										var state_code_obj = jQuery.parseJSON(state_code_convert_arr);
										$.each(state_code_obj, function (key, value) {
											if (place.address_components[field][prop] == key) {
												place.address_components[field][prop] = value;
											}
										});
									}

									$('#' + cart_shipping_component_form[adrs_fld][0]).val([place.address_components[field][prop]]).change();
								} else {
									$('#' + cart_shipping_component_form[adrs_fld][0]).val([place.address_components[field][prop]]).change();
								}

								//console.log(component_form[adrs_fld][0] + " = " + place.address_components[field][prop]);
							}
						}
					}
				}
			}

			fill_cart_shipping_street_number(place);
			fill_cart_shipping_state_field(place);
			$(document.body).trigger("thmap_after_cart_shipping_autofill", [place, cart_shipping_component_form]);

		}

		function clear_cart_shipping_form_values() {
			for (var field in cart_shipping_form_fields) {
				$('#' + field).val('').change();
			}
		}

		function set_autocomplete_cart_shipping_country() {
			var country = '';
			country = document.getElementById('shipping_country').value;

			if (country == '' && specific_country == '') {
				country = store_country;
			} else if (specific_country && country == '') {
				country = specific_country;
			}
			cart_shipping_autocomplete.setComponentRestrictions({
				'country': country
			});
		}

		function fill_cart_shipping_street_number($place) {
			var street_number = $place.name;
			if (street_number != '') {
				{

					$('#shipping_address_1').val([street_number]).change();
				}
			}
		}

		function fill_cart_shipping_state_field(place) {
			var cart_shipping_state = document.getElementById("shipping_state").value;
			if (cart_shipping_state == '') {
				for (var field in place.address_components) {
					for (var type in place.address_components[field].types) {
						if (place.address_components[field].types[type] == 'administrative_area_level_2') {
							$('#shipping_state').val([place.address_components[field]['short_name']]).change();
						}
					}
				}

			}
		}
	}

	if (thwma_public_var.enable_autofill == 'yes') {
		if (!(document.getElementById('billing_address_1') === null))
			init_autocomplete();
		if (!(document.getElementById('shipping_address_1') === null)) {
			init_shipping_autocomplete();
		}

		if (document.getElementById("billing_address_1") != null) {
			var adrs_billing_field = document.getElementById("billing_address_1");

			google.maps.event.addDomListener(adrs_billing_field, 'keydown', function (e) {
				if (e.keyCode == 13) {
					e.preventDefault();
				}
			});
		}

		if (document.getElementById("shipping_address_1") != null) {
			var adrs_shipping_field = document.getElementById("shipping_address_1");
			google.maps.event.addDomListener(adrs_shipping_field, 'keydown', function (e) {
				if (e.keyCode == 13) {
					e.preventDefault();
				}
			});
		}
	}
	if (thwma_public_var.enable_autofill == 'yes') {
		return {
			init_cart_shipping_autocomplete: init_cart_shipping_autocomplete,
		}
	}

}(window.jQuery, window, document));