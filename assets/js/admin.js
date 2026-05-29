jQuery(function ($) {
  // Existing product search handling (kept intact)
  if ($(".zs-product-search").length) {
    $(".zs-product-search").select2({
      placeholder: "Search products...",
      ajax: {
        url: zsAdmin.ajaxUrl,
        dataType: "json",
        delay: 250,
        data: function (params) {
          return {
            term: params.term,
            action: "zs_search_products",
            nonce: zsAdmin.nonce,
          };
        },
        processResults: function (data) {
          return {
            results: data,
          };
        },
        cache: true,
      },
      minimumInputLength: 1,
    });
  }

  // Existing category dropdown select2 handling (kept intact)
  if ($(".zs-modern-select").length) {
    $(".zs-modern-select").select2({
      placeholder: "Select categories",
    });
  }

  /*
  |--------------------------------------------------------------------------
  | Step 1 to Step 2 Drilldown Matrix Handler
  |--------------------------------------------------------------------------
  */
  function loadMatrixProducts(categoryId, selectedProductIds) {
    var $matrixContainer = $("#zs_product_checkbox_matrix");

    if (!categoryId) {
      $matrixContainer.html(
        '<p class="zs-matrix-notice">Please pick a product category above to populate matching items.</p>',
      );
      return;
    }

    $matrixContainer.html(
      '<p class="zs-matrix-notice">Fetching matching products, please wait...</p>',
    );

    $.ajax({
      url: zsAdmin.ajaxUrl,
      type: "POST",
      dataType: "json",
      data: {
        action: "zs_get_products_by_category",
        nonce: zsAdmin.nonce,
        category_id: categoryId,
      },
      success: function (response) {
        if (response.success) {
          var products = response.data;

          if (products.length === 0) {
            $matrixContainer.html(
              '<p class="zs-matrix-notice">No active products found inside this specific category.</p>',
            );
            return;
          }

          var htmlOutput = "";
          $.each(products, function (index, product) {
            // Check if this product was previously saved/attached to this rule
            var isChecked =
              $.inArray(parseInt(product.id), selectedProductIds) !== -1
                ? " checked"
                : "";

            htmlOutput += '<label class="zs-matrix-item">';
            htmlOutput +=
              '  <input type="checkbox" name="products[]" value="' +
              product.id +
              '"' +
              isChecked +
              ">";
            htmlOutput += '  <span class="zs-matrix-label">';
            htmlOutput += "    <strong>" + product.name + "</strong>";
            htmlOutput +=
              '    <span class="zs-matrix-sub">ID: #' + product.id + "</span>";
            htmlOutput += "  </span>";
            htmlOutput += "</label>";
          });

          $matrixContainer.html(htmlOutput);
        } else {
          $matrixContainer.html(
            '<p class="zs-matrix-notice" style="color:#e06666;">Error: Could not retrieve items.</p>',
          );
        }
      },
      error: function () {
        $matrixContainer.html(
          '<p class="zs-matrix-notice" style="color:#e06666;">Network error communicating with the validation server.</p>',
        );
      },
    });
  }

  // Handle dropdown value modifications manually
  $("#zs_category_drilldown").on("change", function () {
    var categoryId = $(this).val();
    var selectedProductIds =
      $("#zs_product_checkbox_matrix").data("selected-products") || [];
    loadMatrixProducts(categoryId, selectedProductIds);
  });

  // FIX: Run immediately on page load to restore checkboxes automatically when in EDIT mode
  var initialCategory = $("#zs_category_drilldown").val();
  if (initialCategory) {
    var savedProducts =
      $("#zs_product_checkbox_matrix").data("selected-products") || [];
    loadMatrixProducts(initialCategory, savedProducts);
  }
});
