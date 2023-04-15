<template>
    <v-dialog v-model="dialogActive" width="500">
        <v-form v-model="valid" ref="form"
                class="d-flex align-center justify-center flex-column bg-white elevation-2 rounded pa-3"
                @submit.prevent="submit">
            <span class="text-h4 mb-3">{{ dialogHeader }}</span>
            <span
                class="text-caption text-grey align-self-start ma-2">Please fill the fields to create a category</span>
            <v-text-field label="Category name" v-model="name" :rules="nameRules" class="w-100"
                          counter="255"></v-text-field>
            <v-select label="Parent category" v-model="parentId" clearable
                      class="w-100"
                      :items="availableCategories"
                      item-title="name"
                      v-if="type === 'create' || initialParentId"
                      item-value="id">
            </v-select>
            <v-btn-group class="w-100">
                <v-spacer></v-spacer>
                <v-btn color="primary" variant="text" type="submit" :loading="isCreateLoading"
                       v-if="this.type === 'create'">
                    Create
                </v-btn>
                <v-btn color="primary" variant="text" type="submit" :loading="isSaveLoading"
                       v-else>
                    Save
                </v-btn>
                <v-btn color="grey-lighten-1" variant="text" @click="onDialogClose">
                    Close
                </v-btn>
            </v-btn-group>
        </v-form>
    </v-dialog>
</template>

<script>
import {validationText} from "@/config";
import {api} from "@/api";

export default {
    name: "ManageCategoriesDialog",
    props: {
        active: {
            type: Boolean
        },
        type: {
            type: String
        },
        categories: {
            type: Array
        },
        categoryToEdit: {
            type: Object
        }
    },
    data() {
        return {
            valid: false,
            name: '',
            isCreateLoading: false,
            isSaveLoading: false,
            nameRules: [
                v => !!v || `Category name ${validationText.required}`,
                v => (v && v.length >= 2) || `Category name ${validationText.min2letters}`,
                v => (v && v.length <= 255) || `Category name ${validationText.max255letters}`,
            ],
            initialParentId: null,
            parentId: null
        }
    },
    computed: {
        dialogActive() {
            return this.active;
        },
        dialogHeader() {
            return this.type === 'create' ? 'New category' : 'Edit category'
        },
        filteredCategories() {
            return this.categories.filter((category) => category.id !== this.categoryToEdit);
        },
        availableCategories() {
            return this.type === 'create' ? this.categories : this.filteredCategories;
        }
    },
    watch: {
        async active() {
            if(this.dialogActive && this.type === 'edit') {
                this.isSaveLoading = true;
                const foundCategory = await api.category.getCategoryById(this.categoryToEdit);
                this.name = foundCategory.name;
                this.parentId = foundCategory.parent_id;
                this.initialParentId = this.parentId;
                this.isSaveLoading = false;
            }
        }
    },
    methods: {
        async submit() {
            const formValidation = await this.$refs.form.validate();
            if (formValidation.valid) {
                const categoryInfo = {
                    name: this.name,
                    parent_id: this.parentId
                }
                if (this.type === 'create') {
                    this.isCreateLoading = true;
                    await api.category.newCategory(categoryInfo);
                    this.isCreateLoading = false;
                } else {
                    this.isSaveLoading = true;
                    categoryInfo.id = this.categoryToEdit;
                    await api.category.editCategory(categoryInfo);
                    this.isSaveLoading = false;
                }
                this.$emit('confirm');
            }
        },
        onDialogClose() {
            this.$emit('close');
        }
    }
}
</script>

<style scoped>

</style>
