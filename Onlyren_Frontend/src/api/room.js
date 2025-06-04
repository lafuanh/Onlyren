// @/api/room.js
import apiClient from './client'

/**
 * Fetch rooms with search and filter parameters
 * @param {Object} params - Search and filter parameters
 * @returns {Promise} API response with rooms data
 */
export const fetchRooms = async (params = {}) => {
  try {
    // Clean up parameters and build query string
    const cleanParams = {}
    
    // Add search query if provided
    if (params.query && typeof params.query === 'string' && params.query.trim()) {
      cleanParams.search = params.query.trim()
    }
    
    // Add location search (since your UI has location in search)
    if (params.location && typeof params.location === 'string' && params.location.trim()) {
      cleanParams.location = params.location.trim()
    }
    
    // Add room type filter
    if (params.type && typeof params.type === 'string' && params.type.trim()) {
      cleanParams.type = params.type.trim()
    }
    
    // Add price range filters with validation
    if (params.priceRange && Array.isArray(params.priceRange) && params.priceRange.length === 2) {
      const [minPrice, maxPrice] = params.priceRange
      
      if (typeof minPrice === 'number' && minPrice > 0) {
        cleanParams.price_min = minPrice
      }
      if (typeof maxPrice === 'number' && maxPrice > 0 && maxPrice < 1000000) {
        cleanParams.price_max = maxPrice
      }
    }
    
    // Add amenities filter
    if (params.amenities && Array.isArray(params.amenities) && params.amenities.length > 0) {
      cleanParams.amenities = params.amenities.filter(Boolean).join(',')
    }
    
    // Add period filter with validation
    if (params.period && ['Harian', 'Mingguan', 'Bulanan'].includes(params.period)) {
      cleanParams.period = params.period
    }
    
    // Add pagination with validation
    cleanParams.page = Math.max(1, parseInt(params.page) || 1)
    cleanParams.per_page = Math.min(50, Math.max(1, parseInt(params.per_page) || 12))
    
    // Add sorting (optional)
    cleanParams.sort_by = params.sort_by || 'created_at'
    cleanParams.sort_order = ['asc', 'desc'].includes(params.sort_order) ? params.sort_order : 'desc'
    
    console.log('Fetching rooms with params:', cleanParams)
    
    const response = await apiClient.get('/rooms', {
      params: cleanParams,
      timeout: 15000 // 15 second timeout for search
    })
    
    // Validate response structure
    if (!response || !response.data) {
      throw new Error('Invalid response format from server')
    }
    
    const data = response.data.data || response.data || []
    const meta = response.data.meta || {
      current_page: cleanParams.page,
      last_page: Math.ceil((data.length || 0) / cleanParams.per_page),
      per_page: cleanParams.per_page,
      total: data.length || 0
    }
    
    // Ensure data is array
    const rooms = Array.isArray(data) ? data : []
    
    return {
      data: rooms.map(room => ({
        id: room.id,
        name: room.name || '',
        description: room.description || '',
        location: room.location || '',
        type: room.type || 'General',
        capacity: room.capacity || '1-10',
        price: room.price || room.price_per_day || 50000,
        price_per_day: room.price_per_day || 50000,
        price_per_week: room.price_per_week || 300000,
        price_per_month: room.price_per_month || 1200000,
        image: room.image || room.featured_image || '',
        featured_image: room.featured_image || room.image || '',
        images: Array.isArray(room.images) ? room.images : [],
        amenities: Array.isArray(room.amenities) ? room.amenities : [],
        rating: parseFloat(room.rating || room.average_rating || 4.5),
        review_count: parseInt(room.review_count || room.total_reviews || 0),
        reviews: room.reviews || 0,
        is_available: room.is_available !== false,
        created_at: room.created_at,
        updated_at: room.updated_at
      })),
      meta: {
        current_page: parseInt(meta.current_page || cleanParams.page),
        last_page: parseInt(meta.last_page || 1),
        per_page: parseInt(meta.per_page || cleanParams.per_page),
        total: parseInt(meta.total || 0)
      }
    }
    
  } catch (error) {
    console.error('Error fetching rooms:', error)
    
    // Handle different types of errors
    if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
      throw new Error('Request timeout - please try again')
    }
    
    if (error.response) {
      const status = error.response.status
      const message = error.response.data?.message || error.response.data?.error || error.message
      
      switch (status) {
        case 400:
          throw new Error(`Bad request: ${message}`)
        case 401:
          throw new Error('Authentication required')
        case 403:
          throw new Error('Access forbidden')
        case 404:
          throw new Error('Rooms endpoint not found')
        case 422:
          throw new Error(`Validation error: ${message}`)
        case 429:
          throw new Error('Too many requests - please wait a moment')
        case 500:
          throw new Error('Server error - please try again later')
        case 502:
        case 503:
        case 504:
          throw new Error('Service temporarily unavailable')
        default:
          throw new Error(message || 'Failed to fetch rooms')
      }
    }
    
    if (error.request) {
      throw new Error('Network error - please check your connection')
    }
    
    throw new Error(error.message || 'An unexpected error occurred')
  }
}

