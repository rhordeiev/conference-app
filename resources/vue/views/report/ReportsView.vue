<template>
    <v-container fluid class="d-flex align-center flex-column">
        <ReportSection />
    </v-container>
</template>

<script>
import ReportSection from "@/components/ReportSection.vue";
import LoaderDialog from "@/components/dialog/LoaderDialog.vue";

export default {
    name: "ReportsView",
    components: {LoaderDialog, ReportSection},
}
</script>

<style scoped>

</style>
