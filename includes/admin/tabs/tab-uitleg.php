<?php
/**
 * Tab: Uitleg – How it works
 */

if (!defined('ABSPATH')) exit;

?>

<div class="aisc-tab-content">

	<h2>📘 Hoe werkt de AI Site Chatbot?</h2>
	<p style="max-width:700px;">
		De AI Site Chatbot helpt bezoekers direct met antwoorden op basis van de inhoud van je website.
		Hieronder lees je hoe de plugin werkt, wat er achter de schermen gebeurt en hoe je de chatbot optimaal gebruikt.
	</p>

	<style>
		.aisc-step-box {
			background: #fff;
			padding: 20px 24px;
			border-left: 4px solid #134475;
			border-radius: 10px;
			margin: 22px 0;
			max-width: 760px;
			box-shadow: 0 4px 10px rgba(0,0,0,0.04);
		}
		.aisc-step-title {
			font-size: 18px;
			font-weight: 600;
			margin-bottom: 6px;
			color: #134475;
		}
		.aisc-step-num {
			display: inline-block;
			background: #134475;
			color: #fff;
			width: 26px;
			height: 26px;
			border-radius: 999px;
			text-align: center;
			line-height: 26px;
			margin-right: 8px;
			font-size: 14px;
			font-weight: 600;
		}
		.aisc-tip {
			background: #f7f8fc;
			padding: 14px 18px;
			border-radius: 8px;
			margin-top: 18px;
			font-size: 14px;
			border-left: 3px solid #e9752b;
			max-width: 760px;
		}
		.aisc-pro-pill-small {
			background: #e9752b;
			color: #fff;
			padding: 2px 6px;
			border-radius: 6px;
			font-size: 10px;
			margin-left: 4px;
		}
	</style>

	<div class="aisc-step-box">
		<div class="aisc-step-title">
			<span class="aisc-step-num">1</span> Inhoud indexeren
		</div>
		<p>
			Elke chatbot werkt op basis van een <strong>website-index</strong> – een lijst van pagina’s en hun inhoud.
			In de gratis versie worden automatisch alle <strong>pagina’s</strong> gelezen en samengevat.
		</p>
		<p>
			Je kunt één keer per 30 dagen opnieuw indexeren via de knop <strong>Website herindexeren</strong> in de instellingen.
		</p>

		<div class="aisc-tip">
			💡 <strong>Tip:</strong> Pas pagina’s aan en maak ze zo duidelijk mogelijk.  
			Je chatbot wordt automatisch slimmer na een herindexatie.
		</div>
	</div>

	<div class="aisc-step-box">
		<div class="aisc-step-title">
			<span class="aisc-step-num">2</span> Chatten via de Websitetoday Proxy
		</div>
		<p>
			De gratis versie gebruikt de <strong>Websitetoday Proxy</strong> voor veilige en snelle AI-antwoorden.
			Elke website krijgt:
		</p>
		<ul style="margin-left:18px;list-style:disc;">
			<li><strong>50 chats per 30 dagen</strong></li>
			<li>Alle communicatie loopt beveiligd via SSL</li>
			<li>Chat-antwoorden worden gecombineerd met je geïndexeerde website-inhoud</li>
		</ul>

		<div class="aisc-tip">
			📊 De huidige stand van je chats zie je bovenaan in de <strong>Instellingen</strong> tab.
		</div>
	</div>

	<div class="aisc-step-box">
		<div class="aisc-step-title">
			<span class="aisc-step-num">3</span> Het chatvenster op je website
		</div>
		<p>
			Het chatvenster wordt automatisch op elke pagina geladen.
			Bezoekers kunnen:
		</p>
		<ul style="margin-left:18px;list-style:disc;">
			<li>Vragen stellen</li>
			<li>Nieuwe chat starten</li>
			<li>Jouw telefoonnummer of e-mailadres aanklikken (optioneel)</li>
		</ul>
		<p>
			Je kunt zelf de <strong>naam van de chatbot</strong> en de <strong>site-/bedrijfsnaam</strong> aanpassen.
		</p>
	</div>

	<div class="aisc-step-box">
		<div class="aisc-step-title">
			<span class="aisc-step-num">4</span> Premium mogelijkheden <span class="aisc-pro-pill-small">Pro</span>
		</div>
		<p>
			De Pro-versie opent een hele reeks extra functionaliteiten:
		</p>
		<ul style="margin-left:18px;list-style:disc;">
			<li>Onbeperkt aantal chats & geen proxy-limiet</li>
			<li>Styling aanpassen (kleuren, lettertypes, icon, positie…)</li>
			<li>Websitetoday-branding verwijderen</li>
			<li>Indexeren van meerdere posttypes</li>
			<li>Onbeperkt aantal index-runs</li>
			<li>Trainingsmodus om antwoorden te optimaliseren</li>
		</ul>

		<a href="https://www.websitetoday.nl/ai-chatbot-voor-wordpress-websites/" 
		   target="_blank" 
		   class="button button-primary" 
		   style="background:#e9752b;border-color:#e9752b;margin-top:10px;">
			🚀 Bekijk AI Chatbot Pro
		</a>
	</div>

</div>
