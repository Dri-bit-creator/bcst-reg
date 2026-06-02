import { firebaseConfig } from './firebase-config.js';
import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.24.0/firebase-app.js';
import { getAuth, createUserWithEmailAndPassword as _createUserWithEmailAndPassword, signInWithEmailAndPassword as _signInWithEmailAndPassword, signOut as _signOut, onAuthStateChanged as _onAuthStateChanged, sendPasswordResetEmail as _sendPasswordResetEmail } from 'https://www.gstatic.com/firebasejs/9.24.0/firebase-auth.js';
import { getFirestore, doc as _doc, setDoc as _setDoc, getDoc as _getDoc, addDoc as _addDoc, collection as _collection, getDocs as _getDocs, updateDoc as _updateDoc, query as _query, where as _where, orderBy as _orderBy } from 'https://www.gstatic.com/firebasejs/9.24.0/firebase-firestore.js';

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);

export const createUserWithEmailAndPassword = _createUserWithEmailAndPassword;
export const signInWithEmailAndPassword = _signInWithEmailAndPassword;
export const signOut = _signOut;
export const onAuthStateChanged = _onAuthStateChanged;
export const sendPasswordResetEmail = _sendPasswordResetEmail;
export const doc = _doc;
export const setDoc = _setDoc;
export const getDoc = _getDoc;
export const addDoc = _addDoc;
export const collection = _collection;
export const getDocs = _getDocs;
export const updateDoc = _updateDoc;
export const query = _query;
export const where = _where;
export const orderBy = _orderBy;
export { auth, db };
