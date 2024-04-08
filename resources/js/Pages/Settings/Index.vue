<script setup>
import {ref, computed, onMounted} from 'vue';
import { useForm, usePage, Link } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from '@/Layouts/AppLayout.vue';
import helpers from "../../helpers";

const props = defineProps({
  setting: Object,
  form_data: Object
});

const form = useForm(props.form_data);

onMounted(() => {
//   helpers.lg(props.form_data);
})

function saveSetting()
{
  form.put(route("setting.update", props.setting.id), {
    preserveScroll: true,
    onSuccess: (res) => {
      helpers.flash(res.props.flash)
    }
  });
}

function updateOption(v)
{
  let targetName = v.target.name;
  form[targetName] = v.target.value;
}


</script>

<template>
  <AppLayout title="Impostazioni generali">
    <div class="px-[15vw]">
      <div class="bb-card p-8" v-if="setting && setting.data.length > 0">
        <div v-for="(option, index) in setting.data" :key="index" class="mb-4">
          <bb-label class="mb-1">{{ option.label }}</bb-label>
          <bb-input
              :type="option.type"
              :value="option.value"
              :name="option.name"
              @change="updateOption"
          ></bb-input>
          <bb-input-validation :form="form" :name="option.name"></bb-input-validation>
          <p v-if="option.desc" class="text-sm text-bb-gray-700 my-1">{{ option.desc }}</p>
        </div>

        <div class="flex justify-end items-center">
          <bb-button @click="saveSetting">Salva</bb-button>
        </div>
      </div>

      <div v-else class="bb-card p-8">
        <p class="text-center">Non sono presenti impostazioni generali</p>
      </div>
    </div>

  </AppLayout>
</template>