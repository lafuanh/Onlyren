
<!-- UserMessages.vue -->
<template>
  <div class="bg-white rounded-lg shadow-md h-96 flex flex-col">
    <!-- Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
      <h2 class="text-xl font-bold text-gray-800">Chat</h2>
      <div class="flex space-x-2">
        <button 
          v-for="conversation in conversations"
          :key="conversation.id"
          @click="activeConversation = conversation.id"
          :class="[
            'px-3 py-1 rounded-full text-sm font-medium transition-colors',
            activeConversation === conversation.id 
              ? 'bg-orange-100 text-orange-600' 
              : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
          ]"
        >
          {{ conversation.renterName }}
        </button>
      </div>
    </div>

    <!-- Messages Area -->
    <div class="flex-1 overflow-y-auto p-4 space-y-3" ref="messagesContainer">
      <div v-if="currentMessages.length === 0" class="text-center py-8">
        <i class="fas fa-comments text-gray-400 text-4xl mb-4"></i>
        <p class="text-gray-500">Belum ada pesan</p>
      </div>
      
      <div 
        v-for="message in currentMessages" 
        :key="message.id"
        :class="[
          'flex',
          message.senderId === userId ? 'justify-end' : 'justify-start'
        ]"
      >
        <div 
          :class="[
            'max-w-xs lg:max-w-md px-4 py-2 rounded-lg',
            message.senderId === userId 
              ? 'bg-orange-500 text-white' 
              : 'bg-gray-100 text-gray-800'
          ]"
        >
          <p class="text-sm">{{ message.content }}</p>
          <p 
            :class="[
              'text-xs mt-1',
              message.senderId === userId ? 'text-orange-100' : 'text-gray-500'
            ]"
          >
            {{ formatTime(message.createdAt) }}
          </p>
        </div>
      </div>
    </div>

    <!-- Message Input -->
    <div class="border-t border-gray-200 p-4">
      <div class="flex space-x-2">
        <input 
          v-model="newMessage"
          @keyup.enter="sendMessage"
          type="text" 
          placeholder="Ketik pesan..."
          class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
        />
        <button 
          @click="sendMessage"
          :disabled="!newMessage.trim()"
          class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <i class="fas fa-paper-plane"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'

const props = defineProps({
  conversations: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['send'])

const activeConversation = ref(null)
const newMessage = ref('')
const messagesContainer = ref(null)
const userId = ref(1) // This should come from auth store

const currentMessages = computed(() => {
  if (!activeConversation.value) return []
  const conversation = props.conversations.find(c => c.id === activeConversation.value)
  return conversation?.messages || []
})

const formatTime = (dateString) => {
  return new Date(dateString).toLocaleTimeString('id-ID', { 
    hour: '2-digit', 
    minute: '2-digit' 
  })
}

const sendMessage = () => {
  if (!newMessage.value.trim() || !activeConversation.value) return
  
  const messageData = {
    conversationId: activeConversation.value,
    content: newMessage.value.trim(),
    senderId: userId.value
  }
  
  emit('send', messageData)
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

watch(() => props.conversations, (newConversations) => {
  if (newConversations.length > 0 && !activeConversation.value) {
    activeConversation.value = newConversations[0].id
  }
}, { immediate: true })

watch(currentMessages, () => {
  nextTick(() => {
    scrollToBottom()
  })
})
</script>
          
