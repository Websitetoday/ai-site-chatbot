<?php
/**
 * AI Site Chatbot – Website Indexer (Free Edition, upload-safe)
 * Ontwikkeld door Rick Memelink – Websitetoday.nl
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Zorg dat de uploadmapfunctie beschikbaar is
 */
if ( ! function_exists( 'aisc_get_data_dir' ) ) {
	function aisc_get_data_dir() {
		$upload_dir = wp_upload_dir();
		$path = trailingslashit( $upload_dir['basedir'] ) . 'ai-site-chatbot/';
		if ( ! file_exists( $path ) ) {
			wp_mkdir_p( $path );
		}
		return $path;
	}
}

/**
 * Bouw de index (alleen pagina’s)
 */
function aisc_build_index() {
	try {
		// Controleer maandelijkse limiet
		$last_indexed = get_option('aisc_last_indexed');
		if ( $last_indexed && strtotime($last_indexed) > strtotime('-30 days') ) {
			return [
				'count'   => 0,
				'file'    => '',
				'message' => 'Je kunt slechts één keer per maand indexeren.'
			];
		}

		// Alleen pagina’s ophalen
		$posts = get_posts([
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		]);

		if ( empty($posts) ) {
			return [ 
				'count' => 0, 
				'file' => '', 
				'message' => 'Geen pagina’s gevonden om te indexeren.' 
			];
		}

		$index_data = [];
		foreach ( $posts as $post ) {
			$content = wp_strip_all_tags( $post->post_content );
			$content = preg_replace( '/\s+/', ' ', $content );
			$content = wp_trim_words( $content, 300, '...' );

			$index_data[] = [
				'id'      => $post->ID,
				'title'   => get_the_title( $post->ID ),
				'url'     => get_permalink( $post->ID ),
				'content' => $content,
			];
		}

		// Opslaan in uploads-map
		$dir  = aisc_get_data_dir();
		$file = $dir . 'index.json';

		file_put_contents( $file, wp_json_encode( $index_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
		update_option( 'aisc_last_indexed', current_time( 'mysql' ) );

		return [
			'count'   => count( $index_data ),
			'file'    => $file,
			'message' => '✅ ' . count( $index_data ) . ' pagina’s succesvol geïndexeerd.'
		];
	}
	catch (Throwable $e) {
		$dir = aisc_get_data_dir();
		file_put_contents( $dir . 'debug-log.txt', $e->getMessage() );
		return [
			'count'   => 0,
			'file'    => '',
			'message' => 'Kritieke fout tijdens indexering: ' . $e->getMessage()
		];
	}
}

/**
 * AJAX-handler: start indexatie (alleen voor admins)
 */
function aisc_index_website_content() {
	check_ajax_referer( 'aisc_ajax_index', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error([ 'message' => 'Geen toestemming om te indexeren.' ]);
	}

	$result = aisc_build_index();

	if ( empty( $result['count'] ) ) {
		wp_send_json_error([ 'message' => $result['message'] ?? 'Geen items geïndexeerd.' ]);
	}

	wp_send_json_success([
		'count'   => $result['count'],
		'message' => $result['message'],
		'file'    => $result['file'],
	]);
}
