<?php
/**
 * Plugin Name:       AI Site Chatbot
 * Plugin URI:        https://github.com/Websitetoday/ai-site-chatbot
 * GitHub Plugin URI: https://github.com/Websitetoday/ai-site-chatbot
 * Description:       Voeg een AI-chatbot toe aan je website die automatisch inhoud indexeert en via de Websitetoday-proxy antwoord geeft. Gratis versie met maandelijkse limiet.
 * Version:           2.1.2
 * Author:            Websitetoday.nl
 * Author URI:        https://www.websitetoday.nl
 * Text Domain:       ai-site-chatbot
 * Domain Path:       /languages
 * GitHub Branch:     main
 * Requires at least: 5.2
 * Tested up to:      6.7
 * Requires PHP:      7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// === CONSTANTEN ===
define( 'AISC_VERSION', '2.1.2' );
define( 'AISC_PATH', plugin_dir_path( __FILE__ ) );
define( 'AISC_URL', plugin_dir_url( __FILE__ ) );

// === Proxy configuratie ===
define( 'AISC_PROXY_URL', 'https://api.websitetoday.nl/aisc-proxy/chat.php' );
define( 'AISC_PROXY_CLIENT_KEY', 'wt-client-v1-5b98ef2ac1a0' );
define( 'AISC_PROXY_MONTHLY_LIMIT', 50 );

/**
 * ────────────────────────────────────────────────────────────────
 *  Plugin Update Checker (PUC) – GitHub Automatic Updates
 *  https://github.com/YahnisElsts/plugin-update-checker
 * ────────────────────────────────────────────────────────────────
 */
require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Update-checker initialiseren
$updateChecker = PucFactory::buildUpdateChecker(
	'https://github.com/Websitetoday/ai-site-chatbot/',
	__FILE__,
	'ai-site-chatbot'
);

// Branch instellen
$updateChecker->setBranch( 'main' );

// Release assets ophalen (ZIP)
$updateChecker->getVcsApi()->enableReleaseAssets();

// Icons tonen in WordPress plugin-details
add_filter(
	'plugins_api',
	function ( $res, $action, $args ) {
		if (
			'plugin_information' === $action
			&& is_object( $res )
			&& ! empty( $args->slug )
			&& 'ai-site-chatbot' === $args->slug
		) {
			$res->icons = [
				'1x' => plugin_dir_url( __FILE__ ) . 'assets/icon-128x128.png',
				'2x' => plugin_dir_url( __FILE__ ) . 'assets/icon-256x256.png',
			];
		}
		return $res;
	},
	10,
	3
);

/**
 * Veilig opslagpad in uploads-map
 */
function aisc_get_data_dir() {
	$upload_dir = wp_upload_dir();
	$path       = trailingslashit( $upload_dir['basedir'] ) . 'ai-site-chatbot/';
	if ( ! file_exists( $path ) ) {
		wp_mkdir_p( $path );
	}
	return $path;
}

/**
 * Index samenvatting
 */
function aisc_get_index_summary_text( $limit_items = 100, $limit_words = 600 ) {
	$file = trailingslashit( aisc_get_data_dir() ) . 'index.json';
	if ( ! file_exists( $file ) ) {
		return '';
	}

	$json = @file_get_contents( $file );
	$data = json_decode( $json, true );
	if ( ! is_array( $data ) ) {
		return '';
	}

	$parts = [];
	foreach ( array_slice( $data, 0, $limit_items ) as $item ) {
		$title   = isset( $item['title'] ) ? wp_strip_all_tags( $item['title'] ) : '';
		$content = isset( $item['content'] ) ? wp_strip_all_tags( $item['content'] ) : '';
		$content = wp_trim_words( $content, $limit_words, '…' );

		$parts[] = ( $title ? $title . ': ' : '' ) . $content;
	}

	return implode( "\n\n", $parts );
}

// === INCLUDES ===
require_once AISC_PATH . 'includes/settings-page.php';
require_once AISC_PATH . 'includes/ajax-indexer.php';
require_once AISC_PATH . 'includes/indexer.php';
require_once AISC_PATH . 'includes/api-handler.php';

/**
 * FRONTEND STYLES & SCRIPTS
 */
add_action(
	'wp_enqueue_scripts',
	function () {

		$email = get_option( 'aisc_email' );

		wp_enqueue_style( 'aisc-chat-style', AISC_URL . 'assets/css/chatbot.css', [], AISC_VERSION );
		wp_enqueue_script( 'aisc-chat-script', AISC_URL . 'assets/js/chatbot.js', [ 'jquery' ], AISC_VERSION, true );

		wp_localize_script(
			'aisc-chat-script',
			'aiscChat',
			[
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
				'proxyUrl'     => AISC_PROXY_URL,
				'clientKey'    => AISC_PROXY_CLIENT_KEY,
				'siteName'     => get_option( 'aisc_site_name', get_bloginfo( 'name' ) ),
				'botName'      => get_option( 'aisc_bot_name', 'Rickerd' ),
				'phone'        => get_option( 'aisc_phone' ),
				'email'        => $email,
				'nonce'        => wp_create_nonce( 'aisc_chat_nonce' ),
				'poweredBy'    => '<a href="https://www.websitetoday.nl" target="_blank">Websitetoday.nl</a>',
				'indexSummary' => aisc_get_index_summary_text(),
			]
		);
	}
);

