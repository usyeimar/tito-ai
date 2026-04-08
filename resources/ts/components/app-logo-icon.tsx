import type { ImgHTMLAttributes } from 'react';

export default function AppLogoIcon(props: ImgHTMLAttributes<HTMLImageElement>) {
    return (
        <img {...props} src="/media/logo.png" alt="App Logo" />
    );
}
