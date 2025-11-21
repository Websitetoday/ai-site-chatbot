/**
 * AI Site Chatbot – Verbeterde Admin Indexer
 * Ontwikkeld door Websitetoday.nl
 */

jQuery(document).ready(function ($) {

	// Cache DOM
	const $btn = $("#aisc-start-index");
	const $bar = $("#aisc-progress");
	const $count = $("#aisc-index-count");
	const $last = $("#aisc-last-indexed");
	const $noticeArea = $("#aisc-notice-area");

	// Helper: admin notice
	function showNotice(type, message) {
		const icon = type === "success" ? "✔️" :
		             type === "error" ? "❌" : "ℹ️";

		const html = `
			<div class="aisc-notice ${type}">
				<span class="aisc-icon">${icon}</span>
				${message}
			</div>
		`;

		$noticeArea.html(html);
	}

	// Helper: progress bar
	function setProgress(p) {
		p = Math.max(0, Math.min(100, Math.floor(p)));
		$bar.css({ width: p + "%", display: "block" }).text(p + "%");
	}

	// AJAX call
	function startIndexing() {
		return $.ajax({
			url: aiscIndex.ajaxurl,
			type: "POST",
			dataType: "json",
			data: {
				action: "aisc_index_website",
				nonce: aiscIndex.nonce,
			},
		});
	}

	//---------------------------------------------------------------------
	// CLICK HANDLER
	//---------------------------------------------------------------------

	$btn.on("click", async function () {

		// Disable UI
		$btn.prop("disabled", true).text("Indexeren...");
		setProgress(8);
		showNotice("info", "Indexeren gestart…");

		try {
			const res = await startIndexing();

			setProgress(85);

			if (!res || !res.success) {
				throw new Error(res?.data?.message || "Onbekende fout.");
			}

			//---------------------------------------------------------
			// SUCCESVOL
			//---------------------------------------------------------

			const count = res.data.count || 0;
			const now = new Date().toISOString().slice(0, 19).replace("T", " ");

			$count.text(count);
			$last.text(now);

			setProgress(100);

			showNotice("success", `✔️ Indexering voltooid — ${count} items opgeslagen.`);

			// Knop definitief blokkeren
			$btn.text("Maandlimiet bereikt (1× per 30 dagen)").prop("disabled", true);

		} catch (error) {

			console.error("Indexering mislukt:", error);

			setProgress(100);
			showNotice("error", "❌ Indexeren mislukt: " + error.message);

			// Knop terugzetten
			$btn.prop("disabled", false).text("Website herindexeren");

		} finally {

			// Progressbar vertragen voor UI smoothness
			setTimeout(() => {
				$bar.fadeOut(400, () => {
					$bar.css("width", "0").text("");
				});
			}, 600);

		}
	});
});
