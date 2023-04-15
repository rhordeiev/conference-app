<template>
    <v-row class="w-100">
        <v-col cols="12" sm="12" :md="reportsColsSize" :lg="reportsColsSize" :xl="reportsColsSize"
               class="d-flex flex-column align-center order-last order-sm-last order-md-first order-lg-first
                order-xl-first"
               v-if="isLoading || applyFilterLoading">
            <v-progress-circular color="surface-variant" indeterminate class="my-3"></v-progress-circular>
        </v-col>
        <v-col cols="12" sm="12" :md="reportsColsSize" :lg="reportsColsSize" :xl="reportsColsSize"
               class="d-flex justify-xl-space-around justify-lg-space-around justify-md-space-around
               justify-sm-center justify-center flex-wrap order-last order-sm-last order-md-first order-lg-first
               order-xl-first h-fit"
               v-else>
            <div :class="[reportsBlockWidthClass, 'd-flex', 'justify-center']"
                 v-for="report in reports"
                 :key="report.id">
                <v-row
                    :class="[reportWidthClass, 'elevation-2', 'rounded', 'ma-3', 'bg-white', 'd-flex', 'align-center', 'h-fit']"
                >
                    <v-col cols="12" lg="12" sm="12" class="d-flex flex-column justify-center align-center h-fit"
                           v-if="reportWaitingStatus[report.id]">
                        <v-chip
                            :color="reportWaitingStatus[report.id].color"
                            label
                        >
                            {{ reportWaitingStatus[report.id].status }}
                        </v-chip>
                    </v-col>
                    <v-col cols="12" lg="12" sm="12" class="d-flex flex-column justify-center align-center h-fit">
                        <div class="text-grey text-caption">Topic</div>
                        <div class="text-subtitle-1">{{ report.topic }}</div>
                    </v-col>
                    <v-col cols="12" lg="6" sm="12" class="d-flex flex-column justify-center align-center h-fit">
                        <div class="text-grey text-caption">Start</div>
                        <div class="text-subtitle-1">{{ report.start_time.split(':').slice(0, 2).join(':') }}</div>
                    </v-col>
                    <v-col cols="12" lg="6" sm="12" class="d-flex flex-column justify-center align-center h-fit">
                        <div class="text-grey text-caption">End</div>
                        <div class="text-subtitle-1">{{ report.end_time.split(':').slice(0, 2).join(':') }}</div>
                    </v-col>
                    <v-col cols="12" lg="12" sm="12" class="d-flex flex-column justify-center align-center h-fit">
                        <div class="text-grey text-caption">Description</div>
                        <div class="text-subtitle-1">{{ report.description }}</div>
                    </v-col>
                    <v-col cols="12" lg="12" sm="12"
                           class="bg-grey-darken-3 d-flex flex-column align-center rounded-te rounded-be h-fit">
                        <RouterLink :to="`/report/${report.id}`"
                                    class="text-decoration-none ma-1 w-100">
                            <v-btn small color="normal" class="text-black w-100">DETAILS</v-btn>
                        </RouterLink>
                        <v-btn small color="pink" class="ma-1 w-100" v-if="user && !report.favorite"
                               :id="report.id"
                               :loading="isReportsLoading[report.id] || isFavoriteLoading[report.id]"
                               @click="onAddToFavoriteClick(report.id)">
                            ADD TO FAVORITE
                        </v-btn>
                        <v-btn small color="grey" class="ma-1 w-100" v-if="user && report.favorite"
                               :id="report.id"
                               :loading="isReportsLoading[report.id] || isFavoriteLoading[report.id]"
                               @click="onRemoveFromFavoritesClick(report.id)">
                            REMOVE FROM FAVORITES
                        </v-btn>
                        <v-btn small color="warning" class="ma-1 w-100"
                               v-if="user && user.type === 'admin'"
                               :id="report.id"
                               @click="onCategoryClick(report.id, report.conference_id)">
                            CATEGORY
                        </v-btn>
                        <v-btn small color="success" class="ma-1 w-100"
                               v-if="hasNotJoined(report?.joined) && hasNotEnded(report?.id)"
                               :loading="isReportsLoading[report.id] || isJoinLoading[report.id]"
                               @click="onJoin(report.id)"
                               :id="report.id">
                            JOIN
                        </v-btn>
                        <v-btn small color="warning" class="ma-1 w-100"
                               v-if="isCreator(report.creator_id) || hasJoined(report?.joined)"
                               :loading="isReportsLoading[report.id] || isCancelLoading[report.id]"
                               @click="onCancel(report.id)"
                               :id="report.id">
                            CANCEL
                        </v-btn>
                        <v-btn small color="error" class="ma-1 w-100"
                               v-if="user.type === 'admin'"
                               @click="onDelete(report.id)"
                               :id="report.id">
                            DELETE
                        </v-btn>
                        <a :href="report.start_url"
                           target="_blank" class="text-decoration-none text-white ma-1 w-100"
                           v-if="isCreator(report.creator_id) && report.start_url && !meetingTimerExpired[report.id]">
                            <v-btn small color="#2d8cff" class="w-100 text-white"
                                   :loading="isReportsLoading[report.id] || isCancelLoading[report.id]"
                                   :disabled="meetingTimerExpired[report.id]"
                                   :id="report.id">
                                START ZOOM
                            </v-btn>
                        </a>
                        <a :href="report.join_url"
                           target="_blank" class="text-decoration-none text-white ma-1 w-100"
                           v-if="hasJoined(report?.joined) && report?.join_url && !meetingTimerExpired[report.id]">
                            <v-btn small color="#2d8cff" class="w-100"
                                   :loading="isReportsLoading[report.id] || isCancelLoading[report.id]"
                                   :disabled="meetingTimerExpired[report.id]"
                                   :id="report.id">
                                JOIN ZOOM
                            </v-btn>
                        </a>
                        <v-tooltip :text="tooltipText" location="bottom"
                                   v-if="meetingTimerExpired[report.id] && (report?.join_url || report?.start_url)">
                            <template v-slot:activator="{ props }">
                                <div class="ma-1 w-100" v-bind="props">
                                    <v-btn small color="#2d8cff" class="w-100" disabled>
                                        <span class="text-color-white">{{ meetingTimerExpired[report.id] }}</span>
                                    </v-btn>
                                </div>
                            </template>
                        </v-tooltip>
                    </v-col>
                </v-row>
            </div>
        </v-col>
        <v-col cols="12" sm="12" md="3" lg="3" xl="3" class="d-flex justify-center h-fit" v-if="filterShowCheck">
            <v-expansion-panels v-model="panel" :class="[filterWidthClass, 'mt-3']">
                <v-expansion-panel>
                    <v-expansion-panel-title class="text-center">
                        <span class="text-h6">Filter</span>
                    </v-expansion-panel-title>
                    <v-expansion-panel-text>
                        <v-row>
                            <v-col cols="12">
                                <v-text-field v-model="filterFromTime" type="time" label="From time" clearable
                                              hide-details @change="onFilterChange"
                                              :loading="applyFilterLoading"
                                              :disabled="applyFilterLoading"></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <v-text-field v-model="filterToTime" type="time" label="To time" clearable
                                              hide-details @change="onFilterChange"
                                              :loading="applyFilterLoading"
                                              :disabled="applyFilterLoading"></v-text-field>
                            </v-col>
                            <v-col cols="12">
                                <span class="text-subtitle-2 text-grey">Duration</span>
                                <v-slider class="px-3 pt-8 pb-0" v-model="filterDuration" min="1" max="60"
                                          step="1"
                                          thumb-label="always" @change="onFilterChange"
                                          :disabled="applyFilterLoading || !durationActive"></v-slider>
                                <v-checkbox
                                    v-model="durationActive"
                                    label="Duration"
                                    hide-details
                                ></v-checkbox>
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
                                       :loading="applyFilterLoading || filterCategoriesLoading">
                                    Reset
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-expansion-panel-text>
                </v-expansion-panel>
            </v-expansion-panels>
        </v-col>
    </v-row>
    <ConfirmDialog type="delete" :active="confirmDeleteDialog" header="Removal confirmation"
                   message="Do you really want to delete?" :is-loading="isCancelLoading[chosenReportId]"
                   @confirm="onCancel" @close="confirmDeleteDialog = false"/>
    <CategoryDialog :report-id="chosenReportId" :conference-id="chosenReportConferenceId" type="report"
                    :active="confirmCategoryDialog"
                    @close="onCategoryClose"/>
