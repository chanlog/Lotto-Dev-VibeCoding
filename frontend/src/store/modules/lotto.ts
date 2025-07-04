import { Module } from 'vuex'
import { RootState } from '../index'
import { lottoAPI, LottoNumber } from '../../services/api'

export interface LottoState {
  latestNumbers: LatestNumbersResult | null
  generatedNumbers: GeneratedNumber[]
  myNumbers: LottoNumber[]
  loading: boolean
}

export interface LatestNumbersResult {
  draw_no: number
  draw_date: string
  numbers: number[]
  bonus_number: number
  prize_amounts: { [key: number]: number }
  winners: { [key: number]: number }
}

export interface GeneratedNumber {
  id: number
  numbers: number[]
  type: 'auto' | 'semi' | 'manual' | 'fortune'
  created_at: string
}

export const lotto: Module<LottoState, RootState> = {
  namespaced: true,
  
  state: {
    latestNumbers: null,
    generatedNumbers: [],
    myNumbers: [],
    loading: false
  },

  getters: {
    latestNumbers: (state) => state.latestNumbers,
    generatedNumbers: (state) => state.generatedNumbers,
    myNumbers: (state) => state.myNumbers,
    isLoading: (state) => state.loading
  },

  mutations: {
    SET_LATEST_NUMBERS(state, numbers: LatestNumbersResult) {
      state.latestNumbers = numbers
    },
    
    SET_GENERATED_NUMBERS(state, numbers: GeneratedNumber[]) {
      state.generatedNumbers = numbers
    },
    
    ADD_GENERATED_NUMBERS(state, numbers: GeneratedNumber[]) {
      state.generatedNumbers = [...numbers, ...state.generatedNumbers]
      // 최대 20개까지만 보관
      if (state.generatedNumbers.length > 20) {
        state.generatedNumbers = state.generatedNumbers.slice(0, 20)
      }
    },
    
    SET_MY_NUMBERS(state, numbers: LottoNumber[]) {
      state.myNumbers = numbers
    },
    
    ADD_MY_NUMBER(state, number: LottoNumber) {
      state.myNumbers.unshift(number)
    },
    
    REMOVE_MY_NUMBER(state, id: number) {
      state.myNumbers = state.myNumbers.filter(n => n.id !== id)
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
        const response = await lottoAPI.latest()
        commit('SET_LATEST_NUMBERS', response.data.data)
        return { success: true, data: response.data.data }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '최신 당첨번호를 가져오는데 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async generateNumbers({ commit }, options: { 
      type: 'auto' | 'semi' | 'fortune', 
      count?: number,
      preferred_numbers?: number[] 
    }) {
      try {
        commit('SET_LOADING', true)
        const response = await lottoAPI.generate(
          options.type, 
          options.count || 1, 
          options.preferred_numbers
        )
        const formattedNumbers = response.data.data.numbers.map((num: any, index: number) => ({
          id: Date.now() + index,
          numbers: num.numbers,
          type: num.type,
          created_at: num.created_at
        }))
        commit('ADD_GENERATED_NUMBERS', formattedNumbers)
        return { success: true, data: formattedNumbers }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '번호 생성에 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async fetchMyNumbers({ commit }) {
      try {
        commit('SET_LOADING', true)
        const response = await lottoAPI.myNumbers()
        commit('SET_MY_NUMBERS', response.data.data.numbers)
        return { success: true, data: response.data.data.numbers }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '내 번호를 가져오는데 실패했습니다.' 
        }
      } finally {
        commit('SET_LOADING', false)
      }
    },

    async saveNumber({ commit }, { numbers, type, memo }: { 
      numbers: number[], 
      type: string, 
      memo?: string 
    }) {
      try {
        const response = await lottoAPI.save(numbers, type, memo)
        commit('ADD_MY_NUMBER', response.data.data.lotto_number)
        return { success: true, data: response.data.data.lotto_number }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '번호 저장에 실패했습니다.',
          errors: error.response?.data?.errors
        }
      }
    },

    async deleteNumber({ commit }, id: number) {
      try {
        await lottoAPI.delete(id)
        commit('REMOVE_MY_NUMBER', id)
        return { success: true }
      } catch (error: any) {
        return { 
          success: false, 
          message: error.response?.data?.message || '번호 삭제에 실패했습니다.' 
        }
      }
    }
  }
}