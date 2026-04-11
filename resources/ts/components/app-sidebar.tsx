import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Sparkles, LayoutGrid } from 'lucide-react';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { TenantSwitcher } from '@/components/tenant-switcher';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
import type { TenantSummary } from '@/types/agent';

export function AppSidebar() {
    const page = usePage<{ tenant?: TenantSummary }>();
    const tenant = page.props.tenant;

    const mainNavItems: NavItem[] = tenant
        ? [
              {
                  title: 'Asistentes',
                  href: `/${tenant.slug}/agents`,
                  icon: Sparkles,
              },
              {
                  title: 'Knowledge',
                  href: `/${tenant.slug}/knowledge`,
                  icon: BookOpen,
              },
          ]
        : [
              {
                  title: 'Dashboard',
                  href: dashboard(),
                  icon: LayoutGrid,
              },
          ];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <TenantSwitcher />
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
