<template>
  <div>
    <div class="container">
      <div class="text-center pb-2">
        <a href="#" v-on:click="reload(undefined, $event)" class="farm-loading-refresh"><i class="fas fa-sync"></i></a> -
        <a class="text-muted text-small text-decoration-none">{{ context.address_truncate }}</a>
        -
        <a class="text-muted text-small" target="_blank"
           :href="'https://bscscan.com/address/' + encodeURIComponent(context.address)"><i class="fas fa-external-link-alt"></i></a>
      </div>
    </div>

    <div class="text-center ajax-spinner" :class="!isLoading ? 'd-none' : ''">
      <div class="spinner-border">
        <span class="sr-only">Loading...</span>
      </div>
    </div>

    <div class="fade-in" v-html="content" :class="!content ? 'd-none' : ''"></div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isLoading: true,
      content: '',
      context: {},
    };
  },

  methods: {
    async fetch(url) {
      this.isLoading = true;
      this.content = '';

      const res = await fetch(url);
      const content = await res.text();

      this.isLoading = false;
      this.content = content;
    },

    async reload(platform, event) {
      event.preventDefault()
      await this.fetch(this.$root.$data.api);
    },
  },

  computed: {},

  async created() {
    this.context = JSON.parse(this.$root.$data.context);
    await this.fetch(this.$root.$data.api);
  },
};
</script>
