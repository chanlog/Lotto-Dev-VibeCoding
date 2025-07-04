import { createStore } from 'vuex'
import { auth } from './modules/auth'
import { lotto } from './modules/lotto'
import { fortune } from './modules/fortune'

export interface RootState {
  // 루트 상태 정의
}

export default createStore<RootState>({
  state: {},
  getters: {},
  mutations: {},
  actions: {},
  modules: {
    auth,
    lotto,
    fortune
  }
})