/**
 * FRONTEND CHAT VENSTER
 */
add_action(
	'wp_footer',
	function () {

		$phone = get_option( 'aisc_phone' );
		$email = get_option( 'aisc_email' );
		?>

		<div id="aisc-bubble">
			<div id="aisc-chat-icon">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
					<path d="M12 3C7.03 3 3 6.92 3 11.5c0 2.3 1.03 4.37 2.73 5.91-.09.81-.48 2.19-1.55 3.59 0 0-.08.11-.08.25 0 .19.15.34.34.34.09 0 .17-.04.24-.09 1.87-.95 3.29-1.91 4.13-2.57.92.26 1.9.4 2.9.4 4.97 0 9-3.92 9-8.5S16.97 3 12 3z"/>
				</svg>
			</div>

			<div id="aisc-chat-window">
				<div id="aisc-header">
					<div class="aisc-avatar">🤖</div>
					<div>
						<div class="aisc-title"><?php echo esc_html( get_option( 'aisc_bot_name', 'Rickerd' ) ); ?></div>
						<div class="aisc-subtitle"><?php echo esc_html( get_option( 'aisc_site_name', get_bloginfo( 'name' ) ) ); ?></div>
					</div>
					<button id="aisc-new-chat" title="Nieuwe chat">🔄</button>
					<button id="aisc-close" title="Sluiten">×</button>
				</div>

				<div id="aisc-messages"></div>

				<div id="aisc-input">
					<input type="text" id="aisc-user-input" placeholder="Typ je vraag...">
					<button id="aisc-send" aria-label="Verstuur bericht">
						<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
							<path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
						</svg>
					</button>
				</div>

				<?php if ( $phone || $email ) : ?>
					<div id="aisc-contact">
						<?php if ( $phone ) : ?>
							<a href="tel:<?php echo esc_attr( $phone ); ?>" class="aisc-contact-btn tel">📞 Bel ons</a>
						<?php endif; ?>

						<?php if ( $email ) : ?>
							<a href="mailto:<?php echo esc_attr( $email ); ?>" class="aisc-contact-btn mail">✉️ Mail ons</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<div id="aisc-branding">
					Powered by <strong><a href="https://www.websitetoday.nl" target="_blank">Websitetoday.nl</a></strong>
				</div>
			</div>
		</div>

		<?php
	}
);

/**
 * Proxy force in free mode
 */
function aisc_should_use_proxy() {
	return true;
}

/**
 * Plugin-instellingen link
 */
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=ai-site-chatbot' ) ) . '">Instellingen</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}
);

/**
 * Activatie → uploads-map aanmaken
 */
register_activation_hook(
	__FILE__,
	function () {
		aisc_get_data_dir();
	}
);

/**
 * Admin stylesheet
 */
add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'settings_page_ai-site-chatbot' === $hook ) {
			wp_enqueue_style( 'aisc-admin-style', AISC_URL . 'assets/css/admin.css', [], AISC_VERSION );
		}
	}
);

/**
 * AJAX – credits loggen na elke succesvolle chat
 */
add_action( 'wp_ajax_aisc_log_chat_usage', 'aisc_log_chat_usage' );
add_action( 'wp_ajax_nopriv_aisc_log_chat_usage', 'aisc_log_chat_usage' );

function aisc_log_chat_usage() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'aisc_chat_nonce' ) ) {
		wp_send_json_error(
			[
				'message' => 'Ongeldige nonce',
			],
			403
		);
	}

	$limit        = (int) AISC_PROXY_MONTHLY_LIMIT;
	$used         = (int) get_option( 'aisc_credits_used', 0 );
	$period_start = (int) get_option( 'aisc_credits_period_start', 0 );

	// Reset periode na 30 dagen
	if ( ! $period_start || ( time() - $period_start ) > 30 * DAY_IN_SECONDS ) {
		$period_start = time();
		$used         = 0;
	}

	$used++;

	update_option( 'aisc_credits_used', $used );
	update_option( 'aisc_credits_period_start', $period_start );

	$remaining = max( 0, $limit - $used );

	wp_send_json_success(
		[
			'used'      => $used,
			'limit'     => $limit,
			'remaining' => $remaining,
		]
	);
}
