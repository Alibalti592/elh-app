import { createApp } from 'vue'
import { createStore } from 'vuex'
import datatableStore from "@/stores/datatable-store";

export const mainStore = createStore({
    modules: {
        datatableStore
    },
});
