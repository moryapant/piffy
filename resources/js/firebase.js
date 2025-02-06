import { initializeApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider, signInWithPopup, onAuthStateChanged } from 'firebase/auth';

// Validate Firebase configuration
const requiredEnvVars = [
    'VITE_FIREBASE_API_KEY',
    'VITE_FIREBASE_AUTH_DOMAIN',
    'VITE_FIREBASE_PROJECT_ID',
    'VITE_FIREBASE_STORAGE_BUCKET',
    'VITE_FIREBASE_MESSAGING_SENDER_ID',
    'VITE_FIREBASE_APP_ID'
];

const missingEnvVars = requiredEnvVars.filter(varName => !import.meta.env[varName]);
if (missingEnvVars.length > 0) {
    throw new Error(`Missing required Firebase configuration: ${missingEnvVars.join(', ')}`);
}

const firebaseConfig = {
    apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: import.meta.env.VITE_FIREBASE_APP_ID
};

// Initialize Firebase
let app;
let auth;
let googleProvider;

try {
    app = initializeApp(firebaseConfig);
    auth = getAuth(app);
    googleProvider = new GoogleAuthProvider();
    googleProvider.setCustomParameters({
        prompt: 'select_account'
    });
} catch (error) {
    console.error('Firebase initialization error:', error);
    throw error;
}

// Sign in with Google
export const signInWithGoogle = async () => {
    try {
        const result = await signInWithPopup(auth, googleProvider);
        const idToken = await result.user.getIdToken();

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }

        // Send the token to your Laravel backend
        const response = await fetch('/login/google/callback', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ firebase_token: idToken })
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.message || 'Authentication failed');
        }

        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Google sign-in error:', error);
        throw error;
    }
};

// Auth state observer
export const onAuthStateChange = (callback) => {
    return onAuthStateChanged(auth, (user) => {
        callback(user);
    });
};

// Sign out
export const signOut = async () => {
    try {
        await auth.signOut();
    } catch (error) {
        console.error('Sign out error:', error);
        throw error;
    }
};

export { auth };
