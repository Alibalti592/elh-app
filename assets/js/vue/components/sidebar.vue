<template>
  <div class="back-col-menu">
    <div class="placeholder-load" v-if="!hasLoaded"></div>
    <div v-if="hasLoaded">
      <div class="text-center card-box" v-if="headCard != null">
        <div class="member-card">
          <v-thumb :full-name="headCard.name" :image="headCard.image" :letters="headCard.letters" extraClass="mt-big"></v-thumb>
        </div>
      </div>

      <div class="head-menu" @click="toggleFilter">
        <div class="mobile-btn" v-if="head != null">
          <i :class="head.icon"></i> {{ head.name }}
          <i :class="['i-tgl-mobile', 'icon', showFilterContent ? 'icon-chevron-up' : 'icon-chevron-down']"></i>
        </div>
      </div>
      <ul :class="['content-menu', showFilterContent ? 'show' : 'hide']" v-if="menu != null">
        <li v-for="item in menu.items" :class="[item.active ? 'active' : '', item.hasSub ? 'has-sub' : ''] ">
          <!-- item simple -->
          <a :href="item.url" :target="item.target" v-if="!item.hasSub" :class="[ 'main-item', 'in-title', isActiveItem(item) ? 'active' : ''] ">
            <i :class="item.icon"></i> {{ item.name }}
          </a>

          <!-- with submenu-->
          <div v-if="item.hasSub" class="submenu-label in-title d-flex align-items-center justify-content-between"
               @click="toggleOpenSub(item.id)">
            <div class="name">
              <i :class="item.icon"></i>
              {{ item.name }}
            </div>
            <i :class="['tgl-sub-menu', 'icon', openItemId == item.id || isActiveParentItem(item) ? 'icon-chevron-down' : 'icon-chevron-right']"></i>
          </div>
          <ul v-if="item.hasSub" :class="['submenu', openItemId == item.id || isActiveParentItem(item) ? 'sopen' : 'sclosed']">
            <li v-for="itemSub in item.subItems" :class="[ isActiveItem(itemSub) ? 'active' : ''] ">
              <a :href="itemSub.url" :target="itemSub.target"> <i :class="itemSub.icon"></i> {{ itemSub.name }}</a>
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import VThumb from "@/components/v-thumb";
import {urlAdminService} from "@/services/urlAdminService";
export default {
  name: 'back-col-menu',
  components: { VThumb },
  data() {
    return {
      hasLoaded: false,
      menu: [],
      showFilterContent: false,
      openItemId: null,
      headCard: null,
      head: null,
      r: null, //current encrypt route
    }
  },
  created() {
    this.loadMenu();
  },
  methods: {
    loadMenu() {
      let cr = document.getElementById("sidebar-route").getAttribute('data-cr');
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-menu-left'), { params: { cr }}).then(function(response){
        self.menu = response.data.menu;
        self.headCard = response.data.headCard;
        self.r = response.data.r;
        self.hasLoaded = true;
      }).catch(function (error) {
      });
    },
    toggleFilter() {
      this.showFilterContent = !this.showFilterContent;
    },
    toggleOpenSub(itemId) {
      this.openItemId = this.openItemId == itemId ?  null : itemId;
    },
    isActiveParentItem(item) {
      let isActive = false;
      item.subItems.forEach(subItem => {
        if(this.isActiveItem(subItem)) {
          isActive = true;
        }
      });
      return isActive;
    },
    isActiveItem(item) {
      if(item.r === this.r) {
        this.setHeadName(item);
        return true;
      }
      return false;
    },
    setHeadName(item) {
      this.head = item;
    }
  },
};
</script>