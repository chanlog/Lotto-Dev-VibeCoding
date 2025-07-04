<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- 네비게이션 바 -->
    <nav class="bg-white shadow-lg">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between h-16">
          <div class="flex">
            <!-- 로고 -->
            <div class="flex-shrink-0 flex items-center">
              <router-link to="/" class="text-2xl font-bold text-blue-600">
                🎯 로또분석
              </router-link>
            </div>
            
            <!-- 네비게이션 메뉴 -->
            <div class="hidden md:ml-6 md:flex md:space-x-8">
              <router-link
                to="/"
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                :class="{ 'border-blue-500 text-gray-900': $route.name === 'Home' }"
              >
                홈
              </router-link>
              
              <router-link
                to="/generate"
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                :class="{ 'border-blue-500 text-gray-900': $route.name === 'Generate' }"
              >
                번호생성
              </router-link>
              
              <router-link
                to="/analysis"
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                :class="{ 'border-blue-500 text-gray-900': $route.name === 'Analysis' }"
              >
                분석결과
              </router-link>
              
              <router-link
                to="/statistics"
                class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium"
                :class="{ 'border-blue-500 text-gray-900': $route.name === 'Statistics' }"
              >
                통계
              </router-link>
            </div>
          </div>
          
          <!-- 사용자 메뉴 -->
          <div class="hidden md:ml-6 md:flex md:items-center">
            <div v-if="isAuthenticated" class="relative">
              <button
                @click="showUserMenu = !showUserMenu"
                class="bg-gray-800 flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white"
              >
                <span class="sr-only">사용자 메뉴 열기</span>
                <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-medium">
                  {{ currentUser?.name?.charAt(0).toUpperCase() }}
                </div>
              </button>
              
              <!-- 드롭다운 메뉴 -->
              <div
                v-if="showUserMenu"
                class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50"
              >
                <router-link
                  to="/profile"
                  class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                  @click="showUserMenu = false"
                >
                  마이페이지
                </router-link>
                <button
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                >
                  로그아웃
                </button>
              </div>
            </div>
            
            <div v-else class="flex space-x-4">
              <router-link
                to="/login"
                class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
              >
                로그인
              </router-link>
              <router-link
                to="/register"
                class="bg-blue-600 text-white hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium"
              >
                회원가입
              </router-link>
            </div>
          </div>
          
          <!-- 모바일 메뉴 버튼 -->
          <div class="md:hidden flex items-center">
            <button
              @click="showMobileMenu = !showMobileMenu"
              class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500"
            >
              <span class="sr-only">메뉴 열기</span>
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path
                  :class="{ 'hidden': showMobileMenu, 'inline-flex': !showMobileMenu }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"
                />
                <path
                  :class="{ 'hidden': !showMobileMenu, 'inline-flex': showMobileMenu }"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>
      
      <!-- 모바일 메뉴 -->
      <div v-if="showMobileMenu" class="md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
          <router-link
            to="/"
            class="text-gray-500 hover:text-gray-700 block px-3 py-2 rounded-md text-base font-medium"
            @click="showMobileMenu = false"
          >
            홈
          </router-link>
          <router-link
            to="/generate"
            class="text-gray-500 hover:text-gray-700 block px-3 py-2 rounded-md text-base font-medium"
            @click="showMobileMenu = false"
          >
            번호생성
          </router-link>
          <router-link
            to="/analysis"
            class="text-gray-500 hover:text-gray-700 block px-3 py-2 rounded-md text-base font-medium"
            @click="showMobileMenu = false"
          >
            분석결과
          </router-link>
          <router-link
            to="/statistics"
            class="text-gray-500 hover:text-gray-700 block px-3 py-2 rounded-md text-base font-medium"
            @click="showMobileMenu = false"
          >
            통계
          </router-link>
        </div>
      </div>
    </nav>
    
    <!-- 메인 콘텐츠 -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <router-view />
    </main>
    
    <!-- 푸터 -->
    <footer class="bg-gray-800 text-white mt-auto">
      <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="text-center">
          <p class="text-sm text-gray-400">
            © 2024 로또 분석 서비스. All rights reserved.
          </p>
          <p class="text-xs text-gray-500 mt-2">
            본 서비스는 참고용이며, 당첨을 보장하지 않습니다.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'

const store = useStore()
const router = useRouter()

const showUserMenu = ref(false)
const showMobileMenu = ref(false)

const isAuthenticated = computed(() => store.getters['auth/isAuthenticated'])
const currentUser = computed(() => store.getters['auth/currentUser'])

const handleLogout = async () => {
  await store.dispatch('auth/logout')
  showUserMenu.value = false
  router.push('/')
}

// 초기 사용자 정보 가져오기
onMounted(() => {
  store.dispatch('auth/fetchUser')
})

// 클릭 외부 감지로 메뉴 닫기
const handleClickOutside = (event: Event) => {
  const target = event.target as HTMLElement
  if (!target.closest('.relative')) {
    showUserMenu.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})
</script>