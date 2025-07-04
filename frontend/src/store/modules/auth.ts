import { Module } from 'vuex'
import { RootState } from '../index'
import api from '../../services/api'

export interface AuthState {
  token: string | null
  user: User | null
  isAuthenticated: boolean
  loading: boolean
}

export interface User {
  id: number
  name: string
  email: string
  created_at: string
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
}

export const auth: Module<AuthState, RootState> = {
  namespaced: true,
  
  state: {
    token: localStorage.getItem('token'),
    user: null,
    isAuthenticated: false,
    loading: false
  },

  getters: {
    isAuthenticated: (state) => state.isAuthenticated,
    currentUser: (state) => state.user,
    token: (state) => state.token,
    isLoading: (state) => state.loading
  },

  mutations: {
    SET_TOKEN(state, token: string) {
      state.token = token
      state.isAuthenticated = true
      localStorage.setItem('token', token)
      api.defaults.headers.common['Authorization'] = `Bearer ${token}`
    },
    
    SET_USER(state, user: User) {
      state.user = user
    },
    
    LOGOUT(state) {
      state.token = null
      state.user = null
      state.isAuthenticated = false
      localStorage.removeItem('token')
      delete api.defaults.headers.common['Authorization']
    },
    
    SET_LOADING(state, loading: boolean) {
      state.loading = loading
    }
  },

  actions: {
    async login({ commit }, credentials: LoginCredentials) {
      try {
        commit('SET_LOADING', true)
        const response = await api.post('/api/auth/login', credentials)
        const { token, user } = response.data
        
        commit('SET_TOKEN', token)
        commit('SET_USER', user)
        
        return { success: true }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '로그인에 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async register({ commit }, userData: RegisterData) {
      try {
        commit('SET_LOADING', true)
        const response = await api.post('/api/auth/register', userData)
        const { token, user } = response.data
        
        commit('SET_TOKEN', token)
        commit('SET_USER', user)
        
        return { success: true }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '회원가입에 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async logout({ commit }) {
      try {
        await api.post('/api/auth/logout')
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        commit('LOGOUT')
      }
    },

    async fetchUser({ commit, state }) {
      if (!state.token) return
      
      try {
        const response = await api.get('/api/auth/user')
        commit('SET_USER', response.data)
        commit('SET_TOKEN', state.token) // 인증 상태 유지
      } catch (error) {
        commit('LOGOUT')
      }
    }
  }
}