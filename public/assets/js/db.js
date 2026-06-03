import { firebaseReady, db, addDoc, collection, serverTimestamp, getDocs, query, where } from './firebase.js';

export async function createEnrollment(enrollmentData) {
  if (!enrollmentData) throw new Error('enrollmentData required');
  await firebaseReady;
  // If an owner UID is present, prevent duplicate enrollment by owner
  if (enrollmentData.owner) {
    try {
      const q = query(collection(db, 'enrollment'), where('owner', '==', enrollmentData.owner));
      const snap = await getDocs(q);
      if (snap.size > 0) {
        // Return the existing document reference to indicate prior submission
        return snap.docs[0].ref;
      }
    } catch (err) {
      console.warn('createEnrollment: duplicate check failed', err);
    }
  }

  const docRef = await addDoc(collection(db, 'enrollment'), {
    ...enrollmentData,
    created_at: serverTimestamp()
  });
  return docRef;
}

export async function createStudent(studentData) {
  if (!studentData) throw new Error('studentData required');
  await firebaseReady;
  const docRef = await addDoc(collection(db, 'students'), {
    ...studentData,
    enrollment_date: studentData.enrollment_date || serverTimestamp()
  });
  return docRef;
}
