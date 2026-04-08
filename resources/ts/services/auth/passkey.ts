import PasskeyLoginController from '@/actions/App/Http/Controllers/Central/API/Auth/Passkey/PasskeyLoginController';

export const getPasskeyOptions = async (email?: string) => {
    const url = PasskeyLoginController.getOptions.definition.url;
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') ?? '',
        },
        body: JSON.stringify({ email }),
    });

    if (!response.ok) {
        throw new Error('Failed to fetch passkey options');
    }

    return response.json();
};

export const verifyPasskey = async (credentials: any) => {
    const url = PasskeyLoginController.login.definition.url;
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') ?? '',
        },
        body: JSON.stringify(credentials),
    });

    if (!response.ok) {
        throw new Error('Failed to verify passkey');
    }

    return response.json();
};

function getCookie(name: string) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop()?.split(';').shift();
}
