<template>
  <div>

    <div class="card">
      <div class="row">
        <div class="col-md-6">
          <h4 class="mb-3 small">
            Texte info
          </h4>
        </div>
        <div class="col-md-6">
          <div class="text-right mb-1 mr-2">
            <button @click="editDonText" class="bb-btn">Éditer texte info</button>
          </div>
        </div>
      </div>
      <Loading v-if="isLoading"></Loading>

      <div v-html="content"></div>
    </div>

    <modal ref="modalTextDon" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Éditer le texte</h2>
      </template>
      <template v-slot:body>
        <form method="post" @submit="saveDonText">
          <div class="form-group mt-3">
            <ckeditor :editor="editor" v-model="contentEdit" :config="editorConfig" :aria-required="true"></ckeditor>
          </div>

          <div class="text-center mt-4">
            <Loading v-if="isSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!isSaving">
              Enregistrer
            </button>
          </div>
        </form>
      </template>
    </modal>



    <div class="card">
      <div class="text-right mb-1">
        <button @click="addNewDon" class="bb-btn">Ajouter une Associtation</button>
      </div>
      <div id="bb-datatable" class="card p-2" @update-data="loadDons">
        <!-- Datatable -->
        <DataTable
          :header-fields="headerFields"
          :data="dons || []"
          :is-loading="isLoading"
          not-found-msg="Aucun résultat"
          track-by="id"
          @update-data="loadDons"
        >



          <template v-slot:image="props">

            <BBImageCropUpload
              :uploadUrl="uploadURL"
              :imageSrc="props.rowData.logo"
              :previewWidth="100"
              :itemId="props.rowData.id"
              :title="'Image: ' + props.rowData.name"
              :forceMimeType="'image/jpeg'"
              :uploadImageMaxWidth="100"
              :uploadImageMaxHeight="100"
              @image-change="imageChanged"
            >
            </BBImageCropUpload>

          </template>

          <template v-slot:description="props">
            <div v-html="props.rowData.description" />
          </template>


          <template v-slot:link="props">
            <a :href="props.rowData.link" v-if="props.rowData.link != null">Lien</a>
          </template>

          <template v-slot:actions="props">
            <div class="text-right">
              <button class="bb-btn-icon" @click="editDon(props.rowData)">
                <i class="icon-edit"></i>
              </button>

              <button class="bb-btn-icon ml-2" @click="deleteDon(props.rowData)">
                <i class="icon-trash"></i>
              </button>
            </div>
          </template>

        </DataTable>

        <modal ref="modalDon" modal-class="modal-lg">
          <template v-slot:header>
            <h2 class="text-center">Ajouter / éditer une Associtation</h2>
          </template>
          <template v-slot:body>

            <form method="post" @submit="saveDon" v-if="donEdit != null">

              <div class="form-group mt-3">
                <label>Nom</label>
                <input type="text" class="form-control" v-model="donEdit.name">
              </div>

              <div class="form-group mt-3">
                <label>Description</label>
                <ckeditor :editor="editor" v-model="donEdit.description" :config="editorConfig" :aria-required="true"></ckeditor>
              </div>

              <div class="form-group mt-3">
                <label>Lien (url)</label>
                <input type="text" class="form-control" v-model="donEdit.link">
              </div>

              <div class="text-center mt-4">
                <Loading v-if="donSaving"></Loading>
                <button type="submit" class="bb-btn" v-if="!donSaving">
                  Enregistrer
                </button>
              </div>
            </form>
          </template>

        </modal>
      </div>
    </div>
  </div>


</template>


