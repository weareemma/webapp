<script setup>
import { ref, computed, onMounted, onUpdated } from "vue";
import { useForm, usePage } from "@inertiajs/inertia-vue3";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";
import helpers from "../../helpers";
import { TrashIcon } from "@heroicons/vue/solid";

const props = defineProps({
  model: Object,
  levels: Object,
  types: Object
});

const form = useForm(props.model);
const photo_url = ref(props.model.photo_url);

function storeModel() {
  if (form.id) {
    form.put(route("hairService.update", form.id), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.flash(res.props.flash);
      },
    });
  } else {
    form.post(route("hairService.store"), {
      preserveScroll: true,
      onSuccess: (res) => {
        helpers.flash(res.props.flash);
      },
    });
  }
}

function updateUidTempMedia(data) {
  form.uid = data.uid;
}

function deleteMedia() {
  photo_url.value = null;
  form.uid = "delete";
}
</script>

<template>
  <AppLayout title="Utente" :show-title="false">
    <div class="flex justify-start items-center mt-4 mb-6">
      <bb-back-link></bb-back-link>
    </div>

    <div class="bb-card p-8">
      <h1 class="text-bb-blue-500 mb-4 big-header-title">
        <span v-if="form.id">Modifica Servizio</span>
        <span v-else>Aggiungi nuovo servizio</span>
      </h1>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5 gap-y-4">
        <div>
          <bb-label class="mb-1">Nome</bb-label>
          <bb-input
            type="text"
            placeholder="Nome"
            v-model="form.title"
            disabled
          ></bb-input>
          <bb-input-validation :form="form" name="title"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Livello</bb-label>
          <bb-radio-group
              class="py-2"
              v-model="form.level"
              :options="levels"
              disabled
          ></bb-radio-group>
          <bb-input-validation :form="form" name="level"></bb-input-validation>
        </div>
        <div v-if="form.level === 'primary'" class="sm:col-span-2 flex justify-start items-center my-2">
          <bb-checkbox v-model="form.dry_style"></bb-checkbox>
          <bb-label class="ml-2 mr-4">No add-on</bb-label>
          <bb-checkbox v-model="form.afro"></bb-checkbox>
          <bb-label class="ml-2">Afro</bb-label>
        </div>
        <div v-if="form.level === 'addon'" class="sm:col-span-2">
          <bb-label class="mb-1">Tipologia</bb-label>
          <bb-radio-group
              class="py-2"
              v-model="form.type"
              :options="types"
          ></bb-radio-group>
          <bb-input-validation :form="form" name="type"></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Durata (minuti)</bb-label>
          <bb-input
            type="number"
            placeholder="Durata"
            v-model="form.execution_time"
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="execution_time"
          ></bb-input-validation>
        </div>
        <div>
          <bb-label class="mb-1">Prezzo iva inclusa</bb-label>
          <bb-input
            type="number"
            placeholder="Prezzo iva inclusa"
            v-model="form.net_price"
            disabled
          ></bb-input>
          <bb-input-validation
            :form="form"
            name="net_price"
          ></bb-input-validation>
        </div>
        <div class="sm:col-span-2">
          <bb-label class="mb-1">Descrizione</bb-label>
          <bb-textarea
            class="min-h-[180px]"
            type="text"
            v-model="form.description"
          ></bb-textarea>
          <bb-input-validation
            :form="form"
            name="description"
          ></bb-input-validation>
        </div>
<!--        <div class="sm:col-span-2">-->
<!--          <bb-label class="mb-4">-->
<!--            <p class="mb-0">Foto</p>-->
<!--            <p class="text-sm text-bb-gray-700">-->
<!--              Carica una foto rappresentativa del servizio-->
<!--            </p>-->
<!--          </bb-label>-->
<!--          <div v-if="photo_url" class="flex justify-start items-center">-->
<!--            <img :src="photo_url" />-->
<!--            <button-->
<!--              @click="deleteMedia"-->
<!--              class="bg-bb-gray-200 text-bb-gray-600 h-7 w-7 rounded p-1 transition-colors hover:text-bb-danger ml-4"-->
<!--            >-->
<!--              <TrashIcon />-->
<!--            </button>-->
<!--          </div>-->
<!--          <bb-uploader-->
<!--            v-else-->
<!--            :submitRoute="route('media.upload')"-->
<!--            mediaCollection="hairService:photo"-->
<!--            :mimeTypes="['image/png', 'image/jpeg', 'image/jpg']"-->
<!--            dragText="Clicca qui o trascina il file per caricarlo"-->
<!--            dropText="Rilascia qui il file per caricarlo"-->
<!--            :multiple="false"-->
<!--            @completed="updateUidTempMedia"-->
<!--          ></bb-uploader>-->
<!--        </div>-->
        <div>
          <bb-label class="mb-1">Ordine</bb-label>
          <bb-input
              type="number"
              placeholder=""
              v-model="form.order"
              disabled
          ></bb-input>
          <bb-input-validation
              :form="form"
              name="order"
          ></bb-input-validation>
        </div>
        <div class="sm:col-span-2 flex justify-start items-center">
          <bb-switch v-model="form.active"></bb-switch>
          <bb-label class="mb-0 ml-2">Servizio attivo</bb-label>
        </div>
      </div>

      <div class="flex justify-end items-center mt-4">
        <bb-button
          type="button"
          @click="storeModel"
          :disabled="form.processing"
        >
          <span v-if="form.id">Salva</span>
          <span v-else>Aggiungi</span>
        </bb-button>
      </div>
    </div>
  </AppLayout>
</template>
