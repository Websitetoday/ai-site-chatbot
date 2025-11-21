<?php
/**
 * Tab: Instellingen – AI Site Chatbot
 * Vrije versie
 */

if (!defined('ABSPATH')) exit;

$bot_name     = get_option('aisc_bot_name', 'Rickerd');
$site_name    = get_option('aisc_site_name', get_bloginfo('name'));
$phone        = get_option('aisc_phone');
$email        = get_option('aisc_email');
$last_indexed = get_option('aisc_last_indexed');

// Bepaal index.json locatie
if (function_exists('aisc_get_data_dir')) {
	$index_file = trailingslashit(aisc_get_data_dir()) . 'index.json';
} else {
	$upload_dir = wp_upload_dir();
	$index_file = trailingslashit($upload_dir['basedir']) . 'ai-site-chatbot/index.json';
}

$index_count = 0;
if (file_exists($index_file)) {
	$json = @file_get_contents($index_file);
	$arr  = json_decode($json, true);
	if (is_array($arr)) $index_count = count($arr);
}

$can_index = true;
if ($last_indexed && strtotime($last_indexed) > strtotime('-30 days')) {
	$can_index = false;
}

// Proxy stats
$domain        = parse_url(home_url(), PHP_URL_HOST);
$credits_limit = (int) AISC_PROXY_MONTHLY_LIMIT;
$credits_used  = 0;
$credits_rem   = $credits_limit;
$month_label   = '';
$reset_label   = '';
$bar_class     = 'ok';
$percent       = 0;
$stats_error   = '';

$stats = aisc_get_proxy_stats_for_domain($domain);
if (is_wp_error($stats)) {
	$stats_error = $stats->get_error_message();
} else {
	$credits_used  = isset($stats['count']) ? (int) $stats['count'] : 0;
	$credits_limit = isset($stats['limit']) ? (int) $stats['limit'] : $credits_limit;
	$credits_used  = max(0, min($credits_used, $credits_limit));
	$credits_rem   = max(0, $credits_limit - $credits_used);

	$month_key = $stats['month'] ?? date('Y-m');
	$dt        = DateTime::createFromFormat('Y-m-d', $month_key . '-01');

	$month_label = $dt ? date_i18n('F Y', $dt->getTimestamp()) : $month_key;

	$reset_hint = $stats['reset_hint'] ?? '';
	if ($reset_hint) {
		$reset_ts = strtotime($reset_hint);
		if ($reset_ts) $reset_label = date_i18n('j F Y, H:i', $reset_ts);
	}

	if (!$reset_label) {
		$reset_label = 'Begin van de volgende maand';
	}

	$percent = $credits_limit > 0 ? round(($credits_used / $credits_limit) * 100) : 0;
	$percent = max(0, min(100, $percent));

	if ($credits_rem <= 10) {
		$bar_class = 'danger';
	} elseif ($credits_rem <= 25) {
		$bar_class = 'warn';
	}
}

?>

