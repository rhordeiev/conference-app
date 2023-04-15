<template>
    <v-card :class="type === 'readonly' || type === 'edit' ? widthClasses : []">
        <v-form v-model="valid" ref="form"
                @submit.prevent="onJoinConfirm">
            <v-breadcrumbs :items="reportPath" v-if="type === 'readonly'">
                <template v-slot:title="{ item }">
                    {{ item.name }}
                </template>
                <template v-slot:divider>
                    <v-icon icon="mdi-chevron-right"></v-icon>
                </template>
            </v-breadcrumbs>
            <v-breadcrumbs :items="categoryPath" v-if="type === 'readonly'">
                <template v-slot:title="{ item }">
                    {{ item.name }}
                </template>
                <template v-slot:divider>
                    <v-icon icon="mdi-chevron-right"></v-icon>
                </template>
            </v-breadcrumbs>
            <v-card-title class="d-flex justify-center">
                <span class="text-h5">Report</span>
            </v-card-title>
            <v-card-text>
                <v-container :max-width="breakpoint === 'md' ">
                    <v-row>
                        <v-col
                            cols="12"
                        >
                            <v-text-field
                                label="Topic"
                                required
                                v-model="topic"
                                :rules="topicRules"
                                :counter="255"
                                :readonly="type === 'readonly'"
                                :loading="(type === 'readonly' || type === 'edit') && dataLoading"
                            ></v-text-field>
                        </v-col>
                        <v-col
                            cols="12"
                            sm="6"
                        >
                            <v-text-field
                                label="Start time"
                                ref="startTimeRef"
                                v-model="startTimeExtracted"
                                required
                                :rules="startTimeRules"
                                prepend-inner-icon="mdi-clock-time-two-outline"
                                @click="onStartTimePickerClick"
                                :loading="dataLoading"
                                :disabled="dataLoading"
                                :readonly="type === 'readonly'"
                            ></v-text-field>
                            <v-dialog v-model="startDatepickerDialog" :max-width="timePickerDialogMaxWidth">
                                <TimePicker :min-hours="minHours" :min-minutes="minMinutes" :max-hours="maxHours"
                                            :max-minutes="maxMinutes"
                                            @close="startDatepickerDialog = !startDatepickerDialog"
                                            @confirm="onStartTimePickerConfirm"/>
                            </v-dialog>
                        </v-col>
                        <v-col
                            cols="12"
                            sm="6"
                        >
                            <v-text-field
                                label="End time"
                                ref="endTimeRef"
                                v-model="endTimeExtracted"
                                required
                                :rules="endTimeRules"
                                prepend-inner-icon="mdi-clock-time-ten"
                                @click="onEndTimePickerClick"
                                :loading="dataLoading"
                                :disabled="dataLoading"
                                :readonly="type === 'readonly'"
                            ></v-text-field>
                            <v-dialog v-model="endDatepickerDialog" :max-width="timePickerDialogMaxWidth">
                                <TimePicker :min-hours="minHours" :min-minutes="minMinutes" :max-hours="maxHours"
                                            :max-minutes="maxMinutes"
                                            @close="endDatepickerDialog = !endDatepickerDialog"
                                            @confirm="onEndTimePickerConfirm"/>
                            </v-dialog>
                        </v-col>
                        <v-col cols="12">
                            <v-textarea
                                filled
                                v-model="description"
                                label="Description"
                                auto-grow
                                :readonly="type === 'readonly'"
                                :loading="(type === 'readonly' || type === 'edit') && dataLoading"
                            ></v-textarea>
                        </v-col>
                        <v-col cols="12">
                            <v-file-input
                                accept=".pptx"
                                v-model="presentation"
                                :rules="presentationRules"
                                label="Presentation"
                                prepend-inner-icon="mdi-paperclip"
                                prepend-icon=""
                                clearable
                                v-if="type !== 'readonly'"
                            ></v-file-input>
                        </v-col>
                        <v-col cols="12" v-if="type !== 'readonly' && type !== 'edit'">
                            <v-checkbox
                                v-model="online"
                                label="Online"
                                hide-details
                            ></v-checkbox>
                        </v-col>
                        <v-col cols="12" v-if="type !== 'readonly' && type !== 'edit' && online">
                            <v-alert
                                type="info"
                                title="Meeting information"
                                text="Your link for meeting will be available 10 minutes before report starts"
                                variant="tonal"
                            ></v-alert>
                        </v-col>
                    </v-row>
                </v-container>
            </v-card-text>
            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    color="grey darken-1"
                    text
                    @click="$emit('close')"
                    v-if="type !== 'readonly' && type !== 'edit'"
                >
                    Close
                </v-btn>
                <v-btn color="pink" v-if="type === 'readonly' && !favorite"
                       :loading="dataLoading || favoriteLoading"
                       @click="onAddToFavoriteClick">
                    ADD TO FAVORITE
                </v-btn>
                <v-btn color="grey" v-if="type === 'readonly' && favorite"
                       :loading="dataLoading || favoriteLoading"
                       @click="onRemoveFromFavoritesClick">
                    REMOVE FROM FAVORITES
                </v-btn>
                <v-btn
                    color="success"
                    :loading="dataLoading || joinLoading"
                    type="submit"
                    v-if="type !== 'readonly' && type !== 'edit'"
                >
                    JOIN
                </v-btn>
                <v-btn
                    color="success"
                    :loading="dataLoading || joinLoading"
                    @click="onJoin"
                    v-if="type === 'readonly' && hasNotJoined"
                >
                    JOIN
                </v-btn>
                <v-btn
                    color="surface-variant"
                    :loading="dataLoading"
                    v-if="type === 'readonly' && presentation"
                    :href="`/storage/app/public/${presentation}`"
                >
                    Presentation
                </v-btn>
                <v-btn
                    color="primary"
                    :loading="dataLoading"
                    v-if="type === 'readonly' && isCreator"
                    :href="`/report/edit/${this.reportId}`"
                >
                    Edit
                </v-btn>
                <v-btn
                    color="primary"
                    :loading="dataLoading || saveLoading"
                    v-if="type === 'edit'"
                    @click="onChangesSave"
                >
                    Save
                </v-btn>
                <v-btn
                    color="warning"
                    :loading="dataLoading || isCancelLoading"
                    v-if="((type === 'readonly' || type === 'edit') && isCreator) || (type === 'readonly' && hasJoined)"
                    @click="onCancel"
                >
                    Cancel
                </v-btn>
                <v-btn
                    color="error"
                    :loading="dataLoading"
                    v-if="(type === 'readonly' || type === 'edit') && user.type === 'admin'"
                    @click="onDelete"
                >
                    Delete
                </v-btn>
                <v-btn color="teal" @click="exportComments" :loading="exportLoading"
                       v-if="(type === 'readonly' || type === 'edit') && user.type === 'admin'">
                    Export comments
                </v-btn>
                <csv-export-snackbar :loading="exportLoading" :progress="progress"/>
                <a :href="this.startUrl"
                   target="_blank" class="text-decoration-none text-white me-3"
                   v-if="isCreator && this.startUrl && !meetingTimerExpired">
                    <v-btn small color="#2d8cff">START ZOOM</v-btn>
                </a>
                <a :href="this.joinUrl"
                   target="_blank" class="text-decoration-none text-white me-3"
                   v-if="hasJoined && this.joinUrl && !meetingTimerExpired">
                    <v-btn small color="#2d8cff">JOIN ZOOM</v-btn>
                </a>
                <v-tooltip :text="tooltipText" location="top"
                           v-if="meetingTimerExpired && (this.startUrl || this.joinUrl)">
                    <template v-slot:activator="{ props }">
                        <div class="me-3" v-bind="props">
                            <v-btn small color="#2d8cff" disabled>
                                <span class="text-color-white">{{ meetingTimerExpired }}</span>
                            </v-btn>
                        </div>
                    </template>
                </v-tooltip>
            </v-card-actions>
        </v-form>
        <InfoDialog type="error" header="Validation error" message="Check if you correctly filled the inputs."
                    :active="errorDialog" @close="errorDialog = false"/>
        <InfoDialog type="error" header="Set time error" :message="timePickerErrorText"
                    :active="errorTimeDialog" @close="errorTimeDialog = false"/>
        <InfoDialog type="error" header="Conference join error" :message="conferenceJoinErrorText"
                    :active="errorConferenceJoinDialog" @close="onErrorConferenceJoinDialogClose"/>
        <ConfirmDialog type="delete" :active="confirmDeleteDialog" header="Removal confirmation"
                       message="Do you really want to delete?" :is-loading="isCancelLoading"
                       @confirm="onCancel" @close="confirmDeleteDialog = false"/>
    </v-card>
