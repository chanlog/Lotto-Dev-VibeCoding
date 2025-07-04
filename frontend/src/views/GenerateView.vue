<template>
  <div class="space-y-6">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">🎯 로또 번호 생성</h1>
      <p class="text-gray-600">원하는 방식으로 로또 번호를 생성해보세요</p>
    </div>

    <!-- 생성 방식 선택 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">생성 방식 선택</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <button
          @click="selectedType = 'auto'"
          :class="[
            'p-4 rounded-lg border-2 transition duration-200',
            selectedType === 'auto' 
              ? 'border-blue-500 bg-blue-50' 
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <div class="text-3xl mb-2">🎲</div>
          <div class="font-medium">자동 번호</div>
          <div class="text-sm text-gray-600">완전 랜덤으로 생성</div>
        </button>
        
        <button
          @click="selectedType = 'semi'"
          :class="[
            'p-4 rounded-lg border-2 transition duration-200',
            selectedType === 'semi' 
              ? 'border-green-500 bg-green-50' 
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <div class="text-3xl mb-2">🎯</div>
          <div class="font-medium">반자동 번호</div>
          <div class="text-sm text-gray-600">선호 번호 + 자동</div>
        </button>
        
        <button
          @click="selectedType = 'fortune'"
          :class="[
            'p-4 rounded-lg border-2 transition duration-200',
            selectedType === 'fortune' 
              ? 'border-purple-500 bg-purple-50' 
              : 'border-gray-200 hover:border-gray-300'
          ]"
        >
          <div class="text-3xl mb-2">🔮</div>
          <div class="font-medium">운세 기반</div>
          <div class="text-sm text-gray-600">사주 분석 기반</div>
        </button>
      </div>
    </div>

    <!-- 생성 옵션 -->
    <div v-if="selectedType" class="bg-white rounded-lg shadow-md p-6">
      <h3 class="text-lg font-semibold mb-4">생성 옵션</h3>
      
      <!-- 제외 번호 설정 -->
      <div class="mb-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
          <label class="block text-lg font-bold text-red-800 mb-3 flex items-center">
            <span class="text-2xl mr-2">🚫</span>
            제외할 번호 (선택사항)
          </label>
          <p class="text-sm text-red-600 mb-3">이 번호들은 생성 시 제외됩니다</p>
          <div class="grid grid-cols-9 gap-2">
            <button
              v-for="number in 45"
              :key="number"
              @click="toggleExcludeNumber(number)"
              :class="[
                'w-10 h-10 text-sm font-medium rounded-lg border-2 transition-all duration-200',
                excludeNumbers.includes(number)
                  ? 'bg-red-500 text-white border-red-500 shadow-lg transform scale-105'
                  : 'bg-white text-gray-700 border-gray-300 hover:border-red-400 hover:bg-red-50'
              ]"
            >
              {{ number }}
            </button>
          </div>
          <p class="text-sm text-red-600 mt-3 font-medium">
            제외된 번호: {{ excludeNumbers.length }}개 
            <span v-if="excludeNumbers.length > 0" class="ml-2">
              {{ excludeNumbers.sort((a, b) => a - b).join(', ') }}
            </span>
          </p>
        </div>
      </div>

      <!-- 반자동 모드 선호 번호 -->
      <div v-if="selectedType === 'semi'" class="mb-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
          <label class="block text-lg font-bold text-green-800 mb-3 flex items-center">
            <span class="text-2xl mr-2">⭐</span>
            선호 번호 선택 (최대 5개)
          </label>
          <p class="text-sm text-green-600 mb-3">이 번호들이 우선적으로 포함됩니다</p>
          <div class="grid grid-cols-9 gap-2">
            <button
              v-for="number in 45"
              :key="number"
              @click="togglePreferredNumber(number)"
              :class="[
                'w-10 h-10 text-sm font-medium rounded-lg border-2 transition-all duration-200',
                preferredNumbers.includes(number)
                  ? 'bg-green-500 text-white border-green-500 shadow-lg transform scale-105'
                  : excludeNumbers.includes(number)
                  ? 'bg-gray-100 text-gray-400 border-gray-200 cursor-not-allowed opacity-50'
                  : 'bg-white text-gray-700 border-gray-300 hover:border-green-400 hover:bg-green-50'
              ]"
              :disabled="excludeNumbers.includes(number)"
            >
              {{ number }}
            </button>
          </div>
          <p class="text-sm text-green-600 mt-3 font-medium">
            선택한 번호: {{ preferredNumbers.length }}/5개
            <span v-if="preferredNumbers.length > 0" class="ml-2">
              {{ preferredNumbers.sort((a, b) => a - b).join(', ') }}
            </span>
          </p>
        </div>
      </div>

      <!-- 운세 기반 모드 -->
      <div v-if="selectedType === 'fortune' && !currentAnalysis" class="mb-4">
        <div class="bg-yellow-50 border border-yellow-200 rounded p-4">
          <p class="text-yellow-800">
            운세 기반 번호 생성을 위해서는 먼저 사주 분석이 필요합니다.
          </p>
          <router-link
            to="/analysis"
            class="text-yellow-900 font-medium hover:underline"
          >
            사주 분석하러 가기 →
          </router-link>
        </div>
      </div>

      <!-- 생성 개수 -->
      <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          생성할 번호 개수
        </label>
        <select
          v-model="generateCount"
          class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="1">1개</option>
          <option value="3">3개</option>
          <option value="5">5개</option>
          <option value="10">10개</option>
        </select>
      </div>

      <!-- 생성 버튼 -->
      <button
        @click="generateNumbers"
        :disabled="isLoading || (selectedType === 'fortune' && !currentAnalysis)"
        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
      >
        {{ isLoading ? '생성 중...' : '번호 생성하기' }}
      </button>
    </div>

    <!-- 생성된 번호 결과 -->
    <div v-if="generatedNumbers.length > 0" class="bg-white rounded-lg shadow-md p-6">
      <h3 class="text-lg font-semibold mb-4">생성된 번호</h3>
      <div class="space-y-4">
        <div
          v-for="(numberSet, index) in generatedNumbers"
          :key="numberSet.id"
          class="border rounded-lg p-4"
        >
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm text-gray-600">
              {{ getTypeText(numberSet.type) }} | {{ formatDate(numberSet.created_at) }}
            </span>
            <button
              @click="saveNumberSet(numberSet.numbers, numberSet.type)"
              class="text-blue-600 hover:text-blue-800 text-sm font-medium"
            >
              저장
            </button>
          </div>
          
          <div class="flex items-center space-x-2">
            <div
              v-for="number in numberSet.numbers"
              :key="number"
              class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
              :class="getBallColor(number)"
            >
              {{ number }}
            </div>
          </div>
          
          <div v-if="numberSet.fortuneAnalysis" class="mt-3 text-sm text-gray-600">
            <p><strong>운세 점수:</strong> {{ numberSet.fortuneAnalysis.fortuneScore }}/100</p>
            <p class="mt-1">{{ numberSet.fortuneAnalysis.analysis }}</p>
          </div>
        </div>
      </div>
      
      <div class="mt-4 text-center">
        <button
          @click="clearNumbers"
          class="text-gray-500 hover:text-gray-700 text-sm"
        >
          모든 번호 지우기
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRoute } from 'vue-router'

