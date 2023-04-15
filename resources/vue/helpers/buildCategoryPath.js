export default function buildCategoryPath(categories, categoryId) {
    const categoryPathArray = [];
    let currentId = categoryId;
    let currentCategory = {};
    do {
        let categoryIndex = categories.findIndex((category) => category.id === currentId);
        currentCategory = categories[categoryIndex];
        currentId = currentCategory.parent_id;
        currentCategory.href = `/category/${currentCategory.id}`;
        categoryPathArray.push(currentCategory);
    } while(currentCategory.parent_id !== null);

    return categoryPathArray;
}
