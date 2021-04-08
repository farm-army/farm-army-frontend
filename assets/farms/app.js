import Vue from 'vue';
import App from './components/App';

let element = document.querySelector('#app-farms');

new Vue({
    el: '#app-farms',
    render: h => h(App),
    data: {
        api: element.getAttribute('data-api'),
        preload: element.getAttribute('data-preload'),
    },
});

