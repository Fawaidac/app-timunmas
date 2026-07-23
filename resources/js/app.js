// import './bootstrap';
document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("sidebar");
    const backdrop = document.querySelector(".sidebar-backdrop");
    const openButton = document.querySelector("[data-sidebar-open]");
    const closeButtons = document.querySelectorAll("[data-sidebar-close]");

    const openSidebar = () => {
        if (!sidebar || !backdrop) return;
        sidebar.classList.add("open");
        backdrop.classList.add("show");
    };

    const closeSidebar = () => {
        if (!sidebar || !backdrop) return;
        sidebar.classList.remove("open");
        backdrop.classList.remove("show");
    };

    openButton?.addEventListener("click", openSidebar);
    closeButtons.forEach((button) =>
        button.addEventListener("click", closeSidebar),
    );

    const stockSearch = document.getElementById("stock-search");
    const products = document.querySelectorAll("[data-product]");

    stockSearch?.addEventListener("input", (event) => {
        const query = event.target.value.toLowerCase().trim();

        products.forEach((product) => {
            product.style.display = product.dataset.product.includes(query)
                ? ""
                : "none";
        });
    });
});
