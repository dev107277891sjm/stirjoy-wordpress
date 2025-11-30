jQuery('.min-order-rule-select').select2({
  
    minimumResultsForSearch: -1,
    showArrow:true,
    dropdownCssClass: "elex-min-order-wrap",
});





jQuery(document).ready(function($) {
    function loadProducts($row, selectedCategories, selectedProducts) {
        jQuery.ajax({
          url: myAjax.ajaxurl, // WordPress AJAX URL
          type: 'POST',
          data: {
            action: 'fetch_products_by_category',
            categories: selectedCategories,
            nonce: myAjax.nonce
          },
          success: function(response) {
            var productSelect = $row.find('.elex-min-order-rules-table-product .min-order-rule-select');
            productSelect.empty(); // Clear current options
            jQuery("#min-order-table-loader").hide();
            if (response.success) {
              selectedProducts = Array.isArray(selectedProducts) ? selectedProducts : [];
              var products = response.data.products;
              if (products.length > 0) {
                products.forEach(function(product) {
                    var selected = selectedProducts.includes(product.id.toString()) ? 'selected' : '';
                    productSelect.append('<option value="' + product.id + '" ' + selected + '>' + product.name + '</option>');
                    // productSelect.append('<option value="' + product.id + '">' + product.name + '</option>');
                  
                });
              } else {
                productSelect.append('<option value="">No products found</option>');
              }
            } else {
              productSelect.append('<option value="">Error fetching products</option>');
            }
          }
        });
    }

    // Initialize products on page load for each row
    jQuery('.elex-min-order-rules-table tr').each(function() {
        jQuery("#min-order-table-loader").show();
        var $row = $(this);
        var selectedCategories = $row.find('.elex-min-order-rules-table-categories .min-order-rule-select').val(); // Get selected categories
        var selectedProducts = $row.find('.elex-min-order-rules-table-product .min-order-rule-select').val();
        loadProducts($row, selectedCategories, selectedProducts); // Load products for this row
    });

    // Handle category change event for each row
    jQuery('.elex-min-order-rules-table').on('change', '.elex-min-order-rules-table-categories .min-order-rule-select', function() {
        jQuery("#min-order-table-loader").show();
        var $row = jQuery(this).closest('tr');
        var selectedCategories = jQuery(this).val(); // Get selected categories
        var selectedProducts = $row.find('.elex-min-order-rules-table-product .min-order-rule-select').val();
        loadProducts($row, selectedCategories, selectedProducts); // Load products for this row
    });

    

    // user roles and user js
    function loadUsers($row, selectedRoles, selectedUsers) {
      jQuery.ajax({
        url: myAjax.ajaxurl, // WordPress AJAX URL
        type: 'POST',
        data: {
          action: 'fetch_users_by_role',
          roles: selectedRoles,
          nonce: myAjax.nonce
        },
        success: function(response) {
          var $userSelect = $row.find('.elex-min-order-rules-table-user .min-order-rule-select');
          $userSelect.empty(); // Clear current options
          jQuery("#min-order-table-loader").hide();
          if (response.success) {
            var users = response.data.users;
            var addedValues = new Set();
            if (users.length > 0) {
              selectedUsers = Array.isArray(selectedUsers) ? selectedUsers : [];
              users.forEach(function(user) {
                if (!addedValues.has(user.id.toString())) {
                  var selected = selectedUsers.includes(user.id.toString()) ? 'selected' : '';
                  $userSelect.append('<option value="' + user.id + '" ' + selected + '>' + user.name + ' (' + user.email + ')</option>');
                  addedValues.add(user.id.toString());
                }
              });
            } else {
              $userSelect.append('<option value="">No users found</option>');
            }
          } else {
            $userSelect.append('<option value="">Error fetching users</option>');
          }
        }
      });
    }

     // Initialize users on page load for each row
    jQuery('.elex-min-order-rules-table tr').each(function() {
      jQuery("#min-order-table-loader").show();
      var $row = jQuery(this);
      var selectedRoles = $row.find('.elex-min-order-rules-table-user-role .min-order-rule-select').val() || []; // Get selected roles
      var selectedUsers = $row.find('.elex-min-order-rules-table-user .min-order-rule-select').val(); // Get selected users

      loadUsers($row, selectedRoles, selectedUsers); // Load users for this row
    });

    // Handle role change event for dynamically added rows using event delegation
    jQuery('.elex-min-order-rules-table').on('change', '.elex-min-order-rules-table-user-role .min-order-rule-select', function() {
      jQuery("#min-order-table-loader").show();
      var $row = jQuery(this).closest('tr');
      var selectedRoles = jQuery(this).val() || []; // Get selected roles
      var selectedUsers = $row.find('.elex-min-order-rules-table-user .min-order-rule-select').val(); // Get selected users

      loadUsers($row, selectedRoles, selectedUsers); // Load users for this row
    });
  });

jQuery(document).ready(function($){
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, { container: ".elex-min-order-wrap" });
    });
    // var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    // var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    //     return new bootstrap.Tooltip(tooltipTriggerEl)
    //   })
    jQuery(".min_order_create_rule_btn").click(function(){
        jQuery(".min_order_rule_create_div").hide();
        jQuery(".min_order_rule_table_container").show();
        jQuery("#elex_min_order_add_rule").click();
    });


    // Function to disable nearby input and select fields
    function disableNearbyFields(checkbox) {
        var tr = checkbox.closest('tr');
        if (checkbox.prop('checked')) {
            tr.removeClass('elex-min-order-disabled-row');
        } else {
            tr.addClass('elex-min-order-disabled-row');
        }
        tr.find('input:not(:checkbox)').prop('readonly', !checkbox.prop('checked'));
        tr.find('select').prop('disabled', !checkbox.prop('checked'));
    }

    // Event handler for checkbox change
    jQuery('.elex-min-order-rules-table-enable input[type="checkbox"]').change(function(){
        disableNearbyFields(jQuery(this));
    });

    // Initially disable nearby fields based on checkbox state
    jQuery('.elex-min-order-rules-table-enable input[type="checkbox"]').each(function(){
        disableNearbyFields(jQuery(this));
    });
})