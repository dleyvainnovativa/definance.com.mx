export async function sendTokenToBackend(user) {
    const token = await user.getIdToken(true);
    localStorage.setItem("finance_auth_token", token);
    let csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    await fetch(`${app_url}auth/firebase`, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf,
            'Authorization': `Bearer ${token}`
        },
        body: JSON.stringify({ token })
    });
}
