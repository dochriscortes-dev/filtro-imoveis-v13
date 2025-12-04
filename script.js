jQuery(document).ready(function($) {

    // Initialize Select2
    $('.apaf-select2').select2({
        minimumResultsForSearch: Infinity // Hide search box if few items, or keep default
    });

    $('.apaf-select2-modal').select2({
        dropdownParent: $('#apaf-modal')
    });

    // Toggle Modal
    $('#apaf-advanced-filters-trigger').on('click', function(e) {
        e.preventDefault();
        $('#apaf-modal-overlay').fadeIn();
    });

    $('#apaf-modal-close, #apaf-modal-overlay').on('click', function(e) {
        if (e.target === this) {
            $('#apaf-modal-overlay').fadeOut();
        }
    });

    // Numeric Buttons Logic
    $('.apaf-numeric-buttons button').on('click', function() {
        var $btn = $(this);
        var $group = $btn.closest('.apaf-numeric-buttons');
        var field = $group.data('field');
        var value = $btn.data('val');

        // Update active class
        $group.find('button').removeClass('active');
        $btn.addClass('active');

        // Update hidden input
        $('#input_' + field).val(value);
    });

    // Sync Sticky Bar and Modal inputs
    // When Sticky City changes, update Modal City
    $('#apaf_cidade').on('change', function() {
        $('#modal_cidade').val($(this).val()).trigger('change');
    });

    // When Modal City changes, update Sticky City
    $('#modal_cidade').on('change', function() {
        $('#apaf_cidade').val($(this).val()).trigger('change');
    });

    // Same for Bairro
    $('#apaf_bairro').on('change', function() {
        $('#modal_bairro').val($(this).val()).trigger('change');
    });

    $('#modal_bairro').on('change', function() {
        $('#apaf_bairro').val($(this).val()).trigger('change');
    });


    // AJAX Search
    function performSearch() {
        // Collect Data
        var data = {
            action: 'apaf_search',
            nonce: apaf_ajax.nonce,
            tipo: $('input[name="tipo"]:checked').val(),
            cidade: $('#apaf_cidade').val(),
            bairro: $('#apaf_bairro').val(),
            rua: $('#apaf_rua').val(),
            quartos: $('#input_quartos').val(),
            banheiros: $('#input_banheiros').val(),
            vagas: $('#input_vagas').val()
        };

        // Validation: City Required
        if (!data.cidade) {
            alert('Por favor, selecione uma cidade.');
            return;
        }

        // Show Loading (Optional)
        $('#apaf-results').html('<p>Buscando...</p>');

        // Hide modal if open
        $('#apaf-modal-overlay').fadeOut();

        $.post(apaf_ajax.ajax_url, data, function(response) {
            $('#apaf-results').html(response);
        });
    }

    // Trigger Search
    $('#apaf-search-btn').on('click', function() {
        performSearch();
    });

    $('#apaf-apply-filters').on('click', function() {
        performSearch();
    });

});
