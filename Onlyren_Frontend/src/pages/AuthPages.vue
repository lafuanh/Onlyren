<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
      <!-- Tab navigation -->
      <div class="flex border-b border-gray-200 mb-6">
        <button 
          @click="activeTab = 'user'"
          :class="[
            'py-2 px-4 font-medium', 
            activeTab === 'user' ? 'border-b-2 border-black' : 'text-gray-500'
          ]"
        >
          User
        </button>
        <button 
          @click="activeTab = 'renter'"
          :class="[
            'py-2 px-4 font-medium', 
            activeTab === 'renter' ? 'border-b-2 border-black' : 'text-gray-500'
          ]"
        >
          Renter
        </button>
      </div>
      
      <h1 class="text-2xl font-bold text-center mb-6">{{ isLogin ? 'Login' : 'Register' }}</h1>
      
      <!-- Login Form -->
      <form v-if="isLogin" @submit.prevent="handleLogin" class="space-y-6">
        <div>
          <input 
            type="email" 
            v-model="loginForm.email" 
            placeholder="Email"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <div>
          <input 
            type="password" 
            v-model="loginForm.password" 
            placeholder="Password"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <div>
          <button 
            type="submit" 
            class="w-full bg-orange-500 text-white py-2 px-4 rounded hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50"
          >
            Login
          </button>
        </div>
      </form>
      
      <!-- Register Form -->
      <form v-else @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <input 
            type="text" 
            v-model="registerForm.name" 
            placeholder="Full Name"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <div>
          <input 
            type="email" 
            v-model="registerForm.email" 
            placeholder="Email"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <div>
          <input 
            type="password" 
            v-model="registerForm.password" 
            placeholder="Password"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <div>
          <input 
            type="password" 
            v-model="registerForm.passwordConfirmation"
            placeholder="Confirm Password"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-orange-500"
          />
        </div>
        <div>
          <button 
            type="submit" 
            class="w-full bg-orange-500 text-white py-2 px-4 rounded hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50"
          >
            Register
          </button>
        </div>
      </form>
      
      <!-- Toggle between login and register -->
      <div class="mt-4 text-center">
        <button 
          @click="isLogin = !isLogin"
          class="text-sm text-orange-500 hover:underline focus:outline-none"
        >
          {{ isLogin ? 'Need an account? Register' : 'Already have an account? Login' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
// import { login, register } from '@/api/auth' // Uncomment when API is ready

export default {
  name: 'AuthPage',
  setup() {
    const router = useRouter()
    const isLogin = ref(true)
    const activeTab = ref('user')
    
    const loginForm = reactive({
      email: '',
      password: ''
    })
    
    const registerForm = reactive({
      name: '',
      email: '',
      password: '',
      passwordConfirmation: ''
    })
    
    const handleLogin = async () => {
      try {
        // When API is ready:
        // await login({
        //   email: loginForm.email,
        //   password: loginForm.password,
        //   role: activeTab.value
        // })
        console.log('Login attempt', { ...loginForm, role: activeTab.value })
        
        // Redirect to dashboard or home after successful login
        router.push('/')
      } catch (error) {
        console.error('Login failed', error)
        // Handle error (show message, etc.)
      }
    }
    
    const handleRegister = async () => {
      try {
        if (registerForm.password !== registerForm.passwordConfirmation) {
          // Handle password mismatch
          console.error('Passwords do not match')
          return
        }
        
        // When API is ready:
        // await register({
        //   name: registerForm.name,
        //   email: registerForm.email,
        //   password: registerForm.password,
        //   password_confirmation: registerForm.passwordConfirmation,
        //   role: activeTab.value
        // })
        console.log('Register attempt', { ...registerForm, role: activeTab.value })
        
        // Redirect or show success message
        isLogin.value = true
      } catch (error) {
        console.error('Registration failed', error)
        // Handle error (show message, etc.)
      }
    }
    
    return {
      isLogin,
      activeTab,
      loginForm,
      registerForm,
      handleLogin,
      handleRegister
    }
  }
}
</script>