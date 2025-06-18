<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden h-96 flex flex-col">
    <div class="border-b border-gray-200 p-4 flex-shrink-0">
      <h2 class="text-2xl font-bold text-gray-800">Pesan & Chat</h2>
    </div>
    <div class="flex flex-1 min-h-0">
      <!-- Conversation List -->
      <div class="w-1/3 border-r border-gray-200 flex flex-col min-h-0">
        <div class="flex-1 overflow-y-auto">
          <div v-if="loading" class="p-4 text-center text-gray-500">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
            Memuat percakapan...
          </div>
          <div v-else-if="conversations.length === 0" class="p-4 text-center text-gray-500">
            <i class="fas fa-comments text-gray-400 text-3xl mb-2"></i>
            <p>Belum ada percakapan</p>
          </div>
          <div 
            v-for="conversation in conversations" 
            :key="conversation.id"
            @click="selectConversation(conversation)"
            :class="[
              'p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors',
              selectedConversation?.id === conversation.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''
            ]"
          >
            <div class="flex items-center mb-2">
              <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-white font-bold mr-3 flex-shrink-0">
                {{ getInitials(conversation.user?.name) }}
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-medium text-gray-800 truncate">{{ conversation.user?.name }}</h4>
                <p class="text-sm text-gray-600 truncate">{{ conversation.roomName || '' }}</p>
              </div>
              <div class="text-right flex-shrink-0">
                <p class="text-xs text-gray-500">{{ formatTime(conversation.last_message?.sent_at) }}</p>
                <span 
                  v-if="conversation.unread_count > 0"
                  class="inline-block w-5 h-5 bg-red-500 text-white text-xs rounded-full text-center leading-5 mt-1"
                >
                  {{ conversation.unread_count }}
                </span>
              </div>
            </div>
            <p class="text-sm text-gray-600 truncate">{{ conversation.last_message?.content }}</p>
          </div>
        </div>
      </div>
      <!-- Chat Area -->
      <div class="flex-1 flex flex-col min-h-0">
        <div v-if="!selectedConversation" class="flex-1 flex items-center justify-center text-gray-500">
          <div class="text-center">
            <i class="fas fa-comments text-gray-400 text-4xl mb-4"></i>
            <p>Pilih percakapan untuk mulai chat</p>
          </div>
        </div>
        <div v-else class="flex-1 flex flex-col min-h-0">
          <!-- Chat Header -->
          <div class="p-4 border-b border-gray-200 bg-gray-50 flex-shrink-0">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-white font-bold mr-3 flex-shrink-0">
                {{ getInitials(selectedConversation.user?.name) }}
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-medium text-gray-800 truncate">{{ selectedConversation.user?.name }}</h4>
                <p class="text-sm text-gray-600 truncate">{{ selectedConversation.roomName || '' }}</p>
              </div>
            </div>
          </div>
          <!-- Messages -->
          <div class="flex-1 p-4 overflow-y-auto min-h-0" ref="messagesContainer">
            <div v-if="loadingMessages" class="text-center text-gray-500 py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
              Memuat pesan...
            </div>
            <div v-else-if="selectedMessages.length === 0" class="text-center text-gray-500 py-8">
              <p>Belum ada pesan</p>
            </div>
            <div 
              v-for="message in selectedMessages" 
              :key="message.id"
              :class="[
                'mb-4 flex',
                message.sender_id === userId ? 'justify-end' : 'justify-start'
              ]"
            >
              <div 
                :class="[
                  'max-w-xs lg:max-w-md px-4 py-2 rounded-lg break-words',
                  message.sender_id === userId 
                    ? 'bg-blue-500 text-white' 
                    : 'bg-gray-200 text-gray-800'
                ]"
              >
                <p class="text-sm">{{ message.message }}</p>
                <p 
                  :class="[
                    'text-xs mt-1',
                    message.sender_id === userId ? 'text-blue-100' : 'text-gray-500'
                  ]"
                >
                  {{ formatTime(message.sent_at) }}
                </p>
              </div>
            </div>
          </div>
          <!-- Message Input - Fixed at bottom -->
          <div class="p-4 border-t border-gray-200 flex-shrink-0 bg-white">
            <form @submit.prevent="sendMessage" class="flex space-x-2">
              <input 
                v-model="newMessage"
                type="text" 
                placeholder="Ketik pesan..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                :disabled="!selectedConversation"
              />
              <button 
                type="submit"
                :disabled="!newMessage.trim() || !selectedConversation || sending"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex-shrink-0"
              >
                <i v-if="!sending" class="fas fa-paper-plane"></i>
                <div v-else class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div v-if="error" class="p-2 text-red-600 text-sm text-center flex-shrink-0">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { fetchConversations, fetchConversationMessages, sendMessage as sendMessageApi } from '@/api/messages'
// import { useAuthStore } from '@/stores/auth' // If you have Pinia or Vuex

const conversations = ref([])
const loading = ref(false)
const error = ref(null)
const selectedConversation = ref(null)
const selectedMessages = ref([])
const newMessage = ref('')
const sending = ref(false)
const messagesContainer = ref(null)
const loadingMessages = ref(false)
// const userId = useAuthStore().user?.id || 1 // Replace with your auth logic
const userId = ref(1) // TODO: Replace with real user id from auth

const getInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatTime = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const loadConversations = async () => {
  loading.value = true
  error.value = null
  try {
    const res = await fetchConversations()
    conversations.value = res.data || []
    // Auto-select first conversation
    if (conversations.value.length > 0) {
      selectConversation(conversations.value[0])
    } else {
      selectedConversation.value = null
      selectedMessages.value = []
    }
  } catch (err) {
    error.value = err.message || 'Gagal memuat percakapan.'
  } finally {
    loading.value = false
  }
}

const selectConversation = async (conversation) => {
  selectedConversation.value = conversation
  await loadMessages(conversation.id)
  nextTick(() => scrollToBottom())
}

const loadMessages = async (conversationId) => {
  loadingMessages.value = true
  error.value = null
  try {
    const res = await fetchConversationMessages(conversationId)
    // API returns { success, data: { messages, other_user } }
    selectedMessages.value = res.data?.messages || []
  } catch (err) {
    error.value = err.message || 'Gagal memuat pesan.'
    selectedMessages.value = []
  } finally {
    loadingMessages.value = false
  }
}

const sendMessage = async () => {
  if (!newMessage.value.trim() || !selectedConversation.value) return
  sending.value = true
  try {
    await sendMessageApi(selectedConversation.value.id, newMessage.value.trim())
    newMessage.value = ''
    await loadMessages(selectedConversation.value.id)
    nextTick(() => scrollToBottom())
  } catch (err) {
    error.value = err.message || 'Gagal mengirim pesan.'
  } finally {
    sending.value = false
  }
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

onMounted(loadConversations)

watch(selectedMessages, () => {
  nextTick(() => {
    scrollToBottom()
  })
})
</script>