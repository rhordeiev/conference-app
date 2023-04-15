<template>
    <v-container fluid class="d-flex align-center flex-column">
        <ManageCategoriesDialog type="create" :active="showNewCategoryDialog" :categories="categories"
                          @confirm="onCategoryCreateDialogConfirm" @close="onCategoryCreateDialogClose"/>
        <span class="text-h4 my-6">Categories</span>
        <div :class="[breakpoint === 'xs' && 'w-100', breakpoint === 'sm' && 'w-75', breakpoint === 'md' && 'w-50',
            breakpoint === 'lg' && 'w-50', breakpoint === 'xl' && 'w-50', 'd-flex', 'flex-column', 'align-center']"
             v-if="!categoryTreeBuilding">
            <v-btn variant="text" color="primary" @click="onCategoryCreateClick" class="align-self-end">
                Create
            </v-btn>
            <CategoryTreeView
                v-for="child in categoriesTree"
                :key="child.name"
                :node="child"
                :categories="categories"
                @change="resetCategories"
            />
        </div>
        <v-progress-circular
            indeterminate
            color="surface-variant"
            v-else
        ></v-progress-circular>
    </v-container>
</template>

<script>
import CategoryTreeView from "@/components/CategoryTreeView.vue";
import buildCategoryTree from "@/helpers/buildCategoryTree"
import {api} from "@/api";
import ManageCategoriesDialog from "@/components/dialog/ManageCategoriesDialog.vue";

export default {
    name: "CategoryView",
    components: {ManageCategoriesDialog, CategoryTreeView},
    data() {
        return {
            categoryTreeBuilding: false,
            categories: [],
            categoriesTree: [],
            showNewCategoryDialog: false
        }
    },
    methods: {
        async resetCategories() {
            this.categoryTreeBuilding = true;
            this.categories = await api.category.getCategories();
            this.categoriesTree = buildCategoryTree(this.categories);
            this.categoryTreeBuilding = false;
        },
        onCategoryCreateClick() {
            this.showNewCategoryDialog = true;
        },
        onCategoryCreateDialogClose() {
            this.showNewCategoryDialog = false;
        },
        async onCategoryCreateDialogConfirm() {
            await this.resetCategories();
            this.showNewCategoryDialog = false;
        }
    },
    computed: {
        breakpoint() {
            return this.$vuetify.display.name;
        },
    },
    async mounted() {
        await this.resetCategories();
    }
}
</script>

<style scoped>
</style>
