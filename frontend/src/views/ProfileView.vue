<template>
  <div class="storefront">
    <header class="store-header">
      <div class="page-shell store-header-inner">
        <RouterLink class="brand" to="/" :aria-label="t('home')">
          <span class="brand-mark">M</span>
          <span>microstore</span>
        </RouterLink>
        <nav class="store-nav" :aria-label="t('profile')">
          <LanguageSwitcher />
          <ThemeToggle />
          <RouterLink to="/chat">{{ t('chat') }}</RouterLink>
          <RouterLink to="/">{{ t('storefront') }}</RouterLink>
          <button class="nav-signout" type="button" @click="logout">{{ t('logout') }}</button>
        </nav>
      </div>
    </header>

    <main class="page-shell page-content profile-page">
      <header class="page-heading">
        <div>
          <p class="eyebrow">{{ t('account') }}</p>
          <h1>{{ t('myProfile') }}</h1>
          <p>{{ t('profileSettings') }}</p>
        </div>
      </header>

      <div v-if="loading" class="status-state">{{ t('loadingProfile') }}</div>
      <div v-else-if="loadError" class="status-state is-error" role="alert">
        {{ loadError }}
        <button class="button button-secondary" type="button" @click="loadProfile">{{ t('retry') }}</button>
      </div>

      <div v-else class="profile-layout">
        <aside class="profile-summary panel">
          <div class="profile-avatar" aria-hidden="true">{{ avatarInitial }}</div>
          <h2>{{ authStore.user?.name || t('user') }}</h2>
          <p class="profile-email">{{ authStore.user?.email }}</p>
          <span class="profile-status"><i></i> {{ t('accountActive') }}</span>

          <div class="profile-summary-divider"></div>
          <p class="profile-detail-label">{{ t('memberSince') }}</p>
          <p class="profile-detail-value">{{ memberSince }}</p>
          <RouterLink class="profile-summary-link" to="/admin/products">{{ t('goToProducts') }} <span>↗</span></RouterLink>
        </aside>

        <div class="profile-settings">
          <section class="profile-panel panel">
            <div class="profile-panel-heading">
              <div class="profile-panel-icon" aria-hidden="true">◎</div>
              <div>
                <h2>{{ t('personalDetails') }}</h2>
                <p>{{ t('personalDetailsText') }}</p>
              </div>
            </div>

            <form class="profile-form" @submit.prevent="saveProfile">
              <div class="field">
                <label for="profile-name">{{ t('name') }}</label>
                <input id="profile-name" v-model.trim="profile.name" type="text" autocomplete="name" maxlength="255" required />
              </div>
              <div class="field">
                <label for="profile-email">{{ t('email') }}</label>
                <input id="profile-email" v-model.trim="profile.email" type="email" autocomplete="email" maxlength="255" required />
              </div>
              <div class="profile-form-footer">
                <p v-if="profileMessage" class="profile-message" :class="`is-${profileMessageType}`" role="status">{{ profileMessage }}</p>
                <button class="button" type="submit" :disabled="savingProfile">{{ savingProfile ? t('saving') : t('saveChanges') }}</button>
              </div>
            </form>
          </section>

          <section class="profile-panel panel">
            <div class="profile-panel-heading">
              <div class="profile-panel-icon profile-panel-icon-lock" aria-hidden="true">⌑</div>
              <div>
                <h2>{{ t('changingPassword') }}</h2>
                <p>{{ t('passwordSecurity') }}</p>
              </div>
            </div>

            <form class="profile-form" @submit.prevent="savePassword">
              <div class="field">
                <label for="current-password">{{ t('currentPassword') }}</label>
                <input id="current-password" v-model="passwordForm.current_password" type="password" autocomplete="current-password" required />
              </div>
              <div class="profile-password-row">
                <div class="field">
                  <label for="new-password">{{ t('newPassword') }}</label>
                  <input id="new-password" v-model="passwordForm.password" type="password" autocomplete="new-password" minlength="8" required />
                  <small>{{ t('passwordMin8') }}</small>
                </div>
                <div class="field">
                  <label for="confirm-password">{{ t('repeatNewPassword') }}</label>
                  <input id="confirm-password" v-model="passwordForm.password_confirmation" type="password" autocomplete="new-password" minlength="8" required />
                </div>
              </div>
              <div class="profile-form-footer">
                <p v-if="passwordMessage" class="profile-message" :class="`is-${passwordMessageType}`" role="status">{{ passwordMessage }}</p>
                <button class="button button-secondary" type="submit" :disabled="savingPassword">{{ savingPassword ? t('updating') : t('updatePassword') }}</button>
              </div>
            </form>
          </section>

          <section class="profile-panel panel">
            <div class="profile-panel-heading profile-sessions-heading">
              <div class="profile-panel-icon" aria-hidden="true">⌁</div>
              <div>
                <h2>{{ t('activeSessions') }}</h2>
                <p>{{ t('sessionsText') }}</p>
              </div>
              <button
                v-if="sessions.some((session) => !session.is_current)"
                class="button button-secondary sessions-revoke-all"
                type="button"
                :disabled="revokingAll || loadingSessions"
                @click="requestRevokeOthers"
              >{{ revokingAll ? t('ending') : t('endOthers') }}</button>
            </div>

            <p v-if="sessionsMessage" class="profile-message" :class="`is-${sessionsMessageType}`" role="status">{{ sessionsMessage }}</p>
            <div v-if="loadingSessions" class="sessions-empty">{{ t('loadingSessions') }}</div>
            <div v-else-if="sessionsError" class="sessions-empty is-error" role="alert">
              {{ sessionsError }}
              <button class="button button-secondary" type="button" @click="loadSessions">{{ t('retry') }}</button>
            </div>
            <div v-else-if="sessions.length" class="sessions-list">
              <article v-for="session in sessions" :key="session.id" class="session-item">
                <div class="session-device-icon" aria-hidden="true">{{ session.is_current ? '●' : '◉' }}</div>
                <div class="session-details">
                  <div class="session-title-row">
                    <h3>{{ sessionLabel(session.name) }}</h3>
                    <span v-if="session.is_current" class="session-current-badge">{{ t('thisDevice') }}</span>
                  </div>
                  <p>{{ t('signedInAt') }} {{ formatSessionDate(session.created_at) }}</p>
                  <p v-if="session.expires_at">{{ t('expiresAt') }} {{ formatSessionDate(session.expires_at) }}</p>
                </div>
                <button
                  class="session-revoke"
                  type="button"
                  :disabled="revokingSessionId === session.id || revokingAll"
                  :aria-label="t('currentSessionAria', { name: session.name })"
                  @click="requestRevokeSession(session)"
                >{{ revokingSessionId === session.id ? t('ending') : t('endSession') }}</button>
              </article>
            </div>
            <div v-else class="sessions-empty">{{ t('noSessions') }}</div>
          </section>
        </div>
      </div>
    </main>
    <Teleport to="body">
        <div v-if="confirmationAction" class="session-modal-backdrop" tabindex="-1" autofocus @click.self="closeConfirmation" @keydown.esc.prevent="closeConfirmation">
          <section
            class="session-confirm-modal panel"
            role="dialog"
            aria-modal="true"
            aria-labelledby="session-modal-title"
            aria-describedby="session-modal-description"
            tabindex="-1"
          >
            <div class="session-modal-icon" aria-hidden="true">!</div>
            <h2 id="session-modal-title">{{ confirmationTitle }}</h2>
            <p id="session-modal-description">{{ confirmationDescription }}</p>
            <div class="session-modal-actions">
              <button class="button button-secondary" type="button" :disabled="confirmationBusy" @click="closeConfirmation">{{ t('cancel') }}</button>
              <button class="button session-modal-danger" type="button" :disabled="confirmationBusy" @click="confirmSessionAction">
                {{ confirmationBusy ? t('ending') : confirmationButtonLabel }}
              </button>
            </div>
          </section>
        </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { t } from '@/i18n'
