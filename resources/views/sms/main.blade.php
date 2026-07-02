@extends('layouts.dashboard-shell')

@section('page-title', 'SMS Workspace')

@section('content')
    <div class="space-y-6 lg:space-y-8">
        <section class="bp-card value_span8">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <div>
                    <p class="bp-section-kicker">SMS Chat</p>
                    <h2 class="bp-section-title value_span9">Conversation workspace</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-slate-500">
                        Review incoming conversations, move them between queues, and reply from the same operational workspace.
                    </p>
                </div>

                <span class="bp-status-pill" data-sms-status>Loading</span>
            </div>
        </section>

        <section
            id="smsWorkspace"
            class="bp-sms-workspace"
            data-user-id="{{ $userId }}"
            data-csrf-token="{{ csrf_token() }}"
        >
            <div class="bp-card bp-sms-panel value_span8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Inbox</p>
                        <h3 class="bp-section-title value_span9">Conversations</h3>
                    </div>

                    <div class="bp-sms-tabs" role="tablist" aria-label="Conversation groups">
                        <button type="button" class="bp-sms-tab is-active" data-group="open">Open</button>
                        <button type="button" class="bp-sms-tab" data-group="sold">Sold</button>
                        <button type="button" class="bp-sms-tab" data-group="ignore">Ignore</button>
                    </div>
                </div>

                <div class="mt-6 bp-sms-list" data-conversation-list>
                    <div class="bp-sms-empty">Loading conversations...</div>
                </div>
            </div>

            <div class="bp-card bp-sms-panel value_span8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="bp-section-kicker">Thread</p>
                        <h3 class="bp-section-title value_span9" data-thread-title>Select a conversation</h3>
                        <p class="mt-2 bp-table-meta" data-thread-meta>Choose an open conversation to load messages.</p>
                    </div>

                    <button type="button" class="bp-button-secondary" data-refresh-conversations>Refresh</button>
                </div>

                <div class="mt-6 bp-sms-messages" data-message-list>
                    <div class="bp-sms-empty">No conversation selected.</div>
                </div>

                <form class="mt-6 bp-sms-composer" data-message-form enctype="multipart/form-data">
                    <label class="bp-form-label" for="smsMessage">Reply</label>
                    <textarea
                        id="smsMessage"
                        class="bp-form-input bp-form-textarea"
                        name="message"
                        rows="4"
                        placeholder="Write a reply..."
                        disabled
                        data-message-input
                    ></textarea>

                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div class="bp-form-field md:max-w-sm">
                            <label class="bp-form-label" for="smsImage">Image</label>
                            <input id="smsImage" class="bp-form-input" type="file" name="image" data-image-input disabled>
                        </div>

                        <button type="submit" class="bp-button-primary" disabled data-send-button>Send reply</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@section('footer')
    <script>
        (() => {
            const workspace = document.querySelector('[data-user-id]');

            if (!workspace) {
                return;
            }

            const statusEl = document.querySelector('[data-sms-status]');
            const listEl = workspace.querySelector('[data-conversation-list]');
            const messageListEl = workspace.querySelector('[data-message-list]');
            const threadTitleEl = workspace.querySelector('[data-thread-title]');
            const threadMetaEl = workspace.querySelector('[data-thread-meta]');
            const messageForm = workspace.querySelector('[data-message-form]');
            const messageInput = workspace.querySelector('[data-message-input]');
            const imageInput = workspace.querySelector('[data-image-input]');
            const sendButton = workspace.querySelector('[data-send-button]');
            const refreshButton = workspace.querySelector('[data-refresh-conversations]');
            const tabs = Array.from(workspace.querySelectorAll('[data-group]'));
            const csrfToken = workspace.getAttribute('data-csrf-token');
            const adminSuffix = window.location.search.includes('adminLogin') ? '?adminLogin=1' : '';
            const groups = ['open', 'sold', 'ignore'];

            let conversations = [];
            let currentGroup = 'open';
            let activeConversation = null;
            let refreshTimer = null;

            const setStatus = (label, active = false) => {
                statusEl.textContent = label;
                statusEl.classList.toggle('bp-status-pill-active', active);
            };

            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',"'": '&#039;',
            }[char]));

            const formatPhone = (phone) => {
                const digits = String(phone ?? '').replace(/[^\d]/g, '');

                if (digits.length === 11) {
                    return digits.replace(/(\d{1})(\d{3})(\d{3})(\d{4})/, '+$1 ($2) $3-$4');
                }

                return phone || 'Unknown number';
            };

            const relativeTime = (timestamp) => {
                if (!timestamp) {
                    return 'No messages yet';
                }

                const normalized = String(timestamp).replace(' ', 'T');
                const date = new Date(normalized);

                if (Number.isNaN(date.getTime())) {
                    return timestamp;
                }

                const seconds = Math.round((date.getTime() - Date.now()) / 1000);
                const units = [
                    ['year', 31536000],
                    ['month', 2592000],
                    ['week', 604800],
                    ['day', 86400],
                    ['hour', 3600],
                    ['minute', 60],
                ];

                for (const [unit, amount] of units) {
                    const value = Math.trunc(seconds / amount);

                    if (Math.abs(value) >= 1) {
                        return new Intl.RelativeTimeFormat('en', { numeric: 'auto' }).format(value, unit);
                    }
                }

                return 'just now';
            };

            const request = async (url, options = {}) => {
                const response = await fetch(url + adminSuffix, {
                    ...options,
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        ...(options.headers || {}),
                    },
                });

                const text = await response.text();
                let data = text;

                try {
                    data = text ? JSON.parse(text) : null;
                } catch (error) {
                    data = text;
                }

                if (!response.ok) {
                    throw new Error(typeof data === 'string' && data ? data : 'Request failed');
                }

                return data;
            };

            const sortedVisibleConversations = () => conversations
                .filter((conversation) => (conversation.group || 'open') === currentGroup)
                .sort((a, b) => new Date(String(b.last_message_timestamp || '').replace(' ', 'T')) - new Date(String(a.last_message_timestamp || '').replace(' ', 'T')));

            const renderTabs = () => {
                tabs.forEach((tab) => {
                    tab.classList.toggle('is-active', tab.getAttribute('data-group') === currentGroup);
                });
            };

            const renderConversations = () => {
                renderTabs();

                const visible = sortedVisibleConversations();

                if (!visible.length) {
                    listEl.innerHTML = `<div class="bp-sms-empty">No ${escapeHtml(currentGroup)} conversations.</div>`;
                    return;
                }

                listEl.innerHTML = visible.map((conversation) => {
                    const isActive = activeConversation && activeConversation.conversation_id === conversation.conversation_id;
                    const unread = Number(conversation.unread_messages || 0);
                    const actions = groups
                        .filter((group) => group !== currentGroup)
                        .map((group) => `<button type="button" class="bp-sms-chip" data-move-group="${group}" data-id="${escapeHtml(conversation.id)}">${group}</button>`)
                        .join('');

                    return `
                        <article class="bp-sms-conversation ${isActive ? 'is-active' : ''}" data-conversation-id="${escapeHtml(conversation.conversation_id)}">
                            <button type="button" class="bp-sms-conversation-main" data-open-conversation="${escapeHtml(conversation.conversation_id)}">
                                <span class="bp-sms-number">${escapeHtml(formatPhone(conversation.recipient_phone_number))}</span>
                                <span class="bp-sms-time">Last message ${escapeHtml(relativeTime(conversation.last_message_timestamp))}</span>
                                ${unread > 0 ? `<span class="bp-sms-unread">${unread} new</span>` : ''}
                            </button>
                            <div class="bp-sms-actions">
                                ${actions}
                                <button type="button" class="bp-sms-chip bp-sms-chip-primary" data-open-conversation="${escapeHtml(conversation.conversation_id)}">View</button>
                            </div>
                        </article>
                    `;
                }).join('');
            };

            const renderMessages = (messages) => {
                if (!messages.length) {
                    messageListEl.innerHTML = '<div class="bp-sms-empty">No messages in this conversation yet.</div>';
                    return;
                }

                messageListEl.innerHTML = messages.map((message) => {
                    const fromService = activeConversation && String(message.sending_phone_number_id) === String(activeConversation.service_phone_id);
                    const sender = fromService ? 'Me' : formatPhone(message.sendingNumber);
                    const media = message.mediaUrl
                        ? `<a class="bp-sms-media" href="${escapeHtml(message.mediaUrl)}" target="_blank" rel="noopener"><img src="${escapeHtml(message.mediaUrl)}" alt="Attached media"></a>`
                        : '';

                    return `
                        <article class="bp-sms-message ${fromService ? 'is-outbound' : 'is-inbound'}">
                            <div class="bp-sms-message-heading">
                                <span>${escapeHtml(sender)}</span>
                                <time>${escapeHtml(message.created_at || '')}</time>
                            </div>
                            <p>${escapeHtml(message.message || '')}</p>
                            ${media}
                        </article>
                    `;
                }).join('');

                messageListEl.scrollTop = messageListEl.scrollHeight;
            };

            const setComposerEnabled = (enabled) => {
                messageInput.disabled = !enabled;
                imageInput.disabled = !enabled;
                sendButton.disabled = !enabled;
            };

            const loadMessages = async (conversationId) => {
                const conversation = conversations.find((item) => String(item.conversation_id) === String(conversationId));

                if (!conversation) {
                    return;
                }

                activeConversation = conversation;
                threadTitleEl.textContent = formatPhone(conversation.recipient_phone_number);
                threadMetaEl.textContent = `Group: ${conversation.group || 'open'} | Last message ${relativeTime(conversation.last_message_timestamp)}`;
                setComposerEnabled(true);
                renderConversations();
                messageListEl.innerHTML = '<div class="bp-sms-empty">Loading messages...</div>';

                try {
                    const [conversationDetails, messages] = await Promise.all([
                        request(`/sms/api/conversations/${conversationId}`),
                        request(`/sms/api/conversations/${conversationId}/messages`),
                    ]);

                    activeConversation = { ...conversation, ...conversationDetails };
                    await request(`/sms/api/conversations/${conversationId}/read-new-messages`, { method: 'PATCH' });
                    conversation.unread_messages = 0;
                    renderConversations();
                    renderMessages(Array.isArray(messages) ? messages : []);
                } catch (error) {
                    messageListEl.innerHTML = `<div class="bp-sms-empty">Could not load messages. ${escapeHtml(error.message)}</div>`;
                }
            };

            const loadConversations = async ({ quiet = false } = {}) => {
                if (!quiet) {
                    setStatus('Loading');
                }

                try {
                    const data = await request('/sms/api/conversations');
                    conversations = Array.isArray(data) ? data : [];

                    if (activeConversation) {
                        const updated = conversations.find((item) => item.conversation_id === activeConversation.conversation_id);
                        activeConversation = updated ? { ...activeConversation, ...updated } : activeConversation;
                    }

                    renderConversations();
                    setStatus('Connected', true);
                } catch (error) {
                    listEl.innerHTML = `<div class="bp-sms-empty">Could not load conversations. ${escapeHtml(error.message)}</div>`;
                    setStatus('Offline');
                }
            };

            listEl.addEventListener('click', async (event) => {
                const openButton = event.target.closest('[data-open-conversation]');
                const moveButton = event.target.closest('[data-move-group]');

                if (openButton) {
                    await loadMessages(openButton.getAttribute('data-open-conversation'));
                    return;
                }

                if (moveButton) {
                    const id = moveButton.getAttribute('data-id');
                    const group = moveButton.getAttribute('data-move-group');

                    try {
                        await request('/sms/api/conversations', {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id, group }),
                        });

                        conversations = conversations.map((conversation) => (
                            String(conversation.id) === String(id) ? { ...conversation, group } : conversation
                        ));
                        renderConversations();
                    } catch (error) {
                        setStatus('Update failed');
                    }
                }
            });

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    currentGroup = tab.getAttribute('data-group');
                    renderConversations();
                });
            });

            refreshButton.addEventListener('click', () => loadConversations());

            messageInput.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    messageForm.requestSubmit();
                }
            });

            messageForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (!activeConversation) {
                    return;
                }

                const message = messageInput.value.trim();

                if (!message && !imageInput.files.length) {
                    return;
                }

                const formData = new FormData();
                formData.append('fromPhoneNumberId', activeConversation.service_phone_id);
                formData.append('toPhoneNumberId', activeConversation.recipient_phone_id);
                formData.append('message', message);

                if (imageInput.files[0]) {
                    formData.append('image', imageInput.files[0]);
                }

                sendButton.disabled = true;
                setStatus('Sending');

                try {
                    await request('/sms/api/messages/send', {
                        method: 'POST',
                        body: formData,
                    });

                    messageInput.value = '';
                    imageInput.value = '';
                    await loadMessages(activeConversation.conversation_id);
                    setStatus('Connected', true);
                } catch (error) {
                    setStatus('Send failed');
                } finally {
                    sendButton.disabled = false;
                }
            });

            loadConversations();
            refreshTimer = setInterval(() => loadConversations({ quiet: true }), 10000);
            window.addEventListener('beforeunload', () => clearInterval(refreshTimer));
        })();
    </script>
@endsection
