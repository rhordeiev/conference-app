<template>
    <v-form v-model="valid" ref="form"
            :class="[breakpoint === 'xs' && 'w-75', breakpoint === 'sm' && 'w-75', breakpoint === 'md' && 'w-50',
                    breakpoint === 'lg' && 'w-50', breakpoint === 'xl' && 'w-50', 'd-flex', 'align-center',
                     'justify-center']"
            @submit.prevent="submit">
        <v-row class="elevation-2 rounded my-3 bg-white">
            <v-col cols="12" v-if="type === 'readonly'">
                <v-breadcrumbs :items="['conferences', `${title}`]">
                    <template v-slot:divider>
                        <v-icon icon="mdi-chevron-right"></v-icon>
                    </template>
                </v-breadcrumbs>
                <v-breadcrumbs :items="categoryPath">
                    <template v-slot:title="{ item }">
                        {{ item.name }}
                    </template>
                    <template v-slot:divider>
                        <v-icon icon="mdi-chevron-right"></v-icon>
                    </template>
                </v-breadcrumbs>
            </v-col>
            <v-col cols="12" class="d-flex justify-center">
                <span class="text-h4" v-if="type === 'readonly'">Conference information</span>
                <span class="text-h4" v-else-if="type === 'edit'">Edit conference</span>
                <span class="text-h4" v-else>New conference</span>
            </v-col>
            <v-col cols="12">
                <span class="text-caption text-grey">Please fill the fields to create a conference</span>
            </v-col>
            <v-col cols="12">
                <v-text-field
                    v-model="title"
                    :rules="titleRules"
                    :counter="255"
                    label="Title"
                    :readonly="type === 'readonly'"
                    :loading="dataLoading"
                    required
                ></v-text-field>
            </v-col>
            <v-col cols="12">
                <v-text-field
                    v-model="date"
                    :rules="dateRules"
                    label="Date"
                    required
                    :readonly="type === 'readonly'"
                    :loading="dataLoading"
                    type="date"
                ></v-text-field>
            </v-col>
            <v-col cols="12" v-if="type === 'readonly'">
                <GMapMap
                    :center="center"
                    :zoom="6"
                    map-type-id="terrain"
                    class="w-100"
                    style="height: 50vh"
                >
                    <GMapMarker
                        :position="marker.position"
                        :clickable="true"
                        :draggable="true"
                    />
                </GMapMap>
            </v-col>
            <v-col cols="12" v-else>
                <GMapMap
                    :center="center"
                    :zoom="6"
                    map-type-id="terrain"
                    class="w-100"
                    style="height: 50vh"
                    @click="onMapClick"
                >
                    <GMapMarker
                        :position="marker.position"
                        :clickable="true"
                        :draggable="true"
                        @click="onMarkerClick"
                    />
                </GMapMap>
            </v-col>
            <v-col
                cols="12"
                sm="6"
            >
                <v-text-field
                    v-model="startTimeExtracted"
                    :rules="[startTimeRules[0], startTimeLessThanEndTimeRule]"
                    ref="startTimeRef"
                    label="Start time"
                    required
                    prepend-inner-icon="mdi-clock-time-two-outline"
                    :loading="dataLoading"
                    @click="onStartTimePickerClick"
                ></v-text-field>
                <v-dialog v-model="startDatepickerDialog" :max-width="timePickerDialogMaxWidth">
                    <TimePicker :min-hours="0" :min-minutes="0" :max-hours="23" :max-minutes="59"
                                @close="startDatepickerDialog = !startDatepickerDialog"
                                @confirm="onStartTimePickerConfirm"/>
                </v-dialog>
            </v-col>
            <v-col
                cols="12"
                sm="6"
            >
                <v-text-field
                    v-model="endTimeExtracted"
                    :rules="[endTimeRules[0], endTimeBiggerThanStartTimeRule]"
                    ref="endTimeRef"
                    label="End time"
                    required
                    prepend-inner-icon="mdi-clock-time-ten"
                    :loading="dataLoading"
                    @click="onEndTimePickerClick"
                ></v-text-field>
                <v-dialog v-model="endDatepickerDialog" :max-width="timePickerDialogMaxWidth">
                    <TimePicker :min-hours="0" :min-minutes="0" :max-hours="23" :max-minutes="59"
                                @close="endDatepickerDialog = !endDatepickerDialog"
                                @confirm="onEndTimePickerConfirm"/>
                </v-dialog>
            </v-col>
            <v-col cols="12">
                <v-select
                    :items="countries"
                    item-title="name"
                    item-value="name"
                    v-model="country"
                    :rules="countryRules"
                    label="Country"
                    required
                    :loading="dataLoading"
                    :readonly="type === 'readonly'"
                ></v-select>
            </v-col>
            <v-col cols="12" class="d-flex justify-center" v-if="type !== 'readonly' && type !== 'edit'">
                <v-btn
                    :loading="isCreateLoading"
                    color="surface-variant"
                    type="submit"
                >
                    CREATE
                </v-btn>
            </v-col>
            <v-col cols="12" class="d-flex justify-center" v-if="type === 'readonly'">
                <v-btn
                    color="success"
                    @click="onJoin($event)"
                    v-if="user.type === 'member' && !joined"
                    :loading="isJoinLoading"
                    :id="id"
                >
                    JOIN
                </v-btn>
                <v-btn color="warning"
                       v-else-if="user.type === 'member' && joined"
                       :loading="isCancelLoading"
                       @click="onCancel($event)"
                       class="me-3"
                       :id="id">
                    CANCEL
                </v-btn>
                <a :href="`${twitterUrl}${url}conference/${id}`"
                   target="_blank" class="text-decoration-none text-white me-3"
                   v-if="user.type === 'member' && joined">
                    <v-btn small color="#1DA1F2">TWITTER</v-btn>
                </a>
                <a :href="`${facebookUrl}${url}conference/${id}`"
                   target="_blank" class="text-decoration-none text-white"
                   v-if="user.type === 'member' && joined">
                    <v-btn small color="#4267B2">FACEBOOK</v-btn>
                </a>
                <RouterLink :to="`/conference/edit/${id}`"
                            class="text-decoration-none me-3"
                            v-if="(user.type === 'announcer' && creator) || user.type === 'admin'"
                >
                    <v-btn color="primary">
                        EDIT
                    </v-btn>
                </RouterLink>
                <v-btn
                    class="me-3"
                    color="error"
                    @click="confirmDeleteDialog = true"
                    v-if="(user.type === 'announcer' && creator) || user.type === 'admin'"
                >
                    DELETE
                </v-btn>
                <v-btn class="me-3" color="teal" @click="exportReports" v-if="user.type === 'admin'"
                       :loading="exportReportsLoading">
                    Export reports
                </v-btn>
                <csv-export-snackbar :loading="exportReportsLoading" :progress="progressReports" />
                <v-btn class="me-3" color="teal" @click="exportMembers" v-if="user.type === 'admin'"
                       :loading="exportMembersLoading">
                    Export members
                </v-btn>
                <csv-export-snackbar :loading="exportMembersLoading" :progress="progressMembers" />
            </v-col>
            <v-col cols="12" class="d-flex justify-center" v-if="type === 'edit'">
                <v-btn
                    :loading="isEditLoading"
                    color="primary"
                    type="submit"
                >
                    SAVE
                </v-btn>
            </v-col>
        </v-row>
        <InfoDialog type="success" header="Successfully created" message="Conference was successfully created."
                    :active="successCreationDialog" @close="onCreationSuccess"/>
        <ConfirmDialog type="delete" :active="confirmDeleteDialog" header="Removal confirmation"
                       message="Do you really want to delete?" :is-loading="isDeleteLoading"
                       @confirm="onDeleteConfirm" @close="confirmDeleteDialog = false"/>
        <InfoDialog type="success" header="Successfully edited" message="Conference was successfully edited."
                    :active="successEditDialog" @close="onEditSuccess"/>
        <v-dialog
            v-model="confirmJoinDialog"
            persistent
            max-width="600px"
        >
            <Report :chosen-conference-id="$route.params.id" @confirm="onReportConfirm" @close="onReportClose"/>
        </v-dialog>
    </v-form>
