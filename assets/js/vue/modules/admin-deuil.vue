<template>
  <div>
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="mb-3 small">
        Textes avec une date saisie le : {{ startDateDisplay }}
      </h4>
      <div class="text-right mb-1 mt-1 ml-2">
        <button @click="editDeuil" class="bb-btn">Éditer les textes des périodes</button>
      </div>
    </div>

    <div class="">
      <Loading v-if="isLoading"></Loading>
      <div v-for="deuil in deuils" class="mb-4 card">
        <h3 style="color: #000">{{  getLabel(deuil.type) }}</h3>
        <div v-html="deuil.content"></div>
      </div>

    </div>

    <modal ref="modalDeuil" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Éditer le texte</h2>
      </template>
      <template v-slot:body>

        <p class="info">
          Dans le texte {date_plus_trois_jour} correspond à la date dsaisie +3 jours <br>
          Dans le texte {datefin} correspond à la date saisie +4 mois et 10 jours. <br>
        </p>

        <form method="post" @submit="saveDeuil">

          <div class="form-group mt-3">
            <div v-for="deuil in deuilsEdit" class="mb-3">
              <h3>{{  getLabel(deuil.type) }}</h3>
              <ckeditor :editor="editor" v-model="deuil.content" :config="editorConfig" :aria-required="true"></ckeditor>
            </div>


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
import {ArrayObjectService} from '@/services/ArrayObject';
import VueDatePicker from '@vuepic/vue-datepicker';

export default {
  name: 'admin-maraude-list',
  components: {
    Loading,
    Modal,
    ckeditor: CKEditor.component,
    VueDatePicker
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
      deuils: [],
      deuilsEdit: [],
      contentEdit: "",
      startDateDisplay: "",
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    getLabel(type) {
      if(type == 'epouse') {
        return "Pour l'épouse";
      } else if(type == 'enceinte') {
        return "Pour l'épouse enceinte";
      }
      return  "Pour la famille";
    },
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-deuil'), this.$store.getters.filterParams).then(function(response){
        self.deuils = response.data.deuils;
        self.deuilsEdit = response.data.deuilsEdit;
        self.startDateDisplay = response.data.startDateDisplay;
        self.isLoading = false;
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    editDeuil() {
      this.isSaving = false;
      this.$refs.modalDeuil.openModal();
    },
    saveDeuil(e) {
      e.preventDefault();
      this.isSaving = true;
      let formData = new FormData();
      formData.append('deuils', JSON.stringify(this.deuilsEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-deuil"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalDeuil.closeModal();
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