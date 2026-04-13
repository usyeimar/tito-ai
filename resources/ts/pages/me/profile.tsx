import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { MeLayout } from './me-layout';
import type { User } from '@/types';

interface Props {
    user: User;
}

export default function Profile({ user }: Props) {
    const initials = user.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);

    return (
        <>
            <Head title="Profile" />
            <MeLayout user={user} activeTab="profile">
                <div className="rounded-xl border border-border bg-card">
                    <div className="p-6">
                        <h1 className="text-base font-semibold">Profile</h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Update your identity details and avatar.
                        </p>

                        {/* Avatar section */}
                        <div className="mt-6 flex items-center gap-4">
                            <Avatar className="size-14">
                                <AvatarImage
                                    src={user.avatar_url}
                                    alt={user.name}
                                />
                                <AvatarFallback className="bg-muted text-base">
                                    {initials}
                                </AvatarFallback>
                            </Avatar>
                            <div className="flex items-center gap-3">
                                <Button variant="outline" size="sm">
                                    Upload image
                                </Button>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="text-muted-foreground hover:text-foreground"
                                >
                                    Remove
                                </Button>
                            </div>
                        </div>

                        {/* Form */}
                        <div className="mt-8 space-y-5">
                            <div className="space-y-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    defaultValue={user.name}
                                    placeholder="Your name"
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    defaultValue={user.email}
                                    placeholder="your@email.com"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Changing your email will prompt for password
                                    confirmation.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* Footer */}
                    <div className="flex justify-end border-t border-border bg-muted/30 px-6 py-4">
                        <Button size="sm">Save profile</Button>
                    </div>
                </div>
            </MeLayout>
        </>
    );
}
