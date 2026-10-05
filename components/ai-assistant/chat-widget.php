<?php
/**
 * SARGODHAMART - Public Customer AI Assistant Floating Chat Widget
 * Floating toggle button & responsive slide-up modal with Urdu/Roman Urdu quick prompts.
 */

if (!class_exists('PublicAIAssistant')) {
    require_once __DIR__ . '/../../services/AI/PublicAIAssistant.php';
}

if (!PublicAIAssistant::isEnabled()) {
    return;
}

$currentUserObj = isLoggedIn() ? currentUser() : null;
?>
<!-- SargodhaMart Public AI Floating Button -->
<div id="sgm-ai-widget-container" class="position-fixed" style="bottom: 24px; right: 24px; z-index: 1060;">
    <!-- Launcher Button -->
    <button id="sgm-ai-launcher" type="button" class="btn btn-dark shadow-lg rounded-pill px-3 py-2 d-flex align-items-center gap-2 border border-secondary"
            style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); transition: all 0.3s ease;">
        <span class="fs-5">🤖</span>
        <span class="fw-bold text-white small">Ask SargodhaMart</span>
        <span class="badge bg-success rounded-circle p-1" style="width: 8px; height: 8px;"></span>
    </button>

    <!-- Chat Card Window (Hidden by Default) -->
    <div id="sgm-ai-chatbox" class="card shadow-lg border border-secondary rounded-4 overflow-hidden d-none mt-2" 
         style="width: 360px; max-width: calc(100vw - 32px); height: 520px; max-height: calc(100vh - 120px); background: #0f172a; color: #fff;">
        
        <!-- Header -->
        <div class="card-header bg-dark border-bottom border-secondary p-3 d-flex justify-content-between align-items-center"
             style="background: #1e293b !important;">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-success-subtle text-success p-2 rounded-circle fs-6 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    🤖
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-white small">SargodhaMart Assistant</h6>
                    <small class="text-success" style="font-size: 0.72rem;"><i class="bi bi-circle-fill me-1" style="font-size: 6px;"></i>Online • 24/7 AI Support</small>
                </div>
            </div>
            <div class="d-flex gap-1">
                <button id="sgm-ai-close" type="button" class="btn btn-sm btn-link text-secondary text-decoration-none p-1">
                    <i class="bi bi-x-lg fs-6"></i>
                </button>
            </div>
        </div>

        <!-- Chat Body / Messages Stream -->
        <div id="sgm-ai-messages" class="card-body p-3 overflow-y-auto small d-flex flex-column gap-3" style="background: #0f172a;">
            <!-- Welcome Bot Message -->
            <div class="d-flex gap-2">
                <div class="bg-dark text-success p-1 rounded-circle fs-6 flex-shrink-0" style="width: 28px; height: 28px; text-align: center;">🤖</div>
                <div class="bg-dark p-3 rounded-3 border border-secondary text-white-50" style="max-width: 85%;">
                    <p class="mb-1 text-white fw-semibold">Assalam-o-Alaikum <?= $currentUserObj ? e($currentUserObj['full_name']) : 'Bhai' ?>! 👋</p>
                    <p class="mb-2">Main SargodhaMart par aapki madad kar sakta hoon. Koi cheez talash karni ho ya ad post karna ho, farmayein:</p>
                    
                    <!-- Quick Pill Prompts -->
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <button class="btn btn-sm btn-outline-secondary text-white rounded-pill px-2 py-1 sgm-quick-prompt" style="font-size: 0.7rem;" data-prompt="Product dhoondein">
                            🔍 Product dhoondein
                        </button>
                        <button class="btn btn-sm btn-outline-secondary text-white rounded-pill px-2 py-1 sgm-quick-prompt" style="font-size: 0.7rem;" data-prompt="Mujhe job chahiye">
                            💼 Job dhoondein
                        </button>
                        <button class="btn btn-sm btn-outline-secondary text-white rounded-pill px-2 py-1 sgm-quick-prompt" style="font-size: 0.7rem;" data-prompt="Ad kaise post karein?">
                            📝 Ad kaise post karein?
                        </button>
                        <button class="btn btn-sm btn-outline-secondary text-white rounded-pill px-2 py-1 sgm-quick-prompt" style="font-size: 0.7rem;" data-prompt="Seller account kaise activate karein?">
                            ⭐ Seller activation (Rs. 1,000)
                        </button>
                        <?php if ($currentUserObj): ?>
                            <button class="btn btn-sm btn-outline-success text-white rounded-pill px-2 py-1 sgm-quick-prompt" style="font-size: 0.7rem;" data-prompt="Mera activation status kya hai?">
                                📊 Mera activation status
                            </button>
                            <button class="btn btn-sm btn-outline-info text-white rounded-pill px-2 py-1 sgm-quick-prompt" style="font-size: 0.7rem;" data-prompt="Meri ads edit karni hain">
                                ✏️ Post edit kaise karein?
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input Footer -->
        <div class="card-footer bg-dark border-top border-secondary p-2" style="background: #1e293b !important;">
            <form id="sgm-ai-form" class="d-flex gap-2">
                <input id="sgm-ai-input" type="text" class="form-control form-control-sm bg-black text-white border-secondary rounded-pill px-3" 
                       placeholder="Apna sawal likhein (Urdu / English)..." autocomplete="off" maxlength="300">
                <button type="submit" class="btn btn-success btn-sm rounded-circle d-flex align-items-center justify-content-center p-2" style="width: 34px; height: 34px;">
                    <i class="bi bi-send-fill" style="font-size: 0.75rem;"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const launcher = document.getElementById('sgm-ai-launcher');
    const chatbox = document.getElementById('sgm-ai-chatbox');
    const closeBtn = document.getElementById('sgm-ai-close');
    const form = document.getElementById('sgm-ai-form');
    const input = document.getElementById('sgm-ai-input');
    const messages = document.getElementById('sgm-ai-messages');

    if (!launcher || !chatbox) return;

    // Toggle Chatbox
    launcher.addEventListener('click', function() {
        chatbox.classList.toggle('d-none');
        if (!chatbox.classList.contains('d-none')) {
            input.focus();
        }
    });

    closeBtn.addEventListener('click', function() {
        chatbox.classList.add('d-none');
    });

    // Quick prompt clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('sgm-quick-prompt')) {
            const promptText = e.target.getAttribute('data-prompt');
            if (promptText) {
                input.value = promptText;
                form.dispatchEvent(new Event('submit'));
            }
        }
    });

    // Handle send message
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        // Append User Message
        appendMessage('user', text);
        input.value = '';

        // Append Typing indicator
        const typingId = 'typing-' + Date.now();
        const typingEl = document.createElement('div');
        typingEl.id = typingId;
        typingEl.className = 'd-flex gap-2 align-items-center text-muted small';
        typingEl.innerHTML = '<span class="spinner-grow spinner-grow-sm text-success"></span> SargodhaMart Assistant typing...';
        messages.appendChild(typingEl);
        messages.scrollTop = messages.scrollHeight;

        try {
            const res = await fetch('/api/public/ai.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: text })
            });
            const data = await res.json();
            typingEl.remove();

            if (data.success) {
                appendMessage('bot', data.message, data.cards || [], data.products || []);
            } else {
                appendMessage('bot', data.error || 'Maazrat, rabta nahi ho saka. Baraye meharbani dobara koshish karein.');
            }
        } catch (err) {
            typingEl.remove();
            appendMessage('bot', 'Network issue. Baraye meharbani page refresh karein.');
        }
    });

    function appendMessage(sender, text, cards = [], products = []) {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'd-flex gap-2 ' + (sender === 'user' ? 'justify-content-end' : '');

        let content = text.replace(/\n/g, '<br>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Render Product Cards if returned
        let productsHtml = '';
        if (products.length > 0) {
            productsHtml = '<div class="d-flex flex-column gap-2 mt-2">';
            products.forEach(p => {
                productsHtml += `
                    <a href="${p.url}" class="card bg-black text-white p-2 border border-secondary text-decoration-none rounded-3 d-flex flex-row align-items-center gap-2">
                        <div class="flex-grow-1">
                            <strong class="d-block text-white" style="font-size:0.75rem;">${p.title}</strong>
                            <span class="text-success fw-bold" style="font-size:0.75rem;">${p.price_formatted}</span> · <span class="text-muted" style="font-size:0.65rem;">${p.city}</span>
                        </div>
                        <i class="bi bi-chevron-right text-secondary small"></i>
                    </a>
                `;
            });
            productsHtml += '</div>';
        }

        // Render Action Buttons
        let cardsHtml = '';
        if (cards.length > 0) {
            cardsHtml = '<div class="d-flex flex-wrap gap-1 mt-2">';
            cards.forEach(c => {
                cardsHtml += `<a href="${c.url}" class="btn btn-xs btn-outline-info text-white rounded-pill px-2 py-1 text-decoration-none" style="font-size: 0.7rem;"><i class="bi ${c.icon || 'bi-link-45deg'} me-1"></i>${c.title}</a>`;
            });
            cardsHtml += '</div>';
        }

        if (sender === 'user') {
            msgDiv.innerHTML = `
                <div class="bg-success text-white p-2 px-3 rounded-3" style="max-width: 80%;">
                    ${content}
                </div>
            `;
        } else {
            msgDiv.innerHTML = `
                <div class="bg-dark text-success p-1 rounded-circle fs-6 flex-shrink-0" style="width: 28px; height: 28px; text-align: center;">🤖</div>
                <div class="bg-dark p-3 rounded-3 border border-secondary text-white" style="max-width: 85%;">
                    ${content}
                    ${productsHtml}
                    ${cardsHtml}
                </div>
            `;
        }

        messages.appendChild(msgDiv);
        messages.scrollTop = messages.scrollHeight;
    }
});
</script>
