<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP\Enum;

enum Permission: string
{
    case Settings = 'settings';
    case Contacts = 'contacts';
    case Calls = 'calls';
    case Explorer = 'explorer';
    case Downloader = 'downloader';
    case Parental = 'parental';
    case Pvr = 'pvr';
    case Profile = 'profile';
}
