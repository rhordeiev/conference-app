<template>
    <v-form v-model="valid" ref="form" @submit.prevent="submit"
            :class="[breakpoint === 'xxl' && 'w-25', breakpoint === 'xl' && 'w-25', breakpoint === 'lg' && 'w-25',
                     breakpoint === 'md' && 'w-25', breakpoint === 'sm' && 'w-50', breakpoint === 'xs' && 'w-75',
                     'h-75', 'd-flex', 'align-center', 'justify-center']">
        <v-row class="w-100 elevation-2 rounded my-3 bg-white">
            <v-col cols="12" class="d-flex justify-center">
                <span class="text-h5">Login</span>
            </v-col>
            <v-col cols="12">
                <span class="text-caption text-grey">Please enter your credentials to login</span>
            </v-col>
            <v-col cols="12" class="d-flex justify-center">
                <v-text-field
                    v-model="email"
                    :rules="emailRules"
                    :counter="60"
                    label="Email"
                    required
                ></v-text-field>
            </v-col>
            <v-col cols="12" class="d-flex justify-center">
                <v-text-field
                    v-model="password"
                    :rules="passwordRules"
                    :counter="60"
                    label="Password"
                    type="password"
                    required
                ></v-text-field>
            </v-col>
            <v-col cols="12" class="d-flex justify-center">
                <v-btn
                    :disabled="!valid"
                    color="surface-variant"
                    type="submit"
                    :loading="isLoginLoading"
                >
                    LOGIN
                </v-btn>
            </v-col>
        </v-row>
    </v-form>
    <InfoDialog type="error" header="Login error" message="User with entered credentials was not found."
                :active="errorDialog" @close="errorDialog = false"/>
</template>

<script>
import InfoDialog from "@/components/dialog/InfoDialog.vue";

export default {
    name: "LoginView",
    components: {InfoDialog},
    data: () => ({
        valid: false,
        isLoginLoading: false,
        errorDialog: false,
        email: '',
        emailRules: [
            v => !!v || 'E-mail is required',
            v => (v && v.length <= 60) || 'Email must less than 60 characters',
            v => /.+@.+/.test(v) || 'E-mail must be valid',
        ],
        password: '',
        passwordRules: [
            v => !!v || 'Password is required',
            v => (v && v.length <= 60) || 'Password must less than 60 characters',
        ],
    }),
    methods: {
        submit() {
            this.isLoginLoading = true;
            this.$store.dispatch('loginUser', {
                userInfo: {
                    email: this.email,
                    password: this.password
                }
            }).then(() => {
                this.isLoginLoading = false;
                window.location.reload();
            }).catch(() => {
                this.errorDialog = true;
                this.isLoginLoading = false;
            });

        },
    },
    computed: {
        breakpoint() {
            return this.$vuetify.display.name;
        }
    },
}
</script>

<style scoped>

</style>
