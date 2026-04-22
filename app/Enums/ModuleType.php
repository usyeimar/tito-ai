<?php

namespace App\Enums;

enum ModuleType: string
{
    case LEADS = 'leads';
    case COMPANIES = 'companies';
    case PROPERTIES = 'properties';
    case CONTACTS = 'contacts';
    case PROJECTS = 'projects';
    case VENDOR_COMPANIES = 'vendor_companies';
    case USERS = 'users';

    public function morphKey(): string
    {
        return match ($this) {
            self::LEADS => 'lead',
            self::COMPANIES => 'company',
            self::PROPERTIES => 'property',
            self::CONTACTS => 'contact',
            self::PROJECTS => 'project',
            self::VENDOR_COMPANIES => 'vendor_company',
            self::USERS => 'tenant_user',
        };
    }

    public function tableName(): string
    {
        return match ($this) {
            self::LEADS => 'leads',
            self::COMPANIES => 'companies',
            self::PROPERTIES => 'properties',
            self::CONTACTS => 'contacts',
            self::PROJECTS => 'projects',
            self::VENDOR_COMPANIES => 'vendor_companies',
            self::USERS => 'users',
        };
    }
}
