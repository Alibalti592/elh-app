<template>
  <select
    :class="['v-datatable-light-items-per-page', css.select]"
    @change="onUpdate"
  >
    <option
      v-for="item in listItemsPerPage"
      :key="item"
      :value="item"
      :selected="itemsPerPage === item"
    >
      {{ item }}
    </option>
  </select>
</template>
<script>
export default {
  name: 'DataTableItemsDropdown',
  props: {
    listItemsPerPage: {
      type: Array,
      default: () => [25, 50, 100]
    },
    css: {
      type: Object,
      default: () => ({
        select: 'item-per-page-dropdown'
      })
    }
  },
  data: function () {
    return {
      itemsPerPage: this.$store.state.itemsPerPage
    }
  },
  methods: {
    onUpdate: function (event) {
      let itemsPerPage = event.target.value;
      this.$store.dispatch('setItemsPerPage', itemsPerPage);
      this.$emit('update-data', itemsPerPage)
    }
  }
}
</script>
