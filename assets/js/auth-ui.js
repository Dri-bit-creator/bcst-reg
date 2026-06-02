import { firebaseReady, auth, db, createUserWithEmailAndPassword, signInWithEmailAndPassword, signOut, onAuthStateChanged, sendPasswordResetEmail, doc, setDoc, getDoc, serverTimestamp, GoogleAuthProvider, signInWithPopup } from './firebase.js';

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

  // Wait for Firebase SDK to be ready before attaching auth listeners.
  firebaseReady.then(() => {
    // Attach Google sign-in buttons on this page
    const googleButtons = document.querySelectorAll('[data-google-signin]');
    googleButtons.forEach((btn) => {
      btn.addEventListener('click', async (ev) => {
        ev.preventDefault();
        try {
          const provider = new GoogleAuthProvider();
          const userCred = await signInWithPopup(auth, provider);
          const user = userCred.user;
          // ensure user doc
          const existing = await getDoc(doc(db, 'users', user.uid));
          if (!existing.exists()) {
            await setDoc(doc(db, 'users', user.uid), {
              name: user.displayName || '',
              email: user.email || '',
              password: '',
              role: 'user',
              created_at: serverTimestamp()
            });
          }
          const finalDoc = await getDoc(doc(db, 'users', user.uid));
          const role = finalDoc.exists() ? (finalDoc.data().role || 'user') : 'user';
          if (role === 'admin') window.location.href = redirectOnAdmin;
          else window.location.href = redirectOnUser;
        } catch (err) {
          console.error('Google sign-in error:', err);
          showFlashById(flashId, err.message || 'Google sign-in failed', 'error');
        }
      });
    });

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
      // If the login form is present, don't auto-redirect — allow the page to show
      // the login UI even when an auth session exists. Explicit sign-in handlers
      // (form submit / Google sign-in) still perform their own redirects.
      if (form && document.body.contains(form)) {
        console.log('initAuthUI: skipping auto-redirect while login form is present.');
        return;
      }
      const udoc = await getDoc(doc(db, 'users', user.uid));
      const role = udoc.exists() ? (udoc.data().role || 'user') : 'user';
      if (role === 'admin') window.location.href = redirectOnAdmin;
      else window.location.href = redirectOnUser;
    });
  }).catch((err) => {
    console.error('Firebase failed to initialize in initAuthUI:', err);
    showFlashById(flashId, 'Unable to initialize authentication. Check console.', 'error');
  });
}

