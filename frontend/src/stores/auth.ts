import { defineStore } from 'pinia'
import axios from 'axios'
import { setEchoAuthToken } from '@/realtime/echo'

interface AuthUser {
    id?: number
    name?: string
    email?: string
    created_at?: string
    [key: string]: unknown
}

interface LoginCredentials {
    email: string
    password: string
}

interface LoginResponse {
    token: string
    user: AuthUser
}

interface RegisterCredentials extends LoginCredentials {
    name: string
    password_confirmation: string
}

interface ProfileFields {
    name: string
    email: string
}

interface PasswordChange {
    current_password: string
    password: string
    password_confirmation: string
}

interface UserResponse {
    user: AuthUser
}

export interface ActiveSession {
    id: string
    name: string
    created_at: string | null
    expires_at: string | null
    is_current: boolean
}

axios.defaults.baseURL = 'http://localhost:8005/api'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user') || 'null') as AuthUser | null,
        token: localStorage.getItem('token') || null
    }),

    getters: {
        isAuthenticated: (state) => !!state.token

    },

    actions: {
        async fetchProfile() {
            try {
                const response = await axios.get<UserResponse>('/profile')
                this.user = response.data.user
                localStorage.setItem('user', JSON.stringify(this.user))
                return this.user
            } catch (error: unknown) {
                if (axios.isAxiosError(error)) throw error.response?.data || error.message
                throw error
            }
        },

        async updateProfile(fields: ProfileFields) {
            try {
                const response = await axios.patch<UserResponse>('/profile', fields)
                this.user = response.data.user
                localStorage.setItem('user', JSON.stringify(this.user))
                return this.user
            } catch (error: unknown) {
                if (axios.isAxiosError(error)) throw error.response?.data || error.message
                throw error
            }
        },

        async updatePassword(fields: PasswordChange) {
            try {
                const response = await axios.put<{ message: string }>('/profile/password', fields)
                return response.data
            } catch (error: unknown) {
                if (axios.isAxiosError(error)) throw error.response?.data || error.message
                throw error
            }
        },

        async fetchSessions() {
            try {
                const response = await axios.get<{ sessions: ActiveSession[] }>('/profile/sessions')
                return response.data.sessions
            } catch (error: unknown) {
                if (axios.isAxiosError(error)) throw error.response?.data || error.message
                throw error
            }
        },

        async revokeSession(id: string) {
            try {
                const response = await axios.delete<{ message: string }>(`/profile/sessions/${encodeURIComponent(id)}`)
                return response.data
            } catch (error: unknown) {
                if (axios.isAxiosError(error)) throw error.response?.data || error.message
                throw error
            }
        },

        async revokeOtherSessions() {
            try {
                const response = await axios.post<{ message: string; revoked_count: number }>('/profile/sessions/revoke-others')
                return response.data
            } catch (error: unknown) {
                if (axios.isAxiosError(error)) throw error.response?.data || error.message
                throw error
            }
        },

        async register(credentials: RegisterCredentials) {
            try {
                const response = await axios.post<LoginResponse>('/register', credentials)
                this.token = response.data.token
                this.user = response.data.user

                localStorage.setItem('token', this.token)
                localStorage.setItem('user', JSON.stringify(this.user))
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
                setEchoAuthToken(this.token)

                return response.data
            } catch (error: unknown) {
                if (axios.isAxiosError(error)) {
                    throw error.response?.data || error.message
                }
                throw error
            }
        },

        async login(credentials: LoginCredentials) {
            try {
                const response = await axios.post<LoginResponse>('/login', credentials)

                this.token = response.data.token
                this.user = response.data.user

                localStorage.setItem('token', this.token)
                localStorage.setItem('user', JSON.stringify(this.user))

                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
                setEchoAuthToken(this.token)

                return response.data
            } catch (error: unknown) {
                if (axios.isAxiosError(error)) {
                    throw error.response?.data || error.message
                }
                throw error
            }
        },

        async logout() {
            try {
                await axios.post('/logout')
            } catch (error: unknown) {
                console.error('Failed to revoke token on backend', error)
            } finally {
                this.token = null
                this.user = null

                localStorage.removeItem('token')
                localStorage.removeItem('user')

                delete axios.defaults.headers.common['Authorization']
                setEchoAuthToken(null)
            }
        },

        initializeAuth() {
            if (this.token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
            }
            setEchoAuthToken(this.token)
        }
    }
})
