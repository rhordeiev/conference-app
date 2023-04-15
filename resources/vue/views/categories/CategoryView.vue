<template>
    <v-container fluid class="d-flex align-center flex-column">
        <span class="text-h4">Category: {{categoryName}}</span>
        <ConferenceSection :conferences="conferences" :is-loading="conferencesLoading"/>
        <ReportSection type="categories"/>
    </v-container>
</template>

<script>
import ConferenceSection from "@/components/ConferenceSection.vue";
import ReportSection from "@/components/ReportSection.vue";
import LoaderDialog from "@/components/dialog/LoaderDialog.vue";
import {api} from "@/api";

export default {
    name: "CategoryView",
    components: {LoaderDialog, ReportSection, ConferenceSection},
    data() {
        return {
            categoryName: '',
            conferences: [],
            conferencesLoading: false,
            nameLoading: false
        }
    },
    methods: {
        async resetConferences() {
            this.conferencesLoading = true;
            await this.$store.dispatch('resetConferencesByCategory', {id: this.$route.params.id});
            this.conferences = this.$store.state.conferences;
            this.conferencesLoading = false;
        }
    },
    async mounted() {
        this.nameLoading = true;
        const category = await api.category.getCategoryById(this.$route.params.id);
        this.nameLoading = false;
        this.categoryName = category.name;
        await this.resetConferences();
    }
}
</script>

<style scoped>

</style>
