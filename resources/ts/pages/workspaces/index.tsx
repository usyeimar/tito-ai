import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { ThemeToggle } from '@/components/theme-toggle';
import { buttonVariants } from '@/components/ui/button';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { logout } from '@/routes';
import { cn } from '@/lib/utils';
import { LogIn, LogOut, Plus, User } from 'lucide-react';

type Workspace = {
    id: string;
    name: string;
    slug: string;
    created_at: string;
    updated_at: string;
};

type Invitation = {
    id: string;
    tenant: { id: string; name: string; slug: string };
    expires_at: string | null;
};

type Props = {
    workspaces: Workspace[];
    invitations: Invitation[];
    appUrl: string;
};

type Tab = 'workspaces' | 'invitations';

export default function WorkspacesIndex({
    workspaces,
    invitations,
    appUrl,
}: Props) {
    const [activeTab, setActiveTab] = React.useState<Tab>('workspaces');
    const [open, setOpen] = React.useState(false);
    const [name, setName] = React.useState('');
    const [processing, setProcessing] = React.useState(false);
    const [nameError, setNameError] = React.useState('');

    const handleCreate = (e: React.FormEvent) => {
        e.preventDefault();
        setNameError('');
        setProcessing(true);
        router.post(
            '/workspaces',
            { name },
            {
                onSuccess: () => {
                    setName('');
                    setOpen(false);
                },
                onError: (errors) => {
                    setNameError(errors.name ?? 'Something went wrong.');
                },
                onFinish: () => setProcessing(false),
            },
        );
    };

    const workspaceUrl = (slug: string) => {
        const url = new URL(appUrl);
        return `${url.protocol}//${url.hostname}/${slug}`;
    };

    return (
        <>
            <Head title="Workspaces" />
            <div className="relative flex min-h-svh flex-col bg-background">
                <header className="absolute top-0 right-0 flex items-center gap-4 px-6 py-4 text-sm text-muted-foreground">
                    <Link
                        href="/me/profile"
                        className="flex items-center gap-1.5 transition-colors hover:text-foreground"
                    >
                        <User className="size-4" />
                        My account
                    </Link>
                    <button
                        onClick={() => router.post(logout())}
                        className="flex items-center gap-1.5 transition-colors hover:text-destructive"
                    >
                        <LogOut className="size-4" />
                        Sign out
                    </button>
                </header>

                <main className="flex flex-1 flex-col items-center px-4 pt-16 pb-16">
                    <div className="w-full max-w-lg">
                        {/* Logo — left aligned */}
                        <div className="mb-8">
                            <Link href="/">
                                <AppLogoIcon className="h-28 w-auto" />
                            </Link>
                        </div>

                        {/* Title row */}
                        <div className="mb-6 flex items-start justify-between gap-4">
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">
                                    Workspaces
                                </h1>
                                <p className="mt-1 text-sm text-muted-foreground">
                                    Choose a workspace to manage your pipeline,
                                    <br />
                                    billing, and project work.
                                </p>
                            </div>
                            <button
                                onClick={() => setOpen(true)}
                                className={cn(
                                    buttonVariants({ size: 'default' }),
                                    'shrink-0 gap-2',
                                )}
                            >
                                <Plus className="size-4" />
                                Create workspace
                            </button>
                        </div>

                        {/* Card */}
                        <div className="rounded-2xl border border-border bg-card p-4 shadow-sm">
                            {/* Tabs — segmented style */}
                            <div className="mb-4 flex rounded-xl bg-muted p-1">
                                <button
                                    onClick={() => setActiveTab('workspaces')}
                                    className={cn(
                                        'flex flex-1 items-center justify-between rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                        activeTab === 'workspaces'
                                            ? 'bg-background text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    <span>Workspaces</span>
                                    <span
                                        className={cn(
                                            'text-sm',
                                            activeTab === 'workspaces'
                                                ? 'text-foreground'
                                                : 'text-muted-foreground',
                                        )}
                                    >
                                        {(workspaces || []).length}
                                    </span>
                                </button>
                                <button
                                    onClick={() => setActiveTab('invitations')}
                                    className={cn(
                                        'flex flex-1 items-center justify-between rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                        activeTab === 'invitations'
                                            ? 'bg-background text-foreground shadow-sm'
                                            : 'text-muted-foreground hover:text-foreground',
                                    )}
                                >
                                    <span>Invitations</span>
                                    <span
                                        className={cn(
                                            'text-sm',
                                            activeTab === 'invitations'
                                                ? 'text-foreground'
                                                : 'text-muted-foreground',
                                        )}
                                    >
                                        {(invitations || []).length}
                                    </span>
                                </button>
                            </div>

                            {/* Workspace list */}
                            {activeTab === 'workspaces' &&
                                ((workspaces || []).length === 0 ? (
                                    <div className="flex flex-col items-center justify-center gap-3 py-12 text-center">
                                        <p className="text-sm font-medium">
                                            No workspaces yet
                                        </p>
                                        <p className="max-w-[240px] text-xs text-muted-foreground">
                                            Create your first workspace to start
                                            signing in to tenant-specific areas.
                                        </p>
                                    </div>
                                ) : (
                                    <ul className="space-y-2">
                                        {(workspaces || []).map((workspace) => (
                                            <li key={workspace.id}>
                                                <div className="flex items-center gap-3 rounded-xl border border-border bg-background px-4 py-3">
                                                    {/* Avatar */}
                                                    <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                                                        {workspace.name
                                                            .charAt(0)
                                                            .toUpperCase()}
                                                    </div>
                                                    {/* Info */}
                                                    <div className="min-w-0 flex-1">
                                                        <p className="truncate text-sm font-semibold capitalize">
                                                            {workspace.name}
                                                        </p>{' '}
                                                        <p className="truncate text-xs text-muted-foreground">
                                                            {workspaceUrl(
                                                                workspace.slug,
                                                            )}
                                                        </p>
                                                    </div>
                                                    {/* Login button */}
                                                    <a
                                                        href={`/workspaces/${workspace.slug}/enter`}
                                                        className="flex size-8 shrink-0 items-center justify-center rounded-lg border border-border bg-background text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                                    >
                                                        <LogIn className="size-4" />
                                                    </a>
                                                </div>
                                            </li>
                                        ))}
                                    </ul>
                                ))}

                            {/* Invitations list */}
                            {activeTab === 'invitations' &&
                                ((invitations || []).length === 0 ? (
                                    <div className="flex flex-col items-center justify-center gap-3 py-12 text-center">
                                        <p className="text-sm font-medium">
                                            No pending invitations
                                        </p>
                                        <p className="max-w-[240px] text-xs text-muted-foreground">
                                            You'll see workspace invitations
                                            here when someone invites you.
                                        </p>
                                    </div>
                                ) : (
                                    <ul className="space-y-2">
                                        {(invitations || []).map(
                                            (invitation) => (
                                                <li
                                                    key={invitation.id}
                                                    className="flex items-center gap-3 rounded-xl border border-border bg-background px-4 py-3"
                                                >
                                                    <div className="flex size-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-semibold text-blue-600">
                                                        {invitation.tenant.name
                                                            .charAt(0)
                                                            .toUpperCase()}
                                                    </div>
                                                    <div className="min-w-0 flex-1">
                                                        <p className="truncate text-sm font-semibold">
                                                            {
                                                                invitation
                                                                    .tenant.name
                                                            }
                                                        </p>
                                                        <p className="truncate text-xs text-muted-foreground">
                                                            {
                                                                invitation
                                                                    .tenant.slug
                                                            }
                                                        </p>
                                                    </div>
                                                    <button
                                                        className={cn(
                                                            buttonVariants({
                                                                size: 'sm',
                                                                variant:
                                                                    'outline',
                                                            }),
                                                        )}
                                                    >
                                                        Accept
                                                    </button>
                                                </li>
                                            ),
                                        )}
                                    </ul>
                                ))}
                        </div>

                        <div className="mt-8 flex items-center justify-between">
                            <p className="text-xs text-muted-foreground">
                                Need help?{' '}
                                <Link
                                    href="#"
                                    className="underline underline-offset-4 hover:text-foreground"
                                >
                                    Contact support
                                </Link>{' '}
                                for workspace access issues.
                            </p>
                            <ThemeToggle />
                        </div>
                    </div>
                </main>

                {/* Create workspace modal */}
                <Dialog open={open} onOpenChange={setOpen}>
                    <DialogContent className="sm:max-w-md">
                        <DialogHeader>
                            <DialogTitle className="text-lg font-bold">
                                Create a new workspace
                            </DialogTitle>
                            <DialogDescription>
                                Give your workspace a clear name. You can invite
                                teammates after creation.
                            </DialogDescription>
                        </DialogHeader>
                        <form
                            onSubmit={handleCreate}
                            className="mt-2 flex flex-col gap-4"
                        >
                            <div className="flex flex-col gap-1.5">
                                <Label
                                    htmlFor="workspace-name"
                                    className="font-semibold"
                                >
                                    Workspace name
                                </Label>
                                <Input
                                    id="workspace-name"
                                    placeholder="Acme Workspace"
                                    value={name}
                                    onChange={(e) => setName(e.target.value)}
                                    className="h-11 text-base"
                                    autoFocus
                                />
                                {nameError && (
                                    <p className="text-xs text-destructive">
                                        {nameError}
                                    </p>
                                )}
                            </div>
                            <DialogFooter>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => setOpen(false)}
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    disabled={processing || !name.trim()}
                                >
                                    Create workspace
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </>
    );
}
