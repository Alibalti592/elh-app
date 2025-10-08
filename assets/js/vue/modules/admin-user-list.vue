<template>
  <div id="bb-datatable" class="card p-2" @update-data="loadDatas">
    <search-bar @update-data="loadDatas"></search-bar>
    <!-- Datatable -->
    <DataTable
        :header-fields="headerFields"
        :data="users || []"
        :is-loading="isLoading"
        not-found-msg="Aucun résultat"
        track-by="id"
        @update-data="loadDatas"
    >

      <template v-slot:user="props">
        <div class="name">{{ props.rowData.fullname }}
          <br>
          <small>{{props.rowData.email}}</small>
          <br>
          <small>{{props.rowData.phoneFull}}</small>
        </div>
      </template>

      <template v-slot:enabled="props">
        <div class="pointer" @click="blockUser(props.rowData)">
          <i v-if="props.rowData.enabled" class="icon-check-circle color-success"></i>
          <i v-if="!props.rowData.enabled" class="icon-x-circle color-error"></i>
        </div>
      </template>



      <!-- Custom element for SWITCHING (create component ?!) -->
      <div slot="SwitchValidated" slot-scope="props" class="text-center">
<!--        <toggle-button-->
<!--            v-model="props.rowData.testeur"-->
<!--            @change="roleTesteur(props.rowData.id, props.rowData.testeur, 'testeur')"-->
<!--            :labels="false" :color="{unchecked: '#ff4646'}"/>-->
      </div>



      <template v-slot:actions="props">
        <div class="text-right">
          <bb-dropdown>
            <li class="dropdown-item" @click="openModalUpdatePassword(props.rowData)">
              <i class="icon-edit"></i> Modifier mot de passe
            </li>
            <li class="dropdown-item" @click="deleteUser(props.rowData)">
              <i class="icon-trash"></i> Supprimer
            </li>
          </bb-dropdown>
        </div>
      </template>

    </DataTable>

    <modal ref="modalUpdatePassword" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">Mise à jour du mot de passe</h2>
      </template>
      <template v-slot:body>

        <div class="form-group">
          <label for="newp">Nouveau mot de passe</label>
          <input type="text" v-model="newPassword" class="form-control">
        </div>

        <Loading v-if="updatingPassword"></Loading>
        <div class="my-2" v-if="!updatingPassword">
          <div class="bb-btn" @click="savePassword">
            Sauvegarder
          </div>
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
import { toogleField } from "@/services/actions";
import { messageService } from "@/services/message";
import {urlAdminService} from "@/services/urlAdminService";
import Loading from "@/components/loading";
import Modal from "@/components/modal";
import BbDropdown from "@/components/__bb-dropdown.vue";

export default {
  name: 'admin-user-list',
  components: {
    BbDropdown,
    Loading,
    DataTable,
    ItemsPerPageDropdown,
    Pagination,
    SearchBar,
    Modal
  },
  data: function () {
    return {
      headerFields: [
        // '__slot:checkboxes',
        {
          name: 'id',
          label: '#',
          width: '90px',
          sortable: true
        },
        {
          name: 'user',
          label: 'Utilisateur',
          customElement: 'user',
          width: '350px',
          sortable: false
        },
        {
          name: 'type',
          label: 'Type',
          sortable: false
        },
        {
          name: 'city',
          label: 'Ville',
          sortable: false,
        },
        {
          name: 'created',
          label: 'Inscrit le',
          sortable: false,
        },
        {
          name: 'lastLogin',
          // customHeader: 'createdHeader',
          label: 'Dernière connexion',
          sortable: false,
        },
        {
          name: 'enabled',
          label: 'Compte actif',
          customElement: 'enabled',
          sortable: false,
        },
        {
          name: 'actions',
          label: '',
          customElement: 'actions'
        },
      ],
      entity: "User",
      hasLoadForm: true,
      itemId: null,
      users: [],
      isLoading: true,
      formTitle: 'Éditer',
      userDatasIdLoading: null,
      currentUserEdit: null,
      updatingPassword: false,
      newPassword: "",
    }
  },

  async created() {
    await this.loadDatas();
  },

  methods: {
    loadDatas: async function () {
      this.isLoading = true;
      let self = this;

      axios.get(urlAdminService.getAURL('/v-load-list-users'), this.$store.getters.filterParams).then(function(response){
        self.users = response.data.users;
        self.isLoading = false;
        self.$store.dispatch('setPaginationAfterLoadData', response.data.count);
      }).catch(function (error) {
          messageService.showMessageFromResponse(error.response);
      });
    },
    loadModalForm(itemId) {
      // setter le bon item puis charger le form !!
      this.itemId = itemId;
      this.hasLoadForm = true;
      this.$refs.modalName.openModal();
      // this.$emit('form-modal-callback');
    },
    toogleField: function (id, value, field) {
      toogleField(id, value, field, this.toogleFieldUrl, this.entity);
    },
    roleTesteur: function (id, value, field) {
      toogleField(id, value, field, this.admin_add_role_testeur, this.entity);
    },
    onFormValidationSuccess: function () {
      this.loadDatas();
      this.$refs.modalName.closeModal();
    },
    loadUserDatas(user) {
      let self = this;
      this.userDatasIdLoading = user.id;
      axios.get(urlAdminService.getAURL("/v-admin-load-user-extradatas"), { params: { userId : user.id }}).then(function(response){
        self.userDatasIdLoading = null;
        user.userDatas = response.data.userDatas;
      }).catch(function (error) {
        messageService.showMessageFromResponse(error.response);
      });
    },
    openModalUpdatePassword(user) {
      this.currentUserEdit = user;
      this.$refs.modalUpdatePassword.openModal();
    },
    savePassword() {
      if(confirm("Modifier le mot de passe ?")) {
        let formData = new FormData();
        formData.append('user', this.currentUserEdit.id);
        formData.append('newPassword', this.newPassword);
        let self = this;
        self.updatingPassword = true;
        axios.post(urlAdminService.getAURL("/v-admin-user-updatepassword"), formData).then((response) => {
          messageService.showMessageFromResponse(response);
          self.updatingPassword = false;
          self.$refs.modalUpdatePassword.closeModal();
        }).catch((error) => {
          messageService.showMessageFromResponse(error.response);
          self.updatingPassword = false;
        });
      }
    },
    blockUser(user) {
      let ph = user.enabled ? "Confirmer le blocage ?" : "Confirmer le déblocage ?";
      if(confirm(ph)) {
        let formData = new FormData();
        formData.append('user', user.id);
        let self = this;
        axios.post(urlAdminService.getAURL("/v-admin-user-block"), formData).then((response) => {
          messageService.showMessageFromResponse(response);
          self.loadDatas();
        }).catch((error) => {
          messageService.showMessageFromResponse(error.response);
        });
      }
    },
    deleteUser(user) {
      let ph = user.enabled ? "Confirmer le blocage ?" : "Confirmer le déblocage ?";
      if(confirm("SUPPRIMER définitivement le compte ???")) {
        let formData = new FormData();
        formData.append('user', user.id);
        let self = this;
        axios.post(urlAdminService.getAURL("/v-admin-user-delete"), formData).then((response) => {
          messageService.showMessageFromResponse(response);
          self.loadDatas();
        }).catch((error) => {
          messageService.showMessageFromResponse(error.response);
        });
      }
    }

  }

}
</script>