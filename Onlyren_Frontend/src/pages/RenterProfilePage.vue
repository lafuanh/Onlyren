<template>
  <div class="min-h-screen bg-gray-50">
    <OnlyHeader />
    
    <div class="flex">
      <!-- Sidebar -->
      <div class="w-64 bg-white shadow-lg min-h-screen">
        <div class="p-6">
          <!-- Renter Profile Section -->
          <div class="flex items-center mb-8">
            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold">
              {{ getInitials(profile.name) }}
            </div>
            <div class="ml-3">
              <h3 class="font-semibold text-gray-800">{{ profile.name || 'Renter' }}</h3>
              <p class="text-sm text-gray-600">Penyewa</p>
            </div>
          </div>

          <!-- Navigation Menu -->
          <nav class="space-y-2">
            <button 
              @click="activeTab = 'profile'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors flex items-center',
                activeTab === 'profile' ? 'bg-blue-100 text-orange-600 border-l-4 border-orange-600' : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-user mr-3"></i>Profil
            </button>
            <button 
              @click="activeTab = 'rooms'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors flex items-center',
                activeTab === 'rooms' ? 'bg-blue-100 text-orange-600 border-l-4 border-orange-600' : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-building mr-3"></i>Ruangan
            </button>
            <button 
              @click="activeTab = 'orders'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors flex items-center',
                activeTab === 'orders' ? 'bg-blue-100 text-orange-600 border-l-4 border-orange-600' : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-clipboard-list mr-3"></i>Pesanan
            </button>
            <button 
              @click="activeTab = 'messages'" 
              :class="[
                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors flex items-center',
                activeTab === 'messages' ? 'bg-blue-100 text-orange-600 border-l-4 border-orange-600' : 'text-gray-600 hover:bg-gray-100'
              ]"
            >
              <i class="fas fa-comments mr-3"></i>Chat
            </button>
          </nav>

          <!-- Logout Button -->
          <div class="mt-8 pt-4 border-t border-gray-200">
            <button 
              @click="confirmLogout"
              :disabled="isLoggingOut"
              class="w-full text-left px-4 py-3 rounded-lg font-medium text-red-600 hover:bg-red-50 transition-colors disabled:opacity-50 flex items-center"
            >
              <i class="fas fa-sign-out-alt mr-3"></i>
              {{ isLoggingOut ? 'Logging out...' : 'Logout' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex-1 p-6">
        <!-- Success/Error Messages -->
        <div v-if="error" class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
          <i class="fas fa-exclamation-circle mr-2"></i>{{ error }}
        </div>
        <div v-if="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
          <i class="fas fa-check-circle mr-2"></i>{{ successMessage }}
        </div>

        <!-- Profile Tab -->
        <div v-if="activeTab === 'profile'">
          <RenterProfileForm :profile="profile" @update="handleProfileUpdate" />
        </div>

        <!-- Rooms Management Tab -->
        <div v-if="activeTab === 'rooms'">
          <RoomManagement 
            :rooms="rooms" 
            @add="handleAddRoom"
            @edit="handleEditRoom" 
            @delete="handleDeleteRoom" 
          />
        </div>

        <!-- Orders Management Tab -->
        <div v-if="activeTab === 'orders'">
          <OrderManagement 
            :orders="orders" 
            @approve="handleApproveOrder"
            @reject="handleRejectOrder"
            @complete="handleCompleteOrder"
          />
        </div>

        <!-- Messages Tab -->
        <div v-if="activeTab === 'messages'">
          <RenterMessages :conversations="conversations" @send="handleSendMessage" />
        </div>
      </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div v-if="showLogoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl max-w-sm w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Confirm Logout</h3>
        <p class="text-gray-600 mb-6">Are you sure you want to logout? You will be redirected to the login page.</p>
        <div class="flex space-x-3">
          <button 
            @click="handleLogout"
            :disabled="isLoggingOut"
            class="flex-1 bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600 transition-colors disabled:opacity-50"
          >
            {{ isLoggingOut ? 'Logging out...' : 'Yes, Logout' }}
          </button>
          <button 
            @click="cancelLogout"
            :disabled="isLoggingOut"
            class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded hover:bg-gray-400 transition-colors disabled:opacity-50"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>

    <OnlyFooter />
  </div>
</template>

<script setup>
// Import necessary components and API methods
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
  fetchRenterProfile, 
  updateRenterProfile,
  fetchRenterRooms, 
  createRoom,
  updateRoom,
  deleteRoom,
  fetchRenterOrders, 
  approveOrder,
  rejectOrder,
  completeOrder,
  fetchRenterConversations,
  sendMessage,
  logout 
} from '@/api/renter';
import OnlyHeader from '@/components/OnlyHeader.vue';
import OnlyFooter from '@/components/OnlyFooter.vue';
import RenterProfileForm from '@/components/RenterProfileForm.vue';
import RoomManagement from '@/components/RoomManagement.vue';
import OrderManagement from '@/components/OrderManagement.vue';
import RenterMessages from '@/components/RenterMessages.vue';

const activeTab = ref('profile');
const profile = ref({});
const rooms = ref([]);
const orders = ref([]);
const conversations = ref([]);

const error = ref(null);
const successMessage = ref(null);
const showLogoutModal = ref(false);
const isLoggingOut = ref(false);

const router = useRouter();

const getInitials = (name) => {
  if (!name) return 'R';
  return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const showMessage = (message, type = 'success') => {
  if (type === 'success') {
    successMessage.value = message;
    error.value = null;
  } else {
    error.value = message;
    successMessage.value = null;
  }
  setTimeout(() => {
    error.value = null;
    successMessage.value = null;
  }, 5000);
};

const loadProfile = async () => {
  try {
    profile.value = await fetchRenterProfile();
  } catch (err) {
    showMessage(err.message || 'Failed to load profile', 'error');
  }
};

const loadRooms = async () => {
  try {
    rooms.value = await fetchRenterRooms();
  } catch (err) {
    showMessage(err.message || 'Failed to load rooms', 'error');
  }
};

const loadOrders = async () => {
  try {
    orders.value = await fetchRenterOrders();
  } catch (err) {
    showMessage(err.message || 'Failed to load orders', 'error');
  }
};

const loadConversations = async () => {
  try {
    conversations.value = await fetchRenterConversations();
  } catch (err) {
    showMessage(err.message || 'Failed to load conversations', 'error');
  }
};

// Profile update handler - now properly aligned with API
const handleProfileUpdate = async (updatedProfile) => {
  try {
    const result = await updateRenterProfile(updatedProfile);
    profile.value = { ...profile.value, ...result };
    showMessage('Profile updated successfully');
  } catch (err) {
    showMessage(err.message || 'Failed to update profile', 'error');
  }
};

// Room management event handlers - now properly aligned with API
const handleAddRoom = async (roomData) => {
  try {
    await createRoom(roomData);
    await loadRooms(); // Refresh the rooms list
    showMessage('Room added successfully');
  } catch (err) {
    showMessage(err.message || 'Failed to add room', 'error');
  }
};

const handleEditRoom = async (roomData) => {
  try {
    await updateRoom(roomData.id, roomData);
    await loadRooms(); // Refresh the rooms list
    showMessage('Room updated successfully');
  } catch (err) {
    showMessage(err.message || 'Failed to update room', 'error');
  }
};

const handleDeleteRoom = async (roomId) => {
  try {
    await deleteRoom(roomId);
    await loadRooms(); // Refresh the rooms list
    showMessage('Room deleted successfully');
  } catch (err) {
    showMessage(err.message || 'Failed to delete room', 'error');
  }
};

// Order management event handlers - now properly aligned with API
const handleApproveOrder = async (orderId) => {
  try {
    await approveOrder(orderId);
    await loadOrders(); // Refresh the orders list
    showMessage('Order approved successfully');
  } catch (err) {
    showMessage(err.message || 'Failed to approve order', 'error');
  }
};

const handleRejectOrder = async (orderId, reason = '') => {
  try {
    await rejectOrder(orderId, reason);
    await loadOrders(); // Refresh the orders list
    showMessage('Order rejected successfully');
  } catch (err) {
    showMessage(err.message || 'Failed to reject order', 'error');
  }
};

const handleCompleteOrder = async (orderId) => {
  try {
    await completeOrder(orderId);
    await loadOrders(); // Refresh the orders list
    showMessage('Order completed successfully');
  } catch (err) {
    showMessage(err.message || 'Failed to complete order', 'error');
  }
};

// Message handling - now properly aligned with API
const handleSendMessage = async (messageData) => {
  try {
    await sendMessage(messageData);
    await loadConversations(); // Refresh conversations
    showMessage('Message sent successfully');
  } catch (err) {
    showMessage(err.message || 'Failed to send message', 'error');
  }
};

// Logout functionality - now properly aligned with API
const confirmLogout = () => {
  showLogoutModal.value = true;
};

const cancelLogout = () => {
  showLogoutModal.value = false;
};

const handleLogout = async () => {
  isLoggingOut.value = true;
  try {
    await logout();
    router.push('/login');
  } catch (err) {
    showMessage(err.message || 'Failed to logout', 'error');
  } finally {
    isLoggingOut.value = false;
    showLogoutModal.value = false;
  }
};

onMounted(() => {
  loadProfile();
  loadRooms();
  loadOrders();
  loadConversations();
});
</script>