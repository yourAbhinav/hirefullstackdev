window.DevHireFirebase = window.DevHireFirebase || {};

function ensureFirebaseDataServices() {
  if (typeof firebase === 'undefined') {
    throw new Error('Firebase compat SDK must be loaded before firebase-data.js.');
  }

  if (typeof firebase.firestore !== 'function' || typeof firebase.storage !== 'function') {
    throw new Error('Firebase Firestore and Storage compat SDKs must be loaded before firebase-data.js.');
  }

  const app = window.DevHireFirebase.ensureFirebaseApp ? window.DevHireFirebase.ensureFirebaseApp() : firebase.app();
  return {
    app,
    firestore: firebase.firestore(),
    storage: firebase.storage()
  };
}

function getFirebaseFirestore() {
  return ensureFirebaseDataServices().firestore;
}

function logFirestoreWrite({ collection, operation, payload, result, error }) {
  try {
    const auth = firebase.auth && firebase.auth();
    const user = auth && auth.currentUser ? auth.currentUser : null;
    console.groupCollapsed(`[Firestore] ${operation} -> ${collection}`);
    console.log('uid:', user ? user.uid : null);
    console.log('email:', user ? user.email : null);
    console.log('payload:', payload);
    if (result !== undefined) console.log('result:', result);
    if (error !== undefined) console.error('error:', error);
    console.groupEnd();
  } catch (e) {
    // silent
  }
}

function getFirebaseStorage() {
  return ensureFirebaseDataServices().storage;
}

function firebaseServerTimestamp() {
  return firebase.firestore.FieldValue.serverTimestamp();
}

async function uploadResumeToFirebase(file, userId) {
  if (!file) {
    return null;
  }

  const storage = getFirebaseStorage();
  const safeName = String(file.name || 'resume').replace(/[^a-zA-Z0-9._-]/g, '_');
  const filePath = `resumes/${userId}/${Date.now()}-${safeName}`;
  const ref = storage.ref().child(filePath);
  const snapshot = await ref.put(file, {
    contentType: file.type || 'application/octet-stream'
  });
  const downloadURL = await snapshot.ref.getDownloadURL();

  return {
    path: snapshot.ref.fullPath,
    url: downloadURL,
    name: file.name || 'resume'
  };
}

async function getFirebaseStorageDownloadUrl(storagePath) {
  if (!storagePath) {
    return '';
  }

  const storage = getFirebaseStorage();
  return storage.ref(storagePath).getDownloadURL();
}

async function createApplicationInFirestore(applicationData) {
  const firestore = getFirebaseFirestore();
  const payload = {
    ...applicationData,
    status: applicationData.status || 'pending',
    created_at: firebaseServerTimestamp(),
    updated_at: firebaseServerTimestamp()
  };

  try {
    const docRef = await firestore.collection('applications').add(payload);
    logFirestoreWrite({ collection: 'applications', operation: 'add', payload, result: { id: docRef.id } });
    return docRef.id;
  } catch (err) {
    logFirestoreWrite({ collection: 'applications', operation: 'add', payload, error: err });
    throw err;
  }
}

async function upsertUserProfileInFirestore(userId, profileData) {
  const firestore = getFirebaseFirestore();
  const profileRef = firestore.collection('user_profiles').doc(String(userId));
  const snapshot = await profileRef.get();

  const payload = {
    ...profileData,
    user_id: profileData.user_id ?? userId,
    updated_at: firebaseServerTimestamp(),
    created_at: snapshot.exists && snapshot.data() && snapshot.data().created_at
      ? snapshot.data().created_at
      : firebaseServerTimestamp()
  };

  try {
    await profileRef.set(payload, { merge: true });
    logFirestoreWrite({ collection: 'user_profiles', operation: 'set', payload, result: { id: profileRef.id } });
    return profileRef.id;
  } catch (err) {
    logFirestoreWrite({ collection: 'user_profiles', operation: 'set', payload, error: err });
    throw err;
  }
}

