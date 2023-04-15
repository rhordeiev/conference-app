<template>
    <v-app class="bg-grey-lighten-5">
        <v-app-bar app color="surface-variant">
            <v-toolbar-title>
                <RouterLink to="/" class="text-decoration-none text-white text-h5 font-weight-bold">
                    Conferences
                </RouterLink>
            </v-toolbar-title>
            <v-spacer></v-spacer>
            <div class="h-100 w-25 d-flex align-center">
                <v-text-field type="search"
                              color="bg-grey-lighten-5"
                              density="compact"
                              variant="solo"
                              label="Search"
                              single-line
                              hide-details
                              @click="searchActive = true"
                ></v-text-field>
            </div>
            <v-dialog v-model="searchActive" width="600">
                <v-row class="d-flex w-100 bg-white rounded">
                    <v-col cols="12" class="h-fit">
                        <v-text-field type="search"
                                      variant="outlined"
                                      label="Search"
                                      single-line
                                      hide-details
                                      v-model="searchValue"
                                      @input="onSearchInput"
                        ></v-text-field>
                    </v-col>
                    <v-col cols="8" class="align-self-start">
                        <span v-show="showConferences" class="text-subtitle-2 text-grey">Conferences</span>
                        <div class="w-100 d-flex justify-center" v-if="conferencesLoading">
                            <v-progress-circular color="surface-variant" indeterminate></v-progress-circular>
                        </div>
                        <div class="d-flex align-center py-1" v-else v-for="conference in foundConferences">
                            <div
                                class="me-3 text-white d-flex align-center justify-center rounded search-type-icon
                                conference-type-icon">
                                C
                            </div>
                            <a :href="`/conference/${conference.id}`" target="_blank"
                               class="text-decoration-none text-shades-black">
                                {{ conference.title }}
                            </a>
                        </div>
                        <span v-show="showReports" class="text-subtitle-2 text-grey">Reports</span>
                        <div class="w-100 d-flex justify-center" v-if="reportsLoading">
                            <v-progress-circular color="surface-variant" indeterminate></v-progress-circular>
                        </div>
                        <div class="d-flex align-center py-1" v-else v-for="report in foundReports">
                            <div class="me-3 text-white d-flex align-center justify-center rounded search-type-icon
                            report-type-icon">
                                R
                            </div>
                            <a :href="`/report/${report.id}`" target="_blank"
                               class="text-decoration-none text-shades-black">
                                {{ report.topic }}
                            </a>
                        </div>
                    </v-col>
                    <v-col cols="4" class="align-self-start">
                        <span class="text-subtitle-2 text-grey">Filter by</span>
                        <v-radio-group color="surface-variant" v-model="filterBy">
                            <v-radio value="conference">
                                <template v-slot:label>
                                    <span class="text-surface-variant">Conference</span>
                                </template>
                            </v-radio>
                            <v-radio value="report">
                                <template v-slot:label>
                                    <span class="text-surface-variant">Report</span>
                                </template>
                            </v-radio>
                        </v-radio-group>
                        <div class="text-center">
                            <v-btn color="grey" variant="text" @click="resetSearchFilters">Reset</v-btn>
                        </div>
                    </v-col>
                </v-row>
            </v-dialog>
            <v-spacer></v-spacer>
            <v-btn-toggle class="h-100 rounded-0" v-if="!loggedIn">
                <RouterLink to="/login" class="text-decoration-none text-white">
                    <v-btn variant="text" class="h-100">
                        LOGIN
                    </v-btn>
                </RouterLink>
                <RouterLink to="/register" class="text-decoration-none text-white">
                    <v-btn variant="text" class="h-100">
                        REGISTER
                    </v-btn>
                </RouterLink>
            </v-btn-toggle>
            <RouterLink to="/reports" class="text-decoration-none text-white h-100" v-if="loggedIn">
                <v-btn variant="text" class="h-100">
                    REPORTS
                </v-btn>
            </RouterLink>
            <RouterLink to="/categories" class="text-decoration-none text-white h-100"
                        v-if="loggedIn && user.type === 'admin'">
                <v-btn variant="text" class="h-100">
                    CATEGORIES
                </v-btn>
            </RouterLink>
            <RouterLink to="/meetings" class="text-decoration-none text-white h-100"
                        v-if="loggedIn && user.type === 'admin'">
                <v-btn variant="text" class="h-100">
                    MEETINGS
                </v-btn>
            </RouterLink>
            <RouterLink to="/plans" class="text-decoration-none text-white h-100"
                        v-if="loggedIn && user.type === 'member'">
                <v-btn variant="text" class="h-100">
                    PLANS
                </v-btn>
            </RouterLink>
            <v-menu transition="slide-y-transition" v-if="loggedIn">
                <template v-slot:activator="{ props }">
                    <v-btn variant="text" class="h-100 rounded-0" :loading="isLogoutLoading" v-bind="props">
                        <v-chip
                            class="ma-2"
                            color="warning"
                            label
                            v-if="loggedIn"
                        >
                            {{ firstname }} {{ lastname }}
                        </v-chip>
                    </v-btn>
                </template>
                <v-list>
                    <v-list-item
                        link>
                        <RouterLink to="/profile/edit" class="text-decoration-none text-black">
                            <v-list-item-title>
                                EDIT
                            </v-list-item-title>
                        </RouterLink>
                    </v-list-item>
                    <v-list-item
                        link
                    >
                        <v-list-item-title @click="logout">
                            LOGOUT
                        </v-list-item-title>
                    </v-list-item>
                </v-list>
            </v-menu>
            <RouterLink to="/favorites" class="text-decoration-none text-white h-100"
                        v-if="loggedIn && !favoritesLoading">
                <v-btn variant="text" class="h-100">
                    <v-badge :content="favoritesCount.toString()" color="warning" v-if="favoritesCount > 0">
                        <v-icon size="35" color="pink">mdi-heart</v-icon>
                    </v-badge>
                    <v-icon size="35" color="grey" v-else>mdi-heart</v-icon>
                </v-btn>
            </RouterLink>
            <v-progress-circular color="pink" v-if="loggedIn && favoritesLoading" indeterminate
                                 width="3" size="25" class="me-3"></v-progress-circular>
        </v-app-bar>
        <v-main class="d-flex align-center flex-column">
            <slot/>
        </v-main>
    </v-app>
