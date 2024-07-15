<script setup>
import Menu from "../components/MenuComponent.vue";

</script>

<template>
  <Menu>
    <template #content>
      <router-view></router-view>
    </template>
  </Menu>
</template>
