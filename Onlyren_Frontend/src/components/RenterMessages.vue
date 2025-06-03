<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden">
    <div class="border-b border-gray-200 p-4">
      <h2 class="text-2xl font-bold text-gray-800">Pesan & Chat</h2>
    </div>
    
    <div class="flex h-96">
      <!-- Conversation List -->
      <div class="w-1/3 border-r border-gray-200 overflow-y-auto">
        <div v-if="conversations.length === 0" class="p-4 text-center text-gray-500">
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
            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-white font-bold mr-3">
              {{ getInitials(conversation.userName) }}
            </div>
            <div class="flex-1">
              <h4 class="font-medium text-gray-800">{{ conversation.userName }}</h4>
              <p class="text-sm text-gray-600">{{ conversation.roomName }}</p>
            </div>
            <div class="text-right">
              <p class="text-xs text-gray-500">{{ formatTime(conversation.lastMessageTime) }}</p>
              <span 
                v-if="conversation.unreadCount > 0"
                class="inline-block w-5 h-5 bg-red-500 text-white text-xs rounded-full text-center leading-5 mt-1"
              >
                {{ conversation.unreadCount }}
              </span>
            </div>
          </div>
          <p class="text-sm text-gray-600 truncate">{{ conversation.lastMessage }}</p>
        </div>
      </div>

      <!-- Chat Area -->
      <div class="flex-1 flex flex-col">
        <div v-if="!selectedConversation" class="flex-1 flex items-center justify-center text-gray-500">
          <div class="text-center">
            <i class="fas fa-comments text-gray-400 text-4xl mb-4"></i>
            <p>Pilih percakapan untuk mulai chat</p>
          </div>
        </div>
        
        <div v-else class="flex-1 flex flex-col">
          <!-- Chat Header -->
          <div class="p-4 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center">
              <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center text-white font-bold mr-3">
                {{ getInitials(selectedConversation.userName) }}
              </div>
              <div>
                <h4 class="font-medium text-gray-800">{{ selectedConversation.userName }}</h4>
                <p class="text-sm text-gray-600">{{ selectedConversation.roomName }}</p>
              </div>
            </div>
          </div>

          <!-- Messages -->
          <div class="flex-1 p-4 overflow-y-auto" ref="messagesContainer">
            <div v-if="selectedMessages.length === 0" class="text-center text-gray-500 py-8">
              <p>Belum ada pesan</p>
            </div>
            
            <div 
              v-for="message in selectedMessages" 
              :key="message.id"
              :class="[
                'mb-4 flex',
                message.isFromRenter ? 'justify-end' : 'justify-start'
              ]"
            >
              <div 
                :class="[
                  'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
                  message.isFromRenter 
                    ? 'bg-blue-500 text-white' 
                    : 'bg-gray-200 text-gray-800'
                ]"
              >
                <p class="text-sm">{{ message.content }}</p>
                <p 
                  :class="[
                    'text-xs mt-1',
                    message.isFromRenter ? 'text-blue-100' : 'text-gray-500'
                  ]"
                >
                  {{ formatTime(message.timestamp) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Message Input -->
          <div class="p-4 border-t border-gray-200">
            <form @submit.prevent="sendMessage" class="flex space-x-2">
              <input 
                v-model="newMessage"
                type="text" 
                placeholder="Ketik pesan..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <button 
                type="submit"
                :disabled="!newMessage.trim()"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
              >
                <i class="fas fa-paper-plane"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue'

const props = defineProps({
  conversations: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['send'])

const selectedConversation = ref(null)
const newMessage = ref('')
const messagesContainer = ref(null)

// Mock messages data - in real app, this would come from API
const messages = ref({
  1: [
    {
      id: 1,
      content: 'Halo, saya tertarik dengan ruangan meeting Anda',
      timestamp: new Date('2024-01-15T09:00:00'),
      isFromRenter: false
    },
    {
      id: 2,
      content: 'Halo! Terima kasih atas minatnya. Ruangan mana yang Anda butuhkan?',
      timestamp: new Date('2024-01-15T09:05:00'),
      isFromRenter: true
    },
    {
      id: 3,
      content: 'Saya butuh untuk tanggal 20 Januari, dari jam 9 pagi sampai 12 siang',
      timestamp: new Date('2024-01-15T09:10:00'),
      isFromRenter: false
    }
  ],
  2: [
    {
      id: 4,
      content: 'Apakah ruangan tersedia untuk besok?',
      timestamp: new Date('2024-01-15T10:00:00'),
      isFromRenter: false
    },
    {
      id: 5,
      content: 'Mari saya cek ketersediaan dulu',
      timestamp: new Date('2024-01-15T10:05:00'),
      isFromRenter: true
    }
  ]
})

const selectedMessages = computed(() => {
  return selectedConversation.value ? messages.value[selectedConversation.value.id] || [] : []
})

const getInitials = (name) => {
  if (!name) return 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase()
}

const formatTime = (timestamp) => {
  const date = new Date(timestamp)
  const now = new Date()
  const diffTime = Math.abs(now - date)
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  
  if (diffDays === 1) {
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
  } else if (diffDays <= 7) {
    return date.toLocaleDateString('id-ID', { weekday: 'short' })
  } else {
    return date.toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit' })
  }
}

const selectConversation = (conversation) => {
  selectedConversation.value = conversation
  // Mark as read
  conversation.unreadCount = 0
  
  nextTick(() => {
    scrollToBottom()
  })
}

const sendMessage = () => {
  if (!newMessage.value.trim() || !selectedConversation.value) return
  
  const message = {
    id: Date.now(),
    content: newMessage.value.trim(),
    timestamp: new Date(),
    isFromRenter: true
  }
  
  if (!messages.value[selectedConversation.value.id]) {
    messages.value[selectedConversation.value.id] = []
  }
  
  messages.value[selectedConversation.value.id].push(message)
  
  // Update conversation
  selectedConversation.value.lastMessage = message.content
  selectedConversation.value.lastMessageTime = message.timestamp
  
  emit('send', {
    conversationId: selectedConversation.value.id,
    message: message.content
  })
  
  newMessage.value = ''
  
  nextTick(() => {
    scrollToBottom()
  })
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

watch(selectedMessages, () => {
  nextTick(() => {
    scrollToBottom()
  })
})
</script>