</template>

<script>

import {api} from "@/api";

export default {
    name: 'DefaultLayout',
    data() {
        return {
            isLogoutLoading: false,
            favoritesLoading: false,
            filterBy: null,
            searchValue: null,
            showConferences: false,
            showReports: false,
            conferencesLoading: false,
            reportsLoading: false,
            foundConferences: [],
            foundReports: [],
            timer: null,
            searchActive: false
        }
    },
    methods: {
        logout() {
            this.isLogoutLoading = true;
            this.$store.dispatch('logoutUser').then(() => {
                this.isLogoutLoading = false;
                window.location.reload();
            });
        },
        resetSearchFilters() {
            this.filterBy = null;
        },
        filterRelevantConferences() {
            this.foundConferences = this.foundConferences.filter(conference => {
                const conferenceEndDatetime = new Date(`${conference.date}T${conference.end_time}`);
                return Date.now() < conferenceEndDatetime;
            });
        },
        filterRelevantReports() {
            this.foundReports = this.foundReports.filter(report => {
                const reportEndDatetime = new Date(`${report.date}T${report.end_time}`);
                return Date.now() < reportEndDatetime;
            });
        },
        async search() {
            this.showConferences = false;
            this.showReports = false;
            this.foundConferences = [];
            this.foundReports = [];
            if (!this.searchValue.trim()) {
                return;
            }
            if (this.filterBy === 'conference') {
                this.showConferences = true;
                this.conferencesLoading = true;
                this.foundConferences = await api.conference.search({
                    'title': this.searchValue
                });
                this.filterRelevantConferences();
                this.conferencesLoading = false;
            }
            if (this.filterBy === 'report') {
                this.showReports = true;
                this.reportsLoading = true;
                this.foundReports = await api.report.search({
                    'topic': this.searchValue
                });
                this.filterRelevantReports();
                this.reportsLoading = false;
            }
            if (!this.filterBy) {
                this.showConferences = true;
                this.showReports = true;
                this.conferencesLoading = true;
                this.reportsLoading = true;
                api.conference.search({
                    'title': this.searchValue
                }).then((result) => {
                    this.foundConferences = result;
                    if(this.foundConferences.length === 0) {
                        this.showConferences = false;
                    }
                    this.filterRelevantConferences();
                    this.conferencesLoading = false;
                });
                api.report.search({
                    'topic': this.searchValue
                }).then((result) => {
                    this.foundReports = result;
                    if(this.foundReports.length === 0) {
                        this.showReports = false;
                    }
                    this.filterRelevantReports();
                    this.reportsLoading = false;
                });
            }
        },
        async onSearchInput() {
            clearTimeout(this.timer);
            this.timer = setTimeout(this.search, 500);
        }
    },
    computed: {
        firstname() {
            return this.$store.state.user.firstname;
        },
        lastname() {
            return this.$store.state.user.lastname;
        },
        loggedIn() {
            return !!this.$store.state.user;
        },
        user() {
            return this.$store.state.user;
        },
        favoritesCount() {
            return this.$store.state.favoritesCount;
        }
    },
    async mounted() {
        this.isLogoutLoading = true;
        this.favoritesLoading = true;
        try {
            if (this.$store.state.user) {
                await api.user.hasToken();
                await this.$store.dispatch('setFavoritesCount');
            }
        } catch (e) {
            this.$store.commit('unsetUser');
            window.location.reload();
        }
        this.favoritesLoading = false;
        this.isLogoutLoading = false;
    }
}
</script>

<style scoped>
.w-120 {
    width: 120%;
}

.search-type-icon {
    width: 30px;
    height: 30px;
}

.conference-type-icon {
    background-color: #55a1e0;
}

.report-type-icon {
    background-color: #e05555;
}

.text-surface-variant {
    color: rgb(66, 66, 66);
}

.h-fit {
    height: fit-content;
}
</style>
