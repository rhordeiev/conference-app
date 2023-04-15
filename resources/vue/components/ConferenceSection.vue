<template>
    <v-row class="w-100">
        <v-col cols="12" sm="12" :md="conferencesColsSize" :lg="conferencesColsSize" :xl="conferencesColsSize"
               class="d-flex flex-column align-center order-last order-sm-last order-md-first order-lg-first
                order-xl-first"
               v-if="isLoading || applyFilterLoading">
            <v-progress-circular color="surface-variant" indeterminate class="my-3"></v-progress-circular>
        </v-col>
        <v-col cols="12" sm="12" :md="conferencesColsSize" :lg="conferencesColsSize" :xl="conferencesColsSize"
               class="d-flex flex-column align-center order-last order-sm-last order-md-first order-lg-first
                order-xl-first h-fit"
               v-else>
            <v-row
                :class="[conferencesWidthClass, 'elevation-2', 'rounded', 'my-3', 'bg-white', 'd-flex', 'align-center', 'h-fit']"
                v-for="conference in conferences"
                :key="conference.id"
            >
                <v-col cols="12" md="4" lg="5" sm="12" class="d-flex flex-column justify-center align-center h-fit">
                    <div class="text-grey text-caption">Title</div>
                    <div class="text-subtitle-1 text-center">{{ conference.title }}</div>
                </v-col>
                <v-col cols="12" md="4" lg="5" sm="12" class="d-flex flex-column justify-center align-center h-fit">
                    <div class="text-grey text-caption">Date</div>
                    <div class="text-subtitle-1">{{ conference.date.split('T')[0] }}</div>
                </v-col>
                <v-col cols="12" md="4" lg="2" sm="12"
                       class="bg-grey-darken-3 d-flex flex-column align-center rounded-te rounded-be h-fit">
                    <RouterLink :to="`/conference/${conference.id}`"
                                class="text-decoration-none ma-1 w-100">
                        <v-btn small color="normal" class="text-black w-100">DETAILS</v-btn>
                    </RouterLink>
                    <v-btn small color="success" class="ma-1 w-100"
                           v-if="attendanceAvailabilityCheck && !conference.joined"
                           :loading="isConferencesLoading[conference.id]"
                           :id="conference.id"
                           @click="onJoin($event)">JOIN
                    </v-btn>
                    <v-btn small color="warning" class="ma-1 w-100"
                           v-else-if="attendanceAvailabilityCheck && conference.joined"
                           :loading="isConferencesLoading[conference.id]"
                           @click="onCancel($event)"
                           :id="conference.id">
                        CANCEL
                    </v-btn>
                    <a :href="`${twitterUrl}${url}conference/${conference.id}`"
                       target="_blank" class="text-decoration-none text-white ma-1 w-100"
                       v-if="attendanceAvailabilityCheck && conference.joined">
                        <v-btn small color="#1DA1F2" class="w-100">TWITTER</v-btn>
                    </a>
                    <a :href="`${facebookUrl}${url}conference/${conference.id}`"
                       target="_blank" class="text-decoration-none text-white ma-1 w-100"
                       v-if="attendanceAvailabilityCheck && conference.joined">
                        <v-btn small color="#4267B2" class="w-100">FACEBOOK</v-btn>
                    </a>
                    <v-btn small color="warning" class="ma-1 w-100" v-if="$store.state.user?.type === 'admin'"
                           :id="conference.id"
                           @click="onCategoryClick($event)">CATEGORY
                    </v-btn>
                    <v-btn small color="error" class="ma-1 w-100" v-if="deleteAvailabilityCheck(conference)"
                           :id="conference.id"
                           @click="onDeleteClick($event)">DELETE
                    </v-btn>
                </v-col>
            </v-row>
        </v-col>
        <v-col cols="12" sm="12" md="3" lg="3" xl="3" class="d-flex justify-center align-center flex-column h-fit"
               v-if="filterShowCheck">
            <v-expansion-panels v-model="panel" :class="[filterWidthClass, 'mt-3']">
                <v-expansion-panel>
                    <v-expansion-panel-title class="text-center">
                        <span class="text-h6">Filter</span>
                    </v-expansion-panel-title>
                    <v-expansion-panel-text>
                        <v-row>
                            <v-col cols="12">
                                <span class="text-subtitle-2 text-grey">Report count</span>
                                <v-slider class="px-3 pt-8 pb-0" v-model="filterReportCount" min="0"
                                          :max="maxFilterReportCount"
                                          step="1"
                                          thumb-label="always"
                                          hide-details
                                          @change="onFilterChange"
                                          :disabled="applyFilterLoading || !reportCountActive"></v-slider>
                                <v-checkbox
                                    v-model="reportCountActive"
                                    label="Report count"
                                    hide-details
                                ></v-checkbox>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field v-model="filterFromDate" type="date" label="From date" clearable
                                              hide-details @change="onFilterChange" :loading="applyFilterLoading"
                                              :disabled="applyFilterLoading"></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field v-model="filterToDate" type="date" label="To date" clearable
                                              hide-details @change="onFilterChange" :loading="applyFilterLoading"
                                              :disabled="applyFilterLoading"></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-combobox
                                    v-model="filterChosenCategories"
                                    :items="filterCategories"
                                    item-value="id"
                                    item-title="name"
                                    label="Categories"
                                    :loading="filterCategoriesLoading || applyFilterLoading"
                                    :disabled="applyFilterLoading"
                                    multiple
                                    chips
                                    clearable
                                    hide-details
                                    @change="onFilterChange"
                                ></v-combobox>
                            </v-col>
                            <v-col cols="12" class="text-center">
                                <v-btn variant="text" color="primary" @click="onFilterReset"
                                       :loading="applyFilterLoading || filterCategoriesLoading || filterReportCountLoading">
                                    Reset
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-expansion-panel-text>
                </v-expansion-panel>
            </v-expansion-panels>
            <div class="w-100 ma-3 d-flex justify-center" v-if="user?.type === 'admin'">
                <v-btn color="teal" @click="exportConferences"
                       :loading="exportLoading || applyFilterLoading || isLoading">
                    Export
                </v-btn>
                <csv-export-snackbar :loading="exportLoading" :progress="progress"/>
            </div>
        </v-col>
    </v-row>
    <ConfirmDialog type="delete" :active="confirmDeleteDialog" header="Removal confirmation"
                   message="Do you really want to delete?" :is-loading="isDeleteLoading"
                   @confirm="onDeleteConfirm" @close="confirmDeleteDialog = false"/>
    <CategoryDialog :conference-id="chosenConferenceId" type="conference" :active="confirmCategoryDialog"
                    @close="onCategoryClose"/>
    <v-dialog
        v-model="confirmJoinDialog"
        persistent
        max-width="600px"
    >
        <Report :chosen-conference-id="chosenConferenceId" @confirm="closeJoinDialog" @close="closeJoinDialog"/>
    </v-dialog>
