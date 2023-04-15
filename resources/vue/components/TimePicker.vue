<template>
    <v-card>
        <v-card-title class="d-flex justify-center">Time picker</v-card-title>
        <v-card-text>
            <v-row>
                <v-col cols="5" class="d-flex flex-column justify-center align-center">
                    <v-btn variant="text" @click="onHoursUp">
                        <v-icon size="x-large">mdi-chevron-up</v-icon>
                    </v-btn>
                    <span class="d-flex justify-center text-h5">{{ hours < 10 ? `0${hours}` : hours }}</span>
                    <v-btn variant="text" @click="onHoursDown">
                        <v-icon size="x-large">mdi-chevron-down</v-icon>
                    </v-btn>
                </v-col>
                <v-col cols="2" class="d-flex flex-column justify-center align-center">
                    <span class="d-flex justify-center text-h5">:</span>
                </v-col>
                <v-col cols="5" class="d-flex flex-column justify-center align-center">
                    <v-btn variant="text" @click="onMinutesUp">
                        <v-icon size="x-large">mdi-chevron-up</v-icon>
                    </v-btn>
                    <span class="d-flex justify-center text-h5">{{ minutes < 10 ? `0${minutes}` : minutes }}</span>
                    <v-btn variant="text" @click="onMinutesDown">
                        <v-icon size="x-large">mdi-chevron-down</v-icon>
                    </v-btn>
                </v-col>
            </v-row>
        </v-card-text>
        <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
                color="grey darken-1"
                text
                @click="closeTimePicker()"
            >
                Close
            </v-btn>
            <v-btn
                color="success"
                @click="confirmTime()"
            >
                Confirm
            </v-btn>
        </v-card-actions>
    </v-card>
</template>

<script>
export default {
    name: "TimePicker",
    data() {
        return {
            minutes: this.minMinutes,
            hours: this.minHours
        };
    },
    props: {
        minHours: Number,
        minMinutes: Number,
        maxHours: Number,
        maxMinutes: Number,
    },
    methods: {
        closeTimePicker() {
            this.$emit('close');
        },
        confirmTime() {
            const timestamp = new Date();
            timestamp.setHours(this.hours);
            timestamp.setMinutes(this.minutes);
            this.$emit('confirm', timestamp);
        },
        onHoursUp() {
            if ((this.hours === this.maxHours - 1 && this.minutes <= this.maxMinutes) || this.hours < this.maxHours - 1) {
                this.hours++;
            }
        },
        onHoursDown() {
            if ((this.hours === this.minHours + 1 && this.minutes >= this.minMinutes) || this.hours > this.minHours + 1) {
                this.hours--;
            }
        },
        onMinutesUp() {
            if ((this.hours < this.maxHours && this.minutes < 59) ||
                (this.hours === this.maxHours && this.minutes < this.maxMinutes)) {
                this.minutes++;
            } else if (this.minutes === 59 && this.hours < this.maxHours) {
                this.minutes = 0;
                this.hours++;
            }
        },
        onMinutesDown() {
            if ((this.hours > this.minHours && this.minutes > 0) ||
                (this.hours === this.minHours && this.minutes > this.minMinutes)) {
                this.minutes--;
            } else if (this.minutes === 0 && this.hours > this.minHours) {
                this.minutes = 59;
                this.hours--;
            }
        },
    }
}
</script>

<style scoped>

</style>
