<template>
  <transition name="fade">
    <div class="modal show" v-if="show" ref="modal">
        <div class="modal__backdrop" @click="closeModal()"/>

        <div :class="['modal__dialog', modalClass]" ref="modaldialog">
          <div class="modal__header">
            <slot name="header"/>
              <i class="mdi mdi-close modal__close" @click="closeModal()"></i>
          </div>

          <div class="modal__body">
            <slot name="body"/>
          </div>

          <div class="modal__footer">
            <slot name="footer"/>
          </div>
        </div>
      </div>
  </transition>
</template>

<script>
  export default {
    name: "Modal",
    props: {
      modalClass: {
        type: String,
        default: 'modal-medium',
        required: false
      }
    },
    data() {
      return {
        show: false,
      };
    },
    methods: {
      closeModal() {
        this.show = false;
        document.querySelector("body").classList.remove("modal-open");
      },
      openModal() {
        this.show = true;
        document.querySelector("body").classList.add("modal-open");
      },
    },
    //when is rendered !
    updated: function () {
      this.$nextTick(function () {
        if(typeof this.$refs.modaldialog != "undefined") {
          let modalHeight = this.$refs.modaldialog.scrollHeight + 50;
          if(modalHeight > document.documentElement.clientHeight) {
            document.querySelector(".modal__backdrop").classList.add("has-scroll");
          }
        }
      });
    }
  };
</script>