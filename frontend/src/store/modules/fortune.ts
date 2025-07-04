import { Module } from 'vuex'
import { RootState } from '../index'
import { fortuneAPI, FortuneAnalysis } from '../../services/api'

export interface FortuneState {
  currentAnalysis: FortuneAnalysis | null
  analysisHistory: FortuneAnalysis[]
  loading: boolean
}

export interface BirthInfo {
  birth_date: string
  birth_time?: string
  gender: 'male' | 'female'
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
    todaysLuckyNumbers: (state) => state.currentAnalysis?.lucky_numbers || [],
    fortuneScore: (state) => state.currentAnalysis?.wealth_luck || 0
  },

  mutations: {
    SET_CURRENT_ANALYSIS(state, analysis: FortuneAnalysis) {
      state.currentAnalysis = analysis
    },
    
    ADD_TO_HISTORY(state, analysis: FortuneAnalysis) {
      // 중복 제거 (같은 날짜의 분석이 있으면 교체)
      const existingIndex = state.analysisHistory.findIndex(
        item => item.analysis_date === analysis.analysis_date
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
    
    SET_ANALYSIS_HISTORY(state, history: FortuneAnalysis[]) {
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
    async analyzeFortune({ commit }, birthInfo: BirthInfo) {
      try {
        commit('SET_LOADING', true)
        const response = await fortuneAPI.analyze(
          birthInfo.birth_date,
          birthInfo.birth_time,
          birthInfo.gender
        )
        const analysis = response.data.data
        
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

    async generateFortuneNumbers({ commit }, { analysis_id, count = 1 }: { analysis_id?: number, count?: number }) {
      try {
        commit('SET_LOADING', true)
        const response = await fortuneAPI.generateNumbers(analysis_id, count)
        
        return { success: true, data: response.data.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '운세 기반 번호 생성에 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async fetchAnalysisHistory({ commit }) {
      try {
        commit('SET_LOADING', true)
        const response = await fortuneAPI.history()
        commit('SET_ANALYSIS_HISTORY', response.data.data.analyses)
        return { success: true, data: response.data.data.analyses }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '분석 기록을 가져오는데 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async saveAnalysis({ commit }, data: Partial<FortuneAnalysis>) {
      try {
        const response = await fortuneAPI.saveAnalysis(data)
        commit('ADD_TO_HISTORY', response.data.data)
        return { success: true, data: response.data.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '분석 저장에 실패했습니다.' 
        }
      }
    },

    async updateTodayFortune({ commit }, { id, today_fortune }: { id: number, today_fortune: string }) {
      try {
        const response = await fortuneAPI.updateTodayFortune(id, today_fortune)
        commit('SET_CURRENT_ANALYSIS', response.data.data)
        return { success: true, data: response.data.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '오늘의 운세 업데이트에 실패했습니다.' 
        }
      }
    },

    clearCurrentAnalysis({ commit }) {
      commit('CLEAR_ANALYSIS')
    }
  }
}