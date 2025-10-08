<template>
  <div class="bb-datable-base">
    <div class="table-responsive">
      <table :class="['v-bb-datatable', css.table]">
        <thead :class="css.thead" :style="theadStyle">
        <tr :class="css.theadTr">
          <th
              v-for="(item, columnIndex) in headers"
              :key="item.label"
              :class="[css.theadTh, `header-column-${columnIndex}`]"
              :style="getColumnWidth(item)"
          >
            <!-- header free text -->
            <div v-if="!isFieldSpecial(item.name) && !item.customHeader" :class="[css.thWrapper, `header-column-${columnIndex}`]" @click="orderBy(item.name)">
              <span v-html="item.label"></span>
              <div v-if="item.help" class="ml-1"><i class="mdi mdi-information-outline" :title="item.help" v-tippy></i></div>
              <div v-if="item.sortable" :class="[arrowsWrapper(item.name, css.arrowsWrapper), activeSortItem(item)]">
                <div v-if="showOrderArrow(item, 'desc')" :class="css.arrowUp" />
                <div v-if="showOrderArrow(item, 'asc')" :class="css.arrowDown" />
              </div>
            </div>
            <!-- end header free text -->
            <!-- header custom header -->
            <div v-if="!isFieldSpecial(item.name) && item.customHeader" :class="[css.thWrapper, `header-column-${columnIndex}`]" @click="orderBy(item.name)">
              <slot
                  v-if="item.customHeader"
                  :header-data="item"
                  :name="customHeaderName(item)"
              />
              <div v-if="item.sortable" :class="[arrowsWrapper(item.name, css.arrowsWrapper), activeSortItem(item)]">
                <div v-if="showOrderArrow(item, 'desc')" :class="css.arrowUp" />
                <div v-if="showOrderArrow(item, 'asc')" :class="css.arrowDown" />
              </div>
            </div>
            <!-- end header custom header -->
            <!-- especial field -->
            <div
                v-if="isFieldSpecial(item.name) && extractArgs(item.name) === 'checkboxes'"
                :class="css.thWrapperCheckboxes"
            >
              <input
                  type="checkbox"
                  :class="css.checkboxHeader"
                  :checked="checkedAll"
                  @click="checkAll"
              >
            </div>
            <!-- end especial field -->
          </th>
        </tr>
        </thead>
        <tbody :class="css.tbody" :style="tbodyStyle" id="tbody-height">
        <!-- spinner slot -->
        <!-- Spinner element -->

        <template v-if="isLoading">
          <tr :class="css.tbodyTrSpinner">
            <td :colspan="headers.length" :class="css.tbodyTdSpinner">
              <div class="loader-wrapper" :style="{ 'min-height': currentTableHeight}">
                <Loading slot="spinner" v-show="isLoading"></Loading>
              </div>
            </td>
          </tr>
        </template>
        <!-- end spinner slot -->
        <!-- table rows -->
        <template v-else-if="data.length">
          <tr v-for="(item, index) in data" :key="index" :class="['tr-item', css.tbodyTr, `row-${index}`]">
            <td
                v-for="(key, columnIndex) in headers"
                :key="`${index}-${key.name}`"
                :class="[css.tbodyTd, `column-${columnIndex}`, key.class]"
                :style="getColumnWidth(key)">
              <slot
                  v-if="isFieldSpecial(key.name) && extractArgs(key.name) === 'actions'"
                  :name="extractActionID(key.name)"
                  :row-data="item"
                  :row-index="index"
              />
              <input
                  v-if="isFieldSpecial(key.name) && extractArgs(key.name) === 'checkboxes'"
                  type="checkbox"
                  :class="css.checkbox"
                  :row-data="item"
                  :row-index="index"
                  :checked="checkedAll || isCheckedItem(item)"
                  @click="checkItem(item, $event)"
              >
              <div v-if="key.type == 'boolean'">
                <i v-if="!item[key.name]" class="mdi mdi-close-circle-outline text-danger"></i>
                <i v-else class="mdi mdi-check-circle-outline text-success"></i>
              </div>
              <slot
                  v-if="key.customElement"
                  :row-data="item"
                  :row-index="index"
                  :name="customElementName(key)"
              />
              <template v-else-if="key.format == 'html'"><div v-html="item[key.name]"></div></template>
              <template v-else-if="key.format">{{ key.format(item[key.name]) }}</template>
              <template v-else>{{ item[key.name] }}</template>
            </td>
          </tr>
        </template>
        <!-- end table rows -->
        <!-- table not found row -->
        <template v-else>
          <tr :class="css.notFoundTr">
            <td :colspan="headers.length" :class="css.notFoundTd">
              {{ notFoundMessage }}
            </td>
          </tr>
        </template>
        <!-- end table not found row -->
        </tbody>
