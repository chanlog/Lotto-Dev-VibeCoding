<template>
  <div class="space-y-6">
    <div class="text-center">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">👤 마이페이지</h1>
      <p class="text-gray-600">내 정보와 로또 구매 기록을 확인하세요</p>
    </div>

    <!-- 사용자 정보 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">사용자 정보</h2>
      <div v-if="currentUser" class="space-y-4">
        <div class="flex items-center space-x-4">
          <div class="w-16 h-16 rounded-full bg-blue-500 flex items-center justify-center text-white text-2xl font-bold">
            {{ currentUser.name.charAt(0).toUpperCase() }}
          </div>
          <div>
            <h3 class="text-lg font-medium">{{ currentUser.name }}</h3>
            <p class="text-gray-600">{{ currentUser.email }}</p>
            <p class="text-sm text-gray-500">가입일: {{ formatDate(currentUser.created_at) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- 구매 통계 -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-lg shadow-md p-6 text-center">
        <div class="text-3xl mb-2">🎫</div>
        <div class="text-2xl font-bold text-blue-600">{{ userTickets.length }}</div>
        <div class="text-sm text-gray-600">총 구매 횟수</div>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6 text-center">
        <div class="text-3xl mb-2">💰</div>
        <div class="text-2xl font-bold text-green-600">{{ totalSpent }}</div>
        <div class="text-sm text-gray-600">총 구매 금액</div>
      </div>
      
      <div class="bg-white rounded-lg shadow-md p-6 text-center">
        <div class="text-3xl mb-2">🏆</div>
        <div class="text-2xl font-bold text-purple-600">{{ winCount }}</div>
        <div class="text-sm text-gray-600">당첨 횟수</div>
      </div>
    </div>

    <!-- 최근 구매 내역 -->
    <div class="bg-white rounded-lg shadow-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-semibold">최근 구매 내역</h2>
        <button
          @click="refreshTickets"
          class="text-blue-600 hover:text-blue-800 text-sm font-medium"
          :disabled="isLoading"
        >
          {{ isLoading ? '로딩중...' : '새로고침' }}
        </button>
      </div>
      
      <div v-if="userTickets.length > 0" class="space-y-4">
        <div
          v-for="ticket in paginatedTickets"
          :key="ticket.id"
          class="border rounded-lg p-4"
        >
          <div class="flex items-center justify-between mb-3">
            <div class="text-sm text-gray-600">
              구매일: {{ formatDate(ticket.purchaseDate) }}
            </div>
            <div class="text-sm font-medium">
              제 {{ ticket.round }}회
            </div>
          </div>
          
          <div class="flex items-center space-x-2 mb-3">
            <div
              v-for="number in ticket.numbers"
              :key="number"
              class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold"
              :class="getBallColor(number)"
            >
              {{ number }}
            </div>
          </div>
          
          <div v-if="ticket.result" class="flex items-center justify-between">
            <div class="text-sm">
              <span class="font-medium">당첨 결과:</span>
              <span v-if="ticket.result.rank" class="text-green-600 font-bold ml-2">
                {{ ticket.result.rank }}등 당첨!
              </span>
              <span v-else class="text-gray-500 ml-2">
                {{ ticket.result.matchCount }}개 일치
              </span>
            </div>
            <div v-if="ticket.result.prize > 0" class="text-sm font-bold text-green-600">
              {{ formatCurrency(ticket.result.prize) }}
            </div>
          </div>
          <div v-else class="text-sm text-gray-500">
            추첨 대기중
          </div>
        </div>
        
        <!-- 페이지네이션 -->
        <div v-if="totalPages > 1" class="flex justify-center space-x-2 mt-6">
          <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-3 py-1 border rounded disabled:opacity-50 disabled:cursor-not-allowed"
          >
            이전
          </button>
          
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="currentPage = page"
            :class="[
              'px-3 py-1 border rounded',
              currentPage === page 
                ? 'bg-blue-600 text-white' 
                : 'hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
          
          <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 border rounded disabled:opacity-50 disabled:cursor-not-allowed"
          >
            다음
          </button>
        </div>
      </div>
      
      <div v-else class="text-center py-8 text-gray-500">
        <div class="text-4xl mb-4">📝</div>
        <p class="mb-4">아직 구매한 로또가 없습니다</p>
        <router-link
          to="/generate"
          class="text-blue-600 hover:text-blue-800 font-medium"
        >
          첫 번호 생성하러 가기 →
        </router-link>
      </div>
    </div>

    <!-- 당첨 통계 -->
    <div v-if="winningStats.length > 0" class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">당첨 통계</h2>
      <div class="space-y-3">
        <div
          v-for="stat in winningStats"
          :key="stat.rank"
          class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
        >
          <div class="flex items-center space-x-3">
            <div class="text-2xl">{{ getRankEmoji(stat.rank) }}</div>
            <div>
              <div class="font-medium">{{ stat.rank }}등</div>
              <div class="text-sm text-gray-600">{{ stat.count }}회</div>
            </div>
          </div>
          <div class="text-right">
            <div class="font-bold text-green-600">
              {{ formatCurrency(stat.totalPrize) }}
            </div>
            <div class="text-sm text-gray-500">
              평균 {{ formatCurrency(stat.averagePrize) }}
            </div>
          </div>
        </div>
      </div>
      
      <div class="mt-4 pt-4 border-t">
        <div class="flex justify-between items-center">
          <span class="font-medium">총 당첨금액</span>
          <span class="text-xl font-bold text-green-600">
            {{ formatCurrency(totalWinnings) }}
          </span>
        </div>
        <div class="flex justify-between items-center mt-2">
          <span class="text-sm text-gray-600">수익률</span>
          <span 
            :class="[
              'text-sm font-medium',
              profitRate >= 0 ? 'text-green-600' : 'text-red-600'
            ]"
          >
            {{ profitRate >= 0 ? '+' : '' }}{{ profitRate }}%
          </span>
        </div>
      </div>
    </div>

    <!-- 선호 번호 분석 -->
    <div v-if="favoriteNumbers.length > 0" class="bg-white rounded-lg shadow-md p-6">
      <h2 class="text-xl font-semibold mb-4">내가 자주 선택하는 번호</h2>
      <div class="grid grid-cols-6 md:grid-cols-10 gap-2">
        <div
          v-for="(item, index) in favoriteNumbers.slice(0, 10)"
          :key="item.number"
          class="text-center"
        >
          <div
            class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold mx-auto"
            :class="getBallColor(item.number)"
          >
            {{ item.number }}
          </div>
          <div class="text-xs text-gray-500 mt-1">{{ item.count }}회</div>
          <div class="text-xs text-gray-400">{{ index + 1 }}위</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'

const store = useStore()

const currentPage = ref(1)
const itemsPerPage = 10

const currentUser = computed(() => store.getters['auth/currentUser'])
const userTickets = computed(() => store.getters['lotto/userTickets'])
const isLoading = computed(() => store.getters['lotto/isLoading'])

const totalSpent = computed(() => {
  return formatCurrency(userTickets.value.length * 1000) // 로또 1장당 1000원
})

const winCount = computed(() => {
  return userTickets.value.filter((ticket: any) => ticket.result?.rank).length
})

const totalWinnings = computed(() => {
  return userTickets.value.reduce((total: number, ticket: any) => {
    return total + (ticket.result?.prize || 0)
  }, 0)
})

const profitRate = computed(() => {
  const spent = userTickets.value.length * 1000
  if (spent === 0) return 0
  return Math.round(((totalWinnings.value - spent) / spent) * 100)
})

const winningStats = computed(() => {
  const stats: { [key: number]: { count: number, totalPrize: number } } = {}
  
  userTickets.value.forEach((ticket: any) => {
    if (ticket.result?.rank) {
      const rank = ticket.result.rank
      if (!stats[rank]) {
        stats[rank] = { count: 0, totalPrize: 0 }
      }
      stats[rank].count++
      stats[rank].totalPrize += ticket.result.prize || 0
    }
  })
  
  return Object.entries(stats).map(([rank, data]) => ({
    rank: Number(rank),
    count: data.count,
    totalPrize: data.totalPrize,
    averagePrize: data.totalPrize / data.count
  })).sort((a, b) => a.rank - b.rank)
})

const favoriteNumbers = computed(() => {
  const numberCount: { [key: number]: number } = {}
  
  userTickets.value.forEach((ticket: any) => {
    ticket.numbers.forEach((number: number) => {
      numberCount[number] = (numberCount[number] || 0) + 1
    })
  })
  
  return Object.entries(numberCount)
    .map(([number, count]) => ({ number: Number(number), count }))
    .sort((a, b) => b.count - a.count)
})

const totalPages = computed(() => {
  return Math.ceil(userTickets.value.length / itemsPerPage)
})

const paginatedTickets = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return userTickets.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

const refreshTickets = async () => {
  await store.dispatch('lotto/fetchUserTickets')
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

const getRankEmoji = (rank: number) => {
  const emojis = {
    1: '🥇',
    2: '🥈', 
    3: '🥉',
    4: '🏅',
    5: '🎖️'
  }
  return emojis[rank as keyof typeof emojis] || '🎯'
}

onMounted(() => {
  if (currentUser.value) {
    store.dispatch('lotto/fetchUserTickets')
  }
})
</script>