<template>
  <div>
    <div class="text-right mb-1 mr-2">
      <button @click="editIntro" class="bb-btn">Éditer</button>
    </div>
    <div class="card">
      <Loading v-if="isLoading"></Loading>
      <h4 class="mb-3 small">
        Texte introduction
      </h4>
      <div v-html="content"></div>
    </div>

    <modal ref="modalIntro" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Éditer le texte</h2>
      </template>
      <template v-slot:body>
        <form method="post" @submit="saveIntro">
          <div class="form-group mt-3">
            <label>Contenu</label>
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

export default {
  name: 'admin-intro',
  components: {
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
        toolbar: [ 'bold', 'italic', '|', 'link', 'BulletedList' ]
      },
      contentEdit: "",
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-intro'), this.$store.getters.filterParams).then(function(response){
        self.content = response.data.content;
        self.contentEdit = response.data.contentEdit;
        self.isLoading = false;
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    editIntro() {
      this.isSaving = false;
      this.$refs.modalIntro.openModal();
    },
    saveIntro(e) {
      e.preventDefault();
      this.isSaving = true;
      let formData = new FormData();
      formData.append('content', this.contentEdit);
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-intro"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalIntro.closeModal();
        self.isSaving = false;
        self.loadDatas();
      }).catch((error) => {
        self.isSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    setLocation(location) {
      this.maraudeEdit.location = location;
    }
  }

}
</script>