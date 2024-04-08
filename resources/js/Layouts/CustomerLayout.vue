<script setup>
import { onMounted, ref, computed } from "vue";
import { Head, usePage, Link } from "@inertiajs/inertia-vue3";
import {
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
} from "@headlessui/vue";
import {
  MenuIcon,
  XIcon,
  CalendarIcon,
  UserIcon,
  TicketIcon,
  PhotographIcon,
  CreditCardIcon,
  ChatIcon,
  AdjustmentsIcon,
  LogoutIcon,
} from "@heroicons/vue/outline";
import {
  PencilIcon,
  ViewGridIcon,
  ChevronRightIcon,
} from "@heroicons/vue/solid";
import Logo from "@/Components/General/Logo.vue";
import { Inertia } from "@inertiajs/inertia";
import helpers from "../helpers";
import Footer from "./Footer.vue";

const user = computed(() => usePage().props.value.user);
const impersonate = computed(() => usePage().props.value.impersonate);
const bookingLocked = computed(() => {
  if (impersonate.value) return false;
  return usePage().props.value.booking_locked;
});

const showPhotoProfileRemember = computed(
  () => ( ! route().current("buy.subscription.checkout")) && usePage().props.value.customer.photo_profile_remember
);
const notificationsCount = computed(
  () => usePage().props.value.customer.notifications_count
);

onMounted(() => {
  if (showPhotoProfileRemember.value) {
    photoProfileModal.value.open();
  }

  // helpers.lg(impersonate.value)
});

const photoProfileModal = ref(null);
function hidePhotoProfileModal() {
  Inertia.post(
    route("customer.photoProfileModal.hide"),
    {},
    {
      onSuccess: () => {
        photoProfileModal.value.close();
      },
    }
  );
}
function gotoProfile() {
  Inertia.post(
    route("customer.photoProfileModal.hide"),
    {},
    {
      onSuccess: () => {
        Inertia.visit(route("customer.profile"));
      },
    }
  );
}

const headerNavigation = [
  // { name: "Servizi", href: "#", current: false },
  // { name: "Membership", href: "#", current: false },
  // { name: "Location", href: "#", current: false },
  // { name: "Contatti", href: "#", current: false },
];

const navigation = [
  {
    name: "Dashboard",
    href: route("customer.dashboard"),
    current: route().current("customer.dashboard"),
    icon: ViewGridIcon,
  },
  {
    name: "Appuntamenti",
    href: route("customer.bookings.future"),
    current: route().current("customer.bookings.*"),
    icon: CalendarIcon,
  },
  {
    name: "Profilo",
    href: route("customer.profile"),
    current: route().current("customer.profile"),
    icon: UserIcon,
  },
  {
    name: "Abbonamenti",
    href: route("plan.detail"),
    current: false,
    icon: TicketIcon,
  },
  {
    name: "Gallery",
    href: route("customer.gallery"),
    current: route().current("customer.gallery"),
    icon: PhotographIcon,
  },
  {
    name: "Pagamenti",
    href: route("customer.payments"),
    current: route().current("customer.payments"),
    icon: CreditCardIcon,
  },
  {
    name: "Notifiche",
    href: route("customer.notifications"),
    current: route().current("customer.notifications"),
    icon: ChatIcon,
  },
  // { name: "Impostazioni", href: "#", current: false, icon: AdjustmentsIcon },
];
const userNavigation = [
  { name: "Profilo", href: route("customer.profile") },
  { name: "Pagamenti", href: route("customer.payments") },
];

defineProps({
  title: String,
  showTitle: { type: Boolean, default: true },
});

const logout = () => {
  Inertia.post(route("logout"));
};

// Photo profile
const photoInput = ref(null);
const selectNewPhoto = () => {
  photoInput.value.click();
};
const savePhotoProfile = () => {
  const photo = photoInput.value.files[0];

  if (photo.size > 2000000) {
    helpers.flash({
      type: "error",
      message: "Non puoi caricare un'immagine più grande di 2 MB",
    });
    return;
  }

  Inertia.post(
    route("customer.profile.photo.update"),
    {
      photo: photoInput.value.files[0],
    },
    {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: (res) => {
        helpers.lg(res);
        clearPhotoFileInput();
        helpers.flash(res.props.flash);
      },
    }
  );
};
const clearPhotoFileInput = () => {
  if (photoInput.value?.value) {
    photoInput.value.value = null;
  }
};

