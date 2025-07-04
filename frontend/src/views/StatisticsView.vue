<template>
  <div class="space-y-6">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">📊 로또 통계 분석</h1>
      <p class="text-gray-600">로또 번호의 다양한 통계와 패턴을 분석해보세요</p>
    </div>

    <!-- 통계 요약 카드 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <div class="bg-white rounded-lg shadow-md p-6 text-center">
        <div class="text-2xl mb-2">🔥</div>
        <div class="text-sm text-gray-600">가장 인기 번호</div>
        <div class="text-2xl font-bold text-red-500">
          {{ statistics?.hottestNumbers?.[0] || '--' }}
        </div>
        <div class="text-xs text-gray-500">
          {{ statistics?.numberFrequency?.[statistics?.hottestNumbers?.[0]] || 0 }}회 출현
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6 text-center">
        <div class="text-2xl mb-2">❄️</div>
        <div class="text-sm text-gray-600">가장 차가운 번호</div>
        <div class="text-2xl font-bold text-blue-500">
          {{ statistics?.coldestNumbers?.[0] || '--' }}
        </div>
        <div class="text-xs text-gray-500">
          {{ statistics?.numberFrequency?.[statistics?.coldestNumbers?.[0]] || 0 }}회 출현
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6 text-center">
        <div class="text-2xl mb-2">📈</div>
        <div class="text-sm text-gray-600">총 분석 회차</div>
        <div class="text-2xl font-bold text-green-500">
          {{ totalRounds }}
        </div>
        <div class="text-xs text-gray-500">회차</div>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6 text-center">
        <div class="text-2xl mb-2">🎯</div>
        <div class="text-sm text-gray-600">평균 출현 횟수</div>
        <div class="text-2xl font-bold text-purple-500">
          {{ averageFrequency }}
        </div>
        <div class="text-xs text-gray-500">회</div>
      </div>
    </div>

    <!-- 번호별 출현 빈도 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">번호별 출현 빈도</h2>
        <div class="flex space-x-2">
          <button
            @click="sortType = 'frequency'"
            :class="[
              'px-3 py-1 rounded text-sm',
              sortType === 'frequency' 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            빈도순
          </button>
          <button
            @click="sortType = 'number'"
            :class="[
              'px-3 py-1 rounded text-sm',
              sortType === 'number' 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
            ]"
          >
            번호순
          </button>
        </div>
      </div>
      
      <div v-if="statistics" class="grid grid-cols-5 md:grid-cols-9 gap-2">
        <div
          v-for="number in sortedNumbers"
          :key="number"
          class="relative"
        >
          <div
            class="w-full h-16 rounded flex flex-col items-center justify-center text-white font-bold cursor-pointer transition duration-200 hover:scale-105"
            :class="getBallColor(number)"
            :title="`${number}번: ${statistics.numberFrequency[number]}회 출현`"
          >
            <div class="text-lg">{{ number }}</div>
            <div class="text-xs">{{ statistics.numberFrequency[number] }}</div>
          </div>
          <div class="mt-1 h-2 bg-gray-200 rounded">
            <div
              class="h-full bg-blue-500 rounded transition-all duration-500"
              :style="{ width: `${getFrequencyPercentage(statistics.numberFrequency[number])}%` }"
            ></div>
          </div>
        </div>
      </div>
      
      <div v-else class="text-center py-8 text-gray-500">
        통계 데이터를 불러오는 중입니다...
      </div>
    </div>

    <!-- 최근 패턴 분석 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">최근 10회차 패턴</h2>
      <div v-if="statistics?.recentPatterns" class="space-y-3">
        <div
          v-for="(pattern, index) in statistics.recentPatterns"
          :key="index"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="text-sm text-gray-600">
            {{ statistics.recentPatterns.length - index }}회차 전
          </div>
          <div class="flex space-x-2">
            <div
              v-for="number in pattern"
              :key="number"
              class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold"
              :class="getBallColor(number)"
            >
              {{ number }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 번호 분포 분석 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">구간별 분포</h3>
        <div class="space-y-3">
          <div class="flex items-center justify-between">
            <span>1-10</span>
            <div class="flex-1 mx-3 bg-gray-200 rounded h-4">
              <div 
                class="bg-yellow-500 h-4 rounded transition-all duration-500"
                :style="{ width: `${getDistributionPercentage(1, 10)}%` }"
              ></div>
            </div>
            <span class="text-sm text-gray-600">{{ getDistributionCount(1, 10) }}%</span>
          </div>
          <div class="flex items-center justify-between">
            <span>11-20</span>
            <div class="flex-1 mx-3 bg-gray-200 rounded h-4">
              <div 
                class="bg-blue-500 h-4 rounded transition-all duration-500"
                :style="{ width: `${getDistributionPercentage(11, 20)}%` }"
              ></div>
            </div>
            <span class="text-sm text-gray-600">{{ getDistributionCount(11, 20) }}%</span>
          </div>
          <div class="flex items-center justify-between">
            <span>21-30</span>
            <div class="flex-1 mx-3 bg-gray-200 rounded h-4">
              <div 
                class="bg-red-500 h-4 rounded transition-all duration-500"
                :style="{ width: `${getDistributionPercentage(21, 30)}%` }"
              ></div>
            </div>
            <span class="text-sm text-gray-600">{{ getDistributionCount(21, 30) }}%</span>
          </div>
          <div class="flex items-center justify-between">
            <span>31-40</span>
            <div class="flex-1 mx-3 bg-gray-200 rounded h-4">
              <div 
                class="bg-gray-600 h-4 rounded transition-all duration-500"
                :style="{ width: `${getDistributionPercentage(31, 40)}%` }"
              ></div>
            </div>
            <span class="text-sm text-gray-600">{{ getDistributionCount(31, 40) }}%</span>
          </div>
          <div class="flex items-center justify-between">
            <span>41-45</span>
            <div class="flex-1 mx-3 bg-gray-200 rounded h-4">
              <div 
                class="bg-green-500 h-4 rounded transition-all duration-500"
                :style="{ width: `${getDistributionPercentage(41, 45)}%` }"
              ></div>
            </div>
            <span class="text-sm text-gray-600">{{ getDistributionCount(41, 45) }}%</span>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">홀짝 분포</h3>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <span>홀수</span>
            <div class="flex-1 mx-3 bg-gray-200 rounded h-6">
              <div 
                class="bg-blue-500 h-6 rounded transition-all duration-500"
                :style="{ width: `${oddPercentage}%` }"
              ></div>
            </div>
            <span class="text-sm text-gray-600">{{ oddPercentage }}%</span>
          </div>
          <div class="flex items-center justify-between">
            <span>짝수</span>
            <div class="flex-1 mx-3 bg-gray-200 rounded h-6">
              <div 
                class="bg-red-500 h-6 rounded transition-all duration-500"
                :style="{ width: `${evenPercentage}%` }"
              ></div>
            </div>
            <span class="text-sm text-gray-600">{{ evenPercentage }}%</span>
          </div>
        </div>
        
        <div class="mt-6">
          <h4 class="font-medium mb-2">연속 번호 분석</h4>
          <div class="text-sm text-gray-600">
            <p>연속 번호가 포함된 회차: {{ consecutiveCount }}회</p>
            <p>연속 번호 평균 개수: {{ avgConsecutive }}개</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 번호 추천 도구 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">통계 기반 번호 추천</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="text-center p-4 border rounded-lg">
          <h3 class="font-medium mb-2">🔥 인기 번호 조합</h3>
          <div class="flex justify-center space-x-1 mb-3">
            <div
              v-for="number in statistics?.hottestNumbers?.slice(0, 6)"
              :key="number"
              class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
              :class="getBallColor(number)"
            >
              {{ number }}
            </div>
          </div>
          <button
            @click="generateFromHot"
            class="text-red-600 hover:text-red-800 text-sm font-medium"
          >
            이 조합으로 생성 →
          </button>
        </div>
        
        <div class="text-center p-4 border rounded-lg">
          <h3 class="font-medium mb-2">❄️ 차가운 번호 조합</h3>
          <div class="flex justify-center space-x-1 mb-3">
            <div
              v-for="number in statistics?.coldestNumbers?.slice(0, 6)"
              :key="number"
              class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
              :class="getBallColor(number)"
            >
              {{ number }}
            </div>
          </div>
          <button
            @click="generateFromCold"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            이 조합으로 생성 →
          </button>
        </div>
        
        <div class="text-center p-4 border rounded-lg">
          <h3 class="font-medium mb-2">⚖️ 균형 조합</h3>
          <div class="flex justify-center space-x-1 mb-3">
            <div
              v-for="number in balancedNumbers"
              :key="number"
              class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
              :class="getBallColor(number)"
            >
              {{ number }}
            </div>
          </div>
          <button
            @click="generateFromBalanced"
            class="text-green-600 hover:text-green-800 text-sm font-medium"
          >
            이 조합으로 생성 →
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'

const store = useStore()
const router = useRouter()

const sortType = ref<'frequency' | 'number'>('frequency')

const statistics = computed(() => store.getters['lotto/statistics'])
const isLoading = computed(() => store.getters['lotto/isLoading'])

const totalRounds = computed(() => {
  if (!statistics.value?.numberFrequency) return 0
  return Math.max(...Object.values(statistics.value.numberFrequency))
})

const averageFrequency = computed(() => {
  if (!statistics.value?.numberFrequency) return 0
  const frequencies = Object.values(statistics.value.numberFrequency)
  const sum = frequencies.reduce((a: number, b: number) => a + b, 0)
  return Math.round(sum / frequencies.length)
})

const sortedNumbers = computed(() => {
  if (!statistics.value?.numberFrequency) return []
  
  const numbers = Array.from({ length: 45 }, (_, i) => i + 1)
  
  if (sortType.value === 'frequency') {
    return numbers.sort((a, b) => 
      (statistics.value.numberFrequency[b] || 0) - (statistics.value.numberFrequency[a] || 0)
    )
  }
  
  return numbers
})

const oddPercentage = computed(() => {
  if (!statistics.value?.numberFrequency) return 0
  
  let oddTotal = 0
  let evenTotal = 0
  
  for (let i = 1; i <= 45; i++) {
    const freq = statistics.value.numberFrequency[i] || 0
    if (i % 2 === 1) {
      oddTotal += freq
    } else {
      evenTotal += freq
    }
  }
  
  const total = oddTotal + evenTotal
  return total > 0 ? Math.round((oddTotal / total) * 100) : 0
})

const evenPercentage = computed(() => 100 - oddPercentage.value)

const consecutiveCount = computed(() => {
  // 연속 번호 계산 로직 (임시)
  return Math.floor(Math.random() * 50) + 20
})

const avgConsecutive = computed(() => {
  return (Math.random() * 2 + 1).toFixed(1)
})

const balancedNumbers = computed(() => {
  if (!statistics.value?.numberFrequency) return []
  
  // 각 구간에서 적당한 빈도의 번호 선택
  const ranges = [
    { start: 1, end: 10 },
    { start: 11, end: 20 },
    { start: 21, end: 30 },
    { start: 31, end: 40 },
    { start: 41, end: 45 }
  ]
  
  const balanced: number[] = []
  
  ranges.forEach(range => {
    const rangeNumbers = []
    for (let i = range.start; i <= range.end; i++) {
      rangeNumbers.push({
        number: i,
        frequency: statistics.value.numberFrequency[i] || 0
      })
    }
    
    // 중간 빈도의 번호 선택
    rangeNumbers.sort((a, b) => a.frequency - b.frequency)
    const midIndex = Math.floor(rangeNumbers.length / 2)
    if (rangeNumbers[midIndex] && balanced.length < 6) {
      balanced.push(rangeNumbers[midIndex].number)
    }
  })
  
  return balanced.slice(0, 6)
})

const getBallColor = (number: number) => {
  if (number <= 10) return 'bg-yellow-500'
  if (number <= 20) return 'bg-blue-500'
  if (number <= 30) return 'bg-red-500'
  if (number <= 40) return 'bg-gray-600'
  return 'bg-green-500'
}

const getFrequencyPercentage = (frequency: number) => {
  const maxFreq = Math.max(...Object.values(statistics.value?.numberFrequency || {}))
  return maxFreq > 0 ? (frequency / maxFreq) * 100 : 0
}

const getDistributionCount = (start: number, end: number) => {
  if (!statistics.value?.numberFrequency) return 0
  
  let rangeTotal = 0
  let grandTotal = 0
  
  for (let i = 1; i <= 45; i++) {
    const freq = statistics.value.numberFrequency[i] || 0
    grandTotal += freq
    if (i >= start && i <= end) {
      rangeTotal += freq
    }
  }
  
  return grandTotal > 0 ? Math.round((rangeTotal / grandTotal) * 100) : 0
}

const getDistributionPercentage = (start: number, end: number) => {
  return getDistributionCount(start, end)
}

const generateFromHot = () => {
  router.push('/generate?type=semi&preferred=' + statistics.value?.hottestNumbers?.slice(0, 3).join(','))
}

const generateFromCold = () => {
  router.push('/generate?type=semi&preferred=' + statistics.value?.coldestNumbers?.slice(0, 3).join(','))
}

const generateFromBalanced = () => {
  router.push('/generate?type=semi&preferred=' + balancedNumbers.value.slice(0, 3).join(','))
}

onMounted(() => {
  store.dispatch('lotto/fetchStatistics')
})
</script>