<template>
  <div>
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="emails || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
      >

        <template v-slot:content="props">
          <div v-html="props.rowData.content" />
        </template>


        <template v-slot:actions="props">
          <div class="text-right">
            <button class="bb-btn-icon" @click="editEmail(props.rowData)">
              <i class="icon-edit"></i>
            </button>
            <button class="bb-btn-icon" @click="openMailContent(props.rowData)">
              <i class="icon-eye"></i>
            </button>
          </div>
        </template>

      </DataTable>
    </div>

    <modal ref="modalEmailPreview" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Prévisualiser le mail</h2>
      </template>
      <template v-slot:body>
        <Loading v-if="mailLoading"></Loading>
        <div v-if="!mailLoading && mailContent != null" v-html="mailContent" />
      </template>
    </modal>

    <modal ref="modalEmail" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Éditer le mail</h2>
      </template>
      <template v-slot:body>

        <form method="post" @submit="saveEmail" v-if="emailEdit != null">

          <p class="info">
            Mail envoyé : {{ emailEdit.name }}
          </p>
          <p class="info">
            Variables utilisables <i class="icon-info" v-tippy content="Les variables sertont remplacé par des valeurs exemple {prenom} => Nicolas"></i> : {{ emailEdit.variables }}
          </p>

          <div class="form-group">
            <label for="question">Titre / Objet</label>
            <input type="text" class="form-control" v-model="emailEdit.subject" id="question" required>
          </div>

          <div class="form-group mt-3">
            <label>Contenu</label>
            <ckeditor :editor="editor" v-model="emailEdit.content" :config="editorConfig" :aria-required="true"></ckeditor>
          </div>

          <div class="text-center mt-4">
            <Loading v-if="emailSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!emailSaving">
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
  name: 'admin-email-list',
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
          name: 'name',
          label: 'Motif envoi',
          width: '250px',
          sortable: false
        },
        {
          name: 'subject',
          label: 'Titre / Objet',
          width: '350px',
          sortable: false
        },
        {
          name: 'content',
          label: 'Contenu',
          customElement: 'content',
          sortable: false
        },
        '__slot:actions'
      ],
      emails: [],
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
      //modal email
      emailEdit: null,
      emailSaving: false,
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
      axios.get(urlAdminService.getAURL('/v-load-list-emails'), this.$store.getters.filterParams).then(function(response){
        self.emails = response.data.emails;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewEmail() {
      this.emailSaving = false;
      this.emailEdit = {
        id: null,
        question: '',
        online: '',
        reponse: ''
      };
      this.$refs.modalEmail.openModal();
    },
    editEmail(email) {
      this.emailSaving = false;
      this.emailEdit = ArrayObjectService.cloneOject(email);
      this.$refs.modalEmail.openModal();
    },
    saveEmail(e) {
      e.preventDefault();
      this.emailSaving = true;
      let formData = new FormData();
      formData.append('email', JSON.stringify(this.emailEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-email"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalEmail.closeModal();
        self.emailSaving = false;
      }).catch((error) => {
        self.emailSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    openMailContent(mail) {
      this.mailLoading = true;
      let self = this;
      this.$refs.modalEmailPreview.openModal();
      axios.get(urlAdminService.getAURL("/v-load-email-content"), { params: {mailkey : mail.mailkey}}).then(function(response){
        self.mailContent = response.data.mailContent;
        self.title = response.data.title;
        self.mailLoading = false;
      }).catch(function (error) {
        messageService.showMessageFromResponse(error.response);
      });
    }
  }

}
</script>
<script setup>
</script>