/**
 * Fetch detailed information for a specific room
 * @param {string|number} roomId - The room ID
 * @returns {Promise} API response with room details
 */
export const fetchRoomDetails = async (roomId) => {
  try {
    if (!roomId) {
      throw new Error('Room ID is required')
    }
    
    // Validate room ID
    const validRoomId = String(roomId).trim()
    if (!validRoomId) {
      throw new Error('Invalid room ID')
    }
    
    console.log(`Fetching room details for ID: ${validRoomId}`)
    
    const response = await apiClient.get(`/rooms/${validRoomId}`, {
      timeout: 10000 // 10 second timeout
    })
    
    if (!response || !response.data) {
      throw new Error('Invalid response format from server')
    }
    
    // Get room data from response
    const room = response.data.data || response.data
    
    if (!room) {
      throw new Error('Room not found')
    }
    
    return {
      id: room.id,
      name: room.name || 'Unnamed Room',
      description: room.description || 'No description available',
      location: room.location || 'Unknown Location',
      type: room.type || 'General',
      capacity: room.capacity || '1-10',
      specifications: room.specifications || 'Specifications not available',
      
      // Pricing with proper validation
      price_per_hour: Math.max(0, parseInt(room.price_per_hour || room.hourly_price || 25000)),
      price_per_day: Math.max(0, parseInt(room.price_per_day || room.daily_price || 300000)),
      price_per_week: Math.max(0, parseInt(room.price_per_week || room.weekly_price || 1800000)),
      price_per_month: Math.max(0, parseInt(room.price_per_month || room.monthly_price || 7000000)),
      
      // Images with validation
      images: Array.isArray(room.images) ? room.images.filter(Boolean) : [],
      featured_image: room.featured_image || room.image || '',
      
      // Amenities and facilities
      amenities: Array.isArray(room.amenities) ? room.amenities.filter(Boolean) : [],
      facilities: Array.isArray(room.facilities) ? room.facilities.filter(Boolean) : (Array.isArray(room.amenities) ? room.amenities.filter(Boolean) : []),
      
      // Reviews and ratings with validation
      rating: Math.max(0, Math.min(5, parseFloat(room.rating || room.average_rating || 4.5))),
      review_count: Math.max(0, parseInt(room.review_count || room.total_reviews || 0)),
      reviews: Array.isArray(room.reviews) ? room.reviews : [],
      
      // Availability
      is_available: room.is_available !== false,
      availability_status: room.availability_status || 'available',
      
      // Owner information
      owner: room.owner || room.renter || null,
      owner_id: room.owner_id || room.renter_id || null,
      
      // Timestamps
      created_at: room.created_at,
      updated_at: room.updated_at
    }
    
  } catch (error) {
    console.error('Error fetching room details:', error)
    
    if (error.response) {
      const status = error.response.status
      const message = error.response.data?.message || error.response.data?.error || error.message
      
      switch (status) {
        case 404:
          throw new Error('Room not found')
        case 401:
          throw new Error('Authentication required to view room details')
        case 403:
          throw new Error('Access forbidden to this room')
        case 500:
          throw new Error('Server error - please try again later')
        default:
          throw new Error(message || 'Failed to fetch room details')
      }
    }
    
    if (error.request) {
      throw new Error('Network error - please check your connection')
    }
    
    throw new Error(error.message || 'Failed to fetch room details')
  }
}

