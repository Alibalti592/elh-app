<template>
  <div>
    <div class="text-right mb-1">
      <button @click="organizeTodos" class="bb-btn mx-3">Ordonner</button>
      <button @click="addNewTodo" class="bb-btn">Ajouter une Formalité</button>
    </div>
    <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
      <!-- Datatable -->
      <DataTable
        :header-fields="headerFields"
        :data="todos || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
      >

        <template v-slot:content="props">
          <div v-html="props.rowData.content" />
        </template>


        <template v-slot:actions="props">
          <div class="text-right d-flex align-items-end">
            <button class="bb-btn-icon mr-2" @click="editTodo(props.rowData)">
              <i class="icon-edit"></i>
            </button>

            <button class="bb-btn-icon mx-2" @click="deleteTodo(props.rowData)">
              <i class="icon-trash"></i>
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
        <form method="post" @submit="saveTodo" v-if="todoEdit != null">
          <div class="form-group mt-3">
            <label>Contenu</label>
            <ckeditor :editor="editor" v-model="todoEdit.content" :config="editorConfig" :aria-required="true"></ckeditor>
          </div>

          <div class="text-center mt-4">
            <Loading v-if="todoSaving"></Loading>
            <button type="submit" class="bb-btn" v-if="!todoSaving">
              Enregistrer
            </button>
          </div>
        </form>
      </template>
    </modal>


    <modal ref="modalTodoOrganize" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Ordonner les formalités</h2>
      </template>
      <template v-slot:body>
        <div v-if="allTodos.length > 0">
          <draggable
                      item-key="id"
                      @start="drag=true"
                      @end="setOrder"
                      handle=".handle" :list="allTodos">
              <div class="list-organize"  v-for="todo in allTodos" :key="todo.id">
                <i class="icon-move handle"></i>
                <div class="content" v-html="todo.contentShort"></div>
              </div>
          </draggable>

          <div class="text-center my-3">
            <div class="bb-btn"  @click="saveOrders">Sauvegarder</div>
          </div>
          <Loading v-if="isLoadingAll"></Loading>
        </div>

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
  name: 'admin-todo-list',
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
          name: 'content',
          label: 'Contenu',
          customElement: 'content',
          sortable: false
        },
        '__slot:actions'
      ],
      todos: [],
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
      todoEdit: null,
      todoSaving: false,
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
      axios.get(urlAdminService.getAURL('/v-load-list-todos'), this.$store.getters.filterParams).then(function(response){
        self.todos = response.data.todos;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.totalItems);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    addNewTodo() {
      this.todoSaving = false;
      this.todoEdit = {
        id: null,
        content: '',
      };
      this.$refs.modalTodo.openModal();
    },
    editTodo(todo) {
      this.todoSaving = false;
      this.todoEdit = ArrayObjectService.cloneOject(todo);
      this.$refs.modalTodo.openModal();
    },
    saveTodo(e) {
      e.preventDefault();
      this.todoSaving = true;
      let formData = new FormData();
      formData.append('todo', JSON.stringify(this.todoEdit));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-todo"), formData).then((response) => {
        self.loadDatas();
        messageService.showMessageFromResponse(response);
        self.$refs.modalTodo.closeModal();
        self.todoSaving = false;
      }).catch((error) => {
        self.todoSaving = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    organizeTodos() {
      this.isLoadingAll = true;
      let self = this;
      axios.get(urlAdminService.getAURL('/v-load-list-todos?all=true'), this.$store.getters.filterParams).then(function(response){
        self.allTodos = response.data.todos;
        self.isLoadingAll = false;
      }).catch(function (error) {
        self.isLoadingAll = false;
        messageService.showMessageFromResponse(error.response);
      });
      this.$refs.modalTodoOrganize.openModal();
    },
    setOrder() {
      this.drag = false;
      this.allTodos.forEach((todo, index) => {
        todo.ordered = index;
      });
    },
    saveOrders() {
      this.isLoadingAll = true;
      let formData = new FormData();
      formData.append('todos', JSON.stringify(this.allTodos));
      let self = this;
      axios.post(urlAdminService.getAURL("/v-save-todos-order"), formData).then((response) => {
        self.loadDatas();
        self.$refs.modalTodoOrganize.closeModal();
        self.isLoadingAll = false;
      }).catch((error) => {
        self.isLoadingAll = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    deleteTodo(todo) {
      if (window.confirm("Souhaitez-vous vraiment supprimer l'élément ?")) {
        this.isLoading = true;
        let formData = new FormData();
        formData.append('todo', JSON.stringify(todo));
        let self = this;
        axios.post(urlAdminService.getAURL("/v-delete-todo"), formData).then((response) => {
          self.loadDatas();
        }).catch((error) => {
          self.isLoading = false;
          messageService.showMessageFromResponse(error.response);
        });
      }

    },
  }

}
</script>