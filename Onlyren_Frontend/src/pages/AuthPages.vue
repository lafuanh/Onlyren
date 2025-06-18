<script>
import OnlyHeader from '@/components/OnlyHeader.vue'
import OnlyFooter from '@/components/OnlyFooter.vue';

import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { login, register } from '@/api/auth'

export default {
  name: 'AuthPage',
  setup() {
    const router = useRouter()

    const isLogin = ref(true)
    const activeTab = ref('user') // Default to user
    const isSubmitting = ref(false)
    const showDebug = ref(false) // Set to false in production
    
    const errorMessage = ref('')
    const successMessage = ref('')
    
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

    const clearMessages = () => {
      errorMessage.value = ''
      successMessage.value = ''
    }

    const toggleAuthMode = () => {
      isLogin.value = !isLogin.value
      clearMessages()
    }

    // Get redirect route based on user role
    const getRedirectRoute = (user) => {
      if (user.role === 'admin') {
        return '/admin/dashboard'
      } else if (user.role === 'renter') {
        return '/renter/profile'
      } else {
        return '/profile' // Regular user profile
      }
    }

    // Handle login functionality
    const handleLogin = async () => {
      clearMessages()
      isSubmitting.value = true
      
      try {
        console.log('Attempting login with:', {
          email: loginForm.email,
          role: activeTab.value
        })

        const { user, token } = await login({
          email: loginForm.email,
          password: loginForm.password,
          role: activeTab.value
        })
        
        console.log('Login successful:', user)
        successMessage.value = `Welcome back, ${user.name}!`
        
        // Redirect based on user role
        const redirectRoute = getRedirectRoute(user)
        
        // Small delay to show success message before redirect
        setTimeout(() => {
          router.push(redirectRoute)
        }, 1000)
        
      } catch (error) {
        console.error('Login failed:', error)
        
        // More specific error handling
        if (error.message.includes('Invalid credentials')) {
          errorMessage.value = 'Invalid email or password. Please check your credentials.'
        } else if (error.message.includes('Access denied')) {
          errorMessage.value = `This account is not registered as a ${activeTab.value}. Please select the correct account type.`
        } else {
          errorMessage.value = error.message || 'Login failed. Please try again.'
        }
      } finally {
        isSubmitting.value = false
      }
    }
    
    // Handle register functionality
    const handleRegister = async () => {
      clearMessages()
      isSubmitting.value = true
      
      try {
        if (registerForm.password !== registerForm.passwordConfirmation) {
          errorMessage.value = 'Passwords do not match'
          return
        }
        
        if (registerForm.password.length < 8) {
          errorMessage.value = 'Password must be at least 8 characters long'
          return
        }
        
        const { user, token } = await register({
          name: registerForm.name,
          email: registerForm.email,
          password: registerForm.password,
          password_confirmation: registerForm.passwordConfirmation,
          role: activeTab.value
        })
        
        console.log('Registration successful:', user)
        successMessage.value = `Registration successful! Welcome, ${user.name}! Redirecting to your dashboard...`
        
        // Clear form
        Object.keys(registerForm).forEach(key => {
          registerForm[key] = ''
        })
        
        // Redirect to appropriate dashboard based on user role
        const redirectRoute = getRedirectRoute(user)
        
        setTimeout(() => {
          router.push(redirectRoute)
        }, 2000)
        
      } catch (error) {
        console.error('Registration failed:', error)
        errorMessage.value = error.message || 'Registration failed. Please try again.'
      } finally {
        isSubmitting.value = false
      }
    }
    
    return {
      isLogin,
      activeTab,
      loginForm,
      registerForm,
      handleLogin,
      handleRegister,
      toggleAuthMode,
      errorMessage,
      successMessage,
      isSubmitting,
      showDebug
    }
  }
}
</script>

<style scoped>
.error {
  color: red;
  font-size: 14px;
}
</style>

<template>
  
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
          <!-- Header -->

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
        <button 
          @click="activeTab = 'admin'"
          :class="[
            'py-2 px-4 font-medium', 
            activeTab === 'admin' ? 'border-b-2 border-black' : 'text-gray-500'
          ]"
        >
          Admin
        </button>
      </div>

      <h1 class="text-2xl font-bold text-center mb-6">{{ isLogin ? 'Login' : 'Register' }}</h1>
      
      <!-- Error Message Display -->
      <div v-if="errorMessage" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ errorMessage }}
      </div>

      <!-- Success Message Display -->
      <div v-if="successMessage" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ successMessage }}
      </div>
      
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
            :disabled="isSubmitting"
            class="w-full bg-orange-500 text-white py-2 px-4 rounded hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Logging in...' : 'Login' }}
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
            :disabled="isSubmitting"
            class="w-full bg-orange-500 text-white py-2 px-4 rounded hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-opacity-50 disabled:opacity-50"
          >
            {{ isSubmitting ? 'Registering...' : 'Register' }}
          </button>
        </div>
      </form>
      
      <!-- Toggle between login and register -->
      <div class="mt-4 text-center">
        <button 
          @click="toggleAuthMode"
          class="text-sm text-orange-500 hover:underline focus:outline-none"
        >
          {{ isLogin ? 'Need an account? Register' : 'Already have an account? Login' }}
        </button>
      </div>

      <!-- Debug Info (remove in production) -->
      <div v-if="showDebug" class="mt-4 p-3 bg-gray-100 rounded text-xs">
        <p><strong>Debug Info:</strong></p>
        <p>Active Tab: {{ activeTab }}</p>
        <p>Login Email: {{ loginForm.email }}</p>
        <p>Is Login: {{ isLogin }}</p>
      </div>
    </div>
  </div>

</template>
