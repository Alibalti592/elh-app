<template>
  <div :class="['text-right search-bar', wrapClass]">
    <input type="text" name="searchTerm" v-model="searchTerm"
           :placeholder="placeholder" @input="searchTermAction" ref="searchTerm" v-tippy='{ trigger : "manual"}' content="Saisir au moins 3 caractères">
      <i class="mdi mdi-close reset-search pointer" v-show="searchInitialized" @click="resetTerm"></i>
  </div>
</template>

<script>
  export default {
    name: 'SearchBar',
    props: {
      nbMinCharacters: {
        required: false,
        default: 3,
        type: Number
      },
      placeholder: {
        required: false,
        default: "Rechercher",
        type: String
      },
      wrapClass: {
        required: false,
        default: "mb-2",
        type: String
      }
    },
    data() {
      return {
        searchTerm: '',
        searchTimeout: null,
        searchInitialized: false
      };
    },
    methods: {
      searchTermAction: function () {
        if (this.searchTimeout) {
          clearTimeout(this.searchTimeout);
        }
        this.searchTimeout = setTimeout(() => {
          this.searchInitialized = true;
          if(this.searchTerm.length >= this.nbMinCharacters) {
            this.$refs['searchTerm']._tippy.hide();
            this.$store.dispatch('updateSearchTerm', this.searchTerm);
            this.$emit('update-data');
            this.searchTimeout = null;
          } else {
            this.$refs['searchTerm']._tippy.show();
          }
        }, 400);
      },
      resetTerm: function () {
        this.searchInitialized = false;
        this.searchTerm = "";
        this.$store.dispatch('updateSearchTerm', "");
        this.searchTimeout = null;
        this.$emit('update-data');
      }
    },
  };
</script>