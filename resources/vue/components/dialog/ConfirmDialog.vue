<template>
    <v-dialog
        v-model="confirmDialog"
        width="500"
    >
        <v-card>
            <v-card-title class="text-h5 bg-surface-variant">
                {{ header }}
            </v-card-title>

            <v-card-text>
                {{ message }}
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions>
                <v-spacer></v-spacer>
                <v-btn
                    :class="[type === 'delete' && 'text-red']"
                    text
                    :loading="isLoading"
                    @click="confirm"
                >
                    Delete
                </v-btn>
                <v-btn
                    class="text-grey-lighten-1"
                    text
                    @click="close"
                >
                    Cancel
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>

<script>
export default {
    name: "ConfirmDialog",
    props: {
        active: Boolean,
        type: String,
        header: String,
        message: String,
        isLoading: Boolean,
        onConfirm: Function
    },
    data() {
        return {
            confirmDialog: false,
        }
    },
    watch: {
        active() {
            this.confirmDialog = this.active;
        }
    },
    methods: {
        confirm() {
            this.$emit('confirm');
        },
        close() {
            this.$emit('close');
        }
    }
}
</script>

<style scoped>

</style>
