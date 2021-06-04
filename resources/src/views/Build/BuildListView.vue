<template>
	<div class="container">
		<div v-if="!hideFilter" class="card">
			<div class="card-header text-center">
				<strong>{{$t('buildList.filter')}}</strong>
			</div>
			<div class="card-body">
				<i18next v-if="isFilterActive" class="alert alert-info" path="buildList.filterReset" tag="div">
					<router-link :to="{name: $route.name}" place="link">{{$t('words.here')}}</router-link>
				</i18next>
				<form @submit.prevent="filterSearch">
					<div class="row">
						<div class="col-md-4 col-sm-6">
							<div class="form-group">
								<label for="titleFilter">
									{{$t('build.title')}}
								</label>
								<input id="titleFilter" v-model="filter.title" :placeholder="$t('build.title')" class="form-control" type="text">
							</div>
						</div>
						<div class="col-md-4 col-sm-6">
							<div class="form-group">
								<label for="authorFilter">
									{{$t('build.author')}}
								</label>
								<input id="authorFilter" v-model="filter.author" :placeholder="$t('build.author')" class="form-control" type="text">
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4 col-sm-6">
							<div class="form-group">
								<label for="difficultyFilter">
									{{$t('build.difficulty')}}
								</label>
								<v-select id="difficultyFilter" v-model="filter.difficulty" :options="difficultySelect" :reduce="option => option.value" multiple />
							</div>
						</div>
						<div class="col-md-4 col-sm-6">
							<div class="form-group">
								<label for="gameModeFilter">
									{{$t('build.gameMode')}}
								</label>
								<v-select id="gameModeFilter" v-model="filter.gameMode" :options="gameModeSelect" :reduce="option => option.value" multiple />
							</div>
						</div>
						<div class="col-md-4 col-sm-6">
							<div class="form-group">
								<label for="mapFilter">
									{{$t('build.map')}}
								</label>
								<v-select id="mapFilter" v-model="filter.map" :options="mapSelect" :reduce="option => option.value" multiple />
							</div>
						</div>
					</div>
					<div class="text-center">
						<button id="search" class="btn btn-primary" type="submit">
							{{$t('words.search')}}
						</button>
					</div>
				</form>
			</div>
		</div>

		<template v-if="builds.length">
			<div :class="{marginTop: !hideFilter}" class="table-responsive">
				<table :class="{'table-dark': $store.state.darkMode}" class="table table-hover">
					<thead>
						<tr>
							<th :class="getHeadlineClass('title')">
								<router-link :to="{name: $route.name, query: getSortQuery('title')}">{{$t('build.title')}}</router-link>
							</th>
							<th :class="getHeadlineClass('difficultyID')">
								<router-link :to="{name: $route.name, query: getSortQuery('difficultyID')}">{{$t('build.difficulty')}}</router-link>
							</th>
							<th :class="getHeadlineClass('mapID')">
								<router-link :to="{name: $route.name, query: getSortQuery('mapID')}">{{$t('build.map')}}</router-link>
							</th>
							<th :class="getHeadlineClass('gameModeID')">
								<router-link :to="{name: $route.name, query: getSortQuery('gameModeID')}">{{$t('build.gameMode')}}</router-link>
							</th>
							<th :class="getHeadlineClass('author')">
								<router-link :to="{name: $route.name, query: getSortQuery('author')}">{{$t('build.author')}}</router-link>
							</th>
							<th :class="getHeadlineClass('likes')" class="columnDigits">
								<router-link :to="{name: $route.name, query: getSortQuery('likes')}">{{$t('build.likes')}}</router-link>
							</th>
							<th :class="getHeadlineClass('views')" class="columnDigits">
								<router-link :to="{name: $route.name, query: getSortQuery('views')}">{{$t('build.views')}}</router-link>
							</th>
							<th :class="getHeadlineClass('date')" class="columnDate">
								<router-link :to="{name: $route.name, query: getSortQuery('date')}">{{$t('build.date')}}</router-link>
							</th>
							<th class="text-right">
								<a v-b-tooltip.left.hover="$t('buildList.viewType.' + (viewMode === 'table' ? 'grid' : 'table'))" class="pointer" @click="changeViewMode">
									<i :class="{'fa-th': viewMode === 'table', 'fa-list': viewMode === 'grid'}" class="fa" />
								</a>
							</th>
						</tr>
					</thead>
					<tbody v-if="viewMode === 'table'">
						<tr v-for="build in builds" :key="build.ID">
							<td>
								<span v-if="build.rifted" class="badge badge-success">{{$t('build.rifted')}}</span>
								<span v-if="build.afkAble" class="badge badge-success">{{$t('build.afkAble')}}</span>
								<span v-if="build.hardcore" class="badge badge-success">{{$t('build.hardcore')}}</span>
								<router-link :to="{name: 'build', params: buildLinkParams(build)}">{{build.title}}</router-link>
							</td>
							<td :class="'difficulty-' + build.difficultyID">
								<router-link :to="{name: $route.name, query: buildListSearch({difficulty: build.difficultyName})}">
									{{$t('difficulty.' + build.difficultyName)}}
								</router-link>
							</td>
							<td>
								<router-link :to="{name: $route.name, query: buildListSearch({map: build.mapName})}">{{$t('map.' + build.mapName)}}</router-link>
							</td>
							<td>
								<router-link :to="{name: $route.name, query: buildListSearch({gameMode: build.gameModeName})}">
									{{$t('gameMode.' + build.gameModeName)}}
								</router-link>
							</td>
							<td>
								<router-link :to="{name: $route.name, query: {author: build.author}}">{{build.author}}</router-link>
							</td>
							<td class="columnDigits">{{number(build.likes)}}</td>
							<td class="columnDigits">{{number(build.views)}}</td>
							<td class="columnDate" colspan="2">{{formatDate(build.date)}}</td>
						</tr>
					</tbody>
				</table>
			</div>

			<ol v-if="viewMode === 'grid'" class="buildList">
				<li v-for="build in builds" :key="build.ID">
					<div class="buildBox">
						<i v-if="build.buildStatus !== buildStatusPublic" v-b-tooltip.hover="$t('buildList.isPrivate')"
							class="fa fa-eye-slash buildUnlisted" />
						<div class="box128">
							<div class="buildDataContainer">
								<span v-if="build.rifted" class="badge badge-success">{{$t('build.rifted')}}</span>
								<span v-if="build.afkAble" class="badge badge-success">{{$t('build.afkAble')}}</span>
								<span v-if="build.hardcore" class="badge badge-success">{{$t('build.hardcore')}}</span>
								<h3 class="buildSubject">
									<router-link :to="{name: 'build', params: buildLinkParams(build)}">{{build.title}}</router-link>
								</h3>

								<ul class="inlineList dotSeparated buildMetaData">
									<li><i class="fa fa-user" /> <router-link :to="{name: $route.name, query: {author: build.author}}">{{build.author}}</router-link></li>
									<li><i class="fa fa-clock-o" /> {{formatDate(build.date)}}</li>
									<li><i class="fa fa-eye" /> {{number(build.views)}}</li>
									<li><i class="fa fa-comment-o" /> {{number(build.comments)}}</li>
									<li :class="{'text-success': build.likes > 0}">
										<i class="fa fa-thumbs-o-up" /> {{(build.likes > 0 ? '+' : '') + number(build.likes)}}
									</li>
								</ul>

								<img :src="'/assets/images/thumbnail/' + build.ID + '.png'" class="img-responsive" style="height: 200px;margin: 15px auto auto;">
							</div>
						</div>
						<div class="buildFiller" />
						<div class="buildFooter">
							<ul class="inlineList dotSeparated buildInformation">
								<li>
									<i class="fa fa-map" /> <router-link :to="{name: $route.name, query: buildListSearch({map: build.mapName})}">
										{{$t('map.' + build.mapName)}}
									</router-link>
								</li>
								<li>
									<i class="fa fa-gamepad" /> <router-link :to="{name: $route.name, query: buildListSearch({gameMode: build.gameModeName})}">
										{{$t('gameMode.' + build.gameModeName)}}
									</router-link>
								</li>
								<li :class="'difficulty-' + build.difficultyID">
									<i class="fa fa-tachometer" /> <router-link :to="{name: $route.name, query: buildListSearch({difficulty: build.difficultyName})}">
										{{$t('difficulty.' + build.difficultyName)}}
									</router-link>
								</li>
							</ul>
						</div>
					</div>
				</li>
			</ol>
		</template>
		<div v-else class="alert alert-info">
			{{$t('buildList.noEntries')}}
		</div>

		<app-pagination :current-page="page" :pages="pages" :route-name="$route.name" />
	</div>
