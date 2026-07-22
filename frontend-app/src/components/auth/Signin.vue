<template>
  <div class="login-page bg-body-secondary app-loaded">
    <main class="login-box" id="main" tabindex="-1">
      <h1 class="login-logo">
        <a href="#"><b>Welcome</b> to Task System</a>
      </h1>
      <!-- /.login-logo -->
      <div class="card">
        <div class="card-body login-card-body">
          <p class="login-box-msg">Sign in to start your Account</p>

          <form @submit.prevent="signIn">
            <label class="visually-hidden" for="loginEmail">Email</label>
            <div class="input-group mb-3">
              <input v-model="user.email" type="email" class="form-control" placeholder="Email"
                :class="{ 'is-invalid': !!userError.email }">
              <div class="input-group-text">
                <span class="bi bi-envelope"></span>
              </div>
              <div class="invalid-feedback">
                {{ userError.email }}
              </div>
            </div>

            <label class="visually-hidden" for="loginPassword">Password</label>
            <div class="input-group mb-3">
              <input type="password" v-model="user.password" class="form-control" placeholder="Password" autocomplete
                :class="{ 'is-invalid': !!userError.password }">
              <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
              </div>
              <div class="invalid-feedback">
                {{ userError.password }}
              </div>
            </div>

            <!--begin::Row-->
            <div class="row">
              <div class="col-8">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                  <label class="form-check-label" for="flexCheckDefault"> Remember Me </label>
                </div>
              </div>
              <!-- /.col -->
              <div class="col-4">
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary">Sign In</button>
                </div>
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->
          </form>

          <div class="social-auth-links text-center mb-3 d-grid gap-2">
            <p>- OR -</p>
            <a href="#" class="btn btn-danger">
              <i class="bi bi-google me-2"></i> Sign in using Google
            </a>
          </div>
          <!-- /.social-auth-links -->

          <p class="mb-0 text-center">
            <router-link :to="{ name: 'auth.reset-password' }" class="link-offset-2 link-underline link-underline-opacity-0">Forgot your password?</router-link>
          </p>
          <p class="mb-0 text-center">
            <router-link :to="{ name: 'auth.signup' }" class="link-offset-2 link-underline link-underline-opacity-0">Signup a new Account</router-link>
          </p>
        </div>
        <!-- /.login-card-body -->
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";
import { reactive } from "vue";
import { apiSignIn } from "@/functions/api/auth";
import { LoadingModal, MessageModal, CloseModal } from "@/functions/swal";
import { useUserStore } from "@/stores/user";
const router = useRouter();
const userStore = useUserStore();

const user = reactive({
  email: "",
  password: "",
});

const userError = reactive({
  email: "",
  password: "",
});

const defaultUser = JSON.parse(JSON.stringify(user));
const defaultUserError = JSON.parse(JSON.stringify(userError));

function resetAllState() {
  Object.assign(user, defaultUser);
  Object.assign(userError, defaultUserError);
}

async function signIn() {
  try {
    LoadingModal('Signing In...');
    const response = await apiSignIn(user);
    const { data } = response;
    userStore.setState(data.user);
    userStore.setSanctumToken(data.token);
    resetAllState();
    router.replace({ name: "dashboard" });
    return CloseModal();
  } catch (error) {
    const { response } = error;
    if (!response) {
      return MessageModal({ icon: "error", title: "Error", text: error.message });
    }
    const { status, data } = response;
    if (status === 422) {
      Object.keys(userError).forEach((key) => {
        userError[key] = data.errors[key]
          ? data.errors[key][0]
          : "";
      });
      return CloseModal();
    }
    return MessageModal({ icon: "error", title: "Error", text: data.message });
  }
}
</script>
