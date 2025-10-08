<template>
  <div>
<!--    <div class="text-right mb-1">-->
<!--      <button @click="addNewSalat" class="bb-btn">Ajouter une Salat</button>-->
<!--    </div>-->
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="salats || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
      >


        <template v-slot:userSalat="props">
          <div>
            {{ props.rowData.firstname }} {{ props.rowData.lastname }}
          </div>
          <small>{{props.rowData.afiliationLabel}}</small>
        </template>

        <template v-slot:infos="props">
          <div v-if="props.rowData.mosque != null">
            Prière : {{ props.rowData.dateDisplayFull }}
          </div>
          <div v-if="props.rowData.mosque != null">
            Mosqué : {{ props.rowData.mosque.name }}
          </div>
          <div>
            Cimetière : {{ props.rowData.cimetary }}
          </div>
        </template>


        <template v-slot:content="props">
          <div v-html="props.rowData.content"> </div>
        </template>

        <template v-slot:actions="props">
          <div class="text-right">
            <button class="bb-btn-icon" @click="deleteSalat(props.rowData)">
              <i class="icon-trash-2"></i>
            </button>
          </div>
        </template>

      </DataTable>
    </div>

<!--    <modal ref="modalSalat" modal-class="modal-lg">-->
<!--      <template v-slot:header>-->
<!--        <h2 class="text-center">Ajouter / éditer une Salat</h2>-->
<!--      </template>-->
<!--      <template v-slot:body>-->

<!--        <form method="post" @submit="saveSalat" v-if="salatEdit != null">-->

<!--          <div class="form-group mt-3">-->
<!--            <label>Ville</label>-->
<!--            <SelectLocation :initialLocation="salatEdit.location" @updateLocation="setLocation"></SelectLocation>-->
<!--          </div>-->

<!--          <div class="form-group mt-3">-->
<!--            <label>Description</label>-->
<!--            <ckeditor :editor="editor" v-model="salatEdit.description" :config="editorConfig" :aria-required="true"></ckeditor>-->
<!--          </div>-->


<!--          <div class="form-group mt-3">-->
<!--            <label>Date & heure</label>-->
<!--            <VueDatePicker v-model="salatEdit.dateVue" locale="fr"></VueDatePicker>-->
<!--          </div>-->


<!--          <div class="form-check mt-3">-->
<!--            <input class="form-check-input" type="checkbox"  v-model="salatEdit.online" id="online">-->
<!--            <label class="form-check-label" for="online">-->
<!--              Afficher la salat dans l'application-->
<!--            </label>-->
<!--          </div>-->

<!--          <div class="text-center mt-4">-->
<!--            <Loading v-if="salatSaving"></Loading>-->
<!--            <button type="submit" class="bb-btn" v-if="!salatSaving">-->
<!--              Enregistrer-->
<!--            </button>-->
<!--          </div>-->
<!--        </form>-->
<!--      </template>-->
      
<!--    </modal>-->
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
  name: 'admin-salat-list',
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
          name: 'createdBy',
          label: 'Créé par',
          sortable: false
        },
        {
          name: 'user',
          label: 'Personne de la salat',
          customElement: 'userSalat',
          sortable: false
        },
        {
          name: 'infos',
          label: 'Infos Salat',
          customElement: 'infos',
          sortable: false
        },
        {
          name: 'mosqueManual',
          label: 'Mosquée saisie manuelle',
          sortable: false
        },
        {
          name: 'content',
          label: 'Message',
          sortable: false
        },
        '__slot:actions'
      ],
      salats: [],
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
      //modal salat
      salatIni: null,
      salatEdit: null,
      salatSaving: false,
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-salats'), this.$store.getters.filterParams).then(function(response){
        self.salats = response.data.salats;
        self.salatIni = response.data.salatIni;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewSalat() {
      this.salatSaving = false;
      this.salatEdit = ArrayObjectService.cloneOject(this.salatIni);
      this.$refs.modalSalat.openModal();
    },
    editSalat(salat) {
      this.salatSaving = false;
      this.salatEdit = ArrayObjectService.cloneOject(salat);
      this.$refs.modalSalat.openModal();
    },
    saveSalat(e) {
      e.preventDefault();
      this.salatSaving = true;
      let formData = new FormData();
      formData.append('salat', JSON.stringify(this.salatEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-salat"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalSalat.closeModal();
        self.salatSaving = false;
      }).catch((error) => {
        self.salatSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    deleteSalat(salat) {
      if(confirm('Confirmer la supression ?')) {
        let self = this;
        let formData = new FormData();
        formData.append('salat', JSON.stringify(salat));
        axios.post(urlAdminService.getAURL("/v-admin-delete-salat"), formData).then((response) => {
          self.loadDatas();
          messageService.showMessageFromResponse(response);
        }).catch((error) => {
          messageService.showMessageFromResponse(error.response);
        });
      }
    },
    setLocation(location) {
      this.salatEdit.location = location;
    }
  }

}
</script>