<?php

namespace App\Enum;

enum Position: string
{
    case MANAGER = 'manager';
    case ACCOUNT_MANAGER = 'account_manager';
    case QA_MANAGER = 'qa_manager';
    case DEV_MANAGER = 'dev_manager';
    case CEO = 'ceo';
    case COO = 'coo';
    case BACKEND_DEV = 'backend_dev';
    case FRONTEND_DEV = 'frontend_dev';
    case QA_TESTER = 'qa_tester';
}