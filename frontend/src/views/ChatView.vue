<template>
  <div class="storefront chat-page">
    <header class="store-header">
      <div class="page-shell store-header-inner">
        <RouterLink class="brand" to="/" :aria-label="t('home')">
          <span class="brand-mark">M</span>
          <span>microstore</span>
        </RouterLink>
        <nav class="store-nav" :aria-label="t('chat')">
          <LanguageSwitcher />
          <ThemeToggle />
          <RouterLink to="/">{{ t('storefront') }}</RouterLink>
          <RouterLink to="/profile">{{ t('myProfile') }}</RouterLink>
        </nav>
      </div>
    </header>

    <main class="page-shell chat-shell">
      <header class="chat-heading">
        <div>
          <p class="eyebrow">microstore · community</p>
          <h1>{{ t('chat') }}</h1>
          <p>{{ t('chatSubtitle') }}</p>
        </div>
        <span class="chat-status" :class="`is-${connectionState}`" role="status">
          <i aria-hidden="true"></i>{{ connectionLabel }}
        </span>
      </header>

      <section class="chat-card" :aria-label="t('chat')">
        <div class="chat-card-heading">
          <div class="chat-room-icon" aria-hidden="true">✦</div>
          <div>
            <h2>{{ t('chatWelcome') }}</h2>
            <p>{{ t('chatWelcomeText') }}</p>
          </div>
        </div>

        <div ref="messageList" class="chat-messages" role="log" aria-live="polite" :aria-label="t('chat')" @scroll="updateAtBottom">
          <p v-if="loadingMessages" class="chat-state">{{ t('loading') }}</p>
          <div v-else-if="messages.length === 0" class="chat-empty">
            <span class="chat-empty-mark" aria-hidden="true">✧</span>
            <p>{{ t('chatEmpty') }}</p>
          </div>
          <article
            v-for="message in messages"
            :key="message.id"
            class="chat-message"
            :class="{ 'is-own': message.user.id === authStore.user?.id }"
          >
            <div class="chat-avatar" aria-hidden="true">{{ initials(message.user.name) }}</div>
            <div class="chat-message-content">
              <div class="chat-message-meta">
                <strong>{{ message.user.name }}</strong>
                <time :datetime="message.created_at">{{ formatTime(message.created_at) }}</time>
              </div>
              <p>{{ message.body }}</p>
            </div>
          </article>
          <button v-if="showNewMessages" class="chat-jump-button" type="button" @click="scrollToLatest(true)">
            {{ t('chat') }} ↓
          </button>
        </div>

        <p v-if="loadError || sendError" class="chat-error" role="alert">
          {{ loadError || sendError }}
          <button v-if="loadError" class="chat-retry" type="button" @click="loadMessages">{{ t('chatRetry') }}</button>
        </p>

        <form class="chat-composer" @submit.prevent="sendMessage">
          <label class="sr-only" for="chat-message">{{ t('chatPlaceholder') }}</label>
          <textarea
            id="chat-message"
            v-model="draft"
            :placeholder="t('chatPlaceholder')"
            maxlength="2000"
            rows="1"
            @keydown.enter.exact.prevent="sendMessage"
          ></textarea>
          <div class="chat-composer-footer">
            <span class="chat-char-count" :class="{ 'is-limit': draft.length >= 1900 }">{{ draft.length }} / 2000</span>
            <button class="button chat-send" type="submit" :disabled="sending || !draft.trim()">
              <span>{{ sending ? t('chatSending') : t('chatSend') }}</span>
              <span aria-hidden="true">↗</span>
            </button>
          </div>
        </form>
      </section>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'
import ThemeToggle from '@/components/ThemeToggle.vue'
import { localeTag, t } from '@/i18n'
import echo, { setEchoAuthToken } from '@/realtime/echo'
import { useAuthStore } from '@/stores/auth'

interface ChatMessage {
  id: number
  body: string
  created_at: string
  user: { id: number; name: string }
}

const authStore = useAuthStore()
const messages = ref<ChatMessage[]>([])
const loadingMessages = ref(true)
const sending = ref(false)
const draft = ref('')
const loadError = ref('')
const sendError = ref('')
const messageList = ref<HTMLElement | null>(null)
const showNewMessages = ref(false)
const connectionState = ref(echo.connector.connectionStatus())
const pendingMessages: ChatMessage[] = []
let unsubscribeConnection: (() => void) | undefined

