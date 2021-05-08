<template>
  <div>

    <div class="row">
      <div class="col-sm-2">
        <div class="list-group provider-options">
          <a rel="nofollow" href="#" class="list-group-item list-group-item-action" :class="platforms.length === 0 ? 'active' : ''" v-on:click="togglePlatform(undefined, $event)">
            <img loading="lazy" width="32" height="32" src="/assets/rocket.png"> Show All
          </a>

          <template v-for="(row, index) in providers">
            <a rel="nofollow" href="#" class="list-group-item list-group-item-action"  :class="platforms.includes(row.id) ? 'active' : ''" v-on:click="togglePlatform(row.id, $event)" :key="`providers-${index}`" >
              <img loading="lazy" width="32" height="32" :src="row.icon">
              <span class="align-middle">{{ row.label }}</span>
            </a>
          </template>
        </div>
      </div>

      <div class="col-sm-10">
        <div class="pb-3">

          <div class="form-group">
            <input name="text" class="form-control" placeholder="Search a farm or liquidity pool" v-model="searchX">
          </div>
        </div>

        <div class="card shadow-sm mb-2 pool-card">
          <div class="card-body">
            <div class="row">
              <div class="col-3">
                <a v-on:click="toggleSort('name', $event)" href="#" class="text-dark">
                  Name
                  <i v-if="sort.startsWith('name_')" :class="[sort ==='name_asc' ? 'fas fa-arrow-down' : 'fas fa-arrow-up' ]"></i>
                </a>
              </div>

              <div class="col-3 text-center">
                <a v-on:click="toggleSort('tvl', $event)" href="#" class="text-dark">
                  TVL
                  <i v-if="sort.startsWith('tvl_')" :class="[sort ==='tvl_asc' ? 'fas fa-arrow-down' : 'fas fa-arrow-up' ]"></i>
                </a>
              </div>

              <div class="col-2 text-center">
                <i class="far fa-question-circle" title="Yearly APY / Daily APR"></i>
                <a v-on:click="toggleSort('yield', $event)" href="#" class="text-dark">
                  Yield
                  <i v-if="sort.startsWith('yield_')" :class="[sort ==='yield_asc' ? 'fas fa-arrow-down' : 'fas fa-arrow-up' ]"></i>
                </a>
              </div>

              <div class="col-2 text-center">
                <a v-on:click="toggleSort('earns', $event)" href="#" class="text-dark">
                  Earns
                  <i v-if="sort.startsWith('earns_')" :class="[sort ==='earns_asc' ? 'fas fa-arrow-down' : 'fas fa-arrow-up' ]"></i>
                </a>
              </div>

              <div class="col-2 text-end">
                <a v-on:click="toggleSort('platform', $event)" href="#" class="text-dark">
                  Platform
                  <i v-if="sort.startsWith('platform_')" :class="[sort ==='platform_asc' ? 'fas fa-arrow-down' : 'fas fa-arrow-up' ]"></i>
                </a>
              </div>
            </div>
          </div>
        </div>

        <div v-for="(farm, index) in filteredRows" :key="`farm-${index}`" v-html="farm.content">
        </div>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  throttleSearchInput: undefined,

  data() {
    return {
      rows: [],
      search: '',
      searchX: '',
      sort: 'tvl_asc',
      cars: [],
      platforms: [],
      providers: [],
    };
  },

  watch: {
    searchX: function (val) {
      if (this.throttleSearchInput) {
        clearTimeout(this.throttleSearchInput)
        this.throttleSearchInput = undefined;
      }

      this.throttleSearchInput = setTimeout(() => {
        this.search = val;
      }, 500)
    },
  },

  methods: {
    togglePlatform(platform, event) {
      event.preventDefault()

      if (!platform) {
        this.platforms = [];
        return;
      }

      if (this.platforms.includes(platform)) {
        this.platforms = this.platforms.filter(item => item !== platform)
      } else {
        this.platforms.push(platform);
      }
    },

    toggleSort(field, event) {
      event.preventDefault()

      let [sort, direction] = this.sort.split('_');

      if (!sort) {
        this.sort = field + '_asc'
        return;
      }

      if (sort !== field) {
        this.sort = field + '_asc'
        return;
      }

      if (direction === 'asc') {
        this.sort = field + '_desc'
        return;
      }

      this.sort = ''
    },

    async fetch(url) {
      const res = await fetch(url);
      let newVar = await res.json();

      this.providers = newVar['platforms'];
      this.rows = newVar['farms'];
    }
  },

  computed: {
    filteredRows() {
      const searchTerm = this.search.toLowerCase();

      let rows = this.rows.filter(row => {
        let search = true
        if (searchTerm) {
          let s = row.name ? row.name.toLowerCase() : '';
          search = s.includes(searchTerm)
        }

        let platforms = true
        if (this.platforms.length > 0) {
          platforms = this.platforms.includes(row.platform)
        }

        return search && platforms;
      });

      if (this.sort) {
        let [sort, direction] = this.sort.split('_');

        if (!direction) {
          direction = 'asc'
        }

        let isNumberCompare = ['tvl', 'yield'].includes(sort);
        let isCount = ['earns'].includes(sort);

        rows.sort((a, b) => {
          let a1 = direction === 'asc' ? a[sort] : b[sort];
          let b1 = direction === 'asc' ? b[sort] : a[sort];

          if (isCount) {
            return (b1 || []).length - (a1 || []).length
          }

          if (isNumberCompare) {
            return (b1 || -1) - (a1 || -1)
          }

          return (a1 || '').toLowerCase().localeCompare((b1 || '').toLowerCase());
        })
      }

      return Object.freeze(rows);
    }
  },

  async created() {
    const preload = JSON.parse(this.$root.$data.preload)
    this.providers = preload['platforms'];
    this.rows = preload['farms'];

    await this.fetch(this.$root.$data.api);
  },
};
</script>
