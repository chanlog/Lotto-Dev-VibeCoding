<template>
  <div class="space-y-6">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">🔮 사주 기반 운세 분석</h1>
      <p class="text-gray-600">생년월일을 입력하여 오늘의 운세를 분석받아보세요</p>
    </div>

    <!-- 사주 입력 폼 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">생년월일 정보 입력</h2>
      <form @submit.prevent="analyzefortune" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              생년월일
            </label>
            <input
              type="date"
              v-model="birthInfo.date"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              성별
            </label>
            <select
              v-model="birthInfo.gender"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              required
            >
              <option value="">선택하세요</option>
              <option value="male">남성</option>
              <option value="female">여성</option>
            </select>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              태어난 시간
            </label>
            <select
              v-model="birthInfo.hour"
              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">모르겠음</option>
              <option v-for="hour in 24" :key="hour-1" :value="hour-1">
                {{ String(hour-1).padStart(2, '0') }}:00 ~ {{ String(hour-1).padStart(2, '0') }}:59
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              달력 종류
            </label>
            <div class="flex items-center space-x-4">
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="birthInfo.lunarCalendar"
                  :value="false"
                  class="form-radio text-blue-600"
                />
                <span class="ml-2">양력</span>
              </label>
              <label class="flex items-center">
                <input
                  type="radio"
                  v-model="birthInfo.lunarCalendar"
                  :value="true"
                  class="form-radio text-blue-600"
                />
                <span class="ml-2">음력</span>
              </label>
            </div>
          </div>
        </div>
        
        <button
          type="submit"
          :disabled="isLoading || !birthInfo.date || !birthInfo.gender"
          class="w-full bg-purple-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
        >
          {{ isLoading ? '분석 중...' : '운세 분석하기' }}
        </button>
      </form>
    </div>

    <!-- 분석 결과 -->
    <div v-if="currentAnalysis" class="bg-white rounded-lg shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">오늘의 운세 분석 결과</h2>
        <div class="text-sm text-gray-500">
          {{ formatDate(currentAnalysis.analysisDate) }}
        </div>
      </div>
      
      <!-- 운세 점수 -->
      <div class="mb-6">
        <div class="flex items-center justify-between mb-2">
          <span class="text-lg font-medium">총 운세 점수</span>
          <span class="text-2xl font-bold text-purple-600">
            {{ currentAnalysis.fortuneScore }}/100
          </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
          <div 
            class="bg-purple-600 h-3 rounded-full transition-all duration-500"
            :style="{ width: `${currentAnalysis.fortuneScore}%` }"
          ></div>
        </div>
      </div>
      
      <!-- 세부 운세 -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="text-center p-4 bg-gray-50 rounded-lg">
          <div class="text-2xl mb-2">💫</div>
          <div class="text-sm text-gray-600">전체운</div>
          <div class="font-semibold">{{ getFortuneLevel(currentAnalysis.fortuneScore) }}</div>
        </div>
        <div class="text-center p-4 bg-green-50 rounded-lg">
          <div class="text-2xl mb-2">💰</div>
          <div class="text-sm text-gray-600">재물운</div>
          <div class="font-semibold text-green-700">상</div>
        </div>
        <div class="text-center p-4 bg-blue-50 rounded-lg">
          <div class="text-2xl mb-2">🍀</div>
          <div class="text-sm text-gray-600">행운</div>
          <div class="font-semibold text-blue-700">상</div>
        </div>
        <div class="text-center p-4 bg-red-50 rounded-lg">
          <div class="text-2xl mb-2">❤️</div>
          <div class="text-sm text-gray-600">건강운</div>
          <div class="font-semibold text-red-700">중</div>
        </div>
        <div class="text-center p-4 bg-yellow-50 rounded-lg">
          <div class="text-2xl mb-2">📈</div>
          <div class="text-sm text-gray-600">사업운</div>
          <div class="font-semibold text-yellow-700">중</div>
        </div>
      </div>
      
      <!-- 세부 분석 -->
      <div class="space-y-4 mb-6">
        <div>
          <h3 class="font-semibold text-gray-900 mb-2">💫 전체운</h3>
          <p class="text-gray-700">{{ currentAnalysis.analysis.overall }}</p>
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 mb-2">💰 재물운</h3>
          <p class="text-gray-700">{{ currentAnalysis.analysis.wealth }}</p>
        </div>
        <div>
          <h3 class="font-semibold text-gray-900 mb-2">🍀 행운</h3>
          <p class="text-gray-700">{{ currentAnalysis.analysis.luck }}</p>
        </div>
      </div>
      
      <!-- 행운 번호 -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <h3 class="font-semibold text-gray-900 mb-3">🎯 오늘의 행운 번호</h3>
          <div class="flex space-x-2">
            <div
              v-for="number in currentAnalysis.luckyNumbers"
              :key="number"
              class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold"
            >
              {{ number }}
            </div>
          </div>
        </div>
        
        <div>
          <h3 class="font-semibold text-gray-900 mb-3">⚠️ 피해야 할 번호</h3>
          <div class="flex space-x-2">
            <div
              v-for="number in currentAnalysis.avoidNumbers"
              :key="number"
              class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white font-bold"
            >
              {{ number }}
            </div>
          </div>
        </div>
      </div>
      
      <!-- 추천 로또 번호 -->
      <div class="bg-purple-50 rounded-lg p-4 mb-6">
        <h3 class="font-semibold text-purple-900 mb-3">🎰 운세 기반 추천 번호</h3>
        <div class="flex items-center space-x-2 mb-3">
          <div
            v-for="number in currentAnalysis.recommendedNumbers"
            :key="number"
            class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold"
            :class="getBallColor(number)"
          >
            {{ number }}
          </div>
        </div>
        <div class="flex space-x-2">
          <button
            @click="generateFromFortune"
            class="bg-purple-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-purple-700 transition duration-200"
          >
            이 번호로 로또 생성
          </button>
          <button
            @click="saveRecommendedNumbers"
            class="border border-purple-600 text-purple-600 px-4 py-2 rounded-lg font-medium hover:bg-purple-50 transition duration-200"
          >
            번호 저장
          </button>
        </div>
      </div>
      
      <!-- 유효기간 안내 -->
      <div class="text-center text-sm text-gray-500">
        <p>이 분석 결과는 {{ currentAnalysis.validity }}까지 유효합니다.</p>
        <p class="mt-1">더 정확한 분석을 위해서는 태어난 시간을 입력해주세요.</p>
      </div>
    </div>

    <!-- 분석 기록 -->
    <div v-if="analysisHistory.length > 0" class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">분석 기록</h2>
      <div class="space-y-3">
        <div
          v-for="history in analysisHistory.slice(0, 5)"
          :key="history.id"
          class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50 cursor-pointer"
          @click="loadAnalysis(history)"
        >
          <div>
            <div class="font-medium">{{ formatDate(history.analysisDate) }}</div>
            <div class="text-sm text-gray-600">
              운세 점수: {{ history.fortuneScore }}/100
            </div>
          </div>
          <div class="text-sm text-gray-500">
            {{ history.validity }}
          </div>
        </div>
      </div>
      
      <div v-if="analysisHistory.length > 5" class="text-center mt-4">
        <button
          @click="showAllHistory = !showAllHistory"
          class="text-blue-600 hover:text-blue-800 text-sm font-medium"
        >
          {{ showAllHistory ? '접기' : '더 보기' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import type { BirthInfo } from '../store/modules/fortune'

const store = useStore()
const router = useRouter()

const birthInfo = ref<{
  date: string
  gender: 'male' | 'female' | ''
  hour: number | ''
  lunarCalendar: boolean
}>({
  date: '',
  gender: '',
  hour: '',
  lunarCalendar: false
})

const showAllHistory = ref(false)

const currentAnalysis = computed(() => store.getters['fortune/currentAnalysis'])
const analysisHistory = computed(() => store.getters['fortune/analysisHistory'])
const isLoading = computed(() => store.getters['fortune/isLoading'])

const analyzefortune = async () => {
  const [year, month, day] = birthInfo.value.date.split('-').map(Number)
  
  const birthData: BirthInfo = {
    year,
    month,
    day,
    hour: typeof birthInfo.value.hour === 'number' ? birthInfo.value.hour : 12,
    minute: 0,
    gender: birthInfo.value.gender as 'male' | 'female',
    lunarCalendar: birthInfo.value.lunarCalendar
  }
  
  const result = await store.dispatch('fortune/analyzefortune', birthData)
  
  if (!result.success) {
    alert(`분석에 실패했습니다: ${result.message}`)
  }
}

const generateFromFortune = () => {
  router.push('/generate?type=fortune')
}

const saveRecommendedNumbers = async () => {
  if (!currentAnalysis.value) return
  
  const result = await store.dispatch('lotto/saveTicket', currentAnalysis.value.recommendedNumbers)
  if (result.success) {
    alert('추천 번호가 저장되었습니다!')
  } else {
    alert(`저장에 실패했습니다: ${result.message}`)
  }
}

const loadAnalysis = (analysis: any) => {
  store.commit('fortune/SET_CURRENT_ANALYSIS', analysis)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('ko-KR')
}

const getFortuneLevel = (score: number) => {
  if (score >= 80) return '대길'
  if (score >= 60) return '길'
  if (score >= 40) return '평'
  if (score >= 20) return '흉'
  return '대흉'
}

const getBallColor = (number: number) => {
  if (number <= 10) return 'bg-yellow-500'
  if (number <= 20) return 'bg-blue-500'
  if (number <= 30) return 'bg-red-500'
  if (number <= 40) return 'bg-gray-600'
  return 'bg-green-500'
}

onMounted(() => {
  // 분석 기록 불러오기
  store.dispatch('fortune/fetchAnalysisHistory')
  
  // 오늘의 운세가 있다면 불러오기
  store.dispatch('fortune/getTodaysFortune')
})
</script>