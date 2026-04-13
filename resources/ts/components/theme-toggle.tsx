import { Monitor, Moon, Sun } from 'lucide-react';
import { useAppearance } from '@/hooks/use-appearance';

export function ThemeToggle() {
    const { appearance, updateAppearance } = useAppearance();

    const icons = {
        light: Sun,
        dark: Moon,
        system: Monitor,
    };

    const CurrentIcon = icons[appearance as keyof typeof icons] || Sun;

    return (
        <button
            type="button"
            className="flex size-8 items-center justify-center rounded-lg border border-border bg-background text-foreground transition-colors hover:bg-muted"
            onClick={() => {
                const modes: Array<'light' | 'dark' | 'system'> = [
                    'light',
                    'dark',
                    'system',
                ];
                const currentIndex = modes.indexOf(
                    appearance as 'light' | 'dark' | 'system',
                );
                const nextMode = modes[(currentIndex + 1) % modes.length];
                updateAppearance(nextMode);
            }}
            title="Toggle theme"
        >
            <CurrentIcon className="size-4" />
        </button>
    );
}
