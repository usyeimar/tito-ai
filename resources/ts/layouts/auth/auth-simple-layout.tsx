import { Link } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { ThemeToggle } from '@/components/theme-toggle';
import { home } from '@/routes';
import type { AuthLayoutProps } from '@/types';

export default function AuthSimpleLayout({
    children,
    title,
    description,
}: AuthLayoutProps) {
    return (
        <div className="relative flex min-h-svh flex-col items-center justify-center bg-background px-6 py-12 md:p-10">
            <div className="absolute top-6 right-6">
                <ThemeToggle />
            </div>
            <div className="w-full max-w-sm">
                <div className="flex flex-col gap-8">
                    <div className="flex flex-col items-center gap-6">
                        <Link
                            href={home()}
                            className="flex flex-col items-center gap-3 font-medium transition-opacity hover:opacity-80"
                        >
                            <div className="flex h-30  items-center justify-center rounded-xl ">
                                <AppLogoIcon className="size-full fill-current" />
                            </div>
                        </Link>

                        <div className="space-y-2 text-center">
                            <h1 className="text-2xl font-bold tracking-tight text-primary">
                                {title}
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                {description}
                            </p>
                        </div>
                    </div>
                    {children}
                </div>
            </div>

            <footer className="mt-12 flex items-center gap-4 text-xs text-muted-foreground">
                <Link href="#" className="hover:text-primary hover:underline">Privacy & Terms</Link>
                <span className="text-muted-foreground/30">•</span>
                <Link href="#" className="hover:text-primary hover:underline">Contact Us</Link>
            </footer>
        </div>
    );
}
