<template>
  <bb-select-autocomplete
    id="typeahead_id"
    :default-item="selectedUser"
    :items="users"
    :minInputLength="3"
    :itemProjection="getUserLabel"
    @selectItem="selectUser"
    @onInput="onTyped"
  >
  </bb-select-autocomplete>
</template>

<script>
import bbSelectAutocomplete from '@/components/select-autocomplete.vue'
import axios from "axios";
import {messageService} from "@/services/message";
import {urlAdminService} from "@/services/urlAdminService";

export default {
  name: 'bb-select-user',
  components: {
    bbSelectAutocomplete,
  },
  props: {
    initialUser: {
      type: Object,
      default: {}
    },
    adresse: { //search adresse or city only
     type: Boolean,
     default: false
    }
  },
  created() {
    this.selectedUser = this.initialUser;
    this.users = [this.initialUser];
  },
  data() {
    return {
      selectedUser: null,
      users: []
    }
  },
  methods: {
    onTyped(search) {
      if(search.input != null &&  search.input.length >= 3) {
        if (this.timeout) {
          clearTimeout(this.timeout);
        }
        this.timeout = setTimeout(() => {
          this.searchUsers(search.input);
        }, 600);

      }

    },
    getUserLabel(user) {
      if(user == null) {
        return "";
      }
      return user.fullname+ " - (" +user.email+")";
    },
    searchUsers(search) {
      let self = this;
      axios.get(urlAdminService.getAURL("/v-search-users"), { params: {search : search }}).then(function(response){
        self.users = response.data.users;
      }).catch(function (error) {
        messageService.showMessageFromResponse(error.response);
      });
    },
    selectUser(user) {
      this.selectedUser = user;
      this.$emit('updateUser', user);
    }
  },
};
</script>