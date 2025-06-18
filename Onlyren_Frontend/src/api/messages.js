// src/api/messages.js
import apiClient from './client'

// Fetch all conversations for the user
export const fetchConversations = async () => {
  try {
    const response = await apiClient.get('/messages/conversations')
    return response.data
  } catch (error) {
    console.error('Error fetching conversations:', error)
    throw error
  }
}

// Fetch messages in a specific conversation
export const fetchConversationMessages = async (conversationId) => {
  try {
    const response = await apiClient.get(`/messages/conversations/${conversationId}`)
    return response.data
  } catch (error) {
    console.error('Error fetching messages:', error)
    throw error
  }
}

// Send a message in a conversation
export const sendMessage = async (receiver_id, message) => {
  try {
    const response = await apiClient.post('/messages/send', { receiver_id, message });
    return response.data;
  } catch (error) {
    console.error('Error sending message:', error)
    throw error
  }
}

// Load more messages in a conversation
export const loadMoreMessages = async (conversationId, page) => {
  try {
    const response = await apiClient.get(`/messages/conversations/${conversationId}/more`, {
      params: { page }
    })
    return response.data
  } catch (error) {
    console.error('Error loading more messages:', error)
    throw error
  }
}

// Mark messages as read
export const markAsRead = async (senderId) => {
  try {
    const response = await apiClient.put(`/messages/conversations/${senderId}/read`)
    return response.data
  } catch (error) {
    console.error('Error marking messages as read:', error)
    throw error
  }
}

// Get unread count
export const getUnreadCount = async () => {
  try {
    const response = await apiClient.get('/messages/unread-count')
    return response.data
  } catch (error) {
    console.error('Error getting unread count:', error)
    throw error
  }
}

// Search users for messaging
export const searchUsers = async (query) => {
  try {
    const response = await apiClient.get('/messages/search-users', {
      params: { query }
    })
    return response.data
  } catch (error) {
    console.error('Error searching users:', error)
    throw error
  }
}

// Delete a message
export const deleteMessage = async (messageId) => {
  try {
    const response = await apiClient.delete(`/messages/${messageId}`)
    return response.data
  } catch (error) {
    console.error('Error deleting message:', error)
    throw error
  }
}
