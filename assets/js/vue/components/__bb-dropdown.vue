<template>
  <div class="dropdown bb-dropdown">
    <a class="bb-btn-icon" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="icon-more-vertical"></i>
    </a>
    <ul class="dropdown-menu">
      <slot>

      </slot>
    </ul>
  </div>
</template>

<script>
import Dropdown from 'bootstrap/js/dist/dropdown';
export default {
  name: 'bb-dropdown',
};
</script>