<script setup>

</script>

<template>
  <div class="login_layout_container">
    <div class="grid sm:grid-cols-2 gap-2">
      <div class="login_layout_img">
      </div>

      <div class="login_layout_content p-4">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<style scoped>
.login_layout_container {
  background-color: #2F6C94;
  height: 100%;
}
.login_layout_img {
  border-top-right-radius: 43px;
  border-bottom-right-radius: 43px;
  overflow: hidden;
  background-image: url('/img/wow-experience.jpg');
  background-size: cover;
  background-position: -850px 0;
  background-repeat: no-repeat;
}
</style>