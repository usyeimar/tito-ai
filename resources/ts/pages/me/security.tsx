import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Fingerprint } from 'lucide-react';
import { MeLayout } from './me-layout';
import type { User } from '@/types';

interface Props {
    user: User;
}

export default function Security({ user }: Props) {
    return (
        <>
            <Head title="Security" />
            <MeLayout user={user} activeTab="security">
                <div className="space-y-4">
                    {/* Password Card */}
                    <div className="rounded-xl border border-border bg-card">
                        <div className="p-6">
                            <h2 className="text-base font-semibold">
                                Password
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Change your password and optionally sign out all
                                active sessions.
                            </p>

                            <div className="mt-6 space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="current-password">
                                        Current password
                                    </Label>
                                    <Input
                                        id="current-password"
                                        type="password"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="new-password">
                                        New password
                                    </Label>
                                    <Input id="new-password" type="password" />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="confirm-password">
                                        Confirm new password
                                    </Label>
                                    <Input
                                        id="confirm-password"
                                        type="password"
                                    />
                                </div>

                                <div className="flex items-center justify-between rounded-lg border border-border bg-muted/50 px-4 py-3">
                                    <div>
                                        <p className="text-sm font-medium">
                                            Sign out all other sessions
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            Recommended after changing your
                                            password.
                                        </p>
                                    </div>
                                    <Switch />
                                </div>
                            </div>
                        </div>

                        <div className="flex justify-end border-t border-border bg-muted/30 px-6 py-4">
                            <Button size="sm">Update password</Button>
                        </div>
                    </div>

                    {/* Two-factor authentication Card */}
                    <div className="rounded-xl border border-border bg-card">
                        <div className="p-6">
                            <h2 className="text-base font-semibold">
                                Two-factor authentication
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Add a second verification step to protect your
                                account.
                            </p>

                            <p className="mt-4 text-sm text-muted-foreground">
                                When enabled, you will be asked for a code from
                                your authenticator app each time you sign in.
                            </p>
                        </div>

                        <div className="flex justify-end border-t border-border bg-muted/30 px-6 py-4">
                            <Button size="sm">Enable two-factor</Button>
                        </div>
                    </div>

                    {/* Passkeys Card */}
                    <div className="rounded-xl border border-border bg-card">
                        <div className="p-6">
                            <h2 className="text-base font-semibold">
                                Passkeys
                            </h2>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Register passkeys for passwordless
                                authentication.
                            </p>

                            <div className="mt-4">
                                <Label htmlFor="passkey-name">
                                    Passkey name{' '}
                                    <span className="text-muted-foreground">
                                        (optional)
                                    </span>
                                </Label>
                                <div className="mt-2 flex gap-3">
                                    <Input
                                        id="passkey-name"
                                        placeholder="e.g. MacBook Pro, YubiKey"
                                        className="flex-1"
                                    />
                                    <Button size="sm" className="gap-2">
                                        <Fingerprint className="size-4" />
                                        Add passkey
                                    </Button>
                                </div>
                            </div>

                            <p className="mt-6 text-center text-sm text-muted-foreground">
                                No passkeys yet. Add one for faster, more secure
                                sign-in.
                            </p>
                        </div>
                    </div>
                </div>
            </MeLayout>
        </>
    );
}
