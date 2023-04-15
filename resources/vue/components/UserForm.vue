<template>
    <v-form v-model="valid" ref="form"
            :class="[breakpoint === 'xs' && 'w-75', breakpoint === 'sm' && 'w-75', breakpoint === 'md' && 'w-50',
                    breakpoint === 'lg' && 'w-50', breakpoint === 'xl' && 'w-50', 'd-flex', 'align-center',
                     'justify-center']"
            @submit.prevent="submit">
        <v-row class="elevation-2 rounded my-3 bg-white">
            <v-col cols="12" class="d-flex justify-center">
                <span class="text-h4">{{ type === 'edit' ? 'Edit profile' : 'Register' }}</span>
            </v-col>
            <v-col cols="12">
                <span class="text-caption text-grey">Please fill the fields to
                    {{ type === 'edit' ? 'edit profile' : 'register' }}</span>
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field
                    v-model="firstname"
                    :rules="firstnameRules"
                    :counter="60"
                    label="First name"
                    required
                ></v-text-field>
            </v-col>
            <v-col cols="12" md="6">
                <v-text-field
                    v-model="lastname"
                    :rules="lastnameRules"
                    :counter="60"
                    label="Last name"
                    required
                ></v-text-field>
            </v-col>
            <v-col cols="12">
                <v-text-field
                    v-model="birthdate"
                    :rules="birthdateRules"
                    label="Birth date"
                    required
                    type="date"
                ></v-text-field>
            </v-col>
            <v-col cols="12">
                <v-select
                    :items="countries"
                    item-title="name"
                    item-value="name"
                    v-model="country"
                    :rules="countryRules"
                    label="Country"
                    required
                ></v-select>
            </v-col>
            <v-col cols="12" v-if="type !== 'edit'">
                <v-select
                    :items="types"
                    item-title="title"
                    item-value="value"
                    v-model="userType"
                    :rules="typeRules"
                    label="Type"
                    required
                ></v-select>
            </v-col>
            <v-col cols="12">
                <v-text-field
                    v-model="phone"
                    :rules="phoneRules"
                    :counter="20"
                    label="Phone"
                    :placeholder="phonePlaceholder"
                    required
                ></v-text-field>
            </v-col>
            <v-col cols="12">
                <v-text-field
                    v-model="email"
                    :rules="emailRules"
                    :counter="60"
                    label="Email"
                    required
                    :bg-color="uniqueEmailError && 'error'"
                    ref="emailRef"
                ></v-text-field>
            </v-col>
            <v-col cols="12">
                <v-text-field
                    v-model="password"
                    :rules="passwordRules"
                    :counter="60"
                    label="Password"
                    type="password"
                    required
                ></v-text-field>
            </v-col>
            <v-divider v-if="type === 'edit'"></v-divider>
            <v-col cols="12" class="d-flex my-3" v-if="type === 'edit'">
                <div class="w-100 d-flex justify-center" v-if="planLoading">
                    <v-progress-circular color="surface-variant" indeterminate class="my-3"></v-progress-circular>
                </div>
                <v-row class="w-100 d-flex align-center" v-else>
                    <v-col cols="10">
                        Your current plan is <b>{{ currentPlan }}</b> join plan.
                        <span
                            v-if="planJoinCount">There's left <b>{{ joinsLeft }}</b> joins left to use this month</span>
                    </v-col>
                    <v-spacer></v-spacer>
                    <v-col col="2">
                        <v-btn color="error" @click="onSubscriptionCancel" :loading="cancelLoading"
                               v-if="planJoinCount !== 1">
                            Cancel
                        </v-btn>
                    </v-col>
                </v-row>
            </v-col>
            <v-col cols="12" class="d-flex justify-center">
                <v-btn
                    :disabled="!valid"
                    :loading="isSaveLoading"
                    color="primary"
                    type="submit"
                    v-if="type === 'edit'"
                >
                    SAVE
                </v-btn>
                <v-btn
                    :disabled="!valid"
                    :loading="isRegisterLoading"
                    color="surface-variant"
                    type="submit"
                    v-else
                >
                    REGISTER
                </v-btn>
            </v-col>
        </v-row>
        <InfoDialog type="error" header="Email error" message="Entered email has already been taken."
                    :active="errorDialog" @close="errorDialog = false"/>
        <InfoDialog type="success" header="Successfully edited" message="Changes have been successfully saved."
                    :active="successDialog" @close="successDialog = false" v-if="type === 'edit'"/>
        <InfoDialog type="success" header="Successfully created" message="Your account was successfully created."
                    :active="successDialog" @close="redirectToLogin" v-else/>
    </v-form>
</template>

<script>
import InfoDialog from "@/components/dialog/InfoDialog.vue";
import {api} from "@/api";