</template>

<script>
import axios from 'axios';
import vSelect from 'vue-select';
import AppPagination from '../../components/AppPagination';
import {hidePageLoader, showPageLoader} from '../../store';
import {buildLinkParams, buildListSearch, STATUS_PUBLIC} from '../../utils/build';
import formatDate from '../../utils/date';
import number from '../../utils/math/number';
import {lcfirst} from '../../utils/string';

export default {
	name: 'BuildListView',
	components: {
		AppPagination,
		vSelect,
	},
	props: {
		hideFilter: {
			type: Boolean,
			default: false,
		},
		fetchParams: {
			type: Object,
			default() {
				return {};
			},
		},
	},
	data() {
		return {
			buildStatusPublic: STATUS_PUBLIC,
			builds: [],
			page: 0,
			pages: 0,
			isFilterActive: false,
			filter: this.getDefaultFilter(),
			viewMode: localStorage?.getItem('viewMode.' + this.$route.name) || 'grid',
		};
	},
	computed: {
		gameModeSelect() {
			let select = [];
			for (let name of Object.keys(this.$t('gameMode'))) {
				select.push({ label: this.$t('gameMode.' + name), value: name });
			}

			return select;
		},
		difficultySelect() {
			let select = [];
			for (let name of Object.keys(this.$t('difficulty'))) {
				select.push({ label: this.$t('difficulty.' + name), value: name });
			}

			return select;
		},
		mapSelect() {
			let select = [];
			for (let name of Object.keys(this.$t('map'))) {
				select.push({ label: this.$t('map.' + name), value: name });
			}

			return select;
		},
	},
	watch: {
		$route() {
			this.fetchList();
		},
		viewMode() {
			localStorage?.setItem('viewMode.' + this.$route.name, this.viewMode);
		},
	},
	created() {
		this.fetchList();

		this.$root.$on('updateLanguage', this.onLanguageUpdate);
	},
	destroyed() {
		this.$root.$off('updateLanguage', this.onLanguageUpdate);
	},
	methods: {
		number,
		buildLinkParams,
		buildListSearch,
		formatDate,
		onLanguageUpdate() {
			this.updateMultipleFilters();
		},
		getDefaultFilter() {
			return {
				title: '',
				author: '',
				difficulty: [],
				gameMode: [],
				map: [],
			};
		},
		getHeadlineClass(field) {
			if (this.$route.query.sortField !== field) {
				return {};
			}

			let isDesc = this.$route.query.sortOrder === 'DESC';

			return {
				DESC: isDesc,
				ASC: !isDesc,
			};
		},
		updateMultipleFilters() {
			for (let key of ['gameMode', 'map', 'difficulty']) {
				let list = [];
				for (let value of this.filter[key]) {
					if (typeof value === 'string') {
						list.push({ label: this.$t(key + '.' + value), value: value });
					}
					else {
						list.push({ label: this.$t(key + '.' + value.value), value: value.value });
					}
				}

				this.filter[key] = list;
			}
		},
		updateFilter() {
			this.filter = this.getDefaultFilter();
			for (let key of Object.keys(this.$route.query)) {
				if (typeof this.filter[key] !== 'undefined') {
					if (Array.isArray(this.filter[key])) {
						this.filter[key] = lcfirst(this.$route.query[key].split(','));
					}
					else {
						this.filter[key] = lcfirst(this.$route.query[key]);
					}
				}
			}

			let filterStatus = false;
			for (let key of Object.keys(this.filter)) {
				if (!Array.isArray(this.filter[key]) && this.filter[key] || Array.isArray(this.filter[key]) && this.filter[key].length) {
					filterStatus = true;
					break;
				}
			}

			this.updateMultipleFilters();

			this.isFilterActive = filterStatus;
		},
		getSortQuery(sortField) {
			let queryOptions = {
				sortField,
			};
			let routeQuery = Object.assign({}, this.$route.query);
			if (routeQuery.sortField === sortField) {
				queryOptions.sortOrder = routeQuery.sortOrder === 'DESC' ? 'ASC' : 'DESC';
				delete routeQuery.sortOrder;
				delete routeQuery.sortField;
			}
			queryOptions = Object.assign(routeQuery, queryOptions);

			return queryOptions;
		},
		fetchList() {
			showPageLoader();
			this.updateFilter();

			let queryParams = (new URLSearchParams(Object.assign({}, this.$route.query, this.fetchParams))).toString();
			let page = this.$route.params.page || 0;

			axios
				.get('/builds/?page=' + page + (queryParams ? '&' + queryParams : ''))
				.then(({ data: { data, currentPage, lastPage } }) => {
					this.builds = data;
					this.page = currentPage;
					this.pages = lastPage;
				})
				.finally(hidePageLoader);
		},
		changeViewMode() {
			this.viewMode = this.viewMode === 'grid' ? 'table' : 'grid';
		},
		filterSearch() {
			try {
				this.$router.push({
					name: this.$route.name,
					query: buildListSearch({ ...this.filter }),
				});
			}
			catch (e) {
				// ignore error
			}
		},
	},
};
</script>