<script>
import axios from "axios";
import Modal from "@/components/modal";
import { messageService } from "@/services/message";
import {urlAdminService} from "@/services/urlAdminService";
import Loading from "@/components/loading";
import CKEditor from '@ckeditor/ckeditor5-vue';
import  ClassicEditor  from '@ckeditor/ckeditor5-build-classic';
import DataTable from "@/components/datatable/components/DataTable.vue";
import {ArrayObjectService} from "@/services/ArrayObject";
import SelectLocation from "@/components/location.vue";
import VueDatePicker from "@vuepic/vue-datepicker";
import BBImageCropUpload from "@/components/bb-imagecropupload.vue";

export default {
  name: 'admin-don',
  components: {
    BBImageCropUpload,
    VueDatePicker, SelectLocation,
    DataTable,
    Loading,
    Modal,
    ckeditor: CKEditor.component,
  },
  data: function () {
    return {
      content: "",
      isLoading: true,
      isSaving: false,
      editor: ClassicEditor,
      editorConfig: {
        link: {
          addTargetToExternalLinks: true
        },
        toolbar: [ 'bold', 'italic', '|', 'link', 'BulletedList']
      },
      contentEdit: "",
      headerFields: [
        {
          name: 'id',
          label: '#',
          width: '90px',
          sortable: true
        },
        {
          name: 'image',
          label: 'Logo',
          sortable: false,
          customElement: 'image'
        },
        {
          name: 'name',
          label: 'Nom',
          sortable: false
        },
        {
          name: 'description',
          label: 'Description',
          customElement: 'description',
          sortable: false
        },
        {
          name: 'Lien',
          label: "Lien (url)",
          sortable: true,
          customElement: 'link'
        },
        '__slot:actions'
      ],
      dons: [],
      //modal don
      donIni: null,
      donEdit: null,
      donSaving: false,
    }
  },

  created() {
    this.loadTextIntro();
    this.loadDons();
  },
  computed: {
    uploadURL() {
      return urlAdminService.getAURL("/v-don-upload-photo");
    }
  },

  methods: {
    loadDons: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-dons')).then(function(response){
        self.dons = response.data.dons;
        self.donIni = response.data.donIni;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
        messageService.showMessageFromResponse(error.response);
      });
    },
    addNewDon() {
      this.donSaving = false;
      this.donEdit = ArrayObjectService.cloneOject(this.donIni);
      this.$refs.modalDon.openModal();
    },
    editDon(don) {
      this.donSaving = false;
      this.donEdit = ArrayObjectService.cloneOject(don);
      this.$refs.modalDon.openModal();
    },
    saveDon(e) {
      e.preventDefault();
      this.donSaving = true;
      let formData = new FormData();
      formData.append('don', JSON.stringify(this.donEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-don"), formData).then((response) => {
        self.loadDons();
        messageService.showMessageFromResponse(response);
        self.$refs.modalDon.closeModal();
        self.donSaving = false;
      }).catch((error) => {
        self.donSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    deleteDon(don) {
      if(confirm('Confirmer la supression ?')) {
        let self = this;
        let formData = new FormData();
        formData.append('don', JSON.stringify(don));
        axios.post(urlAdminService.getAURL("/v-delete-don"), formData).then((response) => {
          self.loadDons();
          messageService.showMessageFromResponse(response);
        }).catch((error) => {
          messageService.showMessageFromResponse(error.response);
        });
      }
    },
    loadTextIntro: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-intro?page=don')).then(function(response){
        self.content = response.data.content;
        self.contentEdit = response.data.contentEdit;
        self.isLoading = false;
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    editDonText() {
      this.isSaving = false;
      this.$refs.modalTextDon.openModal();
    },
    saveDonText(e) {
      e.preventDefault();
      this.isSaving = true;
      let formData = new FormData();
      formData.append('content', this.contentEdit);
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-intro?page=don"), formData).then((response) => {
        self.loadTextIntro();
        messageService.showMessageFromResponse(response);
        self.$refs.modalTextDon.closeModal();
        self.isSaving = false;
        self.loadTextIntro();
      }).catch((error) => {
        self.isSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    imageChanged(data) {
      this.loadDons();
    },
  }

}
</script>