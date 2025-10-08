<template>
  <bb-select-autocomplete
    id="typeahead_id"
    :default-item="selectedLocation"
    :items="locations"
    :minInputLength="3"
    :itemProjection="getLocationLabel"
    @selectItem="selectLocation"
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
  name: 'bb-select-location',
  components: {
    bbSelectAutocomplete,
  },
  props: {
    initialLocation: {
      type: Object,
      default: {}
    },
    adresse: { //search adresse or city only
     type: Boolean,
     default: false
    }
  },
  created() {
    this.selectedLocation = this.initialLocation;
    this.locations = [this.initialLocation];
  },
  data() {
    return {
      selectedLocation: null,
      locations: []
    }
  },
  methods: {
    onTyped(search) {
      if(search.input != null &&  search.input.length >= 3) {
        if (this.timeout) {
          clearTimeout(this.timeout);
        }
        this.timeout = setTimeout(() => {
          this.searchLocations(search.input);
        }, 600);

      }

    },
    getLocationLabel(location) {
      if(location.label == null) {
        return "";
      }
      return location.label+ " - " +location.region;
    },
    searchLocations(search) {
      let self = this;
      axios.get(urlAdminService.getAURL("/v-search-locations"), { params: {search : search, adresse: this.adresse }}).then(function(response){
        self.locations = response.data.locations;
      }).catch(function (error) {
        messageService.showMessageFromResponse(error.response);
      });
    },
    selectLocation(location) {
      this.selectedLocation = location;
      this.$emit('updateLocation', location);
    }
  },
};
</script>