<?php

declare(strict_types=1);

namespace App\Enums\Workflow;

enum WorkflowActionType: string
{
    // Logic
    case IF_ELSE = 'IF_ELSE';
    case SWITCH = 'SWITCH';
    case FILTER = 'FILTER';
    case MERGE = 'MERGE';
    case SUB_WORKFLOW = 'SUB_WORKFLOW';
    case PASSTHROUGH = 'PASSTHROUGH'; // Maps to FORM/WAIT internally usually, or used as generic
    case FORM = 'FORM';
    case WAIT_FOR_EVENT = 'WAIT_FOR_EVENT';
    case CODE = 'CODE';

    // CRM
    case CREATE_RECORD = 'CREATE_RECORD';
    case UPDATE_RECORD = 'UPDATE_RECORD';
    case DELETE_RECORD = 'DELETE_RECORD';
    case FIND_RECORDS = 'FIND_RECORDS';
    case ASSIGN_OWNER = 'ASSIGN_OWNER';
    case CONVERT_LEAD = 'CONVERT_LEAD';

    // Communication
    case SEND_EMAIL = 'SEND_EMAIL';
    case SEND_SMS = 'SEND_SMS';
    case SEND_PUSH = 'SEND_PUSH';
    case NOTIFY = 'NOTIFY';
    case RING_CENTRAL_SMS = 'RING_CENTRAL_SMS';
    case RING_CENTRAL_MAKE_CALL = 'RING_CENTRAL_MAKE_CALL';
    case RING_CENTRAL_LINK_TO_CONTACT = 'RING_CENTRAL_LINK_TO_CONTACT';
    case RING_CENTRAL_RESOLVE_VOICEMAIL = 'RING_CENTRAL_RESOLVE_VOICEMAIL';
    case HTTP_REQUEST = 'HTTP_REQUEST';
    case WEBHOOK = 'WEBHOOK';

    // AI & Helpers
    case AI_AGENT = 'AI_AGENT';
    case AI_IMAGE = 'AI_IMAGE';
    case AI_AUDIO = 'AI_AUDIO';
    case AI_TRANSCRIPTION = 'AI_TRANSCRIPTION';
    case GENERATE_PDF = 'GENERATE_PDF';
    case COMPUTE_VARIABLE = 'COMPUTE_VARIABLE';
    case MATH_HELPER = 'MATH_HELPER';
    case DATE_HELPER = 'DATE_HELPER';

    public function label(): string
    {
        return match ($this) {
            self::IF_ELSE => 'If / Else',
            self::SWITCH => 'Switch',
            self::FILTER => 'Filter',
            self::MERGE => 'Merge Branches',
            self::SUB_WORKFLOW => 'Trigger Workflow',
            self::PASSTHROUGH, self::FORM, self::WAIT_FOR_EVENT => 'Passthrough / Wait',
            self::CODE => 'Execute Code',
            self::CREATE_RECORD => 'Create Record',
            self::UPDATE_RECORD => 'Update Record',
            self::DELETE_RECORD => 'Delete Record',
            self::FIND_RECORDS => 'Find Records',
            self::ASSIGN_OWNER => 'Assign Owner',
            self::CONVERT_LEAD => 'Convert Lead',
            self::SEND_EMAIL => 'Send Email',
            self::SEND_SMS => 'Send SMS',
            self::SEND_PUSH => 'Send Push Notification',
            self::RING_CENTRAL_SMS => 'Send SMS (RingCentral)',
            self::RING_CENTRAL_MAKE_CALL => 'Make Call (RingCentral)',
            self::RING_CENTRAL_LINK_TO_CONTACT => 'Link to CRM Contact (RingCentral)',
            self::RING_CENTRAL_RESOLVE_VOICEMAIL => 'Resolve Voicemail (RingCentral)',
            self::NOTIFY => 'Notify User',
            self::HTTP_REQUEST, self::WEBHOOK => 'HTTP Request',
            self::AI_AGENT => 'AI Agent (Text)',
            self::AI_IMAGE => 'AI Image Generation',
            self::AI_AUDIO => 'AI Audio (TTS)',
            self::AI_TRANSCRIPTION => 'AI Transcription (STT)',
            self::GENERATE_PDF => 'Generate PDF',
            self::COMPUTE_VARIABLE => 'Compute Variable',
            self::MATH_HELPER => 'Math Helper',
            self::DATE_HELPER => 'Date Helper',
        };
    }

