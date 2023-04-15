<template>
    <v-container fluid class="d-flex align-center flex-column">
        <RouterLink to="/conference/new"
                    class="text-decoration-none align-self-end me-xl-6 me-lg-6 me-md-6 me-sm-0">
            <v-btn
                depressed
                color="surface-variant"
                v-if="creationAvailabilityCheck"
            >New
            </v-btn>
        </RouterLink>
        <ConferenceSection :conferences="conferences" :is-loading="conferencesLoading"/>
    </v-container>
</template>

<script>

import ConferenceSection from "@/components/ConferenceSection.vue";
import LoaderDialog from "@/components/dialog/LoaderDialog.vue";

export default {
    name: "ConferencesView",
    components: {LoaderDialog, ConferenceSection},
    data() {
        return {
            conferencesLoading: false,
        }
    },
    props: {
        loggedOut: Boolean
    },
    computed: {
        creationAvailabilityCheck() {
            const loggedInUser = this.$store.state.user;
            return loggedInUser?.type === 'admin' || loggedInUser?.type === 'announcer';
        },
        conferences() {
            return this.$store.state.conferences;
        }
    },
    methods: {
        resetConferences() {
            this.conferencesLoading = true;
            this.$store.dispatch('resetConferences').then(() => {
                this.conferencesLoading = false;
            });
        }
    },
    mounted() {
        this.resetConferences();
    }
}
</script>