<div class="aisc-tab-content">

	<h2>⚙️ Algemene instellingen</h2>
	<p style="max-width:650px;">Pas de basisinstellingen van je chatbot aan. Deze instellingen zijn beschikbaar in de gratis versie.</p>

	<form method="post" action="options.php" style="margin-top:20px;">
		<?php settings_fields('aisc_settings'); ?>

		<table class="form-table">
			<tr>
				<th scope="row"><label for="aisc_bot_name">Naam chatbot</label></th>
				<td><input name="aisc_bot_name" type="text" id="aisc_bot_name" value="<?php echo esc_attr($bot_name); ?>" class="regular-text"></td>
			</tr>

			<tr>
				<th scope="row"><label for="aisc_site_name">Website- of bedrijfsnaam</label></th>
				<td><input name="aisc_site_name" type="text" id="aisc_site_name" value="<?php echo esc_attr($site_name); ?>" class="regular-text"></td>
			</tr>

			<tr>
				<th scope="row"><label for="aisc_phone">Telefoonnummer</label></th>
				<td>
					<input name="aisc_phone" type="text" id="aisc_phone"
					       value="<?php echo esc_attr($phone); ?>"
					       class="regular-text" placeholder="+31 6 12345678">
				</td>
			</tr>

			<tr>
				<th scope="row"><label for="aisc_email">E-mailadres</label></th>
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

	<hr style="margin:40px 0;">

	<h2>🌐 Website-indexering</h2>
	<p>De gratis versie indexeert <strong>alleen pagina’s</strong>. De index wordt gebruikt om antwoorden beter te maken.</p>

	<p>
		<strong id="aisc-index-count"><?php echo intval($index_count); ?></strong> items geïndexeerd<br>
		<em>Laatst geïndexeerd: <?php echo $last_indexed ? esc_html($last_indexed) : '–'; ?></em>
	</p>

	<?php if ($can_index) : ?>
		<button id="aisc-start-index" class="button button-primary">🌐 Website herindexeren</button>
	<?php else : ?>
		<p><em>⚠️ Je kunt slechts één keer per 30 dagen indexeren.</em></p>
	<?php endif; ?>

	<div id="aisc-progress" style="display:none;height:10px;background:#134475;margin-top:12px;width:0;border-radius:6px;transition:width .3s;"></div>

	<hr style="margin:40px 0;">

	<h2>📊 AI Credits</h2>

<div class="aisc-credits-card">
	<div class="aisc-credits-inner">
		<div class="aisc-credits-header">
			<span class="aisc-credits-title">
				📊 AI Credits deze periode
			</span>
			<span class="aisc-credits-badge">
				<?php echo esc_html($credits_rem); ?> over
			</span>
		</div>

		<div class="aisc-credits-progress">
			<div class="aisc-credits-progress-inner aisc-credits-<?php echo esc_attr($bar_class); ?>"
			     style="width: <?php echo esc_attr($percent); ?>%;"></div>
		</div>

		<div class="aisc-credits-meta">
			<span>
				<?php if ($stats_error): ?>
					Teller niet beschikbaar (<?php echo esc_html($stats_error); ?>)
				<?php else: ?>
					<?php echo esc_html("$credits_used / $credits_limit gebruikte chats"); ?>
				<?php endif; ?>
			</span>

			<span>
				<?php if (!$stats_error && $month_label): ?>
					Maand: <?php echo esc_html($month_label); ?> • Reset: <?php echo esc_html($reset_label); ?>
				<?php else: ?>
					Limiet: <?php echo esc_html($credits_limit); ?> chats / 30 dagen
				<?php endif; ?>
			</span>
		</div>

		<?php if ($stats_error): ?>
			<p class="aisc-credits-error">
				De proxy kon niet worden bereikt. De weergegeven waarde kan afwijken.
			</p>
		<?php elseif ($credits_rem <= 10): ?>
			<p class="description" style="color:#f9b4a8;">
				Je zit bijna aan de limiet. Upgrade naar Pro voor onbeperkt gebruik.
			</p>
		<?php else: ?>
			<p class="description" style="color:#d3d7ff;">
				Deze teller wordt realtime bijgewerkt.
			</p>
		<?php endif; ?>

	</div>
</div>

<p style="margin-top:10px;">Gratis modus via Websitetoday-proxy.<br>
	Upgrade naar <a href="https://www.websitetoday.nl/ai-chatbot-voor-wordpress-websites/" target="_blank"><strong>AI Chatbot Pro</strong></a> voor onbeperkt gebruik.</p>

</div>

<?php
// Scripts
wp_enqueue_script('aisc-admin-indexer', AISC_URL . 'assets/js/admin-indexer.js', ['jquery'], AISC_VERSION, true);
wp_localize_script('aisc-admin-indexer', 'aiscIndex', [
	'ajaxurl' => admin_url('admin-ajax.php'),
	'nonce'   => wp_create_nonce('aisc_ajax_index'),
]);
