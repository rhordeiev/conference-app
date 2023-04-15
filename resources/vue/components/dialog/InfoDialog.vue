<template>
    <v-dialog
        v-model="dialogFocus"
        width="500"
    >
        <v-card>
            <v-card-title
                :class="['text-h5', type === 'error' && 'bg-red-darken-3', type === 'success' && 'bg-green-darken-3']">
                {{ header }}
            </v-card-title>

            <v-card-text>
                {{ message }}
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    :class="[type === 'error' && 'text-red-lighten-2', type === 'success' && 'text-green-lighten-2']"
                    text
                    @click="close"
                >
                    Close
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: "InfoDialog",
    props: {
        active: Boolean,
        type: String,
        header: String,
        message: String
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
    },
    methods: {
        close() {
            this.$emit('close');
        }
    }
}
</script>

<style scoped>

</style>
