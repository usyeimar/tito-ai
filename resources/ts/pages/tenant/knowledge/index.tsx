import React from 'react';
import { Head } from '@inertiajs/react';
import { BookOpen, Search, Plus } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import type { TenantSummary } from '@/types/agent';
import type { BreadcrumbItem } from '@/types';

type Props = {
    tenant: TenantSummary;
    documents: any[];
};

export default function KnowledgeIndex({ tenant, documents }: Props) {
    const [search, setSearch] = React.useState('');

    const breadcrumbs: BreadcrumbItem[] = [
        { title: tenant.name, href: `/${tenant.slug}/dashboard` },
        { title: 'Knowledge Base', href: `/${tenant.slug}/knowledge` },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Knowledge Base" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <header className="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            Knowledge Base
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Entrena tus asistentes con documentos, URLs, and custom text.
                        </p>
                    </div>
                    <div className="flex items-center gap-2">
                        <div className="relative">
                            <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                            <Input
                                type="search"
                                placeholder="Search documents..."
                                className="w-full bg-background pl-8 sm:w-[250px]"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </div>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Document
                        </Button>
                    </div>
                </header>

                <main className="flex-1">
                    {documents.length === 0 ? (
                        <div className="flex h-[400px] flex-col items-center justify-center rounded-xl border border-dashed text-center">
                            <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                                <BookOpen className="h-6 w-6 text-muted-foreground" />
                            </div>
                            <h3 className="mb-1 text-lg font-semibold">No documents yet</h3>
                            <p className="mb-4 max-w-sm text-sm text-muted-foreground">
                                Upload documents or add URLs to create a knowledge base que tus asistentes puedan usar to answer questions accurately.
                            </p>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Add your first document
                            </Button>
                        </div>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {/* Render documents here */}
                        </div>
                    )}
                </main>
            </div>
        </AppLayout>
    );
}
