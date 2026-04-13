import { Link, router, usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { ThemeToggle } from '@/components/theme-toggle';
import {
    Building2,
    ChevronDown,
    IdCard,
    LogOut,
    Settings,
    Shield,
} from 'lucide-react';
import { logout } from '@/routes';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { User } from '@/types';

interface Workspace {
    id: string;
    name: string;
    slug: string;
}

interface MeLayoutProps {
    user: User;
    activeTab: 'profile' | 'security';
    children: React.ReactNode;
}

function WorkspacesDropdown() {
    const { workspaces } = usePage<{ workspaces: Workspace[] }>().props;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger className="flex items-center gap-2 rounded-lg border border-border bg-background px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                <Building2 className="size-4" />
                Workspaces
                <ChevronDown className="size-3.5 text-muted-foreground" />
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuGroup>
                    <DropdownMenuLabel>Open a workspace</DropdownMenuLabel>
                    {workspaces?.map((workspace) => (
                        <DropdownMenuItem key={workspace.id} asChild>
                            <Link
                                href={`/workspaces/${workspace.slug}/enter`}
                                className="cursor-pointer"
                            >
                                {workspace.name}
                            </Link>
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuGroup>
                <DropdownMenuSeparator />
                <DropdownMenuGroup>
                    <DropdownMenuItem asChild>
                        <Link
                            href="/workspaces"
                            className="flex cursor-pointer items-center gap-2"
                        >
                            <Settings className="size-4" />
                            Manage workspaces
                        </Link>
                    </DropdownMenuItem>
                </DropdownMenuGroup>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

export function MeLayout({ user, activeTab, children }: MeLayoutProps) {
    const handleLogout = () => {
        router.post(logout().url);
    };

    return (
        <div className="min-h-screen bg-background">
            {/* Sticky Header */}
            <header className="sticky top-0 z-50 border-b border-border bg-background">
                <div className="flex h-16 items-center justify-between px-8">
                    <Link href="/">
                        <AppLogoIcon className="h-10 w-auto" />
                    </Link>
                    <div className="flex items-center gap-3">
                        <ThemeToggle />
                        <WorkspacesDropdown />
                    </div>
                </div>
            </header>

            {/* Main Layout */}
            <div className="flex gap-6 p-8">
                {/* Sidebar Card - Sticky below header */}
                <aside className="sticky top-24 h-fit w-64 shrink-0 rounded-xl border border-border bg-card p-5">
                    <div className="mb-4">
                        <h2 className="text-sm font-semibold">
                            Account settings
                        </h2>
                        <p className="mt-0.5 text-xs text-muted-foreground">
                            {user.email}
                        </p>
                    </div>

                    <nav className="space-y-0.5">
                        <Link
                            href="/me/profile"
                            className={`flex items-center gap-2 rounded-md px-2.5 py-2 text-xs font-medium transition-colors ${
                                activeTab === 'profile'
                                    ? 'bg-accent text-accent-foreground'
                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                            }`}
                        >
                            <IdCard className="size-4" />
                            Profile
                        </Link>
                        <Link
                            href="/me/security"
                            className={`flex items-center gap-2 rounded-md px-2.5 py-2 text-xs font-medium transition-colors ${
                                activeTab === 'security'
                                    ? 'bg-accent text-accent-foreground'
                                    : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                            }`}
                        >
                            <Shield className="size-4" />
                            Security
                        </Link>
                    </nav>

                    <div className="mt-4 border-t border-border pt-4">
                        <p className="text-[10px] leading-relaxed text-muted-foreground">
                            Email changes and passkey registration require
                            password confirmation.
                        </p>
                    </div>

                    <button
                        onClick={handleLogout}
                        className="mt-4 flex w-full items-center gap-2 rounded-md px-2.5 py-2 text-xs text-muted-foreground transition-colors hover:bg-muted hover:text-destructive"
                    >
                        <LogOut className="size-4" />
                        Log out
                    </button>
                </aside>

                {/* Content Area - Scrollable */}
                <main className="flex-1 pb-8">{children}</main>
            </div>
        </div>
    );
}
