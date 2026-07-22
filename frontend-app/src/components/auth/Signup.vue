<template>
  <div class="register-page bg-body-secondary app-loaded">
    <main class="register-box" id="main" tabindex="-1">
      <h1 class="register-logo">
        <a href="../index2.html"><b>Create</b> Task Account s</a>
      </h1>
      <!-- /.register-logo -->
      <div class="card">
        <div class="card-body register-card-body">
          <p class="register-box-msg">Register a new Account</p>

          <form @submit.prevent="signUp">
            <label class="visually-hidden" for="registerName">Full Name</label>
            <div class="input-group mb-3">
              <input type="text" v-model="user.name" class="form-control" placeholder="Name"
                :class="{ 'is-invalid': !!userError.name }" />
              <div class="input-group-text">
                <span class="bi bi-person"></span>
              </div>
              <div class="invalid-feedback">
                {{ userError.name }}
              </div>
            </div>
            <label class="visually-hidden" for="registerEmail">Email</label>
            <div class="input-group mb-3">
              <input type="email" v-model="user.email" class="form-control" placeholder="Email"
                :class="{ 'is-invalid': !!userError.email }" />
              <div class="input-group-text">
                <span class="bi bi-envelope"></span>
              </div>
              <div class="invalid-feedback">
                {{ userError.email }}
              </div>
            </div>
            <label class="visually-hidden" for="registerPassword">Password</label>
            <div class="input-group mb-3">
              <input type="password" v-model="user.password" class="form-control" placeholder="Password" autocomplete
                :class="{ 'is-invalid': !!userError.password }" />
              <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
              </div>
              <div class="invalid-feedback">
                {{ userError.password }}
              </div>
            </div>
            <div class="input-group mb-3">
              <input type="password" v-model="user.password_confirmation" class="form-control"
                placeholder="Confirm Password" autocomplete />
              <div class="input-group-text">
                <span class="bi bi-lock-fill"></span>
              </div>
            </div>
            <!--begin::Row-->
            <div class="row">
              <div class="col-8">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">
                  <label class="form-check-label" for="flexCheckDefault">
                    I agree to the <a href="#">terms</a>
                  </label>
                </div>
              </div>
              <!-- /.col -->
              <div class="col-4">
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary">Register</button>
                </div>
              </div>
              <!-- /.col -->
            </div>
            <!--end::Row-->
          </form>

          <div class="social-auth-links text-center mb-3 d-grid gap-2">
            <p>- OR -</p>
            <a href="#" class="btn btn-danger">
              <i class="bi bi-google me-2"></i> Sign up using Google
            </a>
          </div>
          <!-- /.social-auth-links -->

          <p class="mb-0">
            <router-link :to="{ name: 'auth.signin' }" class="text-center">I already have an account</router-link>
          </p>
        </div>
        <!-- /.register-card-body -->
        <hr>
        <div v-if="signedUpEmail" class="mt-3">
          <p>Signed up with <strong>{{ signedUpEmail }}</strong></p>
          <p class="mb-3">
            Didn't receive the verification email?
          </p>
          <button @click="sendVerificationEmail" class="btn btn-secondary btn-block">Resend Verification
            Email</button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from "vue-router";
import { reactive, ref } from "vue";
import { apiSignUp, apiSendVerificationEmail } from "@/functions/api/auth";
import { LoadingModal, MessageModal, CloseModal } from "@/functions/swal";
const router = useRouter();

const user = reactive({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
});

const userError = reactive({
  name: "",
  email: "",
  password: "",
});

const defaultUser = JSON.parse(JSON.stringify(user));
const defaultUserError = JSON.parse(JSON.stringify(userError));

function resetAllState() {
  Object.assign(user, defaultUser);
  Object.assign(userError, defaultUserError);
}

async function signUp() {
  resetSignedUpEmail();
  try {
    LoadingModal('Signing Up...');
    await apiSignUp(user);
    signedUpEmail.value = user.email;
    resetAllState();
    return MessageModal({
      icon: "success",
      title: "Success",
      text: "Your account has been created successfully."
    });
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

const signedUpEmail = ref("");
async function sendVerificationEmail() {
  try {
    LoadingModal('Requesting verification email...');
    const response = await apiSendVerificationEmail(signedUpEmail.value);
    const { data } = response;
    return MessageModal({
      icon: "success",
      title: "Success",
      text: data.message
    });
  } catch (error) {
    const { response } = error;
    if (!response) {
      return MessageModal({ icon: "error", title: "Error", text: error.message });
    }
    const { data } = response;
    return MessageModal({ icon: "error", title: "Error", text: data.message });
  }
}
function resetSignedUpEmail() {
  signedUpEmail.value = "";
}
</script>
