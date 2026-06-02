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
	const cdnBases = [
		(v) => `https://www.gstatic.com/firebasejs/${v}/`,
		(v) => `https://cdn.jsdelivr.net/npm/firebase@${v}/`,
	];

	let appModule, authModule, firestoreModule, versionUsed = null;

	for (const v of cdnVersions) {
		for (const baseFn of cdnBases) {
			const base = baseFn(v);
			try {
				console.log('Attempting to load Firebase SDK from', base);
				appModule = await import(`${base}firebase-app.js`);
				authModule = await import(`${base}firebase-auth.js`);
				firestoreModule = await import(`${base}firebase-firestore.js`);
				versionUsed = `${v} @ ${base}`;
				console.log('Loaded Firebase SDK from', versionUsed);
				break;
			} catch (err) {
				console.warn('Failed to load Firebase SDK from', base, err && err.message);
			}
		}
		if (appModule) break;
	}

	if (!appModule) {
		// Try local vendor folder then current directory (assets/js/vendor/ then assets/js/)
		const localBases = [new URL('./vendor/', import.meta.url).href, new URL('./', import.meta.url).href];
		let localLoaded = false;
		for (const lb of localBases) {
			try {
				console.log('Attempting to load Firebase SDK from local path', lb);
				appModule = await import(`${lb}firebase-app.js`);
				authModule = await import(`${lb}firebase-auth.js`);
				firestoreModule = await import(`${lb}firebase-firestore.js`);
				versionUsed = `local @ ${lb}`;
				console.log('Loaded Firebase SDK from', versionUsed);
				localLoaded = true;
				break;
			} catch (err) {
				console.warn('Failed to load Firebase SDK from local path', lb, err && err.message);
			}
		}
		if (!localLoaded) {
			console.error('Failed to load Firebase SDK from CDN and local paths');
			throw new Error('Firebase SDK not found (CDN and local paths failed)');
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
