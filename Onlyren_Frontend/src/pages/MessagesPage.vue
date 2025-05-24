<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { useRoute } from 'vue-router'
import { 
  fetchConversations, 
  fetchMessagesByConversation, 
  sendMessage 
} from '@/api/messages'

</script>

<template>
  <div class="flex h-screen">
    <!-- Conversations List -->
    <div class="w-1/4 bg-gray-100 border-r p-4 overflow-y-auto">
      <h2 class="text-xl font-semibold mb-4">Conversations</h2>
      
      <!-- Search Conversations -->
      <input 
        type="text"
        placeholder="Search conversations"
        class="w-full px-3 py-2 mb-4 border rounded-lg"
      />

      <!-- Conversation List -->
      <div class="space-y-2">
        <div 
          v-for="conversation in conversations" 
          :key="conversation.id"
          @click="selectConversation(conversation)"
          :class="{
            'p-3 rounded-lg cursor-pointer transition': true,
            'bg-blue-100': selectedConversation?.id === conversation.id,
            'hover:bg-gray-200': selectedConversation?.id !== conversation.id
          }"
        >
          <div class="flex items-center">
            <!-- User Avatar -->
            <img 
              :src="conversation.user.avatar || '/default-avatar.png'"
              alt="User Avatar"
              class="w-10 h-10 rounded-full mr-3"
            />
            
            <div>
              <h3 class="font-semibold">{{ conversation.user.name }}</h3>
              <p class="text-sm text-gray-600 truncate">
                {{ conversation.last_message?.content || 'No messages yet' }}
              </p>
            </div>
            
            <!-- Unread Indicator -->
            <div 
              v-if="conversation.unread_count > 0"
              class="ml-auto bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs"
            >
              {{ conversation.unread_count }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Messages Section -->
    <div class="w-3/4 flex flex-col">
      <!-- Conversation Header -->
      <div 
        v-if="selectedConversation" 
        class="bg-white border-b p-4 flex items-center"
      >
        <img 
          :src="selectedConversation.user.avatar || '/default-avatar.png'"
          alt="User Avatar"
          class="w-12 h-12 rounded-full mr-4"
        />
        <div>
          <h2 class="text-lg font-semibold">
            {{ selectedConversation.user.name }}
          </h2>
          <p class="text-sm text-gray-600">
            {{ selectedConversation.user.email }}
          </p>
        </div>
      </div>

      <!-- No Conversation Selected -->
      <div 
        v-else 
        class="flex-grow flex items-center justify-center text-gray-500"
      >
        Select a conversation to start messaging
      </div>

      <!-- Messages Container -->
      <div 
        v-if="selectedConversation"
        ref="messageContainer"
        class="flex-grow overflow-y-auto p-4 space-y-4"
      >
        <!-- Load More Button -->
        <div 
          v-if="hasMoreMessages"
          class="text-center mb-4"
        >
          <button 
            @click="loadMoreMessages"
            class="px-4 py-2 bg-blue-500 text-white rounded-lg"
          >
            Load More
          </button>
        </div>

        <!-- Messages List -->
        <div 
          v-for="message in messages" 
          :key="message.id"
          class="flex"
          :class="{
            'justify-end': message.sender_id === selectedConversation.user.id,
            'justify-start': message.sender_id !== selectedConversation.user.id
          }"
        >
          <div 
            class="max-w-[70%] p-3 rounded-lg"
            :class="{
              'bg-blue-500 text-white': message.sender_id === selectedConversation.user.id,
              'bg-gray-200': message.sender_id !== selectedConversation.user.id
            }"
          >
            <p>{{ message.content }}</p>
            <p class="text-xs mt-1 opacity-75">
              {{ formatTimestamp(message.created_at) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Message Input -->
      <div 
        v-if="selectedConversation"
        class="bg-white border-t p-4 flex items-center"
      >
        <input 
          v-model="messageInput"
          type="text"
          placeholder="Type a message..."
          class="flex-grow px-3 py-2 border rounded-lg mr-4"
          @keyup.enter="submitMessage"
        />
        <button 
          @click="submitMessage"
          class="px-4 py-2 bg-blue-500 text-white rounded-lg"
          :disabled="!messageInput.trim()"
        >
          Send
        </button>
      </div>
    </div>

    <!-- Error Modal -->
    <div 
      v-if="error"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white p-6 rounded-lg max-w-md">
        <h2 class="text-xl font-semibold text-red-600 mb-4">Error</h2>
        <p class="mb-4">{{ error }}</p>
        <button 
          @click="error = null"
          class="px-4 py-2 bg-red-500 text-white rounded-lg"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>