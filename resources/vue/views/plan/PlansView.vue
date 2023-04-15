<template>
    <v-container fluid class="d-flex align-center flex-column">
        <span class="text-h4">Plans</span>
        <v-progress-circular color="surface-variant" indeterminate class="my-3"
                             v-if="plansLoading"></v-progress-circular>
        <v-row class="w-50 d-flex justify-center ma-3">
            <v-col cols="6" v-for="plan in plans">
                <v-card class="w-100 pa-3">
                    <div class="w-100 d-flex justify-center">
                        <v-chip
                            class="ali"
                            color="success"
                            label
                            v-if="(!plan?.id && !plan?.id === !userPlan) || plan?.id === userPlan"
                        >
                            Chosen
                        </v-chip>
                    </div>
                    <v-card-title class="pa-3">
                        <span class="text-h5">{{ plan.price }}</span>
                        <span class="text-subtitle-1 text-grey">/month</span>
                    </v-card-title>
                    <v-divider></v-divider>
                    <v-card-item><b>{{ plan.join_count || 'unlimited' }}</b> joins to conferences</v-card-item>
                    <v-card-actions class="w-100 d-flex justify-center">
                        <v-btn color="error" variant="elevated" @click="onSubscriptionCancel"
                               v-if="plan?.id === userPlan" :loading="cancelLoading">
                            Cancel
                        </v-btn>
                        <v-btn color="success" variant="elevated" @click="showUpgradeDialog(plan.id)"
                               v-else-if="plan?.id && plan?.id !== userPlan">
                            Upgrade
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-col>
        </v-row>
    </v-container>
    <v-dialog
        v-model="upgradeSubscriptionDialog"
        width="500"
        eager
        @close="closeUpgradeDialog"
    >
        <v-card class="w-100 d-flex justify-center">
            <v-card-title class="text-center text-h5">Card credentials</v-card-title>
            <v-card-item v-if="subscriptionError || subscriptionSuccess">
                <v-alert color="error" variant="tonal" v-if="subscriptionError">
                    <v-alert-title>Subscription payment failed</v-alert-title>
                    {{ subscriptionErrorText }}
                </v-alert>
            </v-card-item>
            <v-card-item class="mx-3 my-6">
                <div id="card-element"></div>
            </v-card-item>
            <v-card-actions class="d-flex justify-center">
                <v-btn color="primary" variant="elevated" @click="onSubscribe" :loading="subscribeLoading">Subscribe
                </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
    <InfoDialog type="success" header="Subscription payment succeeded" message="Your subscription has successfully started."
                :active="subscriptionSuccess" @close="subscriptionSuccess = false"/>
</template>

<script>
import {api} from "@/api";
import InfoDialog from "@/components/dialog/InfoDialog.vue";

export default {
    name: "PlansView",
    components: {InfoDialog},
    data() {
        return {
            plans: [],
            plansLoading: false,
            upgradeSubscriptionDialog: false,
            stripeKey: import.meta.env.VITE_STRIPE_KEY,
            stripe: null,
            card: null,
            subscriptionError: false,
            subscriptionErrorText: '',
            subscriptionSuccess: false,
            subscribeLoading: false,
            chosenPlan: null,
            cancelLoading: null
        }
    },
    computed: {
        user() {
            return this.$store.state.user;
        },
        userPlan() {
            return this.$store.state.user.plan_id;
        }
    },
    methods: {
        showUpgradeDialog(planId) {
            this.upgradeSubscriptionDialog = true;
            this.stripe = Stripe(this.stripeKey);

            const elements = this.stripe.elements();
            this.card = elements.create('card', {style: {base: {fontSize: '20px'}}});

            this.card.mount('#card-element');

            this.chosenPlan = planId;
            this.subscriptionError = false;
        },
        closeUpgradeDialog() {
            this.upgradeSubscriptionDialog = false;
        },
        async onSubscribe() {
            this.subscriptionSuccess = false;
            this.subscriptionError = false;
            this.subscribeLoading = true;
            const clientSecret = (await api.plan.createSetupIntent()).client_secret;
            const {setupIntent, error} = await this.stripe.confirmCardSetup(
                clientSecret, {
                    payment_method: {
                        card: this.card,
                        billing_details: {
                            name: `${this.user.firstname} ${this.user.lastname}`,
                            email: this.user.email,
                            phone: this.user.phone
                        }
                    }
                }
            );
            if (error) {
                this.subscriptionErrorText = error.message;
                this.subscriptionError = true;
            } else {
                const subscriptionInfo = {
                    planId: this.chosenPlan,
                    paymentMethod: setupIntent.payment_method
                };
                await this.$store.dispatch('subscribeToPlan', subscriptionInfo);
                this.subscriptionSuccess = true;
                this.upgradeSubscriptionDialog = false;
            }
            this.subscribeLoading = false;
        },
        async onSubscriptionCancel() {
            this.cancelLoading = true;
            await this.$store.dispatch('cancelSubscriptionToPlan');
            this.cancelLoading = false;
        }
    },
    async mounted() {
        this.plansLoading = true;
        this.plans = await api.plan.getAllPlans();
        this.plans.unshift({
            price: 0,
            join_count: 1
        });
        this.plansLoading = false;
    }
}
</script>

<style scoped>

</style>
