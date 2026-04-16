import { useState } from 'react';
import { Head } from '@inertiajs/react';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/auth-layout';
import { logout } from '@/routes';
import { centralApi, CentralApiError } from '@/lib/central-api';

export default function VerifyEmail({ status }: { status?: string }) {
    const [processing, setProcessing] = useState(false);
    const [successMessage, setSuccessMessage] = useState<string | null>(null);
    const [errorMessage, setErrorMessage] = useState<string | null>(null);

    const handleResendVerification = async () => {
        setProcessing(true);
        setSuccessMessage(null);
        setErrorMessage(null);

        try {
            await centralApi<{ message: string }>(
                '/auth/email/verification-notification',
                {
                    method: 'POST',
                },
            );
            setSuccessMessage(
                'A new verification link has been sent to your email address.',
            );
        } catch (error) {
            if (error instanceof CentralApiError) {
                setErrorMessage(error.message);
            } else {
                setErrorMessage(
                    'Unable to send verification email. Please try again later.',
                );
            }
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
                <div className="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                    A new verification link has been sent to your email address.
                </div>
            )}

            <div className="space-y-6 text-center">
                {successMessage && (
                    <div className="rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                        {successMessage}
                    </div>
                )}

                {errorMessage && (
                    <div className="rounded-lg bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                        {errorMessage}
                    </div>
                )}

                <Button
                    onClick={handleResendVerification}
                    disabled={processing}
                    variant="secondary"
                    className="w-full"
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
