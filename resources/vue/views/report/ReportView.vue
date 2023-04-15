<template>
    <RouterLink to="/reports"
                class="w-100 text-decoration-none">
        <v-btn
            depressed
            color="surface-variant"
            class="float-start ms-xl-6 ms-lg-6 ms-md-6 ms-sm-6 ms-6 my-3"
        >Back
        </v-btn>
    </RouterLink>
    <v-form class="w-75 d-flex align-center justify-center">
        <Report type="readonly" :report-id="$route.params.id"/>
    </v-form>
    <v-container fluid class="d-flex w-100 flex-column align-center justify-center">
        <CommentSection />
    </v-container>
</template>

<script>
import Report from "@/components/Report.vue";
import CommentSection from "@/components/CommentSection.vue";

export default {
    name: "ReportView",
    components: {CommentSection, Report}
}
</script>

<style scoped>

</style>
