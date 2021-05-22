import axios from 'axios';
import {NavbarPlugin, TooltipPlugin} from 'bootstrap-vue';
import Vue from 'vue';
import InfiniteLoading from 'vue-infinite-loading';
import Notifications from 'vue-notification';
import {addXmlHttpRequest} from '../../vendor/derpierre65/laravel-debug-bar/resources/js/LaravelDebugBar';
import App from './App.vue';
import acceptanceTest from './directives/acceptanceTest';
import i18n, {initI18n} from './i18n';
import router from './router';
import store from './store';

Vue.config.productionTip = false;

require('webpack-jquery-ui/draggable');
require('webpack-jquery-ui/droppable');

axios.defaults.baseURL = '/api/';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

if (process.env.NODE_ENV !== 'production') {
	axios.interceptors.request.use((request) => {
		request.meta = request.meta || {};
		request.meta.requestStart = Date.now();

		return request;
	});

	axios.interceptors.response.use((response) => {
		addXmlHttpRequest({
			status: response.status,
			method: response.config.method.toUpperCase(),
			url: response.request.responseURL,
			headers: response.headers,
			time: Date.now() - response.config.meta.requestStart,
		});

		return response;
	});
}

Vue.use(NavbarPlugin);
Vue.use(TooltipPlugin);
Vue.use(Notifications);
Vue.use(InfiniteLoading, {
	slots: {
		noResults: {
			render(h) {
				return h('div', { attrs: { class: 'alert alert-info' } }, this.$t(this.$parent.$attrs.type + '.noResults'));
			},
		},
		noMore: {
			render(h) {
				return h('div', { attrs: { class: 'alert alert-info' } }, this.$t(this.$parent.$attrs.type + '.noMore'));
			},
		},
		error: {
			render(h) {
				return h('div', { attrs: { class: 'alert alert-danger' } }, this.$t('infiniteLoading.error'));
			},
		},
	},
});

// directives
Vue.use(acceptanceTest);

initI18n(() => {
	new Vue({
		el: '#app',
		i18n,
		router,
		store,
		render: h => h(App),
	});
});