</template>

<script>
import TimePicker from "@/components/TimePicker.vue";
import Report from "@/components/Report.vue";
import InfoDialog from "@/components/dialog/InfoDialog.vue";
import ConfirmDialog from "@/components/dialog/ConfirmDialog.vue";
import {center, validationText} from "@/config";
import buildCategoryPath from "@/helpers/buildCategoryPath";
import {api} from "@/api";
import downloadCsvFile from "@/helpers/downloadCsvFile";
import CsvExportSnackbar from "@/components/CsvExportSnackbar.vue";

export default {
    name: "Conference",
    components: {CsvExportSnackbar, ConfirmDialog, InfoDialog, Report, TimePicker},
    props: {
        type: String
    },
    data: () => ({
        valid: false,
        errorDialog: false,
        successCreationDialog: false,
        successEditDialog: false,
        isEditLoading: false,
        isCreateLoading: false,
        dataLoading: false,
        isDeleteLoading: false,
        isJoinLoading: [],
        isCancelLoading: [],
        confirmJoinDialog: false,
        confirmDeleteDialog: false,
        url: window.location.href,
        id: '',
        title: '',
        titleRules: [
            v => !!v || `Title ${validationText.required}`,
            v => (v && v.length >= 2) || `Title ${validationText.min2letters}`,
            v => (v && v.length <= 255) || `Title ${validationText.max255letters}`,
        ],
        date: '',
        dateRules: [
            v => !!v || `Birth date ${validationText.required}`,
        ],
        country: '',
        countries: [],
        countryRules: [
            v => !!v || `Country ${validationText.required}`,
        ],
        marker: {
            position: null,
        },
        startDatepickerDialog: false,
        endDatepickerDialog: false,
        startTime: null,
        endTime: null,
        startTimeRules: [
            v => !!v || `Start time ${validationText.required}`
        ],
        endTimeRules: [
            v => !!v || `End time ${validationText.required}`
        ],
        joined: false,
        creator: false,
        categoryPath: [],
        twitterUrl: import.meta.env.VITE_TWITTER_SHARE_URL,
        facebookUrl: import.meta.env.VITE_FACEBOOK_SHARE_URL,
        progressReports: 0,
        exportReportsLoading: false,
        progressMembers: 0,
        exportMembersLoading: false

    }),
    computed: {
        center() {
            return center
        },
        breakpoint() {
            return this.$vuetify.display.name;
        },
        startTimeExtracted() {
            return this.startTime === null ? "" : this.startTime.toTimeString().split(':').slice(0, 2).join(':');
        },
        endTimeExtracted() {
            return this.endTime === null ? "" : this.endTime.toTimeString().split(':').slice(0, 2).join(':');
        },
        timePickerDialogMaxWidth() {
            switch (this.breakpoint) {
                case 'sm':
                    return '30%';
                case 'xs':
                    return '50%';
                default:
                    return '20%';
            }
        },
        startTimeLessThanEndTimeRule(value) {
            if (value && this.endTime) {
                this.$refs.startTimeRef.resetValidation();
                this.$refs.endTimeRef.resetValidation();
                if (this.startTime >= this.endTime) {
                    return validationText.startLessThanEnd;
                }
            }
            return true;
        },
        endTimeBiggerThanStartTimeRule(value) {
            if (this.startTime && value) {
                this.$refs.startTimeRef.resetValidation();
                this.$refs.endTimeRef.resetValidation();
                if (this.startTime >= this.endTime) {
                    return validationText.endBiggerThanStart;
                }
            }
            return true;
        },
        user() {
            return this.$store.state.user;
        }
    },
    methods: {
        async submit() {
            if (this.type === 'edit') {
                this.isEditLoading = true;
            } else {
                this.isCreateLoading = true;
            }
            const formValidation = await this.$refs.form.validate();
            if (formValidation.valid) {
                const conferenceInfo = {
                    id: this.id,
                    title: this.title,
                    date: this.date,
                    lat: this.marker.position?.lat || null,
                    lng: this.marker.position?.lng || null,
                    start_time: `${this.startTimeExtracted}:00`,
                    end_time: `${this.endTimeExtracted}:00`,
                    country_name: this.country,
                }
                if (this.type === 'edit') {
                    this.isEditLoading = true;
                    await this.$store.dispatch('editConference', {conferenceInfo});
                    this.successEditDialog = true;
                } else {
                    await this.$store.dispatch('newConference', {conferenceInfo});
                    this.successCreationDialog = true;
                }
            }
            if (this.type === 'edit') {
                this.isEditLoading = false;
            } else {
                this.isCreateLoading = false;
            }
        },
        onEditSuccess() {
            this.successEditDialog = false;
            this.$router.push('/');
        },
        onCreationSuccess() {
            this.successCreationDialog = false;
            this.$router.push('/');
        },
        onMapClick(event) {
            this.marker.position = {
                lat: event.latLng.lat(),
                lng: event.latLng.lng()
            };
        },
        onMarkerClick() {
            this.marker.position = null;
        },
        onStartTimePickerConfirm(timestamp) {
            this.startDatepickerDialog = !this.startDatepickerDialog;
            this.startTime = timestamp;
        },
        onEndTimePickerConfirm(timestamp) {
            this.endDatepickerDialog = !this.endDatepickerDialog;
            this.endTime = timestamp;
        },
        onStartTimePickerClick() {
            return this.type === 'readonly' ? this.startDatepickerDialog = false : this.startDatepickerDialog = true
        },
        onEndTimePickerClick() {
            return this.type === 'readonly' ? this.endDatepickerDialog = false : this.endDatepickerDialog = true
        },
        onJoin() {
            const loggedInUser = this.$store.state.user;
            if (!loggedInUser) {
                return this.$router.push('/register');
            }
            this.confirmJoinDialog = true;
        },
        async onCancel() {
            this.isCancelLoading = true;
            await api.report.deletePresentation(this.id);
            await this.$store.dispatch('deleteReport', {
                conferenceId: this.id
            });
            await this.$store.dispatch('cancelConference', {
                id: this.id
            });
            this.joined = false;
            this.isCancelLoading = false;
        },
        onDeleteConfirm() {
            this.isDeleteLoading = true;
            this.$store.dispatch('deleteConference', {
                id: this.$route.params.id
            }).then(() => {
                this.isDeleteLoading = false;
                this.confirmDeleteDialog = false;
                this.$router.push('/');
            })
        },
        onReportConfirm() {
            this.confirmJoinDialog = false;
            this.joined = true;
        },
        onReportClose() {
            this.confirmJoinDialog = false;
        },
        async getConference(id) {
            const result = await api.conference.getConference(id, true);
            this.id = result.conference.id;
            this.title = result.conference.title;
            this.date = result.conference.date.split('T')[0];
            this.center = {
                lat: parseFloat(result.conference.lat) || 48.3794,
                lng: parseFloat(result.conference.lng) || 31.1656
            };
            this.marker = {
                position: {
                    lat: parseFloat(result.conference.lat),
                    lng: parseFloat(result.conference.lng)
                }
            };
            this.country = result.conference.country_name;
            if (result.conference.joined) {
                this.joined = result.conference.joined;
            }
            if (result.conference.creator) {
                this.creator = result.conference.creator;
            }
            const startTimeDate = new Date();
            startTimeDate.setHours(parseInt(result.conference.start_time.split(':')[0]));
            startTimeDate.setMinutes(parseInt(result.conference.start_time.split(':')[1]));
            this.startTime = startTimeDate;
            const endTimeDate = new Date();
            endTimeDate.setHours(parseInt(result.conference.end_time.split(':')[0]));
            endTimeDate.setMinutes(parseInt(result.conference.end_time.split(':')[1]));
            this.endTime = endTimeDate;

            const categories = result.categories;
            const category = result.category;
            return {
                categories,
                category
            };
        },
        getCountries() {
            return new Promise((resolve) => {
                api.country.getCountries().then((result) => {
                    this.countries = result;
                    resolve();
                });
            });
        },
        async exportReports() {
            this.progressReports = 0;
            this.exportReportsLoading = true;
            const csvContent = await api.conference.exportReports(this.$route.params.id);
            downloadCsvFile(csvContent, 'reports.csv');
            this.exportReportsLoading = false;
        },
        async exportMembers() {
            this.progressMembers = 0;
            this.exportMembersLoading = true;
            const csvContent = await api.conference.exportMembers(this.$route.params.id);
            downloadCsvFile(csvContent, 'members.csv');
            this.exportMembersLoading = false;
        }
    },
    async mounted() {
        if (this.type === 'readonly') {
            if (this.user?.type === 'admin') {
                Echo.channel(`csv-export-progress-changed-reports-${this.user?.id}`)
                    .listen('CsvExportProgressChanged', (result) => {
                        this.progressReports = result.message;
                    });
                Echo.channel(`csv-export-progress-changed-members-${this.user?.id}`)
                    .listen('CsvExportProgressChanged', (result) => {
                        this.progressMembers = result.message;
                    });
            }
            this.isJoinLoading = true;
            this.isCancelLoading = true;
            this.dataLoading = true;
            this.categoryPath = [
                {
                    name: 'category'
                }
            ];
            const {categories, category} = await this.getConference(this.$route.params.id);
            this.categoryPath = [];
            if (category) {
                this.categoryPath = buildCategoryPath(categories, category.id);
            }
            this.categoryPath = [{name: 'categories'}, ...this.categoryPath.reverse(), {name: ''}];
            this.isJoinLoading = false;
            this.isCancelLoading = false;
            this.dataLoading = false;
        } else if (this.type === 'edit') {
            this.dataLoading = true;
            this.isEditLoading = true;
            await this.getConference(this.$route.params.id);
            await this.getCountries();
            this.isEditLoading = false;
            this.dataLoading = false;
        } else {
            this.dataLoading = true;
            await this.getCountries();
            this.dataLoading = false;
        }
    }
}
</script>

<style scoped>

</style>
