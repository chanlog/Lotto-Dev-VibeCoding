import { Module } from 'vuex'
import { RootState } from '../index'
import api from '../../services/api'

export interface LottoState {
  latestNumbers: LottoResult | null
  generatedNumbers: LottoSet[]
  statistics: LottoStatistics | null
  userTickets: UserTicket[]
  loading: boolean
}

export interface LottoResult {
  round: number
  date: string
  numbers: number[]
  bonusNumber: number
  totalSales: number
  winners: WinnerInfo[]
}

export interface LottoSet {
  id: string
  numbers: number[]
  generationType: 'auto' | 'semi' | 'fortune'
  createdAt: string
  fortuneAnalysis?: FortuneAnalysis
}

export interface LottoStatistics {
  numberFrequency: { [key: number]: number }
  recentPatterns: number[][]
  hottestNumbers: number[]
  coldestNumbers: number[]
}

export interface UserTicket {
  id: number
  numbers: number[]
  round: number
  purchaseDate: string
  result?: TicketResult
}

export interface WinnerInfo {
  rank: number
  count: number
  prize: number
}

export interface TicketResult {
  matchCount: number
  rank: number | null
  prize: number
}

export interface FortuneAnalysis {
  luckyNumbers: number[]
  avoidNumbers: number[]
  fortuneScore: number
  analysis: string
}

export const lotto: Module<LottoState, RootState> = {
  namespaced: true,
  
  state: {
    latestNumbers: null,
    generatedNumbers: [],
    statistics: null,
    userTickets: [],
    loading: false
  },

  getters: {
    latestNumbers: (state) => state.latestNumbers,
    generatedNumbers: (state) => state.generatedNumbers,
    statistics: (state) => state.statistics,
    userTickets: (state) => state.userTickets,
    isLoading: (state) => state.loading
  },

  mutations: {
    SET_LATEST_NUMBERS(state, numbers: LottoResult) {
      state.latestNumbers = numbers
    },
    
    ADD_GENERATED_NUMBERS(state, numbers: LottoSet) {
      state.generatedNumbers.unshift(numbers)
      // 최대 10개까지만 보관
      if (state.generatedNumbers.length > 10) {
        state.generatedNumbers = state.generatedNumbers.slice(0, 10)
      }
    },
    
    SET_STATISTICS(state, statistics: LottoStatistics) {
      state.statistics = statistics
    },
    
    SET_USER_TICKETS(state, tickets: UserTicket[]) {
      state.userTickets = tickets
    },
    
    ADD_USER_TICKET(state, ticket: UserTicket) {
      state.userTickets.unshift(ticket)
    },
    
    SET_LOADING(state, loading: boolean) {
      state.loading = loading
    },
    
    CLEAR_GENERATED_NUMBERS(state) {
      state.generatedNumbers = []
    }
  },

  actions: {
    async fetchLatestNumbers({ commit }) {
      try {
        commit('SET_LOADING', true)
        const response = await api.get('/api/lotto/latest')
        commit('SET_LATEST_NUMBERS', response.data)
        return { success: true, data: response.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '최신 당첨번호를 가져오는데 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async generateNumbers({ commit }, options: { type: 'auto' | 'semi' | 'fortune', excludeNumbers?: number[], fortuneData?: any }) {
      try {
        commit('SET_LOADING', true)
        const response = await api.post('/api/lotto/generate', options)
        commit('ADD_GENERATED_NUMBERS', response.data)
        return { success: true, data: response.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '번호 생성에 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async fetchStatistics({ commit }) {
      try {
        commit('SET_LOADING', true)
        const response = await api.get('/api/lotto/statistics')
        commit('SET_STATISTICS', response.data)
        return { success: true, data: response.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '통계 정보를 가져오는데 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async fetchUserTickets({ commit }) {
      try {
        commit('SET_LOADING', true)
        const response = await api.get('/api/user/tickets')
        commit('SET_USER_TICKETS', response.data)
        return { success: true, data: response.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '사용자 티켓을 가져오는데 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async saveTicket({ commit }, numbers: number[]) {
      try {
        const response = await api.post('/api/user/tickets', { numbers })
        commit('ADD_USER_TICKET', response.data)
        return { success: true, data: response.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '티켓 저장에 실패했습니다.' 
        }
      }
    }
  }
}