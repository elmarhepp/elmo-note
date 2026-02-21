<template>
  <div class="auth-page">
    <div class="auth-card">
      <h1 class="auth-title">elmo-note</h1>
      <p class="auth-subtitle">Anmelden</p>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label>E-Mail</label>
          <input v-model="email" type="email" placeholder="deine@email.de" required autofocus />
        </div>
        <div class="form-group">
          <label>Passwort</label>
          <input v-model="password" type="password" placeholder="••••••••" required />
        </div>
        <p v-if="error" class="error">{{ error }}</p>
        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Anmelden...' : 'Anmelden' }}
        </button>
      </form>

      <p class="auth-link">
        Noch kein Konto? <router-link to="/register">Registrieren</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''
  loading.value = true
  try {
    await auth.login(email.value, password.value)
    router.push('/')
  } catch (e) {
    error.value = e.response?.data?.message || 'Anmeldung fehlgeschlagen.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.auth-page {
  width: 100%;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--color-bg);
}

.auth-card {
  background: var(--color-white);
  border: 1px solid var(--color-border);
  border-radius: 12px;
  padding: 2.5rem;
  width: 380px;
}

.auth-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--color-accent);
  margin-bottom: 0.25rem;
}

.auth-subtitle {
  color: var(--color-text-muted);
  margin-bottom: 2rem;
  font-size: 0.95rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 0.35rem;
  color: var(--color-text);
}

.form-group input {
  width: 100%;
  padding: 0.6rem 0.8rem;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  font-size: 0.95rem;
  outline: none;
  transition: border-color 0.15s;
}

.form-group input:focus {
  border-color: var(--color-accent);
}

.btn-primary {
  width: 100%;
  padding: 0.7rem;
  background: var(--color-accent);
  color: white;
  border-radius: 6px;
  font-size: 0.95rem;
  font-weight: 600;
  margin-top: 0.5rem;
  transition: background 0.15s;
}

.btn-primary:hover:not(:disabled) {
  background: var(--color-accent-hover);
}

.btn-primary:disabled {
  opacity: 0.6;
}

.error {
  color: #e53e3e;
  font-size: 0.85rem;
  margin-bottom: 0.5rem;
}

.auth-link {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.85rem;
  color: var(--color-text-muted);
}

.auth-link a {
  color: var(--color-accent);
  text-decoration: none;
  font-weight: 500;
}
</style>
