export default function buildCategoryTree(categories, parentId = null) {
    let branch = [];

    categories.forEach((category) => {
        if (category.parent_id === parentId) {
            let children = buildCategoryTree(categories, category.id);
            if (children) {
                category.children = children;
            }
            branch.push(category);
        }
    })

    return branch;
}
