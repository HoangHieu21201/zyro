<template>
  <div class="user-layout d-flex flex-column min-vh-100">
    <Header />

    <main class="flex-grow-1">
      <router-view></router-view>
    </main>

    <Footer />

  </div>
</template>

<script setup>
import Header from '../components/client/Header.vue';
import Footer from '../components/client/Footer.vue';
import router from '@/router';
</script>