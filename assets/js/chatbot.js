/**
 * AI Site Chatbot – Free Edition v2.1
 * Ontwikkeld door Websitetoday.nl
 */
jQuery(document).ready(function ($) {

	/* ====================================================
	   ELEMENTEN
	   ==================================================== */
	const chatIcon   = $("#aisc-chat-icon");
	const chatWindow = $("#aisc-chat-window");
	const closeBtn   = $("#aisc-close");
	const newChatBtn = $("#aisc-new-chat");
	const inputField = $("#aisc-user-input");
	const sendBtn    = $("#aisc-send");
	const messages   = $("#aisc-messages");

	let chatHistory = [];
	let inactivityTimer = null;
	let feedbackShown = false;


	/* ====================================================
	   HELPER — SESSIE OPSLAG
	   ==================================================== */
	function persistHistory() {
		try {
			sessionStorage.setItem("aiscChatHistory", JSON.stringify(chatHistory));
		} catch(e){}
	}

	function loadHistory() {
		try {
			const saved = sessionStorage.getItem("aiscChatHistory");
			if (saved) chatHistory = JSON.parse(saved) || [];
		} catch(e){}
	}


	/* ====================================================
	   BERICHTEN & UI
	   ==================================================== */
	function appendMessage(text, sender = "bot", raw = false) {
		const msg = $("<div>").addClass("message").addClass(sender);

		if (raw) msg.html(text);
		else msg.text(text);

		messages.append(msg);
		scrollToBottom();
		resetInactivityTimer();
	}

	function renderHistory() {
		messages.empty();
		chatHistory.forEach(entry => appendMessage(entry.text, entry.sender, entry.raw));
	}

	function scrollToBottom() {
		messages.stop().animate({ scrollTop: messages[0].scrollHeight }, 300);
	}

	function showTyping() {
		const el = $(`
			<div class="message bot typing">
				<div class="typing-wrapper">
					<span class="typing-text">${aiscChat.botName} typt</span>
					<div class="typing-dots">
						<span class="dot"></span><span class="dot"></span><span class="dot"></span>
					</div>
				</div>
			</div>
		`);
		messages.append(el);
		scrollToBottom();
		return el;
	}


	/* ====================================================
	   DYNAMISCH FEEDBACKBLOK (tel / mail / geen)
	   ==================================================== */
	function showFeedback() {
		if (feedbackShown) return;
		feedbackShown = true;

		let phone = aiscChat.phone ? `<a href="tel:${aiscChat.phone}" class="aisc-contact-btn tel">📞 Bel ons</a>` : "";
		let mail  = aiscChat.email ? `<a href="mailto:${aiscChat.email}" class="aisc-contact-btn mail">✉️ Mail ons</a>` : "";

		let hasContact = (aiscChat.phone || aiscChat.email);

		let messageText = hasContact
			? `Was dit antwoord nuttig?<br>Je kunt ook direct contact opnemen:`
			: `Was dit antwoord nuttig?<br>Je kunt ook direct contact opnemen via de contactpagina.`;

		let contactButtons = hasContact
			? `<div class="aisc-feedback-buttons">${phone}${mail}</div>`
			: ``;

		const feedback = $(`
			<div class="message bot feedback">
				<p>${messageText}</p>
				${contactButtons}
			</div>
		`);

		messages.append(feedback);
		scrollToBottom();
	}

	function resetInactivityTimer() {
		clearTimeout(inactivityTimer);
		feedbackShown = false;
		inactivityTimer = setTimeout(showFeedback, 30000);
	}


	/* ====================================================
	   INTERNE LINKS → ZELFDE VENSTER + AUTO-OPEN CHAT
	   ==================================================== */
	function addLinkFormatting(text) {
		return text.replace(/\[([^\]]+)\]\((https?:\/\/[^\)]+)\)/g, function (match, label, url) {
			const isInternal = url.indexOf(window.location.hostname) !== -1;

			if (isInternal) {
				return `<a href="${url}" class="aisc-link aisc-open-next">${label}</a>`;
			} else {
				return `<a href="${url}" target="_blank" class="aisc-link">${label}</a>`;
			}
		});
	}

	$(document).on("click", ".aisc-open-next", function () {
		sessionStorage.setItem("aiscOpenOnLoad", "1");
	});


	/* ====================================================
	   INLINE MODAL — NIEUWE CHAT
	   ==================================================== */
	function resetChat() {
		chatHistory = [];
		sessionStorage.removeItem("aiscChatHistory");
		messages.empty();

		appendMessage(
			`👋 Hallo! Ik ben <strong>${aiscChat.botName}</strong>, de <strong>AI-assistent</strong> van ${aiscChat.siteName}. Waarmee kan ik je helpen vandaag?`,
			"bot",
			true
		);

		scrollToBottom();
	}

	function showNewChatModal() {
		$(".aisc-modal").remove();

		const modal = $(`
			<div class="message bot aisc-modal">
				<p><strong>Nieuwe chat starten?</strong><br>
				Je huidige gesprek wordt verwijderd.</p>
				<div class="aisc-modal-buttons">
					<button type="button" class="aisc-modal-yes">Nieuwe chat</button>
					<button type="button" class="aisc-modal-cancel">Annuleren</button>
				</div>
			</div>
		`);

		messages.append(modal);
		scrollToBottom();

		modal.find(".aisc-modal-cancel").on("click", () => modal.remove());
		modal.find(".aisc-modal-yes").on("click", () => {
			modal.remove();
			resetChat();
		});
	}


	/* ====================================================
	   PROXY REQUEST + CREDITS LOG
	   ==================================================== */
	async function sendMessageToProxy(message) {
		const typingEl = showTyping();

		try {
			const res = await fetch(aiscChat.proxyUrl, {
				method: "POST",
				headers: {
					"Content-Type": "application/json",
					"X-Client-Key": aiscChat.clientKey
				},
				body: JSON.stringify({
					message: message,
					domain: window.location.hostname,
					history: chatHistory,
					model: "gpt-4o-mini",
					index: aiscChat.indexSummary || ""
				})
			});

			if (!res.ok) throw new Error(`Proxy fout (${res.status})`);

			const data = await res.json();
			typingEl.remove();

			if (data.error) {
				appendMessage("⚠️ " + data.error, "bot");
				return;
			}

			const answer = addLinkFormatting(data.reply || "Er ging iets mis.");
			appendMessage(answer, "bot", true);

			chatHistory.push({ text: message, sender: "user" });
			chatHistory.push({ text: answer, sender: "bot", raw: true });
			persistHistory();

			logCreditUsage();

		} catch (err) {
			console.error("Chatfout:", err);
			typingEl.remove();
			appendMessage("❌ Er ging iets mis bij het contact met de server.", "bot");
		}
	}

	function logCreditUsage() {
		if (!aiscChat.ajaxurl || !aiscChat.nonce) return;

		const body = new URLSearchParams();
		body.append('action', 'aisc_log_chat_usage');
		body.append('nonce', aiscChat.nonce);

		fetch(aiscChat.ajaxurl, {
			method: "POST",
			headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
			body: body.toString()
		}).catch(() => {});
	}


	/* ====================================================
	   EVENTS
	   ==================================================== */
	sendBtn.on("click", function () {
		const message = inputField.val().trim();
		if (!message) return;

		appendMessage(message, "user");
		inputField.val("");
		scrollToBottom();
		sendMessageToProxy(message);
	});

	inputField.on("keypress", function (e) {
		if (e.which === 13 && !e.shiftKey) {
			e.preventDefault();
			sendBtn.click();
		}
	});

	chatIcon.on("click", function () {
		chatWindow.toggleClass("active");
		if (chatWindow.hasClass("active")) inputField.focus();
	});

	closeBtn.on("click", function () {
		chatWindow.removeClass("active");
	});

	newChatBtn.on("click", function () {
		showNewChatModal();
	});


	/* ====================================================
	   INIT
	   ==================================================== */
	loadHistory();

	if (chatHistory.length) {
		renderHistory();
	} else {
		appendMessage(
			`👋 Hallo! Ik ben <strong>${aiscChat.botName}</strong>, de <strong>AI-assistent</strong> van ${aiscChat.siteName}. Hoe kan ik je helpen?`,
			"bot",
			true
		);
	}

	// chat openen na interne link
	if (sessionStorage.getItem("aiscOpenOnLoad") === "1") {
		chatWindow.addClass("active");
		sessionStorage.removeItem("aiscOpenOnLoad");
	}
});