/**
 * Check room availability for specific dates and times
 * @param {string|number} roomId - The room ID
 * @param {Object} params - Availability check parameters
 * @returns {Promise} API response with availability data
 */
export const checkRoomAvailability = async (roomId, params = {}) => {
  try {
    if (!roomId) {
      throw new Error('Room ID is required')
    }
    
    // Validate parameters
    const { date, start_time, end_time } = params
    
    if (!date || !start_time || !end_time) {
      throw new Error('Date, start time, and end time are required')
    }
    
    // Basic date validation
    const dateRegex = /^\d{4}-\d{2}-\d{2}$/
    const timeRegex = /^\d{2}:\d{2}$/
    
    if (!dateRegex.test(date)) {
      throw new Error('Invalid date format. Use YYYY-MM-DD')
    }
    
    if (!timeRegex.test(start_time) || !timeRegex.test(end_time)) {
      throw new Error('Invalid time format. Use HH:MM')
    }
    
    const response = await apiClient.get(`/rooms/${roomId}/availability`, {
      params: {
        date: date.trim(),
        start_time: start_time.trim(),
        end_time: end_time.trim()
      },
      timeout: 10000
    })
    
    return response.data || { available: false }
    
  } catch (error) {
    console.error('Error checking room availability:', error)
    
    if (error.response?.status === 404) {
      throw new Error('Room not found')
    }
    
    throw new Error(error.response?.data?.message || error.message || 'Failed to check availability')
  }
}

/**
 * Create a new reservation
 * @param {Object} reservationData - Reservation details
 * @returns {Promise} API response with reservation data
 */
export const createReservation = async (reservationData) => {
  try {
    // Validate required fields
    const requiredFields = ['room_id', 'date', 'start_time', 'end_time', 'guests']
    const missingFields = []
    
    for (const field of requiredFields) {
      if (!reservationData[field]) {
        missingFields.push(field.replace('_', ' '))
      }
    }
    
    if (missingFields.length > 0) {
      throw new Error(`Missing required fields: ${missingFields.join(', ')}`)
    }
    
    // Validate data types and formats
    const guests = parseInt(reservationData.guests)
    if (isNaN(guests) || guests < 1) {
      throw new Error('Number of guests must be at least 1')
    }
    
    // Prepare the payload
    const payload = {
      room_id: String(reservationData.room_id).trim(),
      date: String(reservationData.date).trim(),
      start_time: String(reservationData.start_time).trim(),
      end_time: String(reservationData.end_time).trim(),
      guests: guests,
      notes: String(reservationData.notes || '').trim(),
      status: 'pending'
    }
    
    const response = await apiClient.post('/reservations', payload, {
      timeout: 15000
    })
    
    return response.data.data || response.data
    
  } catch (error) {
    console.error('Error creating reservation:', error)
    
    // Handle validation errors
    if (error.response?.status === 422) {
      const validationErrors = error.response.data.errors
      if (validationErrors) {
        const errorMessages = Object.values(validationErrors).flat()
        throw new Error(errorMessages.join(', '))
      }
    }
    
    if (error.response?.status === 409) {
      throw new Error('Time slot is no longer available')
    }
    
    throw new Error(error.response?.data?.message || error.message || 'Failed to create reservation')
  }
}

/**
 * Get user's reservations
 * @param {Object} params - Query parameters
 * @returns {Promise} API response with reservations data
 */
export const fetchUserReservations = async (params = {}) => {
  try {
    const cleanParams = {
      page: Math.max(1, parseInt(params.page) || 1),
      per_page: Math.min(50, Math.max(1, parseInt(params.per_page) || 10)),
      sort_by: params.sort_by || 'created_at',
      sort_order: ['asc', 'desc'].includes(params.sort_order) ? params.sort_order : 'desc'
    }
    
    if (params.status && ['pending', 'confirmed', 'cancelled', 'completed'].includes(params.status)) {
      cleanParams.status = params.status
    }
    
    const response = await apiClient.get('/user/reservations', {
      params: cleanParams,
      timeout: 10000
    })
    
    return {
      data: response.data.data || [],
      meta: response.data.meta || {}
    }
    
  } catch (error) {
    console.error('Error fetching user reservations:', error)
    throw new Error(error.response?.data?.message || error.message || 'Failed to fetch reservations')
  }
}