    public function category(): WorkflowActionCategory
    {
        return match ($this) {
            self::IF_ELSE, self::SWITCH, self::FILTER, self::MERGE, self::SUB_WORKFLOW, self::PASSTHROUGH, self::FORM, self::WAIT_FOR_EVENT, self::CODE => WorkflowActionCategory::LOGIC,
            self::CREATE_RECORD, self::UPDATE_RECORD, self::DELETE_RECORD, self::FIND_RECORDS, self::ASSIGN_OWNER, self::CONVERT_LEAD, self::RING_CENTRAL_LINK_TO_CONTACT => WorkflowActionCategory::CRM,
            self::SEND_EMAIL, self::SEND_SMS, self::SEND_PUSH, self::RING_CENTRAL_SMS, self::RING_CENTRAL_MAKE_CALL, self::RING_CENTRAL_RESOLVE_VOICEMAIL, self::NOTIFY, self::HTTP_REQUEST, self::WEBHOOK => WorkflowActionCategory::COMMUNICATION,
            self::AI_AGENT, self::AI_IMAGE, self::AI_AUDIO, self::AI_TRANSCRIPTION => WorkflowActionCategory::AI,
            self::GENERATE_PDF, self::COMPUTE_VARIABLE, self::MATH_HELPER, self::DATE_HELPER => WorkflowActionCategory::HELPER,
        };
    }

    public function icon(): WorkflowActionIcon
    {
        return match ($this) {
            self::IF_ELSE => WorkflowActionIcon::ARROWS_SPLIT,
            self::SWITCH => WorkflowActionIcon::ARROWS_SPLIT,
            self::FILTER => WorkflowActionIcon::FILTER,
            self::MERGE => WorkflowActionIcon::GIT_MERGE,
            self::SUB_WORKFLOW => WorkflowActionIcon::SUB_TASK,
            self::PASSTHROUGH, self::FORM, self::WAIT_FOR_EVENT => WorkflowActionIcon::ARROW_RIGHT,
            self::CODE => WorkflowActionIcon::CODE,
            self::CREATE_RECORD => WorkflowActionIcon::DATABASE,
            self::UPDATE_RECORD => WorkflowActionIcon::EDIT,
            self::DELETE_RECORD => WorkflowActionIcon::TRASH,
            self::FIND_RECORDS => WorkflowActionIcon::SEARCH,
            self::ASSIGN_OWNER => WorkflowActionIcon::USER,
            self::CONVERT_LEAD => WorkflowActionIcon::USER,
            self::SEND_EMAIL => WorkflowActionIcon::MAIL,
            self::SEND_SMS => WorkflowActionIcon::MESSAGE,
            self::SEND_PUSH => WorkflowActionIcon::DEVICE_MOBILE,
            self::RING_CENTRAL_SMS => WorkflowActionIcon::MESSAGE,
            self::RING_CENTRAL_MAKE_CALL => WorkflowActionIcon::PHONE,
            self::RING_CENTRAL_LINK_TO_CONTACT => WorkflowActionIcon::USER,
            self::RING_CENTRAL_RESOLVE_VOICEMAIL => WorkflowActionIcon::MICROPHONE,
            self::NOTIFY => WorkflowActionIcon::BELL,
            self::HTTP_REQUEST, self::WEBHOOK => WorkflowActionIcon::WORLD,
            self::AI_AGENT => WorkflowActionIcon::ROBOT,
            self::AI_IMAGE => WorkflowActionIcon::PHOTO,
            self::AI_AUDIO => WorkflowActionIcon::VOLUME,
            self::AI_TRANSCRIPTION => WorkflowActionIcon::MICROPHONE,
            self::GENERATE_PDF => WorkflowActionIcon::FILE_PDF,
            self::COMPUTE_VARIABLE => WorkflowActionIcon::VARIABLE,
            self::MATH_HELPER => WorkflowActionIcon::CALCULATOR,
            self::DATE_HELPER => WorkflowActionIcon::CALENDAR,
        };
    }
}
