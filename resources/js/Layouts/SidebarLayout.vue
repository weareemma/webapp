<script setup>
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import {Head, Link, usePage} from "@inertiajs/inertia-vue3";
import SidebarLinks from "@/Components/General/SidebarLinks.vue";
import Logo from "@/Components/General/Logo.vue";
import SessionStoreSelection from "@/Components/General/SessionStoreSelection.vue";

import {
  Dialog,
  DialogPanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { BellIcon, MenuAlt2Icon, XIcon } from "@heroicons/vue/outline";
import {
  CalendarGearIcon,
  CalendarIcon,
  DiscountIcon,
  HairDryerIcon,
  PackagesIcon,
  SettingsIcon,
  StoreIcon,
  SubscriptionIcon,
  UserCheckIcon,
  UserIcon,
  HomeIcon
} from "@/Components/Icons";
import {computed} from "@vue/runtime-core";

const user = ref(usePage().props.value.user);
const checkoutError = ref(usePage().props.value.checkoutError);
const isAdmin = computed(() => usePage().props.value.is_admin);
const impersonate = computed(() => usePage().props.value.impersonate);

const navigation = [
  {
    name: "",
    can: isAdmin.value,
    children: [
      {
        name: "Dashboard",
        href: route("admin.dashboard"),
        can: isAdmin.value,
        icon: HomeIcon,
        current: route().current("admin.dashboard"),
      },
    ]
  },
  {
    name: "Per store",
    can: true,
    children: [
      {
        name: "Appuntamenti",
        href: route("schedule.appointment.index"),
        can: true,
        icon: CalendarIcon,
        current:
          route().current("schedule.appointment.*") &&
          !route().current("schedule.appointment.past") && 
          !route().current("schedule.appointment.all"),
      },
      {
        name: "Appuntamenti passati",
        href: route("schedule.appointment.past"),
        can: true,
        icon: CalendarIcon,
        current: route().current("schedule.appointment.past"),
      },
      {
        name: "Appuntamenti export",
        href: route("schedule.appointment.all"),
        can: true,
        icon: CalendarIcon,
        current: route().current("schedule.appointment.all"),
      },
      {
        name: "Prog. default",
        href: route("schedule.default.index"),
        can: true,
        icon: CalendarGearIcon,
        current: route().current("schedule.default.*"),
      },
      {
        name: "Prog. specifica",
        href: route("schedule.specific.index"),
        can: true,
        icon: CalendarGearIcon,
        current: route().current("schedule.specific.*"),
      },  
      {
        name: "Prog. effettiva",
        href: route("schedule.actual.index"),
        can: true,
        icon: CalendarGearIcon,
        current: route().current("schedule.actual.*"),
      },
    ],
  },
  {
    name: "Generali",
    can: true,
    children: [
      {
        name: "Errori checkout",
        href: route("checkoutError.index"),
        can: true,
        icon: SettingsIcon,
        current: route().current("checkoutError.*"),
        warning: checkoutError.value
      },
      {
        name: "Clienti",
        href: route("customer.index"),
        can: true,
        icon: UserCheckIcon,
        current: route().current("customer.*"),
      },
      {
        name: "Abbonamenti Acquistati",
        href: route("subscription.index"),
        can: true,
        icon: SubscriptionIcon,
        current: route().current("subscription.*")
      },
      {
        name: "Transazioni",
        href: route("payment.index"),
        can: true,
        icon: UserCheckIcon,
        current: route().current("payment.*"),
        spaced: true,
      },
      {
        name: "Servizi",
        href: route("hairService.index"),
        can: true,
        icon: HairDryerIcon,
        current: route().current("hairService.*"),
      },
      {
        name: "Store",
        href: route("store.index"),
        can: true,
        icon: StoreIcon,
        current: route().current("store.*"),
      },
      {
        name: "Utenti",
        href: route("user.index"),
        can: true,
        icon: UserIcon,
        current: route().current("user.*"),
        spaced: true,
      },
      {
        name: "Abbonamenti",
        href: route("plan.index"),
        can: true,
        icon: SubscriptionIcon,
        current: route().current("plan.*"),
      },
      {
        name: "Codici sconto",
        href: route("discount.index"),
        can: true,
        icon: DiscountIcon,
        current: route().current("discount.*"),
      },
      {
        name: "Pacchetti",
        href: route("package.index"),
        can: true,
        icon: PackagesIcon,
        current: route().current("package.*"),
        spaced: true,
      },
      {
        name: "Impostazioni",
        href: route("setting.index"),
        can: true,
        icon: SettingsIcon,
        current: route().current("setting.*"),
      },
      {
        name: "Logs",
        href: route("logs.index"),
        can: true,
        icon: SettingsIcon,
        current: route().current("logs.*"),
      },
    ],
  },
];
const userNavigation = [{ name: "Profilo", href: route("profile.show") }];

const sidebarOpen = ref(false);

defineProps({
  title: String,
  showTitle: { type: Boolean, default: true },
});

const logout = () => {
  Inertia.post(route("logout"));
};
</script>

<template>
  <div class="min-h-screen bg-bb-green pb-10">
    <Head :title="title" />

    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog
        as="div"
        class="relative z-40 md:hidden"
        @close="sidebarOpen = false"
      >
        <TransitionChild
          as="template"
          enter="transition-opacity ease-linear duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="transition-opacity ease-linear duration-300"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-gray-600 bg-opacity-75" />
        </TransitionChild>

        <div class="fixed inset-0 flex z-40">
          <TransitionChild
            as="template"
            enter="transition ease-in-out duration-300 transform"
            enter-from="-translate-x-full"
            enter-to="translate-x-0"
            leave="transition ease-in-out duration-300 transform"
            leave-from="translate-x-0"
            leave-to="-translate-x-full"
          >
            <DialogPanel
              class="relative flex-1 flex flex-col max-w-xs w-full pt-5 pb-4 bg-white rounded-tr-3xl rounded-br-3xl"
            >
              <TransitionChild
                as="template"
                enter="ease-in-out duration-300"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in-out duration-300"
                leave-from="opacity-100"
                leave-to="opacity-0"
              >
                <div class="absolute top-0 right-0 -mr-12 pt-2">
                  <button
                    type="button"
                    class="ml-1 flex items-center justify-center h-10 w-10 rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white"
                    @click="sidebarOpen = false"
                  >
                    <span class="sr-only">Close sidebar</span>
                    <XIcon class="h-6 w-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              <div class="flex-shrink-0 flex items-center px-4">
                <Logo :light="false"></Logo>
              </div>
              <div class="mt-5 flex-1 h-0 overflow-y-auto">
                <nav class="px-2 space-y-1">
                  <SidebarLinks :links="navigation" />
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
          <div class="flex-shrink-0 w-14" aria-hidden="true">
            <!-- Dummy element to force sidebar to shrink to fit close icon -->
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Static sidebar for desktop -->
    <div class="hidden md:flex w-[300px] flex-col fixed top-0 h-screen">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div
        class="h-full flex flex-col pt-5 pb-6 bg-white rounded-tr-3xl rounded-br-3xl"
      >
        <div class="flex justify-center items-center flex-shrink-0 px-10 mt-3">
          <Logo :light="false"></Logo>
        </div>
        <div class="flex-1 mt-5 overflow-y-auto">
          <nav class="px-5 pb-4">
            <SidebarLinks :links="navigation" />
          </nav>
        </div>
      </div>
    </div>

    <!-- main content -->
    <div class="md:pl-[21rem] flex flex-col flex-1 md:pr-10">
      <div
        class="sticky top-0 z-40 flex-shrink-0 flex h-16 bg-bb-green border-b border-white mb-10"
      >
        <button
          type="button"
          class="px-4 border-r border-gray-200 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 md:hidden"
          @click="sidebarOpen = true"
        >
          <span class="sr-only">Open sidebar</span>
          <MenuAlt2Icon class="h-6 w-6" aria-hidden="true" />
        </button>
        <div class="flex-1 px-4 flex justify-between">
          <div class="flex-1 flex justify-start items-center">
            <session-store-selection></session-store-selection>
          </div>
          <div class="ml-4 flex items-center md:ml-6">
            <!-- Notifications -->
            <!-- <button
              type="button"
              class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span class="sr-only">View notifications</span>
              <BellIcon class="h-6 w-6" aria-hidden="true" />
            </button> -->

            <div class="flex-col justify-start gap-y-0 text-white sm:flex hidden">
              <p class="m-0 text-sm">{{user.email}}</p>
              <p class="m-0 text-xs">
                <small>{{user.role}}</small>
              </p>
            </div>

            <!-- Profile dropdown -->
            <Menu as="div" class="ml-3 relative">
              <div>
                <MenuButton
                  class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <span class="sr-only">Open user menu</span>
                  <img
                    class="h-8 w-8 rounded-full"
                    :src="user.profile_photo_url"
                    alt=""
                  />
                </MenuButton>
              </div>
              <transition
                enter-active-class="transition ease-out duration-100"
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
                    <Link
                      :href="item.href"
                      :class="[
                        active ? 'bg-gray-100' : '',
                        'block px-4 py-2 text-sm text-gray-700',
                      ]"
                      >{{ item.name }}</Link
                    >
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
        </div>
      </div>

      <main class="bg-bb-green md:p-0 p-5">
        <h3 v-if="showTitle" class="big-header-title text-bb-blue-500 mb-4">
          {{ title }}
        </h3>
        <slot />
      </main>
    </div>
  </div>
</template>
