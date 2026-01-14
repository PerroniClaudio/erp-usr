import { animate } from "framer-motion";

const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
).matches;

const runHomepageMotion = () => {
    const homepage = document.querySelector("[data-homepage]");
    if (!homepage) return;

    const items = homepage.querySelectorAll(
        "[data-home-card], [data-home-panel]"
    );
    if (!items.length) return;

    items.forEach((item, index) => {
        animate(
            item,
            { opacity: [0, 1], y: [16, 0], filter: ["blur(6px)", "blur(0px)"] },
            {
                duration: 0.45,
                delay: index * 0.06,
                ease: [0.21, 0.47, 0.32, 0.99],
            }
        );
    });
};

const runSidebarMotion = () => {
    const sidebar = document.querySelector("[data-sidebar]");
    if (!sidebar) return;

    if (sidebar.dataset.motionPlayed === "true") return;

    const items = sidebar.querySelectorAll("li");
    if (!items.length) return;

    items.forEach((item, index) => {
        animate(
            item,
            { opacity: [0, 1], x: [-12, 0] },
            { duration: 0.35, delay: index * 0.03, ease: "easeOut" }
        );
    });

    sidebar.dataset.motionPlayed = "true";
};

const runUserSidebarMotion = () => {
    const sidebar = document.querySelector("[data-user-sidebar]");
    if (!sidebar) return;

    if (sidebar.dataset.motionPlayed === "true") return;

    const items = sidebar.querySelectorAll("li");
    if (!items.length) return;

    items.forEach((item, index) => {
        animate(
            item,
            { opacity: [0, 1], x: [-10, 0] },
            { duration: 0.3, delay: index * 0.02, ease: "easeOut" }
        );
    });

    sidebar.dataset.motionPlayed = "true";
};

const runUserPageMotion = () => {
    const page = document.querySelector("[data-userpage]");
    if (!page) return;

    const hero = page.querySelectorAll("[data-user-hero]");
    hero.forEach((item, index) => {
        animate(
            item,
            { opacity: [0, 1], y: [12, 0] },
            { duration: 0.4, delay: index * 0.04, ease: "easeOut" }
        );
    });

    window.addEventListener("user-section-change", (event) => {
        const section = event.detail?.section;
        if (!section) return;

        animate(
            section,
            { opacity: [0, 1], y: [12, 0] },
            { duration: 0.35, ease: "easeOut" }
        );
    });
};

if (!prefersReducedMotion) {
    runHomepageMotion();
    runUserPageMotion();

    const drawerToggle = document.getElementById("main-drawer");
    const isDesktop = () => window.matchMedia("(min-width: 1024px)").matches;

    const maybeRunSidebarMotion = () => {
        if (isDesktop() || drawerToggle?.checked) {
            runSidebarMotion();
        }
    };

    maybeRunSidebarMotion();

    if (drawerToggle) {
        drawerToggle.addEventListener("change", maybeRunSidebarMotion);
    }

    window.addEventListener("resize", maybeRunSidebarMotion, { passive: true });

    const userDrawerToggle = document.getElementById("user-drawer");
    const maybeRunUserSidebarMotion = () => {
        if (isDesktop() || userDrawerToggle?.checked) {
            runUserSidebarMotion();
        }
    };

    maybeRunUserSidebarMotion();

    if (userDrawerToggle) {
        userDrawerToggle.addEventListener("change", maybeRunUserSidebarMotion);
    }

    window.addEventListener("resize", maybeRunUserSidebarMotion, {
        passive: true,
    });
}