const connectionLabel = computed(() => connectionState.value === 'connected'
  ? t('chatLive')
  : connectionState.value === 'connecting' ? t('chatConnecting') : t('chatOffline'))

function headers() {
  return {
    Accept: 'application/json',
    Authorization: `Bearer ${authStore.token}`,
    'Content-Type': 'application/json',
  }
}

function appendMessage(message: ChatMessage, forceScroll = false) {
  if (messages.value.some((item) => item.id === message.id)) return
  const list = messageList.value
  const wasAtBottom = !list || list.scrollHeight - list.scrollTop - list.clientHeight < 100
  messages.value.push(message)
  if (forceScroll || wasAtBottom) void scrollToLatest(true)
  else showNewMessages.value = true
}

async function loadMessages() {
  loadingMessages.value = true
  loadError.value = ''
  try {
    const response = await fetch('http://localhost:8005/api/chat/messages', { headers: headers() })
    const result = await response.json()
    if (!response.ok) throw new Error(result.message || t('chatLoadError'))
    messages.value = result.messages as ChatMessage[]
    pendingMessages.splice(0).forEach((message) => appendMessage(message))
    await scrollToLatest(true)
  } catch {
    loadError.value = t('chatLoadError')
  } finally {
    loadingMessages.value = false
  }
}

function receiveMessage(payload: { message: ChatMessage }) {
  if (loadingMessages.value) pendingMessages.push(payload.message)
  else appendMessage(payload.message)
}

async function sendMessage() {
  const body = draft.value.trim()
  if (!body || sending.value) return

  sending.value = true
  sendError.value = ''
  try {
    const response = await fetch('http://localhost:8005/api/chat/messages', {
      method: 'POST',
      headers: headers(),
      body: JSON.stringify({ body }),
    })
    const result = await response.json()
    if (!response.ok) throw new Error(result.message || t('chatSendError'))
    draft.value = ''
    appendMessage(result.message as ChatMessage, true)
  } catch {
    sendError.value = t('chatSendError')
  } finally {
    sending.value = false
  }
}

async function scrollToLatest(force = false) {
  await nextTick()
  if (messageList.value) messageList.value.scrollTop = messageList.value.scrollHeight
  if (force) showNewMessages.value = false
}

function updateAtBottom() {
  const list = messageList.value
  if (list && list.scrollHeight - list.scrollTop - list.clientHeight < 40) showNewMessages.value = false
}

function initials(name: string) {
  return name.trim().split(/\s+/).slice(0, 2).map((part) => part[0]).join('').toUpperCase()
}

function formatTime(value: string) {
  return new Intl.DateTimeFormat(localeTag.value, { hour: '2-digit', minute: '2-digit' }).format(new Date(value))
}

onMounted(() => {
  setEchoAuthToken(authStore.token)
  unsubscribeConnection = echo.connector.onConnectionChange((status) => { connectionState.value = status })
  echo.private('chat.global').listen('.chat.message.sent', receiveMessage)
  void loadMessages()
})

onBeforeUnmount(() => {
  unsubscribeConnection?.()
  echo.leave('chat.global')
})
</script>

