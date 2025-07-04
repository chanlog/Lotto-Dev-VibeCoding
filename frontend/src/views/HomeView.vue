<template>
  <div class="space-y-8">
    <!-- 히어로 섹션 -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-xl p-8 text-white">
      <div class="text-center">
        <h1 class="text-4xl font-bold mb-4">🎯 사주 기반 로또 분석 서비스</h1>
        <p class="text-xl mb-6">
          당신의 사주를 분석하여 맞춤형 로또 번호를 추천해드립니다
        </p>
        <div class="flex justify-center space-x-4">
          <router-link
            to="/generate"
            class="bg-white text-blue-600 font-semibold px-6 py-3 rounded-lg hover:bg-gray-100 transition duration-200"
          >
            번호 생성하기
          </router-link>
          <router-link
            to="/analysis"
            class="border-2 border-white text-white font-semibold px-6 py-3 rounded-lg hover:bg-white hover:text-blue-600 transition duration-200"
          >
            분석 결과 보기
          </router-link>
        </div>
      </div>
    </div>

    <!-- 최신 당첨번호 섹션 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-gray-900">🏆 최신 당첨번호</h2>
        <button
          @click="refreshLatestNumbers"
          class="text-blue-600 hover:text-blue-800 font-medium"
          :disabled="isLoading"
        >
          {{ isLoading ? '로딩중...' : '새로고침' }}
        </button>
      </div>
      
      <div v-if="latestNumbers" class="space-y-4">
        <div class="flex items-center justify-between">
          <div class="text-lg font-semibold">
            제 {{ latestNumbers.round }}회 ({{ formatDate(latestNumbers.date) }})
          </div>
          <div class="text-sm text-gray-500">
            총 판매금액: {{ formatCurrency(latestNumbers.totalSales) }}
          </div>
        </div>
        
        <div class="flex items-center space-x-2">
          <div class="flex space-x-2">
            <div
              v-for="number in latestNumbers.numbers"
              :key="number"
              class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
              :class="getBallColor(number)"
            >
              {{ number }}
            </div>
          </div>
          <div class="text-gray-400 text-lg">+</div>
          <div
            class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center text-white font-bold"
          >
            {{ latestNumbers.bonusNumber }}
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
          <div
            v-for="winner in latestNumbers.winners"
            :key="winner.rank"
            class="bg-gray-50 rounded-lg p-4 text-center"
          >
            <div class="text-sm text-gray-600">{{ getRankText(winner.rank) }}</div>
            <div class="text-lg font-bold">{{ winner.count }}명</div>
            <div class="text-sm text-blue-600">{{ formatCurrency(winner.prize) }}</div>
          </div>
        </div>
      </div>
      
      <div v-else class="text-center py-8 text-gray-500">
        당첨번호 정보를 불러오는 중입니다...
      </div>
    </div>

    <!-- 통계 대시보드 -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">🔥 인기 번호</h3>
        <div v-if="statistics" class="space-y-2">
          <div
            v-for="number in statistics.hottestNumbers.slice(0, 5)"
            :key="number"
            class="flex items-center justify-between"
          >
            <div class="flex items-center space-x-2">
              <div
                class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold"
                :class="getBallColor(number)"
              >
                {{ number }}
              </div>
              <span>{{ number }}번</span>
            </div>
            <span class="text-sm text-gray-500">
              {{ statistics.numberFrequency[number] }}회
            </span>
          </div>
        </div>
        <div v-else class="text-gray-500 text-sm">통계 로딩중...</div>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">❄️ 차가운 번호</h3>
        <div v-if="statistics" class="space-y-2">
          <div
            v-for="number in statistics.coldestNumbers.slice(0, 5)"
            :key="number"
            class="flex items-center justify-between"
          >
            <div class="flex items-center space-x-2">
              <div
                class="w-6 h-6 rounded-full flex items-center justify-center text-white text-xs font-bold"
                :class="getBallColor(number)"
              >
                {{ number }}
              </div>
              <span>{{ number }}번</span>
            </div>
            <span class="text-sm text-gray-500">
              {{ statistics.numberFrequency[number] }}회
            </span>
          </div>
        </div>
        <div v-else class="text-gray-500 text-sm">통계 로딩중...</div>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold mb-4">📊 내 기록</h3>
        <div v-if="isAuthenticated && userTickets.length > 0" class="space-y-2">
          <div class="text-sm text-gray-600">
            총 구매 횟수: {{ userTickets.length }}회
          </div>
          <div class="text-sm text-gray-600">
            최근 구매일: {{ formatDate(userTickets[0]?.purchaseDate) }}
          </div>
          <router-link
            to="/profile"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            상세 보기 →
          </router-link>
        </div>
        <div v-else-if="!isAuthenticated" class="text-gray-500 text-sm">
          <p class="mb-2">로그인하여 내 기록을 확인하세요</p>
          <router-link
            to="/login"
            class="text-blue-600 hover:text-blue-800 text-sm font-medium"
          >
            로그인 →
          </router-link>
        </div>
        <div v-else class="text-gray-500 text-sm">
          아직 구매 기록이 없습니다
        </div>
      </div>
    </div>

    <!-- 빠른 실행 버튼들 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h3 class="text-lg font-semibold mb-4">⚡ 빠른 실행</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <router-link
          to="/generate?type=auto"
          class="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-center transition duration-200"
        >
          <div class="text-2xl mb-2">🎲</div>
          <div class="font-medium text-blue-900">자동 번호</div>
          <div class="text-sm text-blue-600">완전 랜덤 생성</div>
        </router-link>
        
        <router-link
          to="/generate?type=semi"
          class="bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-center transition duration-200"
        >
          <div class="text-2xl mb-2">🎯</div>
          <div class="font-medium text-green-900">반자동 번호</div>
          <div class="text-sm text-green-600">선호 번호 + 자동</div>
        </router-link>
        
        <router-link
          to="/generate?type=fortune"
          class="bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg p-4 text-center transition duration-200"
        >
          <div class="text-2xl mb-2">🔮</div>
          <div class="font-medium text-purple-900">운세 기반</div>
          <div class="text-sm text-purple-600">사주 분석 기반</div>
        </router-link>
        
        <router-link
          to="/statistics"
          class="bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-lg p-4 text-center transition duration-200"
        >
          <div class="text-2xl mb-2">📈</div>
          <div class="font-medium text-yellow-900">통계 분석</div>
          <div class="text-sm text-yellow-600">패턴 분석 보기</div>
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useStore } from 'vuex'

