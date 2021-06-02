import VueI18Next from '@derpierre65/vue-i18next';
import axios from 'axios';
import i18next from 'i18next';
import i18nextHttpBackend from 'i18next-http-backend';
import Vue from 'vue';

const supportedLanguages = window.APP.supportedLocales;
let isReady = false;
let onReadyCallback;

function getBrowserLanguage() {
	if (localStorage && localStorage.getItem) {
		let language = localStorage.getItem('language');
		if (language) {
			return language;
		}
	}

	let lang = navigator.language;
	if (!supportedLanguages.includes(navigator.language)) {
		lang = navigator.language.substr(0, 2).toLowerCase();
	}

	if (!supportedLanguages.includes(lang)) {
		lang = 'en';
	}

	return lang;
}

function initI18n(callback) {
	if (isReady) {
		callback();
	}
	else {
		onReadyCallback = callback;
	}
}

Vue.prototype.$changeLanguage = function (newLanguage) {
	return i18next.changeLanguage(newLanguage).then((done) => {
		axios.defaults.headers.common['Accept-Language'] = newLanguage;
		document.querySelector('html').setAttribute('lang', newLanguage);

		// save language in local storage
		if (localStorage && localStorage.setItem) {
			localStorage.setItem('language', newLanguage);
		}

		this.$root.$emit('updateLanguage');

		return done;
	});
};

Vue.use(VueI18Next);

i18next.use(i18nextHttpBackend);
i18next.init({
	supportedLngs: supportedLanguages,
	fallbackLng: false,
	returnObjects: true,
	lng: getBrowserLanguage(),
	backend: {
		loadPath: '/assets/locales/{{lng}}.json',
		addPath: '/assets/locales/{{lng}}.json',
	},
	interpolation: {
		format(value, format) {
			if (format === 'bold') {
				return '<strong>' + value + '</strong>';
			}

			return value;
		},
	},
}, function (err) {
	if (err) {
		console.error(err);
	}
	axios.defaults.headers.common['Accept-Language'] = i18next.language;

	isReady = true;
	if (typeof onReadyCallback !== 'undefined') {
		onReadyCallback();
	}
});

const i18n = new VueI18Next(i18next);

export {
	i18n as default,
	supportedLanguages,
	initI18n,
	getBrowserLanguage,
};