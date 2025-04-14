import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import fg from "fast-glob";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                ...fg.sync("resources/js/**/*.js"), // Usa fast-glob per risolvere i percorsi dei file JS
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
