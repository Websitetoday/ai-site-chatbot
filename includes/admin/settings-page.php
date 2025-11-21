<?php
/**
 * AI Site Chatbot – Admin Settings (Modulair)
 * Ontwikkeld door Rick Memelink – Websitetoday.nl
 */

if (!defined('ABSPATH')) exit;

/**
 * Registreer instellingen
 */
add_action('admin_init', function () {
	register_setting('aisc_settings', 'aisc_site_name');
	register_setting('aisc_settings', 'aisc_bot_name');
	register_setting('aisc_settings', 'aisc_phone');
	register_setting('aisc_settings', 'aisc_email');
});

/**
 * Voeg top-level menu + submenu’s toe
 */
add_action('admin_menu', function () {

	// Top-level menu
	add_menu_page(
		'AI Site Chatbot',
		'AI Site Chatbot',
		'manage_options',
		'ai-site-chatbot',
		'aisc_settings_page_html',
		AISC_URL . 'assets/icon-20x20.png',
		58
	);

	// Submenu's met eigen slug (ZONDER tab param)
	add_submenu_page(
		'ai-site-chatbot',
		'Instellingen',
		'Instellingen',
		'manage_options',
		'ai-site-chatbot',
		'aisc_settings_page_html'
	);

	add_submenu_page(
		'ai-site-chatbot',
		'Personaliseer',
		'Personaliseer',
		'manage_options',
		'ai-site-chatbot-personaliseer',
		'aisc_settings_page_html'
	);

	add_submenu_page(
		'ai-site-chatbot',
		'Uitleg',
		'Uitleg',
		'manage_options',
		'ai-site-chatbot-uitleg',
		'aisc_settings_page_html'
	);

	add_submenu_page(
		'ai-site-chatbot',
		'Changelog',
		'Changelog',
		'manage_options',
		'ai-site-chatbot-changelog',
		'aisc_settings_page_html'
	);

	add_submenu_page(
		'ai-site-chatbot',
		'Word Pro',
		'Word Pro 🔒',
		'manage_options',
		'ai-site-chatbot-upgrade',
		'aisc_settings_page_html'
	);
});

/**
 * Proxy stats helper
 */
function aisc_get_proxy_stats_for_domain($domain)
{
	$stats_url = defined('AISC_PROXY_URL')
		? preg_replace('#chat\.php$#', 'stats.php', AISC_PROXY_URL)
		: '';

	if (!$stats_url) return new WP_Error('no_url', 'Stats-URL niet beschikbaar.');

	$response = wp_remote_get(
		add_query_arg('domain', $domain, $stats_url),
		[
			'timeout' => 5,
			'headers' => [
				'X-Client-Key' => defined('AISC_PROXY_CLIENT_KEY') ? AISC_PROXY_CLIENT_KEY : '',
			]
		]
	);

	if (is_wp_error($response)) return $response;

	$code = wp_remote_retrieve_response_code($response);
	$body = wp_remote_retrieve_body($response);

	if ($code !== 200) {
		return new WP_Error('bad_status', 'Proxy gaf HTTP ' . $code, ['body' => $body]);
	}

	$data = json_decode($body, true);
	if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
		return new WP_Error('bad_json', 'Ongeldige JSON van proxy.');
	}

	return $data;
}

/**
 * MAIN ADMIN PAGE ROUTER
 */
function aisc_settings_page_html()
{
	if (!current_user_can('manage_options')) return;

	// Bepaal tab op basis van ?tab=... óf submenu slug
	$tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';

	$page = isset($_GET['page']) ? sanitize_key($_GET['page']) : 'ai-site-chatbot';

	if (!$tab) {
		$map = [
			'ai-site-chatbot'              => 'instellingen',
			'ai-site-chatbot-personaliseer'=> 'personaliseer',
			'ai-site-chatbot-uitleg'       => 'uitleg',
			'ai-site-chatbot-changelog'    => 'changelog',
			'ai-site-chatbot-upgrade'      => 'upgrade',
		];
		$tab = $map[$page] ?? 'instellingen';
	}

	$tabs = [
		'instellingen' => 'Instellingen',
		'personaliseer' => 'Personaliseer <span class="aisc-pro-pill">🔒 Pro</span>',
		'uitleg'        => 'Uitleg',
		'changelog'     => 'Changelog',
		'upgrade'       => 'Word Pro',
	];
	?>
	<div class="wrap aisc-admin-wrap">

		<h1 class="aisc-admin-title">🤖 AI Site Chatbot</h1>

		<h2 class="nav-tab-wrapper aisc-nav-tabs">
			<?php foreach ($tabs as $key => $label) :
				$active = ($tab === $key) ? ' nav-tab-active' : '';
				?>
				<a href="<?php echo esc_url(admin_url('admin.php?page=ai-site-chatbot&tab=' . $key)); ?>"
				   class="nav-tab<?php echo esc_attr($active); ?>">
					<?php echo $label; ?>
				</a>
			<?php endforeach; ?>
		</h2>

		<?php
		$base = AISC_PATH . 'includes/admin/tabs/';

		switch ($tab) {
			case 'personaliseer':
				require $base . 'tab-personaliseer.php';
				break;
			case 'uitleg':
				require $base . 'tab-uitleg.php';
				break;
			case 'changelog':
				require $base . 'tab-changelog.php';
				break;
			case 'upgrade':
				require $base . 'tab-upgrade.php';
				break;
			case 'instellingen':
			default:
				require $base . 'tab-instellingen.php';
		}
		?>
	</div>
	<?php
}
