import axios from 'axios'

// axios 인스턴스 생성
const api = axios.create({
  baseURL: process.env.VUE_APP_API_URL || 'http://localhost:8000/api',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// 요청 인터셉터
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// 응답 인터셉터
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      // 토큰이 만료되었거나 유효하지 않은 경우
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

// API 인터페이스 정의
export interface User {
  id: number
  name: string
  email: string
  created_at: string
  updated_at: string
}

export interface LottoNumber {
  id: number
  user_id: number
  numbers: number[]
  type: 'auto' | 'semi' | 'manual' | 'fortune'
  memo?: string
  is_winner: boolean
  prize_amount: number
  draw_no?: number
  created_at: string
  updated_at: string
}

export interface FortuneAnalysis {
  id: number
  user_id: number
  birth_date: string
  birth_time?: string
  gender: 'male' | 'female'
  wealth_luck: number
  general_luck: number
  lucky_numbers: number[]
  lucky_colors: string[]
  analysis_summary: string
  today_fortune?: string
  analysis_date: string
  created_at: string
  updated_at: string
}

// 인증 API
export const authAPI = {
  login: (email: string, password: string) =>
    api.post('/auth/login', { email, password }),
  
  register: (name: string, email: string, password: string, password_confirmation: string) =>
    api.post('/auth/register', { name, email, password, password_confirmation }),
  
  logout: () =>
    api.post('/auth/logout'),
  
  me: () =>
    api.get('/auth/me')
}

// 로또 API
export const lottoAPI = {
  latest: () =>
    api.get('/lotto/latest'),
  
  generate: (type: string, count: number = 1, preferred_numbers?: number[]) =>
    api.post('/lotto/generate', { type, count, preferred_numbers }),
  
  save: (numbers: number[], type: string, memo?: string) =>
    api.post('/lotto/save', { numbers, type, memo }),
  
  myNumbers: () =>
    api.get('/lotto/my-numbers'),
  
  delete: (id: number) =>
    api.delete(`/lotto/my-numbers/${id}`)
}

// 사주 분석 API
export const fortuneAPI = {
  analyze: (birth_date: string, birth_time?: string, gender?: string) =>
    api.post('/fortune/analyze', { birth_date, birth_time, gender }),
  
  generateNumbers: (analysis_id?: number, count: number = 1) =>
    api.post('/fortune/generate-numbers', { analysis_id, count }),
  
  history: () =>
    api.get('/fortune/history'),
  
  saveAnalysis: (data: Partial<FortuneAnalysis>) =>
    api.post('/fortune/save-analysis', data),
  
  updateTodayFortune: (id: number, today_fortune: string) =>
    api.put(`/fortune/analyses/${id}/today`, { today_fortune })
}

export default api