/**
 * Get specific reservation details
 * @param {string|number} reservationId - Reservation ID
 * @returns {Promise} API response with reservation details
 */
export const fetchReservationDetails = async (reservationId) => {
  try {
    if (!reservationId) {
      throw new Error('Reservation ID is required')
    }
    
    const response = await apiClient.get(`/reservations/${reservationId}`, {
      timeout: 10000
    })
    
    return response.data.data || response.data
    
  } catch (error) {
    console.error('Error fetching reservation details:', error)
    
    if (error.response?.status === 404) {
      throw new Error('Reservation not found')
    }
    
    throw new Error(error.response?.data?.message || error.message || 'Failed to fetch reservation details')
  }
}

/**
 * Update/Cancel a reservation
 * @param {string|number} reservationId - Reservation ID
 * @param {Object} updateData - Update data
 * @returns {Promise} API response
 */
export const updateReservation = async (reservationId, updateData) => {
  try {
    if (!reservationId) {
      throw new Error('Reservation ID is required')
    }
    
    if (!updateData || typeof updateData !== 'object') {
      throw new Error('Update data is required')
    }
    
    const response = await apiClient.put(`/reservations/${reservationId}`, updateData, {
      timeout: 15000
    })
    
    return response.data.data || response.data
    
  } catch (error) {
    console.error('Error updating reservation:', error)
    
    if (error.response?.status === 404) {
      throw new Error('Reservation not found')
    }
    
    if (error.response?.status === 409) {
      throw new Error('Cannot update reservation - time slot conflict')
    }
    
    throw new Error(error.response?.data?.message || error.message || 'Failed to update reservation')
  }
}

/**
 * Cancel a reservation
 * @param {string|number} reservationId - Reservation ID
 * @returns {Promise} API response
 */
export const cancelReservation = async (reservationId) => {
  try {
    if (!reservationId) {
      throw new Error('Reservation ID is required')
    }
    
    const response = await apiClient.delete(`/reservations/${reservationId}`, {
      timeout: 10000
    })
    
    return response.data
    
  } catch (error) {
    console.error('Error cancelling reservation:', error)
    
    if (error.response?.status === 404) {
      throw new Error('Reservation not found')
    }
    
    if (error.response?.status === 409) {
      throw new Error('Cannot cancel reservation - it may already be confirmed')
    }
    
    throw new Error(error.response?.data?.message || error.message || 'Failed to cancel reservation')
  }
}

/**
 * Search rooms by location (helper function)
 * @param {string} location - Location to search
 * @param {Object} additionalParams - Additional search parameters
 * @returns {Promise} API response with rooms data
 */
export const searchRoomsByLocation = async (location, additionalParams = {}) => {
  if (!location || typeof location !== 'string') {
    throw new Error('Location is required for search')
  }
  
  return fetchRooms({
    query: location.trim(),
    location: location.trim(),
    ...additionalParams
  })
}

/**
 * Get popular/featured rooms
 * @param {number} limit - Number of rooms to fetch
 * @returns {Promise} API response with featured rooms
 */
export const fetchFeaturedRooms = async (limit = 8) => {
  try {
    const response = await apiClient.get('/rooms/featured', {
      params: {
        limit: Math.min(20, Math.max(1, parseInt(limit)))
      },
      timeout: 10000
    })
    
    return {
      data: response.data.data || response.data || [],
      meta: response.data.meta || {}
    }
    
  } catch (error) {
    console.error('Error fetching featured rooms:', error)
    
    // Fallback to regular rooms if featured endpoint doesn't exist
    if (error.response?.status === 404) {
      return fetchRooms({ per_page: limit, sort_by: 'rating', sort_order: 'desc' })
    }
    
    throw new Error(error.response?.data?.message || error.message || 'Failed to fetch featured rooms')
  }
}