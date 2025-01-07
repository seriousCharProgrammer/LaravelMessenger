import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                "resources/js/messenger.js",
            ],
            refresh: true,
        }),
    ],
    server: {
        host: "0.0.0.0", // Allow external connections
        port: 3000, // Specify the port if needed
    },
});
