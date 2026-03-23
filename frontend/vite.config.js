import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) }
  },
  server: {
    port: 5173,
    proxy: {
      // Redirige /api al backend PHP en Laragon.
      // Laragon crea el dominio según la carpeta en www/.
      // Ejemplo: si la carpeta es "rentcar", el dominio es http://rentcar.test
      // Ajusta el target si tu dominio de Laragon es diferente:
      '/api': {
        target: 'http://rentcar.test',  // ← cambia si tu dominio Laragon es diferente
        changeOrigin: true,
        rewrite: (path) => path,        // mantiene /api/... tal cual
      }
    }
  }
})
