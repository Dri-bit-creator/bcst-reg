import { firebaseConfig } from './firebase-config.js';

// Live bindings exported here; they'll be assigned once the SDK is loaded.
export let auth = null;
export let db = null;
export let createUserWithEmailAndPassword = null;
export let signInWithEmailAndPassword = null;
export let signOut = null;
export let onAuthStateChanged = null;
export let sendPasswordResetEmail = null;
export let doc = null;
export let setDoc = null;
export let getDoc = null;
export let addDoc = null;
export let collection = null;
export let getDocs = null;
export let updateDoc = null;
export let query = null;
export let where = null;
export let orderBy = null;
export let serverTimestamp = null;

// Promise that resolves when Firebase SDK is loaded and initialized.
export const firebaseReady = (async function loadFirebase() {
	const cdnVersions = ['9.24.0', '9.23.0', '9.22.0', '9.21.0'];
	let appModule, authModule, firestoreModule, versionUsed = null;

	for (const v of cdnVersions) {
		const base = `https://www.gstatic.com/firebasejs/${v}/`;
		try {
			appModule = await import(`${base}firebase-app.js`);
			authModule = await import(`${base}firebase-auth.js`);
			firestoreModule = await import(`${base}firebase-firestore.js`);
			versionUsed = v;
			console.log('Loaded Firebase SDK from CDN version', v);
			break;
		} catch (err) {
			console.warn('Failed to load Firebase SDK from CDN version', v, err && err.message);
		}
	}

	if (!appModule) {
		// Try local vendor files
		const localBase = new URL('./vendor/', import.meta.url).href;
		try {
			appModule = await import(`${localBase}firebase-app.js`);
			authModule = await import(`${localBase}firebase-auth.js`);
			firestoreModule = await import(`${localBase}firebase-firestore.js`);
			versionUsed = 'local';
			console.log('Loaded Firebase SDK from local vendor');
		} catch (err) {
			console.error('Failed to load Firebase SDK from CDN and local vendor', err && err.message);
			throw err;
		}
	}

	const { initializeApp } = appModule;
	const {
		getAuth,
		createUserWithEmailAndPassword: _createUserWithEmailAndPassword,
		signInWithEmailAndPassword: _signInWithEmailAndPassword,
		signOut: _signOut,
		onAuthStateChanged: _onAuthStateChanged,
		sendPasswordResetEmail: _sendPasswordResetEmail
	} = authModule;
	const {
		getFirestore,
		doc: _doc,
		setDoc: _setDoc,
		getDoc: _getDoc,
		addDoc: _addDoc,
		collection: _collection,
		getDocs: _getDocs,
		updateDoc: _updateDoc,
		query: _query,
		where: _where,
		orderBy: _orderBy,
		serverTimestamp: _serverTimestamp
	} = firestoreModule;

	const app = initializeApp(firebaseConfig);
	console.log('Firebase initialized for project:', firebaseConfig.projectId, 'sdk:', versionUsed);
	auth = getAuth(app);
	db = getFirestore(app);

	createUserWithEmailAndPassword = _createUserWithEmailAndPassword;
	signInWithEmailAndPassword = _signInWithEmailAndPassword;
	signOut = _signOut;
	onAuthStateChanged = _onAuthStateChanged;
	sendPasswordResetEmail = _sendPasswordResetEmail;
	doc = _doc;
	setDoc = _setDoc;
	getDoc = _getDoc;
	addDoc = _addDoc;
	collection = _collection;
	getDocs = _getDocs;
	updateDoc = _updateDoc;
	query = _query;
	where = _where;
	orderBy = _orderBy;
	serverTimestamp = _serverTimestamp;

	return true;
})();
