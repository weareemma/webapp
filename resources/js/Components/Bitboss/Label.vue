<template>
    <label class="bb-label">
        <template v-if="value">{{ value }}</template>
        <template v-else><slot></slot></template>
    </label>
</template>

<script>
    import { defineComponent } from 'vue'

    export default defineComponent({
        props: ['value']
    })
</script>
