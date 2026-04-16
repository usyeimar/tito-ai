import { useState } from 'react';
import { Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { logout } from '@/routes';

export default function VerifyEmail({ status }: { status?: string }) {
    const [processing, setProcessing] = useState(false);
    const [message, setMessage] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);

    const handleResend = async () => {
        setProcessing(true);
        setMessage(null);
        setError(null);

        try {
            const response = await fetch('/email/verification-notification', {
                method: 'POST',
                credentials: 'include',
                headers: {
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (response.ok) {
                setMessage(
                    'A new verification link has been sent to your email address.',
                );
            } else {
                const data = await response.json();
                setError(data.message || 'Failed to send verification email.');
            }
        } catch (err) {
            setError('An error occurred. Please try again.');
        } finally {
            setProcessing(false);
        }
    };

    return (
        <AuthLayout
            title="Verify email"
            description="Please verify your email address by clicking on the link we just emailed to you."
        >
            <Head title="Email verification" />

            {status === 'verification-link-sent' && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    A new verification link has been sent to the email address
                    you provided during registration.
                </div>
            )}

            {message && (
                <div className="mb-4 text-center text-sm font-medium text-green-600">
                    {message}
                </div>
            )}

            {error && (
                <div className="mb-4 text-center text-sm font-medium text-red-600">
                    {error}
                </div>
            )}

            <div className="space-y-6 text-center">
                <Button
                    onClick={handleResend}
                    disabled={processing}
                    variant="secondary"
                >
                    {processing && <Spinner />}
                    Resend verification email
                </Button>

                <TextLink href={logout()} className="mx-auto block text-sm">
                    Log out
                </TextLink>
            </div>
        </AuthLayout>
    );
}