import { computed, onMounted, reactive, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import ThemeToggle from '@/components/ThemeToggle.vue'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'
import { useAuthStore, type ActiveSession } from '@/stores/auth.ts'
import { localeTag } from '@/i18n'

const authStore = useAuthStore()
const router = useRouter()
const loading = ref(true)
const loadError = ref('')
const savingProfile = ref(false)
const savingPassword = ref(false)
const profileMessage = ref('')
const passwordMessage = ref('')
const profileMessageType = ref<'success' | 'error'>('success')
const passwordMessageType = ref<'success' | 'error'>('success')
const sessions = ref<ActiveSession[]>([])
const loadingSessions = ref(false)
const sessionsError = ref('')
const sessionsMessage = ref('')
const sessionsMessageType = ref<'success' | 'error'>('success')
const revokingSessionId = ref<string | null>(null)
const revokingAll = ref(false)
const confirmationAction = ref<{ type: 'session'; session: ActiveSession } | { type: 'others' } | null>(null)

const profile = reactive({ name: '', email: '' })
const passwordForm = reactive({ current_password: '', password: '', password_confirmation: '' })

const avatarInitial = computed(() => (authStore.user?.name || authStore.user?.email || 'U').trim().charAt(0).toUpperCase())
const memberSince = computed(() => {
  const date = authStore.user?.created_at
  if (!date) return t('recently')
  return new Intl.DateTimeFormat(localeTag.value, { month: 'long', year: 'numeric' }).format(new Date(date))
})

function errorText(error: unknown, fallback: string): string {
  if (typeof error === 'string') return error
  if (error && typeof error === 'object') {
    const data = error as { message?: string; errors?: Record<string, string[]> }
    const firstFieldError = Object.values(data.errors ?? {})[0]?.[0]
    return firstFieldError || data.message || fallback
  }
  return fallback
}

async function loadProfile() {
  loading.value = true
  loadError.value = ''
  try {
    const user = await authStore.fetchProfile()
    profile.name = user?.name || ''
    profile.email = user?.email || ''
  } catch (error: unknown) {
    loadError.value = errorText(error, t('loadCatalogError'))
  } finally {
    loading.value = false
  }
}

async function loadSessions() {
  loadingSessions.value = true
  sessionsError.value = ''
  try {
    sessions.value = await authStore.fetchSessions()
  } catch (error: unknown) {
    sessionsError.value = errorText(error, t('endSessionError'))
  } finally {
    loadingSessions.value = false
  }
}

function formatSessionDate(value: string | null): string {
  if (!value) return t('unknown')
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return t('unknown')
  return new Intl.DateTimeFormat(localeTag.value, { dateStyle: 'medium', timeStyle: 'short' }).format(date)
}

function sessionLabel(value: string): string {
  return value.startsWith(t('sessionPrefix')) ? value.slice(t('sessionPrefix').length) : value || t('sessionLabel')
}

const confirmationBusy = computed(() => revokingAll.value || revokingSessionId.value !== null)
const confirmationTitle = computed(() => confirmationAction.value?.type === 'others'
  ? t('endOtherSessionsTitle')
  : confirmationAction.value?.session.is_current ? t('endCurrentSessionTitle') : t('endThisSessionTitle'))
const confirmationButtonLabel = computed(() => confirmationAction.value?.type === 'others' ? t('endOthers') : t('endSession'))
const confirmationDescription = computed(() => confirmationAction.value?.type === 'others'
  ? t('endOtherSessionsText')
  : confirmationAction.value?.session.is_current
    ? t('endCurrentSessionText')
    : t('endSessionText'))

function requestRevokeSession(session: ActiveSession) {
  confirmationAction.value = { type: 'session', session }
}

function requestRevokeOthers() {
  confirmationAction.value = { type: 'others' }
}

function closeConfirmation() {
  if (!confirmationBusy.value) confirmationAction.value = null
}

async function confirmSessionAction() {
  const action = confirmationAction.value
  if (!action || confirmationBusy.value) return
  sessionsMessage.value = ''
  try {
    if (action.type === 'session') {
      const { session } = action
      revokingSessionId.value = session.id
      await authStore.revokeSession(session.id)
      confirmationAction.value = null
      if (session.is_current) {
        await authStore.logout()
        await router.push({ name: 'login' })
        return
      }
      sessions.value = sessions.value.filter((item) => item.id !== session.id)
      sessionsMessageType.value = 'success'
      sessionsMessage.value = t('selectedSessionEnded')
    } else {
      revokingAll.value = true
      const result = await authStore.revokeOtherSessions()
      confirmationAction.value = null
      sessions.value = sessions.value.filter((session) => session.is_current)
      sessionsMessageType.value = 'success'
      sessionsMessage.value = result.revoked_count
        ? t('sessionsEnded', { count: result.revoked_count })
        : t('noOtherSessions')
    }
  } catch (error: unknown) {
    confirmationAction.value = null
    sessionsMessageType.value = 'error'
    sessionsMessage.value = errorText(error, t('endSessionError'))
  } finally {
    revokingSessionId.value = null
    revokingAll.value = false
  }
}

async function saveProfile() {
  savingProfile.value = true
  profileMessage.value = ''
  try {
    await authStore.updateProfile({ name: profile.name, email: profile.email })
    profileMessageType.value = 'success'
    profileMessage.value = t('profileSaved')
  } catch (error: unknown) {
    profileMessageType.value = 'error'
    profileMessage.value = errorText(error, t('saveProfileError'))
  } finally {
    savingProfile.value = false
  }
}

async function savePassword() {
  passwordMessage.value = ''
  if (passwordForm.password !== passwordForm.password_confirmation) {
    passwordMessageType.value = 'error'
    passwordMessage.value = t('passwordMismatchNew')
    return
  }

  savingPassword.value = true
  try {
    const result = await authStore.updatePassword({ ...passwordForm })
    passwordMessageType.value = 'success'
    passwordMessage.value = t('passwordSaved')
    passwordForm.current_password = ''
    passwordForm.password = ''
    passwordForm.password_confirmation = ''
  } catch (error: unknown) {
    passwordMessageType.value = 'error'
    passwordMessage.value = errorText(error, t('changePasswordError'))
  } finally {
    savingPassword.value = false
  }
}

async function logout() {
  await authStore.logout()
  await router.push({ name: 'login' })
}

onMounted(() => {
  void loadProfile()
  void loadSessions()
})
</script>