<style scoped>
.chat-shell { max-width: 900px; padding-top: 38px; padding-bottom: 64px; }
.chat-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 24px; }
.chat-heading h1 { margin: 0; font-size: clamp(30px, 5vw, 42px); line-height: 1.1; letter-spacing: -.045em; }
.chat-heading > div > p:last-child { margin: 8px 0 0; color: var(--muted); }
.chat-status { display: inline-flex; align-items: center; gap: 8px; flex: 0 0 auto; padding: 8px 12px; border: 1px solid var(--line); border-radius: 999px; background: var(--surface); color: var(--text-medium); font-size: 12px; font-weight: 650; }
.chat-status i { width: 8px; height: 8px; border-radius: 50%; background: #d19a46; }
.chat-status.is-connected i { background: #2ba77c; }
.chat-status.is-disconnected i, .chat-status.is-failed i { background: #d36b75; }
.chat-card { overflow: hidden; border: 1px solid var(--line); border-radius: 20px; background: var(--surface); box-shadow: var(--shadow); }
.chat-card-heading { display: flex; align-items: center; gap: 13px; padding: 20px 24px; border-bottom: 1px solid var(--line-soft); }
.chat-room-icon { display: grid; width: 42px; height: 42px; flex: 0 0 auto; place-items: center; border-radius: 14px; background: var(--brand-soft); color: var(--brand); font-size: 20px; }
.chat-card-heading h2 { margin: 0; font-size: 15px; }
.chat-card-heading p { margin: 3px 0 0; color: var(--muted); font-size: 12px; }
.chat-messages { position: relative; display: flex; min-height: 320px; max-height: min(54vh, 600px); flex-direction: column; gap: 15px; overflow-y: auto; padding: 22px 24px; scroll-behavior: auto; }
.chat-state { margin: auto; color: var(--muted); font-size: 13px; }
.chat-empty { display: grid; min-height: 260px; align-content: center; justify-items: center; gap: 10px; color: var(--muted); text-align: center; }
.chat-empty p { margin: 0; }
.chat-empty-mark { display: grid; width: 46px; height: 46px; place-items: center; border-radius: 16px; background: var(--brand-soft); color: var(--brand); font-size: 23px; }
.chat-message { display: flex; max-width: 84%; align-items: flex-start; gap: 10px; align-self: flex-start; }
.chat-message.is-own { flex-direction: row-reverse; align-self: flex-end; }
.chat-avatar { display: grid; width: 34px; height: 34px; flex: 0 0 auto; place-items: center; border: 1px solid var(--line); border-radius: 12px; background: var(--surface-soft); color: var(--text-medium); font-size: 11px; font-weight: 750; }
.chat-message.is-own .chat-avatar { border-color: var(--brand-outline); background: var(--brand-soft); color: var(--brand); }
.chat-message-content { min-width: 0; padding: 10px 13px; border: 1px solid var(--line-soft); border-radius: 4px 15px 15px; background: var(--surface-soft); }
.chat-message.is-own .chat-message-content { border-color: var(--brand-soft); border-radius: 15px 4px 15px 15px; background: var(--brand-soft); }
.chat-message-meta { display: flex; align-items: baseline; gap: 9px; }
.chat-message-meta strong { overflow: hidden; color: var(--ink); font-size: 11px; text-overflow: ellipsis; white-space: nowrap; }
.chat-message-meta time { flex: 0 0 auto; color: var(--text-faint); font-size: 10px; }
.chat-message-content > p { margin: 4px 0 0; color: var(--text-soft); font-size: 13px; line-height: 1.55; overflow-wrap: anywhere; white-space: pre-wrap; }
.chat-jump-button { position: sticky; bottom: 0; align-self: center; padding: 7px 12px; border: 1px solid var(--brand-outline); border-radius: 999px; background: var(--surface); color: var(--brand); font-size: 11px; font-weight: 700; cursor: pointer; }
.chat-error { display: flex; align-items: center; gap: 12px; margin: 0; padding: 11px 24px; border-top: 1px solid var(--danger-line); background: var(--danger-soft); color: var(--danger); font-size: 12px; }
.chat-retry { padding: 0; border: 0; background: transparent; color: inherit; font-weight: 750; text-decoration: underline; cursor: pointer; }
.chat-composer { padding: 14px 18px 16px; border-top: 1px solid var(--line-soft); background: var(--surface); }
.chat-composer textarea { display: block; width: 100%; min-height: 52px; max-height: 150px; resize: vertical; padding: 12px 13px; border: 1px solid var(--input-border); border-radius: 13px; outline: none; background: var(--surface); color: var(--ink); font: inherit; font-size: 13px; line-height: 1.5; }
.chat-composer textarea:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(49, 92, 245, .12); }
.chat-composer textarea::placeholder { color: var(--text-faint); }
.chat-composer-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 1px 0; }
.chat-char-count { color: var(--text-faint); font-size: 10px; }
.chat-char-count.is-limit { color: var(--warning); }
.chat-send { gap: 10px; min-width: 116px; }
.chat-send:disabled { cursor: default; opacity: .55; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
@media (max-width: 620px) {
  .chat-shell { padding-top: 25px; }
  .chat-heading { align-items: flex-start; flex-direction: column; gap: 13px; }
  .chat-card { border-radius: 16px; }
  .chat-card-heading { padding: 16px; }
  .chat-messages { min-height: 300px; max-height: 54vh; padding: 16px 13px; }
  .chat-message { max-width: 94%; }
  .chat-composer { padding: 12px; }
  .chat-error { align-items: flex-start; flex-direction: column; padding-inline: 16px; }
}
</style>
