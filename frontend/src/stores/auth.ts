import { defineStore } from 'pinia'
import axios from 'axios'

interface AuthUser {
    id?: number
    name?: string
    email?: string
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
        async register(credentials: RegisterCredentials) {
            try {
                const response = await axios.post<LoginResponse>('/register', credentials)
                this.token = response.data.token
                this.user = response.data.user

                localStorage.setItem('token', this.token)
                localStorage.setItem('user', JSON.stringify(this.user))
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`

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
            }
        },

        initializeAuth() {
            if (this.token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
            }
        }
    }
})
