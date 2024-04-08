<script setup>
import StylistLayout from '@/Layouts/StylistLayout.vue';
import {ref} from "vue";
import {useForm, usePage} from "@inertiajs/inertia-vue3";
import helpers from "../../helpers";
import {Inertia} from "@inertiajs/inertia";
import {
  PencilIcon,
  TrashIcon
} from "@heroicons/vue/solid";

const props = defineProps({
  user: Object
});

const password = {
  password: null,
  password_confirmation: null,
  photo: null
}
const form = useForm({...props.user, ...password});

const photoPreview = ref(null);
const photoInput = ref(null);

function updateProfile()
{
  if (photoInput.value) {
    form.photo = photoInput.value.files[0];
  }

  form.post(route("stylist.profile.update"), {
    preserveScroll: true,
    onSuccess: (res) => {
      helpers.flash(res.props.flash);
      clearPhotoFileInput();
    }
  });
}

const selectNewPhoto = () => {
  photoInput.value.click();
};
const updatePhotoPreview = () => {
  const photo = photoInput.value.files[0];

  helpers.lg(photo.size);
  if (photo.size > 2000000)
  {
    helpers.flash({
      type: 'success',
      message: "Non puoi caricare un'immagine più grande di 2 MB"
    });
    return;
  }

  if (! photo) return;

  const reader = new FileReader();

  reader.onload = (e) => {
    photoPreview.value = e.target.result;
  };

  reader.readAsDataURL(photo);
};

const deletePhoto = () => {
  Inertia.delete(route('current-user-photo.destroy'), {
    preserveScroll: true,
    onSuccess: () => {
      photoPreview.value = null;
      clearPhotoFileInput();
    },
  });
};

const clearPhotoFileInput = () => {
  if (photoInput.value?.value) {
    photoInput.value.value = null;
  }

  form.reset('photo');
};

</script>

<template>
  <StylistLayout title="Dati account">
    <div class="mx-auto w-full md:w-1/2">
      <h3 class="text-xl text-bb-blue-500 font-extrabold mb-5 text-center">Dati account</h3>

      <div class="bb-card py-8 px-10 shadow-xl">
        <div class="my-4">
          <bb-label class="mb-1">Foto profilo</bb-label>
          <div class="col-span-6 sm:col-span-4">
            <!-- Profile Photo File Input -->
            <input
                ref="photoInput"
                type="file"
                class="hidden"
                accept=".jpg, .jpeg, .png"
                @change="updatePhotoPreview"
            >

            <div class="flex justify-start items-center gap-2">
              <!-- Current Profile Photo -->
              <div v-show="! photoPreview" class="mt-2">
                <img :src="user.profile_photo_url" :alt="user.name" class="rounded-full h-20 w-20 object-cover">
              </div>

              <!-- New Profile Photo Preview -->
              <div v-show="photoPreview" class="mt-2">
                    <span
                        class="block rounded-full w-20 h-20 bg-cover bg-no-repeat bg-center"
                        :style="'background-image: url(\'' + photoPreview + '\');'"
                    />
              </div>

              <button type="button" class="mt-2 icon-button" @click.prevent="selectNewPhoto">
                <PencilIcon />
              </button>

              <button
                  v-if="user.profile_photo_path || form.photo"
                  type="button"
                  class="mt-2 icon-button"
                  @click.prevent="deletePhoto"
              >
                <TrashIcon />
              </button>
            </div>

            <bb-input-validation :form="form" name="photo"></bb-input-validation>
          </div>
        </div>
        <div class="my-4">
          <bb-label class="mb-1">Email</bb-label>
          <bb-input type="email" placeholder="Email" v-model="form.email"></bb-input>
          <bb-input-validation :form="form" name="email"></bb-input-validation>
        </div>
        <div class="my-4">
          <bb-label class="mb-1">Password</bb-label>
          <bb-input type="password" placeholder="Password" v-model="form.password"></bb-input>
          <bb-input-validation :form="form" name="password"></bb-input-validation>
        </div>
        <div class="my-4">
          <bb-label class="mb-1">Conferma password</bb-label>
          <bb-input type="password" placeholder="Conferma Password" v-model="form.password_confirmation"></bb-input>
          <bb-input-validation :form="form" name="password_confirmation"></bb-input-validation>
        </div>
        <div class="flex justify-end items-center mt-4">
          <bb-button type="button" @click="updateProfile" :disabled="form.processing">
            <span>Salva</span>
          </bb-button>
        </div>
      </div>
    </div>
  </StylistLayout>
</template>