const store = useStore()

const latestNumbers = computed(() => store.getters['lotto/latestNumbers'])
const statistics = computed(() => store.getters['lotto/statistics'])
const userTickets = computed(() => store.getters['lotto/userTickets'])
const isAuthenticated = computed(() => store.getters['auth/isAuthenticated'])
const isLoading = computed(() => store.getters['lotto/isLoading'])

const refreshLatestNumbers = async () => {
  await store.dispatch('lotto/fetchLatestNumbers')
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('ko-KR')
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('ko-KR', {
    style: 'currency',
    currency: 'KRW'
  }).format(amount)
}

const getBallColor = (number: number) => {
  if (number <= 10) return 'bg-yellow-500'
  if (number <= 20) return 'bg-blue-500'
  if (number <= 30) return 'bg-red-500'
  if (number <= 40) return 'bg-gray-600'
  return 'bg-green-500'
}

const getRankText = (rank: number) => {
  const texts = {
    1: '1등',
    2: '2등',
    3: '3등',
    4: '4등',
    5: '5등'
  }
  return texts[rank as keyof typeof texts] || `${rank}등`
}

onMounted(() => {
  // 초기 데이터 로드
  store.dispatch('lotto/fetchLatestNumbers')
  store.dispatch('lotto/fetchStatistics')
  
  if (isAuthenticated.value) {
    store.dispatch('lotto/fetchUserTickets')
  }
})
</script>