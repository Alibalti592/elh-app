import { createStore } from 'vuex'

const state = () => ({
    sortField: 'id',
    sort: 'desc',
    itemsPerPage: 25,
    currentPage: 1,
    totalItems: 0,
    nbPages: 1,
    canGoToNextPage: false,
    searchTerm: null,
    checkItems: []
});
const getters = {
    filterParams: (state) => {
        return { params: {
                itemsPerPage: state.itemsPerPage,
                currentPage: state.currentPage ,
                sortField: state.sortField,
                sort: state.sort,
                searchTerm: state.searchTerm
            }
        };
    },
    getSearchTerm(state) {
        return state.searchTerm;
    }
};
const mutations = {
    SET_ITEMS_PER_PAGE: (state, payload) => {
        state.itemsPerPage = payload;
    },
    SET_PAGE: (state, payload) => {
        state.currentPage = payload;
    },
    RESET_PAGINATION: (state) => {
        state.currentPage = 1;
    },
    SET_TOTAL_ITEMS: (state, payload) => {
        state.totalItems = payload;
    },
    SET_SORT: (state, payload) => {
        state.sort = payload.sort;
        state.sortField = payload.sortField;
    },
    SET_NBPAGES: (state) => {
        state.nbPages = Math.ceil(state.totalItems / state.itemsPerPage);
    },
    SET_CAN_GO_NEXT_PAGE:(state) => {
        state.canGoToNextPage = state.currentPage === state.nbPages || !state.totalItems || state.currentPage * state.itemsPerPage >= state.totalItems
    },
    SET_SEARCH_TERM: (state, value) => {
        state.searchTerm = value;
    },
    SET_CHECK_ITEMS: (state, value) => {
        state.checkItems = value;
    },
};

const actions = {
    sort(context, payload) {
        context.commit('SET_SORT', payload);
    },
    setPaginationAfterLoadData(context, totalItems) {
        context.commit('SET_TOTAL_ITEMS', totalItems);
        context.commit('SET_NBPAGES');
        context.commit('SET_CAN_GO_NEXT_PAGE');
    },
    setItemsPerPage(context, itemsPerPage) {
        context.commit('SET_ITEMS_PER_PAGE', itemsPerPage);
        context.commit('RESET_PAGINATION');
    },
    updateSearchTerm(context, value) {
        context.commit('SET_SEARCH_TERM', value);
        context.commit('RESET_PAGINATION');
    },
    updateCheckItems(context, value) {
        context.commit('SET_CHECK_ITEMS', value);
    },
};

export const datatableStore = createDataTableStore();

function createDataTableStore() {
    return createStore({
        state,
        getters,
        mutations,
        actions
    });
}

export default {
    state,
    getters,
    actions,
    mutations
}