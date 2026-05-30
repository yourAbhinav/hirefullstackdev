/**
 * FIREBASE SECURITY REQUIREMENTS - PRODUCTION CHECKLIST
 * =====================================================
 *
 * 1. API KEY RESTRICTIONS (Cloud Console > APIs & Services > Credentials)
 *    - Restrict by application (bundle ID / package name)
 *    - Restrict by Android/iOS app restrictions
 *    - Restrict by HTTP referrer to your domain only
 *
 * 2. AUTHORIZED DOMAINS (Firebase Console > Authentication > Settings)
 *    - Add only your production domain(s)
 *    - Remove localhost after development
 *    - Use HTTPS in production
 *
 * 3. FIRESTORE/REALTIME DATABASE RULES (Firebase Console)
 *    - DO NOT use "allow read, write: if true" in production
 *    - Implement role-based access control (admin, company, developer)
 *    - Validate user authentication before any access
 *    - Example rule: allow read, write: if request.auth.uid == resource.data.userId
 *
 * 4. APP CHECK (Firebase Console > App Check)
 *    - Enable App Check to prevent abuse
 *    - Use reCAPTCHA v3 for web apps (no user interaction required)
 *    - Enforce App Check for Realtime Database and Cloud Storage
 *
 * 5. AUTHENTICATION SECURITY
 *    - Enforce strong password policies (minimum 8 characters, complexity)
 *    - Enable email verification before account activation
 *    - Implement account lockout after failed login attempts
 *    - Use secure session storage on server-side (not localStorage)
 *
 * 6. OAUTH PROVIDER CONFIGURATION
 *    - Google: Whitelist your web app in Google Cloud Console
 *    - Verify OAuth redirect URIs match your domain exactly
 *    - Use approved scopes only (email, profile)
 *
 * NOTE: The Firebase apiKey is public by design for web apps. Security
 * relies on the rules above, not on hiding the key.
 */

/* WARNING:
   The Firebase `apiKey` is intentionally present in frontend code for web apps.
   Ensure your Firebase project uses appropriate App Check, authentication rules,
   and domain restrictions. Do NOT rely on hiding the API key for security.
*/
window.DevHireFirebase = window.DevHireFirebase || {};

const firebaseConfig = {
  apiKey: 'AIzaSyAXU64W3PalTEkDHy0CbYkqsHBZsKH0MY0',
  authDomain: 'abhhire-e8807.firebaseapp.com',
  projectId: 'abhhire-e8807',
  storageBucket: 'abhhire-e8807.firebasestorage.app',
  messagingSenderId: '173557301887',
  appId: '1:173557301887:web:dd10d71b680477c555354a',
  measurementId: 'G-5KN443QPP4'
};

function ensureFirebaseApp() {
  if (typeof firebase === 'undefined') {
    throw new Error('Firebase compat SDK must be loaded before firebase-config.js.');
  }

  if (!firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
  }

  return firebase.app();
}

function getFirebaseAuth() {
  ensureFirebaseApp();
  return firebase.auth();
}

function createGoogleProvider() {
  ensureFirebaseApp();
  const provider = new firebase.auth.GoogleAuthProvider();
  provider.setCustomParameters({ prompt: 'select_account' });
  return provider;
}

async function setFirebasePersistence() {
  const auth = getFirebaseAuth();
  await auth.setPersistence(firebase.auth.Auth.Persistence.LOCAL);
  return auth.currentUser;
}

async function signInWithGoogle() {
  const auth = getFirebaseAuth();
  await auth.setPersistence(firebase.auth.Auth.Persistence.LOCAL);
  return auth.signInWithPopup(createGoogleProvider());
}

async function signInWithEmailAndPassword(email, password) {
  const auth = getFirebaseAuth();
  await auth.setPersistence(firebase.auth.Auth.Persistence.LOCAL);
  return auth.signInWithEmailAndPassword(email, password);
}

async function signOutFirebase() {
  const auth = getFirebaseAuth();
  await auth.signOut();
}

window.DevHireFirebase = {
  config: firebaseConfig,
  ensureFirebaseApp,
  getFirebaseAuth,
  createGoogleProvider,
  setFirebasePersistence,
  signInWithGoogle,
  signInWithEmailAndPassword,
  signOutFirebase
};

ensureFirebaseApp();
