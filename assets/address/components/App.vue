<template>
  <div>
    <div class="container">
      <div class="text-center pb-2">
        <a href="#" v-on:click="reload(undefined, $event)" class="farm-loading-refresh"><i class="fas fa-sync"></i></a> -
        <a class="text-muted text-small text-decoration-none">{{ context.address_truncate }}</a>
        -
        <a class="text-muted text-small" target="_blank"
           :href="context.explorer + '/address/' + encodeURIComponent(context.address)"><i class="fas fa-external-link-alt"></i></a>
      </div>
    </div>

    <div class="position-relative" :class="!isLoading ? 'd-none' : ''">
      <div  style="margin-bottom: -30px;" class="position-absolute bottom-0 start-50 translate-middle-x">
        <div class="spinner-border">
          <span class="sr-only">Loading...</span>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <template v-if="wallet && wallet.html">
          <a class="btn btn-outline-dark" data-bs-toggle="modal" data-bs-target="#wallet-modal"><i class="fas fa-wallet" title="Token & Liquidity Pools"></i><span class="d-none d-md-inline"> Wallet</span></a>
        </template>

        <a class="btn btn-outline-dark" :href="`${context.address}/transactions`"><i class="fas fa-exchange-alt"></i><span class="d-none d-md-inline"> Transactions</span></a>
      </div>

      <div class="col-md-auto text-end">
        <div class="d-flex float-end">
          <div class="d-flex ps-2" v-for="s in summary" :key="`summary-${s.key}`">
            <div class="d-flex ps-2">
              <div class="lh-sm pe-1">
                <div class="fw-bold" v-if="s.key === 'total'">{{ s.value }}</div>
                <div v-else>{{ s.value }}</div>
                <div class="text-muted" style="font-size: 0.8em;">{{ s.label }}</div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div v-html="wallet.html"></div>

    <div v-for="platform in getPlatforms" :key="`platform-${platform.id}`" v-html="platform.html"></div>
  </div>
</template>

<script>
import Utils from "../utils/utils";

export default {
  data() {
    return {
      isLoading: true,
      context: {},
      platforms: [],
      wallet: {},
      summary: []
    };
  },

  methods: {
    async fetchChunk(url) {
      let platforms = {};

      const res = await fetch(url);
      platforms = await res.json();

      const state = Object.values(this.platforms);

      for (const [key, platform] of Object.entries(platforms)) {
        const index = state.findIndex(platformOld => {
          if (platformOld.id === key) {
            return true
          }
        })

        if (index >= 0) {
          this.platforms[index] = platform;
        } else {
          this.platforms.push(platform)
        }
      }

      this.calculateSummary();
      this.appendPlatformWalletInfo();
    },

    async fetchWallet() {
      const res = await fetch(this.context.wallet_url);
      this.wallet = await res.json();
    },

    async reload(platform, event) {
      event.preventDefault();

      if (this.isLoading) {
        return;
      }

      await this.fetchData();
    },

    calculateSummary() {
      const summary = {
        rewards: 0,
        wallet: 0,
        liquidityPools : 0,
        vaults: 0,
      };

      for (const key in this.platforms) {
        const platform = this.platforms[key];

        if (platform.usd) {
          summary.vaults += platform.usd
        }

        if (platform.rewards_total) {
          summary.rewards += platform.rewards_total
        }
      }

      for (const key in this.wallet.tokens || []) {
        const item = this.wallet.tokens[key];

        if (item.usd) {
          summary.wallet += item.usd
        }
      }

      for (const key in this.wallet.liquidityPools || []) {
        const item = this.wallet.liquidityPools[key];

        if (item.usd) {
          summary.liquidityPools += item.usd
        }
      }

      summary.total = Object.values(summary).reduce((a, b) => a + b, 0);

      const result = Object.entries(summary).map(row => {
        const [key, value] = row;

        return {
          key: key,
          label: key[0].toUpperCase() + key.slice(1),
          usd: value,
          value: Utils.formatCurrency(value),
        };
      }).filter(s => s.usd > 0).sort((a, b) => a.usd - b.usd);

      this.summary = result;
    },

    appendPlatformWalletInfo() {
      if (!this.wallet.tokens || !this.wallet) {
        return;
      }

      for (const key in this.platforms) {
        const platform = this.platforms[key];

        const token = platform.token;
        if (!token) {
          continue;
        }

        const balance = this.wallet.tokens.find(t => t.symbol.toLowerCase() === token.toLowerCase());
        if (!balance) {
          continue;
        }

        const wrapperElm = document.createElement('div');
        wrapperElm.innerHTML = platform.html;

        let innerHTML = ` <i class="fas fa-wallet"></i> <span style="font-size: 0.75em">${Utils.formatTokenAmount(balance.amount)}</span>`;

        if (balance.usd) {
          innerHTML += ` <span style="font-size: 0.75em">(${Utils.formatCurrency(balance.usd)})</span>`
        }

        wrapperElm.querySelector('.platform-balance').innerHTML = innerHTML;

        platform.html = wrapperElm.innerHTML;
      }
    },

    async fetchData() {
      this.isLoading = true;
      this.platforms = [];
      this.wallet = {};
      this.summary = [];

      const calls = this.context.platform_chunks.map(chunk => () => {
        return this.fetchChunk(chunk)
      });

      calls.push(this.fetchWallet);

      const retry = [];
      (await Promise.allSettled([...calls.map(m => m())])).forEach((p, index) => {
        if (p.status === 'rejected') {
          retry.push(calls[index]);
        }
      });

      if (retry.length > 0) {
        console.log(`http chunk retry: ${retry.length}`);

        await new Promise(r => setTimeout(r, 3000));
        await Promise.allSettled([...calls.map(m => m())]);
      }

      this.calculateSummary();
      this.appendPlatformWalletInfo();

      this.isLoading = false;
    },
  },

  computed: {
    getPlatforms() {
      return Object.values(this.platforms).sort((a, b) => {
        return ((b.usd || 0) + (b.rewards_total || 0)) - ((a.usd || 0) + (a.rewards_total || 0))
      });
    }
  },

  async created() {
    this.context = JSON.parse(this.$root.$data.context);
    await this.fetchData();
  },
};
</script>
