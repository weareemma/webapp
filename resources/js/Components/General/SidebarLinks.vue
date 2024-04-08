<template>
  <template v-for="(item, i) in links">
    <!-- group -->
    <div v-if="'children' in item && item.can" :key="i" class="pt-2 md:pt-10 md:mb-1">
      <!-- group title -->
      <div class="flex items items-center md:mb-4">
        <h5 class="uppercase text-xs text-bb-gray-700 mr-4">
          {{ item.name }}
        </h5>
        <div v-if="item.name" class="flex-1 h-px bg-bb-gray-400"></div>
      </div>

      <!-- group children -->
      <div class="pl-2">
        <SidebarLinks :links="item.children" />
      </div>
    </div>

    <!-- link -->
    <Link
      v-if="!item.children && item.can"
      :key="i"
      :href="item.href ?? '#'"
      class="group flex items-center px-2 py-2 rounded-md border border-white transition hover:bg-bb-blue-600"
      :class="{
        'bg-bb-blue-800 hover:bg-bb-blue-800': item.current,
        'mb-4': item.spaced,
      }"
    >
      <component
        :is="item.icon"
        class="mr-4 h-6 w-6 text-bb-gray-800 transition group-hover:text-white transform-gpu"
        :class="{
          '!text-white': item.current,
        }"
        aria-hidden="true"
      />
      <div
        class="text-bb-blue-800 transition group-hover:text-white flex justify-between gap-x-4 w-full"
        :class="{
          '!text-white': item.current,
          underline: item.current,
        }"
      >
        {{ item.name }}
        <span v-if="item.warning">
          <ExclamationIcon class="w-6 h-6" :class="{
            '!text-red-600': ! item.current,
            '!text-white': item.current,
          }" />
        </span>
      </div>
    </Link>
  </template>
</template>

<script setup>
import {
  ExclamationIcon
} from '@heroicons/vue/solid'
defineProps({
  links: Array,
});
</script>
