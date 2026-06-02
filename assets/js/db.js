import { db, addDoc, collection, serverTimestamp } from './firebase.js';

export async function createEnrollment(enrollmentData) {
  if (!enrollmentData) throw new Error('enrollmentData required');
  const docRef = await addDoc(collection(db, 'enrollment'), {
    ...enrollmentData,
    created_at: serverTimestamp()
  });
  return docRef;
}

export async function createStudent(studentData) {
  if (!studentData) throw new Error('studentData required');
  const docRef = await addDoc(collection(db, 'students'), {
    ...studentData,
    enrollment_date: studentData.enrollment_date || serverTimestamp()
  });
  return docRef;
}
