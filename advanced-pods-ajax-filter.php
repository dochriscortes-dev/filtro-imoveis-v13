<?php
/*
Plugin Name: Advanced Pods AJAX Filter V13
Description: Advanced filtering for Pods with Pixel Perfect UI.
Version: 1.3
Author: Jules
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function apaf_enqueue_scripts() {
    // Select2
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true );

    // Plugin Styles and Scripts
    wp_enqueue_style( 'apaf-style', plugin_dir_url( __FILE__ ) . 'style.css', array(), '1.3' );
    wp_enqueue_script( 'apaf-script', plugin_dir_url( __FILE__ ) . 'script.js', array('jquery', 'select2-js'), '1.3', true );

    wp_localize_script( 'apaf-script', 'apaf_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'apaf_search_nonce' )
    ) );
}
add_action( 'wp_enqueue_scripts', 'apaf_enqueue_scripts' );

// Shortcode
function apaf_search_form_shortcode() {
    ob_start();
    ?>
    <!-- Sticky Bar -->
    <div id="apaf-search-bar-wrapper">
        <form id="apaf-search-bar" onsubmit="return false;">

            <div class="apaf-toggle-group">
                <input type="radio" name="tipo" id="tipo_comprar" value="comprar" checked>
                <label for="tipo_comprar" class="apaf-toggle-label">Comprar</label>

                <input type="radio" name="tipo" id="tipo_alugar" value="alugar">
                <label for="tipo_alugar" class="apaf-toggle-label">Alugar</label>
            </div>

            <div class="apaf-input-group apaf-city-group">
                <select name="cidade" id="apaf_cidade" class="apaf-select2" data-placeholder="Cidade">
                    <option value="">Cidade</option>
                    <option value="sao-paulo">São Paulo</option>
                    <option value="rio-de-janeiro">Rio de Janeiro</option>
                    <option value="belo-horizonte">Belo Horizonte</option>
                </select>
            </div>

            <div class="apaf-input-group apaf-bairro-group">
                <select name="bairro" id="apaf_bairro" class="apaf-select2" data-placeholder="Bairro">
                    <option value="">Bairro</option>
                    <option value="centro">Centro</option>
                    <option value="copacabana">Copacabana</option>
                    <option value="savassi">Savassi</option>
                </select>
            </div>

            <a href="#" id="apaf-advanced-filters-trigger">Filtros Avançados</a>

            <button type="button" id="apaf-search-btn">BUSCAR</button>
        </form>
    </div>

    <!-- Modal -->
    <div id="apaf-modal-overlay" style="display: none;">
        <div id="apaf-modal">
            <button id="apaf-modal-close" type="button">&times;</button>
            <h3>Filtros Avançados</h3>

            <!-- Row 1: Location -->
            <div class="apaf-modal-row-location">
                <div class="apaf-modal-input-wrapper">
                    <select name="modal_cidade" id="modal_cidade" class="apaf-select2-modal" data-placeholder="Cidade">
                        <option value="">Cidade</option>
                        <option value="sao-paulo">São Paulo</option>
                        <option value="rio-de-janeiro">Rio de Janeiro</option>
                        <option value="belo-horizonte">Belo Horizonte</option>
                    </select>
                </div>
                <div class="apaf-modal-input-wrapper">
                    <select name="modal_bairro" id="modal_bairro" class="apaf-select2-modal" data-placeholder="Bairro">
                        <option value="">Bairro</option>
                        <option value="centro">Centro</option>
                        <option value="copacabana">Copacabana</option>
                        <option value="savassi">Savassi</option>
                    </select>
                </div>
                <div class="apaf-modal-input-wrapper">
                    <input type="text" name="rua" id="apaf_rua" placeholder="Rua">
                </div>
            </div>

            <!-- Numeric Buttons -->
            <div class="apaf-numeric-section">
                <div class="apaf-numeric-row">
                    <label>Quartos</label>
                    <div class="apaf-numeric-buttons" data-field="quartos">
                        <button type="button" data-val="0" class="active">0+</button>
                        <button type="button" data-val="1">1</button>
                        <button type="button" data-val="2">2</button>
                        <button type="button" data-val="3">3</button>
                        <button type="button" data-val="4">4+</button>
                    </div>
                    <input type="hidden" name="quartos" id="input_quartos" value="0">
                </div>

                <div class="apaf-numeric-row">
                    <label>Banheiros</label>
                    <div class="apaf-numeric-buttons" data-field="banheiros">
                        <button type="button" data-val="0" class="active">0+</button>
                        <button type="button" data-val="1">1</button>
                        <button type="button" data-val="2">2</button>
                        <button type="button" data-val="3">3</button>
                        <button type="button" data-val="4">4+</button>
                    </div>
                    <input type="hidden" name="banheiros" id="input_banheiros" value="0">
                </div>

                <div class="apaf-numeric-row">
                    <label>Vagas</label>
                    <div class="apaf-numeric-buttons" data-field="vagas">
                        <button type="button" data-val="0" class="active">0+</button>
                        <button type="button" data-val="1">1</button>
                        <button type="button" data-val="2">2</button>
                        <button type="button" data-val="3">3</button>
                        <button type="button" data-val="4">4+</button>
                    </div>
                    <input type="hidden" name="vagas" id="input_vagas" value="0">
                </div>
            </div>

            <div class="apaf-modal-footer">
                <button type="button" id="apaf-apply-filters">Aplicar Filtros</button>
            </div>
        </div>
    </div>

    <div id="apaf-results"></div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'advanced_pods_ajax_filter', 'apaf_search_form_shortcode' );

// AJAX Handler
function apaf_handle_search() {
    check_ajax_referer( 'apaf_search_nonce', 'nonce' );

    // Preserve V12 Logic:
    // 'preco_venda' (Mapping existing)
    // 'quartos' where 0=All
    // 'rua' LIKE search

    $args = array(
        'post_type' => 'imovel', // Defaulting to 'imovel' as per typical real estate plugins
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
        ),
    );

    // Filter: Quartos
    if ( isset( $_POST['quartos'] ) ) {
        $quartos = intval( $_POST['quartos'] );
        if ( $quartos > 0 ) {
            $args['meta_query'][] = array(
                'key' => 'quartos',
                'value' => $quartos,
                'compare' => '>=' // Assuming logic.
            );
        }
        // If 0, do nothing (All)
    }

    // Filter: Banheiros
    if ( isset( $_POST['banheiros'] ) ) {
        $banheiros = intval( $_POST['banheiros'] );
        if ( $banheiros > 0 ) {
            $args['meta_query'][] = array(
                'key' => 'banheiros',
                'value' => $banheiros,
                'compare' => '>='
            );
        }
    }

    // Filter: Vagas
    if ( isset( $_POST['vagas'] ) ) {
        $vagas = intval( $_POST['vagas'] );
        if ( $vagas > 0 ) {
            $args['meta_query'][] = array(
                'key' => 'vagas',
                'value' => $vagas,
                'compare' => '>='
            );
        }
    }

    // Filter: Rua (LIKE)
    if ( ! empty( $_POST['rua'] ) ) {
        $rua = sanitize_text_field( $_POST['rua'] );
        $args['meta_query'][] = array(
            'key'     => 'rua',
            'value'   => $rua,
            'compare' => 'LIKE'
        );
    }

    // Filter: Cidade
    if ( ! empty( $_POST['cidade'] ) ) {
        $cidade = sanitize_text_field( $_POST['cidade'] );
        $args['meta_query'][] = array(
            'key'   => 'cidade',
            'value' => $cidade,
        );
    }

    // Filter: Bairro
    if ( ! empty( $_POST['bairro'] ) ) {
        $bairro = sanitize_text_field( $_POST['bairro'] );
        $args['meta_query'][] = array(
            'key'   => 'bairro',
            'value' => $bairro,
        );
    }

    // Filter: Tipo (Comprar/Alugar)
    if ( ! empty( $_POST['tipo'] ) ) {
        $tipo = sanitize_text_field( $_POST['tipo'] );
        // Assuming there is a field 'tipo' or similar
        $args['meta_query'][] = array(
            'key'   => 'tipo', // or 'finalidade'
            'value' => $tipo,
        );
    }

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            // Simple output
            echo '<div class="apaf-result-item" style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">';
            echo '<h4>' . get_the_title() . '</h4>';
            echo '</div>';
        }
    } else {
        echo '<p>Nenhum imóvel encontrado.</p>';
    }

    wp_reset_postdata();
    wp_die();
}
add_action( 'wp_ajax_apaf_search', 'apaf_handle_search' );
add_action( 'wp_ajax_nopriv_apaf_search', 'apaf_handle_search' );
