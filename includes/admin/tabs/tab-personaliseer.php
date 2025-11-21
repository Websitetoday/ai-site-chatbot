<?php
/**
 * Tab: Personaliseer — Alleen voor Pro versie
 * Free Edition toont slotscherm
 */

if (!defined('ABSPATH')) exit;

?>

<div class="aisc-tab-content">

    <h2>🎨 Personaliseer je Chatbot <span class="aisc-pro-pill">Pro</span></h2>
    <p style="max-width:650px;">
        In de Pro-versie kun je uitgebreide styling toepassen op het chatvenster:
        kleuren, lettertypes, bubble-styling, positie, avatar, branding-verwijderen en meer.
    </p>

    <style>
        .aisc-locked-box {
            background: #fff;
            padding: 30px;
            border-left: 4px solid #e9752b;
            border-radius: 10px;
            max-width: 680px;
            margin-top: 25px;
        }
        .aisc-locked-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .aisc-pro-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            margin-top: 20px;
        }
        .aisc-pro-feature {
            padding: 16px;
            background: #f7f8fc;
            border-radius: 10px;
            border: 1px solid #e3e6ee;
            display: flex;
            gap: 12px;
            align-items: flex-start;
        }
        .aisc-pro-feature-icon {
            font-size: 20px;
        }
        .aisc-upgrade-btn {
            display: inline-block;
            background: #e9752b;
            color: #fff;
            padding: 12px 18px;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            margin-top: 20px;
        }
        .aisc-upgrade-btn:hover {
            opacity: 0.9;
        }
    </style>

    <div class="aisc-locked-box">

        <div class="aisc-locked-title">🔒 Deze functies zijn alleen beschikbaar in AI Chatbot Pro</div>
        <p>
            Met de Pro-versie kun je de chatbot volledig aanpassen zodat hij perfect aansluit bij jouw branding.
        </p>

        <div class="aisc-pro-grid">

            <div class="aisc-pro-feature">
                <div class="aisc-pro-feature-icon">🎨</div>
                <div>
                    <strong>Eigen kleuren & stijlen</strong><br>
                    Pas alle kleuren van het chatvenster aan: achtergrond, icon, bubbels, kopteksten, knoppen en meer.
                </div>
            </div>

            <div class="aisc-pro-feature">
                <div class="aisc-pro-feature-icon">🔤</div>
                <div>
                    <strong>Lettertypes aanpassen</strong><br>
                    Gebruik Google Fonts of eigen fonts voor een volledig branded chatbot.
                </div>
            </div>

            <div class="aisc-pro-feature">
                <div class="aisc-pro-feature-icon">🤖</div>
                <div>
                    <strong>Eigen avatar / bot-icoon</strong><br>
                    Upload een eigen avatar of gebruik een custom illustration.
                </div>
            </div>

            <div class="aisc-pro-feature">
                <div class="aisc-pro-feature-icon">🚫</div>
                <div>
                    <strong>Websitetoday-branding verwijderen</strong><br>
                    Geen "Powered by Websitetoday" meer zichtbaar.
                </div>
            </div>

            <div class="aisc-pro-feature">
                <div class="aisc-pro-feature-icon">🪟</div>
                <div>
                    <strong>Positie & grootte aanpassen</strong><br>
                    Links, rechts, onder, floating size — alles flexibel in te stellen.
                </div>
            </div>

            <div class="aisc-pro-feature">
                <div class="aisc-pro-feature-icon">📱</div>
                <div>
                    <strong>Extra mobiele instellingen</strong><br>
                    Custom layout op smartphones, zoals compact modus of fullscreen chat.
                </div>
            </div>

        </div>

        <a href="https://www.websitetoday.nl/ai-chatbot-voor-wordpress-websites/" 
           target="_blank" 
           class="aisc-upgrade-btn">
            🚀 Upgrade naar AI Chatbot Pro
        </a>

    </div>

</div>
