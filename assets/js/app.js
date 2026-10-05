/**
 * Sargodha Mandi - Frontend JavaScript
 * Dynamic interactions: Live Chat, Image Preview, Subcategory Fetcher, Favorites
 */

document.addEventListener('DOMContentLoaded', () => {

    // 1. Copy Payment Number Helper
    const copyBtns = document.querySelectorAll('.btn-copy-payment');
    copyBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const textToCopy = btn.getAttribute('data-copy') || '03127453108';
            navigator.clipboard.writeText(textToCopy).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-primary');
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-primary');
                }, 2000);
            });
        });
    });

    // 2. Dynamic Subcategories Fetcher on Post-Ad / Filter pages
    const categorySelect = document.getElementById('category_id');
    const subcategorySelect = document.getElementById('subcategory_id');

    if (categorySelect && subcategorySelect) {
        categorySelect.addEventListener('change', async () => {
            const catId = categorySelect.value;
            subcategorySelect.innerHTML = '<option value="">Loading subcategories...</option>';
            if (!catId) {
                subcategorySelect.innerHTML = '<option value="">Select Category First</option>';
                return;
            }

            try {
                const res = await fetch(`/api/get-subcategories.php?category_id=${encodeURIComponent(catId)}`);
                const data = await res.json();
                if (data.success && data.subcategories.length > 0) {
                    let options = '<option value="">All Subcategories</option>';
                    data.subcategories.forEach(sub => {
                        options += `<option value="${sub.id}">${sub.name}</option>`;
                    });
                    subcategorySelect.innerHTML = options;
                } else {
                    subcategorySelect.innerHTML = '<option value="">No subcategories</option>';
                }
            } catch (err) {
                console.error('Error fetching subcategories', err);
                subcategorySelect.innerHTML = '<option value="">Default</option>';
            }
        });
    }

    // 3. Multi-Image Upload Previewer
    const imageInput = document.getElementById('listing_images_input');
    const previewContainer = document.getElementById('image_preview_grid');

    if (imageInput && previewContainer) {
        imageInput.addEventListener('change', () => {
            previewContainer.innerHTML = '';
            const files = Array.from(imageInput.files);
            const maxImages = parseInt(imageInput.getAttribute('data-max') || '8', 10);

            if (files.length > maxImages) {
                alert(`You can upload maximum ${maxImages} images.`);
                imageInput.value = '';
                return;
            }

            files.forEach((file, idx) => {
                if (!file.type.startsWith('image/')) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const card = document.createElement('div');
                    card.className = 'col-4 col-sm-3 col-md-2 position-relative';
                    card.innerHTML = `
                        <div class="border rounded overflow-hidden" style="aspect-ratio: 1/1;">
                            <img src="${e.target.result}" class="w-100 h-100 object-fit-cover" alt="Upload Preview">
                        </div>
                        ${idx === 0 ? '<span class="badge bg-success position-absolute top-0 start-0 m-1">Main Cover</span>' : ''}
                    `;
                    previewContainer.appendChild(card);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // 4. Product Gallery Thumbnail Clicker
    const mainGalleryImg = document.getElementById('main_gallery_image');
    const thumbItems = document.querySelectorAll('.product-thumb-item');

    if (mainGalleryImg && thumbItems.length > 0) {
        thumbItems.forEach(thumb => {
            thumb.addEventListener('click', () => {
                thumbItems.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
                const newSrc = thumb.getAttribute('data-src');
                if (newSrc) {
                    mainGalleryImg.src = newSrc;
                }
            });
        });
    }

    // 5. AJAX Toggle Favorite
    const favButtons = document.querySelectorAll('.btn-fav-toggle');
    favButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const listingId = btn.getAttribute('data-listing-id');
            if (!listingId) return;

            try {
                const res = await fetch('/api/favorite.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ listing_id: listingId })
                });
                const data = await res.json();
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                if (data.success) {
                    const icon = btn.querySelector('i');
                    if (data.is_favorite) {
                        btn.classList.add('btn-danger', 'text-white');
                        btn.classList.remove('btn-outline-danger');
                        if (icon) icon.className = 'bi bi-heart-fill';
                    } else {
                        btn.classList.remove('btn-danger', 'text-white');
                        btn.classList.add('btn-outline-danger');
                        if (icon) icon.className = 'bi bi-heart';
                    }
                }
            } catch (err) {
                console.error('Error toggling favorite', err);
            }
        });
    });

    // 6. Live Chat Poller (if chat messages container is active)
    const chatContainer = document.getElementById('chat_messages_box');
    const chatForm = document.getElementById('chat_send_form');
    const convIdInput = document.getElementById('conversation_id');

    if (chatContainer && convIdInput) {
        const convId = convIdInput.value;

        // Auto scroll to bottom
        chatContainer.scrollTop = chatContainer.scrollHeight;

        // Send message via AJAX
        if (chatForm) {
            chatForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const msgInput = document.getElementById('chat_message_input');
                const text = msgInput.value.trim();
                if (!text) return;

                const submitBtn = chatForm.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                try {
                    const res = await fetch('/api/chat.php?action=send', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ conversation_id: convId, message: text })
                    });
                    const data = await res.json();
                    if (data.success) {
                        msgInput.value = '';
                        pollChatMessages();
                    } else {
                        alert(data.error || 'Failed to send message.');
                    }
                } catch (err) {
                    console.error('Send error', err);
                } finally {
                    if (submitBtn) submitBtn.disabled = false;
                }
            });
        }

        // Periodic Poll
        async function pollChatMessages() {
            try {
                const res = await fetch(`/api/chat.php?action=fetch&conversation_id=${encodeURIComponent(convId)}`);
                const data = await res.json();
                if (data.success && data.messages) {
                    chatContainer.innerHTML = data.messages.map(msg => `
                        <div class="chat-bubble ${msg.is_mine ? 'mine' : 'other'}">
                            <div>${escapeHtml(msg.message)}</div>
                            <div class="chat-time">${msg.time_ago}</div>
                        </div>
                    `).join('');
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            } catch (err) {
                // Background poll silent
            }
        }

        setInterval(pollChatMessages, 3500);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
});