<!--        <tfoot v-if="hasSlots" :class="css.tfoot">-->
<!--          <tr :class="css.tfootTr">-->
<!--            <th :colspan="headers.length" :class="css.tfootTd">-->
<!--              <div :class="css.footer">-->
<!--                <slot name="ItemsPerPage"/>-->
<!--                <slot name="pagination"/>-->
<!--              </div>-->
<!--            </th>-->
<!--          </tr>-->
<!--        </tfoot>-->
      </table>
    </div>

    <!--  PAGIN PART-->
    <div class="row">
      <div class="col-md-6">
        <!-- ItemsPerPage component as a slot, but could be drag out from Database element -->
        <div class="items-per-page" v-if="showSelectNbRows">
          <label>Nb lignes</label>
          <ItemsPerPageDropdown
              :list-items-per-page="listItemsPerPage"
              @update-data="updateData"
          />
        </div>
      </div>
      <div class="col-md-6">
        <Pagination @update-data="updateData"/>
      </div>
    </div>
  </div>
</template>
<script>

import  Loading from '@/components/loading';
import ItemsPerPageDropdown from "@/components/datatable/components/ItemsPerPageDropdown";
import Pagination from "@/components/datatable/components/Pagination";
import { mapState } from "vuex";

export default {
  name: 'DataTable',
  components: {
    Loading,
    ItemsPerPageDropdown,
    Pagination,
  },
  props: {
    headerFields: {
      type: Array,
      required: true
    },
    data: {
      type: Array,
      required: true
    },
    showSelectNbRows: {
      type: Boolean,
      default: true
    },
    isLoading: {
      type: Boolean,
      default: false
    },
    notFoundMsg: {
      type: String,
      default: null
    },
    trackBy: {
      type: String,
      default: 'id'
    },
    css: {
      type: Object,
      default: () => ({
        table: 'table table-hover table-center',
        theadTr: 'header-item',
        tfoot: 'tfoot',
        tfootTd: 'tfoot-td',
        tfootTr: 'tfoot-tr',
        footer: 'footer-table',
        thWrapper: 'th-wrapper',
        thWrapperCheckboxes: 'th-wrapper-checkboxes',
        arrowsWrapper: 'arrows-wrapper',
        arrowUp: 'arrow-up mdi mdi-arrow-up',
        arrowDown: 'arrow-down mdi mdi-arrow-down',
        checkboxHeader: 'checkbox-header',
        checkbox: 'checkbox',
        notFoundTr: 'not-found-tr text-center',
        notFoundTd: 'not-found-tr text-center',
      }),
    },
    tableHeight: {
      type: String,
      default: null
    },
    defaultColumnWidth: {
      type: String,
      default: '150px'
    },
    onlyShowOrderedArrow: {
      type: Boolean,
      default: false
    },
  },

  data: function () {
    return {
      notFoundMessage: this.notFoundMsg,
      loading: this.isLoading,
      checkedAll: false,
      itemsChecked: [],
      currentTableHeight: "200px",
      listItemsPerPage: [25, 50, 100],
      sortedField: null
    }
  },
  computed: {
    hasSlots: function () {
      return (
        this.$slots.pagination !== undefined ||
        this.$slots.ItemsPerPage !== undefined
      )
    },
    headers: function () {
      if (
        this.headerFields &&
        this.headerFields.constructor === Array &&
        this.headerFields.length
      ) {
        return Object.keys(this.headerFields).map(key => {
          const field = this.headerFields[key]
          if (typeof field === 'string') {
            return { label: field, name: field }
          }
          return field
        })
      }
      return []
    },
    tbodyStyle: function () {
      if (this.tableHeight) {
        return {
          height: this.tableHeight,
          display: 'block',
          overflowX: 'auto'
        }
      }
      return null
    },
    theadStyle: function () {
      return this.tableHeight ? { display: 'block' } : null
    },
    ...mapState(['currentPage', 'itemsPerPage', 'totalItems', 'sortField', 'sort'])
  },

  methods: {
    arrowsWrapper: function (field, className) {
      if (this.sortedField === field && this.sortedDir) {
        return `${className} centralized`
      }
      return className
    },

    updateData: function () {
      this.$emit('update-data');
    },

    orderBy: function (field) {
      if (this.isFieldSortable(field)) {
        if (this.sortedField === field) {
          this.sortedDir = this.sortedDir === 'asc' ? 'desc' : 'asc'
        } else {
          this.sortedDir = 'desc'
          this.sortedField = field
        }
        this.$store.dispatch('sort', {
          sortField: this.sortedField,
          sort: this.sortedDir,
        });

        this.updateData()
      }
    },

    checkAll: function () {
      this.checkedAll = !this.checkedAll
      if (this.checkedAll) {
        this.itemsChecked = this.data
      } else {
        this.itemsChecked = []
      }
      this.$store.dispatch('updateCheckItems', this.itemsChecked);
      this.$emit('on-check-all', this.itemsChecked)
    },

    checkItem: function (item) {
      const found = this.itemsChecked.find(
        itemChecked => itemChecked[this.trackBy] === item[this.trackBy]
      )
      if (found) {
        this.itemsChecked = this.itemsChecked.filter(
          itemChecked => itemChecked[this.trackBy] !== item[this.trackBy]
        )
        this.$emit('on-unchecked-item', item)
      } else {
        this.itemsChecked = [...this.itemsChecked, item];
        this.$emit('on-checked-item', item)
      }
      this.$store.dispatch('updateCheckItems', this.itemsChecked);
    },

    isCheckedItem: function (item) {
      return !!this.itemsChecked.find(
        itemChecked => itemChecked[this.trackBy] === item[this.trackBy]
      )
    },

    isFieldSortable: function (field) {
      const foundHeader = this.headerFields.find(item => item.name === field)
      return foundHeader && foundHeader.sortable
    },

    headerItemClass: function (item, className = '') {
      return item && item.sortable ? className : `${className} no-sortable`
    },

    isFieldSpecial: field => field.indexOf('__') > -1,

    extractArgs: string => string.split(':')[1],

    extractActionID: string => {
      const list = string.split(':')
      return list.length === 3 ? list[2] : 'actions'
    },

    getColumnWidth: function (item) {
      if(item.width != 'undefined') {
        return { width: item.width };
      }
      if (this.tableHeight) {
        if (item.name === '__slot:checkboxes') {
          return { width: '50px' }
        }
        return { width: item.width || this.defaultColumnWidth }
      }
    },

    customElementName: ({ customElement, name }) => typeof customElement === 'string' ? customElement : name,

    customHeaderName: ({ customHeader, name }) => typeof customHeader === 'string' ? customHeader : `${name}:header`,

    showOrderArrow: function (item, sortDir) {
      if (this.onlyShowOrderedArrow) {
        return this.sortedField === item.name && this.sortedDir !== sortDir
      }
      return (this.sortedField !== item.name) || (this.sortedField === item.name && this.sortedDir === sortDir)
    },
    activeSortItem: function (item) {
      if(this.sortedField === item.name) {
        return 'sort-active';
      }
    },
  }
}
</script>
