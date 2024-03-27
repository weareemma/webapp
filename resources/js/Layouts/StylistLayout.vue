<script setup>
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import {Head, Link, usePage} from "@inertiajs/inertia-vue3";
import { computed } from "@vue/runtime-core";
import SidebarLinks from "@/Components/General/SidebarLinks.vue";
import Logo from "@/Components/General/Logo.vue";
import SessionStoreSelection from "@/Components/General/SessionStoreSelection.vue";

import {
  Disclosure,
  DisclosureButton,
  DisclosurePanel,
  Dialog,
  DialogPanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { BellIcon, MenuAlt2Icon, XIcon, MenuIcon } from "@heroicons/vue/outline";
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
} from "@/Components/Icons";

const user = ref(usePage().props.value.user);
const impersonate = computed(() => usePage().props.value.impersonate);
const navigation = [
  // { name: "Dashboard", href: route("stylist.dashboard"), current: route().current("stylist.dashboard") },
];
const userNavigation = [
  { name: "Account", href: route("stylist.profile") },
  { name: "Calendario", href: route("stylist.dashboard") },
  { name: "Appuntamenti futuri", href: route("stylist.booking.future") },
  { name: "Appuntamenti passati", href: route("stylist.booking.past") },
];

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
  <div class="min-h-screen bg-bb-black-100 flex flex-col justify-between">
    <Head :title="title" />

    <Disclosure as="nav" class="bg-bb-black-100" v-slot="{ open }">
      <div class="mx-auto px-4 sm:px-6 lg:px-8 p-4">
        <div class="flex justify-between h-16 items-center">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center mr-10">
              <Logo :light="false"></Logo>
            </div>
            <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
              <a
                  v-for="item in navigation"
                  :key="item.name"
                  :href="item.href"
                  :class="[
                  item.current
                    ? 'text-bb-gray-900'
                    : 'border-transparent text-bb-gray-500 hover:text-bb-gray-700',
                  'inline-flex items-center px-1 pt-1 text-sm font-semibold',
                ]"
                  :aria-current="item.current ? 'page' : undefined"
              >
                {{ item.name }}
              </a>
            </div>
          </div>
          <div class=" sm:ml-6 sm:flex sm:items-center">
            <!--            <button type="button" class="bg-white p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">-->
            <!--              <span class="sr-only">View notifications</span>-->
            <!--              <BellIcon class="h-6 w-6" aria-hidden="true" />-->
            <!--            </button>-->

            <!-- Profile dropdown -->
            <Menu as="div" class="ml-3 relative">
              <div>
                <MenuButton
                    class="max-w-xs bg-transparent flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  <span class="sr-only">Open user menu</span>
                  <div class="flex justify-start items-center gap-2">
                    <img
                        class="h-8 w-8 rounded-full object-cover"
                        :src="user.profile_photo_url"
                        alt=""
                    />
                    <p class="hidden sm:block">{{user.full_name}}</p>
                  </div>
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
                    class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white z-50 ring-1 ring-black ring-opacity-5 focus:outline-none"
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
                      Logout
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
          <div class="-mr-2 flex items-center hidden">
            <!-- Mobile menu button -->
            <DisclosureButton
                class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <span class="sr-only">Open main menu</span>
              <MenuIcon v-if="!open" class="block h-6 w-6" aria-hidden="true" />
              <XIcon v-else class="block h-6 w-6" aria-hidden="true" />
            </DisclosureButton>
          </div>
        </div>
      </div>

      <DisclosurePanel class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
          <DisclosureButton
              v-for="item in navigation"
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
        <div class="pt-4 pb-3 border-t border-gray-200">
          <div class="flex items-center px-4">
            <div class="flex-shrink-0">
              <img class="h-10 w-10 rounded-full" :src="user.profile_photo_url" alt="" />
            </div>
            <div class="ml-3">
              <div class="text-base font-medium text-gray-800">
                {{ user.name }}
              </div>
              <div class="text-sm font-medium text-gray-500">
                {{ user.email }}
              </div>
            </div>
            <!--            <button type="button" class="ml-auto bg-white flex-shrink-0 p-1 rounded-full text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">-->
            <!--              <span class="sr-only">View notifications</span>-->
            <!--              <BellIcon class="h-6 w-6" aria-hidden="true" />-->
            <!--            </button>-->
          </div>
          <div class="mt-3 space-y-1">
            <DisclosureButton
                v-for="item in userNavigation"
                :key="item.name"
                as="a"
                :href="item.href"
                class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100"
            >
              {{ item.name }}
            </DisclosureButton>
            <DisclosureButton
                @click="logout"
                as="a"
                class="block px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100"
            >
              Log out
            </DisclosureButton>
            <DisclosureButton v-if="impersonate">
              <a
                  :href="route('impersonate.leave')"
                  :class="[
                  active ? 'bg-gray-100' : '',
                  'block px-4 py-2 text-sm text-bb-danger cursor-pointer',
                ]"
              >
                Torna amministratore
              </a>
            </DisclosureButton>
          </div>
        </div>
      </DisclosurePanel>
    </Disclosure>

    <div class="sm:py-10 grow">
      <main>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>

    <!-- footer -->
    <footer class="relative h-20 rounded-t-3xl pt-4 px-6">
      <div class="relative z-10 flex justify-between items-center">
        <svg
            width="92"
            height="42"
            viewBox="0 0 92 42"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
          <g clip-path="url(#clip0_1108_16953)">
            <path
                d="M1.90949 35.4315C1.90949 39.185 4.01427 41.7047 7.14974 41.7047C10.2852 41.7047 12.39 39.185 12.39 35.4315V32.1212H14.2507V33.9979C14.2507 38.7883 11.4108 41.9974 7.15245 41.9974C2.89407 41.9974 0 38.7883 0 34.0007V23.8262C0 19.0358 2.83982 15.876 7.10092 15.876C11.362 15.876 14.2507 19.0851 14.2507 23.8262V29.0133H1.90949V35.4342V35.4315ZM1.90949 28.7151H12.3873V22.3927C12.3873 18.6391 10.2337 16.1195 7.14703 16.1195C4.06038 16.1195 1.90678 18.6391 1.90678 22.3927V28.7151H1.90949Z"
                fill="#000111"
            />
            <path
                d="M48.5211 16.3191H50.4305V20.8633H50.5282C51.7515 17.7034 53.9078 15.8267 56.4031 15.8267C58.8985 15.8267 61.006 17.8019 61.7898 20.8633H61.8387C63.1108 17.7034 65.3647 15.8267 67.9089 15.8267C71.2396 15.8267 73.735 19.0358 73.735 23.5799V41.5076H71.8255V22.6388C71.8255 18.9838 69.9648 16.4149 67.3691 16.4149C65.0691 16.4149 62.9127 18.4393 61.8848 21.5007C62.0312 22.1436 62.0801 22.833 62.0801 23.5744V41.5021H60.1706V22.6388C60.1706 18.9838 58.3587 16.4149 55.8118 16.4149C53.4602 16.4149 51.3066 18.6363 50.4251 21.8974V41.5049H48.5156V16.3191H48.5211Z"
                fill="#000111"
            />
            <path
                d="M48.5211 16.3191H50.4305V20.8633H50.5282C51.7515 17.7034 53.9078 15.8267 56.4031 15.8267C58.8985 15.8267 61.006 17.8019 61.7898 20.8633H61.8387C63.1108 17.7034 65.3647 15.8267 67.9089 15.8267C71.2396 15.8267 73.735 19.0358 73.735 23.5799V41.5076H71.8255V22.6388C71.8255 18.9838 69.9648 16.4149 67.3691 16.4149C65.0691 16.4149 62.9127 18.4393 61.8848 21.5007C62.0312 22.1436 62.0801 22.833 62.0801 23.5744V41.5021H60.1706V22.6388C60.1706 18.9838 58.3587 16.4149 55.8118 16.4149C53.4602 16.4149 51.3066 18.6363 50.4251 21.8974V41.5049H48.5156V16.3191H48.5211Z"
                fill="#000111"
            />
            <path
                d="M85.0484 16.0728C82.1109 16.0728 80.0549 18.6417 80.0549 22.346V25.9518H78.1455V23.7796C78.1455 19.0384 80.9365 15.8293 85.0484 15.8293C89.1603 15.8293 92.0028 19.0384 92.0028 23.7796V41.5103H90.0933V37.609H89.9957C88.626 40.4734 86.4696 42.0055 83.6786 42.0055C80.055 42.0055 77.7061 39.3873 77.7061 35.3383V34.4C77.7061 30.0528 80.5459 27.979 86.4696 27.979H90.0933V22.3488C90.0933 18.6445 88.0374 16.0756 85.0511 16.0756L85.0484 16.0728ZM90.0906 28.2718H87.0067C82.0621 28.2718 79.6128 30.0993 79.6128 33.8528V36.0743C79.6128 39.3326 81.4247 41.3598 84.3133 41.3598C87.0067 41.3598 89.1115 39.6308 90.0906 36.7172V28.2718Z"
                fill="#000111"
            />
            <path
                d="M18.8505 41.639V39.713H23.3557V39.6145C20.223 38.3807 18.3623 36.2057 18.3623 33.6888C18.3623 31.1719 20.3206 29.0461 23.3557 28.2555V28.2062C20.223 26.9232 18.3623 24.6497 18.3623 22.0835C18.3623 18.724 21.5439 16.207 26.0491 16.207H43.8231V18.133H25.116C21.4924 18.133 18.9455 20.0098 18.9455 22.628C18.9455 24.9479 20.9526 27.1229 23.9877 28.1597C24.6251 28.012 25.3086 27.9628 26.0437 27.9628H43.8177V29.8888H25.116C21.4924 29.8888 18.9455 31.7163 18.9455 34.2852C18.9455 36.6571 21.1479 38.8294 24.381 39.7185H43.8204V41.6445H18.8505V41.639Z"
                fill="#000111"
            />
            <path
                d="M18.6123 4.99566H18.3194L16.89 0.719602L15.4606 4.99566H15.1676L13.3965 0.0356517H13.8006L15.3358 4.4184L16.7652 0.0219727H17.0446L18.474 4.4184L20.0092 0.0356517H20.3862L18.615 4.99566H18.6123Z"
                fill="black"
            />
            <path
                d="M28.7158 4.96008V0.0356445H32.1876V0.366676H29.0766V2.31457H31.873V2.6456H29.0766V4.62905H32.2229V4.96008H28.7158Z"
                fill="black"
            />
            <path
                d="M51.7439 4.96L51.1228 3.58116H48.2423L47.6212 4.96H47.2441L49.5171 0H49.867L52.1399 4.96H51.7439ZM49.6853 0.402163L48.3942 3.25013H50.9682L49.6853 0.402163Z"
                fill="black"
            />
            <path
                d="M64.7984 4.96008L63.2442 2.91918H61.6467V4.96008H61.2832V0.0356445H63.3337C63.5995 0.0356445 63.8382 0.0712099 64.0525 0.136869C64.2668 0.205264 64.4485 0.301017 64.6004 0.424128C64.7523 0.547239 64.8689 0.694972 64.953 0.867328C65.0371 1.03968 65.0778 1.22845 65.0778 1.43364V1.44732C65.0778 1.6525 65.0425 1.83854 64.9693 1.99995C64.896 2.16136 64.7984 2.30089 64.6682 2.41853C64.5407 2.53617 64.3888 2.63192 64.2152 2.70305C64.0417 2.77692 63.8518 2.82616 63.6457 2.85352L65.2568 4.95735H64.7957L64.7984 4.96008ZM64.7143 1.44732C64.7143 1.11355 64.5923 0.850913 64.3482 0.656671C64.104 0.465165 63.7596 0.366676 63.312 0.366676H61.6439V2.58815H63.2903C63.4938 2.58815 63.6836 2.56079 63.8599 2.51154C64.0335 2.45956 64.1854 2.3857 64.3102 2.28994C64.4349 2.19419 64.5353 2.07655 64.6058 1.93703C64.6791 1.7975 64.7143 1.63882 64.7143 1.461V1.44732Z"
                fill="black"
            />
            <path
                d="M75.0967 4.96008V0.0356445H78.5685V0.366676H75.4574V2.31457H78.2539V2.6456H75.4574V4.62905H78.6037V4.96008H75.0967Z"
                fill="black"
            />
          </g>
          <defs>
            <clipPath id="clip0_1108_16953">
              <rect width="92" height="42" fill="white" />
            </clipPath>
          </defs>
        </svg>
        <div class="grow flex justify-start mx-20 mt-4">
          <p class="mr-4 text-bb-gray-800 text-sm">@WeAreEmma</p>
          <p class="text-bb-gray-800 text-sm">Assistenza</p>
        </div>
      </div>
    </footer>
  </div>
</template>
