import { auth, db, createUserWithEmailAndPassword, signInWithEmailAndPassword, signOut, onAuthStateChanged, sendPasswordResetEmail, doc, setDoc, getDoc } from './firebase.js';

function showFlashById(id, message, type = 'info') {
  const el = document.getElementById(id);
  if (!el) return;
  el.innerHTML = `<div class="alert alert--${type}" role="alert">${message}</div>`;
  setTimeout(() => { el.innerHTML = ''; }, 4000);
}

export function initAuthUI({ formId, redirectOnAdmin = '../admin/dashboard.html', redirectOnUser = 'userdash.html', flashId = 'flash' } = {}) {
  const form = document.getElementById(formId);
  if (!form) return;
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
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const name = form.querySelector('#name').value.trim();
    const email = form.querySelector('#email').value.trim();
    const password = form.querySelector('#password').value;
    const confirm = form.querySelector('#confirm_password').value;
    if (password !== confirm) { showFlashById(flashId, 'Passwords do not match.', 'error'); return; }
    try {
      const userCred = await createUserWithEmailAndPassword(auth, email, password);
      await setDoc(doc(db, 'users', userCred.user.uid), { name, email, role: 'user' });
      showFlashById(flashId, 'Account created. Redirecting to login...', 'success');
      setTimeout(() => window.location.href = redirect, 1200);
    } catch (err) {
      showFlashById(flashId, err.message || 'Registration failed', 'error');
    }
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
