import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

;(window as unknown as Window & { Pusher: typeof Pusher }).Pusher = Pusher

const echo = new Echo({
  broadcaster: 'reverb',
  key: import.meta.env.VITE_REVERB_APP_KEY || 'microstore-local-key',
  wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
  wsPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
  wssPort: Number(import.meta.env.VITE_REVERB_PORT || 8080),
  forceTLS: (import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '')) === 'https',
  enabledTransports: ['ws', 'wss'],
  authEndpoint: 'http://localhost:8005/api/broadcasting/auth',
  auth: {
    headers: {
      Authorization: localStorage.getItem('token') ? `Bearer ${localStorage.getItem('token')}` : '',
    },
  },
})

export function setEchoAuthToken(token: string | null) {
  echo.connector.options.auth.headers.Authorization = token ? `Bearer ${token}` : ''
}

export default echo
