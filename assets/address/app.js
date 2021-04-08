import Vue from 'vue';
import App from './components/App';

let element = document.querySelector('#app-address');

new Vue({
    el: '#app-address',
    render: h => h(App),
    data: {
        api: element.getAttribute('data-url'),
        context: element.getAttribute('data-context'),
    },
});