</template>

<script>
import ConfirmDialog from "@/components/dialog/ConfirmDialog.vue";
import Report from "@/components/Report.vue";
import CategoryDialog from "@/components/dialog/CategoryDialog.vue";
import {api} from "@/api";
import downloadCsvFile from "@/helpers/downloadCsvFile";
import CsvExportSnackbar from "@/components/CsvExportSnackbar.vue";

export default {
    name: "ConferenceSection",
    components: {CsvExportSnackbar, CategoryDialog, Report, ConfirmDialog},
    data() {
        return {
            isConferencesLoading: [],
            isDeleteLoading: false,
            confirmDeleteDialog: false,
            confirmCategoryDialog: false,
            conferenceIdToDelete: null,
            url: window.location.href,
            confirmJoinDialog: false,
            chosenConferenceId: null,
            twitterUrl: import.meta.env.VITE_TWITTER_SHARE_URL,
            facebookUrl: import.meta.env.VITE_FACEBOOK_SHARE_URL,
            filterReportCount: null,
            filterFromDate: null,
            filterToDate: null,
            filterCategories: [],
            filterChosenCategories: [],
            applyFilterLoading: false,
            filterCategoriesLoading: false,
            filterReportCountLoading: false,
            maxFilterReportCount: 0,
            progress: 0,
            exportLoading: false,
            panel: [],
            timer: null,
            reportCountActive: false,
            filterActivated: false,
            conferencesReset: false
        }
    },
    props: {
        conferences: Array,
        isLoading: Boolean,
    },
    computed: {
        breakpoint() {
            return this.$vuetify.display.name;
        },
        attendanceAvailabilityCheck() {
            const loggedInUser = this.$store.state.user;
            return loggedInUser?.type !== 'announcer' && loggedInUser?.type !== 'admin' && loggedInUser?.type !== 'listener'
        },
        user() {
            return this.$store.state.user;
        },
        conferencesWidthClass() {
            if (this.breakpoint === 'xs') {
                return 'w-100';
            } else if (!this.filterShowCheck) {
                return 'w-50';
            } else {
                return 'w-75';
            }
        },
        filterWidthClass() {
            if (this.breakpoint === 'sm') {
                return 'w-75';
            } else {
                return 'w-100';
            }
        },
        conferencesColsSize() {
            if (!this.filterShowCheck) {
                return 12;
            } else {
                return 9;
            }
        },
        filterShowCheck() {
            return this.user && this.$route.name === 'Home';
        },
        filterData() {
            return {
                reportCount: this.filterReportCount,
                fromDate: this.filterFromDate,
                toDate: this.filterToDate,
                categories: this.filterChosenCategories.map(category => category.id)
            };
        }
    },
    methods: {
        onJoin(event) {
            const loggedInUser = this.$store.state.user;
            if (!loggedInUser) {
                return this.$router.push('/register');
            }
            this.chosenConferenceId = event.currentTarget.id;
            this.confirmJoinDialog = true;
        },
        async onCancel(event) {
            const clickedConferenceId = parseInt(event.currentTarget.id);
            this.isConferencesLoading[clickedConferenceId] = true;
            await api.report.deletePresentation(clickedConferenceId, this.user.id);
            await this.$store.dispatch('deleteReport', {
                conferenceId: clickedConferenceId,
                creatorId: this.user.id,
            });
            await this.$store.dispatch('cancelConference', {
                conferenceId: clickedConferenceId,
                userId: this.user.id,
                withoutCommit: this.user.type === 'admin'
            });
            this.isConferencesLoading[clickedConferenceId] = false;
        },
        onDeleteClick(event) {
            this.conferenceIdToDelete = parseInt(event.currentTarget.id);
            this.confirmDeleteDialog = true;
        },
        onDeleteConfirm() {
            this.isDeleteLoading = true;
            this.$store.dispatch('deleteConference', {
                id: this.conferenceIdToDelete
            }).then(() => {
                this.conferenceIdToDelete = null;
                this.isDeleteLoading = false;
                this.confirmDeleteDialog = false;
                this.$router.push('/');
            })
        },
        onCategoryClick(event) {
            this.chosenConferenceId = parseInt(event.currentTarget.id);
            this.confirmCategoryDialog = true;
        },
        onCategoryClose() {
            this.confirmCategoryDialog = false;
        },
        deleteAvailabilityCheck(clickedConference) {
            const loggedInUser = this.$store.state.user;
            return loggedInUser?.type === 'admin' || (loggedInUser?.type === 'announcer' && clickedConference?.creator);
        },
        setLoading() {
            if (this?.conferences) {
                if (this.isLoading && this.user) {
                    this.conferences.forEach((conference) => {
                        this.isConferencesLoading[conference.id] = true;
                    })
                } else if (!this.isLoading && this.user) {
                    this.conferences.forEach((conference) => {
                        this.isConferencesLoading[conference.id] = false;
                    });
                    this.maxFilterReportCount = this.conferences.reduce((previousValue, currentValue) => {
                        return previousValue + currentValue.reportCount;
                    }, 0)
                }
            }
        },
        closeJoinDialog() {
            this.confirmJoinDialog = false;
        },
        async applyFilter() {
            this.applyFilterLoading = true;
            this.filterActivated = !this.conferencesReset;
            const filterOptions = {
                data: this.filterData,
                active: this.filterActivated
            };
            await this.$store.dispatch('resetConferences', filterOptions);
            this.conferencesReset = false;
            this.applyFilterLoading = false;
        },
        onFilterReset() {
            this.filterReportCount = null;
            this.filterFromDate = null;
            this.filterToDate = null;
            this.filterChosenCategories = [];
            this.conferencesReset = true;
        },
        async onFilterChange() {
            clearTimeout(this.timer);
            this.timer = setTimeout(this.applyFilter, 1000);
        },
        async exportConferences() {
            this.progress = 0;
            this.exportLoading = true;
            let filterData;
            if (this.filterActivated) {
                filterData = this.filterData;
                const dateNow = new Date(Date.now());
                filterData.dateNow = dateNow.toISOString().split('.')[0];
            }
            const csvContent = await api.conference.exportConferences(filterData);
            downloadCsvFile(csvContent, 'conferences.csv');
            this.exportLoading = false;
        }
    },
    watch: {
        isLoading() {
            this.setLoading();
        },
        filterChosenCategories() {
            this.onFilterChange();
        },
        filterReportCount() {
            this.onFilterChange();
        },
        reportCountActive() {
            if (!this.reportCountActive) {
                this.filterReportCount = null;
            }
        },
    },
    async mounted() {
        if (this.user?.type === 'admin') {
            Echo.channel(`csv-export-progress-changed-conferences-${this.user?.id}`)
                .listen('CsvExportProgressChanged', (result) => {
                    this.progress = result.message;
                });
        }
        this.setLoading();
        if (this.user) {
            this.filterCategoriesLoading = true;
            this.filterCategories = await api.category.getCategories();
            this.filterCategoriesLoading = false;
        }
    }
}
</script>

<style scoped>
.h-fit {
    height: fit-content;
}
</style>
