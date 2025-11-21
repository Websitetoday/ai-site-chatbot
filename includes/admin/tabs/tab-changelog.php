<?php
/**
 * Tab: Changelog – direct vanaf GitHub laden
 */

if (!defined('ABSPATH')) exit;

// RAW URL naar changelog
$github_changelog_url = 'https://raw.githubusercontent.com/Websitetoday/ai-site-chatbot/main/CHANGELOG.md';

// Cache key
$cache_key = 'aisc_github_changelog_cache';

// Eerst cache proberen
$cached = get_transient($cache_key);
if ($cached !== false) {
	$changelog = $cached;
	$error_message = '';
} else {

	$response = wp_remote_get($github_changelog_url, ['timeout' => 6]);
	$changelog = '';
	$error_message = '';

	if (is_wp_error($response)) {
		$error_message = 'Kon geen verbinding maken met GitHub.';
	} else {
		$code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);

		if ($code !== 200 || empty($body)) {
			$error_message = 'GitHub gaf een foutmelding (' . intval($code) . ').';
		} else {
			$changelog = $body;
			set_transient($cache_key, $changelog, HOUR_IN_SECONDS);
		}
	}
}

// Markdown simpel tonen (veilig)
function aisc_simple_md_to_html($text) {
	$text = esc_html($text);
	$text = preg_replace('/^# (.*)$/m', '<h2>$1</h2>', $text);
	$text = preg_replace('/^## (.*)$/m', '<h3>$1</h3>', $text);
	$text = preg_replace('/^\* (.*)$/m', '<li>$1</li>', $text);
	$text = preg_replace('/^- (.*)$/m', '<li>$1</li>', $text);
	$text = preg_replace('/(<li>.*<\/li>)/m', '<ul>$1</ul>', $text);
	return nl2br($text);
}

?>

<div class="aisc-tab-content">

	<h2>📝 Changelog</h2>
	<p style="max-width:650px;">
		De changelog wordt automatisch geladen vanuit GitHub (altijd de nieuwste versie).
	</p>

	<?php if ($error_message): ?>

		<div class="aisc-changelog-error">
			<strong>❌ Changelog kon niet geladen worden.</strong><br>
			<?php echo esc_html($error_message); ?><br><br>
			Bekijk hem handmatig op GitHub:<br>
			<a href="https://github.com/Websitetoday/ai-site-chatbot/blob/main/CHANGELOG.MD" target="_blank">
				➡️ Bekijk CHANGELOG.md
			</a>
		</div>

	<?php else: ?>

		<div class="aisc-changelog-box">
			<?php echo aisc_simple_md_to_html($changelog); ?>
		</div>

	<?php endif; ?>

</div>