// mobile profile toggle
const mobileProfileOpen = ref(false);
const body = document.querySelector("body");
function toggleMobileProfile(forcedState = null) {
  const isOpen = forcedState !== null ? !forcedState : mobileProfileOpen.value;

  if (isOpen) {
    body.style.overflow = "initial";
    mobileProfileOpen.value = false;
  } else {
    body.style.overflow = "hidden";
    mobileProfileOpen.value = true;
  }
}
</script>

<template>
  <div class="min-h-screen bg-white flex flex-col justify-between">
    <Head :title="title" />

    <Disclosure
      as="nav"
      class="fixed z-20 sm:static w-full bg-white shadow-md sm:shadow-sm"
      v-slot="{ open, close: closeDisclosure }"
    >
      <div class="mx-auto px-4 sm:px-6 lg:px-8 py-2">
        <div class="flex justify-between h-16">
          <div class="flex justify-between items-center flex-grow">
            <!-- Mobile menu button -->
            <div class="-mr-2 flex items-center sm:hidden">
<!--              <DisclosureButton-->
<!--                class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-0"-->
<!--                @click="toggleMobileProfile(false)"-->
<!--              >-->
<!--                <span class="sr-only">Open main menu</span>-->
<!--                <MenuIcon-->
<!--                  v-if="!open"-->
<!--                  class="block h-6 w-6"-->
<!--                  aria-hidden="true"-->
<!--                />-->
<!--                <XIcon v-else class="block h-6 w-6" aria-hidden="true" />-->
<!--              </DisclosureButton>-->
            </div>

            <!-- logo -->
            <div class="flex-shrink-0 flex items-center">
              <Logo :light="false"></Logo>
            </div>

            <!-- desktop links -->
            <div
              class="hidden items-center sm:-my-px sm:ml-6 sm:flex sm:space-x-2 lg:space-x-8"
            >
              <!-- links -->
              <a
                v-for="item in headerNavigation"
                :key="item.name"
                :href="item.href"
                :class="[
                  item.current
                    ? 'text-bb-blue-700'
                    : 'border-transparent text-bb-blue-500 hover:text-bb-gray-700',
                  'inline-flex items-center px-1 pt-1 text-sm font-semibold',
                ]"
                :aria-current="item.current ? 'page' : undefined"
              >
                {{ item.name }}
              </a>

              <!-- booking button -->
              <Link :href="route('booking.wizard')" v-if="! bookingLocked">
                <bb-button
                  type="button"
                  class="hidden sm:block px-4 lg:px-8 py-1 text-sm"
                >
                  Prenota
                </bb-button>
              </Link>

              <!-- Profile dropdown -->
              <Menu as="div" class="hidden sm:block ml-3 relative">
                <div>
                  <MenuButton
                    class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    <span class="sr-only">Open user menu</span>
                    <img
                      class="h-8 w-8 rounded-full object-cover"
                      :src="user.profile_photo_url"
                      alt=""
                    />
                  </MenuButton>
                </div>
                <transition
                  enter-active-class="transition ease-out duration-200"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <MenuItems
                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                  >
                    <MenuItem
                      v-for="item in userNavigation"
                      :key="item.name"
                      v-slot="{ active }"
                    >
                      <a
                        :href="item.href"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'block px-4 py-2 text-sm text-gray-700',
                        ]"
                      >
                        {{ item.name }}
                      </a>
                    </MenuItem>
                    <MenuItem v-slot="{ active }">
                      <a
                        @click="logout"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'block px-4 py-2 text-sm text-gray-700 cursor-pointer',
                        ]"
                      >
                        Log out
                      </a>
                    </MenuItem>
                    <MenuItem v-if="impersonate" v-slot="{ active }">
                      <a
                        :href="route('impersonate.leave')"
                        :class="[
                          active ? 'bg-gray-100' : '',
                          'block px-4 py-2 text-sm text-bb-danger cursor-pointer',
                        ]"
                      >
                        Torna amministratore
                      </a>
                    </MenuItem>
                  </MenuItems>
                </transition>
              </Menu>
            </div>

            <!-- mobile profile dropdown -->
            <div class="block sm:hidden">
              <button
                @click="
                  () => {
                    toggleMobileProfile();
                    closeDisclosure();
                  }
                "
              >
                <span class="sr-only">Toggle user menu</span>
                <img
                  v-if="!mobileProfileOpen"
                  class="h-8 w-8 rounded-full object-cover"
                  :src="user.profile_photo_url"
                />
                <div
                  v-else
                  class="h-8 w-8 bg-bb-gray-100 rounded-full grid place-items-center"
                >
                  <XIcon class="h-5 w-5" />
                </div>
              </button>
              <teleport to="body">
                <transition
                  enter-active-class="transition ease-out duration-150"
                  enter-from-class="transform opacity-0"
                  enter-to-class="transform opacity-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100"
                  leave-to-class="transform opacity-0"
                >
                  <div
                    v-show="mobileProfileOpen"
                    class="fixed z-10 top-20 left-0 bottom-0 w-full bg-white flex flex-col"
                  >
                    <div class="flex-1 flex flex-col items-center pt-7">
                      <!-- profile image -->
                      <div class="flex flex-col justify-start items-center">
                        <div class="relative inline-block">
                          <img
                            class="h-24 w-24 rounded-full object-cover"
                            :src="user.profile_photo_url"
                            alt=""
                          />
                          <PencilIcon
                            @click="selectNewPhoto"
                            class="absolute bottom-0 right-0 block h-6 w-6 p-1 rounded-full bg-white text-bb-blue-500 cursor-pointer"
                          />
                          <input
                            ref="photoInput"
                            type="file"
                            class="hidden"
                            accept=".jpg, .jpeg, .png"
                            @change="savePhotoProfile"
                          />
                        </div>
                        <p class="mt-3 text-bb-blue-500 text-md font-bold">
                          {{ user.full_name }}
                        </p>
                        <Link
                          :href="route('customer.profile')"
                          class="text-xs underline"
                        >
                          Visualizza profilo
                        </Link>
                      </div>

                      <!-- links -->
                      <nav
                        class="mt-5 flex-1 w-full overflow-auto px-7 pb-8 max-h-[calc(100vh-277px)]"
                      >
                        <div class="space-y-1 px-2 divide-y">
                          <div
                            v-for="item in navigation"
                            :key="item.name"
                            class="pt-1"
                          >
                            <Link
                              :href="item.href"
                              :class="[
                                item.current
                                  ? 'text-white bg-bb-lightblue'
                                  : 'text-bb-blue-500',
                                'group flex items-center justify-between px-4 py-2 font-semibold rounded-full',
                              ]"
                            >
                              <div class="flex items-center">
                                <component
                                  :is="item.icon"
                                  :class="[
                                    item.current
                                      ? 'text-white'
                                      : 'text-bb-blue-500',
                                    'mr-3 h-6 w-6 stroke-1',
                                  ]"
                                  aria-hidden="true"
                                />
                                {{ item.name }}
                                <span
                                  v-if="
                                    item.name === 'Notifiche' &&
                                    notificationsCount > 0
                                  "
                                  :class="[
                                    item.current
                                      ? 'bg-white text-bb-blue-500'
                                      : 'bg-bb-blue-500 text-white group-hover:bg-white group-hover:text-bb-blue-500',
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ml-2',
                                  ]"
                                  >{{ notificationsCount }}</span
                                >
                              </div>
                              <ChevronRightIcon class="h4 w-4" />
                            </Link>
                          </div>

                          <!-- logout and leave impersonate -->

                          <a
                            @click="() => (impersonate ? null : logout())"
                            :href="
                              impersonate ? route('impersonate.leave') : '#'
                            "
                            class="text-bb-blue-500 group flex items-center justify-between px-4 py-2 cursor-pointer font-bold rounded-md"
                          >
                            <div class="flex items-center">
                              <LogoutIcon
                                class="text-bb-blue-500 mr-3 h-6 w-6 stroke-1"
                              />
                              Logout
                            </div>
                            <ChevronRightIcon class="h4 w-4" />
                          </a>
                        </div>
                      </nav>
                    </div>

                    <!-- footer -->
                    <!-- <Footer /> -->
                  </div>
                </transition>
              </teleport>
            </div>
          </div>
        </div>
      </div>

      <teleport to="body">
        <transition
          enter-active-class="transition ease-out duration-150"
          enter-from-class="transform opacity-0"
          enter-to-class="transform opacity-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100"
          leave-to-class="transform opacity-0"
        >
          <DisclosurePanel
            class="sm:hidden fixed z-10 top-20 left-0 bottom-0 w-full bg-white"
          >
            <div class="pt-2 pb-3 space-y-1">
              <DisclosureButton
                v-for="item in headerNavigation"
                :key="item.name"
                as="a"
                :href="item.href"
                :class="[
                  item.current
                    ? 'bg-indigo-50 border-indigo-500 text-indigo-700'
                    : 'border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800',
                  'block pl-3 pr-4 py-2 border-l-4 text-base font-medium',
                ]"
                :aria-current="item.current ? 'page' : undefined"
              >
                {{ item.name }}
              </DisclosureButton>
            </div>
          </DisclosurePanel>
        </transition>
      </teleport>
    </Disclosure>

    <div class="pt-20 sm:pt-0 pb-10 sm:py-10 grow">
      <main>
        <div class="mx-auto w-full max-w-7xl flex-grow lg:flex xl:px-8 scroll-auto">
          <!-- Left sidebar & main wrapper -->
          <div class="min-w-0 flex-1 bg-white md:flex">
            <!-- only desktop -->
            <div class="hidden bg-white xl:w-64 xl:flex-shrink-0" :class="{
              'sm:block':  ! route().current('buy.subscription.checkout')
            }">
              <div class="h-full py-6 pl-4 pr-6 sm:pl-6 lg:pl-8 xl:pl-0">
                <!-- Start left column area -->
                <div class="bb-card bg-bb-lilla-200 py-5">
                  <div class="flex flex-col justify-start items-center">
                    <div class="relative inline-block">
                      <img
                        class="h-24 w-24 rounded-full object-cover"
                        :src="user.profile_photo_url"
                        alt=""
                      />
                      <PencilIcon
                        @click="selectNewPhoto"
                        class="absolute bottom-0 right-0 block h-6 w-6 p-1 rounded-full bg-white text-bb-blue-500 cursor-pointer"
                      />
                      <input
                        ref="photoInput"
                        type="file"
                        class="hidden"
                        accept=".jpg, .jpeg, .png"
                        @change="savePhotoProfile"
                      />
                    </div>
                    <p class="mt-3 text-bb-blue-500 text-md font-bold">
                      {{ user.full_name }}
                    </p>
                    <p class="text-xs">{{ user.email }}</p>
                  </div>
                  <div class="bg-white h-[1px] mx-2 mb-4 mt-4"></div>
                  <div>
                    <nav class="mt-5 flex-1" aria-label="Sidebar">
                      <div class="space-y-1 px-2">
                        <Link
                          v-for="item in navigation"
                          :key="item.name"
                          :href="item.href"
                          :class="[
                            item.current
                              ? 'text-white'
                              : 'text-bb-blue-500 hover:text-white',
                            'group flex items-center px-2 py-2 text-sm font-bold rounded-md',
                          ]"
                        >
                          <component
                            :is="item.icon"
                            :class="[
                              item.current
                                ? 'text-white'
                                : 'text-bb-blue-500 group-hover:text-white',
                              'mr-3 h-6 w-6 stroke-1',
                            ]"
                            aria-hidden="true"
                          />
                          {{ item.name }}
                          <span
                            v-if="
                              item.name === 'Notifiche' &&
                              notificationsCount > 0
                            "
                            :class="[
                              item.current
                                ? 'bg-white text-bb-blue-500'
                                : 'bg-bb-blue-500 text-white group-hover:bg-white group-hover:text-bb-blue-500',
                              'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ml-2',
                            ]"
                            >{{ notificationsCount }}</span
                          >
                        </Link>
                        <a
                          @click="logout"
                          class="text-bb-blue-500 hover:text-white group flex items-center px-2 py-2 text-sm font-bold rounded-md"
                        >
                          <LogoutIcon
                            class="text-bb-blue-500 group-hover:text-white mr-3 h-6 w-6 stroke-1"
                          />
                          Logout
                        </a>
                      </div>
                    </nav>
                  </div>
                </div>
                <!-- End left column area -->
              </div>
            </div>

            <div class="bg-white lg:min-w-0 flex-1 md:pr-4 xl:pr-0">
              <div class="h-full py-6 px-4 sm:px-6 lg:px-8">
                <!-- Start main area-->
                <slot />
                <!-- End main area -->
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Photo profile remember modal -->
    <BbDialog ref="photoProfileModal" type="plain" size="md" :no-cancel="true">
      <template #title>
        <p class="text-bb-blue-500 text-md font-bold">
          Carica la tua foto profilo!
        </p>
      </template>

      <span
        >In questo modo ci aiuterai a riconoscerti quando ti presenterai nello
        store!</span
      >

      <template #buttons>
        <BbButton secondary light @click="hidePhotoProfileModal()">
          Non ora
        </BbButton>
        <BbButton primary @click="gotoProfile()"> Carica foto </BbButton>
      </template>
    </BbDialog>

    <!-- footer -->
    <Footer />
  </div>
</template>
