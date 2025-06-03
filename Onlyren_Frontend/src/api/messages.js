// src/api/messages.js
import axios from './axios'

const API_URL = import.meta.env.VITE_API_URL // Your API URL

// Fetch all conversations for the user
export const fetchConversations = async () => {
  try {
    const response = await axios.get(`${API_URL}/messages/conversations`)
    return response.data
  } catch (error) {
    console.error('Error fetching conversations:', error)
    throw error
  }
}

// Fetch messages in a specific conversation
export const fetchConversationMessages = async (conversationId) => {
  try {
    const response = await axios.get(`${API_URL}/messages/conversations/${conversationId}`)
    return response.data
  } catch (error) {
    console.error('Error fetching messages:', error)
    throw error
  }
}

export const fetchUserConversations = async (conversationId) => {
  try {
    const response = await axios.get(`${API_URL}/messages/conversations/${conversationId}`)
    return response.data
  } catch (error) {
    console.error('Error fetching messages:', error)
    throw error
  }
}


// Send a message in a conversation
export const sendMessage = async (conversationId, messageData) => {
  try {
    const response = await axios.post(`${API_URL}/messages/conversations/${conversationId}`, messageData)
    return response.data
  } catch (error) {
    console.error('Error sending message:', error)
    throw error
  }
}

// Load more messages in a conversation
export const loadMoreMessages = async (conversationId, page) => {
  try {
    const response = await axios.get(`${API_URL}/messages/conversations/${conversationId}/more`, {
      params: { page }
    })
    return response.data
  } catch (error) {
    console.error('Error loading more messages:', error)
    throw error
  }
}
