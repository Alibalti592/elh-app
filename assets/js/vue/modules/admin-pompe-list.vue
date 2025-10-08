<template>
  <div>
    <div class="text-right mb-1">
      <button @click="addNewPompe" class="bb-btn">Ajouter une Pompe Funèbre</button>
    </div>
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="pompes || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
      >


        <template v-slot:description="props">
          <div v-html="props.rowData.description" />
        </template>

        <template v-slot:user="props">
          <div v-if="props.rowData.user != null">
            {{ props.rowData.user.fullname }} <br>
            <small> {{ props.rowData.user.phoneFull }}</small> <br>
            <small> {{ props.rowData.user.email }}</small>
          </div>

        </template>


        <template v-slot:location="props">
          <div>
            {{ props.rowData.location.label }}
            <small> - {{ props.rowData.location.region }}</small>
          </div>
        </template>


        <template v-slot:validated="props">
          <i v-if="props.rowData.validated" class="icon-check-circle color-success"></i>
          <i v-if="!props.rowData.validated" class="icon-x-circle color-error"> À valider</i>
        </template>

        <template v-slot:online="props">
          <i v-if="props.rowData.online" class="icon-check-circle color-success"></i>
          <i v-if="!props.rowData.online" class="icon-x-circle color-error"></i>
        </template>

        <template v-slot:actions="props">
          <div class="text-right">
            <button class="bb-btn-icon" @click="editPompe(props.rowData)">
              <i class="icon-edit"></i>
            </button>
          </div>
        </template>

      </DataTable>
    </div>

    <modal ref="modalPompe" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Ajouter / éditer une Pompe Funèbre</h2>
      </template>
      <template v-slot:body>

        <form method="post" @submit="savePompe" v-if="pompeEdit != null">
          <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" class="form-control" v-model="pompeEdit.name" id="name" required>
          </div>

          <div class="form-group mt-3">
            <label>Description</label>
<!--            <ckeditor :editor="editor" v-model="pompeEdit.description" :config="editorConfig" :aria-required="true"></ckeditor>-->
              <textarea class="form-control" rows="5" v-model="pompeEdit.description"></textarea>
          </div>

          <div class="form-group mt-3">
            <label>Gestionnaire</label>
            <BbSelectUser :initial-user="pompeEdit.user" @updateUser="setManagedBy"></BbSelectUser>
          </div>

          <div class="form-group mt-3">
            <label>Adresse</label>
            <SelectLocation :initialLocation="pompeEdit.location" @updateLocation="setLocation" :adresse="true"></SelectLocation>
          </div>

          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox"  v-model="pompeEdit.online" id="online">
            <label class="form-check-label" for="online">
              Afficher la pompe funèbre dans l'application
            </label>
          </div>

          <div class="text-center mt-4">
            <Loading v-if="pompeSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!pompeSaving">
              Valider
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
  name: 'admin-pompe-list',
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
          label: 'Pompe',
          width: '150px',
          sortable: false
        },
        {
          name: 'location',
          label: 'Lieu',
          customElement: 'location',
          sortable: false,
          width: '250px',
        },
        {
          name: 'description',
          label: 'Description',
          customElement: 'description',
          sortable: false
        },
        {
          name: 'fullname',
          label: 'Nom du responsable',
          sortable: false
        },
        {
          name: 'phone',
          label: 'Téléphone',
          sortable: false
        },
        {
          name: 'managedBy',
          label: 'Compte lié',
          sortable: false,
          customElement: 'user',
          width: '150px',
        },
        {
          name: 'validated',
          label: "Validation",
          sortable: true,
          width: '150px',
          customElement: 'validated'
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
      pompes: [],
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
      //modal pompe
      pompeIni: null,
      pompeEdit: null,
      pompeSaving: false,
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-pompes'), this.$store.getters.filterParams).then(function(response){
        self.pompes = response.data.pompes;
        self.pompeIni = response.data.pompeIni;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewPompe() {
      this.pompeSaving = false;
      this.pompeEdit = ArrayObjectService.cloneOject(this.pompeIni);
      this.$refs.modalPompe.openModal();
    },
    editPompe(pompe) {
      this.pompeSaving = false;
      this.pompeEdit = ArrayObjectService.cloneOject(pompe);
      this.$refs.modalPompe.openModal();
    },
    savePompe(e) {
      e.preventDefault();
      this.pompeSaving = true;
      let formData = new FormData();
      formData.append('pompe', JSON.stringify(this.pompeEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-pompe"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalPompe.closeModal();
        self.pompeSaving = false;
      }).catch((error) => {
        self.pompeSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    setLocation(location) {
      this.pompeEdit.location = location;
    },
    setManagedBy(user) {
      this.pompeEdit.user = user;
    }
  }

}
</script>