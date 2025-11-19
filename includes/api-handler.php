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

	$index_summary = function_exists('aisc_get_index_summary_text') ? aisc_get_index_summary_text() : '';

	$response = wp_remote_post( AISC_PROXY_URL, [
		'timeout' => 60,
		'headers' => [
			'Content-Type'   => 'application/json',
			'X-Client-Key'   => AISC_PROXY_CLIENT_KEY, // ✅ verplaatst naar header
		],
		'body' => wp_json_encode([
			'domain'   => $_SERVER['SERVER_NAME'],
			'message'  => $user_message,
			'site'     => get_bloginfo('name'),
			'url'      => home_url(),
			'model'    => 'gpt-4o-mini',
			'index'    => $index_summary, // ✅ stuur index mee
			'history'  => [], // optioneel: kan gevuld worden als je via AJAX met history werkt
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
