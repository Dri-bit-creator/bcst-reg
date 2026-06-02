import { auth, db, createUserWithEmailAndPassword, signInWithEmailAndPassword, signOut, onAuthStateChanged, sendPasswordResetEmail, doc, setDoc, getDoc, serverTimestamp } from './firebase.js';

function showFlashById(id, message, type = 'info') {
  const el = document.getElementById(id);
  if (!el) return;
  el.innerHTML = `<div class="alert alert--${type}" role="alert">${message}</div>`;
  setTimeout(() => { el.innerHTML = ''; }, 4000);
}

export function initAuthUI({ formId, redirectOnAdmin = '../admin/dashboard.html', redirectOnUser = 'userdash.html', flashId = 'flash' } = {}) {
  const form = document.getElementById(formId);
  if (!form) return;
  enablePasswordToggles(form);
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = form.querySelector('#email').value.trim();
    const password = form.querySelector('#password').value;
    try {
      const userCred = await signInWithEmailAndPassword(auth, email, password);
      const udoc = await getDoc(doc(db, 'users', userCred.user.uid));
      const role = udoc.exists() ? (udoc.data().role || 'user') : 'user';
      if (role === 'admin') window.location.href = redirectOnAdmin;
      else window.location.href = redirectOnUser;
    } catch (err) {
      showFlashById(flashId, err.message || 'Sign in failed', 'error');
    }
  });

  onAuthStateChanged(auth, async (user) => {
    if (!user) return;
    const udoc = await getDoc(doc(db, 'users', user.uid));
    const role = udoc.exists() ? (udoc.data().role || 'user') : 'user';
    if (role === 'admin') window.location.href = redirectOnAdmin;
    else window.location.href = redirectOnUser;
  });
}

export function initSignUp({ formId, redirect = 'login.html', flashId = 'flash' } = {}) {
  const form = document.getElementById(formId);
  if (!form) return;
  enablePasswordToggles(form);
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = form.querySelector('#name').value.trim();
    const email = form.querySelector('#email').value.trim();
    const password = form.querySelector('#password').value;
    const confirm = form.querySelector('#confirm_password').value;
    if (password !== confirm) { showFlashById(flashId, 'Passwords do not match.', 'error'); return; }
    try {
      console.log('Attempting to create user with email:', email);
        const userCred = await createUserWithEmailAndPassword(auth, email, password);
        console.log('User created:', userCred.user && userCred.user.uid);
        console.log('Writing user doc to Firestore for uid:', userCred.user.uid);
        // Hash the password client-side before writing to Firestore (do not store plaintext)
        const passwordHash = await hashPassword(password);
        await setDoc(doc(db, 'users', userCred.user.uid), {
          name,
          email,
          password: passwordHash,
          role: 'user',
          created_at: serverTimestamp()
        });
      console.log('User document written successfully.');
      showFlashById(flashId, 'Account created. Redirecting to login...', 'success');
      setTimeout(() => window.location.href = redirect, 1200);
    } catch (err) {
      console.error('Registration error:', err);
      showFlashById(flashId, err.message || 'Registration failed', 'error');
    }
  });
}

// Simple SHA-256 hash helper using Web Crypto API
async function hashPassword(password) {
  const enc = new TextEncoder();
  const data = enc.encode(password);
  const hashBuffer = await crypto.subtle.digest('SHA-256', data);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

function enablePasswordToggles(form) {
  const pwInputs = form.querySelectorAll('input[type="password"]');
  console.log('enablePasswordToggles: found', pwInputs.length, 'password inputs');
  if (!pwInputs || pwInputs.length === 0) return;
  pwInputs.forEach((input) => {
    const field = input.closest('.form-field') || input.parentElement;
    if (!field) return;
    field.classList.add('has-toggle');

    // If input already wrapped, ensure field has class and skip
    if (input.parentElement && input.parentElement.classList && input.parentElement.classList.contains('input-with-toggle')) {
      if (!input.parentElement.querySelector('.password-toggle')) {
        // add button if missing
      } else return;
    }

    // Wrap the input in a positioned container so the toggle aligns with the input
    if (!(input.parentElement && input.parentElement.classList && input.parentElement.classList.contains('input-with-toggle'))) {
      const wrapper = document.createElement('div');
      wrapper.className = 'input-with-toggle';
      input.parentNode.insertBefore(wrapper, input);
      wrapper.appendChild(input);
    }

    const wrapper = input.parentElement;
    // Avoid adding multiple toggles
    if (wrapper.querySelector('.password-toggle')) return;

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'password-toggle';
    btn.setAttribute('aria-label', 'Show password');
    btn.innerHTML = `
      <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    `;

    btn.addEventListener('click', () => {
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      // swap icon
      btn.innerHTML = isPassword ? `
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M10.58 10.58A3 3 0 0113.42 13.42" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M2.5 12s3.5-6.5 9.5-6.5c2.02 0 3.9.5 5.42 1.3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M21.5 12s-1.86 3.45-5.1 4.9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      ` : `
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M2.5 12s3.5-6.5 9.5-6.5S21.5 12 21.5 12s-3.5 6.5-9.5 6.5S2.5 12 2.5 12z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      `;
    });

    wrapper.appendChild(btn);
    console.log('enablePasswordToggles: added toggle for', input);
  });
}

export function requireAuth({ requireAdmin = false, redirectTo = '../public/login.html', onAuthorized } = {}) {
  onAuthStateChanged(auth, async (user) => {
    if (!user) { window.location.href = redirectTo; return; }
    const udoc = await getDoc(doc(db, 'users', user.uid));
    const role = udoc.exists() ? (udoc.data().role || 'user') : 'user';
    if (requireAdmin && role !== 'admin') { window.location.href = redirectTo; return; }
    if (typeof onAuthorized === 'function') onAuthorized(user, udoc.exists() ? udoc.data() : null);
  });
}

export async function signOutAndRedirect(redirectTo = 'login.html') {
  await signOut(auth);
  window.location.href = redirectTo;
}

export async function sendReset(email) {
  return sendPasswordResetEmail(auth, email);
}
