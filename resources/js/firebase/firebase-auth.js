import {
    signInWithEmailAndPassword,
    signInWithPopup,
    GoogleAuthProvider,
    signOut,
    updatePassword,
    EmailAuthProvider,
    reauthenticateWithCredential
} from "firebase/auth";

import {
    auth
} from "./firebase-init";
import {
    sendTokenToBackend
} from "./firebase-token";

export async function loginWithEmail(email, password) {
    const result = await signInWithEmailAndPassword(auth, email, password);
    await sendTokenToBackend(result.user);

    window.location.href = `${app_url}`;
}

export async function loginWithGoogle() {
    const provider = new GoogleAuthProvider();
    const result = await signInWithPopup(auth, provider);
    await sendTokenToBackend(result.user);

    window.location.href = `${app_url}`;
}

export async function logoutUser() {
    await signOut(auth);

    await fetch(`${app_url}logout`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": window.csrf
        }
    });

    window.location.href = `${app_url}login`;
}

export async function changePassword(currentPassword, newPassword) {

    try {
        const user = auth.currentUser;
        if (!user || !user.email) {
            throw new Error("No authenticated user");
        }
        const credential = EmailAuthProvider.credential(
            user.email,
            currentPassword
        );
        await reauthenticateWithCredential(user, credential);
        await updatePassword(user, newPassword);
        return {
            success: true,
            message: "Password updated successfully"
        };
    } catch (error) {
        console.error(error);
        return {
            success: false,
            message: error.message
        };
    }
}
