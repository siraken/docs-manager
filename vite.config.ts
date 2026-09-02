import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { svelte } from "@sveltejs/vite-plugin-svelte";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/ts/app.ts"],
      refresh: true,
    }),
    svelte(),
    tailwindcss(),
  ],
  resolve: {
    alias: {
      "@": "/resources/ts",
    },
  },
});