</template>

<script>
import TimePicker from "@/components/TimePicker.vue";
import InfoDialog from "@/components/dialog/InfoDialog.vue";
import {fileTypes, validationText} from "@/config";
import buildCategoryPath from "@/helpers/buildCategoryPath";
import {api} from "@/api";
import downloadCsvFile from "@/helpers/downloadCsvFile";
import ConfirmDialog from "@/components/dialog/ConfirmDialog.vue";
import CsvExportSnackbar from "@/components/CsvExportSnackbar.vue";
import {getTimeDifferenceFromDateString} from "@/helpers/getTimeDifferenceFromDate";

export default {
    name: "Report",
    components: {CsvExportSnackbar, ConfirmDialog, InfoDialog, TimePicker},
    props: {
        chosenConferenceId: Number,
        reportId: Number,
        type: String
    },
    data() {
        return {
            startDatepickerDialog: false,
            endDatepickerDialog: false,
            valid: false,
            errorDialog: false,
            errorTimeDialog: false,
            minMinutes: 0,
            minHours: 0,
            maxMinutes: 59,
            maxHours: 23,
            url: window.location.href,
            dataLoading: false,
            joinLoading: false,
            saveLoading: false,
            favoriteLoading: false,
            topic: "",
            topicRules: [
                v => !!v || `Topic ${validationText.required}`,
                v => (v && v.length >= 2) || `Topic ${validationText.min2letters}`,
                v => (v && v.length <= 255) || `Topic ${validationText.max255letters}`,
            ],
            startTime: "",
            startTimeRules: [
                v => !!v || `Start time ${validationText.required}`,
                () => {
                    if (this.endTime) {
                        if (this.startTime >= this.endTime) {
                            return validationText.startLessThanEnd;
                        } else {
                            this.$refs.endTimeRef.resetValidation();
                        }
                    }
                    return true;
                },
                () => {
                    if (this.endTime) {
                        const differenceInMinutes = Math.round((this.endTime - this.startTime) / 60000);
                        if (differenceInMinutes > 60) {
                            return validationText.reportLength;
                        } else {
                            this.$refs.endTimeRef.resetValidation();
                        }
                    }
                    return true;
                }
            ],
            endTime: "",
            endTimeRules: [
                v => !!v || `End time ${validationText.required}`,
                () => {
                    if (this.startTime) {
                        if (this.startTime >= this.endTime) {
                            return validationText.endBiggerThanStart;
                        } else {
                            this.$refs.startTimeRef.resetValidation();
                        }
                    }
                    return true;
                },
                () => {
                    if (this.startTime) {
                        const differenceInMinutes = Math.round((this.endTime - this.startTime) / 60000);
                        if (differenceInMinutes > 60) {
                            return validationText.reportLength;
                        } else {
                            this.$refs.startTimeRef.resetValidation();
                        }
                    }
                    return true;
                }
            ],
            description: "",
            presentation: null,
            presentationFilename: '',
            presentationRules: [
                v => v === null || v[0]?.type === fileTypes.pptx
                    || validationText.fileExtension,
                v => v === null || v[0]?.size < 10000000 || validationText.fileSize,
            ],
            creatorId: "",
            conferenceId: "",
            favorite: false,
            joined: false,
            isCancelLoading: false,
            conferenceReports: [],
            timePickerErrorText: '',
            categoryPath: [],
            reportPath: [],
            progress: 0,
            exportLoading: false,
            confirmDeleteDialog: false,
            online: false,
            startUrl: null,
            joinUrl: null,
            meetingTimerExpired: false,
            date: null,
            errorConferenceJoinDialog: false,
            conferenceJoinErrorText: ''
        }
    },
    computed: {
        breakpoint() {
            return this.$vuetify.display.name;
        },
        widthClasses() {
            return [this.breakpoint === 'xs' && 'w-100', this.breakpoint === 'sm' && 'w-100'
                , this.breakpoint === 'md' && 'w-100', this.breakpoint === 'lg' && 'w-75',
                this.breakpoint === 'xl' && 'w-50'];
        },
        startTimeExtracted() {
            if (this.type === 'readonly') {
                return this.startTime === "" ? "" : this.startTime.split(':').slice(0, 2).join(':');
            }
            return this.startTime === "" ? "" : this.startTime.toTimeString().split(':').slice(0, 2).join(':');
        },
        endTimeExtracted() {
            if (this.type === 'readonly') {
                return this.endTime === "" ? "" : this.endTime.split(':').slice(0, 2).join(':');
            }
            return this.endTime === "" ? "" : this.endTime.toTimeString().split(':').slice(0, 2).join(':');
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
        isCreator() {
            return this.creatorId === this.user.id;
        },
        hasJoined() {
            return this.user?.type === 'listener' && this.joined
        },
        hasNotJoined() {
            return this.user?.type === 'listener' && !this.joined
        },
        user() {
            return this.$store.state.user;
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
        onStartTimePickerConfirm(timestamp) {
            this.startDatepickerDialog = !this.startDatepickerDialog;
            this.startTime = timestamp;
        },
        onEndTimePickerConfirm(timestamp) {
            this.endDatepickerDialog = !this.endDatepickerDialog;
            this.endTime = timestamp;
        },
        async onJoinConfirm() {
            const formValidation = await this.$refs.form.validate();
            if (formValidation.valid) {
                this.joinLoading = true;
                let error = false;
                const enteredStartTime = this.startTime;
                const enteredEndTime = this.endTime;
                const arrayOfEndTimes = [];
                for (let i = 0; i < this.conferenceReports.length; i++) {
                    const startTime = new Date()
                    startTime.setHours(parseInt(this.conferenceReports[i].start_time.split(':')[0]));
                    startTime.setMinutes(parseInt(this.conferenceReports[i].start_time.split(':')[1]));
                    startTime.setSeconds(0);
                    const endTime = new Date();
                    endTime.setHours(parseInt(this.conferenceReports[i].end_time.split(':')[0]));
                    endTime.setMinutes(parseInt(this.conferenceReports[i].end_time.split(':')[1]));
                    endTime.setSeconds(0);
                    arrayOfEndTimes.push(endTime);
                    if (enteredStartTime > startTime && enteredStartTime < endTime) {
                        error = true;
                    }
                    if (enteredEndTime > startTime && enteredEndTime < endTime) {
                        error = true;
                    }
                }
                if (error) {
                    let nearestTime;
                    let minDifference = Math.abs(enteredEndTime - arrayOfEndTimes[0]);
                    let minDifferenceIndex = 0;
                    for (let i = 1; i < arrayOfEndTimes.length; i++) {
                        if (Math.abs(enteredEndTime - arrayOfEndTimes[i]) < minDifference) {
                            minDifference = Math.abs(enteredEndTime - arrayOfEndTimes[i]);
                            minDifferenceIndex = i;
                        }
                    }
                    nearestTime = new Date();
                    nearestTime.setHours(arrayOfEndTimes[minDifferenceIndex].getHours());
                    nearestTime.setMinutes(arrayOfEndTimes[minDifferenceIndex].getMinutes() + 1);
                    if (nearestTime.getMinutes() === 60) {
                        nearestTime.setHours(nearestTime.getHours() + 1);
                        nearestTime.setMinutes(0);
                    }
                    this.errorTimeDialog = true;
                    const nearestTimeMinutes = nearestTime.getMinutes() < 10 ? `0${nearestTime.getMinutes()}` :
                        nearestTime.getMinutes();
                    this.timePickerErrorText = `This time is already taken. The nearest time to start is
                 ${nearestTime.getHours()}:${nearestTimeMinutes}`;
                    this.joinLoading = false;
                    return;
                }

                let generatedFilename = '';
                if (this.presentation) {
                    generatedFilename = await api.report.uploadPresentation(this.presentation[0]);
                }
                const reportInfo = {
                    topic: this.topic,
                    start_time: `${this.startTimeExtracted}:00`,
                    end_time: `${this.endTimeExtracted}:00`,
                    description: this.description,
                    presentation: this.presentation ? generatedFilename : null,
                    conference_id: this.chosenConferenceId,
                    online: this.online
                }
                try {
                    await this.$store.dispatch('joinConference', {id: this.chosenConferenceId});
                    await this.$store.dispatch('newReport', {reportInfo});
                    this.$emit('confirm');
                    if (this.$route.name === 'Category') {
                        window.location.reload();
                    }
                } catch(error) {
                    this.conferenceJoinErrorText = error.message;
                    this.errorConferenceJoinDialog = true;
                }
                this.joinLoading = false;
            } else {
                this.errorDialog = true;
            }
        },
        onErrorConferenceJoinDialogClose() {
            this.errorConferenceJoinDialog = false;
            this.$emit('confirm');
            this.$router.push('plans');
        },
        onStartTimePickerClick() {
            return this.type === 'readonly' ? this.startDatepickerDialog = false : this.startDatepickerDialog = true
        },
        onEndTimePickerClick() {
            return this.type === 'readonly' ? this.endDatepickerDialog = false : this.endDatepickerDialog = true
        },
        async onJoin() {
            this.joinLoading = true;
            const result = await this.$store.dispatch('joinReport', {
                id: this.$route.params.id
            });
            if (result?.join_url) {
                this.joinUrl = result.join_url;
                this.date = result.date;
            }
            this.joined = true;
            this.joinLoading = false;
        },
        async onCancel() {
            this.isCancelLoading = true;
            if (this.user.type === 'member' || this.user.type === 'admin') {
                await api.report.deletePresentation(this.conferenceId, this.creatorId);
                await this.$store.dispatch('deleteReport', {
                    conferenceId: this.conferenceId,
                    creatorId: this.creatorId
                });
                await this.$store.dispatch('cancelConference', {
                    conferenceId: this.conferenceId,
                    userId: this.creatorId,
                    withoutCommit: this.user.type === 'admin'
                });
            } else if (this.user.type === 'listener') {
                await this.$store.dispatch('cancelReport', {
                    id: this.$route.params.id
                });
                if (this.joinUrl) {
                    this.joinUrl = null;
                    this.date = null;
                }
                this.joined = false;
            }
            this.isCancelLoading = false;
            this.confirmDeleteDialog = false;
            if (this.user.type === 'member' || this.user.type === 'admin') {
                this.$router.push('/reports');
            }
        },
        onDelete() {
            this.confirmDeleteDialog = true;
        },
        async onChangesSave() {
            const formValidation = await this.$refs.form.validate();
            if (formValidation.valid) {
                this.saveLoading = true;
                let generatedFilename = '';
                if (this.presentation) {
                    await api.report.deletePresentation(this.conferenceId);
                    generatedFilename = await api.report.uploadPresentation(this.presentation[0]);
                }
                const reportInfo = {
                    id: this.reportId,
                    topic: this.topic,
                    start_time: `${this.startTimeExtracted}:00`,
                    end_time: `${this.endTimeExtracted}:00`,
                    description: this.description,
                    presentation: this.presentation ? generatedFilename : this.presentationFilename,
                    conferenceId: this.conferenceId
                }
                await this.$store.dispatch('editReport', {reportInfo});
                this.saveLoading = false;
                this.$router.push('/reports');
            } else {
                this.errorDialog = true;
            }
        },
        async onAddToFavoriteClick() {
            this.favoriteLoading = true;
            await this.$store.dispatch('addToFavorites', {id: this.$route.params.id});
            this.favorite = true;
            this.favoriteLoading = false;
        },
        async onRemoveFromFavoritesClick() {
            this.favoriteLoading = true;
            await this.$store.dispatch('removeFromFavorites', {id: this.$route.params.id});
            this.favorite = false;
            this.favoriteLoading = false;
        },
        async parseTime(conferenceId) {
            const conference = await api.conference.getConference(conferenceId);
            this.minHours = parseInt(conference.start_time.split(':')[0]);
            this.minMinutes = parseInt(conference.start_time.split(':')[1]);
            this.maxHours = parseInt(conference.end_time.split(':')[0]);
            this.maxMinutes = parseInt(conference.end_time.split(':')[1]);
        },
        async exportComments() {
            this.progress = 0;
            this.exportLoading = true;
            const csvContent = await api.report.exportComments(this.$route.params.id);
            downloadCsvFile(csvContent, 'comments.csv');
            this.exportLoading = false;
        },
        activateTimer() {
            setInterval(() => {
                if (this.startUrl || this.joinUrl) {
                    const reportDatetime = `${this.date}T${this.startTime}`;
                    if (this.startUrl) {
                        this.meetingTimerExpired = getTimeDifferenceFromDateString(reportDatetime, true);
                    } else {
                        this.meetingTimerExpired = getTimeDifferenceFromDateString(reportDatetime);
                    }
                }
            }, 1000);
        }
    },
    async mounted() {
        this.dataLoading = true;
        if (this.type === 'readonly') {
            if (this.user?.type === 'admin') {
                Echo.channel(`csv-export-progress-changed-comments-${this.user?.id}`)
                    .listen('CsvExportProgressChanged', (result) => {
                        this.progress = result.message;
                    });
            }
            this.reportPath = [
                {
                    name: 'conferences'
                },
            ];
            this.categoryPath = [
                {
                    name: 'category'
                }
            ];
            const result = await api.report.getReport(this.reportId, true);
            this.topic = result.report.topic;
            this.startTime = result.report.start_time;
            this.endTime = result.report.end_time;
            this.description = result.report.description;
            this.presentation = result.report.presentation;
            this.creatorId = result.report.creator_id;
            this.conferenceId = result.report.conference_id;
            this.favorite = result.report.favorite;
            this.joined = result.report.joined;
            if (result.report.meeting_id) {
                this.activateTimer();
            }
            if (result.report.start_url) {
                this.startUrl = result.report.start_url;
            } else if (result.report.join_url) {
                this.joinUrl = result.report.join_url;
            }
            this.date = result.report.date.split('T')[0];
            this.dataLoading = false;

            this.reportPath = [
                {
                    name: 'conferences'
                },
                {
                    name: result.conference.title,
                    href: `/conference/${result.conference.id}`
                },
                {
                    name: 'reports'
                },
                {
                    name: this.topic
                },
            ];
            const categories = result.categories;
            const category = result.category;
            this.categoryPath = [];
            if (category) {
                this.categoryPath = buildCategoryPath(categories, category.id);
            }
            this.categoryPath = [{name: 'categories'}, ...this.categoryPath.reverse(), {name: ''}];
        } else if (this.type === 'edit') {
            const result = await api.report.getReport(this.reportId);

            this.topic = result.topic;
            const startTimeDate = new Date();
            startTimeDate.setHours(parseInt(result.start_time.split(':')[0]));
            startTimeDate.setMinutes(parseInt(result.start_time.split(':')[1]));
            this.startTime = startTimeDate;
            const endTimeDate = new Date();
            endTimeDate.setHours(parseInt(result.end_time.split(':')[0]));
            endTimeDate.setMinutes(parseInt(result.end_time.split(':')[1]));
            this.endTime = endTimeDate;
            this.description = result.description;
            this.presentation = null;
            this.presentationFilename = result.presentation;
            this.creatorId = result.creator_id;
            this.conferenceId = result.conference_id;
            this.favorite = result.favorite;
            if (result.start_url) {
                this.startUrl = result.start_url;
            } else if (result.join_url) {
                this.joinUrl = result.join_url;
            }

            await this.parseTime(this.conferenceId);

            this.dataLoading = false;
        } else {
            await this.parseTime(this.chosenConferenceId);

            this.conferenceReports = await api.report.getReportsByConference(this.chosenConferenceId);

            this.dataLoading = false;
        }
    }
}
</script>

<style scoped>

</style>
