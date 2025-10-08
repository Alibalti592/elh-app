<template>
  <div>
    <div class="text-right mb-1">
      <button @click="addNewFaq" class="bb-btn">Ajouter une Question</button>
    </div>
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="faqs || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
      >


        <template v-slot:reponse="props">
          <div v-html="props.rowData.reponse" />
        </template>

        <template v-slot:online="props">
          <i v-if="props.rowData.online" class="icon-check-circle color-success"></i>
          <i v-if="!props.rowData.online" class="icon-x-circle color-error"></i>
        </template>

        <template v-slot:actions="props">
          <div class="text-right">
            <button class="bb-btn-icon" @click="editFAQ(props.rowData)">
              <i class="icon-edit"></i>
            </button>
          </div>
        </template>

      </DataTable>
    </div>

    <modal ref="modalFaq" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Ajouter / éditer une Question</h2>
      </template>
      <template v-slot:body>

        <form method="post" @submit="saveFAQ" v-if="faqEdit != null">
          <div class="form-group">
            <label for="question">Question</label>
            <input type="text" class="form-control" v-model="faqEdit.question" id="question" required>
          </div>

          <div class="form-group mt-3">
            <label>Réponse</label>
            <ckeditor :editor="editor" v-model="faqEdit.reponse" :config="editorConfig" :aria-required="true"></ckeditor>
          </div>

          <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox"  v-model="faqEdit.online" id="online">
            <label class="form-check-label" for="online">
              Afficher la réponse dans l'application
            </label>
          </div>

          <div class="text-center mt-4">
            <Loading v-if="faqSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!faqSaving">
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
  name: 'admin-faq-list',
  components: {
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
          name: 'question',
          label: 'Question',
          width: '350px',
          sortable: false
        },
        {
          name: 'reponse',
          label: 'Réponse',
          customElement: 'reponse',
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
      faqs: [],
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
      //modal faq
      faqEdit: null,
      faqSaving: false,
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-faqs'), this.$store.getters.filterParams).then(function(response){
        self.faqs = response.data.faqs;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewFaq() {
      this.faqSaving = false;
      this.faqEdit = {
        id: null,
        question: '',
        online: '',
        reponse: ''
      };
      this.$refs.modalFaq.openModal();
    },
    editFAQ(faq) {
      this.faqSaving = false;
      this.faqEdit = ArrayObjectService.cloneOject(faq);
      this.$refs.modalFaq.openModal();
    },
    saveFAQ(e) {
      e.preventDefault();
      this.faqSaving = true;
      let formData = new FormData();
      formData.append('faq', JSON.stringify(this.faqEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-faq"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalFaq.closeModal();
        self.faqSaving = false;
      }).catch((error) => {
        self.faqSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    }
  }

}
</script>