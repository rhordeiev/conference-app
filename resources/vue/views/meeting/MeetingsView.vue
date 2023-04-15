<template>
    <v-container fluid class="d-flex align-center flex-column">
        <span class="text-h4">Meetings</span>
        <v-progress-circular color="surface-variant" indeterminate class="my-3"
                             v-if="meetingsLoading"></v-progress-circular>
        <v-table class="elevation-2 rounded ma-3" v-else>
            <thead>
            <tr>
                <th class="text-center">
                    topic
                </th>
                <th class="text-center">
                    uuid
                </th>
                <th class="text-center">
                    host_id
                </th>
                <th class="text-center">
                    type
                </th>
                <th class="text-center">
                    start_time
                </th>
                <th class="text-center">
                    timezone
                </th>
                <th class="text-center">
                    created_at
                </th>
                <th class="text-center">
                    join_url
                </th>
            </tr>
            </thead>
            <tbody>
            <tr
                v-for="meeting in meetings"
                :key="meeting.id"
            >
                <td>{{ meeting.topic }}</td>
                <td>{{ meeting.uuid }}</td>
                <td>{{ meeting.host_id }}</td>
                <td>{{ meeting.type }}</td>
                <td>{{ meeting.start_time }}</td>
                <td>{{ meeting.timezone }}</td>
                <td>{{ meeting.created_at }}</td>
                <td><a :href="meeting.join_url">{{ meeting.join_url }}</a></td>
            </tr>
            </tbody>
        </v-table>
    </v-container>
</template>

<script>

import {api} from "@/api";

export default {
    name: "FavoritesView",
    data() {
        return {
            meetings: [],
            meetingsLoading: false
        }
    },
    async mounted() {
        this.meetingsLoading = true;
        this.meetings = await api.zoom.getAllMeetings();
        this.meetingsLoading = false;
    }
}
</script>

<style scoped>

</style>
