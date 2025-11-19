<?php
/**
 * AI Site Chatbot - AJAX Index Handler (Free Edition, thin wrapper)
 * Ontwikkeld door Rick Memelink – Websitetoday.nl
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once AISC_PATH . 'includes/indexer.php';

// Gebruik de bestaande handler uit indexer.php
add_action( 'wp_ajax_aisc_index_website', 'aisc_index_website_content' );
add_action( 'wp_ajax_nopriv_aisc_index_website', function() {
	wp_send_json_error([ 'message' => 'Geen toegang (alleen beheerders).' ]);
});
