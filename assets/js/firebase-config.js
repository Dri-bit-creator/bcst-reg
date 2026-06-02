// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries


const firebaseConfig = {
  apiKey: "AIzaSyDg8mI_xQv6NFItVURXYhTkTcxerMFX9so",
  authDomain: "bcstr-6c048.firebaseapp.com",
  projectId: "bcstr-6c048",
  storageBucket: "bcstr-6c048.firebasestorage.app",
  messagingSenderId: "718064182195",
  appId: "1:718064182195:web:12eb82d583405da5f021c6",
  measurementId: "G-RM7NEL96J3"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);
