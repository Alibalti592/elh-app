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

    <modal ref="modalPagePreview" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Prévisualiser le mail</h2>
      </template>
      <template v-slot:body>
        <Loading v-if="mailLoading"></Loading>
        <div v-if="!mailLoading && mailContent != null" v-html="mailContent" />
      </template>
    </modal>

    <modal ref="modalPage" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Éditer le contenu</h2>
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

export default {
  name: 'admin-page-list',
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
      mailLoading: false
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-pages'), this.$store.getters.filterParams).then(function(response){
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
      this.$refs.modalPage.openModal();
    },
    editPage(page) {
      this.pageSaving = false;
      this.pageEdit = ArrayObjectService.cloneOject(page);
      this.$refs.modalPage.openModal();
    },
    savePage(e) {
      e.preventDefault();
      this.pageSaving = true;
      let formData = new FormData();
      formData.append('page', JSON.stringify(this.pageEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-page"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalPage.closeModal();
        self.pageSaving = false;
      }).catch((error) => {
        self.pageSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    }
  }

}
</script>
<script setup>
</script>