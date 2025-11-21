<?php
/**
 * AI Site Chatbot - API Handler (Free Edition)
 * Ontwikkeld door Rick Memelink – Websitetoday.nl
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'wp_ajax_aisc_chat', 'aisc_handle_chat_request' );
add_action( 'wp_ajax_nopriv_aisc_chat', 'aisc_handle_chat_request' );

function aisc_handle_chat_request() {

	check_ajax_referer( 'aisc_chat_nonce', 'nonce' );

	$user_message = sanitize_text_field( $_POST['message'] ?? '' );
	if ( empty( $user_message ) ) {
		wp_send_json_error([ 'message' => 'Leeg bericht ontvangen.' ]);
	}

	$index_summary = function_exists('aisc_get_index_summary_text') 
		? aisc_get_index_summary_text() 
		: '';

	$response = wp_remote_post( AISC_PROXY_URL, [
		'timeout' => 60,
		'headers' => [
			'Content-Type' => 'application/json',
			'X-Client-Key' => AISC_PROXY_CLIENT_KEY, 
		],
		'body' => wp_json_encode([
			'domain'   => $_SERVER['SERVER_NAME'],
			'message'  => $user_message,
			'site'     => get_bloginfo('name'),
			'url'      => home_url(),
			'model'    => 'gpt-4o-mini',
			'index'    => $index_summary,
			'history'  => [],
		]),
	]);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error([ 'message' => 'Fout bij verbinden met proxy.' ]);
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( isset( $data['error'] ) ) {
		wp_send_json_error([ 'message' => $data['error'] ]);
	}

	wp_send_json_success([
		'response' => $data['reply'] ?? '(geen antwoord ontvangen)',
	]);
}


/**
 * -----------------------------------------------------
 * REST ENDPOINT: /wp-json/aisc/reset-index
 * -----------------------------------------------------
 * Hiermee kan de proxy jouw index resetten.
 * Inclusief robuuste header-detectie.
 */
add_action('rest_api_init', function () {

    register_rest_route('aisc', '/reset-index', [
        'methods'  => 'POST',
        'callback' => 'aisc_rest_reset_index',
        'permission_callback' => 'aisc_rest_check_proxy_key'
    ]);
});

/**
 * Controleert of de X-Client-Key overeenkomt
 * Werkt op ALLE servers + Cloudflare + LiteSpeed
 */
function aisc_rest_check_proxy_key() {

    $expected = AISC_PROXY_CLIENT_KEY;
    $received = '';

    // 1 — standaard headers (Apache)
    if (function_exists('getallheaders')) {
        $h = getallheaders();
        if (isset($h['X-Client-Key'])) {
            $received = $h['X-Client-Key'];
        }
    }

    // 2 — LiteSpeed / Nginx varianten
    if (!$received && isset($_SERVER['HTTP_X_CLIENT_KEY'])) {
        $received = $_SERVER['HTTP_X_CLIENT_KEY'];
    }

    // 3 — overige fallback
    if (!$received && isset($_SERVER['X_CLIENT_KEY'])) {
        $received = $_SERVER['X_CLIENT_KEY'];
    }

    return $received === $expected;
}

/**
 * Reset index.json en usage counters
 */
function aisc_rest_reset_index(WP_REST_Request $request)
{
    // verwijder index
    $file = trailingslashit(aisc_get_data_dir()) . 'index.json';
    if (file_exists($file)) unlink($file);

    delete_option('aisc_credits_used');
    delete_option('aisc_credits_period_start');
    delete_option('aisc_last_indexed');

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Index en usage counters succesvol gereset.'
    ], 200);
}


/**
 * Sta custom headers toe
 */
add_filter('rest_allowed_cors_headers', function ($headers) {
    $headers[] = 'X-Client-Key';
    return $headers;
});
