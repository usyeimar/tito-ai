<?php

declare(strict_types=1);

namespace App\Enums\Workflow;

enum WorkflowActionIcon: string
{
    case MAIL = 'IconMail';
    case MESSAGE = 'IconMessage';
    case PHONE = 'IconPhone';
    case DEVICE_MOBILE = 'IconDeviceMobile';
    case BELL = 'IconBell';
    case WORLD = 'IconWorld';
    case DATABASE = 'IconDatabase';
    case EDIT = 'IconEdit';
    case TRASH = 'IconTrash';
    case SEARCH = 'IconSearch';
    case USER = 'IconUser';
    case ARROWS_SPLIT = 'IconArrowsSplit';
    case GIT_MERGE = 'IconGitMerge';
    case FILTER = 'IconFilter';
    case CODE = 'IconCode';
    case ROBOT = 'IconRobot';
    case PHOTO = 'IconPhoto';
    case VOLUME = 'IconSpeakerphone'; // Tabler Icons usually speakerphone
    case MICROPHONE = 'IconMicrophone';
    case FILE_PDF = 'IconFilePdf';
    case CALCULATOR = 'IconCalculator';
    case CALENDAR = 'IconCalendar';
    case VARIABLE = 'IconVariable';
    case SUB_TASK = 'IconSubtask';
    case ARROW_RIGHT = 'IconArrowRight';
}
