<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import OnlyFooter from '@/components/OnlyFooter.vue'

// Router instance
const router = useRouter()

// Search state
const searchQuery = ref('')

// Handle search functionality
const handleSearch = () => {
  if (router.currentRoute.value.name !== 'Search') { // Check if we're already on the search page
    router.push({ name: 'Search', query: { query: searchQuery.value } }) // Navigate to search page
  } else {
    // Perform search if already on the search page but prevent making multiple requests simultaneously
    if (!isLoading.value) {
      performSearch() // Perform search if not already loading
    }
  }
}

// You can implement the search function here if needed
const isLoading = ref(false)

const performSearch = () => {
  // Add your search logic here
  console.log("Performing search for: ", searchQuery.value)
}

</script>


<template>
  <div class="flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="container mx-auto px-8 my-2 pt-2 flex justify-between items-center">
        <div class="flex items-center">
          <router-link to="/" class="flex items-center">
            <div class="flex items-center justify-center">
                 <img src="../assets/images/ss.jpg" alt="Workspace" class="w-[55px] h-[55px] object-cover " />
            </div>
          </router-link>
        </div>
        <!-- Title in the center -->
        <div class="flex-grow text-center">
          <span class="text-6xl font-koulen text-gray-800">ONLYREN</span>
        </div>
        <div class="flex items-center space-x-4">
          <router-link to="/messages" class="text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </router-link>
          <router-link to="/login" class="bg-white border border-gray-300 rounded px-6 py-2 text-sm">
            Masuk
          </router-link>
        </div>
      </div>
    </header>

    <!-- Hero section with search -->
    <section class="bg-white">
      <div class="mx-0 px-0 py-4">
        <div class="relative">
          <img src="@/assets/images/workspace-hero.jpg" alt="Workspace" class="w-full h-128 object-cover " />
          <div class="absolute inset-0 bg-black bg-opacity-10 rounded-lg"></div>
          <div class="absolute top-1/2 left-1/3 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md px-4">
            <!-- Slogan -->
            <div class="text-left mb-6">
              <h1 class="text-black text-xl font-extralight drop-shadow-lg">
                Dari Freelancer hingga Startup – Ruang untuk Semua
              </h1>
            </div>
            <!-- Search Box -->
            <div class="bg-white rounded-lg shadow-md flex items-center p-2">
                <input 
            v-model="searchQuery"
            type="text" 
            placeholder="Cari space" 
            class="flex-grow w-[300px] px-2 py-2 text-sm focus:outline-none" 
                  :disabled="isLoading" 
          />
          <button @click="handleSearch" class="bg-orange-500 text-white p-2 rounded-lg" :disabled="isLoading">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
            </svg>
          </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Lokasi Populer Section -->
    <section class="bg-white py-8">
      <div class="container mx-auto px-8">
        <h2 class="text-xl italic font-extralight mb-6 text-center">Lokasi <b>Populer!</b></h2>
        <div class="grid grid-cols-4 gap-4 mb-6">
          <!-- Semarang -->
          <div class="relative rounded-lg overflow-hidden h-48">
            <img src="../assets/images/semarang.png" alt="Semarang" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-30"></div>
            <div class="absolute bottom-4 left-4">
              <h3 class="text-white text-lg font-semibold">Semarang</h3>
            </div>
          </div>
          
          <!-- Jogja -->
          <div class="relative rounded-lg overflow-hidden h-48">
            <img src="../assets/images/jogja.png" alt="Jogja" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-30"></div>
            <div class="absolute bottom-4 left-4">
              <h3 class="text-white text-lg font-semibold">Jogja</h3>
            </div>
          </div>
          
          <!-- Jakarta -->
          <div class="relative rounded-lg overflow-hidden h-48">
            <img src="../assets/images/jakarta.png" alt="Jakarta" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-30"></div>
            <div class="absolute bottom-4 left-4">
              <h3 class="text-white text-lg font-semibold">Jakarta</h3>
            </div>
          </div>
          
          <!-- Bandung -->
          <div class="relative rounded-lg overflow-hidden h-48">
            <img src="../assets/images/bandung.png" alt="Bandung" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-black bg-opacity-30"></div>
            <div class="absolute bottom-4 left-4">
              <h3 class="text-white text-lg font-semibold">Bandung</h3>
            </div>
          </div>
        </div>
        
        <div class="flex justify-center">
          <button class="text-gray-600 text-sm underline hover:text-gray-800">
            Lihat semua
          </button>
        </div>
      </div>
    </section>

    <!-- Info Cards Section -->
    <section class="bg-gray-50 py-12">
      <div class="container mx-auto px-8">
        <div class="grid grid-cols-2 gap-8">
          <!-- Left Card - Co-Working Space -->
          <div class="bg-blue-100 rounded-lg p-8 flex items-center">
            <div class="flex-1">
              <h3 class="text-lg font-sans mb-2">
                Kelola <span class="text-blue-600">Co-Working Space</span> lebih <span class="text-blue-600">Mudah, Cepat</span>, 
                dan <span class="text-blue-600">Teorganisir</span>. Bangun pengalaman terbaik 
                untuk penyewa dan pengelola - semua dalam satu platform
              </h3>
            </div>
            <div class="ml-6">
              <div class="w-20 h-20 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
            </div>
          </div>

          <!-- Right Card - Platform Features -->
          <div class="bg-orange-100 rounded-lg p-8 flex items-center">
            <div class="mr-6">
              <div class="w-20 h-20 bg-orange-500 rounded-lg flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-lg font-sans">
                <span class="text-orange-600">Platform all-in-one</span> untuk mengelola reservasi, 
                ruang kerja, pembayaran, dan fasilitas tambahan. 
                Cocok untuk <span class="text-orange-600">freelancer, startup, dan tim kerja</span> 
                yang dinamis.
              </h3>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
      <OnlyFooter />
  </div>
</template>

