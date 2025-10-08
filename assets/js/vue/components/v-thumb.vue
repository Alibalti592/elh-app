<template>
  <div v-tippy :title="fullName" :class="['member-thumb mt-small', extraClass]">
    <img v-if="image != null && image != false && image.length > 5" :src="image"
         :title="fullName">
    <span v-else>{{ letters }}</span>
  </div>
</template>

<script>
export default {
  name: 'v-thumb',
  props: {
    fullName: { type: String, default: '' },
    image: { type: String, default: ''  },
    letters: { type: String, default: '' },
    extraClass: {
      type: String,
      default: ''
    }
  }
};
</script>