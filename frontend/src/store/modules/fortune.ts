import { Module } from 'vuex'
import { RootState } from '../index'
import api from '../../services/api'

export interface FortuneState {
  currentAnalysis: FortuneAnalysisResult | null
  analysisHistory: FortuneAnalysisResult[]
  loading: boolean
}

export interface FortuneAnalysisResult {
  id: string
  birthInfo: BirthInfo
  analysis: {
    overall: string
    wealth: string
    luck: string
    health: string
    career: string
  }
  luckyNumbers: number[]
  avoidNumbers: number[]
  fortuneScore: number
  recommendedNumbers: number[]
  analysisDate: string
  validity: string // 유효기간 (오늘, 이번주 등)
}

export interface BirthInfo {
  year: number
  month: number
  day: number
  hour: number
  minute: number
  gender: 'male' | 'female'
  lunarCalendar: boolean
}

export interface DailyFortune {
  date: string
  overall: number
  wealth: number
  luck: number
  health: number
  career: number
  summary: string
  luckyTime: string
  luckyDirection: string
  luckyColor: string
}

export const fortune: Module<FortuneState, RootState> = {
  namespaced: true,
  
  state: {
    currentAnalysis: null,
    analysisHistory: [],
    loading: false
  },

  getters: {
    currentAnalysis: (state) => state.currentAnalysis,
    analysisHistory: (state) => state.analysisHistory,
    isLoading: (state) => state.loading,
    todaysLuckyNumbers: (state) => state.currentAnalysis?.luckyNumbers || [],
    fortuneScore: (state) => state.currentAnalysis?.fortuneScore || 0
  },

  mutations: {
    SET_CURRENT_ANALYSIS(state, analysis: FortuneAnalysisResult) {
      state.currentAnalysis = analysis
    },
    
    ADD_TO_HISTORY(state, analysis: FortuneAnalysisResult) {
      // 중복 제거 (같은 날짜의 분석이 있으면 교체)
      const existingIndex = state.analysisHistory.findIndex(
        item => item.analysisDate === analysis.analysisDate
      )
      
      if (existingIndex !== -1) {
        state.analysisHistory[existingIndex] = analysis
      } else {
        state.analysisHistory.unshift(analysis)
        // 최대 30개까지만 보관
        if (state.analysisHistory.length > 30) {
          state.analysisHistory = state.analysisHistory.slice(0, 30)
        }
      }
    },
    
    SET_ANALYSIS_HISTORY(state, history: FortuneAnalysisResult[]) {
      state.analysisHistory = history
    },
    
    SET_LOADING(state, loading: boolean) {
      state.loading = loading
    },
    
    CLEAR_ANALYSIS(state) {
      state.currentAnalysis = null
    }
  },

  actions: {
    async analyzefortune({ commit }, birthInfo: BirthInfo) {
      try {
        commit('SET_LOADING', true)
        const response = await api.post('/api/fortune/analyze', birthInfo)
        const analysis = response.data
        
        commit('SET_CURRENT_ANALYSIS', analysis)
        commit('ADD_TO_HISTORY', analysis)
        
        return { success: true, data: analysis }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '사주 분석에 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async getTodaysFortune({ commit, state }) {
      // 오늘 날짜의 분석이 이미 있는지 확인
      const today = new Date().toISOString().split('T')[0]
      const todaysAnalysis = state.analysisHistory.find(
        analysis => analysis.analysisDate.startsWith(today)
      )
      
      if (todaysAnalysis) {
        commit('SET_CURRENT_ANALYSIS', todaysAnalysis)
        return { success: true, data: todaysAnalysis }
      }
      
      try {
        commit('SET_LOADING', true)
        const response = await api.get('/api/fortune/today')
        const analysis = response.data
        
        commit('SET_CURRENT_ANALYSIS', analysis)
        commit('ADD_TO_HISTORY', analysis)
        
        return { success: true, data: analysis }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '오늘의 운세를 가져오는데 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async fetchAnalysisHistory({ commit }) {
      try {
        commit('SET_LOADING', true)
        const response = await api.get('/api/fortune/history')
        commit('SET_ANALYSIS_HISTORY', response.data)
        return { success: true, data: response.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '분석 기록을 가져오는데 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async getDailyFortune({ commit }, date: string) {
      try {
        commit('SET_LOADING', true)
        const response = await api.get(`/api/fortune/daily/${date}`)
        return { success: true, data: response.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '일일 운세를 가져오는데 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    clearCurrentAnalysis({ commit }) {
      commit('CLEAR_ANALYSIS')
    }
  }
}