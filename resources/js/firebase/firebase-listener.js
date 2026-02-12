import { onIdTokenChanged } from "firebase/auth";
import { auth } from "./firebase-init";
import { sendTokenToBackend } from "./firebase-token";

let lastToken = null;
let syncing = false;

onIdTokenChanged(auth, async (user) => {
    if (!user) return;
    if (syncing) return;

    const token = await user.getIdToken();

    if (token === lastToken) return;

    syncing = true;
    lastToken = token;

    try {
        await sendTokenToBackend(user);
    } finally {
        syncing = false;
    }
});
