<template>
  <div>
    <div class="text-right mb-1">
      <button @click="addNewImam" class="bb-btn">Ajouter un Imam</button>
    </div>
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="imams || []"
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

    <modal ref="modalImam" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Ajouter / éditer une Imam</h2>
      </template>
      <template v-slot:body>

        <form method="post" @submit="saveMOSQUE" v-if="imamEdit != null">
          <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" class="form-control" v-model="imamEdit.name" id="name" required>
          </div>

          <div class="form-group mt-3">
            <label>Description / Contact</label>
            <ckeditor :editor="editor" v-model="imamEdit.description" :config="editorConfig" :aria-required="true"></ckeditor>
          </div>

          <div class="form-group mt-3">
            <label>Ville</label>
            <SelectLocation :initialLocation="imamEdit.location" @updateLocation="setLocation"></SelectLocation>
          </div>

          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox"  v-model="imamEdit.online" id="online">
            <label class="form-check-label" for="online">
              Afficher l'imam dans l'application
            </label>
          </div>

          <div class="text-center mt-4">
            <Loading v-if="imamSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!imamSaving">
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

export default {
  name: 'admin-imam-list',
  components: {
    SelectLocation,
    Loading,
    DataTable,
    ItemsPerPageDropdown,
    Pagination,
    SearchBar,
    Modal,
    ckeditor: CKEditor.component
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
          name: 'name',
          label: 'Imam',
          width: '350px',
          sortable: false
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
          name: 'online',
          label: "Visible sur l'application",
          sortable: true,
          width: '150px',
          customElement: 'online'
        },
        '__slot:actions'
      ],
      imams: [],
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
      //modal imam
      imamIni: null,
      imamEdit: null,
      imamSaving: false,
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-imams'), this.$store.getters.filterParams).then(function(response){
        self.imams = response.data.imams;
        self.imamIni = response.data.imamIni;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewImam() {
      this.imamSaving = false;
      this.imamEdit = ArrayObjectService.cloneOject(this.imamIni);
      this.$refs.modalImam.openModal();
    },
    editMOSQUE(imam) {
      this.imamSaving = false;
      this.imamEdit = ArrayObjectService.cloneOject(imam);
      this.$refs.modalImam.openModal();
    },
    saveMOSQUE(e) {
      e.preventDefault();
      this.imamSaving = true;
      let formData = new FormData();
      formData.append('imam', JSON.stringify(this.imamEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-imam"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalImam.closeModal();
        self.imamSaving = false;
      }).catch((error) => {
        self.imamSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    setLocation(location) {
      this.imamEdit.location = location;
    }
  }

}
</script>