// @/api/room.js
import apiClient from './client'

/**
 * Fetch rooms with search and filter parameters
 * @param {Object} params - Search and filter parameters
 * @returns {Promise} API response with rooms data
 */
export const fetchRooms = async (params = {}) => {
  try {
    const cleanParams = { ...params };

    // Handle price range array
    if (params.priceRange && Array.isArray(params.priceRange)) {
      cleanParams.price_min = params.priceRange[0];
      cleanParams.price_max = params.priceRange[1];
      delete cleanParams.priceRange;
    }

    const response = await apiClient.get('/rooms', { params: cleanParams });

    if (response.data && response.data.success) {
      return {
        data: response.data.data || [],
        meta: response.data.meta || {}
      };
    } else {
      throw new Error(response.data.message || 'Invalid response from server');
    }

  } catch (error) {
    console.error('Error in fetchRooms API call:', error);
    throw new Error(error.response?.data?.message || error.message || 'Failed to fetch rooms.');
  }
};

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
    // --- THIS IS THE CORRECTED VALIDATION ---
    // We now check for the fields the backend actually needs.
    const requiredFields = ['room_id', 'start_date', 'end_date', 'start_time', 'end_time', 'guests'];

    const missingFields = [];
    for (const field of requiredFields) {
      if (!reservationData[field]) {
        missingFields.push(field.replace('_', ' '));
      }
    }

    if (missingFields.length > 0) {
      throw new Error(`Missing required fields: ${missingFields.join(', ')}`);
    }
    
    // The payload is already correctly formatted by the Vue component, so we just send it.
    const response = await apiClient.post('/reservations', reservationData, {
      timeout: 15000
    });
    
    // Return the full response so the component can get the 'data' object
    return response.data; 

  } catch (error) {
    console.error('Error creating reservation:', error);

    // Handle validation errors from the backend
    if (error.response?.status === 422) {
      const validationErrors = error.response.data.errors;
      if (validationErrors) {
        const errorMessages = Object.values(validationErrors).flat();
        throw new Error(errorMessages.join(', '));
      }
    }
    
    if (error.response?.status === 409) {
      throw new Error('Time slot is no longer available.');
    }
    
    // Re-throw the original error or a custom one
    throw new Error(error.response?.data?.message || error.message || 'Failed to create reservation.');
  }
};

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