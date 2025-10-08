<template>
  <div>
    <div class="text-right mb-1">
      <button @click="addNewMaraude" class="bb-btn">Ajouter une Maraude</button>
    </div>
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="maraudes || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
      >


        <template v-slot:description="props">
          <div v-html="props.rowData.description" />
        </template>

        <template v-slot:location="props">
          <div>
            {{ props.rowData.location.label }}
            <small> - {{ props.rowData.location.region }}</small>
          </div>
        </template>


        <template v-slot:online="props">
          <i v-if="props.rowData.online" class="icon-check-circle color-success"></i>
          <i v-if="!props.rowData.online" class="icon-x-circle color-error"></i>
        </template>

        <template v-slot:actions="props">
          <div class="text-right">
            <button class="bb-btn-icon" @click="editMOSQUE(props.rowData)">
              <i class="icon-edit"></i>
            </button>
          </div>
        </template>

      </DataTable>
    </div>

    <modal ref="modalMaraude" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Ajouter / éditer une Maraude</h2>
      </template>
      <template v-slot:body>

        <form method="post" @submit="saveMOSQUE" v-if="maraudeEdit != null">

          <div class="form-group mt-3">
            <label>Ville</label>
            <SelectLocation :initialLocation="maraudeEdit.location" @updateLocation="setLocation"></SelectLocation>
          </div>

          <div class="form-group mt-3">
            <label>Description</label>
            <ckeditor :editor="editor" v-model="maraudeEdit.description" :config="editorConfig" :aria-required="true"></ckeditor>
          </div>


          <div class="form-group mt-3">
            <label>Date & heure</label>
            <VueDatePicker v-model="maraudeEdit.dateVue" locale="fr"></VueDatePicker>
          </div>


          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox"  v-model="maraudeEdit.online" id="online">
            <label class="form-check-label" for="online">
              Afficher la maraude dans l'application
            </label>
          </div>

          <div class="text-center mt-4">
            <Loading v-if="maraudeSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!maraudeSaving">
              Enregistrer
            </button>
          </div>
        </form>
      </template>
      
    </modal>
  </div>
</template>


<script>
import  DataTable from "@/components/datatable/components/DataTable";
import  Pagination from "@/components/datatable/components/Pagination";
import  SearchBar from "@/components/search-bar";
import  ItemsPerPageDropdown from "@/components/datatable/components/ItemsPerPageDropdown";
import axios from "axios";
import Modal from "@/components/modal";
import { messageService } from "@/services/message";
import {urlAdminService} from "@/services/urlAdminService";
import Loading from "@/components/loading";
import CKEditor from '@ckeditor/ckeditor5-vue';
import  ClassicEditor  from '@ckeditor/ckeditor5-build-classic';
import {ArrayObjectService} from '@/services/ArrayObject';
import SelectLocation from "@/components/location.vue";
import VueDatePicker from '@vuepic/vue-datepicker';

export default {
  name: 'admin-maraude-list',
  components: {
    SelectLocation,
    Loading,
    DataTable,
    ItemsPerPageDropdown,
    Pagination,
    SearchBar,
    Modal,
    ckeditor: CKEditor.component,
    VueDatePicker
  },
  data: function () {
    return {
      headerFields: [
        {
          name: 'id',
          label: '#',
          width: '90px',
          sortable: true
        },
        {
          name: 'location',
          label: 'Lieu',
          customElement: 'location',
          sortable: false
        },
        {
          name: 'description',
          label: 'Description',
          customElement: 'description',
          sortable: false
        },
        {
          name: 'datetimeDisplay',
          label: 'Date & heure',
          sortable: false
        },
        {
          name: 'online',
          label: "Visible sur l'application",
          sortable: true,
          width: '150px',
          customElement: 'online'
        },
        '__slot:actions'
      ],
      maraudes: [],
      isLoading: true,
      editor: ClassicEditor,
      editorConfig: {
        link: {
          addTargetToExternalLinks: true
        },
        // plugins: [
        //   Link, Bold,  Italic, List
        // ],
        toolbar: [ 'bold', 'italic', '|', 'link', 'BulletedList' ]
      },
      //modal maraude
      maraudeIni: null,
      maraudeEdit: null,
      maraudeSaving: false,
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-maraudes'), this.$store.getters.filterParams).then(function(response){
        self.maraudes = response.data.maraudes;
        self.maraudeIni = response.data.maraudeIni;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewMaraude() {
      this.maraudeSaving = false;
      this.maraudeEdit = ArrayObjectService.cloneOject(this.maraudeIni);
      this.$refs.modalMaraude.openModal();
    },
    editMOSQUE(maraude) {
      this.maraudeSaving = false;
      this.maraudeEdit = ArrayObjectService.cloneOject(maraude);
      this.$refs.modalMaraude.openModal();
    },
    saveMOSQUE(e) {
      e.preventDefault();
      this.maraudeSaving = true;
      let formData = new FormData();
      formData.append('maraude', JSON.stringify(this.maraudeEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-maraude"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalMaraude.closeModal();
        self.maraudeSaving = false;
      }).catch((error) => {
        self.maraudeSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    setLocation(location) {
      this.maraudeEdit.location = location;
    }
  }

}
</script>