const store = useStore()
const route = useRoute()

const selectedType = ref<'auto' | 'semi' | 'fortune'>('auto')
const excludeNumbers = ref<number[]>([])
const preferredNumbers = ref<number[]>([])
const generateCount = ref(1)

const generatedNumbers = computed(() => store.getters['lotto/generatedNumbers'])
const currentAnalysis = computed(() => store.getters['fortune/currentAnalysis'])
const isLoading = computed(() => store.getters['lotto/isLoading'])

const toggleExcludeNumber = (number: number) => {
  const index = excludeNumbers.value.indexOf(number)
  if (index > -1) {
    excludeNumbers.value.splice(index, 1)
  } else {
    excludeNumbers.value.push(number)
    // 제외 번호에 추가하면 선호 번호에서 제거
    const prefIndex = preferredNumbers.value.indexOf(number)
    if (prefIndex > -1) {
      preferredNumbers.value.splice(prefIndex, 1)
    }
  }
}

const togglePreferredNumber = (number: number) => {
  if (excludeNumbers.value.includes(number)) return
  
  const index = preferredNumbers.value.indexOf(number)
  if (index > -1) {
    preferredNumbers.value.splice(index, 1)
  } else if (preferredNumbers.value.length < 5) {
    preferredNumbers.value.push(number)
  }
}

const generateNumbers = async () => {
  const options: any = {
    type: selectedType.value,
    count: generateCount.value
  }
  
  if (selectedType.value === 'semi' && preferredNumbers.value.length > 0) {
    options.preferred_numbers = preferredNumbers.value
  }
  
  const result = await store.dispatch('lotto/generateNumbers', options)
  
  if (!result.success) {
    alert(`번호 생성에 실패했습니다: ${result.message}`)
  }
}

const saveNumberSet = async (numbers: number[], type: string) => {
  const result = await store.dispatch('lotto/saveNumber', { 
    numbers, 
    type, 
    memo: `${getTypeText(type)} 생성 번호` 
  })
  if (result.success) {
    alert('번호가 저장되었습니다!')
  } else {
    alert(`저장에 실패했습니다: ${result.message}`)
  }
}

const clearNumbers = () => {
  store.commit('lotto/CLEAR_GENERATED_NUMBERS')
}

const getBallColor = (number: number) => {
  if (number <= 10) return 'bg-yellow-500'
  if (number <= 20) return 'bg-blue-500'
  if (number <= 30) return 'bg-red-500'
  if (number <= 40) return 'bg-gray-600'
  return 'bg-green-500'
}

const getTypeText = (type: string) => {
  const texts = {
    auto: '자동',
    semi: '반자동',
    manual: '수동',
    fortune: '운세기반'
  }
  return texts[type as keyof typeof texts] || type
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleString('ko-KR')
}

onMounted(() => {
  // URL 파라미터에서 타입 설정
  const type = route.query.type as string
  if (type && ['auto', 'semi', 'fortune'].includes(type)) {
    selectedType.value = type as 'auto' | 'semi' | 'fortune'
  }
  
  // 운세 분석 데이터 가져오기
  if (selectedType.value === 'fortune') {
    store.dispatch('fortune/fetchAnalysisHistory')
  }
})
</script>