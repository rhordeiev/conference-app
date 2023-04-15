<template>
    <RouterLink to="/"
                class="w-100 text-decoration-none">
        <v-btn
            depressed
            color="surface-variant"
            class="float-start ms-xl-6 ms-lg-6 ms-md-6 ms-sm-6 ms-6 my-3"
        >Back
        </v-btn>
    </RouterLink>
    <Conference type="readonly" />
</template>

<script>
import Conference from "@/components/Conference.vue";

export default {
    name: "ConferenceView",
    components: {Conference}
}
</script>

<style scoped>

</style>