async function createMessageInFirestore(messageData) {
  const firestore = getFirebaseFirestore();
  const payload = {
    ...messageData,
    read_status: messageData.read_status || false,
    created_at: firebaseServerTimestamp(),
    updated_at: firebaseServerTimestamp()
  };

  try {
    const docRef = await firestore.collection('messages').add(payload);
    logFirestoreWrite({ collection: 'messages', operation: 'add', payload, result: { id: docRef.id } });
    return docRef.id;
  } catch (err) {
    logFirestoreWrite({ collection: 'messages', operation: 'add', payload, error: err });
    throw err;
  }
}

// Generic helpers for instrumentation
async function addFirestoreDoc(collection, data) {
  const firestore = getFirebaseFirestore();
  const payload = { ...data };
  try {
    const docRef = await firestore.collection(collection).add(payload);
    logFirestoreWrite({ collection, operation: 'add', payload, result: { id: docRef.id } });
    return docRef.id;
  } catch (err) {
    logFirestoreWrite({ collection, operation: 'add', payload, error: err });
    throw err;
  }
}

async function setFirestoreDoc(collection, docId, data, options = {}) {
  const firestore = getFirebaseFirestore();
  const payload = { ...data };
  try {
    await firestore.collection(collection).doc(String(docId)).set(payload, options);
    logFirestoreWrite({ collection, operation: 'set', payload, result: { id: String(docId) } });
    return String(docId);
  } catch (err) {
    logFirestoreWrite({ collection, operation: 'set', payload, error: err });
    throw err;
  }
}

async function updateFirestoreDoc(collection, docId, data) {
  const firestore = getFirebaseFirestore();
  const payload = { ...data };
  try {
    await firestore.collection(collection).doc(String(docId)).update(payload);
    logFirestoreWrite({ collection, operation: 'update', payload, result: { id: String(docId) } });
    return String(docId);
  } catch (err) {
    logFirestoreWrite({ collection, operation: 'update', payload, error: err });
    throw err;
  }
}

async function deleteFirestoreDoc(collection, docId) {
  const firestore = getFirebaseFirestore();
  try {
    await firestore.collection(collection).doc(String(docId)).delete();
    logFirestoreWrite({ collection, operation: 'delete', payload: { id: String(docId) }, result: { id: String(docId) } });
    return true;
  } catch (err) {
    logFirestoreWrite({ collection, operation: 'delete', payload: { id: String(docId) }, error: err });
    throw err;
  }
}

function listenToMessagesForUser(userEmail, queryBuilder, onChange, onError) {
  const firestore = getFirebaseFirestore();
  return queryBuilder(firestore, userEmail).onSnapshot((snapshot) => {
    const messages = snapshot.docs.map((doc) => ({
      id: doc.id,
      ...doc.data()
    }));
    onChange(messages);
  }, onError);
}

function listenToApplications(queryBuilder, onChange, onError) {
  const firestore = getFirebaseFirestore();
  return queryBuilder(firestore).onSnapshot((snapshot) => {
    const applications = snapshot.docs.map((doc) => ({
      id: doc.id,
      ...doc.data()
    }));
    onChange(applications);
  }, onError);
}

function applicationTimestampToDate(value) {
  if (!value) {
    return null;
  }

  if (typeof value.toDate === 'function') {
    return value.toDate();
  }

  if (typeof value.seconds === 'number') {
    return new Date(value.seconds * 1000);
  }

  return new Date(value);
}

window.DevHireFirebase = {
  ...window.DevHireFirebase,
  ensureFirebaseDataServices,
  getFirebaseFirestore,
  getFirebaseStorage,
  firebaseServerTimestamp,
  uploadResumeToFirebase,
  getFirebaseStorageDownloadUrl,
  createApplicationInFirestore,
  upsertUserProfileInFirestore,
  createMessageInFirestore,
  listenToMessagesForUser,
  listenToApplications,
  applicationTimestampToDate
  ,
  // instrumentation helpers
  addFirestoreDoc,
  setFirestoreDoc,
  updateFirestoreDoc,
  deleteFirestoreDoc
};