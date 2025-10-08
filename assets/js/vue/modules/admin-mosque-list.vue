<template>
  <div>
    <div class="text-right mb-1">
      <button @click="addNewMosque" class="bb-btn">Ajouter une Mosque</button>
    </div>
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="mosques || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
      >


        <template v-slot:description="props">
          <div v-html="props.rowData.description" />
        </template>

        <template v-slot:managedBy="props">
          <div v-if="props.rowData.managedBy != null">
            <div>{{ props.rowData.managedBy.fullname }}</div>
            <small>{{ props.rowData.managedBy.email }}</small>
          </div>
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

    <modal ref="modalMosque" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Ajouter / éditer une Mosque</h2>
      </template>
      <template v-slot:body>

        <form method="post" @submit="saveMOSQUE" v-if="mosqueEdit != null">
          <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" class="form-control" v-model="mosqueEdit.name" id="name" required>
          </div>

          <div class="form-group mt-3">
            <label>Description</label>
            <ckeditor :editor="editor" v-model="mosqueEdit.description" :config="editorConfig" :aria-required="true"></ckeditor>
          </div>

          <div class="form-group mt-3">
            <label>Ville</label>
            <SelectLocation :initialLocation="mosqueEdit.location" @updateLocation="setLocation"></SelectLocation>
          </div>

          <div class="form-group mt-3">
            <label>Gestionnaire</label>
            <BbSelectUser :initial-user="mosqueEdit.managedBy" @updateUser="setManagedBy"></BbSelectUser>
          </div>

          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox"  v-model="mosqueEdit.online" id="online">
            <label class="form-check-label" for="online">
              Afficher la mosquée dans l'application
            </label>
          </div>

          <div class="text-center mt-4">
            <Loading v-if="mosqueSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!mosqueSaving">
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
import BbSelectUser from "@/components/select-user.vue";

export default {
  name: 'admin-mosque-list',
  components: {
    BbSelectUser,
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
          label: 'Mosque',
          width: '200px',
          sortable: false
        },
        {
          name: 'managedBy',
          label: 'Géré par',
          width: '180px',
          customElement: 'managedBy',
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
      mosques: [],
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
      //modal mosque
      mosqueIni: null,
      mosqueEdit: null,
      mosqueSaving: false,
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-mosques'), this.$store.getters.filterParams).then(function(response){
        self.mosques = response.data.mosques;
        self.mosqueIni = response.data.mosqueIni;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewMosque() {
      this.mosqueSaving = false;
      this.mosqueEdit = ArrayObjectService.cloneOject(this.mosqueIni);
      this.$refs.modalMosque.openModal();
    },
    editMOSQUE(mosque) {
      this.mosqueSaving = false;
      this.mosqueEdit = ArrayObjectService.cloneOject(mosque);
      this.$refs.modalMosque.openModal();
    },
    saveMOSQUE(e) {
      e.preventDefault();
      this.mosqueSaving = true;
      let formData = new FormData();
      formData.append('mosque', JSON.stringify(this.mosqueEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-mosque"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalMosque.closeModal();
        self.mosqueSaving = false;
      }).catch((error) => {
        self.mosqueSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    setLocation(location) {
      this.mosqueEdit.location = location;
    },
    setManagedBy(user) {
      this.mosqueEdit.managedBy = user;
    }
  }

}
</script>