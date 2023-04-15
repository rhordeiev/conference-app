<template>
    <v-row :class="[breakpoint === 'xs' && 'w-100', breakpoint === 'sm' && 'w-75', breakpoint === 'md' && 'w-75',
                    breakpoint === 'lg' && 'w-50', breakpoint === 'xl' && 'w-50']">
        <v-col cols="12" class="my-12">
            <QuillEditor ref="quill" theme="snow"/>
        </v-col>
        <v-col cols="12" class="d-flex justify-end">
            <v-btn @click="send" :loading="sendButtonIsLoading">Send</v-btn>
        </v-col>
    </v-row>
    <v-row :class="[breakpoint === 'xs' && 'w-100', breakpoint === 'sm' && 'w-75', breakpoint === 'md' && 'w-75',
                    breakpoint === 'lg' && 'w-50', breakpoint === 'xl' && 'w-50']">
        <v-col cols="12" v-for="comment in comments" :key="comment.id">
            <v-card v-if="!editMode[comment.id]">
                <v-card-subtitle class="d-flex justify-space-between mt-3">
                    <span>{{ comment.firstname }} {{ comment.lastname }}</span>
                    <span>{{
                        `${comment.date.getDate()} ${monthNames[comment.date.getMonth()]} ${comment.date.getFullYear()}
                         ${comment.date.getHours()}:${comment.date.getMinutes()}`
                    }}</span>
                </v-card-subtitle>
                <v-card-text v-html="comment.text"></v-card-text>
                <v-card-actions class="d-flex justify-end">
                    <v-btn color="primary" @click="edit(comment.id)" v-if="comment.user_id === userId"
                           @mouseover="editMouseOver(comment.id)" :disabled="editButtonsActive[comment.id]">Edit
                    </v-btn>
                </v-card-actions>
            </v-card>
            <div class="w-100" v-if="editMode[comment.id]">
                <div class="my-5 w-100">
                    <QuillEditor v-model:content="enteredText[comment.id]" contentType="html" theme="snow"/>
                </div>
                <div class="w-100 d-flex justify-end">
                    <v-btn @click="save(comment.id)" v-if="editMode[comment.id]"
                           :loading="saveButtonLoading[comment.id]">Save
                    </v-btn>
                </div>
            </div>
        </v-col>
        <v-col cols="12" v-if="commentLoading" class="d-flex justify-center">
            <v-progress-circular
                indeterminate
                color="surface-variant"
            ></v-progress-circular>
        </v-col>
    </v-row>
    <InfoDialog type="error" header="Edit error" message="Check if you correctly filled the inputs."
                :active="errorDialog"/>
</template>

<script>
import InfoDialog from "@/components/dialog/InfoDialog.vue";
import {api} from "@/api";

export default {
    name: "CommentSection",
    components: {InfoDialog},
    data() {
        return {
            commentContent: "",
            comments: [],
            editMode: [],
            sendButtonIsLoading: false,
            enteredText: [],
            editButtonsActive: [],
            saveButtonLoading: [],
            errorDialog: false,
            monthNames: ["January", "February", "March", "April", "May", "June",
                "July", "August", "September", "October", "November", "December"
            ],
            commentLoading: false,
            commentsLoadedCount: 0,
            timer: null,
            allCommentsLoaded: false
        }
    },
    computed: {
        userId() {
            return this.$store.state.user.id;
        },
        breakpoint() {
            return this.$vuetify.display.name;
        },
    },
    methods: {
        send() {
            this.sendButtonIsLoading = true;
            const commentText = this.$refs.quill.getText();
            const dateInMilliseconds = Date.now();
            const date = new Date(dateInMilliseconds);
            if (commentText) {
                const commentInfo = {
                    user_id: this.$store.state.user.id,
                    firstname: this.$store.state.user.firstname,
                    lastname: this.$store.state.user.lastname,
                    date,
                    text: this.$refs.quill.getHTML(),
                    report_id: this.$route.params.id,
                }
                api.comment.newComment(commentInfo).then(() => {
                    this.sendButtonIsLoading = false;
                    this.allCommentsLoaded = false;
                });
            }
        },
        editMouseOver(commentId) {
            const dateNowInMilliseconds = Date.now();
            const dateNow = new Date(dateNowInMilliseconds);
            const foundIndex = this.comments.findIndex((comment) => comment.id === commentId);
            const differenceInMinutes = Math.round((dateNow - this.comments[foundIndex].date) / 60000);
            this.editButtonsActive[commentId] = differenceInMinutes > 10;
        },
        edit(commentId) {
            this.editMode[commentId] = true;
        },
        save(commentId) {
            this.saveButtonLoading[commentId] = true;
            const foundIndex = this.comments.findIndex((comment) => comment.id === commentId);
            const editedComment = this.comments[foundIndex];
            editedComment.text = this.enteredText[commentId];
            api.comment.editComment(editedComment).then(() => {
                this.comments.splice(foundIndex, 1, editedComment);
                this.saveButtonLoading[commentId] = false;
                this.editMode[commentId] = false;
            });
        },
        loadComments(reportId, offset, limit) {
            return new Promise((resolve) => {
                api.comment.getComments(reportId, offset, limit).then(result => {
                    result = _.toArray(result);
                    if(result.length !== 0) {
                        result.forEach((comment) => {
                            let dateWithTimezone = new Date(comment.date);
                            const myTimeZone = 2;
                            dateWithTimezone.setTime(dateWithTimezone.getTime() + myTimeZone * 60 * 60 * 1000);
                            comment.date = dateWithTimezone;
                            this.enteredText[comment.id] = comment.text;
                        })
                        this.comments.push(result[0]);
                        this.commentsLoadedCount++;
                    } else {
                        this.allCommentsLoaded = true;
                    }
                    resolve();
                });
            });
        },
        async onScroll() {
            if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight && !this.commentLoading) {
                if(!this.allCommentsLoaded) {
                    this.commentLoading = true;
                    await this.loadComments(this.$route.params.id, this.commentsLoadedCount, 1);
                    this.commentLoading = false;
                }
            }
        }
    },
    watch: {
        allCommentsLoaded() {
            if(this.allCommentsLoaded === true) {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => {
                    this.allCommentsLoaded = false;
                }, 10000);
            }
        }
    },
    async mounted() {
        window.addEventListener('scroll', this.onScroll);
    },
    unmounted() {
        window.removeEventListener('scroll', this.onScroll);
    }
}
</script>

<style scoped>

</style>
