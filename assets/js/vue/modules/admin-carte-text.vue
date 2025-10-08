<template>
  <div>
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="cartes || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
      >

        <template v-slot:content="props">
          <div style="white-space: pre-line">{{props.rowData.content}}</div>
        </template>


        <template v-slot:actions="props">
          <div class="text-right d-flex align-items-end">
            <button class="bb-btn-icon mr-2" @click="editTodo(props.rowData)">
              <i class="icon-edit"></i>
            </button>
          </div>
        </template>

      </DataTable>
    </div>

    <modal ref="modalTodo" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Ajouter / éditer une Formalité</h2>
      </template>
      <template v-slot:body>
        <form method="post" @submit="saveTodo" v-if="carteEdit != null">
          <div class="form-group mt-3">
            <label>Contenu</label>
            <textarea v-model="carteEdit.content" class="form-control" rows="20" style="height: 300px"></textarea>
          </div>

          <div class="text-center mt-4">
            <Loading v-if="carteSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!carteSaving">
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
import { VueDraggableNext } from 'vue-draggable-next'

export default {
  name: 'admin-carte-list',
  components: {
    Loading,
    DataTable,
    ItemsPerPageDropdown,
    Pagination,
    SearchBar,
    Modal,
    ckeditor: CKEditor.component,
    draggable: VueDraggableNext,
  },
  computed: {
    dragOptions() {
      return {
        animation: 200,
        disabled: false,
        ghostClass: "ghost"
      };
    },
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
          label: 'Nom',
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
      cartes: [],
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
      //modal
      carteEdit: null,
      carteSaving: false,
      //all
      isLoadingAll: false,
      allTodos: [],
      drag:false
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-cartes-textes'), this.$store.getters.filterParams).then(function(response){
        self.cartes = response.data.cartes;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewTodo() {
      this.carteSaving = false;
      this.carteEdit = {
        id: null,
        content: '',
      };
      this.$refs.modalTodo.openModal();
    },
    editTodo(carte) {
      this.carteSaving = false;
      this.carteEdit = ArrayObjectService.cloneOject(carte);
      this.$refs.modalTodo.openModal();
    },
    saveTodo(e) {
      e.preventDefault();
      this.carteSaving = true;
      let formData = new FormData();
      formData.append('carte', JSON.stringify(this.carteEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-carte"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalTodo.closeModal();
        self.carteSaving = false;
      }).catch((error) => {
        self.carteSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    }
  }

}
</script>