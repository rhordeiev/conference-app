<template>
    <v-snackbar
        v-model="exportLoading"
        timeout="-1"
        color="white"
    >
        <v-progress-linear
            v-model="exportProgress"
            color="teal"
            height="20"
        >
            <template v-slot:default="{ value }">
                <strong>{{ Math.ceil(value) }}%</strong>
            </template>
        </v-progress-linear>
    </v-snackbar>
</template>

<script>
export default {
    name: "CsvExportSnackbar",
    props: {
        loading: {
            type: Boolean
        },
        progress: {
            type: Number
        }
    },
    computed: {
        exportLoading() {
            return this.loading;
        },
        exportProgress() {
            return this.progress;
        }
    }
}
</script>

<style scoped>

</style>
