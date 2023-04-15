<template>
    <div class="w-100">
        <v-card
            @click="onNodeClick"
            class="pa-3 my-1 text-h6 d-flex flex-row"
            :style="{
                'width': `calc(100% - ${depth*30}px)`,
                'margin-left': `${depth*30}px`
            }"
        >
            <v-icon v-if="expanded">mdi-chevron-down</v-icon>
            <v-icon v-else>mdi-chevron-right</v-icon>
            {{ node.name }}
            <v-spacer/>
            <v-btn color="warning" flat variant="text" :id="node.id" @click="onEditClick($event)">
                <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn color="error" flat variant="text" :id="node.id" @click="onDeleteClick($event)">
                <v-icon>mdi-delete</v-icon>
            </v-btn>
        </v-card>
        <v-slide-y-transition>
            <div v-if="expanded" class="align-self-start">
                <CategoryTreeView
                    v-for="child in node.children"
                    :key="child.name"
                    :node="child"
                    :categories="categories"
                    :depth="depth+1"
                    @change="this.$emit('change')"
                />
            </div>
        </v-slide-y-transition>
    </div>
    <ConfirmDialog type="delete" :active="confirmDeleteDialog" header="Removal confirmation"
                   message="Do you really want to delete?" :is-loading="isDeleteLoading"
                   @confirm="onDeleteConfirm" @close="confirmDeleteDialog = false"/>
    <ManageCategoriesDialog type="edit" :active="confirmEditDialog" :categories="categories"
                            @confirm="onCategoryEditDialogConfirm" @close="onCategoryEditDialogClose"
                            :category-to-edit="clickedCategoryId"/>
</template>

<script>
import ConfirmDialog from "@/components/dialog/ConfirmDialog.vue";
import {api} from "@/api";
import ManageCategoriesDialog from "@/components/dialog/ManageCategoriesDialog.vue";

export default {
    name: "CategoryTreeView",
    components: {ManageCategoriesDialog, ConfirmDialog},
    props: {
        node: Object,
        depth: {
            type: Number,
            default: 0
        },
        categories: {
            type: Array
        }
    },
    data() {
        return {
            expanded: false,
            confirmDeleteDialog: false,
            isDeleteLoading: false,
            confirmEditDialog: false,
            isSaveLoading: false,
            clickedCategoryId: null,
        }
    },
    computed: {
        breakpoint() {
            return this.$vuetify.display.name;
        }
    },
    methods: {
        onNodeClick() {
            this.expanded = !this.expanded;
        },
        onDeleteClick(event) {
            this.clickedCategoryId = parseInt(event.currentTarget.id);
            this.confirmDeleteDialog = true;
        },
        async onDeleteConfirm() {
            this.isDeleteLoading = true;
            await api.category.deleteCategory(this.clickedCategoryId);
            this.isDeleteLoading = false;
            this.confirmDeleteDialog = false;
            this.$emit('change');
        },
        onEditClick(event) {
            this.clickedCategoryId = parseInt(event.currentTarget.id);
            this.confirmEditDialog = true;
        },
        async onCategoryEditDialogConfirm() {
            this.confirmEditDialog = false;
            this.$emit('change');
        },
        onCategoryEditDialogClose() {
            this.confirmEditDialog = false;
        }
    }
}
</script>

<style scoped>

</style>
