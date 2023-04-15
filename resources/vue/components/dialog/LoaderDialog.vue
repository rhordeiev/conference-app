<template>
    <v-dialog
        v-model="dialogFocus"
        width="fit-content"
    >
        <v-sheet class="bg-white elevation-2 rounded pa-3 align-center">
            <v-progress-circular color="surface-variant" indeterminate></v-progress-circular>
        </v-sheet>
    </v-dialog>
</template>

<script>
export default {
    name: "LoaderDialog",
    props: {
        active: Boolean,
    },
    data() {
        return {
            dialogFocus: false
        }
    },
    watch: {
        active() {
            this.dialogFocus = this.active;
        }
    }
}
</script>

<style scoped>

</style>
