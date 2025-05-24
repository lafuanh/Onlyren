<template>
  <div class="flex flex-col min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm">
      <div class="container mx-auto px-8 py-4 flex justify-between items-center">
        <div class="flex items-center">
          <router-link to="/" class="flex items-center">
            <div class="flex items-center justify-center">
                 <img src="@/assets/images/ss.jpg" alt="Workspace" class="w-[90px] h-[90px] object-cover " />
            </div>
          </router-link>
        </div>
             <!-- Title in the center -->
      <div class="flex-grow text-center">
        <span class="text-6xl font-bold text-gray-800">ONLYREN</span>
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
          <img src="@/assets/images/workspace-hero.jpg" alt="Workspace" class="w-full h-96 object-cover " />
          <div class="absolute inset-0 bg-black bg-opacity-10 rounded-lg"></div>
          <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-md px-4">
            <div class="bg-white rounded-lg shadow-md flex items-center p-2">
              <input 
                type="text" 
                placeholder="Cari space produktivitas-mu" 
                class="flex-grow px-4 py-2 text-sm focus:outline-none" 
              />
              <button class="bg-orange-500 text-white p-2 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Empty gray section -->
    <section class="bg-gray-200 py-12">
      <div class="container mx-auto px-4">
        <!-- This is the gray section shown in the image -->
      </div>
    </section>

    <!-- Feature rooms section -->
    <section class="bg-white py-8">
      <div class="container mx-auto px-4">
        <h2 class="text-lg font-semibold mb-4">Fitur ruang yang bisa didapatkan!</h2>
        <div class="grid grid-cols-4 gap-4">
          <div v-for="i in 4" :key="`feature-${i}`" class="border border-gray-300 rounded-lg aspect-square"></div>
        </div>
      </div>
    </section>

    <!-- Popular spaces section -->
    <section class="bg-white py-8 border-t border-gray-200">
      <div class="container mx-auto px-4">
        <h2 class="text-lg font-semibold mb-4">Space Populer</h2>
        <div class="grid grid-cols-4 gap-4">
          <div v-for="i in 4" :key="`popular-${i}`" class="space-y-2">
            <div class="border border-gray-300 rounded-lg aspect-square"></div>
            <p class="text-sm text-gray-500">Lorem ipsum</p>
          </div>
        </div>
        <div class="flex justify-center mt-4">
          <div class="flex items-center space-x-2">
            <button class="border border-gray-300 rounded-full p-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <button class="bg-orange-500 text-white rounded px-4 py-1 text-sm">Lihat Semua</button>
            <button class="border border-gray-300 rounded-full p-1">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-200 py-6 mt-auto">
      <div class="container mx-auto px-4">
        <!-- Footer content -->
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'HomePage',
  data() {
    return {
      // Data properties here
    }
  },
  methods: {
    // Methods here
  }
}
</script>