export function initSignUp({ formId, redirect = 'login.html', flashId = 'flash' } = {}) {
  const form = document.getElementById(formId);
  if (!form) return;
  enablePasswordToggles(form);

  firebaseReady.then(() => {
    // Attach Google sign-in buttons on this page (signup can also sign-in with Google)
    const googleButtons = document.querySelectorAll('[data-google-signin]');
    googleButtons.forEach((btn) => {
      btn.addEventListener('click', async (ev) => {
        ev.preventDefault();
        try {
          const provider = new GoogleAuthProvider();
          const userCred = await signInWithPopup(auth, provider);
          const user = userCred.user;
          const existing = await getDoc(doc(db, 'users', user.uid));
          if (!existing.exists()) {
            await setDoc(doc(db, 'users', user.uid), {
              name: user.displayName || '',
              email: user.email || '',
              password: '',
              role: 'user',
              created_at: serverTimestamp()
            });
          }
          const finalDoc = await getDoc(doc(db, 'users', user.uid));
          const role = finalDoc.exists() ? (finalDoc.data().role || 'user') : 'user';
          if (role === 'admin') window.location.href = '../admin/dashboard.html';
          else window.location.href = 'userdash.html';
        } catch (err) {
          console.error('Google sign-in error:', err);
          showFlashById(flashId, err.message || 'Google sign-in failed', 'error');
        }
      });
    });

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
        console.log('Preparing to write user doc to Firestore for uid:', userCred.user.uid);
        // Hash the password client-side before writing to Firestore (do not store plaintext)
        const passwordHash = await hashPassword(password);

        // Wait briefly for auth state to reflect the new user (avoids a rare race with security rules)
        try {
          await new Promise((resolve) => {
            let resolved = false;
            const timeout = setTimeout(() => { if (!resolved) { resolved = true; console.warn('Auth state not observed within timeout, proceeding.'); resolve(); } }, 3000);
            const unsub = onAuthStateChanged(auth, (u) => {
              if (u && u.uid === userCred.user.uid) {
                if (!resolved) { resolved = true; clearTimeout(timeout); unsub(); resolve(); }
              }
            });
          });
        } catch (waitErr) {
          console.warn('Error while waiting for auth state:', waitErr);
        }

          const userDocData = {
            name,
            email,
            password: passwordHash,
            role: 'user',
            created_at: serverTimestamp()
          };
          console.log('Writing user document for uid', userCred.user.uid, 'data:', userDocData);
          try {
            await setDoc(doc(db, 'users', userCred.user.uid), userDocData);
            console.log('setDoc resolved for uid', userCred.user.uid);
            try {
              const readBack = await getDoc(doc(db, 'users', userCred.user.uid));
              console.log('Read back user doc:', readBack.exists() ? readBack.data() : null);
            } catch (readErr) {
              console.warn('Failed to read back user doc after setDoc:', readErr);
            }
          } catch (writeErr) {
            console.error('Failed to write user document:', writeErr);
            // Re-throw so outer catch will show a flash message
            throw writeErr;
          }
        console.log('User document written successfully.');
        showFlashById(flashId, 'Account created. Redirecting to login...', 'success');
        setTimeout(() => window.location.href = redirect, 1200);
      } catch (err) {
        console.error('Registration error:', err);
        showFlashById(flashId, err.message || 'Registration failed', 'error');
      }
    });
  }).catch((err) => {
    console.error('Firebase failed to initialize in initSignUp:', err);
    showFlashById(flashId, 'Unable to initialize registration. Check console.', 'error');
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
  firebaseReady.then(() => {
    onAuthStateChanged(auth, async (user) => {
      if (!user) { window.location.href = redirectTo; return; }
      const udoc = await getDoc(doc(db, 'users', user.uid));
      const role = udoc.exists() ? (udoc.data().role || 'user') : 'user';
      if (requireAdmin && role !== 'admin') { window.location.href = redirectTo; return; }
      if (typeof onAuthorized === 'function') onAuthorized(user, udoc.exists() ? udoc.data() : null);
    });
  }).catch((err) => {
    console.error('Firebase failed to initialize in requireAuth:', err);
    window.location.href = redirectTo;
  });
}

export async function signOutAndRedirect(redirectTo = 'login.html', { wait = false, timeout = 500 } = {}) {
  try {
    // If signOut is not yet available because the SDK is still loading, wait briefly for firebaseReady.
    if (typeof signOut !== 'function') {
      try {
        await Promise.race([firebaseReady, new Promise((res) => setTimeout(res, timeout))]);
      } catch (e) {
        // ignore
      }
    }

    if (typeof signOut === 'function') {
      const p = signOut(auth);
      if (wait) {
        // Wait either for signOut to complete or the timeout to elapse
        await Promise.race([p, new Promise((res) => setTimeout(res, timeout))]);
      } else {
        // Fire-and-forget: don't block UI navigation on signOut network work
        p.catch((err) => console.warn('signOut error (background):', err));
      }
    } else {
      console.warn('signOut function not available, proceeding to redirect');
    }
  } catch (err) {
    console.warn('signOutAndRedirect error:', err);
  } finally {
    // Redirect immediately so the user sees the login page without delay
    window.location.href = redirectTo;
  }
}

export async function sendReset(email) {
  return sendPasswordResetEmail(auth, email);
}
