import { Link } from '@inertiajs/react';
import { BadgeCheck, Bell, CreditCard, LogOut, Sparkles } from 'lucide-react';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { logout } from '@/routes';
import type { User } from '@/types';

interface UserMenuContentProps {
    user: User;
}

export function UserMenuContent({ user }: UserMenuContentProps) {
    const cleanup = useMobileNavigation();

    const handleLogout = () => {
        cleanup();
    };

    return (
        <>
            <DropdownMenuGroup>
                <DropdownMenuLabel className="p-0 font-normal">
                    <div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                        <UserInfo user={user} showEmail={true} />
                    </div>
                </DropdownMenuLabel>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem
                    render={
                        <Link
                            className="block w-full cursor-pointer"
                            href="#"
                            as="button"
                        >
                            <Sparkles className="mr-2 size-4" />
                            Upgrade to Pro
                        </Link>
                    }
                />
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem
                    render={
                        <Link
                            className="block w-full cursor-pointer"
                            href="#"
                            as="button"
                        >
                            <BadgeCheck className="mr-2 size-4" />
                            Account
                        </Link>
                    }
                />
                <DropdownMenuItem
                    render={
                        <Link
                            className="block w-full cursor-pointer"
                            href="#"
                            as="button"
                        >
                            <CreditCard className="mr-2 size-4" />
                            Billing
                        </Link>
                    }
                />
                <DropdownMenuItem
                    render={
                        <Link
                            className="block w-full cursor-pointer"
                            href="#"
                            as="button"
                        >
                            <Bell className="mr-2 size-4" />
                            Notifications
                        </Link>
                    }
                />
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                render={
                    <Link
                        className="block w-full cursor-pointer"
                        href={logout().url}
                        method="post"
                        as="button"
                        onClick={handleLogout}
                        data-test="logout-button"
                    />
                }
            >
                <LogOut className="mr-2 size-4" />
                Log out
            </DropdownMenuItem>
        </>
    );
}
