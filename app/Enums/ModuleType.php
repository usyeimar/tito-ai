<?php

namespace App\Enums;

enum ModuleType: string
{
    case LEADS = 'leads';
    case COMPANIES = 'companies';
    case PROPERTIES = 'properties';
    case CONTACTS = 'contacts';
    case PROJECTS = 'projects';
    case PROPOSALS = 'proposals';
    case PROPOSAL_CHANGE_ORDERS = 'proposal_change_orders';
    case SERVICES = 'services';
    case VENDOR_COMPANIES = 'vendor_companies';
    case VEHICLES = 'vehicles';
    case EQUIPMENT = 'equipment';
    case MATERIALS = 'materials';
    case LABOR = 'labor';
    case EMAIL_TEMPLATES = 'email_templates';
    case DOCUMENT_TEMPLATES = 'document_templates';
    case OUTBOUND_EMAILS = 'outbound_emails';
    case USERS = 'users';

    public function morphKey(): string
    {
        return match ($this) {
            self::LEADS => 'lead',
            self::COMPANIES => 'company',
            self::PROPERTIES => 'property',
            self::CONTACTS => 'contact',
            self::PROJECTS => 'project',
            self::PROPOSALS => 'proposal',
            self::PROPOSAL_CHANGE_ORDERS => 'proposal_change_order',
            self::SERVICES => 'catalog_service',
            self::VENDOR_COMPANIES => 'vendor_company',
            self::VEHICLES => 'vehicle',
            self::EQUIPMENT => 'equipment',
            self::MATERIALS => 'material',
            self::LABOR => 'labor',
            self::EMAIL_TEMPLATES => 'email_template',
            self::DOCUMENT_TEMPLATES => 'document_template',
            self::OUTBOUND_EMAILS => 'outbound_email',
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
            self::PROPOSALS => 'proposals',
            self::PROPOSAL_CHANGE_ORDERS => 'proposals',
            self::SERVICES => 'catalog_services',
            self::VENDOR_COMPANIES => 'vendor_companies',
            self::VEHICLES => 'vehicles',
            self::EQUIPMENT => 'equipment',
            self::MATERIALS => 'materials',
            self::LABOR => 'labor',
            self::EMAIL_TEMPLATES => 'email_templates',
            self::DOCUMENT_TEMPLATES => 'document_templates',
            self::OUTBOUND_EMAILS => 'outbound_emails',
            self::USERS => 'users',
        };
    }
}