</template>

<script>
import CategoryDialog from "@/components/dialog/CategoryDialog.vue";
import {api} from "@/api";
import ConfirmDialog from "@/components/dialog/ConfirmDialog.vue";
import {getTimeDifferenceFromDateString} from "@/helpers/getTimeDifferenceFromDate";
import {getWaitingStatus} from "@/helpers/getWaitingStatus";

export default {
    name: "ReportSection",
    components: {ConfirmDialog, CategoryDialog},
    props: {
        type: {
            type: String
        }
    },
    data() {
        return {
            isReportsLoading: [],
            isJoinLoading: [],
            isCancelLoading: [],
            isFavoriteLoading: [],
            chosenReportId: null,
            chosenReportConferenceId: null,
            chosenReportCreatorId: null,
            confirmCategoryDialog: false,
            filterFromTime: null,
            filterToTime: null,
            filterDuration: null,
            filterCategories: [],
            filterChosenCategories: [],
            applyFilterLoading: false,
            filterCategoriesLoading: false,
            confirmDeleteDialog: false,
            panel: [],
            timer: null,
            isLoading: false,
            durationActive: false,
            meetingTimerExpired: [],
            reportWaitingStatus: [],
            filterActivated: false,
            reportsReset: false,
        }
    },
    computed: {
        breakpoint() {
            return this.$vuetify.display.name;
        },
        user() {
            return this.$store.state?.user;
        },
        reportsBlockWidthClass() {
            if (this.breakpoint === 'xs') {
                return 'w-100';
            } else if (this.breakpoint === 'sm') {
                return 'w-75';
            } else if (this.breakpoint === 'md') {
                return 'w-50';
            } else {
                return 'w-30';
            }
        },
        reportWidthClass() {
            if (this.breakpoint === 'sm') {
                return 'w-75';
            } else {
                return 'w-100'
            }
        },
        filterWidthClass() {
            if (this.breakpoint === 'sm') {
                return 'w-75';
            } else {
                return 'w-100';
            }
        },
        reportsColsSize() {
            if (!this.filterShowCheck) {
                return 12;
            } else {
                return 9;
            }
        },
        filterShowCheck() {
            return this.user && this.$route.name === 'Reports';
        },
        reports() {
            return this.$store.state.reports;
        },
        tooltipText() {
            if (this.user.type === 'listener') {
                return 'Wait till report starts to join the meeting';
            } else if (this.user.type === 'member') {
                return 'Your link will be available 10 minutes before meeting starts';
            }
        }
    },
    methods: {
        async onJoin(clickedReportId) {
            this.isJoinLoading[clickedReportId] = true;
            await this.$store.dispatch('joinReport', {
                id: clickedReportId
            });
            this.checkWaitingStatusAndTimer();
            this.isJoinLoading[clickedReportId] = false;
        },
        async onCancel(clickedReportId) {
            if (clickedReportId) {
                this.setChosenReportData(clickedReportId);
            }
            this.isCancelLoading[clickedReportId || this.chosenReportId] = true;
            if (this.user.type === 'member' || this.user.type === 'admin') {
                await api.report.deletePresentation(this.chosenReportConferenceId, this.chosenReportCreatorId);
                await this.$store.dispatch('deleteReport', {
                    conferenceId: this.chosenReportConferenceId,
                    creatorId: this.chosenReportCreatorId,
                });
                await this.$store.dispatch('cancelConference', {
                    conferenceId: this.chosenReportConferenceId,
                    userId: this.chosenReportCreatorId,
                    withoutCommit: this.user.type === 'admin'
                });
            } else if (this.user.type === 'listener') {
                await this.$store.dispatch('cancelReport', {
                    id: clickedReportId
                });
            }
            this.isCancelLoading[clickedReportId || this.chosenReportId] = false;
            this.confirmDeleteDialog = false;
        },
        onDelete(clickedReportId) {
            this.setChosenReportData(clickedReportId);
            this.confirmDeleteDialog = true;
        },
        setChosenReportData(clickedReportId) {
            this.chosenReportId = clickedReportId;
            this.reports.forEach((report) => {
                if (report.id === clickedReportId) {
                    this.chosenReportConferenceId = report.conference_id;
                    this.chosenReportCreatorId = report.creator_id;
                }
            });
        },
        isCreator(creatorId) {
            return this.user?.type === 'member' && creatorId === this.user?.id
        },
        hasJoined(joined) {
            return this.user?.type === 'listener' && joined
        },
        hasNotJoined(joined) {
            return this.user?.type === 'listener' && !joined
        },
        hasNotEnded(reportId) {
            return this.user?.type === 'listener' && this.reportWaitingStatus[reportId]?.status !== 'Ended';
        },
        onCategoryClick(reportId, reportConferenceId) {
            this.chosenReportId = reportId;
            this.chosenReportConferenceId = reportConferenceId;
            this.confirmCategoryDialog = true;
        },
        onCategoryClose() {
            this.confirmCategoryDialog = false;
        },
        async onAddToFavoriteClick(clickedReportId) {
            this.isFavoriteLoading[clickedReportId] = true;
            await this.$store.dispatch('addToFavorites', {id: clickedReportId});
            this.isFavoriteLoading[clickedReportId] = false;
        },
        async onRemoveFromFavoritesClick(clickedReportId) {
            this.isFavoriteLoading[clickedReportId] = true;
            await this.$store.dispatch('removeFromFavorites', {id: clickedReportId});
            this.isFavoriteLoading[clickedReportId] = false;
        },
        async applyFilter() {
            this.applyFilterLoading = true;
            this.filterActivated = !this.reportsReset;
            const filterData = {
                duration: this.filterDuration,
                fromTime: this.filterFromTime,
                toTime: this.filterToTime,
                categories: this.filterChosenCategories.map(category => category.id)
            }
            const filterOptions = {
                data: filterData,
                active: this.filterActivated
            }
            await this.$store.dispatch('resetReports', filterOptions);
            this.reportsReset = false;
            this.applyFilterLoading = false;
        },
        onFilterReset() {
            this.filterDuration = null;
            this.filterFromTime = null;
            this.filterToTime = null;
            this.filterChosenCategories = [];
            this.reportsReset = true;
        },
        async onFilterChange() {
            clearTimeout(this.timer);
            this.timer = setTimeout(this.applyFilter, 1000);
        },
        checkWaitingStatusAndTimer() {
            this.reports.forEach(report => {
                if (this.user) {
                    this.reportWaitingStatus[report.id] = getWaitingStatus(report.date, report.start_time, report.end_time);
                }
                if (report.start_url || report.join_url) {
                    const reportDate = report.date.split('T')[0];
                    const reportDatetime = `${reportDate}T${report.start_time}`;
                    console.log(reportDatetime);
                    if (report.start_url) {
                        this.meetingTimerExpired[report.id] = getTimeDifferenceFromDateString(reportDatetime, true);
                    } else {
                        this.meetingTimerExpired[report.id] = getTimeDifferenceFromDateString(reportDatetime);
                    }
                }
            });
        },
        activateTimer() {
            setInterval(() => {
                this.checkWaitingStatusAndTimer();
            }, 1000);
        }
    },
    watch: {
        filterChosenCategories() {
            this.onFilterChange();
        },
        filterDuration() {
            this.onFilterChange();
        },
        durationActive() {
            if (!this.durationActive) {
                this.filterDuration = null;
            }
        }
    },
    async mounted() {
        this.isLoading = true;
        if(this.type === 'favorites') {
            await this.$store.dispatch('resetReportsByFavorites');
        } else if(this.type === 'categories') {
            await this.$store.dispatch('resetReportsByCategory');
        } else {
            await this.$store.dispatch('resetReports');
        }
        this.checkWaitingStatusAndTimer();
        this.activateTimer();
        this.isLoading = false;
        if (this.user) {
            this.filterCategoriesLoading = true;
            this.filterCategories = await api.category.getCategories();
            this.filterCategoriesLoading = false;
        }
    }
}
</script>

<style scoped>
.w-30 {
    width: 30%;
}

.h-fit {
    height: fit-content;
}

.h-10 {
    height: 10%;
}

.text-color-white {
    color: white;
}
</style>
