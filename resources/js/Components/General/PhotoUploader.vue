<script setup>
import {ref, onMounted, computed, watch} from "vue";
import {Inertia} from "@inertiajs/inertia";
import { TrashIcon, PlusCircleIcon } from "@heroicons/vue/solid";
import helpers from "../../helpers";

const props = defineProps({
  photos: Object
})

const currentPhotos = ref(props.photos);
const photoInput = ref(null);
const newPhotoCounter = ref(-1);

const emit = defineEmits(['changed'])

function deletephoto(id)
{
  currentPhotos.value = currentPhotos.value.filter((i) => i.id !== id);
  emit('changed', currentPhotos.value);
}
const selectNewPhoto = () => {
  photoInput.value.click();
};

const updatePhotoPreview = () => {
  helpers.lg(photoInput.value.files);
  for (const photo of photoInput.value.files) {
    if (photo.size > 4000000)
    {
      helpers.flash({
        type: 'error',
        message: "Non puoi caricare un'immagine più grande di 4 MB"
      });
      return;
    }
  }

  for (const photo of photoInput.value.files)
  {
    if (! photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
      currentPhotos.value.push({
        id: newPhotoCounter.value,
        url: e.target.result,
        file: photo
      });

      newPhotoCounter.value--;
    };

    reader.readAsDataURL(photo);
  }
  emit('changed', currentPhotos.value);
};

const upload = () =>
{
  helpers.lg(currentPhotos.value);
}


</script>

<template>
  <div>
    <div>

    </div>
    <div class="flex justify-start items-center gap-4">
      <div class="relative inline-block">
        <plus-circle-icon @click.prevent="selectNewPhoto" class="h-16 w-16 text-bb-gray-200 cursor-pointer"></plus-circle-icon>
        <input
            ref="photoInput"
            type="file"
            class="hidden"
            accept=".jpg, .jpeg, .png"
            @change="updatePhotoPreview"
            multiple
        >
      </div>
      <div class="flex justify-start items-center gap-3 flex-wrap">
        <div v-for="photo in currentPhotos" :key="photo.id" class="relative">
          <img class="h-16 w-16 rounded-full" :src="photo.url" alt="">
          <span class="absolute bottom-0 right-0 block translate-y-1/2 translate-x-1/2 transform rounded-full border-0 border-white">
          <trash-icon @click="deletephoto(photo.id)" class="block h-5 w-5 rounded-full text-red-600 cursor-pointer"></trash-icon>
        </span>
        </div>
      </div>
    </div>
  </div>
</template>