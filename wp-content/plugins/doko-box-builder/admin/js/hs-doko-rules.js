(function ($) {
  "use strict";

  function hs_dk_get_dynamic_rule(option_name, hash_code, field_id) {
    var woo_usn_field_options = $(
      "p.doko-dynamic-rules[data-screen-id='" +
        hash_code +
        "'] select.hs-dk-options[name='doko[rules][" +
        hash_code +
        "][dynamic][" +
        field_id +
        "][cl-options]']"
    );
    var woo_usn_field_values = $(
      "p.doko-dynamic-rules[data-screen-id='" +
        hash_code +
        "'] span#" +
        field_id
    );
    switch (option_name) {
      case "total_products_count_on_screen":
      case "products_count_on_screen":
        woo_usn_field_options.empty().append(hs_generate_html_operators());
        woo_usn_field_values
          .empty()
          .attr("name", "filter-by-amount")
          .append(
            '<input type="number" name="doko[rules][' +
              hash_code +
              "][dynamic][" +
              field_id +
              '][cl-values]"  required/>' +
              "&nbsp;&nbsp;&nbsp;<button class='button button-primary doko-rule-remove-block' >Remove</button>"
          );
        break;

      default:
        break;
    }
  }

  var hs_generate_html_operators = function (
    values = window.doko.product_operators
  ) {
    var html;
    for (var option_name in values) {
      html +=
        "<option value='" +
        option_name +
        "'>" +
        values[option_name] +
        "</option>";
    }
    return html;
  };

  function hs_dk_reset_rule_selector(box_id, hash_code) {
    $(
      "p.doko-dynamic-rules[data-screen-id=" +
        box_id +
        '] select[name="doko[rules][' +
        box_id +
        "][dynamic][" +
        hash_code +
        '][cl-rules]"]'
    ).on("click", function () {
      hs_dk_get_dynamic_rule(
        $(this).val(),
        box_id,
        $(this).parent().closest("p.hs-dk-content").data("hash")
      );
    });

    $(
      "p.doko-dynamic-rules[data-screen-id=" +
        box_id +
        '] select[name="doko[rules][' +
        box_id +
        "][dynamic][" +
        hash_code +
        '][cl-rules]"]'
    ).trigger("click");

    $("button.doko-rule-remove-block").on("click", function () {
      $(this).parent().closest("p.hs-dk-content").remove();
    });
  }

  var hs_dk_toggle_rule_action = function (bundleId, idField) {
    var elem = $('select[name="doko[' + idField + '][action]"]');
    elem.on("change", function () {
      var value = $(this).val();
      if (
        value == "limit-product-in-current-step" ||
        value == "limit-product-in-next-step"
      ) {
        $("tr.doko-limit-product-number.doko-" + idField).show();
        $("tr.doko-limit-product-amount.doko-" + idField).hide();
        $("tr.doko-go-to-screen.doko-" + idField).hide();
      }

      if (
        value == "limit-amount-in-current-step" ||
        value == "limit-amount-in-next-step"
      ) {
        $("tr.doko-limit-product-number.doko-" + idField).hide();
        $("tr.doko-limit-product-amount.doko-" + idField).show();
        $("tr.doko-go-to-screen.doko-" + idField).hide();
      }

      if (value == "go-to-step") {
        $("tr.doko-limit-product-number.doko-" + idField).hide();
        $("tr.doko-limit-product-amount.doko-" + idField).hide();

        $("tr.doko-go-to-screen.doko-" + idField).show();
      }
    });
    elem.trigger("change");
  };

  function hs_init_rules_selector_picker( bundle_id ) {
    $("select.doko_bundle_screen_to_pick").on("change", function (e) {
      let screen = $(this).val();
      let dataRow = $(this).data('rowIndex');
      $.blockUI({ message: "Loading bundle products, please wait ...." });
      $.post(
        ajaxurl,
        {
          bundle_id: bundle_id,
          screen: screen,
          action: "doko_get_screen_products",
        },
        function (r) {
          var json = JSON.parse(r);
          if (json.html) {
            var e = dataRow;
            var fieldToAssign = $('select[name="doko[rules]['+e+'][rules]"]');
            fieldToAssign.empty().html(json.html);
            fieldToAssign.select2({
                width : '100%'
            });
          }
          $.unblockUI();
        }
      );
    
    });
  }

  $(document).ready(function () {
    var total_rule = 1;

    $('select.doko-rules-assign-product-rules').select2();

    $("button.hs-add-rule").on("click dbclick", function (e) {
      e.preventDefault();
      var bundleId = $("select#doko-associated_rule-bundle").val();

      if (bundleId == "no") {
        alert("Choose a bundle, before choosing a rule.");
        return false;
      }

      $.blockUI({ message: "Preparing your bundle rule, please wait ...." });
      total_rule = parseInt($('tr.doko_tr_element').length )+1 ;
      $.post(
        ajaxurl,
        {
          action: "doko_get_bundle_rule",
          bundle_id: bundleId,
          current_index: total_rule,
        },
        function (r) {
          r = JSON.parse(r);
          $("table.doko_rules_table tbody").append(r.html);
          total_rule++;
          hs_init_rules_selector_picker(bundleId);
          $.unblockUI();

          $('select.doko-rules-assign-product-rules').select2();

           $('button.doko-remove-rule-row').on('click dbclick', function(e) {
              e.preventDefault();
              $(this).closest('tr.doko_tr_element').remove();
          });
        }
      );
    });

    $('button.doko-add-rule-row').on('click dbclick', function(e) {
        e.preventDefault();
        $("button.hs-add-rule").trigger('click');
    });

      $('button.doko-remove-rule-row').on('click dbclick', function(e) {
              e.preventDefault();
              $(this).closest('tr.doko_tr_element').remove();
          });


    $("select#doko-associated_rule-bundle").on('change', function(){
        $('table.doko_rules_table tbody').empty();
        total_rule = 0
    });

     hs_init_rules_selector_picker( $("select#doko-associated_rule-bundle").val() );
  });
})(jQuery);
