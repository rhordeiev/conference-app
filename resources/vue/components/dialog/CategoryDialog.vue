<template>
    <v-dialog
        v-model="confirmDialog"
        width="500"
    >
        <v-card>
            <v-form ref="form" @submit.prevent="">
                <v-card-title class="d-flex justify-center">
                    <span class="text-h5">Choose category</span>
                </v-card-title>
                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col
                                cols="12"
                            >
                                <v-select label="Category" class="w-100" v-model="categoryId" clearable
                                          :items="categories"
                                          item-title="name"
                                          item-value="id"
                                          :loading="selectedCategoryLoading"
                                >
                                </v-select>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="primary"
                        :loading="saveLoading"
                        @click="onConferenceSave"
                        v-if="type === 'conference'"
                    >
                        Save
                    </v-btn>
                    <v-btn
                        color="primary"
                        :loading="saveLoading"
                        @click="onReportSave"
                        v-else
                    >
                        Save
                    </v-btn>
                    <v-btn
                        color="grey darken-1"
                        text
                        @click="close"
                    >
                        Close
                    </v-btn>
                </v-card-actions>
            </v-form>
        </v-card>
    </v-dialog>
</template>

<script>

import {api} from "@/api";

export default {
    name: "CategoryDialog",
    props: {
        conferenceId: Number,
        reportId: Number,
        type: String,
        active: Boolean
    },
    data() {
        return {
            confirmDialog: false,
            saveLoading: false,
            categories: [],
            categoryId: null,
            previousCategoryId: null,
            selectedCategoryLoading: false,
        }
    },
    methods: {
        async onConferenceSave() {
            this.saveLoading = true;
            const info = {
                conference_id: this.conferenceId
            }
            await api.category.detachReports({
                conference_id: this.conferenceId,
                parent_id: this.previousCategoryId
            });
            if (this.previousCategoryId) {
                info.category_id = this.previousCategoryId;
                await api.category.detachConference(info);
                this.previousCategoryId = info.category_id;
            }
            if (this.categoryId !== null) {
                info.category_id = this.categoryId;
                await api.category.attachConference(info);
                this.previousCategoryId = info.category_id;
            }
            this.saveLoading = false;
            this.close();
        },
        async onReportSave() {
            this.saveLoading = true;
            const info = {
                report_id: this.reportId
            }
            if (this.previousCategoryId) {
                info.category_id = this.previousCategoryId;
                await api.category.detachReport(info);
                this.previousCategoryId = info.category_id;
            }
            if (this.categoryId !== null) {
                info.category_id = this.categoryId;
                await api.category.attachReport(info);
                this.previousCategoryId = info.category_id;
            }
            this.saveLoading = false;
            this.close();
        },
        close() {
            this.$emit('close');
        }
    },
    watch: {
        async active() {
            this.confirmDialog = this.active;
            if (this.confirmDialog === true) {
                this.saveLoading = true;
                this.selectedCategoryLoading = true;
                if (this.type === 'conference') {
                    this.categories = await api.category.getCategories();
                    const foundCategory = await api.conference.getCategory(this.conferenceId);
                    this.previousCategoryId = foundCategory.id;
                    this.categoryId = this.previousCategoryId;
                } else {
                    this.categories = _.toArray(await api.category.getChildCategories(this.conferenceId));
                    const foundCategory = await api.report.getCategory(this.reportId);
                    this.previousCategoryId = foundCategory.id;
                    this.categoryId = this.previousCategoryId;
                }
                this.selectedCategoryLoading = false;
                this.saveLoading = false;
            }
        }
    }
}
</script>

<style scoped>

</style>
