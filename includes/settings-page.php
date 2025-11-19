<?php
/**
 * AI Site Chatbot – Instellingenpagina (Free Edition)
 * Ontwikkeld door Rick Memelink – Websitetoday.nl
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registreer instellingen
 */
add_action('admin_init', function() {
	register_setting('aisc_settings', 'aisc_site_name');
	register_setting('aisc_settings', 'aisc_bot_name');
	register_setting('aisc_settings', 'aisc_phone');
	register_setting('aisc_settings', 'aisc_email');
});

/**
 * Voeg menu toe aan instellingen
 */
add_action('admin_menu', function() {
	add_options_page(
		'AI Site Chatbot',
		'AI Site Chatbot',
		'manage_options',
		'ai-site-chatbot',
		'aisc_settings_page_html'
	);
});

/**
 * Helper: stats ophalen uit proxy
 */
function aisc_get_proxy_stats_for_domain( $domain ) {

	// Bepaal stats-URL op basis van proxy-URL (chat.php → stats.php)
	$stats_url = '';
	if ( defined('AISC_PROXY_URL') ) {
		$stats_url = preg_replace('#chat\.php$#', 'stats.php', AISC_PROXY_URL);
	}

	if ( ! $stats_url ) {
		return new WP_Error('no_url', 'Stats-URL niet beschikbaar.');
	}

	$args = [
		'timeout' => 5,
		'headers' => [
			'X-Client-Key' => defined('AISC_PROXY_CLIENT_KEY') ? AISC_PROXY_CLIENT_KEY : '',
		]
	];

	$stats_url = add_query_arg( 'domain', $domain, $stats_url );
	$response  = wp_remote_get( $stats_url, $args );

	if ( is_wp_error($response) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code($response);
	$body = wp_remote_retrieve_body($response);

	if ( $code !== 200 ) {
		return new WP_Error('bad_status', 'Proxy gaf HTTP ' . $code, ['body' => $body]);
	}

	$data = json_decode($body, true);
	if ( json_last_error() !== JSON_ERROR_NONE || ! is_array($data) ) {
		return new WP_Error('bad_json', 'Ongeldige JSON van proxy.');
	}

	return $data;
}

/**
 * HTML voor instellingenpagina
 */
function aisc_settings_page_html() {
	if ( ! current_user_can('manage_options') ) return;

	$bot_name     = get_option('aisc_bot_name', 'Rickerd');
	$site_name    = get_option('aisc_site_name', get_bloginfo('name'));
	$phone        = get_option('aisc_phone');
	$email        = get_option('aisc_email');
	$last_indexed = get_option('aisc_last_indexed');

	// Uploadpad bepalen
	if ( function_exists('aisc_get_data_dir') ) {
		$index_file = trailingslashit( aisc_get_data_dir() ) . 'index.json';
	} else {
		$upload_dir = wp_upload_dir();
		$index_file = trailingslashit( $upload_dir['basedir'] ) . 'ai-site-chatbot/index.json';
	}

	$index_count = 0;
	if ( file_exists($index_file) ) {
		$json = @file_get_contents($index_file);
		$arr  = json_decode($json, true);
		if ( is_array($arr) ) $index_count = count($arr);
	}

	$can_index = true;
	if ( $last_indexed && strtotime($last_indexed) > strtotime('-30 days') ) {
		$can_index = false;
	}

	// === Credits uit proxy ophalen ===
	$domain = parse_url( home_url(), PHP_URL_HOST );
	$credits_limit = (int) AISC_PROXY_MONTHLY_LIMIT;
	$credits_used  = 0;
	$credits_rem   = $credits_limit;
	$month_label   = '';
	$reset_label   = '';
	$bar_class     = 'ok';
	$percent       = 0;
	$stats_error   = '';

	$stats = aisc_get_proxy_stats_for_domain( $domain );
	if ( is_wp_error($stats) ) {
		$stats_error = $stats->get_error_message();
	} else {
		$credits_used = isset($stats['count']) ? (int) $stats['count'] : 0;
		$credits_limit = isset($stats['limit']) ? (int) $stats['limit'] : $credits_limit;
		$credits_used  = max(0, min($credits_used, $credits_limit));
		$credits_rem   = max(0, $credits_limit - $credits_used);

		$month_key = $stats['month'] ?? date('Y-m');
		$dt        = DateTime::createFromFormat('Y-m-d', $month_key . '-01');

		if ( $dt instanceof DateTime ) {
			$month_label = date_i18n('F Y', $dt->getTimestamp());
		} else {
			$month_label = $month_key;
		}

		$reset_hint = $stats['reset_hint'] ?? '';
		if ( $reset_hint ) {
			$reset_ts = strtotime($reset_hint);
			if ( $reset_ts ) {
				$reset_label = date_i18n('j F Y, H:i', $reset_ts);
			}
		}

		if ( ! $reset_label ) {
			$reset_label = 'Begin van de volgende maand';
		}

		$percent = $credits_limit > 0 ? round(($credits_used / $credits_limit) * 100) : 0;
		$percent = max(0, min(100, $percent));

		if ( $credits_rem <= 10 ) {
			$bar_class = 'danger';
		} elseif ( $credits_rem <= 25 ) {
			$bar_class = 'warn';
		}
	}

	$status_html = '<span style="display:inline-block;padding:4px 8px;border-radius:12px;background:#F3F5FF;color:#134475;font-weight:600;">Proxy-modus actief • gratis ' . (int)AISC_PROXY_MONTHLY_LIMIT . '/maand</span>';
	?>

	<div class="wrap">
		<h1>🤖 AI Site Chatbot – Instellingen</h1>
		<p style="color:#555;max-width:700px;">Deze gratis versie gebruikt de beveiligde Websitetoday Proxy om AI-antwoorden te genereren. Elke site kan tot maximaal <strong><?php echo AISC_PROXY_MONTHLY_LIMIT; ?></strong> chats per 30 dagen uitvoeren.</p>

		<style>
			.aisc-credits-card {
				margin: 16px 0 24px;
				padding: 16px 18px;
				border-radius: 14px;
				background: #0b1020;
				color: #f4f4f9;
				box-shadow: 0 14px 35px rgba(0,0,0,0.30);
				max-width: 520px;
				position: relative;
				overflow: hidden;
			}
			.aisc-credits-card::before {
				content: "";
				position: absolute;
				inset: -40%;
				background: radial-gradient(circle at 0 0, rgba(233,117,43,0.25), transparent 55%),
				            radial-gradient(circle at 100% 100%, rgba(19,68,117,0.35), transparent 50%);
				opacity: 0.8;
				pointer-events: none;
			}
			.aisc-credits-inner {
				position: relative;
				z-index: 1;
			}
			.aisc-credits-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 10px;
				gap: 8px;
			}
			.aisc-credits-title {
				font-weight: 600;
				font-size: 15px;
				display: inline-flex;
				align-items: center;
				gap: 6px;
			}
			.aisc-credits-badge {
				font-size: 12px;
				padding: 4px 10px;
				border-radius: 999px;
				background: rgba(2, 247, 132, 0.12);
				color: #55f39b;
				border: 1px solid rgba(2, 247, 132, 0.4);
			}
			.aisc-credits-progress {
				position: relative;
				height: 9px;
				border-radius: 999px;
				background: rgba(255,255,255,0.06);
				overflow: hidden;
				margin: 6px 0 8px;
			}
			.aisc-credits-progress-inner {
				height: 100%;
				border-radius: inherit;
				transition: width .4s ease-out, background .3s ease-out;
			}
			.aisc-credits-ok {
				background: linear-gradient(90deg, #02f784, #31cfff);
			}
			.aisc-credits-warn {
				background: linear-gradient(90deg, #f9c74f, #f8961e);
			}
			.aisc-credits-danger {
				background: linear-gradient(90deg, #f94144, #f3722c);
			}
			.aisc-credits-meta {
				display: flex;
				justify-content: space-between;
				font-size: 12px;
				opacity: 0.9;
				gap: 10px;
				flex-wrap: wrap;
			}
			.aisc-credits-meta span:last-child {
				text-align: right;
			}
			.aisc-credits-card .description {
				margin-top: 6px;
				font-size: 11.5px;
			}
			.aisc-credits-error {
				color: #f9b4a8;
				font-size: 12px;
				margin-top: 6px;
			}
		</style>

		<div class="aisc-credits-card">
			<div class="aisc-credits-inner">
				<div class="aisc-credits-header">
					<span class="aisc-credits-title">
						<span>📊 AI Credits deze periode</span>
					</span>
					<span class="aisc-credits-badge">
						<?php echo esc_html( $credits_rem ); ?> over
					</span>
				</div>

				<div class="aisc-credits-progress">
					<div class="aisc-credits-progress-inner aisc-credits-<?php echo esc_attr($bar_class); ?>"
					     style="width: <?php echo esc_attr($percent); ?>%;"></div>
				</div>

				<div class="aisc-credits-meta">
					<span>
						<?php if ( $stats_error ): ?>
							<?php echo esc_html( 'Teller niet beschikbaar (' . $stats_error . ')' ); ?>
						<?php else: ?>
							<?php echo esc_html( $credits_used . ' / ' . $credits_limit . ' chats gebruikt'); ?>
						<?php endif; ?>
					</span>
					<span>
						<?php if ( ! $stats_error && $month_label ): ?>
							Maand: <?php echo esc_html( $month_label ); ?> • Reset: <?php echo esc_html( $reset_label ); ?>
						<?php else: ?>
							Limiet: <?php echo esc_html( $credits_limit ); ?> chats / 30 dagen
						<?php endif; ?>
					</span>
				</div>

				<?php if ( $stats_error ): ?>
					<p class="aisc-credits-error">
						De proxy kon niet worden bereikt. De weergegeven waarde kan afwijken van de werkelijke stand.
					</p>
				<?php elseif ( $credits_rem <= 10 ): ?>
					<p class="description" style="color:#f9b4a8;">
						Je zit bijna aan de limiet van de gratis proxy. Overweeg een upgrade naar de Pro-versie voor onbeperkt gebruik.
					</p>
				<?php else : ?>
					<p class="description" style="color:#d3d7ff;">
						Deze teller wordt realtime bijgewerkt en is alleen ter indicatie.
					</p>
				<?php endif; ?>
			</div>
		</div>

		<form method="post" action="options.php">
			<?php settings_fields('aisc_settings'); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="aisc_bot_name">Naam chatbot</label></th>
					<td><input name="aisc_bot_name" type="text" id="aisc_bot_name" value="<?php echo esc_attr($bot_name); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="aisc_site_name">Website-/bedrijfsnaam</label></th>
					<td><input name="aisc_site_name" type="text" id="aisc_site_name" value="<?php echo esc_attr($site_name); ?>" class="regular-text"></td>
				</tr>
				<tr>
					<th scope="row"><label for="aisc_phone">Telefoonnummer (optioneel)</label></th>
					<td><input name="aisc_phone" type="text" id="aisc_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" placeholder="+31 6 12345678"></td>
				</tr>
				<tr>
					<th scope="row"><label for="aisc_email">E-mailadres (optioneel)</label></th>
					<td>
						<input name="aisc_email" type="email" id="aisc_email"
						       value="<?php echo esc_attr($email); ?>"
						       class="regular-text" placeholder="info@voorbeeld.nl">
						<p class="description">Wordt gebruikt voor de “Mail ons”-knop in de chat.</p>
					</td>
				</tr>
			</table>

			<?php submit_button('Instellingen opslaan'); ?>
		</form>

		<hr>

		<h2>📚 Website-indexering</h2>
		<p>De gratis versie indexeert alleen <strong>pagina’s</strong> (geen berichten of custom posttypes).<br>
		Je kunt je website maximaal één keer per maand opnieuw laten indexeren.</p>

		<p>
			<strong id="aisc-index-count"><?php echo intval($index_count); ?></strong> items in index.<br>
			<em>Laatst geïndexeerd: <span id="aisc-last-indexed"><?php echo $last_indexed ? esc_html($last_indexed) : '–'; ?></span></em>
		</p>

		<?php if ( $can_index ): ?>
			<button id="aisc-start-index" class="button button-primary">🌐 Website herindexeren</button>
		<?php else: ?>
			<p><em>⚠️ Je kunt slechts 1× per maand indexeren.</em></p>
		<?php endif; ?>

		<div id="aisc-progress" style="display:none;height:10px;background:#134475;margin-top:10px;width:0;border-radius:6px;transition:width .3s;"></div>

		<hr>

		<p><?php echo $status_html; ?></p>
		<p style="font-size:12px;color:#888;">Gratis modus via Websitetoday-proxy: max. <?php echo AISC_PROXY_MONTHLY_LIMIT; ?> chats per 30 dagen per domein.<br>
		Upgrade naar Pro voor onbeperkt gebruik, meer posttypes en WhatsApp-integratie.</p>
	</div>

	<?php
	wp_enqueue_script('aisc-admin-indexer', AISC_URL . 'assets/js/admin-indexer.js', ['jquery'], AISC_VERSION, true);
	wp_localize_script('aisc-admin-indexer', 'aiscIndex', [
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce'   => wp_create_nonce('aisc_ajax_index'),
	]);
}
