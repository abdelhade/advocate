<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission matrix for tenant team members
    |--------------------------------------------------------------------------
    | Keys are permission names stored in `permissions.name`.
    */
    'matrix' => [
        [
            'key' => 'clients',
            'label' => 'الموكلين',
            'permissions' => [
                'clients.view' => 'عرض',
                'clients.create' => 'إضافة',
                'clients.update' => 'تعديل',
                'clients.delete' => 'حذف',
            ],
        ],
        [
            'key' => 'cases',
            'label' => 'القضايا',
            'permissions' => [
                'cases.view' => 'عرض',
                'cases.create' => 'إضافة',
                'cases.update' => 'تعديل',
                'cases.delete' => 'حذف',
            ],
        ],
        [
            'key' => 'documents',
            'label' => 'المستندات',
            'permissions' => [
                'documents.view' => 'عرض',
                'documents.create' => 'رفع',
                'documents.download' => 'تحميل',
                'documents.delete' => 'حذف',
            ],
        ],
        [
            'key' => 'tasks',
            'label' => 'المهام',
            'permissions' => [
                'tasks.view' => 'عرض',
                'tasks.create' => 'إضافة',
                'tasks.update' => 'تعديل',
                'tasks.delete' => 'حذف',
            ],
        ],
        [
            'key' => 'invoices',
            'label' => 'الفواتير',
            'permissions' => [
                'invoices.view' => 'عرض',
                'invoices.create' => 'إضافة',
                'invoices.delete' => 'حذف',
            ],
        ],
        [
            'key' => 'payments',
            'label' => 'سندات القبض',
            'permissions' => [
                'payments.view' => 'عرض',
                'payments.create' => 'إضافة',
                'payments.delete' => 'حذف',
            ],
        ],
        [
            'key' => 'expenses',
            'label' => 'المصروفات',
            'permissions' => [
                'expenses.view' => 'عرض',
                'expenses.create' => 'إضافة',
                'expenses.delete' => 'حذف',
            ],
        ],
        [
            'key' => 'users',
            'label' => 'المستخدمين',
            'permissions' => [
                'users.view' => 'عرض',
                'users.create' => 'إضافة',
                'users.update' => 'تعديل',
                'users.delete' => 'حذف',
            ],
        ],
        [
            'key' => 'audit',
            'label' => 'سجل النشاط',
            'permissions' => [
                'audit.view' => 'عرض',
            ],
        ],
    ],

    'presets' => [
        'lawyer' => [
            'label' => 'محامي',
            'permissions' => [
                'clients.view', 'clients.create', 'clients.update',
                'cases.view', 'cases.create', 'cases.update',
                'documents.view', 'documents.create', 'documents.download',
                'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete',
            ],
        ],
        'accountant' => [
            'label' => 'محاسب',
            'permissions' => [
                'clients.view',
                'invoices.view', 'invoices.create', 'invoices.delete',
                'payments.view', 'payments.create', 'payments.delete',
                'expenses.view', 'expenses.create', 'expenses.delete',
                'audit.view',
            ],
        ],
        'assistant' => [
            'label' => 'مساعد',
            'permissions' => [
                'clients.view', 'clients.create',
                'cases.view',
                'documents.view', 'documents.create', 'documents.download',
                'tasks.view', 'tasks.create', 'tasks.update',
            ],
        ],
    ],
];
