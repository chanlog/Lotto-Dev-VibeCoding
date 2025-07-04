<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-blue-100">
          <span class="text-2xl">🎯</span>
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          회원가입
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          또는
          <router-link to="/login" class="font-medium text-blue-600 hover:text-blue-500">
            기존 계정으로 로그인
          </router-link>
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">
              이름
            </label>
            <input
              id="name"
              name="name"
              type="text"
              autocomplete="name"
              required
              v-model="registerForm.name"
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="이름을 입력하세요"
            />
          </div>

          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
              이메일 주소
            </label>
            <input
              id="email"
              name="email"
              type="email"
              autocomplete="email"
              required
              v-model="registerForm.email"
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="이메일을 입력하세요"
            />
          </div>
          
          <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
              비밀번호
            </label>
            <input
              id="password"
              name="password"
              type="password"
              autocomplete="new-password"
              required
              v-model="registerForm.password"
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="비밀번호를 입력하세요 (8자 이상)"
            />
            <div class="mt-1">
              <div class="flex space-x-1">
                <div 
                  v-for="(check, index) in passwordChecks"
                  :key="index"
                  :class="[
                    'h-1 flex-1 rounded',
                    check ? 'bg-green-500' : 'bg-gray-300'
                  ]"
                ></div>
              </div>
              <p class="text-xs text-gray-500 mt-1">
                8자 이상, 영문/숫자/특수문자 포함
              </p>
            </div>
          </div>
          
          <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
              비밀번호 확인
            </label>
            <input
              id="password_confirmation"
              name="password_confirmation"
              type="password"
              autocomplete="new-password"
              required
              v-model="registerForm.password_confirmation"
              class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
              placeholder="비밀번호를 다시 입력하세요"
            />
            <p v-if="registerForm.password_confirmation && !passwordMatch" class="text-xs text-red-500 mt-1">
              비밀번호가 일치하지 않습니다.
            </p>
          </div>
        </div>

        <div class="flex items-center">
          <input
            id="agree-terms"
            name="agree-terms"
            type="checkbox"
            required
            v-model="agreeTerms"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="agree-terms" class="ml-2 block text-sm text-gray-900">
            <span class="text-blue-600 hover:text-blue-500 cursor-pointer">이용약관</span> 및 
            <span class="text-blue-600 hover:text-blue-500 cursor-pointer">개인정보처리방침</span>에 동의합니다
          </label>
        </div>

        <div v-if="errorMessage" class="bg-red-50 border border-red-200 rounded-md p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <span class="text-red-400">⚠️</span>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-700">{{ errorMessage }}</p>
            </div>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="!canSubmit || isLoading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isLoading ? '가입 중...' : '회원가입' }}
          </button>
        </div>

        <div class="text-center">
          <p class="text-xs text-gray-500">
            회원가입 시 위 약관에 동의한 것으로 간주됩니다.<br/>
            본 서비스는 만 18세 이상만 이용 가능합니다.
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'

const store = useStore()
const router = useRouter()

const registerForm = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const agreeTerms = ref(false)
const errorMessage = ref('')

const isLoading = computed(() => store.getters['auth/isLoading'])

const passwordChecks = computed(() => {
  const password = registerForm.value.password
  return [
    password.length >= 8, // 길이
    /[a-zA-Z]/.test(password), // 영문
    /[0-9]/.test(password), // 숫자
    /[^a-zA-Z0-9]/.test(password) // 특수문자
  ]
})

const passwordStrength = computed(() => {
  return passwordChecks.value.filter(Boolean).length
})

const passwordMatch = computed(() => {
  if (!registerForm.value.password_confirmation) return true
  return registerForm.value.password === registerForm.value.password_confirmation
})

const canSubmit = computed(() => {
  return registerForm.value.name &&
         registerForm.value.email &&
         registerForm.value.password &&
         registerForm.value.password_confirmation &&
         passwordMatch.value &&
         passwordStrength.value >= 3 &&
         agreeTerms.value
})

const handleRegister = async () => {
  errorMessage.value = ''
  
  if (!canSubmit.value) {
    errorMessage.value = '모든 필드를 올바르게 입력하고 약관에 동의해주세요.'
    return
  }
  
  if (passwordStrength.value < 3) {
    errorMessage.value = '비밀번호는 영문, 숫자, 특수문자를 포함하여 8자 이상이어야 합니다.'
    return
  }
  
  const result = await store.dispatch('auth/register', {
    name: registerForm.value.name,
    email: registerForm.value.email,
    password: registerForm.value.password,
    password_confirmation: registerForm.value.password_confirmation
  })
  
  if (result.success) {
    router.push('/')
  } else {
    errorMessage.value = result.message
  }
}
</script>