import { reactive, ref, watch } from "vue";
import { Inertia } from "@inertiajs/inertia";
import _ from "lodash";

export function useSearch(routeName, filtersInit = {}, queryName = 'q') {
    const searchQuery = ref("");
    const filters = reactive(filtersInit);
    const isSearching = ref(false);

    const cancelTokens = ref([]);

    watch(searchQuery, startSearch);
    watch(filters, startSearch);

    function startSearch() {
        isSearching.value = true;
        clearCancelTokens();
        debouncedSearch();
    }

    function clearCancelTokens() {
        if (cancelTokens.value.length)
            cancelTokens.value.forEach((ct) => ct.cancel());
    }

    function search() {
        Inertia.visit(route(routeName, { [queryName]: searchQuery.value, ...filters }), {
            preserveState: true,
            onCancelToken: (cancelToken) =>
                cancelTokens.value.push(cancelToken),
            onSuccess: () => (isSearching.value = false),
        });
    }
    const debouncedSearch = _.debounce(search, 250, { maxWait: 5000 });

    return {
        searchQuery,
        filters,
        isSearching,
    };
}
