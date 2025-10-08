<template>
  <div class="img-crop-upload bb-img-uploader">
    <div class="image-view text-center">
      <slot name="placeholder"></slot>
      <img :src="imageSrc" class="preview-img pointer" :width="previewWidth"
           @click="openModalToCrop" v-if="imageSrc != null"/>
      <i class="icon-pen-tool pointer edit-image" @click="openModalToCrop" v-if="imageSrc != null"></i>
      <i class="icon-camera pointer add-image" @click="openModalToCrop" v-if="imageSrc == null"></i>
      <input ref="FileInput" type="file" style="display: none;" @change="onFileSelect"  @click.stop />
    </div>

    <modal ref="modalCropUpload" modal-class="modal-lg">
      <template v-slot:header>
        <h2 class="text-center">{{ title }}</h2>
      </template>
      <template v-slot:body>
        <div class="bb-btn btn-small mt-3 mb-3" @click="addNewImage">
          <template v-if="imageSrc == null">Ajouter une image</template>
          <template v-else>Remplacer l'image</template>
        </div>

        <div class="image-container">
          <VueCropper v-show="imageCropSrc != null" ref="cropper"
                      :src="imageCropSrc" :viewMode="1" :aspectRatio="calcAspectRation" :zoomable="false"></VueCropper>
        </div>

        <div class="mt-3 text-center" v-if="imageCropSrc != null && !modalLoading">
          <div class="bb-btn" @click="saveImage()">Valider</div>
        </div>
        <Loading v-show="modalLoading"></Loading>
      </template>
    </modal>
  </div>
</template>


<script>
import axios from "axios";
import VueCropper from 'vue-cropperjs';
import  Loading from '@/components/loading';
import {messageService} from "@/services/message";
import Modal from "@/components/modal.vue";
export default {
  name: "BBImageCropUpload",
  components: {
    Modal,
    VueCropper,
    Loading,
  },
  props: {
    uploadUrl: {
      type: String,
      default: null
    },
    title: {
      type: String,
      default: null
    },
    imageSrc: {
      type: String,
      default: null
    },
    forceMimeType: {
      type: String,
      default: null
    },
    itemId: {
      type: Number,
      default: null
    },
    previewWidth: {
      type: Number,
      default: null
    },
    uploadImageMaxWidth: {
      type: Number,
      default: null
    },
    uploadImageMaxHeight: {
      type: Number,
      default: null
    },
    forceRatio: {
      type: Boolean,
      default: true
    },
  },
  data() {
    return {
      mime_type: 'image/jpeg',
      cropedImageBase64: '',
      autoCrop: false,
      modalLoading: true,
      imageCropSrc: this.imageSrc
    }
  },
  computed: {
    calcAspectRation() {
      if(this.forceRatio) {
        return this.uploadImageMaxWidth / this.uploadImageMaxHeight;
      }
      return NaN;
    }
  },
  methods: {
    openModalToCrop() {
      this.$refs.modalCropUpload.openModal();
      this.modalLoading = false;
    },
    addNewImage() {
      this.$refs.FileInput.click();
    },
    saveImage() {
      this.modalLoading = true;
      let mimeType = this.mime_type;
      if(this.forceMimeType != null) {
        mimeType = this.forceMimeType;
      }
      //https://github.com/fengyuanchen/cropperjs#options -> getCroppedCanvas
      this.cropedImageBase64 = this.$refs.cropper.getCroppedCanvas({
        'fillColor' : 'white', //for png
        'width': this.uploadImageMaxWidth,
        'height': this.uploadImageMaxWidth,
        'maxWidth': this.uploadImageMaxWidth + 1500, //for quality !!
        'maxHeight': this.uploadImageMaxHeight + 1500,
        'imageSmoothingEnabled': 'true',
        'imageSmoothingQuality': 'high',
      }).toDataURL(mimeType);
      let formData = new FormData();
      formData.append('image', this.cropedImageBase64);
      formData.append('mimeType', mimeType);
      if(this.itemId != null) {
        formData.append('itemId', this.itemId.toString());
      }
      let self= this;
      axios.post(this.uploadUrl, formData).then((response) => {
        self.$emit('image-change', response.data);
        self.modalLoading = false;
        self.$refs.modalCropUpload.closeModal();
      }).catch(function (error) {
        self.modalLoading = false;
        messageService.showMessageFromResponse(error.response);
      });
    },
    onFileSelect(e) {
      this.modalLoading = true;
      const file = e.target.files[0];
      this.mime_type = file.type;
      if (typeof FileReader === 'function') {
        const reader = new FileReader();
        reader.onload = (event) => {
          this.imageCropSrc = event.target.result.toString();
          this.$refs.cropper.replace(this.imageCropSrc);
        }
        reader.readAsDataURL(file);
      } else {
        alert('Désolé votre navigateur ne supporte pas la gestion des images (FileReader missing)');
      }
      this.modalLoading = false;
    },
  },
};
</script>

<style lang="scss">
.img-crop-upload {
  .image-view {
    position: relative;
  }
  .edit-image {
    position: absolute;
    bottom: 5px;
    right: 10px;
    background: #fff;
    display: block;
    width: 20px;
    height: 20px;
    line-height: 20px;
    text-align: center;
    color: #52bcf9;
    border-radius: 50%;
    &:hover, &:focus {
      color: #fff;
      background: #52bcf9;
    }
  }
}
</style>