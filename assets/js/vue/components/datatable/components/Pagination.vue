<template>
  <ul class="v-bb-datatable-pagination" v-show="hasMutliplePage">
    <li
      v-if="moveFirstPage"
      :class="[css.paginationItem, css.moveFirstPage]"
    >
      <button
        :disabled="isActionDisabled('firstPage')"
        :class="css.pageBtn"
        @click="changePage(1)">
        <i class="mdi mdi-chevron-double-left"></i>
      </button>
    </li>
    <li
      v-if="movePreviousPage"
      :class="[css.paginationItem, css.movePreviousPage]"
    >
      <button
        :disabled="isActionDisabled('previousPage')"
        :class="css.pageBtn"
        @click="changePage(currentPage - 1)">
        <i class="mdi mdi-chevron-left"></i>
      </button>
    </li>
    <li
      v-for="pageNr in qntPages"
      :key="pageNr"
      :class="pageClass(pageNr)"
    >
      <template v-if="pageNr !== currentPage">
        <button
          :class="[css.pageBtn, css.pageNumber]"
          @click="changePage(pageNr)"
        >
          {{ pageNr }}
        </button>
      </template>
      <template v-else>
        {{ pageNr }}
      </template>
    </li>
    <li
      v-if="moveNextPage"
      :class="[css.paginationItem, css.moveNextPage]"
    >
      <button
        :disabled="isActionDisabled('nextPage')"
        :class="css.pageBtn"
        @click="changePage(currentPage + 1)"
      >
        <i class="mdi mdi-chevron-right"></i>
      </button>
    </li>
    <li
      v-if="moveLastPage"
      :class="[css.paginationItem, css.moveLastPage]"
    >
      <button
        :disabled="isActionDisabled('lastPage')"
        :class="css.pageBtn"
        @click="changePage(lastPage)"
      >
        <i class="mdi mdi-chevron-double-right"></i>
      </button>
    </li>
  </ul>
</template>
<script>
import { mapState } from "vuex"
export default {
  name: 'DataTablePagination',
  props: {
    moveLastPage: {
      type: Boolean,
      default: true
    },
    moveFirstPage: {
      type: Boolean,
      default: true
    },
    moveNextPage: {
      type: Boolean,
      default: true
    },
    movePreviousPage: {
      type: Boolean,
      default: true
    },
    css: {
      type: Object,
      default: () => ({
        paginationItem: 'pagination-item',
        moveFirstPage: 'move-first-page',
        movePreviousPage: 'move-previous-page',
        moveNextPage: 'move-next-page',
        moveLastPage: 'move-last-page',
        pageBtn: 'page-btn',
        pageNumber: 'page-number',
      })
    }
  },
  data: function () {
    return {
      itemsPerPage: this.$store.state.itemsPerPage,
      // currentPage: this.$store.state.currentPage,
      totalItems: this.$store.state.totalItems,
      nbPages: this.$store.state.nbPages
    }
  },
  computed: {
    currentPage: {
      get() {
        return this.$store.state.currentPage;
      },
      set(currentPage) {
        this.$store.state.currentPage = currentPage;
      }
    },
    hasMutliplePage: function () {
      this.nbPages = this.$store.state.nbPages;
      return this.$store.state.nbPages > 1 ? true : false;
    },
    qntPages: function () {
      const nrPages = this.$store.state.nbPages;
      if (nrPages > 4) {
        if (this.currentPage <= 3) {
          return Array.apply(null, { length: 5 }).map((_, index) => index + 1)
        } else if (this.$store.state.currentPage + 2 >= nrPages) {
          return Array.apply(null, { length: nrPages }).map((_, index) => index + 1).slice(nrPages - 5, nrPages)
        } else {
          return Array.apply(null, { length: nrPages }).map((_, index) => index + 1).slice(this.currentPage - 3, this.currentPage + 2)
        }
      } else {
        return Array.apply(null, { length: nrPages }).map((_, index) => index + 1)
      }
    },
    lastPage: function () {
      return this.$store.state.nbPages;
    }
  },
  methods: {
    pageClass: function (currentPage) {
      return this.$store.state.currentPage === currentPage ? `${this.css.paginationItem} selected` : this.css.paginationItem
    },
    changePage: function (pageToMove) {
      if (pageToMove <= this.lastPage && pageToMove >= 1 && pageToMove !== this.currentPage) {
        this.currentPage = pageToMove;
        this.$store.commit('SET_PAGE', pageToMove);
        //on doit refaaire le tree en sens inverse ou sinon vuex pour store datas ..
        this.$emit('update-data', pageToMove);
      }
    },
    isActionDisabled: function (action) {
      switch (action) {
        case 'firstPage':
          return this.currentPage === 1
        case 'previousPage':
          return this.currentPage === 1
        case 'lastPage':
          return this.$store.state.canGoToNextPage
        case 'nextPage':
          return this.$store.state.canGoToNextPage
      }
    },
    checkCurrentPageExist: function () {
      if (this.qntPages.indexOf(this.$store.state.currentPage) === -1) {
        const nextPage = this.qntPages.length ? this.qntPages.length : 0
        this.$emit('update-current-page', nextPage)
      }
    }
  }
}
</script>