export default {
    name: "UserForm",
    components: {InfoDialog},
    props: {
        type: String
    },
    data: () => ({
        valid: false,
        errorDialog: false,
        successDialog: false,
        isRegisterLoading: false,
        isSaveLoading: false,
        firstname: '',
        firstnameRules: [
            v => !!v || 'First name is required',
            v => (v && v.length <= 60) || 'First name must be less than 60 characters',
        ],
        lastname: '',
        lastnameRules: [
            v => !!v || 'Last name is required',
            v => (v && v.length <= 60) || 'Last name must be less than 60 characters',
        ],
        birthdate: '',
        birthdateRules: [
            v => !!v || 'Birth date is required',
            v => (v && new Date(v).getTime() <= new Date().getTime()) || "Birth date can't be more than today",
        ],
        email: '',
        emailRules: [
            v => !!v || 'E-mail is required',
            v => (v && v.length <= 60) || 'Email must be less than 60 characters',
            v => /.+@.+/.test(v) || 'E-mail must be valid',
        ],
        password: '',
        passwordRules: [
            v => !!v || 'Password is required',
            v => (v && v.length <= 60) || 'Password be must less than 60 characters',
        ],
        country: '',
        countries: [],
        countryRules: [
            v => !!v || 'Country is required',
        ],
        userType: '',
        types: [
            {
                value: 'listener',
                title: 'listener'
            },
            {
                value: 'member',
                title: 'member'
            },
            {
                value: 'announcer',
                title: 'announcer'
            }
        ],
        typeRules: [
            v => !!v || 'Type is required',
        ],
        phone: '',
        phonePlaceholder: '',
        phoneRules: [
            v => !!v || 'Phone is required',
            v => (v && v.length <= 20) || 'Phone be must less than 20 characters',
            v => (v && /^[+]?[(]?[0-9]{3}[)]?[0-9]*$/gm.test(v)) || 'Phone is incorrect',
        ],
        planJoinCount: null,
        userJoinCount: null,
        userPlanId: null,
        planLoading: false,
        cancelLoading: false
    }),
    computed: {
        breakpoint() {
            return this.$vuetify.display.name;
        },
        joinsLeft() {
            const joinsLeft = this.planJoinCount - this.userJoinCount;
            return joinsLeft < 0 ? 0 : joinsLeft;
        },
        currentPlan() {
            if (this.userPlanId === null) {
                return 1;
            } else if (this.planJoinCount === null) {
                return 'unlimited';
            } else {
                return this.planJoinCount;
            }
        }
    },
    methods: {
        redirectToLogin() {
            this.successDialog = false;
            this.$router.push('/login');
        },
        async submit() {
            const userInfo = {
                firstname: this.firstname,
                lastname: this.lastname,
                birthdate: this.birthdate,
                country_name: this.country,
                type: this.userType,
                phone: this.phone,
                email: this.email,
                previousEmail: this.$store.state.user?.email,
                password: this.password,
                token: this.$store.state.user?.token,
                id: this.$store.state.user?.id
            }
            if (this.type === 'edit') {
                this.isSaveLoading = true;
                try {
                    await this.$store.dispatch('editUser', {userInfo});
                    this.successDialog = true;
                } catch (e) {
                    this.errorDialog = true;
                } finally {
                    this.isSaveLoading = false;
                }
            } else {
                this.isRegisterLoading = true;
                api.user.registerUser(userInfo)
                    .then(() => {
                        this.successDialog = true;
                        this.isRegisterLoading = false;
                    })
                    .catch(() => {
                        this.errorDialog = true;
                        this.isRegisterLoading = false;
                    });
            }
        },
        async onSubscriptionCancel() {
            this.cancelLoading = true;
            await this.$store.dispatch('cancelSubscriptionToPlan');
            this.planJoinCount = 1;
            this.userJoinCount = 0;
            this.userPlanId = null;
            this.cancelLoading = false;
        }
    },
    watch: {
        country(value) {
            for (let country of this.countries) {
                if (country.name === value) {
                    this.phonePlaceholder = country.phone_mask;
                    break;
                }
            }
        }
    },
    async mounted() {
        api.country.getCountries().then((result) => {
            this.countries = result;
        });
        if (this.type === 'edit') {
            this.firstname = this.$store.state.user.firstname;
            this.lastname = this.$store.state.user.lastname;
            this.birthdate = this.$store.state.user.birthdate;
            this.country = this.$store.state.user.country;
            this.userType = this.$store.state.user.type;
            this.phone = this.$store.state.user.phone;
            this.email = this.$store.state.user.email;
            this.password = this.$store.state.user.password;
            this.userPlanId = this.$store.state.user.plan_id;
            this.userJoinCount = this.$store.state.user.join_count;
            this.planLoading = true;
            this.planJoinCount = (await api.plan.getPlan(this.userPlanId)).join_count;
            this.planLoading = false;
        }
    }
}
</script>

<style scoped>

</style>
