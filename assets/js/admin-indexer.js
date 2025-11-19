/**
 * AI Site Chatbot – Admin Indexer (Free Edition)
 * Ontwikkeld door Websitetoday.nl
 */

jQuery(document).ready(function ($) {
	const $btn = $("#aisc-start-index");
	const $bar = $("#aisc-progress");

	function setProgress(p) {
		p = Math.max(0, Math.min(100, p | 0));
		$bar.css({ width: p + "%", display: "block" }).text(p + "%");
	}

	function callAjax() {
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

	$btn.on("click", async function () {
		$btn.prop("disabled", true).text("Bezig met indexeren...");
		setProgress(10);

		try {
			const res = await callAjax();
			setProgress(90);

			if (res && res.success) {
				const count = res.data.count || 0;
				const msg = res.data.message || `✅ ${count} items geïndexeerd.`;

				// Update UI
				$("#aisc-index-count").text(count);
				const now = new Date().toISOString().slice(0, 19).replace("T", " ");
				$("#aisc-last-indexed").text(now);

				setProgress(100);
				alert(msg);

				// Knop blokkeren wegens maandlimiet
				$btn.text("Maandlimiet bereikt").prop("disabled", true);
				return;
			}

			throw new Error((res && res.data && res.data.message) || "Onbekende fout tijdens indexeren.");
		} catch (err) {
			console.error("❌ Indexering mislukt:", err);
			alert("❌ Indexering mislukt: " + (err.message || "Onbekende fout"));
			$btn.prop("disabled", false).text("Website herindexeren");
		} finally {
			setTimeout(() => $bar.fadeOut(300), 800);
		}
	});
});
