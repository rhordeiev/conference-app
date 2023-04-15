<template>
    <v-container fluid class="d-flex align-center flex-column">
        <span class="text-h4">Favorites</span>
        <ReportSection type="favorites"/>
    </v-container>
</template>

<script>
import ReportSection from "@/components/ReportSection.vue";
import LoaderDialog from "@/components/dialog/LoaderDialog.vue";

export default {
    name: "FavoritesView",
    components: {LoaderDialog, ReportSection}
}
</script>

<style scoped>

</style>
