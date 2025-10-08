<template>
  <div>
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="pages || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
      >

        <template v-slot:content="props">
          <div v-html="props.rowData.contentShort" />
        </template>


        <template v-slot:actions="props">
          <div class="text-right">
            <button class="bb-btn-icon" @click="editPage(props.rowData)">
              <i class="icon-edit"></i>
            </button>
          </div>
        </template>

      </DataTable>
    </div>

    <modal ref="modalNavPagePreview" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Prévisualiser le mail</h2>
      </template>
      <template v-slot:body>
        <Loading v-if="mailLoading"></Loading>
        <div v-if="!mailLoading && mailContent != null" v-html="mailContent" />
      </template>
    </modal>

    <modal ref="modalNavPage" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Éditer le contenu page de navigation</h2>
      </template>
      <template v-slot:body>

        <form method="post" @submit="savePage" v-if="pageEdit != null">

          <h2>
           {{ pageEdit.title }}
          </h2>

          <div class="form-group mt-3">
            <label>Contenu</label>
            <ckeditor :editor="editor" v-model="pageEdit.content" :config="editorConfig" :aria-required="true"></ckeditor>
          </div>

          <div class="form-group mt-3">
            <div class="image-uploader">
              <label class="col-form-label">Image</label>
              <div class="images text-center" v-if="imageSrc != null">
                <div class="exisiting-image">
                  <img :src="imageSrc"
                       style="max-width: 250px; max-height: 150px">
                  <i class="mx-1 edit-image icon-trash pointer" @click="deleteFile(pageEdit.image)"></i>
                </div>
              </div>
              <BBUploadImages @changed="handleImage($event, pageEdit.image)" :max="1" uploadMsg="Ajoute ton image"
                              :fileError="fileErrorMessage" :clearAll="clearAllMessage" v-if="imageSrc == null"/>
            </div>
          </div>

          <div class="form-group mt-3">
            <label for="name">Identifiant ou lien vidéo Youtube</label>
            <input type="text" class="form-control" v-model="pageEdit.video" id="video">
          </div>

          <div class="text-center mt-4">
            <Loading v-if="pageSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!pageSaving">
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
import Rotator from "@/services/imageRotator";
import {imageService} from "@/services/imageService";

export default {
  name: 'admin-nav-page-list',
  components: {
    Loading,
    DataTable,
    ItemsPerPageDropdown,
    Pagination,
    SearchBar,
    Modal,
    ckeditor: CKEditor.component,
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
          name: 'title',
          label: 'Titre',
          width: '250px',
          sortable: false
        },
        {
          name: 'contentShort',
          label: 'Contenu',
          customElement: 'content',
          sortable: false
        },
        '__slot:actions'
      ],
      pages: [],
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
      //modal page
      pageEdit: null,
      pageSaving: false,
      //preview
      mailContent: null,
      mailLoading: false,

      fileErrorMessage: "Seule les images sont acceptées",
      clearAllMessage: "Supprimer  l'image"
    }
  },
  computed: {
    imageSrc() {
      if(this.pageEdit != null) {
        if(this.pageEdit.image) {
          if(this.pageEdit.image.src != null) {
            return this.pageEdit.image.src;
          } else if(this.pageEdit.image.fileString != null) {
            return this.pageEdit.image.fileString;
          }
        }

      }
      return null;
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      console.log('rr');
      axios.get(urlAdminService.getAURL('/v-load-list-nav-pages'), this.$store.getters.filterParams).then(function(response){
        self.pages = response.data.pages;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewPage() {
      this.pageSaving = false;
      this.pageEdit = {
        id: null,
        question: '',
        online: '',
        reponse: ''
      };
      this.$refs.modalNavPage.openModal();
    },
    editPage(page) {
      this.pageSaving = false;
      this.pageEdit = ArrayObjectService.cloneOject(page);
      this.$refs.modalNavPage.openModal();
    },
    savePage(e) {
      e.preventDefault();
      this.pageSaving = true;
      let formData = new FormData();
      formData.append('page', JSON.stringify(this.pageEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-nav-page"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalNavPage.closeModal();
        self.pageSaving = false;
      }).catch((error) => {
        self.pageSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    async handleFile(documentToUpload)  {
      this.modalIsLoading = true;
      let file = documentToUpload.file;
      if(file != null) {
        documentToUpload.name = file.name;
        documentToUpload.label = file.name;
        if(file.type == 'image/jpeg') {
          try {
            file = await Rotator.createRotatedImageAsync(file, "blob");
          } catch (err) {}
        }
        let base64Image = await imageService.getBase64(file);
        //force JPEG
        if(file.type == 'image/png' || file.type == 'image/jpeg') {
          if(imageService.getImageSizeFromBase64(base64Image) > 6000) {
            //try redimension
            let mimeType = file.type == 'image/jpeg' ? 'image/jpeg' : false;
            base64Image = await imageService.getBase64ResizeFromFile(file, 1200, 1500, mimeType);
            if(imageService.getImageSizeFromBase64(base64Image) > 6000) {
              documentToUpload.file = null;
              messageService.showMessage('error', 'Fichier trop volumineux !');
              return;
            }
          }
        } else if(imageService.getImageSizeFromBase64(base64Image) > 8000) {
          messageService.showMessage('error', 'Fichier trop volumineux !');
          return;
        }
        documentToUpload.fileString = base64Image;
      }
      this.modalIsLoading = false;
    },

    handleImage(filefromUploader, image) {
      let file = null;
      if(Array.isArray(filefromUploader) && filefromUploader[0] != undefined) {
        file = filefromUploader[0];
      } else {
        file = filefromUploader.file;
      }
      image.file = file;
      this.handleFile(image);
    },

    deleteFile(documentToUpload) {
      if(confirm("Confirmer la supression ?")) {
        this.modalIsLoading = true;
        let formData = new FormData();
        documentToUpload.src = null;
        documentToUpload.contentString = null;
      }
    },
  }

}
</script>
<script setup>
import BBUploadImages from "@/components/bb-upload-image.vue";
</script>