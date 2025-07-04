import { Module } from 'vuex'
import { RootState } from '../index'
import { authAPI, User } from '../../services/api'

export interface AuthState {
  token: string | null
  user: User | null
  isAuthenticated: boolean
  loading: boolean
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
    },
    
    SET_USER(state, user: User) {
      state.user = user
    },
    
    LOGOUT(state) {
      state.token = null
      state.user = null
      state.isAuthenticated = false
      localStorage.removeItem('token')
    },
    
    SET_LOADING(state, loading: boolean) {
      state.loading = loading
    }
  },

  actions: {
    async login({ commit }, credentials: LoginCredentials) {
      try {
        commit('SET_LOADING', true)
        const response = await authAPI.login(credentials.email, credentials.password)
        const { token, user } = response.data.data
        
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
        const response = await authAPI.register(
          userData.name,
          userData.email,
          userData.password,
          userData.password_confirmation
        )
        const { token, user } = response.data.data
        
        commit('SET_TOKEN', token)
        commit('SET_USER', user)
        
        return { success: true }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '회원가입에 실패했습니다.',
          errors: error.response?.data?.errors
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async logout({ commit }) {
      try {
        await authAPI.logout()
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        commit('LOGOUT')
      }
    },

    async fetchUser({ commit, state }) {
      if (!state.token) return
      
      try {
        const response = await authAPI.me()
        commit('SET_USER', response.data.data)
        commit('SET_TOKEN', state.token) // 인증 상태 유지
      } catch (error) {
        commit('LOGOUT')
      }
    },

    // 토큰이 있으면 자동 로그인 시도
    async initializeAuth({ commit, dispatch }) {
      const token = localStorage.getItem('token')
      if (token) {
        commit('SET_TOKEN', token)
        await dispatch('fetchUser')
      }
    }
  }
}