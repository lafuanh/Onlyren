<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <OnlyHeader />

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center px-4 sm:px-6 lg:px-8">
      <div class="max-w-md w-full text-center">
        <div class="mb-8">
          <svg
            class="mx-auto h-32 w-32 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="1"
              d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        
        <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Halaman Tidak Ditemukan</h2>
        <p class="text-gray-600 mb-8">
          Maaf, halaman yang Anda cari tidak ada. Mungkin URL salah atau halaman telah dipindahkan.
        </p>
        
        <div class="space-y-4">
          <router-link
            to="/"
            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-orange-500 hover:bg-orange-600 transition-colors w-full"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Kembali ke Beranda
          </router-link>
          
          <button
            @click="$router.go(-1)"
            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors w-full"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Halaman Sebelumnya
          </button>
        </div>
        
        <div class="mt-8 pt-8 border-t border-gray-200">
          <p class="text-sm text-gray-500">
            Atau coba mencari ruang kerja:
          </p>
          <div class="mt-4">
            <router-link
              to="/search"
              class="text-orange-500 hover:text-orange-400 font-medium"
            >
              Cari Ruang Kerja
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t mt-8">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-center">
          <p class="text-gray-500 text-sm">
            © 2025 Onlyren. Semua hak dilindungi.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import OnlyHeader from '@/components/OnlyHeader.vue'
const router = useRouter()
</script>